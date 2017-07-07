<?php

/**
 * Copyright (C) 2016, 2017 Datto, Inc.
 *
 * This file is part of Cadia.
 *
 * Cadia is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * Cadia is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Cadia. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Griffin Bishop <gbishop@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari;

require TESTPHP_TESTS_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Language\Operators;

$operators = new Operators();
$input = null;


// Test
$parser = new Parser($operators);
$output = $parser->parse($input);


// Input
$input = null;

// Output
throw Exception::invalidType($input);


// Input
$input = false;

// Output
throw Exception::invalidType($input);


// Input
$input = '';

// Output
throw Exception::invalidSyntax('expression', $input, 0);


// Input
$input = ':*';

// Output
throw Exception::invalidSyntax('parameter', $input, 1, ':');


// Input
$input = ':';

// Output
throw Exception::invalidSyntax('parameter', $input, 1, ':');

// Input
$input = ':x ';

// Output
throw Exception::invalidSyntax('end', $input, 2, ':x');


// Input
$input = '.';

// Output
throw Exception::invalidSyntax('expression', $input, 0);

// Input
$input = 'x .';

// Output
throw Exception::invalidSyntax('property', $input, 3, '.');


// Input
$input = 'f(*)';

// Output
throw Exception::invalidSyntax('initial-argument', $input, 2);


// Input
$input = 'f(:x, *)';

// Output
throw Exception::invalidSyntax('noninitial-argument', $input, 6, ':x, ');

// Input
$input = 'f(';

// Output
throw Exception::invalidSyntax('initial-argument', $input, 2);


// Input
$input = 'f(abc';

// Output
throw Exception::invalidSyntax('function-comma', $input, 5, 'abc');


// Input
$input = 'f(abc,';

// Output
throw Exception::invalidSyntax('function-comma', $input, 5, 'abc');


// Input
$input = 'f(abc, ';

// Output
throw Exception::invalidSyntax('noninitial-argument', $input, 7, 'abc, ');


// Input
$input = 'x.f()';

// Output
throw Exception::invalidSyntax('end', $input, 3, 'x.f');


// Input
$input = '(';

// Output
throw Exception::invalidSyntax('group-expression', $input, 1);


// Input
$input = '()';

// Output
throw Exception::invalidSyntax('group-expression', $input, 1);


// Input
$input = '{}';

// Output
throw Exception::invalidSyntax('initial-object-element', $input, 1);


// Input
$input = '{x';

// Output
throw Exception::invalidSyntax('object-comma', $input, 2, 'x');


// Input
$input = '{x, }';

// Output
throw Exception::invalidSyntax('noninitial-object-element', $input, 4, 'x, ');


// Input
$input = '{6: x}';

// Output
throw Exception::invalidSyntax('object-comma', $input, 2, '6');


// Input
$input = '{"x" x}';

// Output
throw Exception::invalidSyntax('pair-colon', $input, 4, '"x"');


// Input
$input = '{"x": *}';

// Output
throw Exception::invalidSyntax('pair-property', $input, 6, '"x":');


// Input
$input = '{"x": x';

// Output
throw Exception::invalidSyntax('object-comma', $input, 7, '"x": x');


// Input
$input = '{"x": :x "x": x}';

// Output
throw Exception::invalidSyntax('object-comma', $input, 8, '"x": :x');


// Input
$input = '{"x": :x, }';

// Output
throw Exception::invalidSyntax('noninitial-object-element', $input, 10, '"x": :x, ');


// Input
$input  = '{x: x}';

// Output
throw Exception::invalidSyntax('object-comma', $input, 2, 'x');
