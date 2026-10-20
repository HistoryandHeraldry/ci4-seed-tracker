# CI4 Seed Tracker

Migration-style database seeds for CodeIgniter 4.

CI4 Seed Tracker discovers timestamped seed files, runs each seed once in chronological order, and records successful executions in the database. Checksums detect changes to seeds that have already run.

## Requirements

- PHP 8.2 or later
- CodeIgniter 4.7 or later
- A database and table type that support transactions for full rollback protection

The automated suite covers SQLite and MariaDB. Other database drivers supported by CI4 should be verified in your environment before production use.

## Installation

Install the package with Composer:

```shell
composer require historyheraldry/ci4-seed-tracker
```

Timestamped seeds are loaded by the tracker rather than PSR-4. Add the seed directory to the application's Composer classmap exclusions alongside CI4 migrations:

```json
{
  "autoload": {
    "exclude-from-classmap": [
      "**/Database/Migrations/**",
      "**/Database/Seeds/**"
    ]
  }
}
```

Regenerate the application autoloader after changing this setting:

```shell
composer dump-autoload
```

Create the `seeds` tracking table:

```shell
php spark migrate -n 'HistoryHeraldry\SeedTracker'
```

CI4's plain `php spark migrate` command scans only the application namespace, so the package namespace must be supplied explicitly.

## Creating a tracked seed

Generate a timestamped seeder:

```shell
php spark make:tracked-seeder AddSystemTasks
```

This creates a file similar to:

```text
app/Database/Seeds/2026-10-20-143501_AddSystemTasks.php
```

Tracked seed filenames use `YYYY-MM-DD-HHMMSS_DescriptiveName.php`. The timestamp must be valid and unique across all configured seed paths. The descriptive name must be a PascalCase PHP class name, and the class must extend `CodeIgniter\Database\Seeder`.

```php
<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class AddSystemTasks extends Seeder
{
    public function run(): void
    {
        $this->db->table('tasks')->insert(['name' => 'example']);
    }
}
```

## Running seeds

```shell
php spark seed:status
php spark seed
```

Running `seed` again does not rerun applied seeds.

## Database groups

Run the migration and seeds against a specific CI4 database group:

```shell
php spark migrate -n 'HistoryHeraldry\SeedTracker' -g reporting
php spark seed --group reporting
php spark seed:status --group reporting
```

The selected group is used for both the seed operations and tracking table. CI4's configured table prefix is applied automatically.

## Status values

- `pending` — the seed has not run.
- `applied` — the recorded checksum matches the current file.
- `modified` — an applied seed file has changed.

Modified seeds block execution. Once a seed has been applied, create a new timestamped seed for subsequent changes instead of editing the original file.

## Configuration

The default seed directory is `app/Database/Seeds`, and the default tracking table is `seeds`.

To override these values, create `app/Config/SeedTracker.php`:

```php
<?php

declare(strict_types=1);

namespace Config;

class SeedTracker extends \HistoryHeraldry\SeedTracker\Config\SeedTracker
{
    public string $table = 'seeds';

    public array $seedPaths = [
        'App\\Database\\Seeds' => APPPATH . 'Database/Seeds',
        'Acme\\Billing\\Database\\Seeds' => ROOTPATH . 'modules/Billing/Database/Seeds',
    ];
}
```

Each `seedPaths` entry maps a PHP namespace to its directory.

## Failure and concurrency behaviour

Each seed and its tracking record run in one database transaction. If the seed throws or the transaction fails, the seed is not recorded as applied.

The tracking table has a unique timestamp constraint, and the runner rechecks the seed inside the transaction. This prevents concurrent runners from successfully applying the same seed twice when the seeded tables use transactional storage.

## Standard CI4 seeders

Non-timestamped seed files are ignored by CI4 Seed Tracker and remain available through CI4's existing command:

```shell
php spark db:seed SeederName
```

## License

CI4 Seed Tracker is released under the MIT License.
