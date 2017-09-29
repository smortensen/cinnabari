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
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\AbstractArtifact;

use Datto\Cinnabari\AbstractArtifact\Statements\AbstractStatement;

/**
 * Class AbstractArtifact - A Somewhat Sql-like Intermediate Language
 *
 * AbstractArtifact is an object-oriented intermediate representation.
 * It is intended for use as the output of the Translator, and as input
 * to the formatter. (Actually, one or more phases may be needed before the
 * formatter; these will operate on the AbstractArtifact representation.)
 */
class AbstractArtifact
{
    /** @var Parameter[] */
    private $parameters;

    /** @var AbstractStatement[] */
    private $statements;

    public function __construct()
    {
        $this->parameters = array();
        $this->statements = array();
    }

    /**
     * Make a query parameter known to AbstractArtifact.
     *
     * @param Parameter $parameter
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * Return an array of the query parameters known to AbstractArtifact via the
     * addParameter function.
     *
     * @return array|Parameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param AbstractStatement $statement (e.g., Select)
     */
    public function addStatement(AbstractStatement $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * Return an array of the Statements constituting this query.
     * @return array|AbstractStatement[]
     */
    public function getStatements()
    {
        return $this->statements;
    }
}
