<?php

declare(strict_types=1);

namespace Romanpravda\Consul\Laravel\Config\Repositories;

use DCarbone\PHPConsulAPI\KV\KVClient;
use DCarbone\PHPConsulAPI\KV\KVPair;
use DCarbone\PHPConsulAPI\KV\KVPairs;
use Generator;
use Illuminate\Support\Str;
use Romanpravda\Consul\Laravel\Config\Interfaces\ConfigRepositoryInterface;

final class ConsulConfigRepository implements ConfigRepositoryInterface
{
    /**
     * ConsulConfigRepository constructor.
     *
     * @param \DCarbone\PHPConsulAPI\KV\KVClient $consul
     * @param string|null $prefix
     */
    public function __construct(
        private readonly KVClient $consul,
        private readonly ?string $prefix,
    ) {}

    /**
     * Получение всех значений конфига.
     *
     * @return \Generator
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function generator(): Generator
    {
        /** @var \DCarbone\PHPConsulAPI\KV\KVPair $pair */
        foreach ($this->generatorWithKVPairs() as $pair) {
            yield $this->transformKeyForLaravel($pair->getKey()) => $this->decodeValue($pair->getValue());
        }
    }

    /**
     * Получение всех значений конфига.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function all(): array
    {
        $map = [];

        /** @var \DCarbone\PHPConsulAPI\KV\KVPair $pair */
        foreach ($this->generatorWithKVPairs() as $pair) {
            $map[$this->transformKeyForLaravel($pair->getKey())] = $this->decodeValue($pair->getValue());
        }

        return $map;
    }

    /**
     * Получение значения конфига.
     *
     * @param string $key
     *
     * @return string|int|float|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function get(string $key): string|int|float|null
    {
        $pair = $this->consul->Get($this->transformKeyForConsul($key))->getValue();
        if (is_null($pair)) {
            return null;
        }

        return $this->decodeValue($pair->getValue());
    }

    /**
     * Установка значения конфига.
     *
     * @param string $key
     * @param string|int|float $value
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function set(string $key, string|int|float $value): void
    {
        $pair = new KVPair();
        $pair->setKey($this->transformKeyForConsul($key));
        $pair->setValue($this->encodeValue($value));

        $this->consul->Put($pair);
    }

    /**
     * Генератор с объектом ключ-значение.
     *
     * @return \Generator
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function generatorWithKVPairs(): Generator
    {
        /** @var \DCarbone\PHPConsulAPI\KV\KVPair $pair */
        foreach ($this->consul->List($this->prefix ?? '')->getValue() ?? new KVPairs([]) as $pair) {
            $key = $pair->getKey();
            if (Str::endsWith($key, '/')) {
                continue;
            }

            yield $pair;
        }
    }

    /**
     * Трансформация ключа для приложения.
     *
     * @param string $key
     *
     * @return string
     */
    private function transformKeyForLaravel(string $key): string
    {
        if (is_null($this->prefix)) {
            return str_replace('/', '.', $key);
        }

        return str_replace([sprintf('%s/', $this->prefix), '/'], ['', '.'], $key);
    }

    /**
     * Трансформация ключа для Consul.
     *
     * @param string $key
     *
     * @return string
     */
    private function transformKeyForConsul(string $key): string
    {
        $key = str_replace('.', '/', $key);

        return is_null($this->prefix) ? $key : sprintf('%s/%s', $this->prefix, $key);
    }

    /**
     * Кодирование значение.
     *
     * @param string|int|float $value
     *
     * @return string
     *
     * @throws \JsonException
     */
    private function encodeValue(string|int|float $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * Декодирование значения.
     *
     * @param string $value
     *
     * @return string|int|float
     *
     * @throws \JsonException
     */
    private function decodeValue(string $value): string|int|float
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}