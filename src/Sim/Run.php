<?php
/**
 * @author miaokuan
 */

namespace Sim;

use Sim\Log;
use Sim\Timer;

class Run
{
    private $mypid = 0;
    private $pid_file = '';
    private $app = '';
    private $action = '';
    private $max = 1;
    public $params = array();

    public function __construct()
    {
        Timer::start('all');

        $this->mypid = getmypid();
        $this->init();
        $this->bootstrap();
        $this->run();

        Timer::end('all');
        $time = Timer::cal('all');

        Log::info('TIME:' . $time . ' us.');
    }

    public function init()
    {
        global $argv;

        $this->app = empty($argv[1]) ? 'demo' : $argv[1];
        $this->action = empty($argv[2]) ? 'run' : $argv[2];

        if (!empty($argv[3])) {
            parse_str($argv[3], $params);
            $this->params = $params;
        }

        if ($this->params['simdp'] > 1) {
            $this->max = $this->params['simdp'];
        }
    }

    public function bootstrap()
    {
        //log
        $logfile = SIM_ROOT . '/var/log/' . $this->app . '_' . $this->mypid . '.log';
        Log::logfile($logfile);
        Log::level(Log::INFO);

        //process exists
        // $pid_files = glob(SIM_ROOT . '/var/pid/' . $this->app . "*.pid");
        // if (is_array($pid_files) && (count($pid_files) >= $this->max)) {
        //     $text = "pid file(" . $pid_files[0] . ") existed.";
        //     Log::fatal($text);
        //     exit;
        // }

        //pid
        $pid_file = SIM_ROOT . '/var/pid/' . $this->app . '_' . $this->mypid . '.pid';
        if (file_put_contents($pid_file, $this->mypid)) {
            $this->pid_file = $pid_file;
        } else {
            Log::fatal("Unable to write pid to " . $pid_file);
            exit;
        }
    }

    public function run()
    {
        $pid = $this->mypid;
        $app = $this->app;
        $action = $this->action . 'Action';
        Log::info("Begin to execute. [app:$app action:$action pid:$pid]");

        $classname = $this->formatApp($app);
        if (!class_exists($classname)) {
            Log::fatal("Failed to find class:$classgname");
            return;
        }

        $class = new $classname($this->params);
        if (!method_exists($class, $action)) {
            Log::fatal("Failed to find method:$action");
            return;
        }

        Log::info("Calling method[$action] for $app.");
        $log = array();
        $result = $class->$action($log);

        //log
        if (!empty($log)) {
            foreach ($log as $l) {
                if (!is_scalar($l)) {
                    $l = explode("\n", trim(print_r($l, true)));
                } elseif (strlen($l) > 256) {
                    $l = substr($l, 0, 256) . '...(truncated)';
                }

                if (is_array($l)) {
                    foreach ($l as $ln) {
                        Log::info($ln);
                    }
                } else {
                    Log::info($l);
                }
            }
        }

        //result
        if (!is_scalar($result)) {
            $result = explode("\n", trim(print_r($result, true)));
        } elseif (strlen($result) > 256) {
            $result = substr($result, 0, 256) . '...(truncated)';
        }

        if (is_array($result)) {
            foreach ($result as $ln) {
                Log::debug($ln);
            }
        } else {
            Log::debug($result);
        }

        Log::info("Execute finished. [app:$app action:$action process id:$pid]");
    }

    public function __destruct()
    {
        if (!empty($this->pid_file) && file_exists($this->pid_file)) {
            if (!unlink($this->pid_file)) {
                Log::warning("Could not delete pid file " . $this->pid_file);
            }
        }
    }

    protected function formatApp($app)
    {
        $arr = explode('.', $app);
        foreach ($arr as $key => $word) {
            $word = ucfirst(strtolower($word));
            $arr[$key] = $word;
        }
        $classname = implode('_', $arr) . 'App';
        return $classname;
    }

}
