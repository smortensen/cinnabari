<?php

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Language\Request\FunctionToken;
use Datto\Cinnabari\Language\Request\ObjectToken;
use Datto\Cinnabari\Language\Request\ParameterToken;
use Datto\Cinnabari\Language\Request\PropertyToken;


// Test
$operators = new Operators();
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
$input = ':x';

// Output
$output = new ParameterToken('x');


// Input
$input = ':_';

// Output
$output = new ParameterToken('_');


// Input
$input = ':0';

// Output
$output = new ParameterToken('0');


// Input
$input = ':Php_7';

// Output
$output = new ParameterToken('Php_7');


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
$input = 'notes';

// Output
$output = new PropertyToken(array('notes'));


// Input
$input = 'x';

// Output
$output = new PropertyToken(array('x'));


// Input
$input = 'x . y';

// Output
$output = new PropertyToken(array('x', 'y'));


// Input
$input = 'x . y . z';

// Output
$output = new PropertyToken(array('x', 'y', 'z'));


// Input
$input = '.';

// Output
throw Exception::invalidSyntax('expression', $input, 0);


// Input
$input = 'x .';

// Output
throw Exception::invalidSyntax('property', $input, 3, '.');


// Input
$input = 'f()';

// Output
$output = new FunctionToken('f', array());


// Input
$input = 'f(x)';

// Output
$output = new FunctionToken('f', array(
    new PropertyToken(array('x'))
));


// Input
$input = 'f(*)';

// Output
throw Exception::invalidSyntax('argument', $input, 2);


// Input
$input = 'f(:x, y)';

// Output
$output = new FunctionToken('f', array(
    new ParameterToken('x'),
    new PropertyToken(array('y'))
));


// Input
$input = 'f(:x, *)';

// Output
throw Exception::invalidSyntax('argument', $input, 6, ':x, ');


// Input
$input = 'f(';

// Output
throw Exception::invalidSyntax('argument', $input, 2);


// Input
$input = 'f(x';

// Output
throw Exception::invalidSyntax('function-comma', $input, 3, 'x');


// Input
$input = 'f(x,';

// Output
throw Exception::invalidSyntax('function-comma', $input, 3, 'x');


// Input
$input = 'f(x, ';

// Output
throw Exception::invalidSyntax('argument', $input, 5, 'x, ');


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
$input = '(:x)';

// Output
$output = new ParameterToken('x');


// Input
$input = '{
    "x": :x
}';

// Output
$output = new ObjectToken(array(
    'x' => new ParameterToken('x')
));


// Input
$input = '{
    "x": x
}';

// Output
$output = new ObjectToken(array(
    'x' => new PropertyToken(array('x'))
));


// Input
$input = '{
    "x": :x,
    "y": y
}';

// Output
$output = new ObjectToken(array(
    'x' => new ParameterToken('x'),
    'y' => new PropertyToken(array('y'))
));


// Input
$input = '{
    "x": :x,
    "x": x
}';

// Output
$output = new ObjectToken(array(
    'x' => new PropertyToken(array('x'))
));


// Input
$input = '{';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{"x": x';

// Output
throw Exception::invalidSyntax('object-comma', $input, 7, '"x": x');


// Input
$input = '{}';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{"x"}';

// Output
throw Exception::invalidSyntax('pair-colon', $input, 4, '"x"');


// Input
$input = '{x}';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{"x" x}';

// Output
throw Exception::invalidSyntax('pair-colon', $input, 4, '"x"');


// Input
$input = '{"x": *}';

// Output
throw Exception::invalidSyntax('pair-property', $input, 6, '"x":');


// Input
$input  = '{x: x}';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{6: x}';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{"x": :x "y": y}';

// Output
throw Exception::invalidSyntax('object-comma', $input, 8, '"x": :x');


// Input
$input = '{x, }';

// Output
throw Exception::invalidSyntax('object-element', $input, 1, '{');


// Input
$input = '{"x": :x, }';

// Output
throw Exception::invalidSyntax('object-element', $input, 10, '"x": :x,');


// Input
$input = 'not :x';

// Output
$output = new FunctionToken('not', array(
    new ParameterToken('x')
));


// Input
$input = 'f() + (:c)';

// Output
$output = new FunctionToken('plus', array(
    new FunctionToken('f', array()),
    new ParameterToken('c')
));


// Input
$input = 'a * b';

// Output
$output = new FunctionToken('times', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a / b';

// Output
$output = new FunctionToken('divides', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a + b';

// Output
$output = new FunctionToken('plus', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a - b';

// Output
$output = new FunctionToken('minus', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a < b';

// Output
$output = new FunctionToken('less', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a <= b';

// Output
$output = new FunctionToken('lessEqual', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a = b';

// Output
$output = new FunctionToken('equal', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a != b';

// Output
$output = new FunctionToken('notEqual', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a >= b';

// Output
$output = new FunctionToken('greaterEqual', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a > b';

// Output
$output = new FunctionToken('greater', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'not a';

// Output
$output = new FunctionToken('not', array(
    new PropertyToken(array('a'))
));


// Input
$input = 'a and b';

// Output
$output = new FunctionToken('and', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a or b';

// Output
$output = new FunctionToken('or', array(
    new PropertyToken(array('a')),
    new PropertyToken(array('b'))
));


// Input
$input = 'a * b + c < d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('plus', array(
        new FunctionToken('times', array(
            new PropertyToken(array('a')),
            new PropertyToken(array('b'))
        )),
        new PropertyToken(array('c'))
    )),
    new PropertyToken(array('d'))
));


// Input
$input = 'a * b < c + d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('times', array(
        new PropertyToken(array('a')),
        new PropertyToken(array('b'))
    )),
    new FunctionToken('plus', array(
        new PropertyToken(array('c')),
        new PropertyToken(array('d'))
    ))
));


// Input
$input = 'a + b * c < d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('plus', array(
        new PropertyToken(array('a')),
        new FunctionToken('times', array(
            new PropertyToken(array('b')),
            new PropertyToken(array('c'))
        ))
    )),
    new PropertyToken(array('d'))
));


