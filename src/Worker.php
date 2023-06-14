<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final class Worker
{
    private readonly WorkerConditions $conditions;

    public function __construct(
        WorkerConditions $conditions = null,
    ) {
        $this->conditions = $conditions ?? new WorkerConditions();
    }

    public function run(callable $fn, mixed ...$args): WorkerResult
    {
        $startTime = time();
        $timeElapsed = 0;
        $countLoops = 0;
        $countHandled = 0;
        $countEmpty = 0;

        while ($this->shouldRun($timeElapsed, $countLoops, $countHandled, $countEmpty)) {
            $isHandled = $fn(...$args);

            ++$countLoops;

            if ($isHandled) {
                ++$countHandled;
            } else {
                ++$countEmpty;
            }

            $timeElapsed = time() - $startTime;
        }

        return new WorkerResult(
            $timeElapsed,
            $countLoops,
            $countHandled,
            $countEmpty,
        );
    }

    private function shouldRun(int $timeElapsed, int $countLoops, int $countHandled, int $countEmpty): bool
    {
        $conditions = $this->conditions;

        if ($conditions->hasTimeSeconds() && $timeElapsed >= $conditions->getTimeSeconds()) {
            return false;
        }

        if ($conditions->hasMemory() && memory_get_usage(true) >= $conditions->getMemory()) {
            return false;
        }

        if ($conditions->hasCountLoops() && $countLoops >= $conditions->getCountLoops()) {
            return false;
        }

        if ($conditions->hasCountHandled() && $countHandled >= $conditions->getCountHandled()) {
            return false;
        }

        if ($conditions->hasCountEmpty() && $countEmpty >= $conditions->getCountEmpty()) {
            return false;
        }

        return true;
    }
}
