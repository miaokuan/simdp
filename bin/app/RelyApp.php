<?php
/**
 * @author miaokuan
 */

use Dp\Task;
use Dp\Job;
use Dp\Rely;
use Sim\Log;

class RelyApp extends App
{
    /**
     * daemon: rely check
     */
    public function runAction(&$log)
    {
        $oTask = new Task();
        $oRely = new Rely();

        while (true) {
            Log::info("loop check rely.");
            $taskArr = $oTask->pending();
            foreach ($taskArr as $task) {
                $ready = true;
                $relyArr = $oRely->job($task['job_id']);
                foreach ($relyArr as $rely) {
                    $ready = $oTask->check($task['time'], $rely['rely_job'], $rely['start'],
                        $rely['long'], $rely['freq']);

                    Log::info("rely check [job_id:".$task['job_id']."] [rely_job:".$rely['rely_job']."] [time:".$task['time']."] [ready:".intval($ready)."]");
                    if (!$ready) {
                        break;
                    }
                }

                if ($ready) {
                    Log::info("rely ready [job_id:".$task['job_id']."] [time:".$task['time']."]");
                    $oTask->setReady($task['job_id'], $task['time']);
                }
            }

            sleep(3);
        }

    }

    /**
     * rely add
     */
    public function addAction()
    {
        $oRely = new Rely();

        $bind = [];
        $fields = Rely::$fields;
        foreach ($fields as $key => $default) {
            isset($this->params[$key]) && $bind[$key] = $this->params[$key];
        }

        $rely_job = $bind['rely_job'];
        $oJob = new Job();
        $arr = $oJob->get($rely_job, 'freq');
        $bind['freq'] = $arr['freq'];

        $id = $oRely->add($bind);

        $errno = $id > 0 ? 0 : -$id;
        $data = [
            'errno' => $errno,
            'id' => $id,
        ];
        $this->json($data);
    }

    /**
     * rely delete
     */
    public function delAction()
    {
        $oRely = new Rely();
        $job_id = $this->params['job_id'];
        $rely_job = $this->params['rely_job'];
        $ret = $oRely->del($job_id, $rely_job);

        $errno = $ret ? 0 : 1;
        $data = [
            "errno" => $errno,
        ];
        $this->json($data);
    }

}
