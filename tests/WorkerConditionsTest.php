<?php

declare(strict_types=1);

namespace PiotrekR\Worker\Tests;

use PiotrekR\Worker\WorkerConditions;
use PHPUnit\Framework\TestCase;

class WorkerConditionsTest extends TestCase
{
    public static function memoryValues(): array
    {
        return [
            [1],
            [10],
            [1025],
            [10000000],
            [PHP_INT_MAX],
        ];
    }

    public static function memoryUnits(): array
    {
        return [
            ['b', 1],
            ['k', 1024],
            ['m', 1024 * 1024],
            ['g', 1024 * 1024 * 1024],
            ['t', 1024 * 1024 * 1024 * 1024],
            ['p', 1024 * 1024 * 1024 * 1024 * 1024],
            ['e', 1024 * 1024 * 1024 * 1024 * 1024 * 1024],
        ];
    }

    /**
     * @dataProvider memoryValues
     */
    public function testMemorySetter(int $number): void
    {
        $workerConditions = new WorkerConditions(
            memory: $number,
        );
        self::assertEquals($number, $workerConditions->getMemory());
    }

    /**
     * @dataProvider memoryUnits
     */
    public function testMemoryUnitConversion(string $suffix, int $expected): void
    {
        $workerConditions = new WorkerConditions(
            memory: 1 . $suffix,
        );
        self::assertEquals($expected, $workerConditions->getMemory());

        $workerConditions = new WorkerConditions(
            memory: 5 . strtolower($suffix),
        );
        self::assertEquals($expected * 5, $workerConditions->getMemory());

        $workerConditions = new WorkerConditions(
            memory: 1 . strtoupper($suffix),
        );
        self::assertEquals($expected, $workerConditions->getMemory());
    }
}
