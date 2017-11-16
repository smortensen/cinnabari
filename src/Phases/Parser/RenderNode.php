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
 * @author Griffin Bishop <gbishop@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Phases\Parser;

use Datto\Cinnabari\Entities\Request\FunctionRequest;
use Datto\Cinnabari\Entities\Request\Request;
use Datto\Cinnabari\Entities\Request\ObjectRequest;
use Datto\Cinnabari\Entities\Request\OperatorRequest;
use Datto\Cinnabari\Entities\Request\ParameterRequest;
use Datto\Cinnabari\Entities\Request\PropertyRequest;

class RenderNode
{
    /**
     * Produces a string representation of the given node
     *
     * @param Request $node
     * @return string
     */
    public static function render(Request $node)
    {
        $type = $node->getNodeType();

        switch ($type) {
            case Request::TYPE_PARAMETER:
                /** @var ParameterRequest $node */
                return self::renderParameter($node);

            case Request::TYPE_PROPERTY:
                /** @var PropertyRequest $node */
                return self::renderProperty($node);

            case Request::TYPE_FUNCTION:
                /** @var FunctionRequest $node */
                return self::renderFunction($node);

            case Request::TYPE_OBJECT:
                /** @var Object $node */
                return self::renderObject($node);

            default: // Node::TYPE_OPERATOR:
                /** @var OperatorRequest $node */
                return self::renderOperator($node);
        }
    }

    private static function renderParameter(ParameterRequest $node)
    {
        $name = $node->getName();

        return ":{$name}";
    }

    private static function renderProperty(PropertyRequest $node)
    {
        $path = $node->getPath();

        return implode('.', $path);
    }

    private static function renderFunction(FunctionRequest $node)
    {
        $name = $node->getFunction();
        $arguments = $node->getArguments();

        $renderedArguments = array_map('self::render', $arguments);
        $argumentList = implode(', ', $renderedArguments);

        return "{$name}({$argumentList})";
    }

    /**
     * Produces a string representation of the given object node
     *
     * @param ObjectRequest $node
     * @return string
     */
    private static function renderObject(ObjectRequest $node)
    {
        $output = array();

        $properties = $node->getProperties();

        foreach ($properties as $key => $value) {
            $renderedValue = self::render($value);

            $output[] = "\"{$key}\": {$renderedValue}";
        }

        return '{' . implode(', ', $output) . '}';
    }

    private static function renderOperator(OperatorRequest $node)
    {
        return $node->getLexeme();
    }
}
