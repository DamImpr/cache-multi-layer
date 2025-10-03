<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 *
 * cache manager in dry run mode, where the entire cache system is bypassed
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheManagerImplDryMode extends CacheManagerImpl
{
    protected function __construct(?CacheConfiguration $cacheConfiguration = null)
    {
        parent::__construct($cacheConfiguration);
    }

    #[\Override]
    public function appendCache(Cache $cache): bool
    {
        return parent::appendCache($cache);
    }

    #[Override]
    public function clear(string $key): bool
    {
        return true;
    }

    #[Override]
    public function clearAllCache(): bool
    {
        return true;
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null): array
    {
        return [];
    }

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        return null;
    }

    #[Override]
    public function getRemainingTTL(string $key): array
    {
        return [];
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null): array
    {
        return [];
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        return true;
    }
}
