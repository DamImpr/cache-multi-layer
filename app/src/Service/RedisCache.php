<?php

namespace App\Service;

use App\Exception\CacheMissingConfigurationException;
use App\Exception\ClearCacheDeniedException;
use App\Interface\Cacheable;
use Override;

/**
 * 
 * Implementazione della cache Redis 
 * per la documentazione dei metodi, si rimanda all'interfaccia Cache
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class RedisCache extends Cache {

    #[\Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): int {
        $value = $this->redis->decr($key);
        if ($value <= $checkDecrementToExpire) {
            $this->redis->expire($key, $this->getTtlToUse($ttl));
        }
        return $value;
    }

    #[\Override]
    public function get(string $key, ?string $class = null): int|float|string|Cacheable|array|null {
        if ($class !== null && !in_array(Cacheable::class, class_implements($class))) {
            //throw error, ma vedi di generalizzare questo livello
        }
        $val = json_decode($this->redis->get($key));
        return is_array($val) ? $this->unserializeValArray($val) : $this->unserializeVal($key, $class);
    }

    #[\Override]
    public function set(int|float|string|Cacheable|array $val, string $key, ?int $ttl = null): void {
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        $this->redis->set($key, json_encode($data), $this->getTtlToUse($ttl));
    }

    private function serializeVal(int|float|string|Cacheable $val): int|float|string {
        if ($val instanceof Cacheable) {
            return $val->serialize();
        }
        return $val;
    }

    private function unserializeVal(int|float|string $val, ?string $class): int|float|string|Cacheable {
        if ($class !== null) {
            $res = new $class();
            $res->unserialize($val);
        }
        return $val;
    }

    private function unserializeValArray(array $val): array {
        $res = [];
        foreach ($val as $key => $value) {
            $res[$key] = $this->unserializeVal($value);
        }
        return $res;
    }

    private function serializeValArray(array $val): array {
        $res = [];
        foreach ($val as $key => $value) {
            $res[$key] = $this->serializeVal($value);
        }
        return $res;
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
    public function clear(string $key): bool {
        return (bool) $this->redis->del($key);
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clearAllCache(): bool {
        throw new ClearCacheDeniedException("CLEAR ALL CACHE ON REDIS IS FORBIDDEN");
    }

    /**
     * 
     * {@InheritDoc}
     */
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
        $this->redis = new Redis();
    }

    private function init(array $configuration): void {
        $mandatoryKeys = [
            'redis_server'
            , 'redis_port'
        ];
        if (!empty(array_diff_key($mandatoryKeys, array_keys($configuration)))) {
            throw new CacheMissingConfigurationException(implode(',', $mandatoryKeys) . " are mandatory configurations");
        }
        $this->redis = new \Redis();
        if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
            $this->redis->pconnect($configuration['redis_server'], $configuration['redis_port']);
        } else {
            $this->redis->connect($configuration['redis_server'], $configuration['redis_port']);
        }
    }

    private readonly Redis $redis;
}
