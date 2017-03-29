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

class Option
{
    public static function isAbstract($value)
    {
        if (is_array($value)) {
            foreach ($value as &$child) {
                if (self::isAbstract($child)) {
                    return true;
                }
            }

            return false;
        }

        return self::isUnknown($value);
    }

    // Assumption: both options are fully concrete
    public static function merge(array &$optionA, $optionB)
    {
        foreach ($optionB as $key => $valueB) {
            $valueA = &$optionA[$key];

            if ($valueA === null) {
                $valueA = $valueB;
            } elseif ($valueA !== $valueB) {
                return false;
            }
        }

        return true;
    }

    // Assumption: the known value is concrete
    public static function restrict(array &$values, $key, $value)
    {
        if (!self::isSameValue($values, $values[$key], $value)) {
            return false;
        }

        unset($values[$key]);
        return true;
    }

    private static function isSameValue(array &$values, $a, $b)
    {
        if (self::isUnknown($a)) {
            return self::replace($values, $a, $b);
        }

        if (is_array($a) && is_array($b)) {
            return self::isSameArray($values, $a, $b);
        }

        return $a === $b;
    }

    private static function isUnknown($type)
    {
        return is_string($type) && (substr($type, 0, 1) === '$');
    }

    private static function replace(&$value, $before, $after)
    {
        if (is_array($value)) {
            foreach ($value as &$child) {
                self::replace($child, $before, $after);
            }
        } elseif ($value === $before) {
            $value = $after;
        }

        return true;
    }

    private static function isSameArray(array &$values, array $a, array $b)
    {
        if (array_keys($a) !== array_keys($b)) {
            return false;
        }

        foreach ($a as $key => $aValue) {
            if (!self::isSameValue($values, $a[$key], $b[$key])) {
                return false;
            }
        }

        return true;
    }
}
