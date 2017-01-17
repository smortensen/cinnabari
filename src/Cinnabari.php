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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Request\Language\Functions;
use Datto\Cinnabari\Request\Language\Operators;
use Datto\Cinnabari\Request\Language\Properties;
use Datto\Cinnabari\Request\Lexer;
use Datto\Cinnabari\Request\Parser;
use Datto\Cinnabari\Request\Resolver;

class Cinnabari
{
    /** @var Operators */
    private $operators;

    /** @var Functions */
    private $functions;

    /** @var Properties */
    private $properties;

    /**
     * Cinnabari constructor.
     *
     * @param Operators $operators
     * @param Functions $functions
     * @param Properties $properties
     */
    public function __construct(Operators $operators, Functions $functions, Properties $properties)
    {
        $this->operators = $operators;
        $this->functions = $functions;
        $this->properties = $properties;
    }

    public function translate($query)
    {
        $lexer = new Lexer();
        $parser = new Parser($this->operators);
        $resolver = new Resolver($this->functions, $this->properties);

        $request = $lexer->tokenize($query);
        $request = $parser->parse($request);
        $request = $resolver->resolve($request);

        echo "request: ", json_encode($request), "\n";
        exit;

        $validator = new InputValidation();
        $compiler = new Compiler();
        $phpInputValidation = $validator->getPhp($request);
        $output = $compiler->compile($request);

        return null;
    }
}
