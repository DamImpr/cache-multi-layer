<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use InvalidArgumentException;

/**
 *
 * Class representing the configuration of cache levels, which is
 * then used by CacheManager to manage data across the various levels.
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheConfiguration
{
    /**
     * Cache configuration.
     * Priority is based on the position of the object in the array, where position 0 is the highest priority.
     */
    private array $configuration = [];

    /**
     * Current level of the cache set, used during cache appending.
     */
    private int $currentLevel = 0;

    /**
     * Array used to track caches that have already been set and check that there are no different levels of the same cache.
     */
    private array $setted = [];

    /**
     * Method that sets the next cache level
     * @param CacheEnum $cacheEnum the enumeration used to indicate the cache system, via the enumeration stored in the CacheEnum class
     * @param int $ttl Time to live expressed in seconds
     * @param array<string,mixed> $configuration Parameters required for connecting a specific cache system
     * @see CacheEnum
     * @throws InvalidArgumentException nel caso sia già stato settato il sistema di cache passato
     */
    public function appendCacheLevel(CacheEnum $cacheEnum, int $ttl, array $configuration = []): bool
    {
        if (array_key_exists($cacheEnum->value, $this->setted)) {
            return false;
        }

        $this->configuration[$this->currentLevel] = Cache::factory($cacheEnum, $ttl, $configuration);
        ++$this->currentLevel;
        $this->setted[$cacheEnum->value] = true;
        return true;
    }

    /**
     * returns the cache configuration, where starting from zero you have the first level and moving to the cells of the array on the right you have the subsequent levels.
     * @return array configuration
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
