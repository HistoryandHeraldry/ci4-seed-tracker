<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

final class Logger extends BaseConfig
{
    /** @var int|list<int> */
    public int|array $threshold = 0;
    public string $dateFormat = 'Y-m-d H:i:s';

    /** @var array<class-string<HandlerInterface>, array<string, int|list<string>|string>> */
    public array $handlers = [
        FileHandler::class => [
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],
            'fileExtension'  => '',
            'filePermissions' => 0644,
            'path'           => '',
        ],
    ];
}
