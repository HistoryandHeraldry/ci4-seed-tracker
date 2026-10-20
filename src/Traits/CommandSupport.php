<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Traits;

use HistoryHeraldry\SeedTracker\Config\SeedTracker;

trait CommandSupport
{
    private function seedTrackerConfig(): SeedTracker
    {
        $applicationConfig = 'Config\\SeedTracker';

        return class_exists($applicationConfig) ? new $applicationConfig() : new SeedTracker();
    }
}
