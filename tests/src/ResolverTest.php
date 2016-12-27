<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Resolver\FunctionSignatures as Types;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'id')
        ));

        $output = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, 'people', array(Types::TYPE_ARRAY, Types::TYPE_OBJECT)),
            array(Parser::TYPE_PROPERTY, 'id', Types::TYPE_INTEGER)
        ), array(Types::TYPE_ARRAY, Types::TYPE_OBJECT));

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $resolver = new Resolver();
        $actualOutput = $resolver->resolve($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
