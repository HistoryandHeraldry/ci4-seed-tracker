<?php

declare(strict_types=1);

namespace HistoryHeraldry\SeedTracker\Config;

use CodeIgniter\Config\BaseConfig;

class SeedTracker extends BaseConfig
{
    public string $table = 'seeds';

    /** @var array<string, string> Namespace => directory */
    public array $seedPaths = [];

    public function __construct()
    {
        parent::__construct();

        if ($this->seedPaths === []) {
            $this->seedPaths = [APP_NAMESPACE . '\\Database\\Seeds' => APPPATH . 'Database/Seeds'];
        }
    }
}
