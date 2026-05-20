<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final readonly class WorkerConfiguration
{
    public function __construct(
        private int $sleepMicrosecondsAfterHandled = 0,
        private int $sleepMicrosecondsAfterEmpty = 0,
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
