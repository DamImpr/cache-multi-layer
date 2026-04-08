<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Memcache;

/**
 * MEMCACHE cache implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class MemcacheCache extends Cache
{

    #[\Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    #[\Override]
    public function clear(string $key): bool
    {
        return $this->memcache->delete($this->getEffectiveKey($key));
    }

    #[\Override]
    public function clearAllCache(): bool
    {
        return $this->memcache->flush();
    }

    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $pair = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($pair)) {
            $this->set($key, -1, $ttl);

            return -1;
        }

        $value = $pair['data'];
        if (!is_numeric($value)) {
            return false;
        }

        --$value;
        $pair['data'] = $value;
        $this->memcache->set($this->getEffectiveKey($key), $pair, $this->compress ? MEMCACHE_COMPRESSED : 0, $this->getRemainingTTL($key));

        return $value;
    }

    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($val)) {
            return null;
        }

        $valDecoded = json_decode((string) $val['data'], true);

        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::MEMCACHE;
    }

    #[\Override]
    public function getRemainingTTL(string $key): ?int
    {
        $val = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($val)) {
            return null;
        }

        return $val['expires_at'] - time();
    }

    #[\Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $pair = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($pair)) {
            $this->set($key, 1, $ttl);

            return 1;
        }

        $value = $pair['data'];
        if (!is_numeric($value)) {
            return false;
        }

        ++$value;
        $pair['data'] = $value;
        $this->memcache->set($this->getEffectiveKey($key), $pair, $this->compress ? MEMCACHE_COMPRESSED : 0, $this->getRemainingTTL($key));

        return $value;
    }

    #[\Override]
    public function isConnected(): bool
    {
        return false !== $this->memcache->getStats();
    }

    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $values = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        $ttlToUse = $this->getTtlToUse($ttl);
        $dataToStore = [
            'data' => json_encode($values), 'expires_at' => time() + $ttlToUse,
        ];

        return $this->memcache->set($this->getEffectiveKey($key), $dataToStore, $this->compress ? MEMCACHE_COMPRESSED : 0, $ttlToUse);
    }

    #[\Override]
    protected function checkInstanceIsCorrect(object $instance): bool
    {
        return $instance instanceof \Memcache;
    }

    #[\Deprecated(message: "use Memcached", since: "3.0")]
    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        if (array_key_exists('instance', $configuration)) {
            $this->memcache = $configuration['instance'];
        } else {
            $this->memcache = new \Memcache();
            $port = $configuration['port'] ?? 11211;
            if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
                $resultConnection = $this->memcache->pconnect($configuration['server_address'], $port);
            } else {
                $resultConnection = $this->memcache->connect($configuration['server_address'], $port);
            }

            if (!$resultConnection) {
                throw new \Exception('Connection not found');
            }
        }

        $this->compress = array_key_exists('compress', $configuration) && $configuration['compress'];
    }

    private readonly \Memcache $memcache;
    private readonly bool $compress;
    private array $mandatoryKeys = [
        'server_address',
    ];
}
