<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Maximaster\Atoa\Converter;
use Maximaster\Atoa\Spec\Stub\DateTimeToExceptionConverter;

describe(Converter::class, function (): void {
    it('converts one object to another', function (): void {
        $converter = new Converter([new DateTimeToExceptionConverter('Y-m-d')]);

        $exception = $converter->convertTo(Exception::class, new DateTime('2020-01-01'));
        expect($exception)->toBeAnInstanceOf(Exception::class);
        expect($exception->getMessage())->toBe('2020-01-01');
    });

    it('can be binded to some type', function (): void {
        $converter = new Converter([new DateTimeToExceptionConverter('Y-m-d')]);
        $converter = $converter->for(Exception::class);

        $exception = $converter(new DateTime('2020-01-01'));
        expect($exception)->toBeAnInstanceOf(Exception::class);
        expect($exception->getMessage())->toBe('2020-01-01');
    });

    it('can use static arrow function converters', function (): void {
        $converter = new Converter([
            static fn (string $value): int => intval($value),
            static fn (string $value): float => floatval($value),
            static fn (string $value): array => [$value],
        ]);

        expect($converter->convertTo('int', '42'))->toBe(42);
        expect($converter->convertTo('float', '42'))->toBe(42.0);
        expect($converter->convertTo('array', '42'))->toBe(['42']);
    });
});
