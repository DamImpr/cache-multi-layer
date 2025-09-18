<?php

namespace App\Service\Cache;


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
    #[\Override]
    public function fetchObject(Cacheable $object, string $key): bool {
        $this->assertObject($object);
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
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
    #[\Override]
    public function getCollectionObject(string $class, string $key): ?array {
        $this->assertClass($class);
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
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
    #[\Override]
    public function setCollectionObject(array $collection, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        array_walk($collection, [$this,'assertObject']);
        $array = [];
        foreach ($collection as $c) {
            $array[] = $c->serialize();
        }
        $data = json_encode($array);
        $redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setObject(Cacheable $object, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        $data = $object->serialize();
        $redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clear(string $key): bool {
        $redis = RedisService::getInstance();
        return (bool)$redis->del($key);
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
    #[\Override]
    public function getCollectionPrimitive(string $key): ?array {
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
        if ($data === false) {
            return null;
        }
        return json_decode((string) $data, true);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void {
        $redis = RedisService::getInstance();
        $actualTTL = $this->checkTTL($ttl);
        foreach ($collection as $v) {
            $this->assertPrimitive($v);
        }
        $redis->set($key, json_encode($collection), $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getFloat(string $key): ?float {
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
        if ($data === false) {
            return null;
        }
        return (float)json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getInteger(string $key): ?int {
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
        if ($data === false) {
            return null;
        }
        return (int)json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getString(string $key): ?string {
        $redis = RedisService::getInstance();
        $data = $redis->get($key);
        if ($data === false) {
            return null;
        }
        return json_decode((string) $data);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setFloat(float $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        $data = json_encode($var);
        $redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setInteger(int $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        $data = json_encode($var);
        $redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setString(string $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        $data = json_encode($var);
        $redis->set($key, $data, $actualTTL);
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[\Override]
    public function increment(string $key, ?int $ttl = null,int $checkIncrementToExpire = 1): int {
        $actualTTL = $this->checkTTL($ttl);
        $redis = RedisService::getInstance();
        $value = $redis->incr($key);
        if ($value <= $checkIncrementToExpire) {
            $redis->expire($key, $actualTTL);
        }
        return $value;
    }
    
    /**
     * 
     * {@InheritDoc}
     */
    public function getRemainingTTL(string $key): ?int {
        $redis = RedisService::getInstance();
        $ttl = $redis->ttl($key);
        return $ttl !== false ? $ttl : null;
    }

}
