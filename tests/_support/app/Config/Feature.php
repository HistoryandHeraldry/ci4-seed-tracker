<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class Feature extends BaseConfig
{
    public bool $autoRoutesImproved = true;
    public bool $oldFilterOrder = false;
    public bool $limitZeroAsAll = true;
    public bool $strictLocaleNegotiation = false;
}
