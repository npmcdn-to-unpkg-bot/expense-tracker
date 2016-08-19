<?php

namespace Core;

class DI
{
    protected static $_services = [];

    public static function set($name, $service)
    {
        if (!empty(self::$_services[$name])) {
            throw new \LogicException('Service ' . $name . ' is already registered in DI');
        }
        self::$_services[$name] = $service;
    }

    public static function get($name)
    {
        if (empty(self::$_services[$name])) {
            throw new \LogicException('Service ' . $name . ' not found in DI');
        }
        if (is_callable(self::$_services[$name])) {
            return call_user_func(self::$_services[$name]);
        } else {
            return self::$_services[$name];
        }
    }
}