<?php

/**
 * Copyright (C) 2016, 2017 Datto, Inc.
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
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari;

use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Language\Request\FunctionToken;
use Datto\Cinnabari\Language\Request\ObjectToken;
use Datto\Cinnabari\Language\Request\OperatorToken;
use Datto\Cinnabari\Language\Request\ParameterToken;
use Datto\Cinnabari\Language\Request\PropertyToken;
use Datto\Cinnabari\Language\Request\Token;

/**
 * Class Parser
 * @package Datto\Cinnabari
 *
 * EBNF:
 *
 * expression = unary-expression, { expression-tail };
 * unary-expression = { unary-expression-head }, unit;
 * unary-expression-head = "not", space;
 * unit = function | property | parameter | object | group;
 * function = identifier, "(", optional-space, [ arguments ], optional-space, ")";
 * arguments = expression, { arguments-tail };
 * arguments-tail = ",", space, expression;
 * property = identifier, { property-tail };
 * property-tail = optional-space, ".", optional-space, identifier;
 * parameter = ":", identifier;
 * identifier = character, { character };
 * character = "a" | "b" | "c" | "d" | "e" | "f" | "g" | "h" | "i" | "j" | "k" | "l" | "m" | "n" | "o" |
 *     "p" | "q" | "r" | "s" | "t" | "u" | "v" | "w" | "x" | "y" | "z" | "A" | "B" | "C" | "D" |
 *     "E" | "F" | "G" | "H" | "I" | "J" | "K" | "L" | "M" | "N" | "O" | "P" | "Q" | "R" | "S" |
 *     "T" | "U" | "V" | "W" | "X" | "Y" | "Z" | "_" | "0" | "1" | "2" | "3" | "4" | "5" | "6" |
 *     "7" | "8" | "9";
 * object = "{", optional-space, pairs, optional-space, "}";
 * pairs = pair, { pairs-tail };
 * pair = json-string, optional-space, ":", optional-space, expression;
 * json-string = ? any JSON-encoded string value (including the enclosing quotation marks) ?;
 * pairs-tail = ",", optional-space, pair;
 * group = "(", optional-space, expression, optional-space, ")";
 * optional-space = ? any character matching the "\s*" regular expression ?;
 * expression-tail = space, binary-operator, space, unary-expression;
 * space = ? any character matching the "\s+" regular expression ?;
 * binary-operator = "+" | "-" | "*" | "/" | "<=" | "<" | "!=" | "=" | ">=" | ">" | "and" | "or";
 */
class Parser
{
    /** @var Operators */
    private $operators;

    /** @var string */
    private $input;

    /** @var string */
    private $state;

    public function __construct(Operators $operators)
    {
        $this->operators = $operators;
    }

    /**
     * @param string $input
     * @return Token
     * @throws Exception
     */
    public function parse($input)
    {
        if (!is_string($input)) {
            throw Exception::invalidType($input);
        }

        $this->input = $input;
        $this->state = $input;

        if (!$this->getExpression($output) || !$this->isInputConsumed()) {
            throw $this->exceptionInvalidSyntax();
        }

        return $output;
    }

    private function getExpression(&$output)
    {
        $tokens = array();

        if (!$this->getUnaryExpression($tokens)) {
            return false;
        }

        while ($this->getExpressionTail($tokens)) {}

        $tokens = $this->sort($tokens);
        $output = $this->getTokenExpression($tokens);

        return true;
    }

    private function getUnaryExpression(&$output)
    {
        while ($this->getUnaryExpressionHead($output)) {}

        return $this->getUnit($output);
    }

    private function getUnaryExpressionHead(&$output)
    {
        if ($this->scan('(not)\s+', $matches)) {
            $output[] = new OperatorToken($matches[1]);
            return true;
        }

        return false;
    }

    private function getUnit(&$output)
    {
        return $this->getFunctionOrProperty($output)
            || $this->getParameter($output)
            || $this->getObject($output)
            || $this->getGroup($output);
    }

    private function getFunctionOrProperty(&$output)
    {
        return $this->getIdentifier($identifier) && (
            $this->getFunction($identifier, $output) ||
            $this->getProperty($identifier, $output)
        );
    }

    private function getFunction($name, &$output)
    {
        if (!$this->scan('\(\s*')) {
            return false;
        }

        $arguments = array();

        if ($this->getExpression($argument)) {
            $arguments[] = $argument;

            while ($this->scan(',\s+')) {
                if (!$this->getExpression($arguments[])) {
                    throw $this->exceptionInvalidSyntax();
                }
            }
        }

        if (!$this->scan('\s*\)')) {
            throw $this->exceptionInvalidSyntax();
        }

        $output[] = new FunctionToken($name, $arguments);
        return true;
    }

    private function getProperty($identifier, &$output)
    {
        $path = array($identifier);

        while ($this->scan('\s*\.\s*')) {
            if (!$this->getIdentifier($path[])) {
                throw $this->exceptionInvalidSyntax();
            }
        }

        $output[] = new PropertyToken($path);
        return true;
    }

    private function getParameter(&$output)
    {
        if (!$this->scan(':([a-zA-Z_0-9]+)', $matches)) {
            return false;
        }

        $output[] = new ParameterToken($matches[1]);
        return true;
    }

