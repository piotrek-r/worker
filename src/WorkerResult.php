<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final class WorkerResult
{
    public function __construct(
        private readonly int $timeElapsed,
        private readonly int $countLoops,
        private readonly int $countHandled,
        private readonly int $countEmpty,
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
