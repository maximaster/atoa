<?php

declare(strict_types=1);

namespace Maximaster\Atoa\Contract\Exception;

/**
 * Requested conversion is not supported.
 */
class UnsupportedConversionException extends AtoaException
{
    public static function new(string $inputType, string $outputType): self
    {
        return new self(sprintf("Conversion between '%s' and '%s' is not supported", $inputType, $outputType));
    }
}
