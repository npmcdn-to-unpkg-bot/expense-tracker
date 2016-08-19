<?php

namespace Core;

class Dispatcher
{

    protected $_method;
    protected $_controller = 'home';
    protected $_action = 'index';
    protected $_params = [];

    public function set_method($method)
    {
        $this->_method = $method;
    }

    public function set_controller($controller)
    {
        $this->_controller = $controller;
    }

    public function set_action($action)
    {
        $this->_action = $action;
    }

    public function set_param($name, $value)
    {
        $this->_params[$name] = $value;
    }


    public function get_method()
    {
        return $this->_method;
    }

    public function get_controller()
    {
        return $this->_controller;
    }

    public function get_action()
    {
        return $this->_action;
    }

    public function get_param($name)
    {
        if (empty($this->_params[$name])) {
            return null;
        }
        return $this->_params[$name];
    }

}