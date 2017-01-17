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

namespace Datto\Cinnabari\Request\Language;

use Datto\Cinnabari\Exceptions\LanguageException;

class Functions
{
    /** @var boolean[] */
    private static $mapFunctions = array(
        'average' => true,
        'count' => true,
        'delete' => true,
        'filter' => true,
        'get' => true,
        'insert' => true,
        'max' => true,
        'min' => true,
        'set' => true,
        'slice' => true,
        'sort' => true,
        'sum' => true
    );

    public function isMapFunction($function)
    {
        return isset(self::$mapFunctions[$function]);
    }

    public function getSignatures($function)
    {
        switch ($function) {
            case 'average':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_NULL),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_INTEGER),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_INTEGER),
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_FLOAT),
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    )
                );

            case 'max':
            case 'min':
            case 'sum':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_NULL),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_INTEGER),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_INTEGER),
                        Types::TYPE_INTEGER
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_FLOAT),
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, Types::TYPE_FLOAT),
                        Types::TYPE_FLOAT
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    )
                );

            case 'count':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, 'A'),
                        Types::TYPE_INTEGER
                    )
                );

            case 'delete':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'filter':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_BOOLEAN,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    )
                );

            case 'get':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        'B',
                        array(Types::TYPE_ARRAY, 'B')
                    )
                );

            case 'insert':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        array(Types::TYPE_OBJECT, 'A'),
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'set':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        array(Types::TYPE_OBJECT, 'B'),
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'slice':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, 'A'),
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        array(Types::TYPE_ARRAY, 'A')
                    )
                );

            case 'sort':
                return array(
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_NULL,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_BOOLEAN,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_INTEGER,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_FLOAT,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    ),
                    array(
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A')),
                        Types::TYPE_STRING,
                        array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'A'))
                    )
                );

            case 'length':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_INTEGER
                    )
                );

            case 'match':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_STRING,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_STRING,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'substring':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_STRING
                    )
                );

            case 'lowercase':
            case 'uppercase':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_STRING
                    )
                );

            case 'times':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    )
                );

            case 'divides':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    )
                );

            case 'plus':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_STRING,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_STRING,
                        Types::TYPE_STRING
                    )
                );

            case 'minus':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT
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
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_INTEGER,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        Types::TYPE_INTEGER,
                        Types::TYPE_FLOAT,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_INTEGER,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_FLOAT,
                        Types::TYPE_FLOAT,
                        Types::TYPE_BOOLEAN
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_STRING,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_STRING,
                        Types::TYPE_STRING,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'not':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'and':
            case 'or':
                return array(
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_NULL,
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_NULL,
                        Types::TYPE_NULL
                    ),
                    array(
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_BOOLEAN
                    )
                );

            default:
                throw LanguageException::unknownFunction($function);
        }
    }
}
