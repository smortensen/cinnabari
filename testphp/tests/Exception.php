<?php

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';


// Test
throw Exception::invalidSyntax('parameter', $input, 1);


// Input
$input = ':*';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected parameter identifier after \':\', found \'*\' instead on line 1, character 2'
);


// Test
throw Exception::invalidSyntax('end', $input, 2, ':x');


// Input
$input = ':x ';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected end of input after \':x\', found \'\' instead on line 1, character 3'
);


// Test
throw Exception::invalidSyntax('expression', $input, 0);


// Input
$input = '.';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected expression, found \'.\' instead on line 1, character 1'
);


// Test
throw Exception::invalidSyntax('property', $input, 3, '.');


// Input
$input = 'x .';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected property identifier after \'.\', found \'\' instead on line 1, character 4'
);


// Test
throw Exception::invalidSyntax('argument', $input, 2);


// Input
$input = 'f(*)';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected function argument, found \'*)\' instead on line 1, character 3'
);


// Test
throw Exception::invalidSyntax('argument', $input, 6, ':x, ');


// Input
$input = 'f(:x, *)';

// Output
throw new Exception(Exception::QUERY_INVALID_SYNTAX, $input, 'Expected function argument '
    . 'after \':x, \', found \'*)\' instead on line 1, character 7');


// Test
throw Exception::invalidSyntax('function-comma', $input, 5, 'abc');


// Input
$input = 'f(abc';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected \', \' or \')\' after \'abc\', found \'\' instead on line 1, character 6'
);


// Test
throw Exception::invalidSyntax('argument', $input, 7, 'abc, ');


// Input
$input = 'f(abc, ';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected function argument after \'abc, \', found \'\' instead on line 1, character 8'
);


// Test
throw Exception::invalidSyntax('group-expression', $input, 1);


// Input
$input = '(';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected expression in group, found \'\' instead on line 1, character 2'
);


// Test
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{}';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected key/value pair (e.g. "name": property) after \'{\', '
    . 'found \'}\' instead on line 1, character 2'
);


// Test
throw Exception::invalidSyntax('object-comma', $input, 2, 'x');


// Input
$input = '{x';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected \', \' or \'}\' after \'x\', found \'\' instead on line 1, character 3'
);


// Test
throw Exception::invalidSyntax('object-element', $input, 4, 'x, ');


// Input
$input = '{x, }';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected key/value pair (e.g. "name": property) after \'x, \', '
    . 'found \'}\' instead on line 1, character 5'
);


// Test
throw Exception::invalidSyntax('pair-colon', $input, 4, '"x"');


// Input
$input = '{"x" x}';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    "Expected ':' after '\"x\"', found 'x}' instead on line 1, character 5"
);


// Test
throw Exception::invalidSyntax('pair-property', $input, 6, '"x":');


// Input
$input = '{"x": *}';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected property expression after \'"x":\', found \'*}\' instead on line 1, character 7'
);


// Test
throw Exception::invalidSyntax('unary-expression', $input, 7, ' + ');


// Input
$input = 'test + *';

// Output
throw new Exception(
    Exception::QUERY_INVALID_SYNTAX,
    $input,
    'Expected unary-expression after \' + \' operator, found \'*\' instead on line 1, character 8'
);
