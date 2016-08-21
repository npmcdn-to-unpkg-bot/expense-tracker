<?php

namespace ExpenseTracker;

use ExpenseTracker\Http\Request;

class Dispatcher
{

    const DEFAULT_CONTROLLER_NAMESPACE = '\\';

    protected $_method;
    protected $_controller_namespace;
    protected $_controller_name = 'home';
    protected $_action_name = 'index';
    protected $_params = [];
    /** @var Di $_di */
    private $_di;

    public function __construct(Di $di)
    {
        $this->_di = $di;
        $this->_controller_namespace = self::DEFAULT_CONTROLLER_NAMESPACE;
    }

    /**
     * @param string $controller_namespace
     */
    public function set_controller_namespace($controller_namespace)
    {
        $this->_controller_namespace = $controller_namespace;
    }


    public function get_method()
    {
        return $this->_method;
    }

    public function get_controller()
    {
        return $this->_controller_name;
    }

    public function get_action()
    {
        return $this->_action_name;
    }

    public function get_param($name)
    {
        if (empty($this->_params[$name])) {
            return null;
        }
        return $this->_params[$name];
    }

    /**
     * @return array
     */
    public function get_params()
    {
        return $this->_params;
    }

    public function handle(Request $request)
    {
        /** @var Router $router */
        $router = $this->_di->get('router');
        $matched_route = $router->handle($request);
        if ($matched_route !== false) {
            $controller_class_name = $this->_controller_namespace . '\\' . $matched_route->get_controller_name();
            $this->_params = $matched_route->get_params();
            $this->_controller_name = $matched_route->get_controller_name();
            $this->_action_name = $matched_route->get_action_name();
            $this->_method = $matched_route->get_method();
            $controllerHandler = new $controller_class_name($this->_di);
            $controllerHandler->{$router->get_action_name()}();
        }

        $view = $this->_di->get('view');
        $view->set_template([
            'controller' => $matched_route->get_controller_name(),
            'action' => $matched_route->get_action_name()
        ]);

        $view->render();
    }

}