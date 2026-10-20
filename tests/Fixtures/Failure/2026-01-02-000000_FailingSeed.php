<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Tests\Fixtures\Failure;

use CodeIgniter\Database\Seeder;
use RuntimeException;

final class FailingSeed extends Seeder
{
    public function run(): void
    {
        $this->db->table('seed_events')->insert(['event' => 'must-roll-back']);
        throw new RuntimeException('Expected fixture failure.');
    }
}
