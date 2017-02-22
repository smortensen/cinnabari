<?php

namespace Datto\Cinnabari\Tests\Request\Resolver;

use Datto\Cinnabari\Request\Resolver\Resolution;
use PHPUnit_Framework_TestCase;

class ResolutionTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $a = new Resolution(array('$x', '$y'));

        $this->verify($a, array('$x', '$y'));
    }

    public function testXMergeY()
    {
        $a = new Resolution(array('$x'));
        $b = new Resolution(array('$y'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('$0'));
    }

    public function testXMergeYX()
    {
        $a = new Resolution(array('$x'));
        $b = new Resolution(array('$y', '$x'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('$0', '$1'));
    }

    public function testXYMergeYX()
    {
        $a = new Resolution(array('$x', '$y'));
        $b = new Resolution(array('$y', '$x'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('$0', '$1'));
    }

    public function testXYMergeZZ()
    {
        $a = new Resolution(array('$x', '$y'));
        $b = new Resolution(array('$z', '$z'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('$0', '$0'));
    }

    public function testZZMergeXYZ()
    {
        $a = new Resolution(array('$z', '$z'));
        $b = new Resolution(array('$x', '$y', '$z'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('$0', '$0', '$1'));
    }

    public function testXMergeA()
    {
        $a = new Resolution(array('$x'));
        $b = new Resolution(array('A'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testXMergeInteger()
    {
        $a = new Resolution(array('$x'));
        $b = new Resolution(array(1));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array(1));
    }

    public function testAMergeX()
    {
        $a = new Resolution(array('A'));
        $b = new Resolution(array('$x'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testAMergeA()
    {
        $a = new Resolution(array('A'));
        $b = new Resolution(array('A'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A'));
    }

    public function testAMergeB()
    {
        $a = new Resolution(array('A'));
        $b = new Resolution(array('B'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, null);
    }

    public function testXYXMergeA()
    {
        $a = new Resolution(array('$x', '$y', '$x'));
        $b = new Resolution(array('A'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A', '$0', 'A'));
    }

    public function testAMergeXYX()
    {
        $a = new Resolution(array('A'));
        $b = new Resolution(array('$x', '$y', '$x'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A', '$0', 'A'));
    }

    public function testXYXMergeZZA()
    {
        $a = new Resolution(array('$x', '$y', '$x'));
        $b = new Resolution(array('$z', '$z', 'A'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array('A', 'A', 'A'));
    }

    public function testXMergePerson()
    {
        $a = new Resolution(array(array(6, 'Person')));
        $b = new Resolution(array(array(6, 'Person')));
        $c = Resolution::merge($a, $b);

        $this->verify($c, array(array(6, 'Person')));
    }

    public function testPersonMergeCrustacean()
    {
        $a = new Resolution(array(array(6, 'Person')));
        $b = new Resolution(array(array(6, 'Crustacean')));
        $c = Resolution::merge($a, $b);

        $this->verify($c, null);
    }

    public function testPersonMerge()
    {
        $a = new Resolution(array(array(6, 'Person')));
        $b = new Resolution(array(array()));
        $c = Resolution::merge($a, $b);

        $this->verify($c, null);
    }

    public function testPersonMergeA()
    {
        $a = new Resolution(array(array(6, 'Person')));
        $b = new Resolution(array('A'));
        $c = Resolution::merge($a, $b);

        $this->verify($c, null);
    }

    private function verify($subject, $expected)
    {
        if ($subject === null) {
            $actual = null;
        } else {
            /** @var Resolution $subject */
            $actual = $subject->getValues();
        }

        $expectedJson = json_encode($expected);
        $actualJson = json_encode($actual);

        $this->assertSame($expectedJson, $actualJson);
    }
}
