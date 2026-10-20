<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker;

final readonly class SeedDefinition
{
    public function __construct(
        public string $version,
        public string $name,
        public string $class,
        public string $file,
        public string $checksum,
    ) {
    }

    public function key(): string
    {
        return $this->version;
    }
}
