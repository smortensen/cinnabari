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

use Datto\Cinnabari\Language\Operators;

class Parser
{
    // Token types
    const TYPE_PARAMETER = 1;
    const TYPE_PROPERTY = 2;
    const TYPE_FUNCTION = 3;
    const TYPE_OBJECT = 4;

    /** @var Operators */
    private $operators;

    public function __construct(Operators $operators)
    {
        $this->operators = $operators;
    }

    public function parse($tokens)
    {
        return $this->getExpression($tokens);
    }

    private function getExpression($tokens)
    {
        $tokens = $this->sortTokens($tokens);
        return $this->getExpressionFromSortedTokens($tokens);
    }

    private function sortTokens($input)
    {
        if (count($input) <= 1) {
            return $input;
        }

        $operators = array();
        $output = array();

        foreach ($input as $token) {
            $type = key($token);

            if ($type === Lexer::TYPE_OPERATOR) {
                $this->releaseOperators($token, $operators, $output);
                $operators[] = $token;
            } else {
                $output[] = $token;
            }
        }

        while (0 < count($operators)) {
            $output[] = array_pop($operators);
        }

        return $output;
    }

    private function releaseOperators($token, &$tokens, &$output)
    {
        $operatorA = $this->getOperator($token);
        $precedenceA = $operatorA['precedence'];

        for ($i = count($tokens) - 1; -1 < $i; --$i) {
            $operatorB = $this->getOperator($tokens[$i]);
            $precedenceB = $operatorB['precedence'];

            if ($precedenceA < $precedenceB) {
                $output[] = array_pop($tokens);
            }
        }
    }

    private function getOperator($token)
    {
        $lexeme = current($token);

        return $this->operators->getOperator($lexeme);
    }

    private function getExpressionFromSortedTokens(&$tokens)
    {
        $token = array_pop($tokens);

        list($type, $value) = each($token);

        switch ($type) {
            case Lexer::TYPE_PARAMETER:
                return $this->getParameterExpression($value);

            case Lexer::TYPE_PROPERTY:
                return $this->getPropertyExpression($value);

            case Lexer::TYPE_FUNCTION:
                return $this->getFunctionExpression($value);

            case Lexer::TYPE_OBJECT:
                return $this->getObjectExpression($value);

            case Lexer::TYPE_GROUP:
                return $this->getExpression($value);

            default: // Lexer::TYPE_OPERATOR:
                return $this->getOperatorExpression($value, $tokens);
        }
    }

    private function getParameterExpression($name)
    {
        return array(self::TYPE_PARAMETER, $name);
    }

    private function getPropertyExpression($path)
    {
        return array(self::TYPE_PROPERTY, $path);
    }

    private function getFunctionExpression($input)
    {
        $name = array_shift($input);

        $arguments = array();

        foreach ($input as $tokens) {
            $arguments[] = $this->getExpression($tokens);
        }

        return array(self::TYPE_FUNCTION, $name, $arguments);
    }

    private function getObjectExpression($input)
    {
        $output = array();

        foreach ($input as $property => $tokens) {
            $output[$property] = $this->getExpression($tokens);
        }

        return array(self::TYPE_OBJECT, $output);
    }

    private function getOperatorExpression($lexeme, &$tokens)
    {
        $operator = $this->operators->getOperator($lexeme);
        $name = $operator['name'];

        if ($operator['arity'] === Operators::BINARY) {
            // Binary operator
            $childB = $this->getExpressionFromSortedTokens($tokens);
            $childA = $this->getExpressionFromSortedTokens($tokens);

            $arguments = array($childA, $childB);
        } else {
            // Unary operator
            $child = $this->getExpressionFromSortedTokens($tokens);

            $arguments = array($child);
        }

        return array(self::TYPE_FUNCTION, $name, $arguments);
    }
}
