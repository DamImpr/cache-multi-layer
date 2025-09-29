<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;
use Override;
use Predis\Client as PredisClient;

/**
 * 
 * Implementazione della cache Redis 
 * per la documentazione dei metodi, si rimanda all'interfaccia Cache
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class RedisCache extends Cache {

    /**
     * 
     * {@InheritDoc}
     */
    #[\Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): int {
        $value = $this->redis->decr($key);
        if ($value <= $checkDecrementToExpire) {
            $this->redis->expire($key, $this->getTtlToUse($ttl));
        }
        return $value;
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null {
        $val = $this->redis->get($key);
        if ($val === null) {
            return null;
        }
        $valDecoded = json_decode($val, true);
        return is_array($valDecoded) ? $this->unserializeValArray($valDecoded) : $valDecoded;
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool {
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        return $this->redis->setex($key, $this->getTtlToUse($ttl), json_encode($data)) !== null;
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int {
        $value = $this->redis->incr($key);
        if ($value <= $checkIncrementToExpire) {
            $this->redis->expire($key, $this->getTtlToUse($ttl));
        }
        return $value;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $key): bool {
        return (bool) $this->redis->del($key);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool {
        return $this->redis->flushall() !== NULL;
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): ?int {
        $ttl = $this->redis->ttl($key);
        return $ttl !== false ? $ttl : null;
    }

    /**
     * 
     * {@InheritDoc}
     */
    public function __construct(int $ttl, array $configuration = []) {
        parent::__construct($ttl, $configuration);
        $this->init($configuration);
    }

    #[\Override]
    public function isConnected(): bool {
        return $this->redis->ping() !== null;
    }

    private function serializeVal(int|float|string|Cacheable $val): int|float|string|array {
        if ($val instanceof Cacheable) {
            return ['__cacheable' => 1, '__class' => $val::class, '__data' => $val->serialize()];
        }
        return $val;
    }

    private function unserializeValArray(array $val): array|Cacheable {
        $res = [];
        if (array_key_exists('__cacheable', $val)) {
            $res = new $val['__class']();
            $res->unserialize($val['__data']);
        } else {
            foreach ($val as $key => $value) {
                $res[$key] = is_array($value) ? $this->unserializeValArray($value) : $value;
            }
        }
        return $res;
    }

    private function serializeValArray(array $val): array {
        $res = [];
        foreach ($val as $key => $value) {
            $res[$key] = is_array($value) ? $this->serializeValArray($value) : $this->serializeVal($value);
        }
        return $res;
    }

    private function init(array $configuration): void {
        $mandatoryKeys = [
            'server_address'
            , 'port'
        ];
        if (!empty(array_diff_key($mandatoryKeys, array_keys($configuration)))) {
            throw new CacheMissingConfigurationException(implode(',', $mandatoryKeys) . " are mandatory configurations");
        }
        $this->redis = new PredisClient([
            'scheme' => $configuration['tcp'] ?? 'tcp',
            'host' => $configuration['server_address'],
            'port' => $configuration['port'],
            'password' => $configuration['password'] ?? '',
            'database' => $configuration['database'] ?? 0,
        ]);
    }

    private readonly PredisClient $redis;
}
