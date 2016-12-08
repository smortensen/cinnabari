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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Exception\LexerException;

/**
 * Class NewLexer
 * @package Datto\Cinnabari
 *
 * EBNF:
 *
 * expression = unary-expression, { expression-tail };
 * unary-expression = { unary-expression-head }, unit;
 * unary-expression-head = "not", space;
 * unit = function | property | parameter | object | group;
 * function = identifier, space, "(", space, [ arguments ], space, ")";
 * arguments = expression, { arguments-tail };
 * arguments-tail = space, ",", space, expression;
 * property = identifier, { property-tail };
 * property-tail = space, ".", space, identifier;
 * parameter = ":", identifier;
 * identifier = character, { character };
 * character = "a" | "b" | "c" | "d" | "e" | "f" | "g" | "h" | "i" | "j" | "k" | "l" | "m" | "n" | "o" |
 *     "p" | "q" | "r" | "s" | "t" | "u" | "v" | "w" | "x" | "y" | "z" | "A" | "B" | "C" | "D" |
 *     "E" | "F" | "G" | "H" | "I" | "J" | "K" | "L" | "M" | "N" | "O" | "P" | "Q" | "R" | "S" |
 *     "T" | "U" | "V" | "W" | "X" | "Y" | "Z" | "_" | "0" | "1" | "2" | "3" | "4" | "5" | "6" |
 *     "7" | "8" | "9";
 * object = "{", space, pairs, space, "}";
 * pairs = pair, { pairs-tail };
 * pairs-tail = space, ",", space, pair;
 * pair = json-string, space, ":", space, expression;
 * json-string = ? any JSON-encoded string value (including the enclosing quotation marks) ?;
 * space = ? any character matching the "\s*" regular expression ?;
 * group = "(", space, expression, space, ")";
 * expression-tail = space, binary-operator, space, unary-expression;
 * binary-operator = "+" | "-" | "*" | "/" | "<=" | "<" | "!=" | "=" | ">=" | ">" | "and" | "or";
 */

class NewLexer
{
    const TYPE_PARAMETER = 1;
    const TYPE_PROPERTY = 2;
    const TYPE_FUNCTION = 3;
    const TYPE_OBJECT = 4;
    const TYPE_OPERATOR = 5;
    const TYPE_GROUP = 6;

    /** @var string */
    private $input;

    /** @var array */
    private $output;

    /**
     * @param string $input
     * @return array
     * @throws LexerException
     */
    public function tokenize($input)
    {
        $originalInput = $input;

        if (!is_string($input)) {
            throw LexerException::typeInvalid($originalInput);
        }

        if (!self::getExpression($input, $output) || !self::isInputConsumed($input)) {
            $position = strlen($input) - strlen($originalInput);
            throw LexerException::syntaxInvalid($originalInput, $position);
        }

        return $output;
    }

    private static function getExpression(&$input, &$output)
    {
        $output = array();

        if (!self::getUnaryExpression($input, $output)) {
            return false;
        }

        while (self::getExpressionTail($input, $output)) {}

        return true;
    }

    private static function getUnaryExpression(&$input, &$output)
    {
        while (self::getUnaryExpressionHead($input, $output)) {}

        return self::getUnit($input, $output);
    }

    private static function getUnaryExpressionHead(&$input, &$output)
    {
        if (self::scan('(not)\s*', $input, $matches)) {
            $output[] = array(self::TYPE_OPERATOR => $matches[1]);
            return true;
        }

        return false;
    }

    private static function getUnit(&$input, &$output)
    {
        return self::getFunctionOrProperty($input, $output)
            || self::getParameter($input, $output)
            || self::getObject($input, $output)
            || self::getGroup($input, $output);
    }

    private static function getFunctionOrProperty(&$input, &$output)
    {
        return self::getIdentifier($input, $identifier) && (
            self::getFunction($identifier, $input, $output) ||
            self::getProperty($identifier, $input, $output)
        );
    }

    private static function getFunction($function, &$input, &$output)
    {
        if (!self::scan('\s*\(\s*', $input)) {
            return false;
        }

        $value = array($function);

        if (self::getExpression($input, $argument)) {
            $value[] = $argument;

            while (self::scan('\s*,\s*', $input)) {
                if (!self::getExpression($input, $value[])) {
                    return false;
                }
            }
        }

        if (!self::scan('\s*\)', $input)) {
            return false;
        }

        $output[] = array(self::TYPE_FUNCTION => $value);
        return true;
    }

    private static function getProperty($identifier, &$input, &$output)
    {
        $identifiers = array($identifier);

        while (self::scan('\s*\.\s*', $input)) {
            if (!self::getIdentifier($input, $identifiers[])) {
                return false;
            }
        }

        $output[] = array(self::TYPE_PROPERTY => $identifiers);
        return true;
    }

    private static function getParameter(&$input, &$output)
    {
        if (self::scan(':([a-zA-Z_0-9]+)', $input, $matches)) {
            $output[] = array(self::TYPE_PARAMETER => $matches[1]);
            return true;
        }

        return false;
    }

    private static function getObject(&$input, &$output)
    {
        if (
            self::scan('{\s*', $input)
            && self::getPairs($input, $properties)
            && self::scan('\s*}', $input)
        ) {
            $output[] = array(self::TYPE_OBJECT => $properties);
            return true;
        }

        return false;
    }

    private static function getPairs(&$input, &$output)
    {
        $output = array();

        if (!self::getPair($input, $output)) {
            return false;
        }

        while (self::scan('\s*,\s*', $input)) {
            if (!self::getPair($input, $output)) {
                return false;
            }
        }

        return true;
    }

    private static function getPair(&$input, &$output)
    {
        return self::getJsonString($input, $key)
            && self::scan('\s*:\s*', $input)
            && self::getExpression($input, $output[$key]);
    }

    private static function getJsonString(&$input, &$output)
    {
        $expression = '\\"(?:[^"\\x00-\\x1f\\\\]|\\\\(?:["\\\\/bfnrt]|u[0-9a-f]{4}))*\\"';

        if (self::scan($expression, $input, $matches)) {
            $output = json_decode($matches[0], true);
            return true;
        }

        return false;
    }

    private static function getGroup(&$input, &$output)
    {
        if (
            self::scan('\(\s*', $input)
            && self::getExpression($input, $expression)
            && self::scan('\s*\)', $input)
        ) {
            $output[] = array(self::TYPE_GROUP => $expression);
            return true;
        }

        return false;
    }

    private static function getIdentifier(&$input, &$output)
    {
        if (self::scan('[a-zA-Z_0-9]+', $input, $matches)) {
            $output = $matches[0];
            return true;
        }

        return false;
    }

    private static function getExpressionTail(&$input, &$output)
    {
        return self::getBinaryOperator($input, $output)
            && self::getUnaryExpression($input, $output);
    }

    private static function getBinaryOperator(&$input, &$output)
    {
        if (self::scan('\s*([-+*/]|and|or|<=|<|!=|=|>=|>)\s*', $input, $matches)) {
            $output[] = array(self::TYPE_OPERATOR => $matches[1]);
            return true;
        }

        return false;
    }

    private static function isInputConsumed($input)
    {
        return ($input === false) || ($input === '');
    }

    private static function scan($expression, &$input, &$output = null)
    {
        $delimiter = "\x03";
        $flags = 'A'; // A: anchored

        $pattern = "{$delimiter}{$expression}{$delimiter}{$flags}";

        if (preg_match($pattern, $input, $matches) !== 1) {
            return false;
        }

        $length = strlen($matches[0]);
        $input = substr($input, $length);
        $output = $matches;

        return true;
    }
}
