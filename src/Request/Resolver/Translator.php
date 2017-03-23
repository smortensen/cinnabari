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

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Request\Language\Properties;
use Datto\Cinnabari\Request\Language\Functions;
use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Parser;

class Translator
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

    public function getConstraints(array $request)
    {
        $constraints = array();

        $context = array(Types::TYPE_OBJECT, 'Database');
        $id = 0;

        $this->getTokenConstraints($request, $context, $id, $constraints);

        return $constraints;
    }

    private function getTokenConstraints(array $token, &$context, &$id, &$constraints)
    {
        if ($token[0] === Parser::TYPE_PROPERTY) {
            $this->getPropertyConstraints($token[1], $context, $id, $constraints);
        } elseif ($token[0] === Parser::TYPE_FUNCTION) {
            $this->getFunctionConstraints($token[1], $token[2], $context, $id, $constraints);
        } elseif ($token[0] === Parser::TYPE_OBJECT) {
            // TODO
        }
    }

    private function getPropertyConstraints($properties, &$context, &$id, &$constraints)
    {
        $context = $this->getPropertyType($context, $properties);

        $constraints[] = self::getConstraintFromType($context, $id);
    }

    private function getPropertyType($type, $properties)
    {
        foreach ($properties as $property) {
            if ($type[0] !== Types::TYPE_OBJECT) {
                throw Exception::invalidPropertyAccess($type, $property);
            }

            $type = $this->properties->getType($type[1], $property);
        }

        return $type;
    }

    private static function getConstraintFromType($type, $id)
    {
        if ($type[0] === Types::TYPE_OR) {
            $types = array_slice($type, 1);
        } else {
            $types = array($type);
        }

        $options = array();

        foreach ($types as $type) {
            $options[] = array(
                $id => $type
            );
        }

        return $options;
    }

    private function getFunctionConstraints($function, $arguments, &$context, &$id, &$constraints)
    {
        $keys = array();
        $parentId = $id;

        if (0 < count($arguments)) {
            $argument = array_shift($arguments);

            $keys[] = ++$id;
            $childContext = $context;
            $this->getTokenConstraints($argument, $childContext, $id, $constraints);

            $isMapFunction = self::isObjectArray($childContext);

            if ($isMapFunction) {
                $context = $childContext[1];
            }

            foreach ($arguments as $argument) {
                $keys[] = ++$id;
                $tmpContext = $context;
                $this->getTokenConstraints($argument, $tmpContext, $id, $constraints);
            }

            if ($isMapFunction) {
                $context = $childContext;
            }
        }

        $keys[] = $parentId;

        $constraints[] = $this->getFunctionOrConstraint($function, $keys);
    }

    private static function isObjectArray($type)
    {
        return ($type[0] == Types::TYPE_ARRAY) && ($type[1][0] === Types::TYPE_OBJECT);
    }

    private function getFunctionOrConstraint($function, $keys)
    {
        $options = array();

        $keyCount = count($keys);

        foreach ($this->functions->getSignatures($function) as $signature) {
            if (count($signature) === $keyCount) {
                $options[] = array_combine($keys, $signature);
            }
        }

        return $options;
    }
}
