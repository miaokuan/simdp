<?php
/**
 * @author miaokuan
 */

use Dp\Job;

class JobApp extends App
{

    /**
     * add a job
     */
    public function addAction()
    {
        $oJob = new Job();

        $bind = [];
        $fields = Job::$fields;
        foreach ($fields as $key => $default) {
            isset($this->params[$key]) && $bind[$key] = $this->params[$key];
        }

        $id = $oJob->add($bind);

        $errno = $id > 0 ? 0 : 1;

        $data = [
            'errno' => $errno,
            'id' => $id,
        ];
        $this->json($data);
    }

    /**
     * delete a job
     */
    public function delAction()
    {
        $oJob = new Job();
        $id = $this->params['id'];
        $ret = $oJob->del($id);

        $errno = $ret ? 0 : 1;
        $data = [
            'errno' => $errno,
        ];
        $this->json($data);
    }

}
