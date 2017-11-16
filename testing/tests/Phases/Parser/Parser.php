<?php

namespace Datto\Cinnabari\Phases\Parser;

use Datto\Cinnabari\Entities\Language\Operators;
use Datto\Cinnabari\Entities\Request\FunctionRequest;
use Datto\Cinnabari\Entities\Request\ObjectRequest;
use Datto\Cinnabari\Entities\Request\ParameterRequest;
use Datto\Cinnabari\Entities\Request\PropertyRequest;
use Datto\Cinnabari\Exception;


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
$output = new ParameterRequest('x');


// Input
$input = ':_';

// Output
$output = new ParameterRequest('_');


// Input
$input = ':0';

// Output
$output = new ParameterRequest('0');


// Input
$input = ':Php_7';

// Output
$output = new ParameterRequest('Php_7');


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
$output = new PropertyRequest(array('notes'));


// Input
$input = 'x';

// Output
$output = new PropertyRequest(array('x'));


// Input
$input = 'x . y';

// Output
$output = new PropertyRequest(array('x', 'y'));


// Input
$input = 'x . y . z';

// Output
$output = new PropertyRequest(array('x', 'y', 'z'));


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
$output = new FunctionRequest('f', array());


// Input
$input = 'f(x)';

// Output
$output = new FunctionRequest('f', array(
    new PropertyRequest(array('x'))
));


// Input
$input = 'f(*)';

// Output
throw Exception::invalidSyntax('argument', $input, 2);


// Input
$input = 'f(:x, y)';

// Output
$output = new FunctionRequest('f', array(
    new ParameterRequest('x'),
    new PropertyRequest(array('y'))
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
$output = new ParameterRequest('x');


// Input
$input = '{
    "x": :x
}';

// Output
$output = new ObjectRequest(array(
    'x' => new ParameterRequest('x')
));


// Input
$input = '{
    "x": x
}';

// Output
$output = new ObjectRequest(array(
    'x' => new PropertyRequest(array('x'))
));


// Input
$input = '{
    "x": :x,
    "y": y
}';

// Output
$output = new ObjectRequest(array(
    'x' => new ParameterRequest('x'),
    'y' => new PropertyRequest(array('y'))
));


// Input
$input = '{
    "x": :x,
    "x": x
}';

// Output
$output = new ObjectRequest(array(
    'x' => new PropertyRequest(array('x'))
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
$output = new FunctionRequest('not', array(
    new ParameterRequest('x')
));


// Input
$input = 'f() + (:c)';

// Output
$output = new FunctionRequest('plus', array(
    new FunctionRequest('f', array()),
    new ParameterRequest('c')
));


// Input
$input = 'a * b';

// Output
$output = new FunctionRequest('times', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a / b';

// Output
$output = new FunctionRequest('divides', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a + b';

// Output
$output = new FunctionRequest('plus', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a - b';

// Output
$output = new FunctionRequest('minus', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a < b';

// Output
$output = new FunctionRequest('less', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a <= b';

// Output
$output = new FunctionRequest('lessEqual', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a = b';

// Output
$output = new FunctionRequest('equal', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a != b';

// Output
$output = new FunctionRequest('notEqual', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a >= b';

// Output
$output = new FunctionRequest('greaterEqual', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a > b';

// Output
$output = new FunctionRequest('greater', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'not a';

// Output
$output = new FunctionRequest('not', array(
    new PropertyRequest(array('a'))
));


// Input
$input = 'a and b';

// Output
$output = new FunctionRequest('and', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a or b';

// Output
$output = new FunctionRequest('or', array(
    new PropertyRequest(array('a')),
    new PropertyRequest(array('b'))
));


// Input
$input = 'a * b + c < d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('plus', array(
        new FunctionRequest('times', array(
            new PropertyRequest(array('a')),
            new PropertyRequest(array('b'))
        )),
        new PropertyRequest(array('c'))
    )),
    new PropertyRequest(array('d'))
));


// Input
$input = 'a * b < c + d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('times', array(
        new PropertyRequest(array('a')),
        new PropertyRequest(array('b'))
    )),
    new FunctionRequest('plus', array(
        new PropertyRequest(array('c')),
        new PropertyRequest(array('d'))
    ))
));


// Input
$input = 'a + b * c < d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('plus', array(
        new PropertyRequest(array('a')),
        new FunctionRequest('times', array(
            new PropertyRequest(array('b')),
            new PropertyRequest(array('c'))
        ))
    )),
    new PropertyRequest(array('d'))
));


// Input
$input = 'a + b < c * d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('plus', array(
        new PropertyRequest(array('a')),
        new PropertyRequest(array('b'))
    )),
    new FunctionRequest('times', array(
        new PropertyRequest(array('c')),
        new PropertyRequest(array('d'))
    ))
));


