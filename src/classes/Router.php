<?php

namespace ExpenseTracker;

use Core\DI;
use ExpenseTracker\Http\Request;

class Router
{

    const DEFAULT_CONTROLLER_NAME = 'index';
    const DEFAULT_ACTION_NAME = 'index';

    private $_routes = [];
    private $_detected_controller_name = '';
    private $_detected_action_name = '';
    private $_detected_params = [];
    private $_default_controller_name;
    private $_default_action_name;

    public function __construct()
    {
        $this->_default_controller_name = self::DEFAULT_CONTROLLER_NAME;
        $this->_default_action_name = self::DEFAULT_ACTION_NAME;
    }

    /**
     * @return string
     */
    public function get_controller_name()
    {
        return $this->_detected_controller_name;
    }

    /**
     * @return string
     */
    public function get_action_name()
    {
        return $this->_detected_action_name;
    }

    /**
     * @return array
     */
    public function get_params()
    {
        return $this->_detected_params;
    }


    public function get_param($param_name)
    {
        return isset($this->_detected_params[$param_name])
            ? $this->_detected_params[$param_name]
            : null;
    }

    /**
     * @param string $default_controller_name
     */
    public function set_default_controller_name($default_controller_name)
    {
        $this->_default_controller_name = $default_controller_name;
    }

    /**
     * @param string $default_action_name
     */
    public function set_default_action_name($default_action_name)
    {
        $this->_default_action_name = $default_action_name;
    }


    public function add($method, $uri, array $params)
    {
        $unnamedParamIndex = 0;
        $uri = preg_replace_callback('#\(([^\)]+)\)#', function(&$input) use (&$unnamedParamIndex, $params) {
            return '{' . array_search(++$unnamedParamIndex, $params) . ':' . $input[1] . '}';
        }, $uri);
        if (!empty($this->_routes[$uri])) {
            throw new \LogicException('URL ' . $uri . ' already registered in router');
        }
        if (empty($params['controller'])) {
            $params['controller'] = $this->_default_controller_name;
        }
        if (empty($params['action'])) {
            $params['action'] = $this->_default_action_name;
        }
        $data = [
            'method' => strtolower($method),
            'uri' => $uri,
            'controller' => $params['controller'],
            'action' => $params['action']
        ];
        foreach ($params as $key => $value) {
            if (!in_array($key, ['controller', 'action'])) {
                $data['params'][$value] = $key;
            }
        }
        $this->_routes[] = $data;
        return $this;
    }


    public function handle(Request $request)
    {
        $method = strtolower($request->get_method());
        $uri = $request->get_uri();
        $route_matched = false;
        if (count($this->_routes) > 0) {
            foreach ($this->_routes as $route) {
                $params = [];
                $param_index = 0;
                $regex = preg_replace_callback('#\{(\w+):([^\}]+)\}#', function($input) use (&$params, &$param_index) {;
                    $params[$input[1]] = ++$param_index;
                    return '(' . $input[2] . ')';
                }, $route['uri']);
                unset($param_index);
                if ($method == $route['method'] && preg_match('#' . $regex . '#', $uri, $matches)) {
                    $route_matched = true;
                    $this->_detected_controller_name = $route['controller'];
                    $this->_detected_action_name = $route['action'];
                    if (count($params) > 0) {
                        foreach ($params as $param_name => $param_index) {
                            $this->_detected_params[$param_name] = $matches[$param_index];
                        }
                    }
                }
            }
        }
        if (!$route_matched) {
            switch (true) {
                case $uri == '/':
                    $this->_detected_controller_name = $this->_default_controller_name;
                    $this->_detected_action_name = $this->_default_action_name;
                    break;
                case preg_match('#^/([\w]+)/?$#', $uri, $matches):
                    $this->_detected_controller_name = $matches[1];
                    $this->_detected_action_name = $this->_default_action_name;
                    break;
                case preg_match('#^/([\w]+)/([\w]+)/?$#', $uri, $matches):
                    $this->_detected_controller_name = $matches[1];
                    $this->_detected_action_name = $matches[2];
                    break;
                default:
                    $this->_detected_controller_name = 'error';
                    $this->_detected_action_name = 'notFound';
            }
        }
    }

}