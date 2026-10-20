<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use HistoryHeraldry\SeedTracker\Runner;
use HistoryHeraldry\SeedTracker\Traits\CommandSupport;
use Throwable;

final class Seed extends BaseCommand
{
    use CommandSupport;

    protected $group = 'Database';
    protected $name = 'seed';
    protected $description = 'Runs all pending tracked database seeds.';
    protected $usage = 'seed [--group <group>]';
    protected $options = ['--group' => 'Database group to seed.'];

    public function run(array $params): int
    {
        $group = $params['group'] ?? CLI::getOption('group');

        try {
            $completed = (new Runner($this->seedTrackerConfig(), is_string($group) ? $group : null))->run();
            foreach ($completed as $seed) {
                CLI::write("Seeded: {$seed->version}_{$seed->name}", 'green');
            }
            CLI::write($completed === [] ? 'No pending tracked seeds.' : count($completed) . ' tracked seed(s) applied.', 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            CLI::error($exception->getMessage());

            return EXIT_ERROR;
        }
    }
}
