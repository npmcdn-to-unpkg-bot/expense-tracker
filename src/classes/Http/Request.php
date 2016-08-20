<?php

namespace ExpenseTracker\Http;

class Request
{

    const GET = 'GET';
    const POST = 'POST';

    private $_uri;
    private $_method;

    public function __construct()
    {
        $this->_method = $_SERVER['REQUEST_METHOD'];
        $this->_uri = $_SERVER['REQUEST_URI'];
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
    public function get_uri()
    {
        return $this->_uri;
    }

}