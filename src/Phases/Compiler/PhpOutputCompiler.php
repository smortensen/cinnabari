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

namespace Datto\Cinnabari\Phases\Compiler;

use Datto\Cinnabari\Entities\Language\Types;
use SpencerMortensen\RegularExpressions\Re;

class PhpOutputCompiler
{
    public function getOutputPhp($aliasId, $dataType)
    {
        $php = $this->getValuePhp($aliasId);
        $php = $this->getCastingPhp($php, $dataType);
        return $this->getAssignmentPhp($php);
    }

    public function getValuePhp($aliasId)
    {
        return "\$row[{$aliasId}]";
    }

    public function getCastingPhp($valuePhp, $type)
    {
        if (is_array($type)) {
            return self::castCompoundValue($valuePhp, $type);
        }

        return self::castPrimitiveValue($valuePhp, $type);
    }

    private static function castCompoundValue($valuePhp, array $type)
    {
        $arguments = $type;
        $type = array_shift($arguments);

        if (($type !== Types::TYPE_OR) || !self::isNullable($arguments) || (count($arguments) !== 1)) {
            // TODO: throw exception
            return null;
        }

        $argument = current($arguments);
        $castPhp = self::castPrimitiveValue($valuePhp, $argument);

        if ($argument === Types::TYPE_STRING) {
            return $valuePhp;
        }

        return "isset({$valuePhp}) ? {$castPhp} : null";
    }

    private static function isNullable(array &$types)
    {
        foreach ($types as $key => $type) {
            if ($type === Types::TYPE_NULL) {
                unset($types[$key]);
                return true;
            }
        }

        return false;
    }

    private static function castPrimitiveValue($valuePhp, $type)
    {
        switch ($type) {
            case Types::TYPE_BOOLEAN:
                return "(boolean){$valuePhp}";

            case Types::TYPE_INTEGER:
                return "(integer){$valuePhp}";

            case Types::TYPE_FLOAT:
                return "(float){$valuePhp}";

            case Types::TYPE_STRING:
                return $valuePhp;

            default:
                // TODO: throw exception
                return null;
        }
    }

    public function getAssignmentPhp($php)
    {
        return "\$output = {$php};";
    }

    public function getArrayPhp($assignmentPhp, $indexPhp)
    {
        return Re::replace(' =', "[{$indexPhp}] =", $assignmentPhp);
    }

    public function getRowsPhp($php)
    {
        $indentedPhp = self::indent($php);

        return "foreach (\$rows as \$row) {\n{$indentedPhp}\n}";
    }

    private static function indent($string)
    {
        return "\t" . preg_replace('~\n(?!\n)~', "\n\t", $string);
    }
}
