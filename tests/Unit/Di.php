<?php

namespace Tests\Unit\Http;

use ExpenseTracker\Di;
use ExpenseTracker\Http\Request;

class DiTest extends \PHPUnit_Framework_TestCase
{

    public function testAddDiResourse()
    {
        $testString = 'test string';
        $di = new Di();
        $di->set('resource', $testString);
        $result = $di->get('resource');
        $this->assertTrue(
            $testString === $result,
            'Expected ' . $testString . ' and got ' . $result
        );
    }


    public function testDiLazyLoadResourse()
    {
        $di = new Di();
        $di->set('resource', function() {
            return 'test string';
        });
        $result = $di->get('resource');
        $this->assertTrue(
            'test string' === $result,
            'Expected "test string" and got ' . $result
        );
    }


    public function testDiLazyLoadCallsFunctionOnlyOnceResourse()
    {
        $di = new Di();
        $di->set('resource', function() {
            return rand();
        });
        $result1 = $di->get('resource');
        $result2 = $di->get('resource');
        $this->assertTrue(
            $result1 === $result2,
            'Function in Di gets called more than once: ' . $result1 . ' != ' . $result2
        );
    }


    public function testDuplicateResourceThrowsException()
    {
        $this->expectException(\LogicException::class);
        $di = new Di();
        $di->set('resource', function() {
            return 'test string';
        });
        $di->set('resource', function() {
            return 'test string';
        });
    }


    public function testNonexistantResourceThrowsException()
    {
        $this->expectException(\LogicException::class);
        $di = new Di();
        $di->get('resource');
    }

}