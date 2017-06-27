<?php

require TESTPHP_TESTS_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser\Tokens\FunctionToken as ParserFunctionToken;
use Datto\Cinnabari\Parser\Tokens\ParameterToken as ParserParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken as ParserPropertyToken;
use Datto\Cinnabari\Parser\Tokens\ObjectToken as ParserObjectToken;
use Datto\Cinnabari\Language\Functions; // Mock
use Datto\Cinnabari\Language\Properties; // Mock
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Resolver\Request;
use Datto\Cinnabari\Resolver\Tokens\FunctionToken as ResolverFunctionToken;
use Datto\Cinnabari\Resolver\Tokens\ParameterToken as ResolverParameterToken;
use Datto\Cinnabari\Resolver\Tokens\PropertyToken as ResolverPropertyToken;
use Datto\Cinnabari\Resolver\Tokens\ObjectToken as ResolverObjectToken;

$functions = new Functions();
$properties = new Properties(array());


// Test
$resolver = new Resolver($functions, $properties);
$output = $resolver->resolve($input);


// Input
$input = new ParserParameterToken('c');

// Output
throw Exception::unresolvableTypeConstraints($input);


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_NULL;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), Types::TYPE_NULL)
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_BOOLEAN;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), Types::TYPE_BOOLEAN)
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_INTEGER;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), Types::TYPE_INTEGER)
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_FLOAT;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), Types::TYPE_FLOAT)
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_STRING;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), Types::TYPE_STRING)
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return array(Types::TYPE_OBJECT, 'Person');
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), array(Types::TYPE_OBJECT, 'Person'))
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return array(Types::TYPE_ARRAY, Types::TYPE_INTEGER);
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), array(Types::TYPE_ARRAY, Types::TYPE_INTEGER))
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')))
));


// Input
$input = new ParserPropertyToken(array('x'));

// Output
$properties->getType('Database', 'x'); // return array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
$output = new Request(array(
	0 => new ResolverPropertyToken(array('x'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN))
));


// Input
$input = new ParserPropertyToken(array('unknown'));

// Output
$properties->getType('Database', 'unknown'); // throw Exception::unknownProperty('Database', 'unknown');
throw Exception::unknownProperty('Database', 'unknown');


// Input
$input = new ParserPropertyToken(array('person', 'age'));

// Output
$properties->getType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$properties->getType('Person', 'age'); // return Types::TYPE_INTEGER;
$output = new Request(array(
	0 => new ResolverPropertyToken(array('person', 'age'), Types::TYPE_INTEGER)
));


// Input
$input = new ParserPropertyToken(array('people', 'age'));

// Output
$properties->getType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
throw Exception::invalidPropertyAccess(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person')), 'age');


// Input
$input = new ParserFunctionToken('f', array());

// Output
$functions->getSignatures('f'); // return array(array(Types::TYPE_BOOLEAN));
$output = new Request(array(
	0 => new ResolverFunctionToken('f', array(), Types::TYPE_BOOLEAN)
));


// Input
$input = new ParserFunctionToken('f', array(
	new ParserPropertyToken(array('x'))
));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_BOOLEAN;
$functions->getSignatures('f'); // return array(array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN));
$output = new Request(array(
	0 => new ResolverFunctionToken('f', array(1), Types::TYPE_BOOLEAN),
	1 => new ResolverPropertyToken(array('x'), Types::TYPE_BOOLEAN)
));


// Input
$input = new ParserFunctionToken('identity', array(
	new ParserPropertyToken(array('x'))
));

// Output
$properties->getType('Database', 'x'); // return array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
$functions->getSignatures('identity'); // return array(array(Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN));


// Input
$input = new ParserFunctionToken('get', array(
	new ParserPropertyToken(array('people')),
	new ParserPropertyToken(array('name'))
));

// Output
$properties->getType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$properties->getType('Person', 'name'); // return Types::TYPE_STRING;
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
$output = new Request(array(
	0 => new ResolverFunctionToken('get', array(1, 2), array(Types::TYPE_ARRAY, Types::TYPE_STRING)),
	1 => new ResolverPropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	2 => new ResolverPropertyToken(array('name'), Types::TYPE_STRING)
));


// Input
$input = new ParserFunctionToken('get', array(
	new ParserFunctionToken('filter', array(
		new ParserPropertyToken(array('people')),
		new ParserFunctionToken('equal', array(
			new ParserPropertyToken(array('id')),
			new ParserParameterToken('id')
		))
	)),
	new ParserPropertyToken(array('name'))
));

// Output
$properties->getType('Database', 'people'); // return array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'));
$properties->getType('Person', 'id'); // return Types::TYPE_INTEGER;
$functions->getSignatures('equal'); // return array(array(Types::TYPE_NULL, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_NULL), array(Types::TYPE_NULL, Types::TYPE_FLOAT, Types::TYPE_NULL), array(Types::TYPE_INTEGER, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_INTEGER, Types::TYPE_INTEGER, Types::TYPE_BOOLEAN), array(Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_BOOLEAN), array(Types::TYPE_FLOAT, Types::TYPE_INTEGER, Types::TYPE_BOOLEAN), array(Types::TYPE_FLOAT, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_FLOAT, Types::TYPE_FLOAT, Types::TYPE_BOOLEAN), array(Types::TYPE_NULL, Types::TYPE_STRING, Types::TYPE_NULL), array(Types::TYPE_STRING, Types::TYPE_NULL, Types::TYPE_NULL), array(Types::TYPE_STRING, Types::TYPE_STRING, Types::TYPE_BOOLEAN));
$functions->getSignatures('filter'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), Types::TYPE_NULL, array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))), array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), Types::TYPE_BOOLEAN, array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x'))));
$properties->getType('Person', 'name'); // return Types::TYPE_STRING;
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
$output = new Request(array(
	0 => new ResolverFunctionToken('get', array(1, 6), array(Types::TYPE_ARRAY, Types::TYPE_STRING)),
	1 => new ResolverFunctionToken('filter', array(2, 3), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	2 => new ResolverPropertyToken(array('people'), array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))),
	3 => new ResolverFunctionToken('equal', array(4, 5), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN)),
	4 => new ResolverPropertyToken(array('id'), Types::TYPE_INTEGER),
	5 => new ResolverParameterToken('id', array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)),
	6 => new ResolverPropertyToken(array('name'), Types::TYPE_STRING)
));


// Input
$input = new ParserFunctionToken('get', array(
	new ParserPropertyToken(array('person')),
	new ParserPropertyToken(array('person'))
));

// Output
$properties->getType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$properties->getType('Database', 'person'); // return array(Types::TYPE_OBJECT, 'Person');
$functions->getSignatures('get'); // return array(array(array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, '$x')), '$y', array(Types::TYPE_ARRAY, '$y')));
throw Exception::unresolvableTypeConstraints($input);


// Input
$input = new ParserObjectToken(array(
	'X' => new ParserPropertyToken(array('x')),
	'Y' => new ParserPropertyToken(array('y'))
));

// Output
$properties->getType('Database', 'x'); // return Types::TYPE_INTEGER;
$properties->getType('Database', 'y'); // return Types::TYPE_STRING;
$output = new Request(array(
	0 => new ResolverObjectToken(array('X' => 1, 'Y' => 2), array(Types::TYPE_OBJECT, array('X' => Types::TYPE_INTEGER, 'Y' => Types::TYPE_STRING))),
	1 => new ResolverPropertyToken(array('x'), Types::TYPE_INTEGER),
	2 => new ResolverPropertyToken(array('y'), Types::TYPE_STRING)
));
