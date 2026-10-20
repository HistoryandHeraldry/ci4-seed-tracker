<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\ArrayHandler;

final class Session extends BaseConfig
{
    public string $driver = ArrayHandler::class;
    public string $cookieName = 'ci_session';
    public int $expiration = 7200;
    public string $savePath = WRITEPATH . 'session';
    public bool $matchIP = false;
    public int $timeToUpdate = 300;
    public bool $regenerateDestroy = false;
    public ?string $DBGroup = null;
    public int $lockRetryInterval = 100_000;
    public int $lockMaxRetries = 300;
}
