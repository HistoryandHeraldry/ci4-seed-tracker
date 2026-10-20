<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;
use Config\Database;
use HistoryHeraldry\SeedTracker\Config\SeedTracker;
use HistoryHeraldry\SeedTracker\Exceptions\SeedTrackerException;
use Throwable;

final class Runner
{
    /** @var BaseConnection<mixed, mixed> */
    private readonly BaseConnection $db;
    private readonly TrackerRepository $repository;

    /** @param BaseConnection<mixed, mixed>|null $db */
    public function __construct(
        private readonly SeedTracker $config,
        ?string $group = null,
        ?BaseConnection $db = null,
        private readonly SeedDiscovery $discovery = new SeedDiscovery(),
    ) {
        $database = new Database();
        $resolvedGroup = $group ?? $database->defaultGroup;
        if ($resolvedGroup === '') {
            throw new SeedTrackerException('The database group name cannot be empty.');
        }

        $this->db = $db ?? Database::connect($resolvedGroup);
        $this->repository = new TrackerRepository($this->db, $config->table);
    }

    /** @return list<SeedStatus> */
    public function status(): array
    {
        $applied = $this->repository->applied();
        $statuses = [];

        foreach ($this->discovery->discover($this->config->seedPaths) as $seed) {
            $record = $applied[$seed->key()] ?? null;
            $state = $record === null ? 'pending' : ($record['checksum'] === $seed->checksum ? 'applied' : 'modified');
            $statuses[] = new SeedStatus($seed, $state, $record['executed_at'] ?? null);
        }

        return $statuses;
    }

    /** @return list<SeedDefinition> */
    public function run(): array
    {
        $statuses = $this->status();
        foreach ($statuses as $status) {
            if ($status->state === 'modified') {
                throw new SeedTrackerException("Applied tracked seed was modified: {$status->seed->file}");
            }
        }

        $pending = array_values(array_map(
            static fn (SeedStatus $status): SeedDefinition => $status->seed,
            array_filter($statuses, static fn (SeedStatus $status): bool => $status->state === 'pending'),
        ));
        if ($pending === []) {
            return [];
        }

        $batch = $this->repository->nextBatch();
        $completed = [];
        foreach ($pending as $seed) {
            $this->runOne($seed, $batch);
            $completed[] = $seed;
        }

        return $completed;
    }

    private function runOne(SeedDefinition $seed, int $batch): void
    {
        $this->db->transBegin();
        $started = hrtime(true);

        try {
            // Recheck inside the transaction. The unique tracker key resolves concurrent runners.
            if (isset($this->repository->applied()[$seed->key()])) {
                $this->db->transRollback();
                return;
            }

            /** @var Seeder $instance */
            $instance = new $seed->class(new Database(), $this->db);
            $instance->setSilent(true)->run();
            $milliseconds = max(0, (int) round((hrtime(true) - $started) / 1_000_000));
            $this->repository->record($seed, $batch, $milliseconds);

            if (! $this->db->transStatus()) {
                throw new SeedTrackerException("Database transaction failed while running {$seed->class}.");
            }

            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw new SeedTrackerException("Tracked seed failed: {$seed->class}: {$exception->getMessage()}", 0, $exception);
        }
    }
}
