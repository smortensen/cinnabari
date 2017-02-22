<?php

namespace Datto\Cinnabari\Tests\Request;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Request\Language\Properties;
use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Parser;
use Datto\Cinnabari\Request\Resolver;
use Datto\Cinnabari\Tests\Request\Language\Functions;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testParameter()
    {
        $input = array(Parser::TYPE_PARAMETER, 'c');

        $exception = self::getUnresolvableTypeConstraintsException($input);

        $this->verifyException($input, $exception);
    }

    public function testNull()
    {
        $input = array(Parser::TYPE_PROPERTY, array('null'));

        $output = array(Parser::TYPE_PROPERTY, array('null'), Types::TYPE_NULL);

        $this->verify($input, $output);
    }

    public function testBoolean()
    {
        $input = array(Parser::TYPE_PROPERTY, array('boolean'));

        $output = array(Parser::TYPE_PROPERTY, array('boolean'), Types::TYPE_BOOLEAN);

        $this->verify($input, $output);
    }

    public function testInteger()
    {
        $input = array(Parser::TYPE_PROPERTY, array('integer'));

        $output = array(Parser::TYPE_PROPERTY, array('integer'), Types::TYPE_INTEGER);

        $this->verify($input, $output);
    }

    public function testFloat()
    {
        $input = array(Parser::TYPE_PROPERTY, array('float'));

        $output = array(Parser::TYPE_PROPERTY, array('float'), Types::TYPE_FLOAT);

        $this->verify($input, $output);
    }

    public function testString()
    {
        $input = array(Parser::TYPE_PROPERTY, array('string'));

        $output = array(Parser::TYPE_PROPERTY, array('string'), Types::TYPE_STRING);

        $this->verify($input, $output);
    }

    public function testNullBoolean()
    {
        $input = array(Parser::TYPE_PROPERTY, array('nullBoolean'));

        $output = array(Parser::TYPE_PROPERTY, array('nullBoolean'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN));

        $this->verify($input, $output);
    }

    public function testNullInteger()
    {
        $input = array(Parser::TYPE_PROPERTY, array('nullInteger'));

        $output = array(Parser::TYPE_PROPERTY, array('nullInteger'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER));

        $this->verify($input, $output);
    }

    public function testNullFloat()
    {
        $input = array(Parser::TYPE_PROPERTY, array('nullFloat'));

        $output = array(Parser::TYPE_PROPERTY, array('nullFloat'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT));

        $this->verify($input, $output);
    }

    public function testNullString()
    {
        $input = array(Parser::TYPE_PROPERTY, array('nullString'));

        $output = array(Parser::TYPE_PROPERTY, array('nullString'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING));

        $this->verify($input, $output);
    }

    public function testPerson()
    {
        $input = array(Parser::TYPE_PROPERTY, array('person'));

        $output = array(Parser::TYPE_PROPERTY, array('person'), array(Types::TYPE_OBJECT, 'Person'));

        $this->verify($input, $output);
    }

    public function testPersonAge()
    {
        $input = array(Parser::TYPE_PROPERTY, array('person', 'age'));

        $output = array(Parser::TYPE_PROPERTY, array('person', 'age'), Types::TYPE_INTEGER);

        $this->verify($input, $output);
    }

    public function testPersonName()
    {
        $input = array(Parser::TYPE_PROPERTY, array('person', 'name'));

        $output = array(Parser::TYPE_PROPERTY, array('person', 'name'), Types::TYPE_STRING);

        $this->verify($input, $output);
    }

    public function testPeople()
    {
        $input = array(Parser::TYPE_PROPERTY, array('people'));

        $output = array(Parser::TYPE_PROPERTY, array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')));

        $this->verify($input, $output);
    }

    public function testUnknown()
    {
        $input = array(Parser::TYPE_PROPERTY, array('unknown'));

        $exception = self::getUnknownPropertyException('Database', 'unknown');

        $this->verifyException($input, $exception);
    }

    /*
    public function testObject()
    {
        $input = array(Parser::TYPE_OBJECT, array(
            'person' => array(Parser::TYPE_PROPERTY, array('person')),
            'age' => array(Parser::TYPE_PROPERTY, array('person', 'age'))
        ));

        $output = array(Parser::TYPE_OBJECT, array(
            'person' => array(Parser::TYPE_PROPERTY, array('person'), array(Types::TYPE_OBJECT, 'Person')),
            'age' => array(Parser::TYPE_PROPERTY, array('person', 'age'), Types::TYPE_INTEGER)
        ));

        $this->verify($input, $output);
    }
    */

    // TODO: identity(nullBoolean)

    public function testBooleanBoolean()
    {
        $input = array(Parser::TYPE_FUNCTION, 'boolean', array(
            array(Parser::TYPE_PROPERTY, array('boolean'))
        ));

        $output = array(Parser::TYPE_FUNCTION, 'boolean', array(
            array(Parser::TYPE_PROPERTY, array('boolean'), Types::TYPE_BOOLEAN)
        ), Types::TYPE_BOOLEAN);

        $this->verify($input, $output);
    }

    public function testGetPeopleName()
    {
        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, array('people')),
            array(Parser::TYPE_PROPERTY, array('name'))
        ));

        $output = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
            array(Parser::TYPE_PROPERTY, array('name'), Types::TYPE_STRING)
        ), array(Types::TYPE_ARRAY, Types::TYPE_STRING));

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $resolver = self::getResolver();
        $actualOutput = $resolver->resolve($input);

        $this->compare($expectedOutput, $actualOutput);
    }

    private static function getResolver()
    {
        $functions = array(
            'boolean' => array(
                array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN)
            ),
            'null_boolean' => array(
                array(Types::TYPE_NULL, Types::TYPE_NULL),
                array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN)
            ),
            'merge' => array(
                array('$x', '$x', '$x')
            ),
            'get' => array(
                array(
                    array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')),
                    '$y',
                    array(Types::TYPE_ARRAY, '$y')
                )
            )
        );

        $properties = array(
            'Database' => array(
                'null' => Types::TYPE_NULL,
                'boolean' => Types::TYPE_BOOLEAN,
                'integer' => Types::TYPE_INTEGER,
                'float' => Types::TYPE_FLOAT,
                'string' => Types::TYPE_STRING,
                'nullBoolean' => array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN),
                'nullInteger' => array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                'nullFloat' => array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                'nullString' => array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING),
                'person' => array(Types::TYPE_OBJECT, 'Person'),
                'people' => array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))
            ),
            'Person' => array(
                'age' => Types::TYPE_INTEGER,
                'name' => Types::TYPE_STRING
            )
        );

        return new Resolver(
            new Functions($functions),
            new Properties($properties)
        );
    }

    private function compare($expected, $actual)
    {
        $expectedJson = json_encode($expected);
        $actualJson = json_encode($actual);

        $this->assertSame($expectedJson, $actualJson);
    }

    private function verifyException($input, $expected)
    {
        $resolver = self::getResolver();

        try {
            $resolver->resolve($input);
            $actual = null;
        } catch (Exception $exception) {
            $actual = array(
                'code' => $exception->getCode(),
                'data' => $exception->getData()
            );
        }

        $this->assertSame($expected, $actual);
    }

    private static function getUnknownPropertyException($class, $property)
    {
        return array(
            'code' => Exception::QUERY_UNKNOWN_PROPERTY,
            'data' => array(
                'class' => $class,
                'property' => $property
            )
        );
    }

    private static function getUnresolvableTypeConstraintsException($request)
    {
        return array(
            'code' => Exception::QUERY_UNRESOLVABLE_TYPE_CONSTRAINTS,
            'data' => array(
                'request' => $request
            )
        );
    }
}
