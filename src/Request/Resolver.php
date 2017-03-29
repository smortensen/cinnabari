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

namespace Datto\Cinnabari\Request;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Request\Language\Properties;
use Datto\Cinnabari\Request\Language\Functions;
use Datto\Cinnabari\Request\Resolver\Applier;
use Datto\Cinnabari\Request\Resolver\Solver;
use Datto\Cinnabari\Request\Resolver\Translator;

class Resolver
{
    /** @var Translator */
    private $translator;

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
        $this->translator = new Translator($functions, $properties);
        $this->solver = new Solver();
        $this->applier = new Applier();
    }

    public function resolve(array $request)
    {
        $constraints = $this->translator->getConstraints($request);
        $solution = $this->solver->solve($constraints);

        if ($solution === null) {
            throw Exception::unresolvableTypeConstraints($request);
        }

        return $this->applier->apply($request, $solution);
    }
}
