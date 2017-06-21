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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Language\Functions;
use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Result\Php\Input\Validator;

class Cinnabari
{
    /** @var Parser */
    private $parser;

    /** @var Resolver */
    private $resolver;

    /** @var Validator */
    private $validator;

    /**
     * Cinnabari constructor.
     *
     * @param Functions $functions
     * @param Operators $operators
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Operators $operators, Properties $properties)
    {
        $this->parser = new Parser($operators);
        $this->resolver = new Resolver($functions, $properties);
        $this->validator = new Validator();
    }

    public function translate($query)
    {
        $request = $this->parser->parse($query);
        $request = $this->resolver->resolve($request);

        $mysql = null;
        $phpInput = $this->validator->validate($request);
        $phpOutput = null;

        return array($mysql, $phpInput, $phpOutput);
    }
}
