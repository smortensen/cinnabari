<?php

namespace Datto\Cinnabari;

use Datto\Cinnabari\Phases\Compiler\PhpOutputCompiler;
use Datto\Cinnabari\Entities\Language\Types;

require __DIR__ . '/autoload.php';

$compiler = new PhpOutputCompiler();
$php = $compiler->getValuePhp(0);
$php = $compiler->getCastingPhp($php, Types::TYPE_INTEGER);
$php = $compiler->getAssignmentPhp($php);
$php = $compiler->getArrayPhp($php, $compiler->getValuePhp(0));
$php = $compiler->getRowsPhp($php);

echo $php, "\n";
