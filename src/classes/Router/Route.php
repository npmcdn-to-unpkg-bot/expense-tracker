<?php

namespace ExpenseTracker\Router;

class Route
{

    private $_method;
    private $_uri;
    private $_params = [];
    private $_controller_name;
    private $_action_name;

    /**
     * @return mixed
     */
    public function get_uri()
    {
        return $this->_uri;
    }

    /**
     * @return mixed
     */
    public function get_method()
    {
        return $this->_method;
    }

    /**
     * @return mixed
     */
    public function get_action_name()
    {
        return $this->_action_name;
    }

    /**
     * @return mixed
     */
    public function get_controller_name()
    {
        return $this->_controller_name;
    }

    /**
     * @return array
     */
    public function get_params()
    {
        return $this->_params;
    }

    /**
     * @param mixed $action_name
     */
    public function set_action_name($action_name)
    {
        $this->_action_name = $action_name;
    }

    /**
     * @param mixed $controller_name
     */
    public function set_controller_name($controller_name)
    {
        $this->_controller_name = $controller_name;
    }

    /**
     * @param mixed $method
     */
    public function set_method($method)
    {
        $this->_method = $method;
    }

    /**
     * @param array $params
     */
    public function set_params($params)
    {
        $this->_params = $params;
    }

    /**
     * @param mixed $uri
     */
    public function set_uri($uri)
    {
        $this->_uri = $uri;
    }

}