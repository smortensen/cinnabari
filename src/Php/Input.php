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
 * @author Anthony Liu <aliu@datto.com>
 * @author Spencer Mortensen <smortensen@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016 Datto, Inc.
 */

namespace Datto\Cinnabari\Php;

class Input
{
    /** @var array */
    private $hasZero;

    /** @var array */
    private $output;

    public function __construct()
    {
        $this->hasZero = array();
        $this->output = array();
    }

    public function useArgument($name, $hasZero)
    {
        $this->hasZero[$name] = $hasZero;

        $input = self::getInputPhp($name);

        return $this->insertParameter($input);
    }

    public function useSliceBeginArgument($name, $hasZero)
    {
        $this->hasZero[$name] = $hasZero;

        $input = self::getInputPhp($name);
        $php = "max({$input}, 0)";

        return $this->insertParameter($php);
    }

    public function useSliceEndArgument($nameA, $nameB, $hasZero)
    {
        $this->hasZero[$nameA] = $hasZero;
        $this->hasZero[$nameB] = $hasZero;

        $inputA = self::getInputPhp($nameA);
        $inputB = self::getInputPhp($nameB);
        $php = "(max({$inputA}, 0) < {$inputB}) ? ({$inputB} - max({$inputA}, 0)): 0";

        return $this->insertParameter($php);
    }

    public function useSubstringBeginArgument($name, $hasZero)
    {
        $this->hasZero[$name] = $hasZero;

        $input = self::getInputPhp($name);
        $php = "1 + max({$input}, 0)";

        return $this->insertParameter($php);
    }

    public function useSubstringEndArgument($nameA, $nameB, $hasZero)
    {
        $this->hasZero[$nameA] = $hasZero;
        $this->hasZero[$nameB] = $hasZero;

        $inputA = self::getInputPhp($nameA);
        $inputB = self::getInputPhp($nameB);
        $php = "{$inputB} - max({$inputA}, 0)";

        return $this->insertParameter($php);
    }

    public function useSubtractiveArgument($nameA, $nameB, $hasZero)
    {
        $this->hasZero[$nameA] = $hasZero;
        $this->hasZero[$nameB] = $hasZero;

        $inputA = self::getInputPhp($nameA);
        $inputB = self::getInputPhp($nameB);

        $php = "{$inputB} - {$inputA}";
        return $this->insertParameter($php);
    }

    public function getPhp($types)
    {
        $parameters = array_map('strval', $types['ordering']);
        $statementsPhp = array_map('self::getArgumentExistencePhp', $parameters);

        $hierarchy = $types['hierarchy'];
        $statementsPhp[] = $this->getOutputArrayPhp($parameters, $hierarchy);

        return implode("\n\n", $statementsPhp);
    }

    protected function getArgumentExistencePhp($parameter)
    {
        $key = var_export($parameter, true);

        return <<<EOS
if (!array_key_exists({$key}, \$input)) {
    throw new Exception({$key}, 1);
}
EOS;
    }

    private function getOutputArrayPhp($parameters, $hierarchy)
    {
        $statements = array_flip($this->output);
        $array = self::getArrayPhp($statements);
        $assignment = self::getAssignmentPhp('$output', $array);

        if (count($this->hasZero) === 0) {
            return $assignment;
        }

        return $this->getGuardedAssignment($parameters, $hierarchy, $assignment);
    }

    private function getGuardedAssignment($parameters, $hierarchy, $body)
    {
        $typeChecks = self::getTypeChecks($parameters, $hierarchy);

        $nullAssignment = self::getAssignmentPhp('$output', 'null');

        return self::getIfElsePhp($typeChecks, $body, $nullAssignment);
    }

