<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;
use HistoryHeraldry\SeedTracker\Exceptions\SeedTrackerException;
use ReflectionClass;

final class SeedDiscovery
{
    private const FILE_PATTERN = '/^(?<version>\d{4}-\d{2}-\d{2}-\d{6})_(?<name>[A-Z][A-Za-z0-9]*)\.php$/';

    /**
     * @param array<string, string> $paths Namespace => directory
     * @return list<SeedDefinition>
     */
    public function discover(array $paths): array
    {
        if ($paths === []) {
            throw new SeedTrackerException('No tracked seed paths are configured.');
        }

        $definitions = [];
        $classes = [];

        foreach ($paths as $namespace => $directory) {
            if (! is_dir($directory)) {
                throw new SeedTrackerException("Tracked seed directory does not exist: {$directory}");
            }

            $files = glob(rtrim($directory, '/\\') . '/*.php') ?: [];
            sort($files, SORT_STRING);

            foreach ($files as $file) {
                $filename = basename($file);

                if (! preg_match('/^\d{4}-/', $filename)) {
                    continue; // Conventional CI4 seeders remain available to db:seed.
                }

                if (! preg_match(self::FILE_PATTERN, $filename, $matches)) {
                    throw new SeedTrackerException("Malformed tracked seed filename: {$filename}. Expected YYYY-MM-DD-HHMMSS_DescriptiveName.php.");
                }

                $date = DateTimeImmutable::createFromFormat('!Y-m-d-His', $matches['version']);
                $errors = DateTimeImmutable::getLastErrors();
                if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
                    throw new SeedTrackerException("Invalid timestamp in tracked seed filename: {$filename}");
                }

                $class = trim($namespace, '\\') . '\\' . $matches['name'];
                require_once $file;

                if (! class_exists($class, false)) {
                    throw new SeedTrackerException("Tracked seed {$filename} must declare class {$class}.");
                }

                $reflection = new ReflectionClass($class);
                if (! $reflection->isInstantiable() || ! $reflection->isSubclassOf(Seeder::class)) {
                    throw new SeedTrackerException("Tracked seed {$class} must be a concrete subclass of " . Seeder::class . '.');
                }

                if (realpath((string) $reflection->getFileName()) !== realpath($file)) {
                    throw new SeedTrackerException("Tracked seed class {$class} is declared by more than one file.");
                }

                $definition = new SeedDefinition(
                    $matches['version'],
                    $matches['name'],
                    $class,
                    $file,
                    hash_file('sha256', $file) ?: throw new SeedTrackerException("Unable to checksum tracked seed: {$file}"),
                );

                if (isset($definitions[$definition->key()])) {
                    throw new SeedTrackerException("Duplicate tracked seed timestamp: {$definition->version}");
                }
                if (isset($classes[$class])) {
                    throw new SeedTrackerException("Duplicate tracked seed class: {$class}");
                }

                $definitions[$definition->key()] = $definition;
                $classes[$class] = true;
            }
        }

        uasort($definitions, static fn (SeedDefinition $a, SeedDefinition $b): int => $a->version <=> $b->version);

        return array_values($definitions);
    }
}
