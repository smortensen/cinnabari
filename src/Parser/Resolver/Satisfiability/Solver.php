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

namespace Datto\Cinnabari\Parser\Resolver\Satisfiability;

use Exception;

class Solver
{
    /** @var array */
    private $solution;

    /** @var array */
    private $seen;

    /** @var Constraints */
    private $constraints;

    public function solve(array $input)
    {
        $this->solution = array();
        $this->seen = array();
        $this->constraints = new Constraints();

        $constraints = $this->getConstraints($input);

        if (count($constraints) === 0) {
            return null;
        }

        $this->solution = array();

        try {
            $knowns = $this->getKnowns($constraints, $constraints);

            $this->reduce($constraints, $knowns);
        } catch (Exception $exception) {
            return null;
        }

        return self::formatSolution($this->solution);
    }

    private function getConstraints($input)
    {
        $constraints = array();

        foreach ($input as $options) {
            $constraints[] = $this->constraints->getKey($options);
        }

        return $constraints;
    }

    private function reduce(array $constraints, array $knowns)
    {
        while (0 < count($knowns)) {
            $this->updateSolution($knowns);
            $dirty = $this->restrictConstraints($constraints, $knowns);
            $knowns = $this->getKnowns($constraints, $dirty);
        }

        if (!$this->isSolved($constraints)) {
            $this->forkProblem($constraints);
        }
    }

    private function getKnowns(array &$constraints, array &$dirty)
    {
        $knowns = array();

        foreach ($dirty as $id => &$constraint) {
            $newKnowns = $this->constraints->extractKnowns($constraint);

            if (!Option::merge($knowns, $newKnowns)) {
                throw new Exception('There is no solution', 0);
            }

            if ($constraint === null) {
                unset($constraints[$id]);
            }
        }

        return $knowns;
    }

    private function updateSolution(array $knowns)
    {
        foreach ($knowns as $propertyId => $value) {
            $valueId = self::getValueKey($value);
            $this->solution[$propertyId][$valueId] = $value;
        }
    }

    private static function getValueKey($type)
    {
        if (is_array($type)) {
            return json_encode($type);
        }

        return $type;
    }

    private function restrictConstraints(array &$constraints, array $knowns)
    {
        $dirty = array();

        foreach ($constraints as $id => &$constraint) {
            if (!$this->constraints->restrict($constraint, $knowns)) {
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

    private function isSolved(array $constraints)
    {
        if (count($constraints) === 0) {
            return true;
        }

        $problemKey = json_encode($constraints);

        $isSeen = array_key_exists($problemKey, $this->seen);

        $this->seen[$problemKey] = true;

        return $isSeen;
    }

    private function forkProblem($constraints)
    {
        $constraints = array_map('self::getValue', $constraints);

        $pivots = $this->getPivots($constraints);

        foreach ($pivots as $knowns) {
            $this->reduce($constraints, $knowns);
        }
    }

    protected static function getValue($value)
    {
        return $value;
    }

    private function getPivots($constraints)
    {
        $pivots = array();

        // TODO: avoid pivots with an abstract value
        $constraint = reset($constraints);
        $options = $this->constraints->getValue($constraint);
        $option = reset($options);
        $propertyId = current(array_keys($option));

        foreach ($options as $option) {
            $value = $option[$propertyId];
            $pivots[] = array($propertyId => $value);
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
