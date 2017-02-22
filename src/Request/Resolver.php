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
use Datto\Cinnabari\Request\Language\Properties;
use Datto\Cinnabari\Request\Language\Functions;
use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Resolver\Resolution;

class Resolver
{
    /** @var Functions */
    private $functions;

    /** @var Properties */
    private $properties;

    /**
     * Resolver constructor.
     *
     * @param Functions $functions
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Properties $properties)
    {
        $this->functions = $functions;
        $this->properties = $properties;
    }

    public function resolve(array $request)
    {
        $context = array(Types::TYPE_OBJECT, 'Database');
        $id = 0;

        $resolutions = $this->getResolutions($context, $request, $id);
        $resolutions = $this->getConcreteResolutions($resolutions);

        if (count($resolutions) === 0) {
            throw Exception::unresolvableTypeConstraints($request);
        }

        return self::getToken($request, $resolutions, $id);
    }

    private function getResolutions(&$context, $token, $id)
    {
        switch ($token[0]) {
            case Parser::TYPE_PARAMETER:
                return $this->getParameterResolutions($id);

            case Parser::TYPE_PROPERTY:
                return $this->getPropertyResolutions($context, $token[1], $id);

            case Parser::TYPE_FUNCTION:
                return $this->getFunctionResolutions($context, $token[1], $token[2], $id);

            // TODO:
            default: // Parser::TYPE_OBJECT:
                return $this->getObjectResolutions($context, $token, $id);
        }
    }

    private function getParameterResolutions($id)
    {
        $resolution = new Resolution(
            array(
                $id => '$x'
            )
        );

        return array($resolution);
    }

    private function getPropertyResolutions(&$context, $path, $id)
    {
        $context = $this->getPropertyType($context, $path);

        return self::getResolutionsFromType($context, $id);
    }

    private function getPropertyType($type, $path)
    {
        foreach ($path as $property) {
            if ($type[0] !== Types::TYPE_OBJECT) {
                throw Exception::invalidPropertyAccess($type, $property);
            }

            $type = $this->properties->getType($type[1], $property);
        }

        return $type;
    }

    private static function getResolutionsFromType($type, $id)
    {
        $resolutions = array();

        if ($type[0] === Types::TYPE_OR) {
            $types = array_slice($type, 1);
        } else {
            $types = array($type);
        }

        foreach ($types as $type) {
            $resolutions[] = new Resolution(
                array(
                    $id => $type
                )
            );
        }

        return $resolutions;
    }

    private function getFunctionResolutions($context, $function, $arguments, $id)
    {
        $signatures = $this->getSignatures($function, $arguments);
        $resolutions = $this->getResolutionsFromSignatures($signatures, $id);

        if (count($arguments) === 0) {
            return $resolutions;
        }

        $argument = array_shift($arguments);

        $type = $context;
        $childResolutions = $this->getResolutions($type, $argument, ++$id);
        $resolutions = self::mergeResolutions($resolutions, $childResolutions);

        // Map function
        if (self::isObjectArray($type)) {
            $context = $type[1];
        }

        foreach ($arguments as $argument) {
            $type = $context;
            $childResolutions = $this->getResolutions($type, $argument, ++$id);
            $resolutions = self::mergeResolutions($resolutions, $childResolutions);
        }

        return $resolutions;
    }

    private static function isObjectArray($type)
    {
        return ($type[0] == Types::TYPE_ARRAY) && ($type[1][0] === Types::TYPE_OBJECT);
    }

    private static function mergeResolutions($aResolutions, $bResolutions)
    {
        $cResolutions = array();

        foreach ($aResolutions as $aResolution) {
            foreach ($bResolutions as $bResolution) {
                $cResolution = Resolution::merge($aResolution, $bResolution);

                if ($cResolution === null) {
                    continue;
                }

                $cResolutions[] = $cResolution;
            }
        }

        return $cResolutions;
    }

    private function getSignatures($function, $arguments)
    {
        $signatures = array();

        $argumentsCount = count($arguments);

        foreach ($this->functions->getSignatures($function) as $signature) {
            $signatureArgumentsCount = count($signature) - 1;

            if ($signatureArgumentsCount === $argumentsCount) {
                $signatures[] = $signature;
            }
        }

        return $signatures;
    }

    private function getResolutionsFromSignatures($signatures, $id)
    {
        $resolutions = array();

        foreach ($signatures as $signature) {
            $resolutions[] = self::getSignatureResolution($signature, $id);
        }

        return $resolutions;
    }

    private static function getSignatureResolution($signature, $id)
    {
        $input = $signature;
        $output = array_pop($input);

        $values = array(
            $id => $output
        );

        foreach ($input as $type) {
            $values[++$id] = $type;
        }

        return new Resolution($values);
    }

    private static function getConcreteResolutions($resolutions)
    {
        $concreteResolutions = array();

        /** @var Resolution $resolution */
        foreach ($resolutions as $resolution) {
            if (!$resolution->isAbstract()) {
                $concreteResolutions[] = $resolution;
            }
        }

        return $concreteResolutions;
    }

    private static function getToken($token, $resolutions, $id)
    {
        $token[] =  self::getTokenType($token, $resolutions, $id);

        if ($token[0] === Parser::TYPE_FUNCTION) {
            self::translateTokens($token[2], $resolutions, $id);
        } elseif ($token[0] === Parser::TYPE_OBJECT) {
            self::translateTokens($token[1], $resolutions, $id);
        }

        return $token;
    }

    private static function translateTokens(&$tokens, $resolutions, $id)
    {
        foreach ($tokens as &$argument) {
            $argument = self::getToken($argument, $resolutions, ++$id);
        }
    }

    private static function getTokenType($token, $resolutions, $id)
    {
        $types = self::getTokenTypesArray($resolutions, $id);

        if (count($types) === 0) {
            throw Exception::unresolvableTypeConstraints($token);
        }

        if (count($types) === 1) {
            return array_shift($types);
        }

        $output = array_values($types);
        array_unshift($output, Types::TYPE_OR);

        return $output;
    }

    private static function getTokenTypesArray($resolutions, $id)
    {
        $tokenTypes = array();

        /** @var Resolution $resolution */
        foreach ($resolutions as $resolution) {
            $value = $resolution->getValue($id);
            $key = json_encode($value);

            $tokenTypes[$key] = $value;
        }

        return array_values($tokenTypes);
    }
}
