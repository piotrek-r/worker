<?php

declare(strict_types=1);

namespace PiotrekR\Worker;

final class WorkerConditions
{
    private const SUFFIXES = ['b', 'k', 'm', 'g', 't', 'p', 'e'];

    private readonly int|null $memory;

    /**
     * @param string|int|null $memory Format: 1, 1b, 1k, 1m, 1g, 1t, 1p, 1e
     */
    public function __construct(
        private readonly int|null $timeSeconds = null,
        string|int|null $memory = null,
        private readonly int|null $countLoops = null,
        private readonly int|null $countHandled = null,
        private readonly int|null $countEmpty = null,
    ) {
        if (is_string($memory)) {
            $this->memory = $this->parseMemoryString($memory);
        } else {
            $this->memory = $memory;
        }
    }

    public function hasTimeSeconds(): bool
    {
        return $this->timeSeconds !== null;
    }

    public function getTimeSeconds(): int|null
    {
        return $this->timeSeconds;
    }

    public function hasMemory(): bool
    {
        return $this->memory !== null;
    }

    public function getMemory(): int|null
    {
        return $this->memory;
    }

    public function hasCountLoops(): bool
    {
        return $this->countLoops !== null;
    }

    public function getCountLoops(): int|null
    {
        return $this->countLoops;
    }

    public function hasCountHandled(): bool
    {
        return $this->countHandled !== null;
    }

    public function getCountHandled(): int|null
    {
        return $this->countHandled;
    }

    public function hasCountEmpty(): bool
    {
        return $this->countEmpty !== null;
    }

    public function getCountEmpty(): int|null
    {
        return $this->countEmpty;
    }

    private function parseMemoryString(string $memory): int
    {
        $memory = trim($memory);

        $number = (int)$memory;
        if ($number === 0) {
            return 0;
        }

        $lastCharacter = strtolower($memory[strlen($memory) - 1]);

        $exponent = array_search($lastCharacter, self::SUFFIXES, true);
        if ($exponent === false) {
            return $number;
        }

        return $number * (1024 ** $exponent);
    }
}
