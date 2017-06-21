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

namespace Datto\Cinnabari\Resolver;

use Datto\Cinnabari\Parser\Tokens\Token as ParserToken;
use Datto\Cinnabari\Parser\Tokens\FunctionToken as ParserFunctionToken;
use Datto\Cinnabari\Parser\Tokens\ObjectToken as ParserObjectToken;
use Datto\Cinnabari\Parser\Tokens\ParameterToken as ParserParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken as ParserPropertyToken;
use Datto\Cinnabari\Resolver\Tokens\FunctionToken as ResolverFunctionToken;
use Datto\Cinnabari\Resolver\Tokens\ObjectToken as ResolverObjectToken;
use Datto\Cinnabari\Resolver\Tokens\ParameterToken as ResolverParameterToken;
use Datto\Cinnabari\Resolver\Tokens\PropertyToken as ResolverPropertyToken;

class Flattener
{
    private $tokens;

    /**
     * @param ParserToken $token
     * @return Request
     */
    public function flatten($token)
    {
        $this->tokens = array();

        $this->getToken($token);

        return new Request($this->tokens);
    }

    private function getToken(ParserToken $token)
    {
        $type = $token->getType();

        switch ($type) {
            default: // ParserToken::TYPE_PARAMETER:
                $this->getParameter($token);
                break;

            case ParserToken::TYPE_PROPERTY:
                $this->getProperty($token);
                break;

            case ParserToken::TYPE_FUNCTION:
                $this->getFunction($token);
                break;

            case ParserToken::TYPE_OBJECT:
                $this->getObject($token);
                break;
        }
    }

    private function getParameter(ParserParameterToken $token)
    {
        $name = $token->getName();

        $this->tokens[] = new ResolverParameterToken($name);
    }

    private function getProperty(ParserPropertyToken $token)
    {
        $path = $token->getPath();

        $this->tokens[] = new ResolverPropertyToken($path);
    }

    private function getFunction(ParserFunctionToken $token)
    {
        $output = &$this->tokens[];

        $name = $token->getName();
        $arguments = $this->flattenArray($token->getArguments());

        $output = new ResolverFunctionToken($name, $arguments);
    }

    private function getObject(ParserObjectToken $token)
    {
        $output = &$this->tokens[];

        $properties = $this->flattenArray($token->getProperties());

        $output = new ResolverObjectToken($properties);
    }

    /**
     * @param ParserToken[] $tokens
     * @return integer[]
     */
    private function flattenArray(array $tokens)
    {
        $output = array();

        foreach ($tokens as $key => &$token) {
            $output[$key] = count($this->tokens);
            $this->getToken($token);
        }

        return $output;
    }
}
