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

namespace Datto\Cinnabari\Request;

use Datto\Cinnabari\Exception;

/**
 * Class Lexer
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
class Lexer
{
    const TYPE_PARAMETER = 1;
    const TYPE_PROPERTY = 2;
    const TYPE_FUNCTION = 3;
    const TYPE_OBJECT = 4;
    const TYPE_OPERATOR = 5;
    const TYPE_GROUP = 6;

    /** @var string */
    private $input;

    /**
     * @param string $input
     * @return array
     * @throws Exception
     */
    public function tokenize($input)
    {
        if (!is_string($input)) {
            throw Exception::invalidType($input);
        }

        $this->input = $input;

        if (!$this->getExpression($input, $output) || !$this->isInputConsumed($input)) {
            throw $this->exceptionInvalidSyntax($input);
        }

        return $output;
    }

    private function getExpression(&$input, &$output)
    {
        $output = array();

        if (!$this->getUnaryExpression($input, $output)) {
            return false;
        }

        while ($this->getExpressionTail($input, $output)) {}

        return true;
    }

    private function getUnaryExpression(&$input, &$output)
    {
        while ($this->getUnaryExpressionHead($input, $output)) {}

        return $this->getUnit($input, $output);
    }

    private function getUnaryExpressionHead(&$input, &$output)
    {
        if (self::scan('(not)\s*', $input, $matches)) {
            $output[] = array(self::TYPE_OPERATOR => $matches[1]);
            return true;
        }

        return false;
    }

    private function getUnit(&$input, &$output)
    {
        return $this->getFunctionOrProperty($input, $output)
            || $this->getParameter($input, $output)
            || $this->getObject($input, $output)
            || $this->getGroup($input, $output);
    }

    private function getFunctionOrProperty(&$input, &$output)
    {
        return $this->getIdentifier($input, $identifier) && (
            $this->getFunction($identifier, $input, $output) ||
            $this->getProperty($identifier, $input, $output)
        );
    }

    private function getFunction($function, &$input, &$output)
    {
        if (!self::scan('\s*\(\s*', $input)) {
            return false;
        }

        $value = array($function);

        if ($this->getExpression($input, $argument)) {
            $value[] = $argument;

            while (self::scan('\s*,\s*', $input)) {
                if (!$this->getExpression($input, $value[])) {
                    throw $this->exceptionInvalidSyntax($input);
                }
            }
        }

        if (!self::scan('\s*\)', $input)) {
            throw $this->exceptionInvalidSyntax($input);
        }

        $output[] = array(self::TYPE_FUNCTION => $value);
        return true;
    }

    private function getProperty($identifier, &$input, &$output)
    {
        $identifiers = array($identifier);

        while (self::scan('\s*\.\s*', $input)) {
            if (!$this->getIdentifier($input, $identifiers[])) {
                throw $this->exceptionInvalidSyntax($input);
            }
        }

        $output[] = array(self::TYPE_PROPERTY => $identifiers);
        return true;
    }

    private function getParameter(&$input, &$output)
    {
        if (self::scan(':([a-zA-Z_0-9]+)', $input, $matches)) {
            $output[] = array(self::TYPE_PARAMETER => $matches[1]);
            return true;
        }

        return false;
    }

    private function getObject(&$input, &$output)
    {
        if (!self::scan('{\s*', $input)) {
            return false;
        }

        $properties = array();

        if (!$this->getPair($input, $properties)) {
            throw $this->exceptionInvalidSyntax($input);
        }

        while (self::scan('\s*,\s*', $input)) {
            if (!$this->getPair($input, $properties)) {
                throw $this->exceptionInvalidSyntax($input);
            }
        }

        if (!self::scan('\s*}', $input)) {
            throw $this->exceptionInvalidSyntax($input);
        }

        $output[] = array(self::TYPE_OBJECT => $properties);
        return true;
    }

    private function getPair(&$input, &$output)
    {
        return $this->getJsonString($input, $key)
            && self::scan('\s*:\s*', $input)
            && $this->getExpression($input, $output[$key]);
    }

    private function getJsonString(&$input, &$output)
    {
        $expression = '\\"(?:[^"\\x00-\\x1f\\\\]|\\\\(?:["\\\\/bfnrt]|u[0-9a-f]{4}))*\\"';

        if (self::scan($expression, $input, $matches)) {
            $output = json_decode($matches[0], true);
            return true;
        }

        return false;
    }

    private function getGroup(&$input, &$output)
    {
        if (!self::scan('\(\s*', $input)) {
            return false;
        }

        if (
            !$this->getExpression($input, $expression) ||
            !self::scan('\s*\)', $input)
        ) {
            throw $this->exceptionInvalidSyntax($input);
        }

        $output[] = array(self::TYPE_GROUP => $expression);
        return true;
    }

    private function getIdentifier(&$input, &$output)
    {
        if (self::scan('[a-zA-Z_0-9]+', $input, $matches)) {
            $output = $matches[0];
            return true;
        }

        return false;
    }

    private function getExpressionTail(&$input, &$output)
    {
        $originalInput = $input;

        if (
            $this->getBinaryOperator($input, $output)
            && $this->getUnaryExpression($input, $output)
        ) {
            return true;
        }

        $input = $originalInput;
        return false;
    }

    private function getBinaryOperator(&$input, &$output)
    {
        if (self::scan('\s*([-+*/]|and|or|<=|<|!=|=|>=|>)\s*', $input, $matches)) {
            $output[] = array(self::TYPE_OPERATOR => $matches[1]);
            return true;
        }

        return false;
    }

    private function isInputConsumed($input)
    {
        return ($input === false) || ($input === '');
    }

    /**
     * @param string $input
     * @return Exception
     */
    private function exceptionInvalidSyntax($input)
    {
        $position = strlen($this->input) - strlen($input);

        return Exception::invalidSyntax($this->input, $position);
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
