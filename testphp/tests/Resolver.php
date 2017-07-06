<?php

require TESTPHP_TESTS_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Functions; // Mock
use Datto\Cinnabari\Language\Properties; // Mock
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Language\Request\FunctionToken;
use Datto\Cinnabari\Language\Request\ParameterToken;
use Datto\Cinnabari\Language\Request\PropertyToken;
use Datto\Cinnabari\Language\Request\ObjectToken;
use Datto\Cinnabari\Resolver;

$functions = new Functions();
$properties = new Properties(array());


// Test
$resolver = new Resolver($functions, $properties);
$resolver->resolve($input);


// Input
$input = new ParameterToken('c');

// Output
throw Exception::unresolvableTypeConstraints($input);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_NULL;
$input->setDataType(Types::TYPE_NULL);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_BOOLEAN;
$input->setDataType(Types::TYPE_BOOLEAN);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_INTEGER;
$input->setDataType(Types::TYPE_INTEGER);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_FLOAT;
$input->setDataType(Types::TYPE_FLOAT);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_STRING;
$input->setDataType(Types::TYPE_STRING);


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return array(Types::TYPE_OBJECT, 'Person');
$input->setDataType(array(Types::TYPE_OBJECT, 'Person'));


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return array(Types::TYPE_ARRAY, Types::TYPE_INTEGER);
$input->setDataType(array(Types::TYPE_ARRAY, Types::TYPE_INTEGER));


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$input->setDataType(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')));


// Input
$input = new PropertyToken(array('x'));

// Output
$properties->getDataType('Database', 'x'); // return array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
$input->setDataType(array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN));


// Input
$input = new PropertyToken(array('unknown'));

// Output
$properties->getDataType('Database', 'unknown'); // throw Exception::unknownProperty('Database', 'unknown');
throw Exception::unknownProperty('Database', 'unknown');


// Input
$input = new PropertyToken(array('person', 'age'));

// Output
$properties->getDataType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$properties->getDataType('Person', 'age'); // return Types::TYPE_INTEGER;
$input->setDataType(Types::TYPE_INTEGER);


// Input
$input = new PropertyToken(array('people', 'age'));

// Output
$properties->getDataType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
throw Exception::invalidPropertyAccess(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')), 'age');


// Input
$input = new FunctionToken('f', array());

// Output
$functions->getSignatures('f'); // return array(array(Types::TYPE_BOOLEAN));
$input->setDataType(Types::TYPE_BOOLEAN);

/*
// Input
$input = new FunctionToken('f', array(
	new PropertyToken(array('x'))
));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_BOOLEAN;
$functions->getSignatures('f'); // return array(array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN));
$output = new Request(array(
	0 => new FunctionToken('f', array(1), Types::TYPE_BOOLEAN),
	1 => new PropertyToken(array('x'), Types::TYPE_BOOLEAN)
));


// Input
$input = new FunctionToken('identity', array(
	new PropertyToken(array('x'))
));

// Output
$properties->getDataType('Database', 'x'); // return array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
$functions->getSignatures('identity'); // return array(array(Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN));


// Input
$input = new FunctionToken('get', array(
	new PropertyToken(array('people')),
	new PropertyToken(array('name'))
));

// Output
$properties->getDataType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$properties->getDataType('Person', 'name'); // return Types::TYPE_STRING;
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
$output = new Request(array(
	0 => new FunctionToken('get', array(1, 2), array(Types::TYPE_ARRAY, Types::TYPE_STRING)),
	1 => new PropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	2 => new PropertyToken(array('name'), Types::TYPE_STRING)
));


// Input
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

// Output
$properties->getDataType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$properties->getDataType('Person', 'id'); // return Types::TYPE_INTEGER;
$functions->getSignatures('equal'); // return array(array(Types::TYPE_NULL, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_NULL), array(Types::TYPE_NULL, Types::TYPE_FLOAT, Types::TYPE_NULL), array(Types::TYPE_INTEGER, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_INTEGER, Types::TYPE_INTEGER, Types::TYPE_BOOLEAN), array(Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_BOOLEAN), array(Types::TYPE_FLOAT, Types::TYPE_INTEGER, Types::TYPE_BOOLEAN), array(Types::TYPE_FLOAT, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_FLOAT, Types::TYPE_FLOAT, Types::TYPE_BOOLEAN), array(Types::TYPE_NULL, Types::TYPE_STRING, Types::TYPE_NULL), array(Types::TYPE_STRING, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_STRING, Types::TYPE_STRING, Types::TYPE_BOOLEAN));
$functions->getSignatures('filter'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), Types::TYPE_NULL, array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))), array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), Types::TYPE_BOOLEAN, array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))));
$properties->getDataType('Person', 'name'); // return Types::TYPE_STRING;
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
$output = new Request(array(
	0 => new FunctionToken('get', array(1, 6), array(Types::TYPE_ARRAY, Types::TYPE_STRING)),
	1 => new FunctionToken('filter', array(2, 3), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	2 => new PropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	3 => new FunctionToken('equal', array(4, 5), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN)),
	4 => new PropertyToken(array('id'), Types::TYPE_INTEGER),
	5 => new ParameterToken('id', array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)),
	6 => new PropertyToken(array('name'), Types::TYPE_STRING)
));


// Input
$input = new FunctionToken('get', array(
	new PropertyToken(array('person')),
	new PropertyToken(array('person'))
));

// Output
$properties->getDataType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$properties->getDataType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
throw Exception::unresolvableTypeConstraints($input);


// Input
$input = new ObjectToken(array(
	'X' => new PropertyToken(array('x')),
	'Y' => new PropertyToken(array('y'))
));

// Output
$properties->getDataType('Database', 'x'); // return Types::TYPE_INTEGER;
$properties->getDataType('Database', 'y'); // return Types::TYPE_STRING;
$output = new Request(array(
	0 => new ObjectToken(array('X' => 1, 'Y' => 2), array(Types::TYPE_OBJECT, array('X' => Types::TYPE_INTEGER, 'Y' => Types::TYPE_STRING))),
	1 => new PropertyToken(array('x'), Types::TYPE_INTEGER),
	2 => new PropertyToken(array('y'), Types::TYPE_STRING)
));
*/
