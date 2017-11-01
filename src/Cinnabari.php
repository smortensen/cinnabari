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

use Datto\Cinnabari\Parser\Language\Functions;
use Datto\Cinnabari\Parser\Language\Operators;
use Datto\Cinnabari\Parser\Language\Properties;
use Datto\Cinnabari\Pixies\Php\Input\Validator;
use Datto\Cinnabari\Translator\Map;
use Datto\Cinnabari\Translator\Translator;
use Datto\Cinnabari\Compiler\Compiler;

class Cinnabari
{
    /** @var Parser */
    private $parser;

    /** @var Validator */
    private $validator;

    /**
     * Cinnabari constructor.
     *
     * @param Functions $functions
     * @param Operators $operators
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Operators $operators, Properties $properties, Map $map)
    {
        $this->parser = new Parser($functions, $operators, $properties);
        $this->translator = new Translator($map);
        $this->compiler = new Compiler();
    }

    public function translate($query)
    {
        $request = $this->parser->parse($query);
        $translation = $this->translator->translate($request);
        return $this->compiler->compile($translation);
    }
}
