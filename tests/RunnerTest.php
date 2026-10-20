<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Tests;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use HistoryHeraldry\SeedTracker\Config\SeedTracker;
use HistoryHeraldry\SeedTracker\Database\Migrations\CreateSeedsTable;
use HistoryHeraldry\SeedTracker\Exceptions\SeedTrackerException;
use HistoryHeraldry\SeedTracker\Runner;

final class RunnerTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../src/Database/Migrations/2026-10-20-100000_CreateSeedsTable.php';
        $this->connection = Database::connect('tests', false);
        $this->connection->initialize();
        $forge = Database::forge($this->connection);
        $forge->dropTable('seeds', true);
        $forge->dropTable('seed_events', true);
        (new CreateSeedsTable($forge))->up();
        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'event' => ['type' => 'VARCHAR', 'constraint' => 80],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('seed_events');
    }

    public function testFirstRunAppliesInOrderAndSecondRunDoesNothing(): void
    {
        $runner = new Runner($this->configFor('Success'), 'tests', $this->connection);

        self::assertCount(2, $runner->run());
        self::assertSame(['first', 'second'], array_column($this->connection->table('seed_events')->orderBy('id')->get()->getResultArray(), 'event'));
        self::assertCount(2, $this->connection->table('seeds')->get()->getResultArray());
        self::assertSame([], $runner->run());
        self::assertCount(2, $this->connection->table('seed_events')->get()->getResultArray());
        self::assertSame(['applied', 'applied'], array_map(static fn ($status) => $status->state, $runner->status()));
    }

    public function testFailureRollsBackAndIsNotRecorded(): void
    {
        $runner = new Runner($this->configFor('Failure'), 'tests', $this->connection);

        try {
            $runner->run();
            self::fail('The failing seed should throw.');
        } catch (SeedTrackerException $exception) {
            self::assertStringContainsString('Expected fixture failure', $exception->getMessage());
        }

        self::assertSame([], $this->connection->table('seed_events')->get()->getResultArray());
        self::assertSame([], $this->connection->table('seeds')->get()->getResultArray());
        self::assertSame('pending', $runner->status()[0]->state);
    }

    public function testTrackingTableUsesDatabasePrefix(): void
    {
        self::assertTrue($this->connection->tableExists('seeds'));
        self::assertContains('db_seeds', $this->connection->listTables());
    }

    public function testMissingTrackingTableReportsMigrationCommand(): void
    {
        Database::forge($this->connection)->dropTable('seeds', true);

        $this->expectException(SeedTrackerException::class);
        $this->expectExceptionMessage('php spark migrate -n HistoryHeraldry\\SeedTracker');
        (new Runner($this->configFor('Success'), 'tests', $this->connection))->status();
    }

    public function testModifiedAppliedSeedIsReportedAndBlocksExecution(): void
    {
        $directory = sys_get_temp_dir() . '/ci4-seed-tracker-modified-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $file = $directory . '/2026-01-05-000000_MutableSeed.php';
        file_put_contents($file, <<<'PHP'
            <?php
            namespace HistoryHeraldry\SeedTracker\Tests\Runtime;
            final class MutableSeed extends \CodeIgniter\Database\Seeder
            {
                public function run(): void
                {
                    $this->db->table('seed_events')->insert(['event' => 'mutable']);
                }
            }
            PHP);

        $config = new SeedTracker();
        $config->seedPaths = [
            'HistoryHeraldry\\SeedTracker\\Tests\\Runtime' => $directory,
        ];

        try {
            $runner = new Runner($config, 'tests', $this->connection);
            self::assertCount(1, $runner->run());
            file_put_contents($file, "\n// changed after execution\n", FILE_APPEND);
            self::assertSame('modified', $runner->status()[0]->state);

            $this->expectException(SeedTrackerException::class);
            $this->expectExceptionMessage('Applied tracked seed was modified');
            $runner->run();
        } finally {
            unlink($file);
            rmdir($directory);
        }
    }

    public function testMigrationCanBeRolledBackAndReapplied(): void
    {
        $migration = new CreateSeedsTable(Database::forge($this->connection));
        $migration->down();
        self::assertFalse($this->connection->tableExists('seeds'));

        $migration->up();
        self::assertTrue($this->connection->tableExists('seeds'));
    }

    public function testSparkCommandsAreDiscovered(): void
    {
        $commands = service('commands')->getCommands();

        self::assertArrayHasKey('seed', $commands);
        self::assertArrayHasKey('seed:status', $commands);
        self::assertArrayHasKey('make:tracked-seeder', $commands);
    }

    private function configFor(string $fixture): SeedTracker
    {
        $config = new SeedTracker();
        $config->seedPaths = [
            "HistoryHeraldry\\SeedTracker\\Tests\\Fixtures\\{$fixture}" => __DIR__ . "/Fixtures/{$fixture}",
        ];

        return $config;
    }
}
