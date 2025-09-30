<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class MemcacheCache extends Cache
{

    #[Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    #[Override]
    public function clear(string $key): bool
    {
        
    }

    #[Override]
    public function clearAllCache(): bool
    {
        
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): int
    {
        
    }

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        
    }

    #[Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::MEMCACHE;
    }

    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int
    {
        
    }

    #[Override]
    public function isConnected(): bool
    {
        return $this->memcache->getStats() !== false;
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $values = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        return $this->memcache->set($key, $values, $this->compress ? MEMCACHE_COMPRESSED : 0, $this->getTtlToUse($ttl));
    }

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        $this->memcache = new \Memcache();
        if (array_key_exists('persistent', $configuration)) {
            $this->memcache->pconnect($configuration['server_address'], $configuration['port']);
        } else {
            $this->memcache->connect($configuration['server_address'], $configuration['port']);
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
