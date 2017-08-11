<?php

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\AST;

// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$count = $ast->newFunctionNode('count', array($rsort));
$ast->setRoot($count);
ob_start();
$ast->prettyPrintAllNodes();
$output = ob_get_contents();
ob_end_clean();

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
> 3: FUNCTION, count([2])

EOS
    ;
