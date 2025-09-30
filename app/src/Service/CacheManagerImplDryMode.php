<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * 
 * manager della cache in ambiente di dev, dove tutto il sistema di cache viene ignorato.
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheManagerImplDryMode extends CacheManager {

    
    protected function __construct(?CacheConfiguration $cacheConfiguration)
    {
        parent::__construct($cacheConfiguration);
    }
    

    #[\Override]
    public function appendCache(Cache $cache): bool {
        return true;
    }

    #[Override]
    public function clear(string $key): bool {
        return true;
    }

    #[Override]
    public function clearAllCache(): bool {
        return true;
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): array {
        return [];
    }

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null {
        return null;
    }

    #[Override]
    public function getRemainingTTL(string $key): array {
        return [];
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array {
        return [];
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool {
        return true;
    }

    
}
