<?php
/**
 * @author miaokuan
 */

namespace Dp;

use Sim\Widget;

class Rely extends Widget
{
    public $database = 'simdp';
    public $table = 'simdp_rely';

    const ERR_NOJOBID = -1;
    const ERR_NORELYJOB = -2;
    const ERR_DB = -501;

    static $fields = array(
            'id' => 0,
            'job_id' => 0,
            'rely_job' => 0,
            'freq' => 0,
            'start' => 0,
            'long' => 1,
            'addtime' => 0,
            'userid' => 0,
        );

    /**
     * add a job's rely
     * @params array $bind
     * @return int
     */
    public function add(array $bind)
    {
        if (empty($bind['job_id'])) {
            return self::ERR_NOJOBID;
        }

        if (empty($bind['rely_job'])) {
            return self::ERR_NORELYJOB;
        }

        $insert = $this->in(self::$fields, $bind);
        unset($insert['id']);
        $nowtime = time();
        $insert['addtime'] = $nowtime;

        $ret = $this->db->insert($this->table, $insert);

        if ($ret === false) {
            return self::ERR_DBERR;
        }

        $id = $this->db->insert_id;
        return $id;
    }

    /**
     * get a job's rely
     * @params int $job_id
     * @return array
     */
    public function job($job_id)
    {
        $fields = array('rely_job', 'start', '`long`' , 'freq');
        $conds = array(
            'job_id=' => $job_id,
            );
        $rows = $this->db->select($this->table, $fields, $conds);

        return is_array($rows) ? $rows : array();
    }

    /**
     * delete a job's rely
     * @params int $id
     * @return boolean
     */
    public function del($job_id,$rely_job = 0)
    {
        $conds = array(
            'job_id=' => $job_id,
            );
        if ($rely_job) {
            $conds['rely_job='] = $rely_job;
        }

        $ret = $this->db->delete($this->table, $conds);

        return $ret === false ? false : true;
    }

}
