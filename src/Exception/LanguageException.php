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

class LanguageException extends Exception
{
    const UNKNOWN_PROPERTY = 1;
    const UNKNOWN_FUNCTION = 2;

    public static function unknownProperty($class, $property)
    {
        $code = self::UNKNOWN_PROPERTY;

        $data = array(
            'class' => $class,
            'property' => $property
        );

        $className = json_encode($class);
        $propertyName = json_encode($property);

        $message = "The {$className} class has no {$propertyName} property.";

        return new self($code, $data, $message);
    }

    public static function unknownFunction($function)
    {
        $code = self::UNKNOWN_FUNCTION;

        $data = array(
            'function' => $function
        );

        $functionName = json_encode($function);

        $message = "There is no {$functionName} function.";

        return new self($code, $data, $message);
    }
}
