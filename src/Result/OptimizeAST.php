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

namespace Datto\Cinnabari\Result;

use Datto\Cinnabari\AST;

/**
 * Class OptimizeAST
 *
 * Perform target-independent optimizations on the Abstract Syntax Tree.
 *
 */

class OptimizeAST
{
    /** @var AST */
    private $ast;

    /**
     * FixMysqlAST constructor.
     *
     * @param AST $ast
     */
    public function __construct($ast)
    {
        $this->ast = $ast;
    }


    /**
     * Make some general optimizations to the AST, applicable to all targets.
     */
    public function optimize()
    {
        $root = $this->ast->getRoot();
        $this->fixUselessSorts($root, false, -1);
        $this->fixFilterOfSort($root);
        $this->fixInsert($root);
    }

    /**
     * filter(sort(a, b), c) => sort(filter(a, c), b).  (Also works for rsort.)
     *
     * This is a general optimization, assuming that the filter runs in linear time.
     * Also, if the eventual output is (My)SQL, the new ordering simplifies the
     * output by removing a subquery.
     * This sequence may match more than once in a single request.
     *
     * @param int $nodeID
     */
    private function fixFilterOfSort($nodeID)
    {
        if ($this->ast->getOpcode($nodeID) !== AST::AST_FUNCTION) {
            return;
        }

        $topKids = $this->ast->getFunctionArgumentArray($nodeID);

        foreach ($topKids as $kid) {
            $this->fixFilterOfSort($kid);
        }

        if ($this->ast->getOpcode($nodeID) == AST::AST_FUNCTION
            && $this->ast->getName($nodeID) == 'filter'
            && in_array($this->ast->getName($topKids[0]), array('rsort', 'sort'))
        ) {
            $bottomKids = $this->ast->getFunctionArgumentArray($topKids[0]);
            $this->ast->setName($nodeID, $this->ast->getName($topKids[0]));
            $this->ast->setName($topKids[0], 'filter');
            $this->ast->setFunctionArgument($nodeID, 1, $bottomKids[1]);
            $this->ast->setFunctionArgument($topKids[0], 1, $topKids[1]);
        }
    }


    /**
     * insert(f(a, b), c) => insert(a, c) where "f" is "(r)sort", "slice", or "filter."
     *
     * An optimization to collapse (r)sort, slice and filter functions when the
     * request method is "insert."
     * This rule may apply more than once during the simplification of a request.
     *
     * @param int $nodeID
     */
    private function fixInsert($nodeID)
    {
        if ($this->ast->getOpcode($nodeID) !== AST::AST_FUNCTION) {
            return;
        }

        $useless = array('rsort', 'sort', 'slice', 'filter');
        $topKids = $this->ast->getFunctionArgumentArray($nodeID);

        foreach ($topKids as $kid) {
            $this->fixInsert($kid);
        }

        if ($this->ast->getOpcode($nodeID) == AST::AST_FUNCTION
            && $this->ast->getName($nodeID) == 'insert'
            && in_array($this->ast->getName($topKids[0]), $useless)
        ) {
            $bottomKids = $this->ast->getFunctionArgumentArray($topKids[0]);
            $this->ast->setFunctionArgument($nodeID, 0, $bottomKids[0]);
        }
    }


    /**
     * A sort or rsort below an aggregate or sort function is useless and
     * can be removed, unless there is a slice between them.
     *
     * @param int  $nodeID    Node we are examining
     * @param bool $mode      If true, delete $nodeID if it's (r)sort
     * @param int  $parentID  ID of $nodeID's parent
     * TODO: We need a function under Language to determine if "xx" is an aggregate function.
     * TODO: We need a function under Language to determine if "xx" is a sort/rsort.
     */
    private function fixUselessSorts($nodeID, $mode, $parentID)
    {
        if ($this->ast->getOpcode($nodeID) !== AST::AST_FUNCTION) {
            return;
        }

        $nodeName = $this->ast->getName($nodeID);

        if ($mode && $parentID >= 0
            && in_array($nodeName, array('sort', 'rsort'))) {
            $sortKids = $this->ast->getFunctionArgumentArray($nodeID);
            $this->ast->setFunctionArgument($parentID, 0, $sortKids[0]);  // orphanize the (r)sort
            $nodeID = $parentID;
        }

        $interestingFunctions = array(
            'average',
            'count',
            'delete',
            'max',
            'min',
            'rsort',
            'set',
            'sort',
            'sum'
        );

        if (in_array($nodeName, $interestingFunctions)) {
            $mode = true;   // if we subsequently see a sort below this, delete it
        } elseif ($nodeName == 'slice') {
            $mode = false;  // A sort below a slice must not be removed
        }

        $kids = $this->ast->getFunctionArgumentArray($nodeID);

        foreach ($kids as $kid) {
            $this->fixUselessSorts($kid, $mode, $nodeID);
        }
    }
}
