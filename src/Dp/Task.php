<?php
/**
 * @author miaokuan
 */

namespace Dp;

use Sim\Widget;
use Sim\Log;

class Task extends Widget
{
    public $database = 'simdp';
    public $table = 'simdp_task';

    const TIME_FMT = 'YmdHis';

    static $fields = array(
            'id' => 0,
            'job_id' => 0,
            'time' => 0,
            'name' => '',
            'priority' => 3,
            'freq' => 3600,
            'status' => 0,
            'addtime' => 0,
            'start_time' => 0,
            'end_time' => 0,
            'userid' => 0,
        );

    const PENDING = 0;
    const READY = 1;
    const KILLED = 8;
    const FINISHED = 9;

    const ERR_NOFREQ = -1;
    const ERR_DB = -501;

    /**
     * add a task
     * @params array $bind
     * @return int
     */
    public function add(array $bind)
    {
        $insert = $this->in(self::$fields, $bind);
        unset($insert['id']);

        $nowtime = time();
        $insert['addtime'] = $nowtime;
        empty($insert['start_time']) && $insert['start_time'] = $nowtime;
        empty($insert['end_time']) && $insert['end_time'] = $nowtime + 10 * $insert['freq'];
        if ($insert['freq'] < 1) {
            return self::ERR_NOFREQ;
        }

        $insert['time'] = $this->timeFormat($insert['time'], $insert['freq']);
        $ret = $this->db->insert($this->table, $insert, $options);

        if ($ret === false) {
            return self::ERR_DB;
        }

        $id = $this->db->insert_id;
        return $id;
    }

    /**
     * format task time
     * @params string $timestr
     * @params int $freq
     * @return int
     */
    public static function timeFormat($timestr, $freq)
    {
        //YmdHis 年月日时分秒
        if ($timestr) {
            $timestamp = strtotime($timestr);
        } else {
            $timestamp = time() - $freq;
        }

        $timestamp = floor($timestamp/$freq) * $freq;
        $s = date(self::TIME_FMT, $timestamp);
        return $s;
    }

    /**
     * get a task
     * @params int $job_id
     * @params int $time
     * @return array
     */
    public function get($job_id, $time)
    {
        $conds = array(
            'job_id=' => $job_id,
            'time=' => $time,
            );

        $row = $this->db->selectRow($this->table, $conds);
        if (is_array($row)) {
            return $row;
        }

        return array();
    }

    /**
     * delete a task
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function del($job_id, $time)
    {
        $conds = array(
            'job_id=' => $job_id,
            'time=' => $time,
            );

        $ret = $this->db->delete($this->table, $conds);

        return $ret;
    }

    /**
     * get pending tasks
     * @return array
     */
    public function pending()
    {
        $fields = array('job_id', 'time');
        $nowtime = time();
        $conds = array(
            'start_time<=' => $nowtime,
            // 'end_time>=' => $nowtime,
            'status=' => self::PENDING,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    /**
     * get ready tasks
     * @return array
     */
    public function ready()
    {
        $fields = array('job_id', 'time');
        $nowtime = time();
        $conds = array(
            'start_time<=' => $nowtime,
            // 'end_time>=' => $nowtime,
            'status=' => self::READY,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    /**
     * set a task's status to pending/ready/killed/finished
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function setStatus($job_id, $time, $status = self::PENDING)
    {
        $conds = array(
            'job_id=' => $job_id,
            'time=' => $time,
            );
        $bind = array(
            'status' => $status,
            );

        $ret = $this->db->update($this->table, $bind, $conds);

        return $ret === false ? false : true;
    }

    /**
     * set a task's status to PENDING
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function setPending($job_id, $time)
    {
        $this->setStatus($job_id, $time, self::PENDING);
    }

    /**
     * set a task's status to READY
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function setReady($job_id, $time)
    {
        $this->setStatus($job_id, $time, self::READY);
    }

    /**
     * set a task's status to killed
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function setKilled($job_id, $time)
    {
        $this->setStatus($job_id, $time, self::KILLED);
    }

    /**
     * set a task's status to finish
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function setFinished($job_id, $time)
    {
        $this->setStatus($job_id, $time, self::FINISHED);
    }

    /**
     * check finish or not
     * @params bigint $timestr
     * @params int $job_id
     * @params int $start
     * @params int $long
     * @params int $freq
     * @return boolean
     */
    public function check($timestr, $job_id, $start, $long, $freq)
    {
        if ($long < 1) {
            return true;
        }
        $timestamp = strtotime($timestr);
        $stime = date(self::TIME_FMT, $timestamp + $start * $freq);
        $etime = date(self::TIME_FMT, $timestamp + $start * $freq + $long * $freq);

        $conds = array(
            'job_id=' => $job_id,
            'time>=' => $stime,
            'time<' => $etime,
            'status=' => self::FINISHED,
            );
        $count = $this->db->count($this->table, $conds);

        return $count === $long ? true : false;
    }
}
