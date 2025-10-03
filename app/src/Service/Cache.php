<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Interface\Cacheable;
use InvalidArgumentException;

/**
 *
 * Service class representing a generic cache system.
 * The methods it offers allow you to save and retrieve a single object or an entire collection
 * passed as an array. Saving and retrieval occurs through the serialisation and deserialisation of an object, associating a key and TTL in seconds, which must be set when constructing the class.
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class Cache
{
    /**
     *
     * @param int $ttl Time to live expressed in seconds
     * @param array<string,mixed> $configuration Parameters required for connecting a specific cache system
     * @throws InvalidArgumentException If the value of ttl is not a positive number
     * @throws CacheMissingConfigurationException If a particular cache system requires configurations that have not been passed in the specific array
     */
    protected function __construct(int $ttl, array $configuration = [])
    {
        if ($ttl <= 0) {
            throw new InvalidArgumentException("ttl must be positive, not like your life");
        }

        $this->assertConfig($configuration);
        $this->ttl = $ttl;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Save data to cache
     * @param string $key cache key
     * @param int|float|string|Cacheable|array $val value to store
     * @param ?int $ttl = null ttl to use, if the passed value is null, the value defined in the constructor is used
     * @return bool true on success, false otherwise
     */
    abstract public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool;

    /**
     * Read data from cache
     * @param string $key cache key
     * @return int|float|string|Cacheable|array|null the value read from the cache, null if nothing was found
     */
    abstract public function get(string $key): int|float|string|Cacheable|array|null;

    /**
     * Check the remaining ttl of a key
     * @param string $key cache key
     * @return ?int ttl remaining ttl, null if nothing was found
     */
    abstract public function getRemainingTTL(string $key): ?int;

    /**
     * Delete data using a key
     * @param string $key cache key
     * @return bool true on success, false otherwise
     */
    abstract public function clear(string $key): bool;

    /**
     * Clear the entire cache
     * @param string $key cache key
     * @return bool true on success, false otherwise
     */
    abstract public function clearAllCache(): bool;

    /**
     * Increase the value based on the key
     * @param string $key cache key
     * @param ?int $ttl = null ttl to be used at the first increment, if the passed value is null, the value defined in the constructor is used
     * @return int|false the new value, false if value is not numeric.
     */
    abstract public function increment(string $key, ?int $ttl = null): int|false;

    /**
    * Decrease the value based on the key
    * @param string $key cache key
    * @param ?int $ttl = null ttl to be used at the first decrement, if the passed value is null, the value defined in the constructor is used
    * @return int|false the new value, false if value is not numeric.
    */
    abstract public function decrement(string $key, ?int $ttl = null): int|false;

    /**
     * Check whether the connection to the cache is still active.
     * @return bool true on success, false otherwise
     */
    abstract public function isConnected(): bool;

    /**
     * Enumeration associated with the instance
     */
    abstract public function getEnum(): CacheEnum;

    /**
     * Factory method of a cache system, where through an enumeration
     * and the ttl to be associated, it returns the specific Cache class.
     * Even if specified as an integer, do not pass numbers, but use the CacheEnum constants.
     * @param CacheEnum $cacheEnum the enumeration used to indicate the cache system, via the enumeration stored in the CacheEnum class
     * @param int $ttl Time to live expressed in seconds
     * @param array<string,mixed> $configuration Parameters required for connecting a specific cache system
     * @return Cache Cache system associated with enumeration
     * @throws CacheMissingConfigurationException If a particular cache system requires configurations that have not been passed in the specific array
     */
    public static function factory(CacheEnum $cacheEnum, int $ttl, array $configuration = []): Cache
    {
        return match ($cacheEnum) {
            CacheEnum::APCU => new ApcuCache($ttl, $configuration),
            CacheEnum::REDIS => new RedisCache($ttl, $configuration),
            CacheEnum::MEMCACHE => new MemcacheCache($ttl, $configuration)
        };
    }

    /**
     * Used in subclasses to check whether a ttl different from that of the constructor is defined in operations and use it, or use that of the constructor.
     * @param ?int $ttl different to check
     * @return int ttl to be used
     */
    protected function getTtlToUse(?int $ttl): int
    {
        return ($ttl ?? -1) < 0 ? $this->ttl : $ttl;
    }

    /**
     * Checks whether the necessary configurations for the cache are present
     * @param array<string,mixed> $configuration
     * @throws CacheMissingConfigurationException if a configuration is missing
     */
    protected function assertConfig(array $configuration): void
    {
        $mandatoryKeys = $this->getMandatoryConfig();
        if (!empty(array_diff_key($mandatoryKeys, array_keys($configuration)))) {
            throw new CacheMissingConfigurationException(implode(',', $mandatoryKeys) . " are mandatory configurations");
        }
    }

    /**
     * @retrun array<string> the necessary configurations
     */
    abstract protected function getMandatoryConfig(): array;

    /**
     * Manages primitive and object variables to save them in the cache
     * @param int|float|string|Cacheable $val value to be serialised
     * @return  int|float|string|array value serialized
     */
    final protected function serializeVal(int|float|string|Cacheable $val): int|float|string|array
    {
        if ($val instanceof Cacheable) {
            return ['__cacheable' => 1, '__class' => $val::class, '__data' => $val->serialize()];
        }

        return $val;
    }

    /**
     * Manages data read from the cache that has been handled using the appropriate methods for insertion
     * @param array $val value to be unserialized
     * @return array|Cacheable value unserialized
     * @see Cache#serializeVal
     * @see Cache#serializeValArray
     */
    final protected function unserializeVal(array $val): array|Cacheable
    {
        $res = [];
        if (array_key_exists('__cacheable', $val)) {
            $res = new $val['__class']();
            $res->unserialize($val['__data']);
        } else {
            foreach ($val as $key => $value) {
                $res[$key] = is_array($value) ? $this->unserializeVal($value) : $value;
            }
        }

        return $res;
    }

    /**
     *
     * Manages array variables to save them in the cache
     * @param array to be serialized
     * @return array unserialized
     */
    final protected function serializeValArray(array $val): array
    {
        $res = [];
        foreach ($val as $key => $value) {
            $res[$key] = is_array($value) ? $this->serializeValArray($value) : $this->serializeVal($value);
        }

        return $res;
    }

    /**
     * ttl in seconds to associate with the cache system
     */
    private readonly int $ttl;
}
