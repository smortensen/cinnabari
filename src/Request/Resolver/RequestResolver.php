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

namespace Datto\Cinnabari\Request\Resolver;

use Datto\Cinnabari\Exceptions\TypeException;
use Datto\Cinnabari\Request\Language\Functions;
use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Parser;

class RequestResolver
{
    /** @var Functions */
    private $functions;

    public function __construct(Functions $functions)
    {
        $this->functions = $functions;
    }

    public function resolve($request)
    {
        return $this->getToken($request, array('x'));
    }

    private function getToken($token, $allowedTypes)
    {
        switch ($token[0]) {
            case Parser::TYPE_PARAMETER:
                return $this->getParameterToken($token, $allowedTypes);

            case Parser::TYPE_PROPERTY:
                return $this->getPropertyToken($token, $allowedTypes);

            case Parser::TYPE_FUNCTION:
                return $this->getFunctionToken($token, $allowedTypes);

            default: // Parser::TYPE_OBJECT:
                return $this->getObjectToken($token);
        }
    }

    private function getParameterToken($token, $allowedTypes)
    {
        $parameter = $token[1];

        foreach ($allowedTypes as $type) {
            if (Scope::isAbstractToken($type)) {
                throw TypeException::unconstrainedParameter($parameter);
            }
        }

        $type = self::getTypeFromTypeList($allowedTypes);

        return array(Parser::TYPE_PARAMETER, $parameter, $type);
    }

    private function getPropertyToken($token, $inputTypes)
    {
        $variable = 0;

        $inputScopes = $this->getPropertyScopes($variable, $inputTypes);
        $outputScopes = array();

        $propertyType = $token[2];
        $outputTypes = self::getTypeListFromType($propertyType);

        foreach ($outputTypes as $type) {
            $boundScopes = $this->bindScopes($inputScopes, $variable, $type);

            if (count($boundScopes) === 0) {
                $path = $token[1];
                $type = $token[2];

                throw TypeException::forbiddenPropertyType($path, $type);
            }

            $outputScopes = array_merge($outputScopes, $boundScopes);
        }

        return $token;
    }

    private function getPropertyScopes($variable, $types)
    {
        $scopes = array();

        foreach ($types as $type) {
            $scope = new Scope();
            $scope->set($variable, $type);

            $scopes[] = $scope;
        }

        return $scopes;
    }

    private function bindVariable($inputScopes, $variable, $types)
    {
        $outputScopes = array();

        foreach ($types as $type) {
            $boundScopes = $this->bindScopes($inputScopes, $variable, $type);
            $outputScopes = array_merge($outputScopes, $boundScopes);
        }

        return $outputScopes;
    }

    private function bindScopes($scopes, $variable, $type)
    {
        $boundScopes = array();

        foreach ($scopes as $scope) {
            /** @var Scope $scope */
            $boundScope = clone $scope;

            if ($boundScope->set($variable, $type)) {
                $boundScopes[] = $boundScope;
            }
        }

        return $boundScopes;
    }

    private function getFunctionToken($token, $outputTypes)
    {
        $function = $token[1];
        $arguments = $token[2];

        $argumentCount = count($arguments);

        $signatures = $this->getFunctionSignatures($function);
        $signatures = $this->filterSignaturesByArguments($signatures, $argumentCount);

        $iOutput = $argumentCount;

        $scopes = $this->getFunctionScopes($signatures);

        $scopes = $this->bindVariable($scopes, $iOutput, $outputTypes);

        foreach ($arguments as $i => &$argument) {
            $allowedTypes = $this->getAllowedTypes($scopes, $i);
            $argument = $this->getToken($argument, $allowedTypes);
            $possibleTypes = $this->getPossibleTypes($argument);

            $scopes = $this->bindVariable($scopes, $i, $possibleTypes);
        }

        if (count($scopes) === 0) {
            throw TypeException::unsatisfiableFunction($function, $arguments);
        }

        $allowedTypes = $this->getAllowedTypes($scopes, $iOutput);
        $type = self::getTypeFromTypeList($allowedTypes);

        return array(Parser::TYPE_FUNCTION, $function, $arguments, $type);
    }

    private function getPossibleTypes($token)
    {
        $type = self::getTokenType($token);

        return self::getTypeListFromType($type);
    }

    private static function getTokenType($token)
    {
        if ($token[0] === Parser::TYPE_FUNCTION) {
            return $token[3];
        }

        return $token[2];
    }

    private function getAllowedTypes($scopes, $variable)
    {
        $types = array();

        foreach ($scopes as $scope) {
            /** @var Scope $scope */
            $type = $scope->get($variable);
            $key = json_encode($type);

            $types[$key] = $type;
        }

        return $types;
    }

    private function getFunctionScopes($signatures)
    {
        $scopes = array();

        foreach ($signatures as $signature) {
            $scopes[] = $this->getFunctionScope($signature);
        }

        return $scopes;
    }

    /**
     * @param array $signature
     * @return Scope
     */
    private function getFunctionScope($signature)
    {
        $scope = new Scope();

        $inputTypes = $signature['input'];
        $outputType = $signature['output'];

        foreach ($inputTypes as $i => $inputType) {
            $scope->set($i, $inputType);
        }

        $i = count($inputTypes);
        $scope->set($i, $outputType);

        return $scope;
    }

    private static function filterSignaturesByArguments($oldSignatures, $argumentCount)
    {
        $newSignatures = array();

        foreach ($oldSignatures as $signature) {
            if (count($signature['input']) === $argumentCount) {
                $newSignatures[] = $signature;
            }
        }

        return $newSignatures;
    }

    private function getFunctionSignatures($function)
    {
        $signatures = $this->functions->getSignatures($function);

        foreach ($signatures as &$signature)
        {
            $input = $signature;
            $output = array_pop($input);

            $signature = array(
                'input' => $input,
                'output' => $output
            );
        }

        return $signatures;
    }

    private function getObjectToken($token)
    {
        // TODO: validate objects
        return $token;
    }

    private static function getTypeFromTypeList($types)
    {
        if (count($types) === 1) {
            $type = array_shift($types);
        } else {
            $type = $types;
            array_unshift($type, Types::TYPE_OR);
        }

        return $type;
    }

    private static function getTypeListFromType($token)
    {
        if (is_array($token) && ($token[0] == Types::TYPE_OR)) {
            $types = $token;
            array_shift($types);
        } else {
            $types = array($token);
        }

        $indexedTypes = array();

        foreach ($types as $type) {
            $key = json_encode($type);
            $indexedTypes[$key] = $type;
        }

        return $indexedTypes;
    }
}
