<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidAiResponseException extends RuntimeException
{
    public static function missingField(string $field): self
    {
        return new self("The AI response is missing the [{$field}] field.");
    }

    public static function invalidField(string $field): self
    {
        return new self("The AI response contains an invalid [{$field}] field.");
    }
}
