<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;
use Override;
use Predis\Client as PredisClient;

/**
 *
 * PREDIS cache implementation
 * @author Damiano Improta <code@damianoimprota.it>
 */
class PRedisCache extends Cache
{
    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $value = $this->predisClient->decr($this->getEffectiveKey($key));
        if (empty($this->getRemainingTTL($key))) {
            $this->predisClient->expire($this->getEffectiveKey($key), $this->getTtlToUse($ttl));
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
        $val = $this->predisClient->get($this->getEffectiveKey($key));
        if ($val === null) {
            return null;
        }

        $valDecoded = json_decode($val, true);
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
        return $this->predisClient->setex($this->getEffectiveKey($key), $this->getTtlToUse($ttl), json_encode($data)) !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $value = $this->predisClient->incr($this->getEffectiveKey($key));
        if (empty($this->getRemainingTTL($key))) {
            $this->predisClient->expire($this->getEffectiveKey($key), $this->getTtlToUse($ttl));
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
        return (bool) $this->predisClient->del($this->getEffectiveKey($key));
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool
    {
        return $this->predisClient->flushall() !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        $ttl = $this->predisClient->ttl($key);
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
            $this->predisClient = $configuration['instance'];
        } else {
            $this->predisClient = new PredisClient([
                'scheme' => $configuration['tcp'] ?? 'tcp',
                'host' => $configuration['server_address'],
                'port' => $configuration['port'] ?? 6379,
                'password' => $configuration['password'] ?? '',
                'database' => $configuration['database'] ?? 0,
                'persistent' => $configuration['persistent'] ?? false,
                'conn_uid' => $configuration['connection_id'] ?? ''
            ]);
        }
        $this->prefixKey = $configuration['key_prefix'] ?? '';
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function isConnected(): bool
    {
        return $this->predisClient->ping() !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[\Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::PREDIS;
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
        if (!array_key_exists('instance', $configuration) || $configuration['instance'] instanceof PredisClient) {
            parent::assertConfig($configuration);
        } elseif (array_key_exists('instance', $configuration)) {
            throw new CacheMissingConfigurationException("instance must be " . PredisClient::class . " class");
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
    private readonly PredisClient $predisClient;
    private readonly string $prefixKey;
    private array $mandatoryKeys = [
        'server_address'
    ];
}
