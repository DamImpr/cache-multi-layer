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

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function fetchObject(Cacheable $object, string $key): bool {
        $this->assertObject($object);
        $data = $this->redis->get($key);
        if ($data !== false) {
            $object->unserialize($data);
            return true;
        }
        return false;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function getCollectionObject(string $class, string $key): ?array {
        $this->assertClass($class);
        $data = $this->redis->get($key);
        $res = [];
        if ($data === false) {
            return null;
        }
        $array = json_decode((string) $data);
        foreach ($array as $row) {
            $obj = new $class();
            $obj->unserialize($row);
            $res[] = $obj;
        }
        return $res;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setCollectionObject(array $collection, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        array_walk($collection, [$this, 'assertObject']);
        $array = [];
        foreach ($collection as $c) {
            $array[] = $c->serialize();
        }
        $data = json_encode($array);
        $this->redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setObject(Cacheable $object, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $data = $object->serialize();
        $this->redis->set($key, $data, $actualTTL);
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
     * {@inheritDoc}
     */
    #[Override]
    public function getCollectionPrimitive(string $key): ?array {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return json_decode((string) $data, true);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        foreach ($collection as $v) {
            $this->assertPrimitive($v);
        }
        $this->redis->set($key, json_encode($collection), $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function getFloat(string $key): ?float {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return (float) json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function getInteger(string $key): ?int {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return (int) json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function getString(string $key): ?string {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setFloat(float $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $data = json_encode($var);
        $this->redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setInteger(int $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $data = json_encode($var);
        $this->redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function setString(string $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $data = json_encode($var);
        $this->redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int {
        $actualTTL = $this->checkTTL($ttl);
        $value = $this->redis->incr($key);
        if ($value <= $checkIncrementToExpire) {
            $this->redis->expire($key, $actualTTL);
        }
        return $value;
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
