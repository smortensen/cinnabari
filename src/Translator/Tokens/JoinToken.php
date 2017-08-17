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

namespace Datto\Cinnabari\Translator\Tokens;

class JoinToken extends Token
{
    /** @var string */
    private $table;

    /** @var string */
    private $condition;

    /** @var bool */
    private $bijective;

    /**
     * @param string $table
     * @param string $condition
     * @param boolean $bijective
     */
    public function __construct($table, $condition, $bijective)
    {
        parent::__construct(Token::TYPE_JOIN);

        $this->table = $table;
        $this->condition = $condition;
        $this->bijective = $bijective;
    }
}
