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

namespace Datto\Cinnabari\Language;

use Datto\Cinnabari\Exception\TranslatorException;

class Schema
{
    /** @var array */
    private $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function getPropertyDefinition($class, $property)
    {
        $definition = &$this->schema['classes'][$class][$property];

        if ($definition === null) {
            throw TranslatorException::unknownProperty($class, $property);
        }

        $type = reset($definition);
        $path = array_slice($definition, 1);

        return array($type, $path);
    }

    public function getListDefinition($list)
    {
        $definition = &$this->schema['lists'][$list];

        if ($definition === null) {
            throw TranslatorException::unknownList($list);
        }

        return $definition;
    }

    public function getConnectionDefinition($table, $connection)
    {
        $definition = &$this->schema['connections'][$table][$connection];

        if ($definition === null) {
            throw TranslatorException::unknownConnection($table, $connection);
        }

        return $definition;
    }

    public function getValueDefinition($table, $value)
    {
        $definition = &$this->schema['values'][$table][$value];

        if ($definition === null) {
            throw TranslatorException::unknownValue($table, $value);
        }

        return $definition;
    }
}
