<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;
use Override;

/**
 *
 * REDIS cache implementation
 * @author Damiano Improta <code@damianoimprota.it>
 */
class RedisCache extends Cache
{
    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $value = $this->redis->decr($key);
        if (empty($this->getRemainingTTL($key))) {
            $this->redis->expire($key, $this->getTtlToUse($ttl));
        }

        return $value;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->redis->get($key);
        if ($val === null) {
            return null;
        }

        $valDecoded = json_decode((string) $val, true);
        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        return $this->redis->setex($key, $this->getTtlToUse($ttl), json_encode($data)) !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $value = $this->redis->incr($key);
        if (empty($this->getRemainingTTL($key))) {
            $this->redis->expire($key, $this->getTtlToUse($ttl));
        }

        return $value;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $key): bool
    {
        return (bool) $this->redis->del($key);
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool
    {
        return $this->redis->flushall() !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        $ttl = $this->redis->ttl($key);
        return $ttl !== false ? $ttl : null;
    }

    /**
     *
     * {@InheritDoc}
     */
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

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function isConnected(): bool
    {
        return $this->redis->ping() !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::REDIS;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    protected function getMandatoryConfig(): array
    {
        return $this->mandatoryKeys;
    }

    #[\Override]
    protected function assertConfig(array $configuration): void
    {
        if (!array_key_exists('instance', $configuration) || $configuration['instance'] instanceof \Redis) {
            parent::assertConfig($configuration);
        } elseif (array_key_exists('instance', $configuration)) {
            throw new CacheMissingConfigurationException("instance must be " . \Redis::class . " class");
        }
    }

    private readonly \Redis $redis;

    private array $mandatoryKeys = [
        'server_address'
    ];
}
