<?php
/**
 * @author miaokuan
 */

namespace App;

use Sim\Widget;

abstract class App extends Widget
{
    protected $params = array();

    public function __construct(array $params = null)
    {
        $this->params = $params;
        parent::__construct();
    }

    public function runAction(&$log)
    {}

    public function json($data)
    {
        echo json_encode($data);
    }
}
