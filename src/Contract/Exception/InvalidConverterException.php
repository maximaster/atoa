<?php

declare(strict_types=1);

namespace Maximaster\Atoa\Contract\Exception;

/**
 * Converter you passed is invalid.
 */
class InvalidConverterException extends AtoaException
{
    public static function new(string $message): self
    {
        // phpcs:ignore Generic.Files.LineLength.TooLong
        return new self(sprintf('Converter you specified is invalid. Try callable or __invoke object with at least one argument and specified return type. Details: %s.', $message));
    }
}
