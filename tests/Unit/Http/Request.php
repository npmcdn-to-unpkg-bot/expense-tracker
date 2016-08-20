<?php

namespace Tests\Unit\Http;

use ExpenseTracker\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    const REQUEST_URI = '/some/test/uri';
    const REQUEST_URI_MIXED_CASE = '/sOme/TEST/uRi';
    const REQUEST_METHOD = Request::GET;

    public function testConstructor()
    {
        $_SERVER = [
            'REQUEST_URI' => self::REQUEST_URI,
            'REQUEST_METHOD' => self::REQUEST_METHOD
        ];
        $request = new Request();

        $this->assertTrue(
            $request->get_method() == self::REQUEST_METHOD,
            'Expected ' . self::REQUEST_METHOD . ' and got ' . $request->get_method()
        );
        $this->assertTrue(
            $request->get_uri() == self::REQUEST_URI,
            'Expected ' . self::REQUEST_URI . ' and got ' . $request->get_uri()
        );
    }

}