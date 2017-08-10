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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Exception;

/**
 * Class AST
 *
 * TODO: This is not necessarily up to date with regard to the Parser output
 *
 * The Abstract Syntax Tree is the intermediate representation which is emitted
 * by the Parser and consumed by the Translator. There may also be phases between
 * these two which operate on the AST (for example, target-independent query
 * optimizations).
 *
 * Currently the data structures of the AST are produced manually by the Parser,
 * and this class serves to help decode them in a transparent way.
 *
 * The AST is currently implemented as an array. Each AST node occupies one
 * entry in the array and is identified by its index.
 * Entry 0 is the top of the tree.
 *
 * A node's format depends on its AST opcode. Each node entry begins with its
 * AST opcode (e.g., AST_PARAMETER).
 *
 * This is an exhaustive list of possible node formats:
 * TODO: This is not complete
 * TODO: need to add support and documentation re types
 *      - array(AST_PARAMETER, name, <types>)
 *      - array(AST_PROPERTY, array of names, <types>)
 *      - array(AST_FUNCTION, name, array of indices of arguments, <types>)
 *      - array(AST_OBJECT, ??)
 */
class AST
{
    // AST node opcodes
    const AST__MIN = 1;
    const AST_PARAMETER = 1;
    const AST_PROPERTY = 2;
    const AST_FUNCTION = 3;
    const AST_OBJECT = 4;
    const AST__MAX = 4;

    private static $astOpcodeNames
        = array(
            '',
            'PARAMETER',
            'PROPERTY',
            'FUNCTION',
            'OBJECT',
        );

    /** @var array */
    private $astArray;

    /** @var int */
    private $highestIndex;


    /**
     * AST constructor.
     *
     * @param array $ast Optional: an already-constructed AST
     */
    public function __construct(array $ast = array())
    {
        $this->astArray = $ast;
        $this->highestIndex = count($ast) > 0 ? max(array_keys($ast)) : -1;
    }


    /**
     * Return the AST node opcode of node $nodeID.
     *
     * @param int $nodeID Identifier of the AST node in question
     *
     * @return int            AST::AST_PARAMETER or whatever
     */
    public function getOpcode($nodeID)
    {
        return $this->astArray[$nodeID][0];
    }


    /**
     * Return the name stored in an AST node. In the case of an
     * AST_PROPERTY node, return the names separated by dots.
     *
     * @param int $nodeID Identifier of the AST node in question
     *
     * @return string
     * @throws Exception
     */
    public function getName($nodeID)
    {
        $astOpcode = $this->astArray[$nodeID][0];

        switch ($astOpcode) {
            case AST::AST_PARAMETER:
            case AST::AST_FUNCTION:
            case AST::AST_OBJECT:
                return $this->astArray[$nodeID][1];
            case AST::AST_PROPERTY:
                return implode('.', $this->astArray[$nodeID][1]);
            default:
                throw Exception::internalError('AST::getName: bad opcode');
        }
    }

    /**
     * Change the name stored in an AST node.
     *
     * @param int    $nodeID Identifier of the AST node in question
     * @param string $name
     *
     * @throws Exception
     */
    public function setName($nodeID, $name)
    {
        $astOpcode = $this->astArray[$nodeID][0];

        switch ($astOpcode) {
            case AST::AST_PARAMETER:
            case AST::AST_FUNCTION:
            case AST::AST_OBJECT:
                $this->astArray[$nodeID][1] = $name;
                break;
            case AST::AST_PROPERTY:
            default:
                throw Exception::internalError('AST::setName: bad opcode');
        }
    }


    /**
     * Return an array of names stored in an AST_PROPERTY node
     *
     * @param int $nodeID Identifier of the AST node in question
     *
     * @return array
     * @throws Exception
     */
    public function getPropertyNameArray($nodeID)
    {
        $astOpcode = $this->astArray[$nodeID][0];

        switch ($astOpcode) {
            case AST::AST_PROPERTY:
                return $this->astArray[$nodeID][1];
            default:
                throw Exception::internalError('AST::getPropertyNameArray: bad opcode');

        }
    }


