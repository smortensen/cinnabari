<?php

/**
 * Copyright (C) 2016, 2017 Datto, Inc.
 *
 * This file is part of Cinnabari.
 *
 * Cinnabari is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * Cinnabari is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Cinnabari. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\Result\OptimizeAST;

function getOutput(AST $ast)
{
    ob_start();
    $ast->prettyPrintAllNodes();
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

/**
 * filter(sort(a, b), c) => sort(filter(a, c), b)
 */
// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$name = $ast->newPropertyNode(array('name'));
$parameter = $ast->newParameterNode(':customer');
$eq = $ast->newFunctionNode('eq', array($name, $parameter));
$top = $ast->newFunctionNode('filter', array($rsort, $eq));
$ast->setRoot($top);
$optimizeAST = new OptimizeAST($ast);
$optimizeAST->optimize();
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, filter([0,5])
  3: PROPERTY, name
  4: PARAMETER, :customer
  5: FUNCTION, eq([3,4])
> 6: FUNCTION, rsort([2,1])

EOS
;

/**
 * insert(f(a, b), c) => insert(a, c) where "f" is "(r)sort", "slice", or "filter."
 */
// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$top = $ast->newFunctionNode('insert', array($rsort, $id));  // nonsensical 2nd argument
$ast->setRoot($top);
$optimizeAST = new OptimizeAST($ast);
$optimizeAST->optimize();
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
> 3: FUNCTION, insert([0,1])

EOS
;

/**
 * A sort or rsort below an aggregate or sort function is useless and
 * can be removed, unless there is a slice between them.
 */

// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$top = $ast->newFunctionNode('sort', array($rsort, $id));  // nonsensical 2nd argument
$ast->setRoot($top);
$optimizeAST = new OptimizeAST($ast);
$optimizeAST->optimize();
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
> 3: FUNCTION, sort([0,1])

EOS
;

/**
 * A sort or rsort below an aggregate or sort function is useless and
 * can be removed, *unless* there is a slice between them.
 * Note: no optimization occurs in this example.
 */
// Test
$ast = new AST();
$devices = $ast->newPropertyNode(array('devices'));
$id = $ast->newPropertyNode(array('id'));
$rsort = $ast->newFunctionNode('rsort', array($devices, $id));
$parameter = $ast->newParameterNode(':blah');
$slice = $ast->newFunctionNode('slice', array($rsort, $parameter, $parameter));
$top = $ast->newFunctionNode('sort', array($slice, $id));  // nonsensical 2nd argument
$ast->setRoot($top);
$optimizeAST = new OptimizeAST($ast);
$optimizeAST->optimize();
$output = getOutput($ast);

// Output
$output = <<<'EOS'
  0: PROPERTY, devices
  1: PROPERTY, id
  2: FUNCTION, rsort([0,1])
  3: PARAMETER, :blah
  4: FUNCTION, slice([2,3,3])
> 5: FUNCTION, sort([4,1])

EOS
;
