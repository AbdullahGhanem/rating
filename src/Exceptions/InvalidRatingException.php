<?php

namespace Ghanem\Rating\Exceptions;

use InvalidArgumentException;

class InvalidRatingException extends InvalidArgumentException
{
    public static function outOfRange(int|float $value, int|float|null $min, int|float|null $max): static
    {
        return new static("Rating value [{$value}] is out of the allowed range [{$min} - {$max}].");
    }

    public static function negativeNotAllowed(int|float $value): static
    {
        return new static("Negative rating value [{$value}] is not allowed.");
    }
}
