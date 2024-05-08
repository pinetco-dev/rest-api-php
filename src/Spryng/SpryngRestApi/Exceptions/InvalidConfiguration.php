<?php

namespace Spryng\SpryngRestApi\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    /**
     * @return static
     */
    public static function configurationNotSet()
    {
        return new static('In order to send notification via Spryng you need to add credentials in the `spryng` key of `config.services`.');
    }
}
