<?php

namespace Datto\Cinnabari\Phases\Evaluator;

require __DIR__ . '/autoload.php';

$conversions = array(
	':0' => "max(\$input['begin'], 0)",
	':1' => "(max(\$input['end'], 0) < \$input['end']) ? (\$input['end'] - max(\$input['begin'], 0)): 0"
);

$converter = new InputConverter($conversions);

$input = array(
	'begin' => 9,
	'end' => 20
);

$output = $converter->convert($input);

echo "output: ", json_encode($output), "\n";
