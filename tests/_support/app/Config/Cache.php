<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Config\BaseConfig;

final class Cache extends BaseConfig
{
    public string $handler = 'dummy';
    public string $backupHandler = 'dummy';
    public string $prefix = '';
    public int $ttl = 60;
    public string $reservedCharacters = '{}()/\\@:';
    public bool|array $cacheQueryString = false;
    /** @var list<int> */
    public array $cacheStatusCodes = [];

    /** @var array<string, class-string<CacheInterface>> */
    public array $validHandlers = ['dummy' => DummyHandler::class];
}
