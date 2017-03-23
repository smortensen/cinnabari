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

use Exception;

class Satisfier
{
    /** @var null|array */
    private static $solution;

    public static function solve(array $constraints)
    {
        self::$solution = array();

        if (count($constraints) === 0) {
            return null;
        }

        try {
            $knowns = self::getKnowns($constraints, $constraints);

            self::reduce($constraints, $knowns);
        } catch (Exception $exception) {
            return null;
        }

        foreach (self::$solution as $propertyId => &$values) {
            $values = array_values($values);
        }

        return self::$solution;
    }

    private static function reduce(array $constraints, array $knowns)
    {
        while (0 < count($knowns)) {
            self::updateSolution($knowns);
            $dirty = self::restrictConstraints($constraints, $knowns);
            $knowns = self::getKnowns($constraints, $dirty);
        }

        if (0 < count($constraints)) {
            self::forkProblem($constraints);
        }
    }

    private static function getKnowns(array &$constraints, array &$dirty)
    {
        $knowns = array();

        foreach ($dirty as $id => &$constraint) {
            $newKnowns = Constraint::getKnowns($constraint);

            if (count($newKnowns) === 0) {
                continue;
            }

            $constraint = Constraint::unsetKnowns($constraint, $newKnowns);

            if (count($constraint) === 0) {
                unset($constraints[$id]);
            }

            if (!Option::merge($knowns, $newKnowns)) {
                throw new Exception('There is no solution', 0);
            }
        }

        return $knowns;
    }

    private static function updateSolution(array $knowns)
    {
        foreach ($knowns as $propertyId => $value) {
            $valueId = self::getValueId($value);
            self::$solution[$propertyId][$valueId] = $value;
        }
    }

    private static function getValueId($type)
    {
        if (is_array($type)) {
            return json_encode($type);
        }

        return $type;
    }

    private static function restrictConstraints(array &$constraints, array $knowns)
    {
        $dirty = array();

        foreach ($constraints as $id => &$constraint) {
            $mutualKnowns = Constraint::getMutualKnowns($constraint, $knowns);

            if (count($mutualKnowns) === 0) {
                continue;
            }

            if (!Constraint::restrict($constraint, $mutualKnowns)) {
                throw new Exception('There is no solution', 0);
            }

            if (count($constraint) === 0) {
                unset($constraints[$id]);
            } else {
                $dirty[$id] = &$constraint;
            }
        }

        return $dirty;
    }

    private static function forkProblem($constraints)
    {
        $constraints = array_map('self::getValue', $constraints);

        $pivots = self::getPivots($constraints);

        foreach ($pivots as $knowns) {
            Satisfier::reduce($constraints, $knowns);
        }
    }

    protected static function getValue($value)
    {
        return $value;
    }

    private static function getPivots($constraints)
    {
        $pivots = array();

        foreach ($constraints as $options) {
            $option = reset($options);
            $propertyId = current(array_keys($option));

            foreach ($options as $option) {
                $value = $option[$propertyId];
                $pivots[] = array($propertyId => $value);
            }
        }

        return $pivots;
    }
}
