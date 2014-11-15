<?php
/**
 * @author miaokuan
 */

namespace Sim;

class Log
{
    /**
     * Log levels can be enabled
     */
    const FATAL = 100;
    const WARNING = 200;
    const NOTICE = 300;
    const INFO = 400;
    const DEBUG = 500;

    static $instance = null;
    protected $logfile;
    protected $syslog = false;

    /**
     * Verbosity level for the running script.
     */
    protected $level = 200;

    protected function log($message, $level = self::INFO)
    {
        if ($this->syslog) {
            $this->_syslog($message, $level);
            return;
        }

        switch ($level) {
            case self::DEBUG:
                $label = 'DEBUG  ';
                break;
            case self::INFO:
                $label = 'INFO   ';
                break;
            case self::NOTICE:
                $label = 'NOTICE ';
                break;
            case self::WARNING:
                $label = 'WARNING';
                break;
            case self::FATAL:
                $label = 'FATAL  ';
                break;
        }

        list($ts, $ms) = explode('.', sprintf("%f", microtime(true)));
        $ds = date('Y-m-d H:i:s') . '.' . str_pad($ms, 6, 0);
        $prefix = "[$ds] $label";
        $log = $prefix . ' ' . str_replace("\n", "\n$prefix ", trim($message)) . "\n";

        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            echo $log;
        }

        if ($level > $this->level) {
            return;
        }

        if ($this->logfile) {
            $file = $this->logfile;
        } else {
            $file = SIM_ROOT . '/var/log/sim.log';
        }

        if ($level <= self::WARNING) {
            $file .= '.wf';
        }

        file_put_contents($file, $log, FILE_APPEND);
    }

    protected function _syslog($message, $level)
    {
        switch ($level) {
            case self::DEBUG:
                $priority = DEBUG;
                break;
            case self::INFO:
                $priority = INFO;
                break;
            case self::NOTICE:
                $priority = NOTICE;
                break;
            case self::WARNING:
                $priority = WARNING;
                break;
            case self::FATAL:
                $priority = ERR;
                break;
        }

        syslog($priority, $message);
    }

    public static function debug($message)
    {
        self::instance()->log($message, self::DEBUG);
    }

    public static function info($message)
    {
        self::instance()->log($message, self::INFO);
    }

    public static function notice($message)
    {
        self::instance()->log($message, self::NOTICE);
    }

    public static function warning($message)
    {
        self::instance()->log($message, self::WARNING);
    }

    public static function fatal($message)
    {
        self::instance()->log($message, self::FATAL);
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __clone()
    {}

    public static function level($level = null)
    {
        if (null === $level) {
            return self::instance()->level;
        } else {
            self::instance()->level = intval($level);
        }
    }

    public static function syslog($syslog = true)
    {
        self::instance()->syslog = $syslog ? true : false;
    }

    public static function logfile($logfile)
    {
        self::instance()->logfile = $logfile;
    }

}
