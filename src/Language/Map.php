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

/*
$map = array(
    'Database' => array(
        'people' => array(
            array('`People`', '`Id`', false, true),
            'Person'
        )
    ),
    'Person' => array(
        'children' => array(
            array('`0`.`Id` <=> `1`.`Parent`', '`Families`', '`Id`', true, true),
            array('`0`.`Child` <=> `1`.`Id`', '`People`', '`Id`', true, true),
            'Person'
        ),
        'id' => array(
            array('`Id`')
        ),
        'name' => array(
            array('`Name`')
        )
    )
);
*/
class Map
{
    /** @var string */
    private static $databaseClass = 'Database';

    /** @var array */
    private $map;

    public function __construct($map)
    {
        $this->map = $map;
    }

    public function getMysqlTokens($class, $property)
    {
        if ($class === null) {
            $class = self::$databaseClass;
        }

        $tokens = &$this->map[$class][$property];

        if (!isset($tokens)) {
            // TODO: throw exception
            return null;
        }

        return $tokens;
    }
}
