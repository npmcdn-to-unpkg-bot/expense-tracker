<?php


use ExpenseTracker\Di;
use ExpenseTracker\Dispatcher;
use ExpenseTracker\Http\Request;
use ExpenseTracker\Router;

class Spendings
{

    public static function main()
    {
        $di = new Di();
        $request = new Request();
        $di->set('request', $request);

        $view = new \Core\View();
        $di->set('view', $view);

        $router = new Router();
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
        $di->set('router', $router);


        $mysql = \Core\DB::getInstance();
        $di->set('mysql', $mysql);

        $dispatcher = new Dispatcher($di);
        $dispatcher->set_controller_namespace('\\ExpenseTracker\\Controller');
        $di->set('dispatcher', $dispatcher);

        $dispatcher->handle($request);

        echo $view->get_contents();
    }

}

if (!isset($_GET['uri']) || $_GET['uri'] == '') {
    $_GET['uri'] = '/';
}

ini_set("display_errors", "On");
ini_set('error_reporting', E_ALL);
ini_set("display_startup_errors", "On");

include_once __DIR__ . '/../conf/system.php';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    Spendings::main();
} catch (\RuntimeException $e) {
    echo $e->getMessage();
}