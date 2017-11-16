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

namespace Datto\Cinnabari\Phases\Evaluator;

use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Exception;

class InputValidator
{
    /** @var array */
    private $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function validate(array $input)
    {
        foreach ($this->types as $name => $type) {
            if (!array_key_exists($name, $input)) {
                throw Exception::missingArgument($name);
            }

            $value = $input[$name];

            if (!$this->verifyType($type, $value)) {
                throw Exception::invalidArgumentType($name, $type, $value);
            }
        }
    }

    private function verifyType($type, $input)
    {
        if (is_integer($type)) {
            return $this->verifyPrimitiveType($type, $input);
        }

        return $this->verifyComplexType($type, $input);
    }

    private function verifyPrimitiveType($type, $input)
    {
        switch ($type) {
            case Types::TYPE_NULL:
                return $input === null;

            case Types::TYPE_BOOLEAN:
                return is_bool($input);

            case Types::TYPE_INTEGER:
                return is_int($input);

            case Types::TYPE_FLOAT:
                return is_float($input);

            case Types::TYPE_STRING:
                return is_string($input);

            default:
                // TODO: throw exception?
                return false;
        }
    }

    private function verifyComplexType($type, $input)
    {
        switch ($type[0]) {
            case Types::TYPE_OBJECT:
                return $this->verifyObjectType($type, $input);

            case Types::TYPE_ARRAY:
                return $this->verifyArrayType($type, $input);

            case Types::TYPE_OR:
                return $this->verifyOrType($type, $input);

            default:
                // TODO: throw exception?
                return false;
        }
    }

    private function verifyObjectType($type, $value)
    {
        // TODO: implement this:
        return false;
    }

    private function verifyArrayType(array $type, $input)
    {
        if (!is_array($input)) {
            return false;
        }

        $valueType = $type[1];

        foreach ($input as $value) {
            if (!$this->verifyType($valueType, $value)) {
                return false;
            }
        }

        return true;
    }

    private function verifyOrType(array $types, $input)
    {
        $types = array_slice($types, 1);

        foreach ($types as $type) {
            if ($this->verifyType($type, $input)) {
                return true;
            }
        }

        return false;
    }
}