// Input
$input = 'a < b * c + d';

// Output
$output = new FunctionRequest('less', array(
    new PropertyRequest(array('a')),
    new FunctionRequest('plus', array(
        new FunctionRequest('times', array(
            new PropertyRequest(array('b')),
            new PropertyRequest(array('c'))
        )),
        new PropertyRequest(array('d'))
    ))
));


// Input
$input = 'a < b + c * d';

// Output
$output = new FunctionRequest('less', array(
    new PropertyRequest(array('a')),
    new FunctionRequest('plus', array(
        new PropertyRequest(array('b')),
        new FunctionRequest('times', array(
            new PropertyRequest(array('c')),
            new PropertyRequest(array('d'))
        ))
    ))
));


// Input
$input = '(a * b) + c < d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('plus', array(
        new FunctionRequest('times', array(
            new PropertyRequest(array('a')),
            new PropertyRequest(array('b'))
        )),
        new PropertyRequest(array('c'))
    )),
    new PropertyRequest(array('d'))
));


// Input
$input = 'a * (b + c) < d';

// Output
$output = new FunctionRequest('less', array(
    new FunctionRequest('times', array(
        new PropertyRequest(array('a')),
        new FunctionRequest('plus', array(
            new PropertyRequest(array('b')),
            new PropertyRequest(array('c'))
        ))
    )),
    new PropertyRequest(array('d'))
));


// Input
$input = 'a * b + (c < d)';

// Output
$output = new FunctionRequest('plus', array(
    new FunctionRequest('times', array(
        new PropertyRequest(array('a')),
        new PropertyRequest(array('b'))
    )),
    new FunctionRequest('less', array(
        new PropertyRequest(array('c')),
        new PropertyRequest(array('d'))
    ))
));


// Input
$input = 'a or not b';

// Output
$output = new FunctionRequest('or', array(
    new PropertyRequest(array('a')),
    new FunctionRequest('not', array(
        new PropertyRequest(array('b'))
    ))
));


// Input
$input = 'not not a';

// Output
$output = new FunctionRequest('not', array(
    new FunctionRequest('not', array(
        new PropertyRequest(array('a'))
    ))
));


// Input
$input = 'x + *';

// Output
throw Exception::invalidSyntax('unary-expression', $input, 4, '+');


// Input
$input = 'map(filter(partners, id > :arg), {"id": id, "test": test(clients, {"name": name})})';

// Output
$output = new FunctionRequest('map', array(
    new FunctionRequest('filter', array(
        new PropertyRequest(array('partners')),
        new FunctionRequest('greater', array(
            new PropertyRequest(array('id')),
            new ParameterRequest('arg')
        ))
    )),
    new ObjectRequest(array(
        'id' => new PropertyRequest(array('id')),
        'test' => new FunctionRequest('test', array(
            new PropertyRequest(array('clients')),
            new ObjectRequest(array(
                'name' => new PropertyRequest(array('name'))
            ))
        ))
    ))
));
