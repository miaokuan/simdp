<?php
/**
 * @author miaokuan
 */

namespace App;

use Dp\Task;
use Dp\Job;
use Sim\Log;

class Task extends App
{
    /**
     * daemon:dispatch ready tasks
     */
    public function runAction(&$log)
    {
        $oTask = new Task();
        $oJob = new Job();
        while (true) {
            Log::info("loop dispatch.");
            $taskArr = $oTask->ready();
            foreach ($taskArr as $task) {
                Log::info("dispatch [job_id:".$task['job_id']."] [time:".$task['time']."]");
                $oJob->dispatch($task['job_id'], $task['time']);
            }

            sleep(3);
        }
    }

    /**
     * add a task
     */
    public function addAction()
    {
        $oTask = new Task();

        $bind = [];
        $fields = Task::$fields;
        foreach ($fields as $key => $default) {
            isset($this->params[$key]) && $bind[$key] = $this->params[$key];
        }

        $oJob = new Job();
        $arr = $oJob->get($bind['job_id'], 'freq,name,priority');
        $bind['freq'] = $arr['freq'];
        $bind['name'] = $arr['name'];

        if (empty($bind['priority'])) {
            $bind['priority'] = $arr['priority'];
        }

        $id = $oTask->add($bind);

        $errno = $id > 0 ? 0 : -$id;
        $data = [
            'errno' => $errno,
            'id' => $id,
        ];
        $this->json($data);
    }

    /**
     * delete a task
     */
    public function delAction()
    {
        $oRely = new Rely();
        $job_id = $this->params['job_id'];
        $time = $this->params['time'];

        $ret = $oRely->del($job_id, $rely_job);

        $errno = $ret ? 0 : 1;
        $data = [
            "errno" => $errno,
        ];
        $this->json($data);
    }

    /**
     * set a task to killed
     */
    public function killAction()
    {
        $job_id = $this->params['job_id'];
        $time = $this->params['time'];
        $time = Task::timeFormat();

        $oTask = new Task();
        $oTask->setKilled($job_id, $time);
    }

    /**
     * set a task to finished
     */
    public function finishAction()
    {
        $job_id = $this->params['job_id'];
        $time = $this->params['time'];

        $oTask = new Task();
        $oTask->setFinished($job_id, $time);
    }

    /**
     * set a task to pending
     */
    public function pendingAction()
    {
        $job_id = $this->params['job_id'];
        $time = $this->params['time'];

        $oTask = new Task();
        $oTask->setPending($job_id, $time);
    }

    /**
     * set a task to ready
     */
    public function readyAction()
    {
        $job_id = $this->params['job_id'];
        $time = $this->params['time'];

        $oTask = new Task();
        $oTask->setReady($job_id, $time);
    }

}
