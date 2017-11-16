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

namespace Datto\Cinnabari\Entities;

use Datto\Cinnabari\Phases\Translator\Aliases;

/*
PARAMETERS:
parameterName, dataType

ALIASES:
aliasId => phpTransformation (e.g. "$input['end'] - max($input['begin'], 0)")
*/
class Parameters
{
    /** @var array */
    private $apiParameters;

    /** @var array */
    private $databaseParameters;

    /** @var Aliases */
    private $databaseParameterAliases;

    public function __construct()
    {
        $this->apiParameters = array();
        $this->databaseParameters = array();
        $this->databaseParameterAliases = new Aliases();
    }

    public function addApiParameter($name, $dataType)
    {
        $this->apiParameters[$name] = $dataType;
    }

    public function getApiParameters()
    {
        return $this->apiParameters;
    }

    public function addDatabaseParameter($php)
    {
        $alias = $this->databaseParameterAliases->getAlias($php);
        $key = ":{$alias}";

        $this->databaseParameters[$key] = $php;

        return $alias;
    }

    public function getDatabaseParameters()
    {
        return $this->databaseParameters;
    }
}
