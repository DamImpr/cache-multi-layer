<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;
use Exception;
use Memcache;
use Override;

/**
 * MEMCACHE cache implementation
 * @author Damiano Improta <code@damianoimprota.it>
 */
class MemcacheCache extends Cache
{

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $key): bool
    {
        return $this->memcache->delete($this->getEffectiveKey($key));
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool
    {
        return $this->memcache->flush();
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
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

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($val)) {
            return null;
        }

        $valDecoded = json_decode((string) $val['data'], true);
        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::MEMCACHE;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        $val = $this->memcache->get($this->getEffectiveKey($key));
        if (empty($val)) {
            return null;
        }

        return $val['exipres_at'] - time();
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
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

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function isConnected(): bool
    {
        return $this->memcache->getStats() !== false;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $values = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        $ttlToUse = $this->getTtlToUse($ttl);
        $dataToStore = [
            'data' => json_encode($values)
            , 'exipres_at' => time() + $ttlToUse
        ];
        return $this->memcache->set($this->getEffectiveKey($key), $dataToStore, $this->compress ? MEMCACHE_COMPRESSED : 0, $ttlToUse);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        if (array_key_exists('instance', $configuration)) {
            $this->memcache = $configuration['instance'];
        } else {
            $this->memcache = new Memcache();
            $port = $configuration['port'] ?? 11211;
            if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
                $resultConnection = $this->memcache->pconnect($configuration['server_address'], $port);
            } else {
                $resultConnection = $this->memcache->connect($configuration['server_address'], $port);
            }

            if (!$resultConnection) {
                throw new Exception("Connection not found");
            }
        }
        $this->prefixKey = $configuration['key_prefix'] ?? '';
        $this->compress = array_key_exists('compress', $configuration) && $configuration['compress'];
    }

    #[\Override]
    protected function assertConfig(array $configuration): void
    {
        if (!array_key_exists('instance', $configuration) || $configuration['instance'] instanceof Memcache) {
            parent::assertConfig($configuration);
        } elseif (array_key_exists('instance', $configuration)) {
            throw new CacheMissingConfigurationException("instance must be " . Memcache::class . " class");
        }
    }

    /**
     * manages keys by adding the prefix set during configuration
     * @param string $key cache key
     * @return string key to be used
     */
    private function getEffectiveKey(string $key): string
    {
        return $this->prefixKey . $key;
    }
    private readonly Memcache $memcache;
    private readonly string $prefixKey;
    private readonly bool $compress;
    private array $mandatoryKeys = [
        'server_address'
    ];
}