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
 * Class Parameter
 *
 * The SIL equivalent of a parameter (e.g., ":value").
 *
 * @package Datto\Cinnabari\Result\SIL
 */
class Parameter
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /** @var bool */
    private $isSynthetic;

    /** @var string */
    private $tag;

    /**
     * Parameter constructor.
     *
     * @param string      $name
     * @param AliasMapper $mapper
     * @param bool        $isSynthetic (not in the original query; compiler-generated)
     */
    public function __construct($name, AliasMapper $mapper, $isSynthetic = false)
    {
        $this->name = $name;
        $this->value = $name;
        $this->isSynthetic = $isSynthetic;
        $this->tag = $mapper->createParameterTag();
    }

    public function setValue($expression)
    {
        $this->value = $expression;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getIsSynthetic()
    {
        return $this->isSynthetic;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
