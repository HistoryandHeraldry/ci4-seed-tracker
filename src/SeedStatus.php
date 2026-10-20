<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker;

final readonly class SeedStatus
{
    public function __construct(
        public SeedDefinition $seed,
        public string $state,
        public ?string $executedAt = null,
    ) {
    }
}
