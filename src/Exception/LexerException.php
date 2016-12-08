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

namespace Datto\Cinnabari\Exception;

class LexerException extends Exception
{
    const TYPE_INVALID = 1;
    const SYNTAX_INVALID = 2;

    public static function typeInvalid($input)
    {
        $code = self::TYPE_INVALID;

        $data = array(
            'statement' => $input
        );

        $description = self::getValueDescription($input);

        $message = "Expected a string query,"
            . " but received {$description} instead.";

        return new self($code, $data, $message);
    }

    public static function syntaxInvalid($input, $position)
    {
        $code = self::SYNTAX_INVALID;

        $data = array(
            'statement' => $input,
            'position' => $position
        );

        $tail = self::getTail($input, $position);
        $tailJson = json_encode($tail);

        list($line, $character) = self::getLineCharacter($input, $position);

        $message = "Syntax error near {$tailJson}"
            . " on line {$line} character {$character}.";

        return new self($code, $data, $message);
    }

    private static function getValueDescription($value)
    {
        $type = gettype($value);

        switch ($type) {
            case 'NULL':
                return 'a null value';

            case 'boolean':
                $valueJson = json_encode($value);
                return "a boolean ({$valueJson})";

            case 'integer':
                $valueJson = json_encode($value);
                return "an integer ({$valueJson})";

            case 'double':
                $valueJson = json_encode($value);
                return "a float ({$valueJson})";

            case 'string':
                $valueJson = json_encode($value);
                return "a string ({$valueJson})";

            case 'array':
                $valueJson = json_encode($value);
                return "an array ({$valueJson})";

            case 'object':
                return 'an object';

            case 'resource':
                return 'a resource';

            default:
                return 'an unknown value';
        }
    }

    private static function getTail($input, $position)
    {
        $tail = ltrim(substr($input, $position));
        $newlinePosition = strpos($tail, "\n");

        if ($newlinePosition !== false) {
            $tail = substr($tail, 0, $newlinePosition);
        }

        $tail = rtrim($tail);

        $maximumLength = 72;

        if ($maximumLength < strlen($tail)) {
            $tail = substr($tail, 0, $maximumLength) . '...';
        }

        return $tail;
    }

    private static function getLineCharacter($input, $errorPosition)
    {
        $iLine = 0;
        $iCharacter = 0;

        $lines = preg_split('~\r?\n~', $input, null, PREG_SPLIT_OFFSET_CAPTURE);

        foreach ($lines as $line) {
            list($lineText, $linePosition) = $line;

            $iCharacter = $errorPosition - $linePosition;

            if ($iCharacter <= strlen($lineText)) {
                break;
            }

            ++$iLine;
        }

        return array($iLine + 1, $iCharacter + 1);
    }
}
