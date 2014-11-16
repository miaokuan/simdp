<?php
/**
 * @author miaokuan
 */


class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        $routes = Yaf_Application::app()->getConfig()->routes;
        Yaf_Dispatcher::getInstance()->getRouter()->addConfig($routes);
    }
}
