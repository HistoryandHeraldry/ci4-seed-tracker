<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use HistoryHeraldry\SeedTracker\Runner;
use HistoryHeraldry\SeedTracker\Traits\CommandSupport;
use Throwable;

final class SeedStatus extends BaseCommand
{
    use CommandSupport;

    protected $group = 'Database';
    protected $name = 'seed:status';
    protected $description = 'Shows applied, pending, and modified tracked seeds.';
    protected $usage = 'seed:status [--group <group>]';
    protected $options = ['--group' => 'Database group to inspect.'];

    public function run(array $params): int
    {
        $group = $params['group'] ?? CLI::getOption('group');

        try {
            $rows = [['Version', 'Seed', 'State', 'Executed at']];
            foreach ((new Runner($this->seedTrackerConfig(), is_string($group) ? $group : null))->status() as $status) {
                $rows[] = [$status->seed->version, $status->seed->name, $status->state, $status->executedAt ?? '—'];
            }
            CLI::table($rows);

            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            CLI::error($exception->getMessage());

            return EXIT_ERROR;
        }
    }
}
