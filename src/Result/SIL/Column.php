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
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Result\SIL;

use Datto\Cinnabari\Result\AliasMapper\AliasMapper;

/**
 * Class Column
 *
 * The SIL equivalent of a SQL-style column, corresponding for example
 * to the input {"id", clientId}, where "id" is the name and clientId the value.
 *
 * @package Datto\Cinnabari\Result\SIL
 */
class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /** @var string */
    private $tag;

    /** @var int */
    private static $tagCounter = 0;

    /**
     * Column constructor
     *
     * @param string $name  The label to display with this column
     * @param string $value The table and column in SQL terms
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
        $this->tag = AliasMapper::createColumnTag(self::$tagCounter);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
