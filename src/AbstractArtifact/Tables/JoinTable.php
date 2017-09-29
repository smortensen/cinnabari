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

namespace Datto\Cinnabari\AbstractArtifact\Tables;

use Datto\Cinnabari\Pixies\AliasMapper;

/**
 * Class JoinTable
 *
 * The AbstractArtifact equivalent of a (My)SQL JOIN.
 */
class JoinTable extends AbstractTable
{
    /** @var string */
    private $name;

    /** @var bool */
    private $isInner;

    /** @var string */
    private $criterion;

    /**
     * JoinTable constructor.
     *
     * @param string      $name
     * @param AliasMapper $mapper
     * @param             $isInner
     */
    public function __construct($name, AliasMapper $mapper, $isInner)
    {
        $this->name = $name;
        $this->isInner = $isInner;
        $this->criterion = null;
        parent::__construct($mapper);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setCriterion($criterion)
    {
        $this->criterion = $criterion;
    }

    public function getCriterion()
    {
        return $this->criterion;
    }

    public function getIsInner()
    {
        return $this->isInner;
    }
}
