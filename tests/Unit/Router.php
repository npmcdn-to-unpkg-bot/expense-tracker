<?php

namespace Tests\Unit;

use ExpenseTracker\Http\Request;
use ExpenseTracker\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    public function testSimpleRoute()
    {
        $_SERVER = [
            'REQUEST_URI' => '/some/test/uri',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->add(Request::GET, '/some/test/uri', [
            'controller' => 'home',
            'action' => 'index'
        ]);
        $router->handle($request);
        $this->assertTrue(
            $router->get_controller_name() == 'home',
            'Expected "home" and got ' . $router->get_controller_name()
        );
        $this->assertTrue(
            $router->get_action_name() == 'index',
            'Expected "index" and got ' . $router->get_action_name()
        );
    }

    public function testDefaultRootRoute()
    {
        $_SERVER = [
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->set_default_controller_name('testController');
        $router->set_default_action_name('testAction');
        $router->handle($request);
        $this->assertTrue(
            $router->get_controller_name() == 'testController',
            'Expected "testController" and got ' . $router->get_controller_name()
        );
        $this->assertTrue(
            $router->get_action_name() == 'testAction',
            'Expected "testAction" and got ' . $router->get_action_name()
        );
    }


    public function testDefaultOnePartRoute()
    {
        $_SERVER = [
            'REQUEST_URI' => '/test',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->handle($request);
        $this->assertTrue(
            $router->get_controller_name() == 'test',
            'Expected "test" and got ' . $router->get_controller_name()
        );
        $this->assertTrue(
            $router->get_action_name() == 'index',
            'Expected "index" and got ' . $router->get_action_name()
        );
    }


    public function testDefaultTwoPartRoute()
    {
        $_SERVER = [
            'REQUEST_URI' => '/testController/testAction',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->handle($request);
        $this->assertTrue(
            $router->get_controller_name() == 'testController',
            'Expected "testController" and got ' . $router->get_controller_name()
        );
        $this->assertTrue(
            $router->get_action_name() == 'testAction',
            'Expected "testAction" and got ' . $router->get_action_name()
        );
    }


    public function testRegexWithParam()
    {
        $_SERVER = [
            'REQUEST_URI' => '/test/123',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->add(Request::GET, '/test/(\d+)', [
            'id' => 1
        ]);
        $router->handle($request);
        $this->assertTrue(
            $router->get_param('id') == '123',
            'Expected "123" and got ' . $router->get_param('id')
        );
    }


    public function testRegexWithParamInUri()
    {
        $_SERVER = [
            'REQUEST_URI' => '/test/123',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->add(Request::GET, '/test/{id:\d+}', [
            'id' => 1
        ]);
        $router->handle($request);
        $this->assertTrue(
            $router->get_param('id') == '123',
            'Expected "123" and got ' . $router->get_param('id')
        );
    }


    public function testRegexWithMixedTypeParams()
    {
        $_SERVER = [
            'REQUEST_URI' => '/test/123/qwe123/456',
            'REQUEST_METHOD' => Request::GET
        ];
        $request = new Request();
        $router = new Router();
        $router->add(Request::GET, '/test/(\d+)/{slug:\w+}/(\d+)', [
            'id1' => 1,
            'id2' => 2
        ]);
        $router->handle($request);
        $this->assertTrue(
            $router->get_param('id1') == '123',
            'Expected "123" and got ' . $router->get_param('id1')
        );
        $this->assertTrue(
            $router->get_param('id2') == '456',
            'Expected "456" and got ' . $router->get_param('id2')
        );
        $this->assertTrue(
            $router->get_param('slug') == 'qwe123',
            'Expected "qwe123" and got ' . $router->get_param('slug')
        );
    }

}