<?php

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\AST;

function getOutput(AST $ast)
{
    ob_start();
    $ast->prettyPrintAllNodes();
    $output = ob_get_clean();
    return $output;
}

// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$count = $ast->newFunctionNode('count', array($rsort));
$ast->setRoot($count);
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
> 3: FUNCTION, count([2])

EOS
    ;

// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$name = $ast->newPropertyNode(array('name'));
$parameter = $ast->newParameterNode(':customer');
$eq = $ast->newFunctionNode('eq', array($name, $parameter));
$count = $ast->newFunctionNode('filter', array($rsort, $eq));
$ast->setRoot($count);
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
  3: PROPERTY, name
  4: PARAMETER, :customer
  5: FUNCTION, eq([3,4])
> 6: FUNCTION, filter([2,5])

EOS
    ;