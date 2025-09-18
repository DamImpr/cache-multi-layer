<?php

namespace App\Service;

use Override;

/**
 * Implementazione della cache APCU 
 * per la documentazione dei metodi, si rimanda alla classe astratta Cache
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see Cache
 */
class ApcuCache extends Cache {

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function fetchObject(Cacheable $object, string $key): bool {
        $this->assertObject($object);
        $data = \apcu_fetch($key);
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
        $data = \apcu_fetch($key);
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
        $array = [];
        array_walk($collection, [$this,'assertObject']);
        foreach ($collection as $c) {
            $array[] = $c->serialize();
        }
        $data = json_encode($array);
        \apcu_store($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setObject(Cacheable $object, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        $data = $object->serialize();
        \apcu_store($key, $data, $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clear(string $key): bool {
        return \apcu_delete($key);
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clearAllCache(): bool {
        return \apcu_clear_cache();
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getCollectionPrimitive(string $key): ?array {
        $data = \apcu_fetch($key);
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
        $actualTTL = $this->checkTTL($ttl);
        foreach ($collection as $v) {
            $this->assertPrimitive($v);
        }
        \apcu_store($key, json_encode($collection), $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getFloat(string $key): ?float {
        $var = \apcu_fetch($key);
        if ($var === false) {
            return null;
        }
        return (float)json_decode((string) $var);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getInteger(string $key): ?int {
        $var = \apcu_fetch($key);
        if ($var === false) {
            return null;
        }
        return (int)json_decode((string) $var);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function getString(string $key): ?string {
        $var = \apcu_fetch($key);
        if ($var === false) {
            return null;
        }
        return json_decode((string) $var);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setFloat(float $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        \apcu_store($key, json_encode($var), $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setInteger(int $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        \apcu_store($key, json_encode($var), $actualTTL);
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[\Override]
    public function setString(string $var, string $key, ?int $ttl = null): void {
        $actualTTL = $this->checkTTL($ttl);
        \apcu_store($key, json_encode($var), $actualTTL);
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int {
        $actualTTL = $this->checkTTL($ttl);
        $success = true;
        return apcu_inc($key, 1, $success, $actualTTL);
    }

    /**
     * 
     * {@InheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): ?int {
        $keyInfo = apcu_key_info($key);
        return $keyInfo !== null ? $keyInfo['ttl'] : null;
    }
    
    
    public function __construct(int $ttl, array $configuration = []) {
        parent::__construct($ttl, $configuration);
    }


}
