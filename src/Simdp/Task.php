<?php
namespace Simdp;

use Wee\Widget;

class Task extends Wee\Widget
{
    public $database = 'simdp';
    public $table = 'simdp_task';

    static $fields = array(
            'id' => 0,
            'job_id' => 0,
            'time' => 0,
            'name' => '',
            'status' => 0,
            'retry' => 0,
            'start_time' => 0,
            'end_time' => 0,
            'addtime' => 0,
        );

    const PENDING = 0;
    const READY = 1;
    const FINISH = 9;

    /**
     * add a task
     * @params array $bind
     * @return int
     */
    public function add(array $bind)
    {
        $insert = $this->in(self::$fields, $bind);
        unset($insert['id']);

        $ret = $this->db->insert($this->table, $insert);

        if ($ret === false) {
            return -1;
        }

        $id = $this->db->insert_id;
        return $id;
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

    public function pending()
    {
        $fields = array('job_id', 'time');
        $nowtime = time();
        $conds = array(
            'start_time<=' => $nowtime,
            'end_time>=' => $nowtime,
            'status=' => self::PENDING,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    public function ready()
    {
        $fields = array('job_id', 'time');
        $nowtime = time();
        $conds = array(
            'start_time<=' => $nowtime,
            'end_time>=' => $nowtime,
            'status=' => self::READY,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    /**
     * set a task's status to READY
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function toReady($job_id, $time)
    {
        $conds = array(
            'job_id=' => $job_id,
            'time=' => $time,
            );
        $bind = array(
            'status' => self::READY,
            );

        $ret = $this->db->update($this->table, $bind, $conds);

        return $ret === false ? false : true;
    }

    /**
     * set a task's status to finish
     * @params int $job_id
     * @params int $time
     * @return boolean
     */
    public function toFinish($job_id, $time)
    {
        $conds = array(
            'job_id=' => $job_id,
            'time=' => $time,
            );
        $bind = array(
            'status' => self::FINISH,
            );

        $ret = $this->db->update($this->table, $bind, $conds);

        return $ret === false ? false : true;
    }

    public function check($time, $job_id, $start, $long, $freq)
    {
        if ($long > 1) {
            return true;
        }

        $stime = $time + $start * $freq;
        $etime = $stime + $long * $freq;

        $conds = array(
            'job_id=' => $job_id,
            'time>=' => $stime,
            'time<' => $etime,
            'status=' => self::FINISH,
            );
        $count = $this->db->count($this->table, $conds);

        return $count === $long ? true : false;
    }
}
