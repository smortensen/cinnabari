<?php

namespace Datto\Cinnabari\Tests\Request\Resolver;

use Datto\Cinnabari\Request\Resolver\Option;
use PHPUnit_Framework_TestCase;

class OptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $input
     * @param boolean $expectedOutput
     *
     * @dataProvider providerIsAbstract
     */
    public function testIsAbstract($input, $expectedOutput)
    {
        $actualOutput = Option::isAbstract($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }

    public function providerIsAbstract()
    {
        return array(
            0 => array(0, false),
            1 => array(1, false),
            2 => array('x', false),
            3 => array('$x', true),
            4 => array(array(6, 'People'), false),
            5 => array(array(6, '$y'), true)
        );
    }

    /**
     * @param array $aInput
     * @param array $bInput
     * @param boolean $resultExpected
     * @param array $aExpected
     *
     * @dataProvider providerMerge
     */
    public function testMerge(array $aInput, array $bInput, $resultExpected, array $aExpected = null)
    {
        $resultActual = Option::merge($aInput, $bInput);

        if ($resultExpected) {
            $expected = json_encode(array($resultExpected, $aExpected));
            $actual = json_encode(array($resultActual, $aInput));
        } else {
            $expected = false;
            $actual = $resultActual;
        }

        $this->assertSame($expected, $actual);
    }

    public function providerMerge()
    {
        return array(
            0 => array(array(), array('x' => 'X'), true, array('x' => 'X')),
            1 => array(array('x' => 'X'), array('x' => 'X'), true, array('x' => 'X')),
            2 => array(array('x' => 'Y'), array('x' => 'X'), false),
            3 => array(array('x' => 'X'), array('y' => 'Y'), true, array('x' => 'X', 'y' => 'Y')),
            4 => array(array('x' => array(1, 2)), array('x' => array(2, 1)), false),
            5 => array(array('x' => array('a' => 'A', 'b' => 'B')), array('x' => array('A', 'B')), false),
            6 => array(array('x' => 'X'), array('x' => array('X')), false)
        );
    }

    /**
     * @param array $values
     * @param mixed $key
     * @param mixed $value
     * @param boolean $expectedResult
     * @param array $expectedValues
     *
     * @dataProvider providerRestrict
     */
    public function testRestrict(array $values, $key, $value, $expectedResult, array $expectedValues = null)
    {
        $actualResult = Option::restrict($values, $key, $value);

        if ($expectedResult) {
            $expected = json_encode(array($expectedResult, $expectedValues));
            $actual = json_encode(array($actualResult, $values));
        } else {
            $expected = false;
            $actual = $actualResult;
        }

        $this->assertSame($expected, $actual);
    }

    public function providerRestrict()
    {
        return array(
            0 => array(array('x' => 1), 'x', 2, false),
            1 => array(array('x' => 1), 'x', 1, true, array()),
            2 => array(array('x' => 'X', 'y' => 'Y'), 'y', 'Y', true, array('x' => 'X')),
            3 => array(array('x' => '$a', 'y' => '$a'), 'x', 1, true, array('y' => 1)),
            4 => array(array('x' => '$a', 'y' => array('$a')), 'y', array('Y'), true, array('x' => 'Y')),
            5 => array(array('x' => array('a' => 'A')), 'x', array('A'), false),
            6 => array(array('x' => array('A', 'B')), 'x', array('B', 'A'), false)
        );
    }
}
