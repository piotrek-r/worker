<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final readonly class WorkerResult
{
    public function __construct(
        private int $timeElapsed,
        private int $countLoops,
        private int $countHandled,
        private int $countEmpty,
    ) {
    }

    public function getTimeElapsed(): int
    {
        return $this->timeElapsed;
    }

    public function getCountLoops(): int
    {
        return $this->countLoops;
    }

    public function getCountHandled(): int
    {
        return $this->countHandled;
    }

    public function getCountEmpty(): int
    {
        return $this->countEmpty;
    }
}
