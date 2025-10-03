<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Override;
use Predis\Client as PredisClient;

/**
 *
 * REDIS cache implementation
 * @author Damiano Improta <code@damianoimprota.dev>
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
        $value = $this->predisClient->decr($key);
        if (empty($this->getRemainingTTL($key))) {
            $this->predisClient->expire($key, $this->getTtlToUse($ttl));
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
        $val = $this->predisClient->get($key);
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
        return $this->predisClient->setex($key, $this->getTtlToUse($ttl), json_encode($data)) !== null;
    }

    /**
     *
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $value = $this->predisClient->incr($key);
        if (empty($this->getRemainingTTL($key))) {
            $this->predisClient->expire($key, $this->getTtlToUse($ttl));
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
        return (bool) $this->predisClient->del($key);
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
        $this->predisClient = new PredisClient([
            'scheme' => $configuration['tcp'] ?? 'tcp',
            'host' => $configuration['server_address'],
            'port' => $configuration['port'],
            'password' => $configuration['password'] ?? '',
            'database' => $configuration['database'] ?? 0,
        ]);
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

    private readonly PredisClient $predisClient;

    private array $mandatoryKeys = [
        'server_address'
        , 'port'
    ];
}
