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

class Constraints
{
    /** @var array */
    private $keys = array();

    /** @var array */
    private $values = array();

    /** @var array */
    private $solutions = array();

    public function __construct()
    {
        $this->keys = array();
        $this->values = array();
        $this->solutions = array();
    }

    public function getKey(array $value)
    {
        if (count($value) === 0) {
            return null;
        }

        $json = json_encode($value);
        $key = &$this->keys[$json];

        if ($key === null) {
            $key = count($this->values);
            $this->values[$key] = $value;
        }

        return $key;
    }

    public function getValue($key)
    {
        return $this->values[$key];
    }

    public function restrict(&$constraintKey, array $knowns)
    {
        $options = $this->values[$constraintKey];

        $mutualKnowns = self::getMutualKnowns($options, $knowns);

        if (count($mutualKnowns) === 0) {
            return false;
        }

        foreach ($mutualKnowns as $knownKey => $knownValue) {
            $constraintKey = $this->restrictByKnown($constraintKey, $knownKey, $knownValue);
        }

        return true;
    }

    private static function getMutualKnowns(array $options, array $knowns)
    {
        $output = array();

        $optionKeys = array_keys(reset($options));

        foreach ($optionKeys as $key) {
            if (isset($knowns[$key])) {
                $output[$key] = $knowns[$key];
            }
        }

        return $output;
    }

    private function restrictByKnown($constraintKey, $knownKey, $knownValue)
    {
        $knownValueKey = self::getValueKey($knownValue);
        $solutions = &$this->solutions[$constraintKey][$knownKey];
        $isSolved = is_array($solutions) && array_key_exists($knownValueKey, $solutions);

        if (!$isSolved) {
            $solutions[$knownValueKey] = $this->solve($constraintKey, $knownKey, $knownValue);
        }

        return $solutions[$knownValueKey];
    }

    private function solve($constraintKey, $knownKey, $knownValue)
    {
        $options = $this->values[$constraintKey];
        $options = self::restrictByValue($options, $knownKey, $knownValue);
        return $this->getKey($options);
    }

    private static function restrictByValue(array $options, $knownKey, $knownValue)
    {
        $isConstraintSatisfied = false;

        foreach ($options as $id => &$option) {
            $isOptionSatisfied = Option::restrict($option, $knownKey, $knownValue);

            if (!$isOptionSatisfied || (count($option) === 0)) {
                unset($options[$id]);
            }

            if ($isOptionSatisfied) {
                $isConstraintSatisfied = true;
            }
        }

        if (!$isConstraintSatisfied) {
            throw new Exception('Unsatisfiable constraint', 0);
        }

        return array_values($options);
    }

    public function extractKnowns(&$constraintKey)
    {
        $options = $this->values[$constraintKey];
        $knowns = self::getKnownsFromOptions($options);

        if (0 < count($knowns)) {
            $options = self::unsetKnowns($options, $knowns);
            $constraintKey = $this->getKey($options);
        }

        return $knowns;
    }

    private static function getKnownsFromOptions(array $options)
    {
        $mutualValues = reset($options);

        while ($option = next($options)) {
            foreach ($mutualValues as $key => $value) {
                if ($value !== $option[$key]) {
                    unset($mutualValues[$key]);
                }
            }
        }

        foreach ($mutualValues as $key => $value) {
            if (Option::isAbstract($value)) {
                unset($mutualValues[$key]);
            }
        }

        return $mutualValues;
    }

    private static function unsetKnowns(array $options, array $knowns)
    {
        $output = array();

        foreach ($options as $option) {
            foreach ($knowns as $key => $value) {
                unset($option[$key]);
            }

            if (count($option) === 0) {
                continue;
            }

            $key = json_encode($option);
            $output[$key] = $option;
        }

        return array_values($output);
    }

    private static function getValueKey($type)
    {
        if (is_array($type)) {
            return json_encode($type);
        }

        return $type;
    }
}
