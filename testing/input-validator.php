<?php

namespace Datto\Cinnabari\Phases\Evaluator;

use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Exception;

require __DIR__ . '/autoload.php';

$parameters = array(
	'begin' => Types::TYPE_INTEGER,
	'end' => array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER)
);

$validator = new InputValidator($parameters);

$input = array(
	'begin' => 9,
	'end' => 20
);

try {
	$validator->validate($input);
} catch (Exception $exception) {
	$code = $exception->getCode();
	$message = $exception->getMessage();
	$data = $exception->getData();

	$output = array(
		'code' => $code,
		'message' => $message,
		'data' => $data
	);

	echo json_encode($output), "\n";
}
