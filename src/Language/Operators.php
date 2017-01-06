<?php

/**
 * Copyright (C) 2016 Datto, Inc.
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
 * @author Spencer Mortensen <smortensen@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016 Datto, Inc.
 */

namespace Datto\Cinnabari\Language;

use Datto\Cinnabari\Exception\LanguageException;

class Operators
{
    // Operator arity
    const UNARY = 1;
    const BINARY = 2;

    private static $operators = array(
        '*' => array(
            'name' => 'times',
            'precedence' => 6,
            'arity' => self::BINARY
        ),
        '/' => array(
            'name' => 'divides',
            'precedence' => 6,
            'arity' => self::BINARY
        ),
        '+' => array(
            'name' => 'plus',
            'precedence' => 5,
            'arity' => self::BINARY
        ),
        '-' => array(
            'name' => 'minus',
            'precedence' => 5,
            'arity' => self::BINARY
        ),
        '<' => array(
            'name' => 'less',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        '<=' => array(
            'name' => 'lessEqual',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        '=' => array(
            'name' => 'equal',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        '!=' => array(
            'name' => 'notEqual',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        '>=' => array(
            'name' => 'greaterEqual',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        '>' => array(
            'name' => 'greater',
            'precedence' => 4,
            'arity' => self::BINARY
        ),
        'not' => array(
            'name' => 'not',
            'precedence' => 3,
            'arity' => self::UNARY
        ),
        'and' => array(
            'name' => 'and',
            'precedence' => 2,
            'arity' => self::BINARY
        ),
        'or' => array(
            'name' => 'or',
            'precedence' => 1,
            'arity' => self::BINARY
        )
    );

    public function getOperator($symbol)
    {
        $definition = &self::$operators[$symbol];

        if (!isset($definition)) {
            throw LanguageException::unknownOperator($symbol);
        }

        return $definition;
    }
}
