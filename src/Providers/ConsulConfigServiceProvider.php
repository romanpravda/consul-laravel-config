<?php

declare(strict_types=1);

namespace Romanpravda\Consul\Laravel\Config\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Romanpravda\Consul\Laravel\Config\Creators\ConsulConfigRepositoryCreator;
use Romanpravda\Consul\Laravel\Config\Interfaces\ConfigRepositoryInterface;

final class ConsulConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../dist/config/consul.php',
            'consul'
        );

        $this->app->bind(ConfigRepositoryInterface::class, function () {
            $keyPrefix = Config::get('consul.key-prefix');

            return ConsulConfigRepositoryCreator::create($keyPrefix);
        });
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $enabled = Config::get('consul.enabled', false);
        if ($enabled) {
            /** @var ConfigRepositoryInterface $configRepository */
            $configRepository = resolve(ConfigRepositoryInterface::class);

            foreach ($configRepository->generator() as $key => $value) {
                Config::set($key, $value);
            }
        }
    }
}