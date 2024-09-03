<?php

declare(strict_types=1);

namespace Maximaster\Atoa;

use Closure;
use Generator;
use Maximaster\Atoa\Contract\Atoa;
use Maximaster\Atoa\Contract\Exception\InvalidConverterException;
use Maximaster\Atoa\Contract\Exception\UnsupportedConversionException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionType;

/**
 * Default implementation.
 */
class Converter implements Atoa
{
    private const PHP_TYPES = [
        'bool', 'int', 'float', 'string',
        'array', 'object', 'callable',
        'iterable', 'resource', 'NULL'
    ];

    /**
     * @var object[]|callable[]
     *
     * @psalm-var list<string, object|callable>
     */
    private array $converters;

    /**
     * For lazy loading.
     *
     * @psalm-var iterable<callable|object>|null
     */
    private ?iterable $toRegister;

    /**
     * @psalm-param iterable<callable|object> $converters
     */
    public function __construct(iterable $converters)
    {
        $this->toRegister = $converters;
    }

    /**
     * @throws InvalidConverterException
     * @throws UnsupportedConversionException
     */
    public function __invoke(string $outputType, $value)
    {
        return $this->convertTo($outputType, $value);
    }

    /**
     * @throws UnsupportedConversionException
     * @throws InvalidConverterException
     */
    public function convertTo(string $outputType, $value)
    {
        $this->ensureConvertersRegistered();

        // There is no need to conversions.
        if (is_object($value) && is_a($value, $outputType)) {
            return $value;
        }

        $inputType = $this->calcValueType($value);

        foreach ($this->possibleConverterKeys($inputType, $outputType) as $converterKey) {
            if (array_key_exists($converterKey, $this->converters)) {
                return $this->converters[$converterKey]($value);
            }
        }

        throw UnsupportedConversionException::new($inputType, $outputType);
    }

    public function for(string $outputType): callable
    {
        return fn ($value) => $this->convertTo($outputType, $value);
    }

    /**
     * @throws InvalidConverterException
     */
    public function available(string $input, string $output): bool
    {
        $this->ensureConvertersRegistered();

        foreach ($this->possibleConverterKeys($input, $output) as $converterKey) {
            if (array_key_exists($converterKey, $this->converters)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws InvalidConverterException
     *
     * @psalm-param iterable<callable|object> $converters
     */
    private function registerConverters(iterable $converters): void
    {
        foreach ($converters as $converter) {
            $this->registerConverter($converter);
        }
    }

    /**
     * @param object|callable $converter
     *
     * @throws InvalidConverterException
     */
    private function registerConverter($converter): void
    {
        if (is_object($converter) && ($converter instanceof Closure) === false) {
            $this->registerInvokableObjectConverter($converter);

            return;
        }

        if (is_callable($converter)) {
            $this->registerFunctionConverter($converter);

            return;
        }

        throw InvalidConverterException::new('converter should be callable');
    }

    /**
     * @throws InvalidConverterException
     */
    private function registerFunctionConverter(callable $converter)
    {
        try {
            $this->registerAbstractFunction(
                $converter,
                new ReflectionFunction(Closure::fromCallable($converter))
            );
        } catch (ReflectionException $e) {
            throw InvalidConverterException::new($e->getMessage());
        }
    }

    /**
     * @throws InvalidConverterException
     */
    private function registerInvokableObjectConverter(object $converter): void
    {
        try {
            $method = (new ReflectionClass($converter))->getMethod('__invoke');
            $this->registerAbstractFunction($converter, $method);
        } catch (ReflectionException $e) {
            throw InvalidConverterException::new($e->getMessage());
        }
    }

    /**
     * @param object|callable $converter
     *
     * @throws InvalidConverterException
     */
    private function registerAbstractFunction($converter, ReflectionFunctionAbstract $fn): void
    {
        $input = $this->getInput($fn);
        $output = $this->getOutput($fn);

        // TODO move less specific converters into at the end?
        $this->converters[$this->formatConverterKey($input, $output)] = $converter;
    }

    /**
     * @psalm-return Generator<non-empty-string>
     */
    private function possibleConverterKeys(string $input, string $output): Generator
    {
        $inputVariations = (in_array($input, self::PHP_TYPES, true) || class_exists($input) === false)
            ? [$input]
            : $this->fetchClassVariations($input);

        foreach ($inputVariations as $inputVariation) {
            yield $this->formatConverterKey($inputVariation, $output);
        }
    }

    /**
     * @throws InvalidConverterException
     */
    private function getInput(ReflectionFunctionAbstract $fn): string
    {
        $params = $fn->getParameters();

        if (count($params) === 0) {
            throw InvalidConverterException::new('callable should have one parameter');
        }

        [$input] = $params;

        return $this->getType($input->getType());
    }

    /**
     * @throws InvalidConverterException
     */
    private function getOutput(ReflectionFunctionAbstract $fn): string
    {
        return $this->getType($fn->getReturnType());
    }

    /**
     * @throws InvalidConverterException
     */
    private function getType(?ReflectionType $type): string
    {
        if (($type instanceof ReflectionNamedType) === false) {
            throw InvalidConverterException::new('type should be named');
        }

        return $type->getName();
    }

    /**
     * @psalm-return non-empty-string
     */
    private function formatConverterKey(string $inputType, string $outputType): string
    {
        return "$inputType=>$outputType";
    }

    private function calcValueType($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    /**
     * @return string[]
     *
     * @psalm-param class-string $class
     * @psalm-return class-string[]
     */
    private function fetchClassVariations(string $class): array
    {
        return array_merge([$class], class_parents($class), class_implements($class));
    }

    /**
     * @throws InvalidConverterException
     */
    private function ensureConvertersRegistered(): void
    {
        if ($this->toRegister === null) {
            return;
        }

        $this->registerConverters($this->toRegister);
        $this->toRegister = null;
    }
}
