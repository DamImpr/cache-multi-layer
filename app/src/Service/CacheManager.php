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
     * @return bool true on success, false if cache type is already setted
     */
    abstract public function appendCache(Cache $cache): bool;

    /**
     *
     */
    abstract public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool;

    /**
     *
     */
    abstract public function get(string $key): int|float|string|Cacheable|array|null;

    /**
     *
     */
    abstract public function getRemainingTTL(string $key): array;

    /**
     * Metodo che cancella il valore di una chiave dalla cache.
     * @param string $key la chiave il cui il valore deve essere cancellato
     * @return bool true se è avvenuta una cancellazione, false altrimenti
     */
    abstract public function clear(string $key): bool;

    /**
     * Metodo che cancella tutte i valori contenuti nella cache, se il sistema di cache lo permette
     * @return bool always true
     * @throws ClearCacheDeniedException se la possibilità di cancellare tutta la cache è negata
     */
    abstract public function clearAllCache(): bool;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkIncrementToExpire limite valore massimo per aggiornare il ttl.
     * @return array il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0
     */
    abstract public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkDecrementToExpire limite valore massimo per aggiornare il ttl.
     * @return array il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0
     */
    abstract public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): array;

    /**
     * Metodo di factory, restituisce Il Sistema di cache in modalità Dev o in modalità Prod in base alla flag settata nell'enviroment di symfony
     * @param ?CacheConfiguration $cacheConfiguration Configurazione della cache da adottare.
     * @return CacheManager istanza della classe che gestisce la cache
     */
    public static function factory(?CacheConfiguration $cacheConfiguration = null, bool $dryMode = false): CacheManager
    {
        return !$dryMode ? new CacheManagerImpl($cacheConfiguration) : new CacheManagerImplDryMode($cacheConfiguration);
    }
}
