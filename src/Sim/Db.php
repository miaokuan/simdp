<?php
/**
 * Mysqli
 * 支持事务嵌套，commit或rollback后自动恢复autocommit模式
 *
 * @author miaokuan
 */

namespace Sim;

use Sim\Config;
use Sim\Log;

class Db
{
    const T_NUM = 'n';
    const T_NUM2 = 'd';
    const T_STR = 's';
    const T_RAW = 'S';
    const T_RAW2 = 'r';
    const V_ESC = '%';

    const FETCH_RAW = 0;
    const FETCH_NUM = 1;
    const FETCH_ASSOC = 2;
    const FETCH_OBJ = 3;

    const MAX_TIME = 600;

    protected $config;
    protected $isConnected = false;
    protected $retryErrno = array(
        2006, //MySQL server has gone away
        2013, //Lost connection to MySQL
        2003, //Can’t connect to MySQL server
    );

    protected $mysqli;

    protected $transaction = 0;
    protected $errno = 0;
    protected $error = '';

    protected $lastSql = '';
    protected $lastCost = 0;
    protected $totalCost = 0;

    static $pool = array();

    public static function instance($database)
    {
        if (empty(self::$pool[$database])) {
            $config = Config::load('db');
            $config['database'] = $database;
            self::$pool[$database] = new self($config);
        }

        return self::$pool[$database];
    }

    protected function __construct($config)
    {
        $this->config = $config;
        $mysqli = mysqli_init();
        if (!$mysqli) {
            Log::fatal('db error. [mysqli_init failed]');
        }
        $this->mysqli = $mysqli;
    }

    protected function connect($force = false)
    {
        if ($force || !$this->isConnected) {
            $config = $this->config;
            $port = empty($config['port']) ? 3306 : $config['port'];
            $charset = empty($config['charset']) ? 'utf8' : $config['charset'];
            $socket = null;
            $flag = null;
            if (isset($config['flag'])) {
                $flag = $config['flag'];
            }

            $this->isConnected = $this->mysqli->real_connect($config['host'],
                $config['user'], $config['password'], $config['database'], $port, $socket, $flag);

            if (!$this->isConnected) {
                Log::fatal('db connect error. [errno:' . mysqli_connect_errno() .
                    ' error:' . mysqli_connect_error() . ']');
            } else {
                $this->mysqli->set_charset($charset);
            }
        }

        return $this->isConnected;
    }

    public function charset($charset)
    {
        $this->connect();
        return $this->mysqli->set_charset($charset);
    }

    public function options($option, $value)
    {
        $this->connect();
        return $this->mysqli->options($option, $value);
    }

    /**
     * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a MySQLi_Result object. For other successful queries mysqli_query() will return TRUE.
     */
    public function query($sql)
    {
        $this->lastSql = $sql;

        $begin = intval(microtime(true) * 1000000);
        $this->connect();
        $res = $this->mysqli->query($sql);

        #reconnect max times 3
        for ($i = 0; $i < 3; $i++) {
            if (in_array($this->mysqli->errno, $this->retryErrno)) {
                Log::warning("db reconnect. [errno:" . $this->mysqli->errno . " error:"
                    . $this->mysqli->error . " sql:$sql]");
                usleep(100000);
                $this->connect(true);
                $res = $this->mysqli->query($sql);
            } else {
                break;
            }
        }

        if (false === $res) {
            Log::warning("db error. [errno:" . $this->mysqli->errno . " error:"
                . $this->mysqli->error . " sql: $sql]");
        }

        $this->lastCost = intval(microtime(true) * 1000000) - $begin;
        $this->totalCost += $this->lastCost;

        Log::debug('query success. [cost: '.$this->lastCost.'us] [sql: ' . $sql . ']');

        return $res;
    }

    public function select($table, $fields = '*', array $conds = null, $options = null, $appends = null, $fetchMode = self::FETCH_ASSOC)
    {
        $this->connect();

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $where = $this->where($conds);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        if (is_array($appends)) {
            $appends = implode(' ', $appends);
        }
        $sql = "SELECT $options $fields FROM $table $where $appends";
        $res = $this->query($sql, $fetchMode);
        if (is_bool($res)) {
            return $res;
        }

        switch ($fetchMode) {
            case self::FETCH_ASSOC:
                $rows = array();
                while ($row = $res->fetch_assoc()) {
                    $rows[] = $row;
                }
                $res->free();
                return $rows;
            case self::FETCH_NUM:
                $rows = array();
                while ($row = $res->fetch_row()) {
                    $rows[] = $row;
                }
                $res->free();
                return $rows;
            case self::FETCH_OBJ:
                $rows = array();
                while ($obj = $res->fetch_object()) {
                    $rows[] = $obj;
                }
                $res->free();
                return $rows;
            default:
                return $res;
        }
    }

    public function selectRow($table, $fields = '*', array $conds = null, $options = null, $appends = null, $fetchMode = self::FETCH_ASSOC)
    {
        $this->connect();

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $where = $this->where($conds);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        if (is_array($appends)) {
            $appends = implode(' ', $appends);
        }
        $sql = "SELECT $options $fields FROM $table $where $appends LIMIT 1";
        $res = $this->query($sql);
        if (is_bool($res)) {
            return $res;
        }

        switch ($fetchMode) {
            case self::FETCH_ASSOC:
                $row = $res->fetch_assoc();
                $res->free();
                return $row;
            case self::FETCH_NUM:
                $row = $res->fetch_row();
                $res->free();
                return $row;
            case self::FETCH_OBJ:
                $row = $res->fetch_object();
                $res->free();
                return $row;
            default:
                return $res;
        }
    }

