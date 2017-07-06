<?php

namespace Datto\Cinnabari;

require TESTPHP_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Language\Types;

$data = array(
	'Person' => array(
		'age' => Types::TYPE_INTEGER
	)
);

$properties = new Properties($data);


// Test
$dataType = $properties->getDataType($class, $property);


// Input
$class = 'Person';
$property = 'age';

// Output
$dataType = Types::TYPE_INTEGER;


// Input
$class = 'Person';
$property = 'unknown';

// Output
throw Exception::unknownProperty($class, $property);
