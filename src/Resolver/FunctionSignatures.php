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

namespace Datto\Cinnabari\Resolver;

use Datto\Cinnabari\Types;

class FunctionSignatures
{
    public static function getFunctionSignature($function)
    {
        switch ($function) {
            case 'average':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                        Types::TYPE_OBJECT
                    )
                );

            case 'count':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 2),
                        array(Types::TYPE_ARRAY, 3),
                        Types::TYPE_INTEGER,
                        Types::TYPE_OBJECT
                    )
                );

            case 'delete':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 2),
                        array(Types::TYPE_ARRAY, 3),
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_OBJECT
                    )
                );

            case 'filter':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        array(Types::TYPE_ARRAY, 3),
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_OBJECT
                    )
                );

            case 'get':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 3), 3),
                        array(Types::TYPE_ARRAY, 2),
                        Types::TYPE_OBJECT
                    )
                );

            case 'insert':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 2),
                        Types::TYPE_OBJECT,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'max':
            case 'min':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                        Types::TYPE_OBJECT
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                        Types::TYPE_OBJECT
                    )
                );

            case 'set':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        Types::TYPE_OBJECT,
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_OBJECT
                    )
                );

            case 'slice':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2, 2), 1),
                        array(Types::TYPE_ARRAY, 3),
                        Types::TYPE_INTEGER,
                        Types::TYPE_OBJECT
                    )
                );

            case 'sort':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        array(Types::TYPE_ARRAY, 3),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN, Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_STRING),
                        Types::TYPE_OBJECT
                    )
                );

            case 'sum':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                        Types::TYPE_OBJECT
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_ARRAY, 4),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                        Types::TYPE_OBJECT
                    )
                );

            case 'length':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 1),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 2),
                        Types::TYPE_STRING,
                        Types::TYPE_INTEGER
                    )
                );

            case 'match':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING),
                        Types::TYPE_STRING,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'substring':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2, 2), 1),
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2, 2), 1),
                        Types::TYPE_STRING,
                        Types::TYPE_INTEGER
                    )
                );

            case 'lowercase':
            case 'uppercase':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING)
                    )
                );

            case 'times':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER
                    )
                );

            case 'divides':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT)
                    )
                );

            case 'plus':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_STRING)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_STRING)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT, Types::TYPE_STRING),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER
                    )
                );

            case 'minus':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER
                    )
                );

            case 'less':
            case 'lessEqual':
            case 'equal':
            case 'notEqual':
            case 'greaterEqual':
            case 'greater':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER, Types::TYPE_FLOAT)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 3),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        array(Types::TYPE_OR, Types::TYPE_INTEGER, Types::TYPE_FLOAT),
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'not':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN)
                    )
                );

            case 'and':
            case 'or':
                return array(
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 1), 1),
                        array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN)
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 1),
                        Types::TYPE_NULL,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        array(Types::TYPE_FUNCTION, array(1, 2), 2),
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_NULL
                    )
                );

            default:
                // TODO: throw exception
                return null;
        }
    }
}
