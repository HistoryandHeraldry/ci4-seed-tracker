<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Tests\Fixtures\Success;

use CodeIgniter\Database\Seeder;

final class FirstSeed extends Seeder
{
    public function run(): void
    {
        $this->db->table('seed_events')->insert(['event' => 'first']);
    }
}
