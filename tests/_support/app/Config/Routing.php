<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\Routing as BaseRouting;

final class Routing extends BaseRouting
{
    /** @var list<string> */
    public array $routeFiles = [APPPATH . 'Config/Routes.php'];
}
