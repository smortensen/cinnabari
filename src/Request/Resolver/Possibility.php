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

class Possibility
{
    /** @var array */
    private $values;

    public function __construct($values)
    {
        $this->values = $values;
    }

    // TODO: remove this:
    public function __toString()
    {
        return json_encode($this->values);
    }

    public function get($name)
    {
        return isset($this->values[$name]) ? $this->values[$name] : null;
    }

    public function getValues()
    {
        return $this->values;
    }

    public static function merge(Possibility $a, Possibility $b)
    {
        self::extract($a, $aValues, $aUnknowns);
        self::extract($b, $bValues, $bUnknowns);

        $cUnknowns = array();
        self::mergeUnknowns($cUnknowns, $aUnknowns);
        self::mergeUnknowns($cUnknowns, $bUnknowns);

        $values = array();

        $mutualKeys = array_intersect(array_keys($aValues), array_keys($bValues));

        foreach ($mutualKeys as $key) {
            $aValue = &$aValues[$key];
            $bValue = &$bValues[$key];

            if (!self::isSame($aValue, $bValue, $cUnknowns)) {
                // TODO
                echo 'Uh-oh!';
                return null;
            }

            $values[$key] = &$aValue;
            unset($aValues[$key], $bValues[$key]);
        }

        foreach ($aValues as $key => &$value) {
            $values[$key] = &$value;
        }

        foreach ($bValues as $key => &$value) {
            $values[$key] = &$value;
        }

        $unknowns = array();
        self::mergeUnknowns($unknowns, $cUnknowns);

        return new self($values);
    }

    private static function extract(Possibility $possibility, &$values, &$unknowns)
    {
        $values = $possibility->getValues();

        $unknowns = array();
        self::getUnknowns($values, $unknowns);
    }

    private static function getUnknowns(array &$values, array &$unknowns)
    {
        foreach ($values as $key => &$value) {
            if (is_array($value)) {
                self::getUnknowns($value, $unknowns);
            } elseif (self::isUnknown($value)) {
                self::bindUnknown($values, $unknowns, $key);
            }
        }
    }

    private static function isUnknown($value)
    {
        return is_string($value) && (substr($value, 0, 1) === '$');
    }

    private static function bindUnknown(array &$values, array &$unknowns, $key)
    {
        $value = &$values[$key];

        if (isset($unknowns[$value])) {
            $values[$key] = &$unknowns[$value];
        } else {
            $unknowns[$value] = &$value;
        }
    }

    private static function mergeUnknowns(array &$a, array &$b)
    {
        $i = count($a);
        $names = array();

        foreach ($b as $key => &$value) {
            $name = &$names[$value];

            if (!isset($name)) {
                $name = '$' . $i++;
            }

            $a[$name] = &$value;
            $value = $name;
        }
    }

    private static function isSame(&$a, &$b, &$unknowns)
    {
        if (gettype($a) !== gettype($b)) {
            return false;
        }

        if (is_array($a)) {
            return self::isSameArray($a, $b, $unknowns);
        }

        if (is_string($a)) {
            return self::isSameString($a, $b, $unknowns);
        }

        return $a === $b;
    }

    private static function isSameArray(&$a, &$b, &$unknowns)
    {
        if (array_keys($a) !== array_keys($b)) {
            return false;
        }

        foreach ($a as $key => $aValue) {
            if (!self::isSame($a[$key], $b[$key], $unknowns)) {
                return false;
            }
        }

        return true;
    }

    private static function isSameString(&$a, &$b, &$unknowns)
    {
        if (self::isUnknown($a) && self::isUnknown($b)) {
            $unknowns[$b] = $a;
            return true;
        }

        if (self::isUnknown($a)) {
            self::satisfy($a, $b, $unknowns);
            return true;
        }

        if (self::isUnknown($b)) {
            self::satisfy($b, $a, $unknowns);
            return true;
        }

        return $a === $b;
    }

    private static function satisfy($aUnknown, $b, &$unknowns)
    {
        $unknowns[$aUnknown] = $b;
        unset($unknowns[$aUnknown]);
    }
}
