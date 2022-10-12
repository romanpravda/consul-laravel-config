<?php

namespace Romanpravda\Consul\Laravel\Config\Repositories;

use DCarbone\PHPConsulAPI\KV\KVClient;
use DCarbone\PHPConsulAPI\KV\KVPair;
use Generator;
use Illuminate\Support\Str;
use Romanpravda\Consul\Laravel\Config\Interfaces\ConfigRepositoryInterface;

class ConsulConfigRepository implements ConfigRepositoryInterface
{
    /**
     * Клиент хранилища "ключ-значение" в Consul.
     *
     * @var \DCarbone\PHPConsulAPI\KV\KVClient
     */
    private $consul;

    /**
     * Префикс ключа.
     *
     * @var string|null
     */
    private $prefix;

    /**
     * ConsulConfigRepository constructor.
     *
     * @param \DCarbone\PHPConsulAPI\KV\KVClient $consul
     * @param string|null $prefix
     */
    public function __construct(KVClient $consul, ?string $prefix) {
        $this->consul = $consul;
        $this->prefix = $prefix;
    }

    /**
     * Получение всех значений конфига.
     *
     * @return \Generator
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
     */
    public function get(string $key)
    {
        $data = $this->consul->get($this->transformKeyForConsul($key));
        if (!isset($data[0])) {
            return null;
        }
        /** @var \DCarbone\PHPConsulAPI\KV\KVPair $pair */
        $pair = $data[0];

        return $this->decodeValue($pair->getValue());
    }

    /**
     * Установка значения конфига.
     *
     * @param string $key
     * @param string|int|float $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $pair = new KVPair();
        $pair->setKey($this->transformKeyForConsul($key));
        $pair->setValue($this->encodeValue($value));

        $this->consul->put($pair);
    }

    /**
     * Генератор с объектом ключ-значение.
     *
     * @return \Generator
     */
    private function generatorWithKVPairs(): Generator
    {
        $data = $this->consul->valueList($this->prefix ?? '');
        if (!isset($data[0])) {
            return;
        }
        /** @var \DCarbone\PHPConsulAPI\KV\KVPair[] $pairs */
        $pairs = $data[0];

        foreach ($pairs as $pair) {
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
     */
    private function encodeValue($value): string
    {
        return json_encode($value);
    }

    /**
     * Декодирование значения.
     *
     * @param string $value
     *
     * @return string|int|float
     */
    private function decodeValue(string $value)
    {
        return json_decode($value, true);
    }
}