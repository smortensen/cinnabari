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

use Datto\Cinnabari\Entities\Language\Functions;
use Datto\Cinnabari\Entities\Language\Properties;
use Datto\Cinnabari\Entities\Request\Request;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Phases\Resolver\Satisfiability\Solver;

class Resolver
{
    /** @var Analyzer */
    private $analyzer;

    /** @var Solver */
    private $solver;

    /** @var Applier */
    private $applier;

    /**
     * Resolver constructor.
     *
     * @param Functions $functions
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Properties $properties)
    {
        $this->analyzer = new Analyzer($functions, $properties);
        $this->solver = new Solver();
        $this->applier = new Applier();
    }

    public function resolve(Request $input)
    {
        $constraints = $this->analyzer->analyze($input);
        $solution = $this->solver->solve($constraints);

        if ($solution === null) {
            throw Exception::unresolvableTypeConstraints($input);
        }

        $this->applier->apply($input, $solution);

        return $input;
    }
}
