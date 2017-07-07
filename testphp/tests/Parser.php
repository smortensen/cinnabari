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
$input = 'f(:x, y)';

// Output
$output = new FunctionToken('f', array(
    new ParameterToken('x'),
    new PropertyToken(array('y'))
));


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
    'x' => new ParameterToken('x'),
    'x' => new PropertyToken(array('x'))
));


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
