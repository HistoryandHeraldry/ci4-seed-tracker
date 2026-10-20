<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

final class Modules extends BaseModules
{
    public $enabled = true;
    public $discoverInComposer = true;
    public $composerPackages = [];
    public $aliases = ['events', 'filters', 'registrars', 'routes', 'services'];
}
