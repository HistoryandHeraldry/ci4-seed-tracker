<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Database\Config;

final class Database extends Config
{
    public string $filesPath = APPPATH . 'Database/';
    public string $defaultGroup = 'tests';

    /** @var array<string, mixed> */
    public array $tests = [
        'DSN' => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'test_',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8',
        'DBCollat' => '',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->tests = array_replace($this->tests, [
            'hostname' => env('database.tests.hostname', $this->tests['hostname']),
            'username' => env('database.tests.username', $this->tests['username']),
            'password' => env('database.tests.password', $this->tests['password']),
            'database' => env('database.tests.database', $this->tests['database']),
            'DBDriver' => env('database.tests.DBDriver', $this->tests['DBDriver']),
            'DBPrefix' => env('database.tests.DBPrefix', $this->tests['DBPrefix']),
            'port' => (int) env('database.tests.port', $this->tests['port']),
        ]);
    }
}
