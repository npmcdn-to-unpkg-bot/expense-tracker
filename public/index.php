<?php

use Core\DI;

class Spendings
{

    public static function main()
    {
        $request = new \ExpenseTracker\Http\Request();
        DI::set('request', $request);
        $dispatcher = new \Core\Dispatcher();
        DI::set('dispatcher', $dispatcher);

        $view = new \Core\View();
        DI::set('view', $view);

        $router = new \ExpenseTracker\Router();
        $router->set_default_controller_name('home');
        $router->add('GET', '/add/{category_id:\d+}/', [
            'controller' => 'add',
            'action' => 'index'
        ])->add('GET', '/stats/{category_id:\d+}/', [
            'controller' => 'stats',
            'action' => 'index'
        ])->add('GET', '/stats/{category_id:\d+}/{year:\d+}/{month:\d+}/', [
            'controller' => 'stats',
            'action' => 'index'
        ])->add('GET', '/expense/{category_id:\d+}/', [
            'controller' => 'expense',
            'action' => 'index'
        ]);
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

define('SYSTEM_DOC_ROOT', __DIR__ . '/..');
include_once SYSTEM_DOC_ROOT . '/conf/system.php';
require_once SYSTEM_DOC_ROOT . '/vendor/autoload.php';

try {
    Spendings::main();
} catch (\RuntimeException $e) {
    echo $e->getMessage();
}