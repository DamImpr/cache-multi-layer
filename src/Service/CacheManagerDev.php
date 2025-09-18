<?php

namespace App\Service\Cache;

/**
 * 
 * manager della cache in ambiente di dev, dove tutto il sistema di cache viene ignorato.
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheManagerDev extends CacheManager {

    private array $caches = [];

    public function __construct(CacheConfiguration $configuration) {
        $this->caches = $configuration->getConfiguration();
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function fetchObject(Cacheable $object, string $key): bool {
        return false;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getCollectionObject(string $class, string $key): ?array {
        return null;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setCollectionObject(array $collection, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setObject(Cacheable $object, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clear(string $key): bool {
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function clearAllCache(): bool {
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getCollectionPrimitive(string $key): ?array {
        return null;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getFloat(string $key): ?float {
        return null;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getInteger(string $key): ?int {
        return null;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getString(string $key): ?string {
        return null;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setFloat(float $var, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setInteger(int $var, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function setString(string $var, string $key, ?int $ttl = null): void {
        
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array {
        return [];
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getRemainingTTL(string $key): array {
        return [];
    }
}
