<?php
/**
 * @author miaokuan
 */

namespace Sim;

class Action extends \Yaf_Controller_Abstract
{
    public function json($data)
    {
        Yaf_Dispatcher::getInstance()->disableView();

        $callback = $this->getRequest()->get('callback');
        $callback = preg_replace('/[^a-z0-9_]/i','',$callback);

        //jsonp
        if ('' != $callback) {
            header('Content-Type: application/javascript');
            echo $callback . '(' . json_encode($data) . ');';

            return true;
        }

        //json
        header('Content-type: application/json');
        echo json_encode($data);
        return true;
    }
}
