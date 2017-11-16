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

namespace Datto\Cinnabari\Phases\Resolver;

use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Entities\Request\Request;
use Datto\Cinnabari\Entities\Request\FunctionRequest;
use Datto\Cinnabari\Entities\Request\ObjectRequest;

class Applier
{
    /** @var array */
    private $solution;

    public function apply(Request $token, array $solution)
    {
        $this->solution = $solution;

        $id = 0;
        $this->read($token, $id);
    }

    private function read(Request $token, &$id)
    {
        $dataType = $this->getDataType($id++);
        $token->setDataType($dataType);

        $tokenType = $token->getNodeType();

        if ($tokenType === Request::TYPE_FUNCTION) {
            /** @var FunctionRequest $token */
            $arguments = $token->getArguments();
            $this->readTokens($arguments, $id);
        } elseif ($tokenType === Request::TYPE_OBJECT) {
            /** @var ObjectRequest $token */
            $properties = $token->getProperties();
            $this->readTokens($properties, $id);
        }
    }

    private function getDataType($id)
    {
        $solution = $this->solution[$id];

        if (count($solution) === 1) {
            $dataType = $solution[0];
        } else {
            $dataType = $solution;
            array_unshift($dataType, Types::TYPE_OR);
        }

        return $dataType;
    }

    /**
     * @param Request[] $tokens
     * @param integer $id
     */
    private function readTokens(array $tokens, &$id)
    {
        foreach ($tokens as $token) {
            $this->read($token, $id);
        }
    }
}
