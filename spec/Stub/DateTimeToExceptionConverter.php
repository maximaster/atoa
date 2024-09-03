<?php

declare(strict_types=1);

namespace Maximaster\Atoa\Spec\Stub;

use DateTime;
use Exception;

/**
 * DateTime instance to Exception instance converter.
 */
class DateTimeToExceptionConverter
{
    private string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }

    public function __invoke(DateTime $time): Exception
    {
        return new Exception($time->format($this->format));
    }
}
