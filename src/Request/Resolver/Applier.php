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

use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Parser;

class Applier
{
    /** @var array */
    private $types;

    /** @var integer */
    private $id;

    public function apply(array $request, array $types)
    {
        $this->types = $types;
        $this->id = -1;

        return $this->getToken($request);
    }

    private function getToken(array $token)
    {
        $type = $this->getType();

        if ($token[0] === Parser::TYPE_FUNCTION) {
            foreach ($token[2] as &$argument) {
                $argument = $this->getToken($argument);
            }
        }

        $token[] = $type;

        return $token;
    }

    private function getType()
    {
        $id = ++$this->id;
        $typeList = $this->types[$id];

        return self::getTypeFromList($typeList);
    }

    private static function getTypeFromList($types)
    {
        if (count($types) === 1) {
            $type = $types[0];
        } else {
            $type = $types;
            array_unshift($type, Types::TYPE_OR);
        }

        return $type;
    }
}
