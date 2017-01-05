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

namespace Datto\Cinnabari\Resolver;

use Datto\Cinnabari\Language\Types;

class Scope
{
    /** @var array */
    private $variables;

    public function set($variable, $newType)
    {
        $oldType = &$this->variables[$variable];

        if ($oldType === null) {
            $oldType = $newType;
            return true;
        }

        return $this->restrict($oldType, $newType);
    }

    public function get($variable)
    {
        return $this->getToken($variable);
    }

    public function isAbstract($variable)
    {
        $token = $this->getToken($variable);

        return self::isAbstractToken($token);
    }

    private function restrict($oldToken, $newToken)
    {
        if (is_string($newToken)) {
            return true;
        }

        if (is_integer($oldToken)) {
            return $this->restrictPrimitive($oldToken, $newToken);
        }

        if (is_array($oldToken)) {
            return $this->restrictCompound($oldToken, $newToken);
        }

        if (is_string($oldToken)) {
            return $this->restrictUnknown($oldToken, $newToken);
        }

        return false;
    }

    private function restrictPrimitive($oldToken, $newToken)
    {
        return $oldToken === $newToken;
    }

    private function restrictCompound($oldToken, $newToken)
    {
        if (!is_array($newToken) || ($newToken[0] !== $oldToken[0])) {
            return false;
        }

        $type = $oldToken[0];

        switch ($type) {
            case Types::TYPE_ARRAY:
                return $this->restrict($oldToken[1], $newToken[1]);

            default: // Types::TYPE_OBJECT || Types::TYPE_FUNCTION || Types::TYPE_OR
                return false;
        }
    }


    private function restrictUnknown($target, $newToken)
    {
        // TODO: replace any placeholders in the new token to avoid naming collisions

        foreach ($this->variables as &$oldToken) {
            $oldToken = $this->replace($target, $oldToken, $newToken);
        }

        return true;
    }

    private function replace($target, $oldToken, $newToken)
    {
        if (is_array($oldToken)) {
            return $this->replaceCompound($target, $oldToken, $newToken);
        }

        if (is_string($oldToken)) {
            return $this->replaceUnknown($target, $oldToken, $newToken);
        }

        return $oldToken;
    }

    private function replaceUnknown($target, $oldToken, $newToken)
    {
        if ($oldToken === $target) {
            return $newToken;
        }

        return $oldToken;
    }

    private function replaceCompound($target, $oldToken, $newToken)
    {
        $type = $oldToken[0];

        switch ($type) {
            case Types::TYPE_OBJECT:
                return $this->replaceObject($target, $oldToken, $newToken);

            case Types::TYPE_ARRAY:
                return $this->replaceArray($target, $oldToken, $newToken);

            case Types::TYPE_FUNCTION:
                return $this->replaceFunction($target, $oldToken, $newToken);

            case Types::TYPE_OR:
                return $this->replaceOr($target, $oldToken, $newToken);
        }

        return $oldToken;
    }

    private function replaceObject($target, $oldToken, $newToken)
    {
        $object = &$oldToken[1];

        foreach ($object as &$value) {
            $value = $this->replace($target, $value, $newToken);
        }

        return $oldToken;
    }

    private function replaceArray($target, $oldToken, $newToken)
    {
        $type = &$oldToken[1];

        $type = $this->replace($target, $type, $newToken);

        return $oldToken;
    }

    private function replaceFunction($target, $oldToken, $newToken)
    {
        $arguments = &$oldToken[2];

        foreach ($arguments as &$argument) {
            $argument = $this->replace($target, $argument, $newToken);
        }

        return $oldToken;
    }

    private function replaceOr($target, $oldToken, $newToken)
    {
        for ($i = 1, $n = count($oldToken); $i < $n; ++$i) {
            $type = &$oldToken[$i];

            $type = $this->replace($target, $type, $newToken);
        }

        return $oldToken;
    }

    private function getToken($variable)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }

        // TODO:
        return null;
    }

    private static function isAbstractToken($token)
    {
        if (is_array($token)) {
            $type = $token[0];

            switch ($type) {
                case Types::TYPE_OBJECT:
                    $object = $token[1];
                    return self::isAbstractArray($object);

                case Types::TYPE_ARRAY:
                    $value = $token[1];
                    return self::isAbstractToken($value);

                case Types::TYPE_FUNCTION:
                    $arguments = $token[2];
                    return self::isAbstractArray($arguments);

                case Types::TYPE_OR:
                    $types = array_slice($token, 1);
                    return self::isAbstractArray($types);
            }
        }

        return is_string($token);
    }

    private static function isAbstractArray($array)
    {
        foreach ($array as $value) {
            if (self::isAbstractToken($value)) {
                return true;
            }
        }

        return false;
    }
}
