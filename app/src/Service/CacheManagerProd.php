<?php

namespace App\Service;



/**

 * Sistema di cache in produzione, segue fedelmente la gerarchia di livelli passata nella configurazione
 * per la documentazione dei metodi, si rimanda alla classe astratta CacheManager
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see CacheManager
 */
class CacheManagerProd extends CacheManager {

    /**
     * le caches lette dalla configurazione
     */
    private array $caches = [];
    private array $priority = [];

    /**
     * dimensione dell'array $caches, mantenuta qui per poter avere l'informazione in tempo costante O(1)
     */
    private int $size = 0;

    public function __construct(CacheConfiguration $configuration) {
        $this->caches = $configuration->getConfiguration();
        $this->priority = $configuration->getPriorityList();
        $this->size = count($this->caches);
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function fetchObject(Cacheable $object, string $key): bool {
        $i = 0;
        $data = false;
        for ($i = 0; $i < $this->size && $data === false; $i++) {
            $data = $this->caches[$i]->fetchObject($object, $key);
        }
        if ($data === false) {
            return false;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setObject($object, $key);
        }
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setObject(Cacheable $object, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setObject($object, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setCollectionObject(array $collection, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setCollectionObject($collection, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getCollectionObject(string $class, string $key): ?array {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; $i++) {
            $data = $this->caches[$i]->getCollectionObject($class, $key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setCollectionObject($data, $key);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clear(string $key): bool {
        $res = false;
        for ($i = 0; $i < $this->size; $i++) {
            $tmp = $this->caches[$i]->clear($key);
            if ($tmp === true) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clearAllCache(): bool {
        for ($i = 0; $i < $this->size; $i++) {
            try {
                $this->caches[$i]->clearAllCache();
            } catch (ClearCacheDeniedException) {
                //skip
            }
        }
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getCollectionPrimitive(string $key): ?array {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; $i++) {
            $data = $this->caches[$i]->getCollectionPrimitive($key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setCollectionPrimitive($data, $key);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setCollectionPrimitive($collection, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getFloat(string $key): ?float {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; $i++) {
            $data = $this->caches[$i]->getFloat($key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setFloat($data, $key);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getInteger(string $key): ?int {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; $i++) {
            $data = $this->caches[$i]->getInteger($key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setInteger($data, $key);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getString(string $key): ?string {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; $i++) {
            $data = $this->caches[$i]->getString($key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$j]->setString($data, $key);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setFloat(float $var, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setFloat($var, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setInteger(int $var, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setInteger($var, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setString(string $var, string $key, ?int $ttl = null): void {
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->setString($var, $key, $ttl);
        }
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array {
        $res = [];
        for ($i = 0; $i < $this->size; $i++) {
            $res[$this->priority[$i]] = $this->caches[$i]->increment($key, $ttl, $checkIncrementToExpire);
        }
        return $res;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getRemainingTTL(string $key): array {
        $res = [];
        for ($i = 0; $i < $this->size; $i++) {
            $res[$this->priority[$i]] = $this->caches[$i]->getRemainingTTL($key);
        }
        return $res;
    }

}
