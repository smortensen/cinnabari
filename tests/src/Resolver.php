<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Parser;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testGetPeopleId()
    {
        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'id')
        ));

        $output = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, 'people', array(array('Person'))),
            array(Parser::TYPE_PROPERTY, 'id', array(Resolver::VALUE_INTEGER))
        ), array(array('??')));

        $this->verify($input, $output);
    }

    private function verify($schema, $input, $expectedOutput)
    {
        $resolver = new Resolver($schema);
        $actualOutput = $resolver->resolve($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
