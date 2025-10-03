<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Memcache;
use Override;

/**
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
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
        return $this->memcache->delete($key);
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
        $pair = $this->memcache->get($key);
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
        $this->memcache->set($key, $pair, $this->compress ? MEMCACHE_COMPRESSED : 0, $this->getRemainingTTL($key));
        return $value;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcache->get($key);
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
        $val = $this->memcache->get($key);
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
        $pair = $this->memcache->get($key);
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
        $this->memcache->set($key, $pair, $this->compress ? MEMCACHE_COMPRESSED : 0, $this->getRemainingTTL($key));
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
        return $this->memcache->set($key, $dataToStore, $this->compress ? MEMCACHE_COMPRESSED : 0, $ttlToUse);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        $this->memcache = new Memcache();
        if (array_key_exists('persistent', $configuration)) {
            $resultConnection = $this->memcache->pconnect($configuration['server_address'], $configuration['port']);
        } else {
            $resultConnection = $this->memcache->connect($configuration['server_address'], $configuration['port']);
        }

        if (!$resultConnection) {
            throw new \Exception("Connection not found");
        }

        $this->compress = array_key_exists('compress', $configuration) && $configuration['compress'];
    }

    private readonly Memcache $memcache;

    private readonly bool $compress;

    private array $mandatoryKeys = [
        'server_address'
        , 'port'
    ];
}
