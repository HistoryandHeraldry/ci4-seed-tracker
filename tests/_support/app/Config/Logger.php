<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\HandlerInterface;

final class Logger extends BaseConfig
{
    /** @var int|list<int> */
    public int|array $threshold = 0;
    public string $dateFormat = 'Y-m-d H:i:s';

    /** @var array<class-string<HandlerInterface>, array<string, int|list<string>|string>> */
    public array $handlers = [];
}
