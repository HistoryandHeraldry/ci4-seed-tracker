<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ResultInterface;
use HistoryHeraldry\SeedTracker\Exceptions\SeedTrackerException;

final class TrackerRepository
{
    public function __construct(
        /** @var BaseConnection<mixed, mixed> $db */
        private readonly BaseConnection $db,
        private readonly string $table = 'seeds',
    ) {
    }

    public function ensureInstalled(): void
    {
        if ($this->db->tableExists($this->table)) {
            return;
        }

        throw new SeedTrackerException(
            "Tracked seed table '{$this->table}' does not exist. Run php spark migrate -n HistoryHeraldry\\SeedTracker first.",
        );
    }

    /** @return array<string, array<string, mixed>> */
    public function applied(): array
    {
        $this->ensureInstalled();
        $query = $this->db->table($this->table)->orderBy('version', 'ASC')->orderBy('name', 'ASC')->get();
        if (! $query instanceof ResultInterface) {
            throw new SeedTrackerException('Unable to read tracked seed records.');
        }
        $rows = $query->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['version']] = $row;
        }

        return $result;
    }

    public function nextBatch(): int
    {
        $this->ensureInstalled();
        $query = $this->db->table($this->table)->selectMax('batch')->get();
        if (! $query instanceof ResultInterface) {
            throw new SeedTrackerException('Unable to determine the next tracked seed batch.');
        }
        $row = $query->getRowArray();

        return ((int) ($row['batch'] ?? 0)) + 1;
    }

    public function record(SeedDefinition $seed, int $batch, int $milliseconds): void
    {
        $this->db->table($this->table)->insert([
            'version' => $seed->version,
            'name' => $seed->name,
            'class' => $seed->class,
            'checksum' => $seed->checksum,
            'batch' => $batch,
            'execution_time' => $milliseconds,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