    public function update($table, array $bind, array $conds = null, $options = null, $appends = null)
    {
        $this->connect();

        $set = $this->set($bind);
        $where = $this->where($conds);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        if (is_array($appends)) {
            $appends = implode(' ', $appends);
        }
        $sql = "UPDATE $options $table SET $set $where $appends";
        return $this->query($sql);
    }

    public function insert($table, array $bind, $options = null, $ondup = null)
    {
        $this->connect();

        $set = $this->set($bind);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        $sql = "INSERT $options $table SET $set";
        if (null !== $ondup) {
            $ondup = $this->set($ondup);
            $sql .= " ON DUPLICATE KEY UPDATE $ondup";
        }
        return $this->query($sql);
    }

    public function replace($table, array $bind, $options = null)
    {
        $this->connect();

        $set = $this->set($bind);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        $sql = "REPLACE $options $table SET $set";
        return $this->query($sql);
    }

    public function delete($table, array $conds = null, $options = null, $appends = null)
    {
        $this->connect();

        $where = $this->where($conds);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        if (is_array($appends)) {
            $appends = implode(' ', $appends);
        }
        $sql = "DELETE $options FROM $table $where $appends";
        return $this->query($sql);
    }

    public function selectField($table, $field, array $conds = null, $options = null, $appends = null)
    {
        $this->connect();

        $where = $this->where($conds);
        if (is_array($options)) {
            $options = implode(' ', $options);
        }
        if (is_array($appends)) {
            $appends = implode(' ', $appends);
        }
        $sql = "SELECT $options $field FROM $table $where $appends LIMIT 1";
        $res = $this->query($sql);
        if (is_bool($res)) {
            return $res;
        }

        $row = $res->fetch_row();
        $res->free();
        if (null !== $row) {
            return $row[0];
        }
        return null;
    }

    public function count($table, array $conds = null)
    {
        $count = $this->selectField($table, "count(*)", $conds);
        return $count;
    }

    public function begin()
    {
        $this->connect();
        $begin = false;
        if (0 === $this->transaction) {
            $begin = $this->mysqli->autocommit(false);
        }
        $this->transaction++;
        return $begin;
    }

    public function commit()
    {
        $this->connect();
        $commit = false;
        if (1 === $this->transaction) {
            $this->mysqli->commit();
            $commit = $this->mysqli->autocommit(true);
        }
        $this->transaction--;
        return $commit;
    }

    public function rollback()
    {
        $this->connect();
        $rollback = false;
        if (1 === $this->transaction) {
            $this->mysqli->rollback();
            $rollback = $this->mysqli->autocommit(true);
        }
        $this->transaction--;
        return $rollback;
    }

    public function __get($name)
    {
        $name = strtolower($name);

        switch ($name) {
            case 'sql':
            case 'lastsql':
                return $this->lastSql;
            case 'cost':
            case 'lastcost':
                return $this->lastCost;
            case 'totalcost':
            case 'total':
                return $this->totalCost;
            case 'error':
                return $this->mysqli->error;
            case 'errno':
                return $this->mysqli->errno;
            case 'insertid':
            case 'insert_id':
                return $this->mysqli->insert_id;
            case 'affectrows':
            case 'affectedrows':
            case 'affect_rows':
            case 'affected_rows':
                return $this->mysqli->affected_rows;
            default:
                return null;
        }
    }

    public function affectRows()
    {
        return $this->mysqli->affected_rows;
    }

    public function insertId()
    {
        return $this->mysqli->insert_id;
    }

    public function errno()
    {
        return $this->mysqli->errno;
    }

    public function error()
    {
        return $this->mysqli->error;
    }

    public function lastSql()
    {
        return $this->lastSql;
    }

    public function sql()
    {
        return $this->lastSql;
    }

    public function cost()
    {
        return $this->lastCost;
    }

    public function lastCost()
    {
        return $this->lastCost;
    }

    public function totalCost()
    {
        return $this->totalCost;
    }

    protected function where(array $conds = null)
    {
        $where = '';
        if (!empty($conds)) {
            $arr = array();
            foreach ($conds as $key => $val) {
                if (is_int($key)) {
                    $arr[] = " $val ";
                } else {
                    $arr[] = ' ' . $key . $this->escape($val) . ' ';
                }
            }
            $where = ' WHERE ' . implode(' AND ', $arr);
        }

        return $where;
    }

    protected function set(array $bind)
    {
        $arr = array();
        foreach ($bind as $key => $val) {
            if (is_int($key)) {
                $arr[] = $val;
            } elseif (is_string($key)) {
                $arr[] = '`' . $key . '`' . '=' . $this->escape($val);
            }
        }
        return implode(',', $arr);
    }

    public function escape($value)
    {
        $this->connect();

        if (null === $value) {
            return "''";
        }

        // if (is_int($value)) {
        //     return $value;
        // }

        return "'" . $this->mysqli->real_escape_string($value) . "'";
    }

}
