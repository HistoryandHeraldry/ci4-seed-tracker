<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

final class Exceptions extends BaseConfig
{
    public bool $log = true;

    /** @var list<int> */
    public array $ignoreCodes = [404];

    public string $errorViewPath = APPPATH . 'Views/errors';

    /** @var list<string> */
    public array $sensitiveDataInTrace = [];

    public bool $logDeprecations = true;
    public string $deprecationLogLevel = LogLevel::WARNING;

    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}
