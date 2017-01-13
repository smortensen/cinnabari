<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Tests\Language\Functions;
use Datto\Cinnabari\Parser;
use Datto\Cinnabari\PropertyResolver;
use PHPUnit_Framework_TestCase;

class PropertyResolverTest extends PHPUnit_Framework_TestCase
{
    public function testParameter()
    {
        $input = array(Parser::TYPE_PARAMETER, 'c');

        $output = $input;

        $this->verify($input, $output);
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

    public function testBooleanBoolean()
    {
        $input = array(Parser::TYPE_FUNCTION, 'boolean', array(
            array(Parser::TYPE_PROPERTY, array('boolean'))
        ));

        $output = array(Parser::TYPE_FUNCTION, 'boolean', array(
            array(Parser::TYPE_PROPERTY, array('boolean'), Types::TYPE_BOOLEAN)
        ));

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
        ));

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
                array('A', 'A', 'A')
            ),
            'get' => array(
                array(
                    array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                    'B',
                    array(Types::TYPE_ARRAY, 'B')
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

        return new PropertyResolver(
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
}