    /**
     * Return an array of arguments stored in an AST_FUNCTION node
     *
     * @param int $nodeID Identifier of the AST node in question
     *
     * @return array
     * @throws Exception
     */
    public function getFunctionArgumentArray($nodeID)
    {
        $astOpcode = $this->astArray[$nodeID][0];

        switch ($astOpcode) {
            case AST::AST_FUNCTION:
                return $this->astArray[$nodeID][2];
            default:
                throw Exception::internalError('AST::getFunctionArgumentArray: bad opcode');
        }
    }


    /**
     * @param int $nodeID          Identifier of the AST node in question
     * @param int $argumentNumber  The zero-based index of the argument
     * @param int $value           The value (an AST node index)
     *
     * @throws Exception
     */
    public function setFunctionArgument($nodeID, $argumentNumber, $value)
    {
        $astOpcode = $this->astArray[$nodeID][0];

        switch ($astOpcode) {
            case AST::AST_FUNCTION:
                $this->astArray[$nodeID][2][$argumentNumber] = $value;
                break;
            default:
                throw Exception::internalError('AST::setFunctionArgument: bad opcode');
        }
    }


    /**
     * Return the name (e.g., "AST_PARAMETER") of an AST Opcode (e.g. AST_PARAMETER).
     *
     * @param int $opcode
     *
     * @return string
     * @throws Exception
     */
    public static function astOpcodeName($opcode)
    {
        if ($opcode >= AST::AST__MIN && $opcode <= AST::AST__MAX) {
            return AST::$astOpcodeNames[$opcode];
        }

        throw Exception::internalError('AST::astOpcodeName: bad opcode');
    }


    /**
     * Return true if the tree defined by $nodeID contains a call to $name
     *
     * @param int    $nodeID       Root of the tree to search
     * @param string $name         name of the function
     * @param array  &$reversePath If returning true, set to reverse path to call
     *
     * @return bool                True if call to function $name is found
     */
    public function subtreeContainsCallToFunction(
        $nodeID,
        $name,
        &$reversePath = array()
    ) {
        if ($this->getOpcode($nodeID) == self::AST_FUNCTION
            && $this->getName($nodeID) == $name
        ) {
            $reversePath[] = $nodeID;
            return true;
        }

        $arguments = $this->getFunctionArgumentArray($nodeID);

        foreach ($arguments as $arg) {
            if ($this->subtreeContainsCallToFunction($arg, $name, $reversePath)) {
                $reversePath[] = $nodeID;
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new FUNCTION node within the AST data structure
     *
     * @param string $name The name of the function
     * @param array  $arguments
     *
     * @return int                    The node ID of the new node in the AST
     */
    public function newFunctionNode($name, $arguments)
    {
        $this->highestIndex++;
        $this->astArray[$this->highestIndex] = array(
            AST::AST_FUNCTION,
            $name,
            $arguments
        );
        return $this->highestIndex;
    }


    /**
     * Create a new PROPERTY node within the AST data structure
     *
     * @param string[] $names          The array of names
     *
     * @return int                     The node ID of the new node in the AST
     */
    public function newPropertyNode($names)
    {
        $this->highestIndex++;
        $this->astArray[$this->highestIndex] = array(AST::AST_PROPERTY, $names);
        return $this->highestIndex;
    }


    /**
     * Create a new PARAMETER node within the AST data structure
     *
     * @param string $name             The name of the parameter
     *
     * @return int                     The node ID of the new node in the AST
     */
    public function newParameterNode($name)
    {
        $this->highestIndex++;
        $this->astArray[$this->highestIndex] = array(AST::AST_FUNCTION, $name);
        return $this->highestIndex;
    }



    /**
     * Print out a human-readable representation of node $nodeID.
     *
     * @param int $nodeID Identifier of the AST node in question
     */
    public function prettyPrintNode($nodeID)
    {
        $opcode = $this->getOpcode($nodeID);
        $nm = $this->getName($nodeID);

        echo $nodeID, ": ", $this->astOpcodeName($opcode), ", ", $nm;

        if ($this->getOpcode($nodeID) == self::AST_FUNCTION) {
            $args = $this->getFunctionArgumentArray($nodeID);
            echo '(', json_encode($args), ")";
        }

        echo "\n";
    }


    /**
     * Print out a human-readable representation of all nodes in the AST.
     */
    public function prettyPrintAllNodes()
    {
        for ($ix = 0; $ix <= $this->highestIndex; $ix++) {
            if (array_key_exists($ix, $this->astArray)) {
                $this->prettyPrintNode($ix);
            }
        }
    }
}
