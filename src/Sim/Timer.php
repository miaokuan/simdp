<?php
/**
 * @author miaokuan
 */

namespace Sim;

class Timer
{
    static $_pool = array();

    static $_elapsed = array();

    public static function start($key)
    {
        self::$_pool[$key]['finish'] = self::$_pool[$key]['start'] = gettimeofday();
    }

    public static function end($key)
    {
        self::finish($key);
    }

    public static function finish($key)
    {
        self::$_pool[$key]['finish'] = gettimeofday();
    }

    public static function cal($key = null)
    {
        if (null === $key) {
            return self::_calAll();
        }

        return self::_calOne($key);
    }

    public static function _calAll()
    {
        if (empty(self::$_pool)) {
            return array();
        }
        foreach (self::$_pool as $key => $time) {
            self::$_elapsed[$key] = ($time['finish']['sec']-$time['start']['sec']) * 1000 * 1000 + $time['finish']['usec']-$time['start']['usec'];
        }

        return self::$_elapsed;
    }

    public static function _calOne($key)
    {
        if (isset(self::$_pool[$key])) {
            if (!isset(self::$_elapsed[$key])) {
                self::$_elapsed[$key] = (self::$_pool[$key]['finish']['sec']-self::$_pool[$key]['start']['sec']) * 1000 * 1000 + self::$_pool[$key]['finish']['usec']-self::$_pool[$key]['start']['usec'];
            }
            return self::$_elapsed[$key];
        }

        return 0;
    }

}
