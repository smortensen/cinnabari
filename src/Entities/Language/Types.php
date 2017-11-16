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

namespace Datto\Cinnabari\Entities\Language;

class Types
{
    const TYPE_NULL = 1;
    const TYPE_BOOLEAN = 2;
    const TYPE_INTEGER = 3;
    const TYPE_FLOAT = 4;
    const TYPE_STRING = 5;
    const TYPE_OBJECT = 6; // array(Types::TYPE_OBJECT, 'Person')
    const TYPE_ARRAY = 7; // array(Types::TYPE_ARRAY, $type)
    const TYPE_OR = 8; // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER)
}
