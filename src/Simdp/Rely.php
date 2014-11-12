<?php
namespace Simdp;

use Wee\Widget;

class Rely extends Wee\Widget
{
    public $database = 'simdp';
    public $table = 'simdp_rely';

    static $fields = array(
            'id' => 0,
            'job_id' => 0,
            'rely_job' => 0,
            'start' => 0,
            'long' => 1,
            'addtime' => 0,
            'username' => '',
        );


    /**
     * add a job rely
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
     * get job rely
     * @params int $job_id
     * @return array()
     */
    public function job($job_id)
    {
        $fields = array('rely_job', 'start', 'long' , 'freq');
        $conds = array(
            'job_id=' => $job_id,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    /**
     * delete a job rely
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

}
