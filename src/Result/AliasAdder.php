<?php

/**
 * Copyright (C) 2017 Datto, Inc.
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
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Result;

use Datto\Cinnabari\Result\SIL\SIL;
use Datto\Cinnabari\Result\SIL\Statements\Select;

/**
 * Class AliasAdder
 *
 * Attach MySQL aliases to values, tables, joins, and parameters.
 *
 * @package Datto\Cinnabari\Result
 */

class AliasAdder
{
    /** @var SIL */
    private $sil;

    public function __construct($sil)
    {
        $this->sil = $sil;
    }

    public function aliasAdder()
    {
        $parameters = $this->sil->getParameters();
        $statements = $this->sil->getStatements();
        $paramCounter = 0;
        $tableCounter = 0;   // For FROMs, JOINs, ...
        $valueCounter = 0;

        // Generate aliases for parameters
        foreach ($parameters as $parameter) {
            $parameter->setAlias($paramCounter);
            $paramCounter++;
        }

        foreach ($statements as $statement) {
            if ($statement instanceof Select) {

                // Generate alias for FROM table
                if ($statement->getFrom()->getTable() !== null) {
                    $statement->getFrom()->setAlias($tableCounter);
                    $tableCounter++;
                }

                // Generate aliases for SELECT values
                foreach ($statement->getValues() as $value) {
                    $value->setAlias($valueCounter);
                    $valueCounter++;
                }
            }

            // Generate aliases for JOINS
            foreach ($statement->getJoins() as $join) {
                $join->setAlias($tableCounter);
                $tableCounter++;
            }
        }
    }
}
