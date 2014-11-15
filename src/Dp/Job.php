<?php
/**
 * @author miaokuan
 */

namespace Dp;

use Sim\Widget;

class Job extends Widget
{
    public $database = 'simdp';
    public $table = 'simdp_job';

    static $fields = array(
            'id' => 0,
            'name' => '',
            'freq' => 3600,
            'priority' => 3,
            'callback' => '',
            'addtime' => 0,
            'userid' => 0,
        );

    const ERR_DB = -501;

    /**
     * add a job
     * @params array $bind
     * @return int
     */
    public function add(array $bind)
    {
        $insert = $this->in(self::$fields, $bind);
        unset($insert['id']);
        $nowtime = time();
        $insert['addtime'] = time();

        $ret = $this->db->insert($this->table, $insert);

        if ($ret === false) {
            return self::ERR_DB;
        }

        $id = $this->db->insert_id;
        return $id;
    }

    /**
     * get a job
     * @params int $id
     * @return array
     */
    public function get($id, $fields = array('*'))
    {
        $conds = array(
            'id=' => $id,
            );

        $row = $this->db->selectRow($this->table, $fields, $conds);
        if (is_array($row)) {
            return $row;
        }

        return array();
    }

    /**
     * delete a job
     * @params int $id
     * @return boolean
     */
    public function del($id)
    {
        $conds = array(
            'id=' => $id,
            );
        $ret = $this->db->delete($this->table, $conds);

        return $ret === false ? false : true;
    }

    /**
     * callback, async
     */
    public function dispatch($id, $time)
    {
        $job = $this->get($id, array('callback'));

        $callback = json_decode($job['callback']);

        switch ($callback->protocol) {
            case 'http':
                $search = array('#id#', '#time#');
                $replace = array($id, $time);
                $url = str_replace($search, $replace, $callback->val);
                file_get_contents($url);
                break;
            case 'func':
                call_user_func_array($call->val, array($id, $time));
                break;
            case 'sh':
                $cmd = 'sh '.$call->val . " $id $time";
                $ret = 1;
                system($cmd, $ret);
                break;
        }

        return;
    }
}

