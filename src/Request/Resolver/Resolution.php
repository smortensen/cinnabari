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

class Resolution
{
    /** @var array */
    private $values;

    /**
     * Resolution constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getValue($name)
    {
        if (isset($this->values[$name])) {
            return $this->values[$name];
        }

        return null;
    }

    public function isAbstract()
    {
        return self::isAbstractArray($this->values);
    }

    /**
     * @param Resolution $a
     * @param Resolution $b
     * @return null|Resolution
     */
    public static function merge(Resolution $a, Resolution $b)
    {
        list($aValues, $aUnknowns) = self::unpack($a);
        list($bValues, $bUnknowns) = self::unpack($b);

        $cValues = array();
        $cUnknowns = array();

        self::mergeUnknowns($cUnknowns, $aUnknowns);
        self::mergeUnknowns($cUnknowns, $bUnknowns);

        $mutualKeys = array_intersect(array_keys($aValues), array_keys($bValues));

        foreach ($mutualKeys as $key) {
            $aValue = &$aValues[$key];
            $bValue = &$bValues[$key];

            if (!self::isSame($aValue, $bValue, $cUnknowns)) {
                return null;
            }

            $cValues[$key] = &$aValue;
            unset($aValues[$key], $bValues[$key]);
        }

        foreach ($aValues as $key => &$value) {
            $cValues[$key] = &$value;
        }

        foreach ($bValues as $key => &$value) {
            $cValues[$key] = &$value;
        }

        $unknowns = array();
        self::mergeUnknowns($unknowns, $cUnknowns);

        return new self($cValues);
    }

    private static function unpack(Resolution $possibility)
    {
        $values = $possibility->getValues();
        $unknowns = array();

        self::getUnknowns($values, $unknowns);

        return array($values, $unknowns);
    }

    private static function getUnknowns(array &$values, array &$unknowns)
    {
        foreach ($values as $key => &$value) {
            if (is_array($value)) {
                self::getUnknowns($value, $unknowns);
            } elseif (self::isUnknown($value)) {
                $unknowns[$value][] = &$value;
            }
        }
    }

    private static function isAbstractArray(array $values)
    {
        foreach ($values as $key => &$value) {
            if (
                self::isUnknown($value) ||
                (is_array($value) && self::isAbstractArray($value))
            ) {
                return true;
            }
        }

        return false;
    }

    private static function isUnknown($value)
    {
        return is_string($value) && (substr($value, 0, 1) === '$');
    }

    private static function mergeUnknowns(array &$a, array &$b)
    {
        $i = count($a);
        $names = array();

        foreach ($b as $oldName => &$references) {
            $newName = &$names[$oldName];

            if (!isset($newName)) {
                $newName = '$' . $i++;
            }

            self::set($references, $newName);
            $a[$newName] = &$references;
        }
    }

    private static function isSame(&$a, &$b, array &$unknowns)
    {
        if (self::isUnknown($a)) {
            if (self::isUnknown($b)) {
                return self::substitute($b, $a, $unknowns);
            }

            return self::satisfy($a, $b, $unknowns);
        }

        if (self::isUnknown($b)) {
            return self::satisfy($b, $a, $unknowns);
        }

        if (is_array($a) && is_array($b)) {
            return self::isSameArray($a, $b, $unknowns);
        }

        return $a === $b;
    }

    private static function isSameArray(&$a, &$b, array &$unknowns)
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

    private static function substitute($aUnknown, $bUnknown, array &$unknowns)
    {
        self::set($unknowns[$bUnknown], $aUnknown);
        $unknowns[$aUnknown] = array_merge($unknowns[$aUnknown], $unknowns[$bUnknown]);
        unset($unknowns[$bUnknown]);

        return true;
    }

    private static function satisfy($aUnknown, $b, array &$unknowns)
    {
        self::set($unknowns[$aUnknown], $b);
        unset($unknowns[$aUnknown]);

        return true;
    }

    private static function set(array &$references, $value)
    {
        foreach ($references as &$reference) {
            $reference = $value;
        }
    }
}
