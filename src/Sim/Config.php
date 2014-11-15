<?php
/**
 * @author miaokuan
 */

namespace Sim;

class Config
{
    static function load($name, $piece = null)
    {
        $name = preg_replace('#[^a-z0-9A-Z_-]#', '', $name);
        $config = include (SIM_ROOT . "/var/config/$name.php");

        if (null !== $piece && isset($config[$piece])) {
            return $config[$piece];
        }

        return $config;
    }

}
