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

class Constraint
{
    public static function getMutualKnowns(array $options, array $knowns)
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

    public static function getKnowns(array $options)
    {
        $mutual = reset($options);

        while ($option = next($options)) {
            foreach ($mutual as $key => $value) {
                if ($value !== $option[$key]) {
                    unset($mutual[$key]);
                }
            }
        }

        foreach ($mutual as $key => $value) {
            if (Option::isAbstract($value)) {
                unset($mutual[$key]);
            }
        }

        return $mutual;
    }

    public static function unsetKnowns(array $options, array $knowns)
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

    public static function restrict(array &$options, array $knowns)
    {
        $isConstraintSatisfied = false;

        foreach ($options as $id => &$option) {
            $isOptionSatisfied = Option::restrict($option, $knowns);

            if (!$isOptionSatisfied || (count($option) === 0)) {
                unset($options[$id]);
            }

            if ($isOptionSatisfied) {
                $isConstraintSatisfied = true;
            }
        }

        $options = array_values($options);

        return $isConstraintSatisfied;
    }
}
