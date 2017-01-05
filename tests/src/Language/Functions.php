<?php

namespace Datto\Cinnabari\Tests\Language;

use Datto\Cinnabari\Language;
use Datto\Cinnabari\Language\Types;

class Functions extends Language\Functions
{
    public function getSignatures($function)
    {
        switch ($function) {
            case 'merge':
                return array(
                    array(
                        'A',
                        'A',
                        'A'
                    )
                );

            case 'boolean':
                return array(
                    array(
                        Types::TYPE_BOOLEAN,
                        Types::TYPE_BOOLEAN
                    )
                );

            case 'null_boolean':
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

            default:
                return null;
        }
    }
}
