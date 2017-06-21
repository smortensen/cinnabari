<?php

namespace Datto\Cinnabari\Tests\Request;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser\Tokens\FunctionToken;
use Datto\Cinnabari\Parser\Tokens\ObjectToken;
use Datto\Cinnabari\Parser\Tokens\ParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken;
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Tests\Language\Functions;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
	public function testParameter()
	{
		$input = new ParameterToken('c');

		$exception = self::getUnresolvableTypeConstraintsException($input);

		$this->verifyException($input, $exception);
	}

	public function testNull()
	{
		$input = new PropertyToken(array('null'));

		$output = new PropertyToken(array('null'), Types::TYPE_NULL);

		$this->verify($input, $output);
	}

	public function testBoolean()
	{
		$input = new PropertyToken(array('boolean'));

		$output = new PropertyToken(array('boolean'), Types::TYPE_BOOLEAN);

		$this->verify($input, $output);
	}

	public function testInteger()
	{
		$input = new PropertyToken(array('integer'));

		$output = new PropertyToken(array('integer'), Types::TYPE_INTEGER);

		$this->verify($input, $output);
	}

	public function testFloat()
	{
		$input = new PropertyToken(array('float'));

		$output = new PropertyToken(array('float'), Types::TYPE_FLOAT);

		$this->verify($input, $output);
	}

	public function testString()
	{
		$input = new PropertyToken(array('string'));

		$output = new PropertyToken(array('string'), Types::TYPE_STRING);

		$this->verify($input, $output);
	}

	public function testNullBoolean()
	{
		$input = new PropertyToken(array('nullBoolean'));

		$output = new PropertyToken(array('nullBoolean'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN));

		$this->verify($input, $output);
	}

	public function testNullInteger()
	{
		$input = new PropertyToken(array('nullInteger'));

		$output = new PropertyToken(array('nullInteger'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER));

		$this->verify($input, $output);
	}

	public function testNullFloat()
	{
		$input = new PropertyToken(array('nullFloat'));

		$output = new PropertyToken(array('nullFloat'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT));

		$this->verify($input, $output);
	}

	public function testNullString()
	{
		$input = new PropertyToken(array('nullString'));

		$output = new PropertyToken(array('nullString'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING));

		$this->verify($input, $output);
	}

	public function testPerson()
	{
		$input = new PropertyToken(array('person'));

		$output = new PropertyToken(array('person'), array(Types::TYPE_OBJECT, 'Person'));

		$this->verify($input, $output);
	}

	public function testPersonAge()
	{
		$input = new PropertyToken(array('person', 'age'));

		$output = new PropertyToken(array('person', 'age'), Types::TYPE_INTEGER);

		$this->verify($input, $output);
	}

	public function testPersonName()
	{
		$input = new PropertyToken(array('person', 'name'));

		$output = new PropertyToken(array('person', 'name'), Types::TYPE_STRING);

		$this->verify($input, $output);
	}

	public function testPeople()
	{
		$input = new PropertyToken(array('people'));

		$output = new PropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')));

		$this->verify($input, $output);
	}

	public function testUnknownProperty()
	{
		$input = new PropertyToken(array('unknown'));

		$exception = self::getUnknownPropertyException('Database', 'unknown');

		$this->verifyException($input, $exception);
	}

	public function testInvalidPropertyAccess()
	{
		$input = new PropertyToken(array('people', 'name'));

		$exception = self::getInvalidPropertyAccessException(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')), 'name');

		$this->verifyException($input, $exception);
	}

	public function testObject()
	{
		$input = new ObjectToken(array(
			'person' => new PropertyToken(array('person')),
			'age' => new PropertyToken(array('person', 'age'))
		));

		$output = new ObjectToken(array(
			'person' => new PropertyToken(array('person'), array(Types::TYPE_OBJECT, 'Person')),
			'age' => new PropertyToken(array('person', 'age'), Types::TYPE_INTEGER)
		));

		$this->verify($input, $output);
	}

	// TODO: identity(nullBoolean)

	public function testTrue()
	{
		$input = new FunctionToken('true', array());

		$output = new FunctionToken('true', array(), Types::TYPE_BOOLEAN);

		$this->verify($input, $output);
	}

	public function testBooleanBoolean()
	{
		$input = new FunctionToken('boolean', array(
			new PropertyToken(array('boolean'))
		));

		$output = new FunctionToken('boolean', array(
			new PropertyToken(array('boolean'), Types::TYPE_BOOLEAN)
		), Types::TYPE_BOOLEAN);

		$this->verify($input, $output);
	}

	public function testGetPeopleName()
	{
		$input = new FunctionToken('get', array(
			new PropertyToken(array('people')),
			new PropertyToken(array('name'))
		));

		$output = new FunctionToken('get', array(
			new PropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
			new PropertyToken(array('name'), Types::TYPE_STRING)
		), array(Types::TYPE_ARRAY, Types::TYPE_STRING));

		$this->verify($input, $output);
	}

	public function testGetFilterPeopleIdName()
	{
		$input = new FunctionToken('get', array(
			new FunctionToken('filter', array(
				new PropertyToken(array('people')),
				new FunctionToken('equal', array(
					new PropertyToken(array('id')),
					new ParameterToken('id')
				))
			)),
			new PropertyToken(array('name'))
		));

		$output = new FunctionToken('get', array(
			new FunctionToken('filter', array(
				new PropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
				new FunctionToken('equal', array(
					new PropertyToken(array('id'), Types::TYPE_INTEGER),
					new ParameterToken('id', array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT))
				), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN))
			), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
			new PropertyToken(array('name'), Types::TYPE_STRING)
		), array(Types::TYPE_ARRAY, Types::TYPE_STRING));

		$this->verify($input, $output);
	}

	public function testUnsatisfiableRequest()
	{
		$input = new FunctionToken('get', array(
			new PropertyToken(array('person')),
			new PropertyToken(array('person'))
		));

		$exception = self::getUnsatisfiableRequestException($input);

		$this->verifyException($input, $exception);
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
			'true' => array(
				array(Types::TYPE_BOOLEAN)
			),
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
			),
			'filter' => array(
				array(
					array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')),
					Types::TYPE_NULL,
					array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))
				),
				array(
					array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')),
					Types::TYPE_BOOLEAN,
					array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))
				)
			),
			'equal' => array(
				array(
					Types::TYPE_NULL,
					Types::TYPE_NULL,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_NULL,
					Types::TYPE_INTEGER,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_NULL,
					Types::TYPE_FLOAT,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_INTEGER,
					Types::TYPE_NULL,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_INTEGER,
					Types::TYPE_INTEGER,
					Types::TYPE_BOOLEAN
				),
				array(
					Types::TYPE_INTEGER,
					Types::TYPE_FLOAT,
					Types::TYPE_BOOLEAN
				),
				array(
					Types::TYPE_FLOAT,
					Types::TYPE_INTEGER,
					Types::TYPE_BOOLEAN
				),
				array(
					Types::TYPE_FLOAT,
					Types::TYPE_NULL,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_FLOAT,
					Types::TYPE_FLOAT,
					Types::TYPE_BOOLEAN
				),
				array(
					Types::TYPE_NULL,
					Types::TYPE_STRING,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_STRING,
					Types::TYPE_NULL,
					Types::TYPE_NULL
				),
				array(
					Types::TYPE_STRING,
					Types::TYPE_STRING,
					Types::TYPE_BOOLEAN
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
				'id' => Types::TYPE_INTEGER,
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

	private static function getInvalidPropertyAccessException($type, $property)
	{
		return array(
			'code' => Exception::QUERY_INVALID_PROPERTY_ACCESS,
			'data' => array(
				'type' => $type,
				'property' => $property
			)
		);
	}

	private static function getUnsatisfiableRequestException($request)
	{
		return array(
			'code' => Exception::QUERY_UNRESOLVABLE_TYPE_CONSTRAINTS,
			'data' => array(
				'request' => $request
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
