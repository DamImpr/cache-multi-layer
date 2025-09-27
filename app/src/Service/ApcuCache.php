<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * Implementazione della cache APCU 
 * per la documentazione dei metodi, si rimanda alla classe astratta Cache
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see Cache
 */
class ApcuCache extends Cache {

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null {
        $success = true;
        $res = apcu_fetch($key, $success);
        return $success ? $res : null;
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool {
        return apcu_store($key, $val, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function clear(string $key): bool {
        return \apcu_delete($key);
    }

    #[Override]
    public function clearAllCache(): bool {
        return apcu_clear_cache();
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): int {
        $success = true;
        return apcu_dec($key, 1, $success, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int {
        $success = true;
        return apcu_inc($key, 1, $success, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function getRemainingTTL(string $key): ?int {
        $keyInfo = apcu_key_info($key);
        return $keyInfo !== null ? $keyInfo['ttl'] : null;
    }

    #[\Override]
    public function isConnected(): bool {
        return extension_loaded('apcu');
    }

    public function __construct(int $ttl, array $configuration = []) {
        parent::__construct($ttl, $configuration);
    }
}
