<?php
/**
 * @author miaokuan
 */

namespace Sim;

use Sim\Db;

abstract class Widget
{
    public $database = '';
    public $db;

    public function __construct()
    {
        if ('' != $this->database) {
            $this->db = Db::instance($this->database);
        }
        $this->auto();
    }

    public function auto()
    {}

    public function min($table, $field = 'id')
    {
        $appends = "order by $field asc";
        $ret = $this->db->selectField($table, $field, null, null, $appends);
        return $ret;
    }

    public function max($table, $field = 'id')
    {
        $appends = "order by $field desc";
        $ret = $this->db->selectField($table, $field, null, null, $appends);
        return $ret;
    }

    public function hash($id)
    {
        return '';
    }

    /**
     * struct insert
     */
    public function in(array $fields, array $bind)
    {
        $insert = array();
        foreach ($fields as $field => $default) {
            $insert[$field] = (isset($bind[$field])) ? $bind[$field] : $default;
        }

        return $insert;
    }

    /**
     * struct update
     */
    public function up(array $fields, array $bind)
    {
        $update = array();
        foreach ($fields as $field => $default) {
            if (isset($bind[$field])) {
                $update[$field] = $bind[$field];
            }
        }

        return $update;
    }

}
