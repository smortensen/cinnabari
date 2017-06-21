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

namespace Datto\Cinnabari\Parser\Tokens;

class ObjectToken extends Token
{
    /** @var Token[] */
    private $properties;

    /**
     * @param Token[] $properties
     */
    public function __construct(array $properties)
    {
        parent::__construct(self::TYPE_OBJECT);

        $this->properties = $properties;
    }

    /**
     * @return Token[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Token[] $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $entries = array();

        foreach ($this->properties as $name => $token) {
            $entries[] = json_encode($name) . ': ' . (string)$token;
        }

        return '{' . implode(', ', $entries) . '}';
    }
}
