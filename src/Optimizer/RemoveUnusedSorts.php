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

namespace Datto\Cinnabari\Optimizer;

use Datto\Cinnabari\Parser;

class RemoveUnusedSorts
{
    const MODE_SAVE = 0;
    const MODE_DELETE = 1;

    public function optimize($token)
    {
        return $this->optimizeToken($token, self::MODE_SAVE);
    }

    private function optimizeToken($token, $mode = self::MODE_SAVE)
    {
        $type = $token[0];

        if ($type === Parser::TYPE_FUNCTION) {
            return $this->optimizeFunctionToken($token, $mode);
        }

        if ($type === Parser::TYPE_OBJECT) {
            return $this->optimizeObjectToken($token);
        }

        return $token;
    }

    private function optimizeFunctionToken($token, $mode)
    {
        $function = $token[1];

        if (($function === 'sort') && ($mode === self::MODE_DELETE)) {
            return $this->deleteFunctionToken($token, self::MODE_DELETE);
        }

        switch ($function) {
            case 'average':
            case 'count':
            case 'delete':
            case 'max':
            case 'min':
            case 'set':
            case 'sort':
            case 'sum':
                $mode = self::MODE_DELETE;
                break;

            case 'get':
            case 'slice':
                $mode = self::MODE_SAVE;
                break;

            case 'filter':
                break;

            default:
                return $token;
        }

        $arguments = $this->optimizeValues($token[2], $mode);

        return array(Parser::TYPE_FUNCTION, $function, $arguments);
    }

    private function deleteFunctionToken($token, $mode)
    {
        $arguments = $token[2];

        if (0 < count($arguments)) {
            return $this->optimizeToken($arguments[0], $mode);
        }

        return $token;
    }

    private function optimizeObjectToken($input)
    {
        $object = $this->optimizeValues($input[1], self::MODE_SAVE);

        return array(Parser::TYPE_OBJECT, $object);
    }

    private function optimizeValues($input, $mode)
    {
        $output = array();

        foreach ($input as $key => $value) {
            $output[$key] = $this->optimizeToken($value, $mode);
        }

        return $output;
    }
}
