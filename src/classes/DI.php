<?php

namespace ExpenseTracker;

class Di
{
    private $_services = [];

    public function set($name, $service)
    {
        if (!empty($this->_services[$name])) {
            throw new \LogicException('Service ' . $name . ' is already registered in Di');
        }
        $this->_services[$name] = $service;
    }

    public function get($name)
    {
        if (empty($this->_services[$name])) {
            throw new \LogicException('Service ' . $name . ' not found in Di');
        }
        if (is_callable($this->_services[$name])) {
            $result = call_user_func($this->_services[$name]);
            $this->_services[$name] = $result;
            return $this->_services[$name];
        } else {
            return $this->_services[$name];
        }
    }
}