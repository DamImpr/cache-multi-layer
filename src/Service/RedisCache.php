<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;

/**
 * REDIS cache implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class RedisCache extends Cache
{
    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $value = $this->redis->decr($this->getEffectiveKey($key));
        if (empty($this->getRemainingTTL($key))) {
            $this->redis->expire($this->getEffectiveKey($key), $this->getTtlToUse($ttl));
        }

        return $value;
    }

    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->redis->get($this->getEffectiveKey($key));
        if (null === $val) {
            return null;
        }

        $valDecoded = json_decode((string) $val, true);

        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);

        return null !== $this->redis->setex($this->getEffectiveKey($key), $this->getTtlToUse($ttl), json_encode($data));
    }

    #[\Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $value = $this->redis->incr($this->getEffectiveKey($key));
        if (empty($this->getRemainingTTL($key))) {
            $this->redis->expire($this->getEffectiveKey($key), $this->getTtlToUse($ttl));
        }

        return $value;
    }

    #[\Override]
    public function clear(string $key): bool
    {
        return (bool) $this->redis->del($this->getEffectiveKey($key));
    }

    #[\Override]
    public function clearAllCache(): bool
    {
        return null !== $this->redis->flushall();
    }

    #[\Override]
    public function getRemainingTTL(string $key): ?int
    {
        $ttl = $this->redis->ttl($this->getEffectiveKey($key));

        return $ttl >= 0 ? $ttl : null;
    }

    #[\Override]
    public function isConnected(): bool
    {
        return null !== $this->redis->ping();
    }

    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::REDIS;
    }

    #[\Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    #[\Override]
    protected function checkInstanceIsCorrect(object $instance): bool
    {
        return $instance instanceof \Redis;
    }

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        if (array_key_exists('instance', $configuration)) {
            $this->redis = $configuration['instance'];
        } else {
            $this->redis = new \Redis();
            if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
                $this->redis->pconnect($configuration['server_address'], $configuration['port'] ?? 6379, $configuration['timeout'] ?? 3, $configuration['connection_id'] ?? 'app_redis_connection');
            } else {
                $this->redis->connect($configuration['server_address'], $configuration['port'] ?? 6379, $configuration['timeout'] ?? 3);
            }
        }
    }

    private readonly \Redis $redis;
    private array $mandatoryKeys = [
        'server_address',
    ];
}
