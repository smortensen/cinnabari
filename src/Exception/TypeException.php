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

namespace Datto\Cinnabari\Exception;

class TypeException extends Exception
{
    const UNCONSTRAINED_PARAMETER = 1;
    const FORBIDDEN_PROPERTY_TYPE = 2;
    const UNSATISFIABLE_FUNCTION = 3;

    public static function unconstrainedParameter($parameter)
    {
        $code = self::UNCONSTRAINED_PARAMETER;

        $data = array(
            'parameter' => $parameter
        );

        $parameterName = json_encode($parameter);

        // TODO: provide more help than this:
        $message = "The parameter {$parameterName} is unconstrained.";

        return new self($code, $data, $message);
    }

    public static function forbiddenPropertyType($property, $type)
    {
        $code = self::FORBIDDEN_PROPERTY_TYPE;

        $data = array(
            'property' => $property,
            'type' => $type
        );

        $propertyName = json_encode(implode('.', $property));

        // TODO: provide better help:
        $message = "The property {$propertyName} can take on values that are forbidden in this query.";

        return new self($code, $data, $message);
    }

    public static function unsatisfiableFunction($function, $arguments)
    {
        $code = self::UNSATISFIABLE_FUNCTION;

        $data = array(
            'function' => $function,
            'arguments' => $arguments
        );

        $functionName = json_encode($function);

        $message = "The function {$functionName} is unsatisfiable.";

        return new self($code, $data, $message);
    }
}