    private function getTypeChecks($parameters, $hierarchy)
    {
        $rootName = $parameters[0];
        $nullCheck = self::getSingleTypeCheck($rootName, Output::TYPE_NULL);

        if (reset($hierarchy) === true) {
            # if this is the last layer of checks
            $hierarchyChecks = array();

            if ($this->hasZero[$rootName] === true) {
                $hierarchyChecks[] = $nullCheck;
            }

            foreach ($hierarchy as $type => $value) {
                $hierarchyChecks[] = self::getSingleTypeCheck($rootName, $type);
            }

            if (count($hierarchyChecks) > 1) {
                return self::group(self::getOr($hierarchyChecks));
            } else {
                return self::getOr($hierarchyChecks);
            }
        } else {
            # there are nested checks within this, so recurse
            $typeChecks = array();

            foreach ($hierarchy as $rootType => $subhierarchy) {
                $typeCheck = self::getSingleTypeCheck($rootName, $rootType);

                if ($this->hasZero[$rootName] === true) {
                    $typeCheck = self::group(
                        "\n" . self::indent(self::getOr(array($nullCheck, $typeCheck))) . "\n"
                    );
                }

                $conditional = self::getTypeChecks(array_slice($parameters, 1), $subhierarchy);
                $typeChecks[] = self::group(
                    "\n" . self::indent(self::getAnd(array($typeCheck, $conditional))) . "\n"
                );
            }
            if (count($typeChecks) > 1) {
                return self::group(
                    "\n" . self::indent(self::getOr($typeChecks)) . "\n"
                );
            } else {
                return self::getOr($typeChecks);
            }
        }
    }

    private static function getSingleTypeCheck($name, $type)
    {
        $variable = self::getInputPhp($name);

        // TODO: fix this!
        // There is a bug in the type system, where "null|string" values are
        // checked as though they were "string" values
        //
        // This hack causes any parameter named 'null' to be checked as a null value
        if ($name === 'null') {
            return 'true';
        }

        return self::getTypeCheckPhp($variable, $type);
    }

    private static function getTypeCheckPhp($variable, $type)
    {
        switch ($type) {
            case Output::TYPE_STRING:
                return "is_string({$variable})";

            case Output::TYPE_FLOAT:
                return "is_float({$variable})";

            case Output::TYPE_INTEGER:
                return "is_integer({$variable})";

            case Output::TYPE_BOOLEAN:
                return "is_bool({$variable})";

            default: // Output::TYPE_NULL
                return "is_null({$variable})";
        }
    }

    private function insertParameter($inputString)
    {
        $id = &$this->output[$inputString];

        if ($id === null) {
            $id = count($this->output) - 1;
        }

        return $id;
    }

    private static function group($expression)
    {
        return "({$expression})";
    }

    private static function getAnd($expressions)
    {
        return self::getBinaryOperatorChain($expressions, '&&');
    }

    private static function getOr($expressions)
    {
        return self::getBinaryOperatorChain($expressions, '||');
    }

    private static function getBinaryOperatorChain($expressions, $operator)
    {
        return implode(" {$operator} ", $expressions);
    }

    private static function getIfElsePhp($conditional, $body, $else)
    {
        $php = self::getIfStatementPhp($conditional, $body);
        $indentedElse = self::indent($else);
        $php .= " else {\n{$indentedElse}\n}";

        return $php;
    }

    private static function getIfStatementPhp($conditional, $body)
    {
        $indentedBody = self::indent($body);

        if (!self::isGrouped($conditional)) {
            $conditional = "({$conditional})";
        }

        return "if {$conditional} {\n{$indentedBody}\n}";
    }

    private static function isGrouped($input)
    {
        if ($input[0] !== '(') {
            return false;
        }
        $parentheses = preg_replace('/[^()]/', '', $input);
        $count = 0;
        for ($i = 1; $i < count($parentheses) - 1; $i++) {
            $count += ($parentheses[$i] === '(') ? 1 : -1;
            if ($count < 0) {
                return false;
            }
        }
        // assumes well-balanced parentheses to begin with
        return $count === 0;
    }

    private static function getArrayPhp($statements)
    {
        $body = '';

        if (0 < count($statements)) {
            for ($i = 0, $n = count($statements); $i < $n; ++$i) {
                $body .= ",\n\t':{$i}' => {$statements[$i]}";
            }

            $body = substr($body, 1) . "\n";
        }

        return "array({$body})";
    }

    private static function getAssignmentPhp($variable, $value)
    {
        return "{$variable} = {$value};";
    }

    private static function getInputPhp($parameter)
    {
        $parameterName = var_export($parameter, true);
        return "\$input[{$parameterName}]";
    }

    private static function indent($string)
    {
        return "\t" . preg_replace('~\n(?!\n)~', "\n\t", $string);
    }
}
