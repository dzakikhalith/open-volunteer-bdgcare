<?php

namespace App\Exceptions;

use RuntimeException;

class RegistrationNotOpenException extends RuntimeException
{
    public static function becauseEventIsClosed(): self
    {
        return new self('Pendaftaran untuk event ini sedang tidak dibuka.');
    }

    public static function becauseQuotaIsFull(): self
    {
        return new self('Kuota volunteer untuk event ini sudah terpenuhi.');
    }
}
