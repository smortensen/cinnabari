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

class FunctionSignatures
{
    const TYPE_NULL = 1;
    const TYPE_BOOLEAN = 2;
    const TYPE_INTEGER = 3;
    const TYPE_FLOAT = 4;
    const TYPE_STRING = 5;
    const TYPE_OBJECT = 6;
    const TYPE_ARRAY = 7;
    const TYPE_FUNCTION = 8;
    const TYPE_OR = 9;

    public static function getFunctionSignature($function)
    {
        switch ($function) {
            case 'average':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT),
                        self::TYPE_OBJECT
                    )
                );

            case 'count':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1), 2),
                        array(self::TYPE_ARRAY, 3),
                        self::TYPE_INTEGER,
                        self::TYPE_OBJECT
                    )
                );

            case 'delete':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1), 2),
                        array(self::TYPE_ARRAY, 3),
                        self::TYPE_BOOLEAN,
                        self::TYPE_OBJECT
                    )
                );

            case 'filter':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        array(self::TYPE_ARRAY, 3),
                        self::TYPE_BOOLEAN,
                        self::TYPE_OBJECT
                    )
                );

            case 'get':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 3), 3),
                        array(self::TYPE_ARRAY, 2),
                        self::TYPE_OBJECT
                    )
                );

            case 'insert':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 2),
                        self::TYPE_OBJECT,
                        self::TYPE_BOOLEAN
                    )
                );

            case 'max':
            case 'min':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER),
                        self::TYPE_OBJECT
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT),
                        self::TYPE_OBJECT
                    )
                );

            case 'set':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        self::TYPE_OBJECT,
                        self::TYPE_BOOLEAN,
                        self::TYPE_OBJECT
                    )
                );

            case 'slice':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2, 2), 1),
                        array(self::TYPE_ARRAY, 3),
                        self::TYPE_INTEGER,
                        self::TYPE_OBJECT
                    )
                );

            case 'sort':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        array(self::TYPE_ARRAY, 3),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_BOOLEAN, self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_STRING),
                        self::TYPE_OBJECT
                    )
                );

            case 'sum':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER),
                        self::TYPE_OBJECT
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_ARRAY, 4),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT),
                        self::TYPE_OBJECT
                    )
                );

            case 'length':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1), 1),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1), 2),
                        self::TYPE_STRING,
                        self::TYPE_INTEGER
                    )
                );

            case 'match':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_STRING),
                        self::TYPE_STRING,
                        self::TYPE_BOOLEAN
                    )
                );

            case 'substring':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2, 2), 1),
                        self::TYPE_NULL,
                        self::TYPE_INTEGER
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2, 2), 1),
                        self::TYPE_STRING,
                        self::TYPE_INTEGER
                    )
                );

            case 'lowercase':
            case 'uppercase':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_STRING)
                    )
                );

            case 'times':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        self::TYPE_INTEGER,
                        self::TYPE_FLOAT
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_FLOAT,
                        self::TYPE_INTEGER
                    )
                );

            case 'divides':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_FLOAT)
                    )
                );

            case 'plus':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_STRING)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_STRING)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_STRING),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        self::TYPE_INTEGER,
                        self::TYPE_FLOAT
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_FLOAT,
                        self::TYPE_INTEGER
                    )
                );

            case 'minus':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        self::TYPE_INTEGER,
                        self::TYPE_FLOAT
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_FLOAT,
                        self::TYPE_INTEGER
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
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_INTEGER, self::TYPE_FLOAT)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        self::TYPE_NULL
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 3),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        array(self::TYPE_OR, self::TYPE_INTEGER, self::TYPE_FLOAT),
                        self::TYPE_BOOLEAN
                    )
                );

            case 'not':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_BOOLEAN)
                    )
                );

            case 'and':
            case 'or':
                return array(
                    array(
                        array(self::TYPE_FUNCTION, array(1, 1), 1),
                        array(self::TYPE_OR, self::TYPE_NULL, self::TYPE_BOOLEAN)
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 1),
                        self::TYPE_NULL,
                        self::TYPE_BOOLEAN
                    ),
                    array(
                        array(self::TYPE_FUNCTION, array(1, 2), 2),
                        self::TYPE_BOOLEAN,
                        self::TYPE_NULL
                    )
                );

            default:
                // TODO: throw exception
                return null;
        }
    }
}
