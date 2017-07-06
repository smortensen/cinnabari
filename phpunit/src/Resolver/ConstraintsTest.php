<?php

namespace Datto\Cinnabari\Tests\Request\Resolver;

use Datto\Cinnabari\Request\Resolver\Constraints;
use PHPUnit_Framework_TestCase;

class ConstraintsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerGetKey
     */
    public function testGetKey()
    {
        $tests = func_get_args();

        $constraints = new Constraints();

        foreach ($tests as $test) {
            list($constraint, $expectedKey) = $test;
            $actualKey = $constraints->getKey($constraint);

            $this->assertSame($expectedKey, $actualKey);
        }
    }

    public function providerGetKey()
    {
        $constraintA = array(array('a' => 1));
        $constraintB = array(array('b' => 1), array('b' => 2));

        return array(
            0 => array(array($constraintA, 0)),
            1 => array(array($constraintA, 0), array($constraintA, 0)),
            2 => array(array($constraintA, 0), array($constraintB, 1), array($constraintA, 0)),
            3 => array(array($constraintA, 0), array($constraintB, 1), array($constraintB, 1))
        );
    }

    // TODO
}
