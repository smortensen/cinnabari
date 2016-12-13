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

class RemoveUnnecessarySorting
{
    const MODE_SAVE = 0;
    const MODE_DELETE = 1;

    public function optimize($token)
    {
        return $this->convert($token, self::MODE_SAVE);
    }

    private function convert($token, $mode = self::MODE_SAVE)
    {
        $type = $token[0];

        if ($type === Parser::TYPE_FUNCTION) {
            return $this->getFunction($token, $mode);
        }

        if ($type === Parser::TYPE_OBJECT) {
            return $this->getObject($token);
        }

        return $token;
    }

    private function getFunction($token, $mode)
    {
        switch ($token[1]) {
            case 'sort':
                if ($mode === self::MODE_SAVE) {
                    return $this->convertFunction($token, self::MODE_DELETE);
                }

                return $this->convert($token[2], $mode);

            case 'average':
            case 'count':
            case 'delete':
            case 'max':
            case 'min':
            case 'set':
            case 'sum':
                return $this->convertFunction($token, self::MODE_DELETE);

            case 'get':
            case 'slice':
                return $this->convertFunction($token, self::MODE_SAVE);

            case 'filter':
                return $this->convertFunction($token, $mode);

            default:
                return $token;
        }
    }

    private function getObject($input)
    {
        $output = $input;
        $output[1] = $this->convertArray($input[1], self::MODE_SAVE);

        return $output;
    }

    private function convertFunction($input, $mode)
    {
        $arguments = array_slice($input, 2);
        $output = $this->convertArray($arguments, $mode);
        array_unshift($output, $input[0], $input[1]);

        return $output;
    }

    private function convertArray($input, $mode)
    {
        $output = array();

        foreach ($input as $key => $value) {
            $output[$key] = $this->convert($value, $mode);
        }

        return $output;
    }
}
