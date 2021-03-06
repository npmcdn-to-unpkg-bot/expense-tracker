<?php

use Core\DI;

class Spendings
{

    public static function main()
    {
        session_start();
        $dispatcher = new \Core\Dispatcher();
        DI::set('dispatcher', $dispatcher);

        $view = new \Core\View();
        DI::set('view', $view);

        $router = new \Core\Router();
        $router->set_url($_GET['uri']);
        $router->add(
            'GET',
            '/add/([\d]+)/',
            [
                'controller' => 'add',
                'action' => 'index',
                'category_id' => 1
            ]
        )->add(
            'GET',
            '/stats/([\d]+)/',
            [
                'controller' => 'stats',
                'action' => 'index',
                'category_id' => 1
            ]
        )->add(
            'GET',
            '/stats/([\d]+)/([\d]+)/([\d]+)/',
            [
                'controller' => 'stats',
                'action' => 'index',
                'category_id' => 1,
                'year' => 2,
                'month' => 3
            ]
        )->add(
            'GET',
            '/expense/([\d]+)/',
            [
                'controller' => 'expense',
                'action' => 'index',
                'category_id' => 1
            ]
        );
        DI::set('router', $router);

        $mysql = \Core\DB::getInstance();
        DI::set('mysql', $mysql);

        $app = new \Core\App();
        $app->run();
    }

}

if (!isset($_GET['uri']) || $_GET['uri'] == '') {
    $_GET['uri'] = '/';
}

ini_set("display_errors", "On");
ini_set('error_reporting', E_ALL & ~E_DEPRECATED);
ini_set("display_startup_errors", "On");

define('SYSTEM_DOC_ROOT', __DIR__ . '/../..');
include_once SYSTEM_DOC_ROOT . '/conf/system.php';
require_once SYSTEM_DOC_ROOT . '/vendor/autoload.php';

try {
    Spendings::main();
} catch (\RuntimeException $e) {
    echo $e->getMessage();
}