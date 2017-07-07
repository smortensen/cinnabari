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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Language\Request\ObjectToken;
use Datto\Cinnabari\Language\Request\Token;


class ShowToken
{
    /**
     * Produces a string representation of the given token
     *
     * @param Token $token
     * @return string
     */
    public static function tokenToString(Token $token)
    {
        $type = $token->getTokenType();

        switch ($type) {
            case Token::TYPE_PARAMETER:
                return ':'.$token->getName();

            case Token::TYPE_PROPERTY:
                return implode('.', $token->getPath());

            case Token::TYPE_FUNCTION:
                $arguments = implode(', ', array_map(array('self','tokenToString'), $token->getArguments()));
                return "{$token->getName()}({$arguments})";

            case Token::TYPE_OBJECT:
                return self::objectTokenToString($token);

            case Token::TYPE_OPERATOR:
                return $token->getLexeme();

        }
    }

    /**
     * Produces a string representation of the given object token.
     *
     * @param ObjectToken $token
     * @return string
     */
    private static function objectTokenToString(ObjectToken $token)
    {
        $entries = array();

        foreach ($token->getProperties() as $name => $token) {
            $entries[] = json_encode($name) . ': ' . self::tokenToString($token);
        }

        return '{' . implode(', ', $entries) . '}';
    }
}