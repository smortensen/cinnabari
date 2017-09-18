<?php

namespace Datto\Cinnabari\Parser;

require LENS . 'autoload.php';

use Datto\Cinnabari\AbstractRequest\Nodes\FunctionNode;
use Datto\Cinnabari\AbstractRequest\Nodes\ObjectNode;
use Datto\Cinnabari\AbstractRequest\Nodes\ParameterNode;
use Datto\Cinnabari\AbstractRequest\Nodes\PropertyNode;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Parser\Language\Operators;


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
$output = new ParameterNode('x');


// Input
$input = ':_';

// Output
$output = new ParameterNode('_');


// Input
$input = ':0';

// Output
$output = new ParameterNode('0');


// Input
$input = ':Php_7';

// Output
$output = new ParameterNode('Php_7');


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
$output = new PropertyNode(array('notes'));


// Input
$input = 'x';

// Output
$output = new PropertyNode(array('x'));


// Input
$input = 'x . y';

// Output
$output = new PropertyNode(array('x', 'y'));


// Input
$input = 'x . y . z';

// Output
$output = new PropertyNode(array('x', 'y', 'z'));


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
$output = new FunctionNode('f', array());


// Input
$input = 'f(x)';

// Output
$output = new FunctionNode('f', array(
    new PropertyNode(array('x'))
));


// Input
$input = 'f(*)';

// Output
throw Exception::invalidSyntax('argument', $input, 2);


// Input
$input = 'f(:x, y)';

// Output
$output = new FunctionNode('f', array(
    new ParameterNode('x'),
    new PropertyNode(array('y'))
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
$output = new ParameterNode('x');


// Input
$input = '{
    "x": :x
}';

// Output
$output = new ObjectNode(array(
    'x' => new ParameterNode('x')
));


// Input
$input = '{
    "x": x
}';

// Output
$output = new ObjectNode(array(
    'x' => new PropertyNode(array('x'))
));


// Input
$input = '{
    "x": :x,
    "y": y
}';

// Output
$output = new ObjectNode(array(
    'x' => new ParameterNode('x'),
    'y' => new PropertyNode(array('y'))
));


// Input
$input = '{
    "x": :x,
    "x": x
}';

// Output
$output = new ObjectNode(array(
    'x' => new PropertyNode(array('x'))
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
$output = new FunctionNode('not', array(
    new ParameterNode('x')
));


// Input
$input = 'f() + (:c)';

// Output
$output = new FunctionNode('plus', array(
    new FunctionNode('f', array()),
    new ParameterNode('c')
));


// Input
$input = 'a * b';

// Output
$output = new FunctionNode('times', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a / b';

// Output
$output = new FunctionNode('divides', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a + b';

// Output
$output = new FunctionNode('plus', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a - b';

// Output
$output = new FunctionNode('minus', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a < b';

// Output
$output = new FunctionNode('less', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a <= b';

// Output
$output = new FunctionNode('lessEqual', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a = b';

// Output
$output = new FunctionNode('equal', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a != b';

// Output
$output = new FunctionNode('notEqual', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a >= b';

// Output
$output = new FunctionNode('greaterEqual', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a > b';

// Output
$output = new FunctionNode('greater', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'not a';

// Output
$output = new FunctionNode('not', array(
    new PropertyNode(array('a'))
));


// Input
$input = 'a and b';

// Output
$output = new FunctionNode('and', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a or b';

// Output
$output = new FunctionNode('or', array(
    new PropertyNode(array('a')),
    new PropertyNode(array('b'))
));


// Input
$input = 'a * b + c < d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('plus', array(
        new FunctionNode('times', array(
            new PropertyNode(array('a')),
            new PropertyNode(array('b'))
        )),
        new PropertyNode(array('c'))
    )),
    new PropertyNode(array('d'))
));


// Input
$input = 'a * b < c + d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('times', array(
        new PropertyNode(array('a')),
        new PropertyNode(array('b'))
    )),
    new FunctionNode('plus', array(
        new PropertyNode(array('c')),
        new PropertyNode(array('d'))
    ))
));


// Input
$input = 'a + b * c < d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('plus', array(
        new PropertyNode(array('a')),
        new FunctionNode('times', array(
            new PropertyNode(array('b')),
            new PropertyNode(array('c'))
        ))
    )),
    new PropertyNode(array('d'))
));


// Input
$input = 'a + b < c * d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('plus', array(
        new PropertyNode(array('a')),
        new PropertyNode(array('b'))
    )),
    new FunctionNode('times', array(
        new PropertyNode(array('c')),
        new PropertyNode(array('d'))
    ))
));


// Input
$input = 'a < b * c + d';

// Output
$output = new FunctionNode('less', array(
    new PropertyNode(array('a')),
    new FunctionNode('plus', array(
        new FunctionNode('times', array(
            new PropertyNode(array('b')),
            new PropertyNode(array('c'))
        )),
        new PropertyNode(array('d'))
    ))
));


// Input
$input = 'a < b + c * d';

// Output
$output = new FunctionNode('less', array(
    new PropertyNode(array('a')),
    new FunctionNode('plus', array(
        new PropertyNode(array('b')),
        new FunctionNode('times', array(
            new PropertyNode(array('c')),
            new PropertyNode(array('d'))
        ))
    ))
));


// Input
$input = '(a * b) + c < d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('plus', array(
        new FunctionNode('times', array(
            new PropertyNode(array('a')),
            new PropertyNode(array('b'))
        )),
        new PropertyNode(array('c'))
    )),
    new PropertyNode(array('d'))
));


// Input
$input = 'a * (b + c) < d';

// Output
$output = new FunctionNode('less', array(
    new FunctionNode('times', array(
        new PropertyNode(array('a')),
        new FunctionNode('plus', array(
            new PropertyNode(array('b')),
            new PropertyNode(array('c'))
        ))
    )),
    new PropertyNode(array('d'))
));


// Input
$input = 'a * b + (c < d)';

// Output
$output = new FunctionNode('plus', array(
    new FunctionNode('times', array(
        new PropertyNode(array('a')),
        new PropertyNode(array('b'))
    )),
    new FunctionNode('less', array(
        new PropertyNode(array('c')),
        new PropertyNode(array('d'))
    ))
));


// Input
$input = 'a or not b';

// Output
$output = new FunctionNode('or', array(
    new PropertyNode(array('a')),
    new FunctionNode('not', array(
        new PropertyNode(array('b'))
    ))
));


// Input
$input = 'not not a';

// Output
$output = new FunctionNode('not', array(
    new FunctionNode('not', array(
        new PropertyNode(array('a'))
    ))
));


// Input
$input = 'x + *';

// Output
throw Exception::invalidSyntax('unary-expression', $input, 4, '+');


// Input
$input = 'map(filter(partners, id > :arg), {"id": id, "test": test(clients, {"name": name})})';

// Output
$output = new FunctionNode('map', array(
    new FunctionNode('filter', array(
        new PropertyNode(array('partners')),
        new FunctionNode('greater', array(
            new PropertyNode(array('id')),
            new ParameterNode('arg')
        ))
    )),
    new ObjectNode(array(
        'id' => new PropertyNode(array('id')),
        'test' => new FunctionNode('test', array(
            new PropertyNode(array('clients')),
            new ObjectNode(array(
                'name' => new PropertyNode(array('name'))
            ))
        ))
    ))
));
