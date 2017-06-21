<?php

require TESTPHP_TESTS_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser\Tokens\ParameterToken as ParserParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken as ParserPropertyToken;
use Datto\Cinnabari\Language\Functions; // Mock
use Datto\Cinnabari\Language\Properties; // Mock
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Resolver\Request;
use Datto\Cinnabari\Resolver\Tokens\PropertyToken as ResolverPropertyToken;

$functions = new Functions();
$properties = new Properties(array());


// Test
$resolver = new Resolver($functions, $properties);
$output = $resolver->resolve($input);


// Input
$input = new ParserPropertyToken(array('unknown'));

// Output
$properties->getType('Database', 'unknown'); // throw Exception::unknownProperty('Database', 'unknown');
