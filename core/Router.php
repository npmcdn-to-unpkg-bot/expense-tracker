<?php

namespace Core;

class Router
{

    private $_url;
    private $_routes = [];

    public function set_url($url)
    {
        $this->_url = $url;
    }

    public function add($method, $url, array $params)
    {
        $regex = str_replace('/', '\/', $url);
        if (!empty($this->_url[$regex])) {
            throw new \LogicException('URL ' . $url . ' already registered in router');
        }
        if (empty($params['controller'])) {
            throw new \InvalidArgumentException('Controller name not specified for url ' . $url);
        }
        if (empty($params['action'])) {
            throw new \InvalidArgumentException('Action name not specified for url ' . $url);
        }
        $data = [
            'method' => $method,
            'regex' => $regex,
            'controller' => $params['controller'],
            'action' => $params['action'],
            'params' => []
        ];
        foreach ($params as $key => $value) {
            if (!in_array($key, ['controller', 'action'])) {
                $data['params'][$value] = $key;
            }
        }
        $this->_routes[] = $data;
        return $this;
    }

    public function handle()
    {
        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = \Core\DI::get('dispatcher');
        $method = $_SERVER['REQUEST_METHOD'];
        $route_matched = false;
        if (count($this->_routes) > 0) {
            foreach ($this->_routes as $route) {
                if ($method == $route['method'] && preg_match('/^' . $route['regex'] . '$/i', $this->_url, $matches)) {
                    $route_matched = true;
                    $dispatcher->set_controller($route['controller']);
                    $dispatcher->set_action($route['action']);
                    if (count($route['params']) > 0) {
                        foreach ($route['params'] as $param_index => $param_name) {
                            $dispatcher->set_param($param_name, $matches[$param_index]);
                        }
                    }
                }
            }
        }
        if (!$route_matched) {
            switch (true) {
                case in_array($this->_url, ['/', '/index.php']):
                    // Default controller and action, do nothing
                    break;
                case preg_match('/^\/([a-zA-Z0-9-]+)\/$/', $this->_url, $matches):
                    $dispatcher->set_controller($matches[1]);
                    break;
                case preg_match('/^\/([a-zA-Z0-9-]+)\/([a-zA-Z0-9-]+)\/$/', $this->_url, $matches):
                    $dispatcher->set_controller($matches[1]);
                    $dispatcher->set_action($matches[2]);
                    break;
                default:
                    $dispatcher->set_controller('error');
                    $dispatcher->set_action('notFound');
            }
        }
    }

}