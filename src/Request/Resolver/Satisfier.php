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

    /** @var null|array */
    private static $seen = array();

    public static function solve(array $input)
    {
        $constraints = self::getConstraints($input);

        if (count($constraints) === 0) {
            return null;
        }

        self::$solution = array();

        try {
            $knowns = self::getKnowns($constraints, $constraints);

            self::reduce($constraints, $knowns);
        } catch (Exception $exception) {
            return null;
        }

        return self::formatSolution(self::$solution);
    }

    private static function getConstraints($input)
    {
        $constraints = array();

        foreach ($input as $options) {
            $constraints[] = Constraint::getKey($options);
        }

        return $constraints;
    }

    private static function reduce(array $constraints, array $knowns)
    {
        while (0 < count($knowns)) {
            self::updateSolution($knowns);
            $dirty = self::restrictConstraints($constraints, $knowns);
            $knowns = self::getKnowns($constraints, $dirty);
        }

        if (!self::isSolved($constraints)) {
            self::forkProblem($constraints);
        }
    }

    private static function isSolved(array $constraints)
    {
        if (count($constraints) === 0) {
            return true;
        }

        $problemKey = json_encode($constraints);

        $isSeen = array_key_exists($problemKey, self::$seen);

        self::$seen[$problemKey] = true;

        return $isSeen;
    }

    private static function getKnowns(array &$constraints, array &$dirty)
    {
        $knowns = array();

        foreach ($dirty as $id => &$constraint) {
            $newKnowns = Constraint::extractKnowns($constraint);

            if (!Option::merge($knowns, $newKnowns)) {
                throw new Exception('There is no solution', 0);
            }

            if ($constraint === null) {
                unset($constraints[$id]);
            }
        }

        return $knowns;
    }

    private static function updateSolution(array $knowns)
    {
        foreach ($knowns as $propertyId => $value) {
            $valueId = self::getValueKey($value);
            self::$solution[$propertyId][$valueId] = $value;
        }
    }

    private static function getValueKey($type)
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
            if (!Constraint::restrict($constraint, $knowns)) {
                continue;
            }

            if ($constraint === null) {
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
            self::reduce($constraints, $knowns);
        }
    }

    protected static function getValue($value)
    {
        return $value;
    }

    private static function getPivots($constraints)
    {
        $pivots = array();

        foreach ($constraints as $constraint) {
            $options = Constraint::getValue($constraint);
            $option = reset($options);
            $propertyId = current(array_keys($option));

            foreach ($options as $option) {
                $value = $option[$propertyId];
                $pivots[] = array($propertyId => $value);
            }
        }

        return $pivots;
    }

    private static function formatSolution($solution)
    {
        ksort($solution, SORT_NUMERIC);

        foreach ($solution as &$values) {
            $values = array_values($values);
        }

        return $solution;
    }
}
