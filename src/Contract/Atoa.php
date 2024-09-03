<?php

declare(strict_types=1);

namespace Maximaster\Atoa\Contract;

use Maximaster\Atoa\Contract\Exception\UnsupportedConversionException;

/**
 * Value converter.
 */
interface Atoa
{
    /**
     * Alias to convertTo.
     *
     * @throws UnsupportedConversionException
     *
     * @psalm-template OutputType
     * @psalm-param class-string<OutputType>|string $outputType
     * @psalm-return OutputType
     */
    public function __invoke(string $outputType, $value);

    /**
     * Convert value to specified type.
     *
     * @throws UnsupportedConversionException
     *
     * @psalm-template OutputType
     * @psalm-param class-string<OutputType>|string $outputType
     * @psalm-return OutputType
     */
    public function convertTo(string $outputType, $value);

    /**
     * Created.
     *
     * @psalm-template ReturnType
     * @psalm-param ReturnType $outputType
     * @psalm-return callable(mixed):ReturnType
     */
    public function for(string $outputType): callable;

    /**
     * Is there any converter for specified input and output?
     */
    public function available(string $input, string $output): bool;
}