// Input
$input = 'a + b < c * d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('plus', array(
        new PropertyToken(array('a')),
        new PropertyToken(array('b'))
    )),
    new FunctionToken('times', array(
        new PropertyToken(array('c')),
        new PropertyToken(array('d'))
    ))
));


// Input
$input = 'a < b * c + d';

// Output
$output = new FunctionToken('less', array(
    new PropertyToken(array('a')),
    new FunctionToken('plus', array(
        new FunctionToken('times', array(
            new PropertyToken(array('b')),
            new PropertyToken(array('c'))
        )),
        new PropertyToken(array('d'))
    ))
));


// Input
$input = 'a < b + c * d';

// Output
$output = new FunctionToken('less', array(
    new PropertyToken(array('a')),
    new FunctionToken('plus', array(
        new PropertyToken(array('b')),
        new FunctionToken('times', array(
            new PropertyToken(array('c')),
            new PropertyToken(array('d'))
        ))
    ))
));


// Input
$input = '(a * b) + c < d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('plus', array(
        new FunctionToken('times', array(
            new PropertyToken(array('a')),
            new PropertyToken(array('b'))
        )),
        new PropertyToken(array('c'))
    )),
    new PropertyToken(array('d'))
));


// Input
$input = 'a * (b + c) < d';

// Output
$output = new FunctionToken('less', array(
    new FunctionToken('times', array(
        new PropertyToken(array('a')),
        new FunctionToken('plus', array(
            new PropertyToken(array('b')),
            new PropertyToken(array('c'))
        ))
    )),
    new PropertyToken(array('d'))
));


// Input
$input = 'a * b + (c < d)';

// Output
$output = new FunctionToken('plus', array(
    new FunctionToken('times', array(
        new PropertyToken(array('a')),
        new PropertyToken(array('b'))
    )),
    new FunctionToken('less', array(
        new PropertyToken(array('c')),
        new PropertyToken(array('d'))
    ))
));


// Input
$input = 'a or not b';

// Output
$output = new FunctionToken('or', array(
    new PropertyToken(array('a')),
    new FunctionToken('not', array(
        new PropertyToken(array('b'))
    ))
));


// Input
$input = 'not not a';

// Output
$output = new FunctionToken('not', array(
    new FunctionToken('not', array(
        new PropertyToken(array('a'))
    ))
));


// Input
$input = 'x + *';

// Output
throw Exception::invalidSyntax('unary-expression', $input, 4, '+');


// Input
$input = 'map(filter(partners, id > :arg), {"id": id, "test": test(clients, {"name": name})})';

// Output
$output = new FunctionToken('map', array(
    new FunctionToken('filter', array(
        new PropertyToken(array('partners')),
        new FunctionToken('greater', array(
            new PropertyToken(array('id')),
            new ParameterToken('arg')
        ))
    )),
    new ObjectToken(array(
        'id' => new PropertyToken(array('id')),
        'test' => new FunctionToken('test', array(
            new PropertyToken(array('clients')),
            new ObjectToken(array(
                'name' => new PropertyToken(array('name'))
            ))
        ))
    ))
));
