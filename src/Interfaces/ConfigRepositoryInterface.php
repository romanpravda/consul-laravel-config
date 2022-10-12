<?php

declare(strict_types=1);

namespace Romanpravda\Consul\Laravel\Config\Interfaces;

interface ConfigRepositoryInterface
{
    /**
     * Получение генератора со всеми значениями конфига.
     *
     * @return \Generator
     */
    public function generator(): \Generator;

    /**
     * Получение всех значений конфига.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Получение значения конфига.
     *
     * @param string $key
     *
     * @return string|int|float|null
     */
    public function get(string $key): string|int|float|null;

    /**
     * Установка значения конфига.
     *
     * @param string $key
     * @param string|int|float $value
     *
     * @return void
     */
    public function set(string $key, string|int|float $value): void;
}