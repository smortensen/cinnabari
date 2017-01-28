<?php

namespace Datto\Cinnabari\Tests\Request\Resolver;

use Datto\Cinnabari\Request\Resolver\Possibility;
use PHPUnit_Framework_TestCase;

class PossibilityTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $a = new Possibility(array('$x', '$y'));

        $this->verify($a, array('$x', '$y'));
    }

    public function testXMergeY()
    {
        $a = new Possibility(array('$x'));
        $b = new Possibility(array('$y'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('$0'));
    }

    public function testXMergeYX()
    {
        $a = new Possibility(array('$x'));
        $b = new Possibility(array('$y', '$x'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('$0', '$1'));
    }

    public function testXYMergeYX()
    {
        $a = new Possibility(array('$x', '$y'));
        $b = new Possibility(array('$y', '$x'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('$0', '$1'));
    }

    public function testXYMergeZZ()
    {
        $a = new Possibility(array('$x', '$y'));
        $b = new Possibility(array('$z', '$z'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('$0', '$0'));
    }

    public function testZZMergeXYZ()
    {
        $a = new Possibility(array('$z', '$z'));
        $b = new Possibility(array('$x', '$y', '$z'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('$0', '$0', '$1'));
    }

    public function testXMergeA()
    {
        $a = new Possibility(array('$x'));
        $b = new Possibility(array('A'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testAMergeX()
    {
        $a = new Possibility(array('A'));
        $b = new Possibility(array('$x'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testAMergeA()
    {
        $a = new Possibility(array('A'));
        $b = new Possibility(array('A'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testAMergeB()
    {
        $a = new Possibility(array('A'));
        $b = new Possibility(array('B'));
        $c = Possibility::merge($a, $b);

        $this->assertSame(null, $c);
    }

    public function testXYXMergeA()
    {
        $a = new Possibility(array('$x', '$y', '$x'));
        $b = new Possibility(array('A'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('A', '$0', 'A'));
    }

    public function testAMergeXYX()
    {
        $a = new Possibility(array('A'));
        $b = new Possibility(array('$x', '$y', '$x'));
        $c = Possibility::merge($a, $b);

        $this->verify($c, array('A', '$0', 'A'));
    }

    private function verify(Possibility $subject, $expected)
    {
        $actual = $subject->getValues();

        $expectedJson = json_encode($expected);
        $actualJson = json_encode($actual);

        $this->assertSame($expectedJson, $actualJson);
    }
}
