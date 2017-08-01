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

namespace Datto\Cinnabari\Language\Request;

class RenderToken
{
    /**
     * Produces a string representation of the given token
     *
     * @param Token $token
     * @return string
     */
    public static function render(Token $token)
    {
        $type = $token->getTokenType();

        switch ($type) {
            case Token::TYPE_PARAMETER:
                /** @var ParameterToken $token */
                return self::renderParameter($token);

            case Token::TYPE_PROPERTY:
                /** @var PropertyToken $token */
                return self::renderProperty($token);

            case Token::TYPE_FUNCTION:
                /** @var FunctionToken $token */
                return self::renderFunction($token);

            case Token::TYPE_OBJECT:
                /** @var ObjectToken $token */
                return self::renderObject($token);

            default: // Token::TYPE_OPERATOR:
                /** @var OperatorToken $token */
                return self::renderOperator($token);
        }
    }

    private static function renderParameter(ParameterToken $token)
    {
        $name = $token->getName();

        return ":{$name}";
    }

    private static function renderProperty(PropertyToken $token)
    {
        $path = $token->getPath();

        return implode('.', $path);
    }

    private static function renderFunction(FunctionToken $token)
    {
        $name = $token->getName();
        $arguments = $token->getArguments();

        $renderedArguments = array_map('self::render', $arguments);
        $argumentList = implode(', ', $renderedArguments);

        return "{$name}({$argumentList})";
    }

    /**
     * Produces a string representation of the given object token.
     *
     * @param ObjectToken $token
     * @return string
     */
    private static function renderObject(ObjectToken $token)
    {
        $output = array();

        $properties = $token->getProperties();

        foreach ($properties as $key => $value) {
            $renderedValue = self::render($token);

            $output[] = "\"{$key}\": {$renderedValue}";
        }

        return '{' . implode(', ', $output) . '}';
    }

    private static function renderOperator(OperatorToken $token)
    {
        return $token->getLexeme();
    }
}
