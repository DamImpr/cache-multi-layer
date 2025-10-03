<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Exception\ClearCacheDeniedException;
use CacheMultiLayer\Interface\Cacheable;

/**
 *
 * Cache manager class, responsible for saving and reading data in various cache systems.
 * The search starts from the first cache level, and if it fails, the search continues
 * to the next level.
 * When a given cache level returns data, all higher cache levels that returned a search failure are updated.
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class CacheManager
{
    /**
     * A configuration can be passed to the builder where the caches to be used with the established levels are already defined.
     * @param CacheConfiguration $cacheConfiguration the configuration of cache systems
     */
    abstract protected function __construct(?CacheConfiguration $cacheConfiguration = null);

    /**
     * Adding a cache.
     * Whenever a cache is added, it should be considered as the last level.
     * @parm Cache $cache
     * @return bool true on success, false if the input cache is already set
     */
    abstract public function appendCache(Cache $cache): bool;

    /**
     * Save data in all cache levels
     * @param string $key cache key
     * @param int|float|string|Cacheable|array $val value to store
     * @param ?int $ttl = null ttl to use, if the passed value is null, the value defined in the constructor is used
     * @return bool true on success, false otherwise
     */
    abstract public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool;

    /**
     * Read data from the first cache level containing the key passed in as input.
     * @param string $key cache key
     * @return int|float|string|Cacheable|array|null the value read from the cache, null if nothing was found
     */
    abstract public function get(string $key): int|float|string|Cacheable|array|null;

    /**
     * get the remaining ttl of a key
     * @param string $key cache key
     * @return array<string,int|null> for each cache level remaining ttl, null if nothing was found
     */
    abstract public function getRemainingTTL(string $key): array;

    /**
     * Delete data from all cache levels using a key
     * @param string $key cache key
     * @return  bool true if all cache leves return trues, false otherwise
     */
    abstract public function clear(string $key): bool;

    /**
      * Delete data from all cache levels
      * @return bool true if all cache leves return trues, false otherwise
      */
    abstract public function clearAllCache(): bool;

    /**
     * Increase the value based on the key in all cache levels
     * @param string $key cache key
     * @param ?int $ttl = null ttl to be used at the first increment, if the passed value is null, the value defined in the constructor is used
     * @return array<string,int|false> for each cache level the new value, false if value is not numeric.
     */
    abstract public function increment(string $key, ?int $ttl = null): array;

    /**
     * Decrease the value based on the key in all cache levels
     * @param string $key cache key
     * @param ?int $ttl = null ttl to be used at the first decrement, if the passed value is null, the value defined in the constructor is used
     * @return array<string,int|false> for each cache level the new value, false if value is not numeric.
     */
    abstract public function decrement(string $key, ?int $ttl = null): array;

    /**
     * factory method with the option to pass a configuration and the dry run option
     * @param ?CacheConfiguration $cacheConfiguration preset cache configuration.
     * @param bool $dryMode true to ensure that the operations of the returned manager are not actually executed
     * @return CacheManager manager returned based on parameters
     */
    public static function factory(?CacheConfiguration $cacheConfiguration = null, bool $dryMode = false): CacheManager
    {
        return !$dryMode ? new CacheManagerImpl($cacheConfiguration) : new CacheManagerImplDryMode($cacheConfiguration);
    }
}
