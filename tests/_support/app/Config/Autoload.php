<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

final class Autoload extends AutoloadConfig
{
    public $psr4 = [APP_NAMESPACE => APPPATH];
    public $classmap = [];
    public $files = [];
    public $helpers = [];
}
