<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class MakeTrackedSeeder extends BaseCommand
{
    protected $group = 'Generators';
    protected $name = 'make:tracked-seeder';
    protected $description = 'Creates a timestamped tracked seeder.';
    protected $usage = 'make:tracked-seeder <name>';
    protected $arguments = ['name' => 'Seeder class name, for example AddSystemTasks.'];

    public function run(array $params): int
    {
        $name = (string) ($params[0] ?? CLI::getSegment(2) ?? '');
        $name = str_ends_with($name, 'Seeder') ? substr($name, 0, -6) : $name;

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $name)) {
            CLI::error('A tracked seeder name must be a valid PascalCase PHP class name.');

            return EXIT_ERROR;
        }

        $directory = APPPATH . 'Database/Seeds';
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            CLI::error("Unable to create seed directory: {$directory}");

            return EXIT_ERROR;
        }

        $filename = date('Y-m-d-His') . "_{$name}.php";
        $path = $directory . '/' . $filename;
        if (is_file($path)) {
            CLI::error("Tracked seeder already exists: {$path}");

            return EXIT_ERROR;
        }

        $namespace = APP_NAMESPACE . '\\Database\\Seeds';
        $source = <<<PHP
            <?php

            declare(strict_types=1);

            namespace {$namespace};

            use CodeIgniter\\Database\\Seeder;

            final class {$name} extends Seeder
            {
                public function run(): void
                {
                    // Add seed operations here.
                }
            }

            PHP;

        if (file_put_contents($path, $source) === false) {
            CLI::error("Unable to write tracked seeder: {$path}");

            return EXIT_ERROR;
        }

        CLI::write("Created: {$path}", 'green');

        return EXIT_SUCCESS;
    }
}
