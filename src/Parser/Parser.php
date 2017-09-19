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
 * @author Griffin Bishop <gbishop@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Parser;

use Datto\Cinnabari\AbstractRequest\Node;
use Datto\Cinnabari\AbstractRequest\Nodes\FunctionNode;
use Datto\Cinnabari\AbstractRequest\Nodes\ObjectNode;
use Datto\Cinnabari\AbstractRequest\Nodes\OperatorNode;
use Datto\Cinnabari\AbstractRequest\Nodes\ParameterNode;
use Datto\Cinnabari\AbstractRequest\Nodes\PropertyNode;
use Datto\Cinnabari\AbstractRequest\RenderNode;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Parser\Language\Operators;

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

    /** @var integer */
    private $watermarkPosition;

    /** @var string */
    private $watermarkExpected;

    /** @var string */
    private $lastParsed;

    public function __construct(Operators $operators)
    {
        $this->operators = $operators;
        $this->watermarkPosition = 0;
        $this->watermarkExpected = 'expression';
        $this->lastParsed = '';
    }

    /**
     * @param string $input
     * @return Node
     * @throws Exception
     */
    public function parse($input)
    {
        if (!is_string($input)) {
            throw Exception::invalidType($input);
        }

        $this->input = $input;
        $this->state = $input;

        if (!$this->getExpression($output)) {
            throw $this->exceptionInvalidSyntax();
        }

        if (!$this->isInputConsumed()) {
            $this->recordWatermark('end', RenderNode::render($output));
            throw $this->exceptionInvalidSyntax();
        }

        return $output;
    }

    private function getExpression(&$output)
    {
        $nodes = array();

        if (!$this->getUnaryExpression($nodes)) {
            return false;
        }

        while ($this->getExpressionTail($nodes)) {
        }

        $nodes = $this->sort($nodes);
        $output = $this->getNodeExpression($nodes);

        return true;
    }

    private function getUnaryExpression(&$output)
    {
        while ($this->getUnaryExpressionHead($output)) {
        }

        return $this->getUnit($output);
    }

    private function getUnaryExpressionHead(&$output)
    {
        if ($this->scan('(not)\s+', $matches)) {
            $this->recordWatermark('expression', '"not "');
            $output[] = new OperatorNode($matches[1]);
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

        $this->recordWatermark('argument');
        if ($this->getExpression($argument)) {
            $arguments[] = $argument;

            $this->recordWatermark('function-comma', RenderNode::render($arguments[0]));
            $i = 0;
            while ($this->scan(',\s+')) {
                if (!$this->getExpression($arguments[])) {

                    $lastParsed = RenderNode::render($arguments[$i]).", ";
                    $this->recordWatermark('argument', $lastParsed);
                    throw $this->exceptionInvalidSyntax();
                }
                $i++;
            }
        }

        if (!$this->scan('\s*\)')) {
            throw $this->exceptionInvalidSyntax();
        }

        $output[] = new FunctionNode($name, $arguments);
        return true;
    }

    private function getProperty($identifier, &$output)
    {
        $path = array($identifier);

        while ($this->scan('\s*\.\s*')) {
            if (!$this->getIdentifier($path[])) {
                $this->recordWatermark('property');
                throw $this->exceptionInvalidSyntax();
            }
        }

        $output[] = new PropertyNode($path);
        return true;
    }

    private function getParameter(&$output)
    {
        if (!$this->scan(':', $matches)) {
            return false;
        }

        if (!$this->scan('([a-zA-Z_0-9]+)', $matches)) {
            $this->recordWatermark('parameter');
            return false;
        }

        $output[] = new ParameterNode($matches[1]);
        return true;
    }

    private function getObject(&$output)
    {
        if (!$this->scan('{\s*')) {
            return false;
        }

        $properties = array();

        $this->recordWatermark('object-element', '{');

        if (!$this->getPair($properties)) {
            throw $this->exceptionInvalidSyntax();
        }

        $keys = array_keys($properties);
        $lastParsed = '"'.$keys[0].'": '.RenderNode::render($properties[$keys[0]]);
        $this->recordWatermark('object-comma', $lastParsed);
        $i = 0;
        while ($this->scan(',\s*')) {
            if (!$this->getPair($properties)) {

                $keys = array_keys($properties);
                $lastParsed = '"'.$keys[0].'": '.RenderNode::render($properties[$keys[0]]).',';
                $this->recordWatermark('object-element', $lastParsed);
                throw $this->exceptionInvalidSyntax();
            }
            $i++;
        }


        if (!$this->scan('\s*}')) {
            throw $this->exceptionInvalidSyntax();
        }

        $output[] = new ObjectNode($properties);
        return true;
    }

    private function getPair(&$output)
    {
        if (!$this->getJsonString($key)) {
            return false;
        }

        $this->recordWatermark('pair-colon', "\"$key\"");

        if (!$this->scan('\s*:\s*')) {
            return false;
        }

        $this->recordWatermark('pair-property', "\"$key\":");

        return $this->getExpression($output[$key]);
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

        if (!$this->getExpression($expression) ||
            !$this->scan('\s*\)')
        ) {
            $this->recordWatermark('group-expression');
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

        if ($this->getBinaryOperator($output)) {
            $lastParsed = RenderNode::render(end($output));
            $this->recordWatermark('unary-expression', $lastParsed);

            if ($this->getUnaryExpression($output)) {
                return true;
            } else {
                throw $this->exceptionInvalidSyntax();
            }
        }

        $this->state = $state;
        return false;
    }

    private function getBinaryOperator(&$output)
    {
        if ($this->scan('\s+([-+*/]|and|or|<=|<|!=|=|>=|>)\s+', $matches)) {
            $output[] = new OperatorNode($matches[1]);
            return true;
        }

        return false;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function sort(array $nodes)
    {
        if (count($nodes) <= 1) {
            return $nodes;
        }

        $operators = array();
        $output = array();

        for ($i = count($nodes) - 1; -1 < $i; --$i) {
            $node = $nodes[$i];

            if ($node->getNodeType() === Node::TYPE_OPERATOR) {
                $this->releaseWeakerOperators($node, $operators, $output);
                $operators[] = $node;
            } else {
                $output[] = $node;
            }
        }

        while (0 < count($operators)) {
            $output[] = array_pop($operators);
        }

        return $output;
    }

    /**
     * @param OperatorNode $operator
     * @param OperatorNode[] $operators
     * @param Node[] $output
     */
    private function releaseWeakerOperators(OperatorNode $operator, array &$operators, array &$output)
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
     * @param OperatorNode $node
     * @return integer
     */
    private function getOperatorPrecedence(OperatorNode $node)
    {
        $lexeme = $node->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['precedence'];
    }

    /**
     * @param OperatorNode $node
     * @return string
     */
    private function getOperatorName(OperatorNode $node)
    {
        $lexeme = $node->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['name'];
    }

    /**
     * @param OperatorNode $node
     * @return integer
     */
    private function getOperatorArity(OperatorNode $node)
    {
        $lexeme = $node->getLexeme();
        $operator = $this->operators->getOperator($lexeme);
        return $operator['arity'];
    }

    /**
     * @param Node[] $nodes
     * @return Node
     */
    private function getNodeExpression(array &$nodes)
    {
        $node = array_pop($nodes);

        if ($node->getNodeType() === Node::TYPE_OPERATOR) {
            $node = $this->getFunctionFromOperator($node, $nodes);
        }

        return $node;
    }

    /**
     * @param OperatorNode $node
     * @param Node[] $nodes
     * @return FunctionNode
     */
    private function getFunctionFromOperator(OperatorNode $node, array &$nodes)
    {
        $arity = $this->getOperatorArity($node);
        $name = $this->getOperatorName($node);

        if ($arity === Operators::BINARY) {
            $arguments = array(
                $this->getNodeExpression($nodes),
                $this->getNodeExpression($nodes)
            );
        } else {
            $arguments = array(
                $this->getNodeExpression($nodes)
            );
        }

        return new FunctionNode($name, $arguments);
    }

    private function isInputConsumed()
    {
        return strlen($this->state) === 0;
    }

    /**
     * Record the point of the last thing to successfully be parsed, and what
     * the parser was expecting at that point.
     *
     * @param string $expected
     * String representing what the parser is expecting next.
     *
     * @param string $lastParsed
     * String reflecting the last item the parser understood.
     */
    private function recordWatermark($expected, $lastParsed = null)
    {
        $position = strlen($this->input) - strlen($this->state);

        if ($this->watermarkPosition < $position) {
            $this->watermarkPosition = $position;
            $this->watermarkExpected = $expected;
            $this->lastParsed = $lastParsed;
        }
    }

    /**
     * @return Exception
     */
    private function exceptionInvalidSyntax()
    {
        return Exception::invalidSyntax($this->watermarkExpected, $this->input, $this->watermarkPosition, $this->lastParsed);
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
