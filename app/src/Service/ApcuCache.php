<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 * Implementazione della cache APCU 
 * per la documentazione dei metodi, si rimanda alla classe astratta Cache
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see Cache
 */
class ApcuCache extends Cache
{

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $success = true;
        $res = apcu_fetch($this->getEffectiveKey($key), $success);
        return $success ? $res : null;
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        return apcu_store($this->getEffectiveKey($key), $val, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function clear(string $key): bool
    {
        return \apcu_delete($this->getEffectiveKey($key));
    }

    #[Override]
    public function clearAllCache(): bool
    {
        return apcu_clear_cache();
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1):  int|false
    {
        $success = true;
        return apcu_dec($this->getEffectiveKey($key), 1, $success, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1):  int|false
    {
        $success = true;
        return apcu_inc($this->getEffectiveKey($key), 1, $success, $this->getTtlToUse($ttl));
    }

    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        $keyInfo = apcu_key_info($this->getEffectiveKey($key));
        return $keyInfo !== null ? $keyInfo['ttl'] : null;
    }

    #[\Override]
    public function isConnected(): bool
    {
        return extension_loaded('apcu');
    }

    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::APCU;
    }

    #[\Override]
    protected function getMandatoryConfig(): array
    {
        return [];
    }

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        $this->prefixKey = $configuration['prefix_key'] ?? '';
    }

    private function getEffectiveKey(string $key): string
    {
        return $this->prefixKey . $key;
    }

    private readonly string $prefixKey;
}
