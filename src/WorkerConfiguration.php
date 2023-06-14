<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final class WorkerConfiguration
{
    public function __construct(
        private readonly int $sleepMicrosecondsAfterHandled = 0,
        private readonly int $sleepMicrosecondsAfterEmpty = 0,
    ) {
    }

    public function getSleepMicrosecondsAfterHandled(): int
    {
        return $this->sleepMicrosecondsAfterHandled;
    }

    public function getSleepMicrosecondsAfterEmpty(): int
    {
        return $this->sleepMicrosecondsAfterEmpty;
    }
}
