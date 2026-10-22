<?php

namespace App\Exceptions;

use RuntimeException;

class AiServiceException extends RuntimeException
{
    public static function unavailable(): self
    {
        return new self('The AI service is temporarily unavailable.');
    }

    public static function invalidJson(): self
    {
        return new self('The AI service returned an invalid JSON response.');
    }
}
