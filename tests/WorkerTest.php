<?php

declare(strict_types=1);

namespace PiotrekR\Worker\Tests;

use PHPUnit\Framework\TestCase;
use PiotrekR\Worker\Worker;
use PiotrekR\Worker\WorkerConditions;
use PiotrekR\Worker\WorkerConfiguration;
use RuntimeException;

class WorkerTest extends TestCase
{
    public static function dataSleepTimes(): array
    {
        return [
            [100000, 0, true, 1],
            [0, 100000, false, 1],
            [100000, 100000, true, 1],
            [100000, 100000, false, 1],
            [100000, 0, null, [0, 1]],
            [0, 100000, null, [0, 1]],
            [100000, 100000, null, 1],
        ];
    }

    public function testSanity(): void
    {
        $worker = new Worker();
        self::assertInstanceOf(Worker::class, $worker);
    }

    public function testWillThrowException(): void
    {
        self::expectException(RuntimeException::class);

        $worker = new Worker();
        $worker->run(function () {
            throw new RuntimeException();
        });
    }

    public function testTimeLimit(): void
    {
        $workerConditions = new WorkerConditions(
            timeSeconds: 1,
        );

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $timeStart = time();
        $result = $worker->run(function () {
            return false;
        });
        $timeEnd = time() - $timeStart;

        self::assertEquals(1, $result->getTimeElapsed());
        self::assertEquals(1, $timeEnd);
    }

    public function testMemoryLimit(): void
    {
        $workerConditions = new WorkerConditions(
            memory: 5000000,
        );

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $str = '';
        $worker->run(function () use (&$str) {
            $str .= str_pad('', 1024, '.');
            return false;
        });

        self::assertGreaterThanOrEqual(5000000, memory_get_usage(true));
    }

    public function testCountLoopsLimit(): void
    {
        $count = 10;

        $workerConditions = new WorkerConditions(
            countLoops: $count,
        );

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $result = $worker->run(function () {
            return false;
        });

        self::assertEquals($count, $result->getCountLoops());
    }

    public function testCountHandledLimit(): void
    {
        $workerConditions = new WorkerConditions(
            countHandled: 10,
        );

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $result = $worker->run(function () {
            return true;
        });

        self::assertEquals(10, $result->getCountHandled());
        self::assertEquals(0, $result->getCountEmpty());
        self::assertEquals(10, $result->getCountLoops());

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $i = 0;
        $result = $worker->run(function () use (&$i) {
            return ($i++) % 3 === 0;
        });

        self::assertGreaterThanOrEqual(10, $i);
        self::assertEquals(10, $result->getCountHandled());
        self::assertGreaterThan(0, $result->getCountEmpty());
        self::assertEquals($result->getCountLoops(), $result->getCountHandled() + $result->getCountEmpty());
    }

    public function testCountLimitEmpty(): void
    {
        $workerConditions = new WorkerConditions(
            countEmpty: 10,
        );

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $result = $worker->run(function () {
            return false;
        });

        self::assertEquals(0, $result->getCountHandled());
        self::assertEquals(10, $result->getCountEmpty());
        self::assertEquals(10, $result->getCountLoops());

        $worker = new Worker(
            conditions: $workerConditions,
        );

        $i = 0;
        $result = $worker->run(function () use (&$i) {
            return ($i++) % 3 === 0;
        });

        self::assertGreaterThanOrEqual(5, $i);
        self::assertGreaterThan(0, $result->getCountHandled());
        self::assertEquals(10, $result->getCountEmpty());
        self::assertEquals($result->getCountLoops(), $result->getCountHandled() + $result->getCountEmpty());
    }

    /**
     * @dataProvider dataSleepTimes
     */
    public function testSleepTimes(int $afterHandled, int $afterEmpty, bool|null $return, int|array $expected): void
    {
        $workerConditions = new WorkerConditions(
            countLoops: 10,
        );
        $workerConfiguration = new WorkerConfiguration(
            sleepMicrosecondsAfterHandled: $afterHandled,
            sleepMicrosecondsAfterEmpty: $afterEmpty,
        );
        $worker = new Worker(
            conditions: $workerConditions,
            configuration: $workerConfiguration,
        );
        $result = $worker->run(function () use ($return) {
            if ($return !== null) {
                return $return;
            }
            return (bool)random_int(0, 1);
        });
        if (is_array($expected)) {
            self::assertGreaterThanOrEqual($expected[0], $result->getTimeElapsed());
            self::assertLessThanOrEqual($expected[1], $result->getTimeElapsed());
        } else {
            self::assertEquals($expected, $result->getTimeElapsed());
        }
    }
}
