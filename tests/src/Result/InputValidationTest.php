<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Parser;
use Datto\Cinnabari\Result\InputValidation;
use PHPUnit_Framework_TestCase;

class InputValidationTest extends PHPUnit_Framework_TestCase
{
    public function testParameterNull()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', Types::TYPE_NULL);

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && ($input['x'] === null))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testParameterBoolean()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', Types::TYPE_BOOLEAN);

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && is_bool($input['x']))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testParameterInteger()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', Types::TYPE_INTEGER);

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && is_integer($input['x']))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testParameterFloat()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', Types::TYPE_FLOAT);

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && is_float($input['x']))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testParameterString()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', Types::TYPE_STRING);

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && is_string($input['x']))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testParameterNullIntegerFloat()
    {
        $input = array(Parser::TYPE_PARAMETER, 'x', array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT));

        $output = <<<'EOS'
if (!(array_key_exists('x', $input) && (($input['x'] === null) || is_integer($input['x']) || is_float($input['x'])))) {
    throw new Exception('x', 1);
}
EOS;

        $this->verify($input, $output);
    }

    public function testGetFilter()
    {
        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_FUNCTION, 'filter', array(
                array(Parser::TYPE_PROPERTY, array('people'), array(Types::TYPE_OBJECT, 'Person')),
                array(Parser::TYPE_FUNCTION, 'and', array(
                    array(Parser::TYPE_FUNCTION, 'equal', array(
                        array(Parser::TYPE_PROPERTY, array('name'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING)),
                        array(Parser::TYPE_PARAMETER, 'name',  array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING))
                    ), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN)),
                    array(Parser::TYPE_FUNCTION, 'less', array(
                        array(Parser::TYPE_PROPERTY, array('age'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER)),
                        array(Parser::TYPE_PARAMETER, 'age', array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER))
                    ), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN))
                ), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN))
            ), array(Types::TYPE_OBJECT, 'Person')),
            array(Parser::TYPE_OBJECT, array(
                'name' => array(Parser::TYPE_PROPERTY, array('name'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING)),
                'age' => array(Parser::TYPE_PROPERTY, array('age'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING))
            ), array(Types::TYPE_OBJECT, 'anonymous'))
        ), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'anonymous')));

        $output = <<<'EOS'
if (!(array_key_exists('name', $input) && (($input['name'] === null) || is_string($input['name'])))) {
    throw new Exception('name', 1);
}

if (!(array_key_exists('age', $input) && (($input['age'] === null) || is_integer($input['age'])))) {
    throw new Exception('age', 1);
}
EOS;

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $validator = new InputValidation();
        $actualOutput = $validator->getPhp($input);

        $this->assertSame(
            self::standardizePhp($expectedOutput),
            self::standardizePhp($actualOutput)
        );
    }

    private static function standardizePhp($php)
    {
        return preg_replace('~\s+~', ' ', trim($php));
    }
}