    private function getObject(&$output)
    {
        if (!$this->scan('{\s*')) {
            return false;
        }

        $properties = array();

        if (!$this->getPair($properties)) {
            throw $this->exceptionInvalidSyntax();
        }

        while ($this->scan(',\s*')) {
            if (!$this->getPair($properties)) {
                throw $this->exceptionInvalidSyntax();
            }
        }

        if (!$this->scan('\s*}')) {
            throw $this->exceptionInvalidSyntax();
        }

        $output[] = new ObjectToken($properties);
        return true;
    }

    private function getPair(&$output)
    {
        return $this->getJsonString($key)
            && $this->scan('\s*:\s*')
            && $this->getExpression($output[$key]);
    }

    private function getJsonString(&$output)
    {
        $expression = '\\"(?:[^"\\x00-\\x1f\\\\]|\\\\(?:["\\\\/bfnrt]|u[0-9a-f]{4}))*\\"';

        if ($this->scan($expression, $matches)) {
            $output = json_decode($matches[0], true);
            return true;
        }

        return false;
    }

    private function getGroup(&$output)
    {
        if (!$this->scan('\(\s*')) {
            return false;
        }

        if (
            !$this->getExpression($expression) ||
            !$this->scan('\s*\)')
        ) {
            throw $this->exceptionInvalidSyntax();
        }

        $output[] = $expression;
        return true;
    }

    private function getIdentifier(&$output)
    {
        if ($this->scan('[a-zA-Z_0-9]+', $matches)) {
            $output = $matches[0];
            return true;
        }

        return false;
    }

    private function getExpressionTail(&$output)
    {
        $state = $this->state;

        if (
            $this->getBinaryOperator($output)
            && $this->getUnaryExpression($output)
        ) {
            return true;
        }

        $this->state = $state;
        return false;
    }

    private function getBinaryOperator(&$output)
    {
        if ($this->scan('\s+([-+*/]|and|or|<=|<|!=|=|>=|>)\s+', $matches)) {
            $output[] = new OperatorToken($matches[1]);
            return true;
        }

        return false;
    }

    /**
     * @param Token[] $tokens
     * @return Token[]
     */
    private function sort(array $tokens)
    {
        if (count($tokens) <= 1) {
            return $tokens;
        }

        $operators = array();
        $output = array();

        for ($i = count($tokens) - 1; -1 < $i; --$i) {
            $token = $tokens[$i];

            if ($token->getTokenType() === Token::TYPE_OPERATOR) {
                $this->releaseWeakerOperators($token, $operators, $output);
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

    /**
     * @param OperatorToken $operator
     * @param OperatorToken[] $operators
     * @param Token[] $output
     */
    private function releaseWeakerOperators(OperatorToken $operator, array &$operators, array &$output)
    {
        $precedenceA = $this->getOperatorPrecedence($operator);

        for ($i = count($operators) - 1; -1 < $i; --$i) {
            $precedenceB = $this->getOperatorPrecedence($operators[$i]);

            if ($precedenceA <= $precedenceB) {
                break;
            }

            $output[] = array_pop($operators);
        }
    }

    /**
     * @param OperatorToken $token
     * @return integer
     */
    private function getOperatorPrecedence(OperatorToken $token)
    {
        $lexeme = $token->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['precedence'];
    }

    /**
     * @param OperatorToken $token
     * @return string
     */
    private function getOperatorName(OperatorToken $token)
    {
        $lexeme = $token->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['name'];
    }

    /**
     * @param OperatorToken $token
     * @return integer
     */
    private function getOperatorArity(OperatorToken $token)
    {
        $lexeme = $token->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['arity'];
    }

    /**
     * @param Token[] $tokens
     * @return Token
     */
    private function getTokenExpression(array &$tokens)
    {
        $token = array_pop($tokens);

        if ($token->getTokenType() === Token::TYPE_OPERATOR) {
            $token = $this->getFunctionFromOperator($token, $tokens);
        }

        return $token;
    }

    /**
     * @param OperatorToken $token
     * @param Token[] $tokens
     * @return FunctionToken
     */
    private function getFunctionFromOperator(OperatorToken $token, array &$tokens)
    {
        $arity = $this->getOperatorArity($token);
        $name = $this->getOperatorName($token);

        if ($arity === Operators::BINARY) {
            $arguments = array(
                $this->getTokenExpression($tokens),
                $this->getTokenExpression($tokens)
            );
        } else {
            $arguments = array(
                $this->getTokenExpression($tokens)
            );
        }

        return new FunctionToken($name, $arguments);
    }

    private function isInputConsumed()
    {
        return strlen($this->state) === 0;
    }

    /**
     * @return Exception
     */
    private function exceptionInvalidSyntax()
    {
        $position = strlen($this->input) - strlen($this->state);

        return Exception::invalidSyntax($this->input, $position);
    }

    private function scan($expression, &$output = null)
    {
        $delimiter = "\x03";
        $flags = 'A'; // A: anchored

        $pattern = "{$delimiter}{$expression}{$delimiter}{$flags}";

        if (preg_match($pattern, $this->state, $matches) !== 1) {
            return false;
        }

        $output = $matches;
        $length = strlen($matches[0]);
        $this->state = (string)substr($this->state, $length);

        return true;
    }
}
