<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use HistoryHeraldry\SeedTracker\Exceptions\SeedTrackerException;
use HistoryHeraldry\SeedTracker\SeedDiscovery;

final class SeedDiscoveryTest extends CIUnitTestCase
{
    public function testDiscoversSeedsInChronologicalOrder(): void
    {
        $seeds = (new SeedDiscovery())->discover([
            'HistoryHeraldry\\SeedTracker\\Tests\\Fixtures\\Success' => __DIR__ . '/Fixtures/Success',
        ]);

        self::assertSame(['FirstSeed', 'SecondSeed'], array_column($seeds, 'name'));
        self::assertSame('2026-01-01-000000', $seeds[0]->version);
    }

    public function testRejectsMissingDirectory(): void
    {
        $this->expectException(SeedTrackerException::class);
        $this->expectExceptionMessage('does not exist');
        (new SeedDiscovery())->discover(['Tests\\Missing' => __DIR__ . '/missing']);
    }

    public function testRejectsDuplicateTimestampsAcrossPaths(): void
    {
        $this->expectException(SeedTrackerException::class);
        $this->expectExceptionMessage('Duplicate tracked seed timestamp');

        (new SeedDiscovery())->discover([
            'HistoryHeraldry\\SeedTracker\\Tests\\Fixtures\\DuplicateA' => __DIR__ . '/Fixtures/DuplicateA',
            'HistoryHeraldry\\SeedTracker\\Tests\\Fixtures\\DuplicateB' => __DIR__ . '/Fixtures/DuplicateB',
        ]);
    }

    public function testRejectsClassThatIsNotASeeder(): void
    {
        $this->expectException(SeedTrackerException::class);
        $this->expectExceptionMessage('must be a concrete subclass');

        (new SeedDiscovery())->discover([
            'HistoryHeraldry\\SeedTracker\\Tests\\Fixtures\\Invalid' => __DIR__ . '/Fixtures/Invalid',
        ]);
    }

    public function testRejectsMalformedTimestampedFilename(): void
    {
        $directory = sys_get_temp_dir() . '/ci4-seed-tracker-' . bin2hex(random_bytes(6));
        mkdir($directory);
        file_put_contents($directory . '/2026-01-bad_Broken.php', '<?php');

        try {
            $this->expectException(SeedTrackerException::class);
            $this->expectExceptionMessage('Malformed tracked seed filename');
            (new SeedDiscovery())->discover(['Tests\\Broken' => $directory]);
        } finally {
            unlink($directory . '/2026-01-bad_Broken.php');
            rmdir($directory);
        }
    }
}
