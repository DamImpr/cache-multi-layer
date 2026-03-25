<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;

/**
 * APCU cache implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 *
 * @see Cache
 */
class ApcuCache extends Cache
{
    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $success = true;
        $res = apcu_fetch($this->getEffectiveKey($key), $success);

        return $success ? $res : null;
    }

    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        return apcu_store($this->getEffectiveKey($key), $val, $this->getTtlToUse($ttl));
    }

    #[\Override]
    public function clear(string $key): bool
    {
        return \apcu_delete($this->getEffectiveKey($key));
    }

    #[\Override]
    public function clearAllCache(): bool
    {
        return apcu_clear_cache();
    }

    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $success = true;
        $res = apcu_dec($this->getEffectiveKey($key), 1, $success, $this->getTtlToUse($ttl));

        return $success ? $res : false;
    }

    #[\Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $success = true;
        $res = apcu_inc($this->getEffectiveKey($key), 1, $success, $this->getTtlToUse($ttl));

        return $success ? $res : false;
    }

    #[\Override]
    public function getRemainingTTL(string $key): ?int
    {
        $keyInfo = apcu_key_info($this->getEffectiveKey($key));

        return null !== $keyInfo ? $keyInfo['ttl'] : null;
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

    #[\Override]
    protected function checkInstanceIsCorrect(object $instance): bool
    {
        return false;
    }

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
    }
}
