<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;

/**
 * MEMCACHED cache implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class MemcachedCache extends Cache
{
    #[\Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    #[\Override]
    public function clear(string $key): bool
    {
        return $this->memcached->delete($this->getEffectiveKey($key));
    }

    #[\Override]
    public function clearAllCache(): bool
    {
        return $this->memcached->flush();
    }

    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $pair = $this->memcached->get($this->getEffectiveKey($key));
        if (\Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            $this->set($key, -1, $ttl);

            return -1;
        }

        $value = $pair['data'];
        if (!is_numeric($value)) {
            return false;
        }

        --$value;
        $pair['data'] = $value;
        $this->memcached->set($this->getEffectiveKey($key), $pair, $this->getRemainingTTL($key) ?? $this->getTtl());

        return $value;
    }

    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcached->get($this->getEffectiveKey($key));
        if (\Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            return null;
        }

        $valDecoded = json_decode((string) $val['data'], true);

        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::MEMCACHED;
    }

    #[\Override]
    public function getRemainingTTL(string $key): ?int
    {
        $val = $this->memcached->get($this->getEffectiveKey($key));
        if (\Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            return null;
        }
        $res = $val['expires_at'] - time();

        return $res >= 0 ? $res : null;
    }

    #[\Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $pair = $this->memcached->get($this->getEffectiveKey($key));
        if (\Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            $this->set($key, 1, $ttl);

            return 1;
        }

        $value = $pair['data'];
        if (!is_numeric($value)) {
            return false;
        }

        ++$value;
        $pair['data'] = $value;
        $this->memcached->set($this->getEffectiveKey($key), $pair, $this->getRemainingTTL($key) ?? $this->getTtl());

        return $value;
    }

    #[\Override]
    public function isConnected(): bool
    {
        $version = $this->memcached->getVersion();

        return false !== $version && !in_array('0.0.0', $version);
    }

    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $values = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        $ttlToUse = $this->getTtlToUse($ttl);
        $dataToStore = [
            'data' => json_encode($values), 'expires_at' => time() + $ttlToUse,
        ];

        return $this->memcached->set($this->getEffectiveKey($key), $dataToStore, $ttlToUse);
    }

    #[\Override]
    protected function checkInstanceIsCorrect(object $instance): bool
    {
        return $instance instanceof \Memcached;
    }

    #[\Override]
    protected function assertConfig(array $configuration): void
    {
        parent::assertConfig($configuration);
        if (($configuration['persistent'] ?? false) && !($configuration['persistentId'] ?? false)) {
            throw new CacheMissingConfigurationException('memcached persistent needs persistentId');
        }
    }

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        if (array_key_exists('instance', $configuration)) {
            $this->memcached = $configuration['instance'];
        } else {
            $this->manageCreactionConnection($ttl, $configuration);
            $this->memcached->setOption(\Memcached::OPT_COMPRESSION, $configuration['compress'] ?? false);
            $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, $configuration['opt_binary_protocol'] ?? false);
        }
        if (!$this->isConnected()) {
            throw new \Exception('no connection found');
        }
    }

    private function manageCreactionConnection(int $ttl, array $configuration = []): void
    {
        $port = $configuration['port'] ?? 11211;
        if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
            $this->memcached = new \Memcached($configuration['persistentId']);
            if (empty($this->memcached->getServerList())) {
                $this->memcached->addServer($configuration['server_address'], $port);
            }
        } else {
            $this->memcached = new \Memcached();
            $this->memcached->addServer($configuration['server_address'], $port);
        }
    }

    private readonly \Memcached $memcached;
    private array $mandatoryKeys = ['server_address'];
}
