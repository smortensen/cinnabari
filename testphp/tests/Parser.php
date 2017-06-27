<?php

namespace Datto\Cinnabari;

require TESTPHP_TESTS_DIRECTORY . '/autoload.php';

use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Parser\Tokens\FunctionToken;
use Datto\Cinnabari\Parser\Tokens\ObjectToken;
use Datto\Cinnabari\Parser\Tokens\ParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken;

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
throw Exception::invalidSyntax($input, 0);


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
throw Exception::invalidSyntax($input, 0);


// Input
$input = ':';

// Output
throw Exception::invalidSyntax($input, 0);


// Input
$input = ':x ';

// Output
throw Exception::invalidSyntax($input, 2);


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
throw Exception::invalidSyntax($input, 0);


// Input
$input = 'x .';

// Output
throw Exception::invalidSyntax($input, 3);


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
throw Exception::invalidSyntax($input, 2);


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
throw Exception::invalidSyntax($input, 6);


// Input
$input = 'f(';

// Output
throw Exception::invalidSyntax($input, 2);


// Input
$input = 'x.f()';

// Output
throw Exception::invalidSyntax($input, 3);


// Input
$input = '(';

// Output
throw Exception::invalidSyntax($input, 1);


// Input
$input = '()';

// Output
throw Exception::invalidSyntax($input, 1);


// Input
$input = '(:x)';

// Output
$output = new ParameterToken('x');


// Input
$input = '{}';

// Output
throw Exception::invalidSyntax($input, 1);


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
$input = '{6: x}';

// Output
throw Exception::invalidSyntax($input, 1);


// Input
$input = '{"x" x}';

// Output
throw Exception::invalidSyntax($input, 4);


// Input
$input = '{"x": *}';

// Output
throw Exception::invalidSyntax($input, 6);


// Input
$input = '{"x": x';

// Output
throw Exception::invalidSyntax($input, 7);


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
$input = '{"x": :x "x": x}';

// Output
throw Exception::invalidSyntax($input, 8);


// Input
$input = '{"x": :x, }';

// Output
throw Exception::invalidSyntax($input, 10);


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
