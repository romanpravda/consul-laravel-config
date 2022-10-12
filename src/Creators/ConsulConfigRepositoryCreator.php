<?php

declare(strict_types=1);

namespace Romanpravda\Consul\Laravel\Config\Creators;

use DCarbone\PHPConsulAPI\Config as ConsulConfig;
use DCarbone\PHPConsulAPI\Consul;
use DCarbone\PHPConsulAPI\HttpAuth;
use Illuminate\Support\Facades\Config;
use Romanpravda\Consul\Laravel\Config\Repositories\ConsulConfigRepository;

final class ConsulConfigRepositoryCreator
{
    /**
     * Создание KV-репозитория для значений конфигурации в Consul.
     *
     * @param string|null $keyPrefix
     *
     * @return \Romanpravda\Consul\Laravel\Config\Repositories\ConsulConfigRepository
     */
    public static function create(?string $keyPrefix = null): ConsulConfigRepository
    {
        $address = Config::get('consul.address');
        $port = Config::get('consul.port');
        $username = Config::get('consul.username');
        $password = Config::get('consul.password');

        $config = ConsulConfig::newDefaultConfig();
        $config->setAddress(sprintf('%s:%d', $address, $port));
        $httpAuth = new HttpAuth();
        if (!is_null($username)) {
            $httpAuth->username = $username;
        }
        if (!is_null($password)) {
            $httpAuth->password = $password;
        }
        $config->setHttpAuth($httpAuth);

        $consul = new Consul($config);
        return new ConsulConfigRepository($consul->KV, $keyPrefix);
    }
}