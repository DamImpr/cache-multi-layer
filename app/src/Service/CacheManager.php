<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Exception\ClearCacheDeniedException;
use CacheMultiLayer\Interface\Cacheable;

/**
 * 
 * Classe gestore delle cache, si occupa di Salvare e Leggere i dati nei vari sistemi di cache.
 * La ricerca viene fatta partendo dal primo livello di cache, e in caso di fallimento viene fatta la ricerca
 * al prossimo livello. Quando un determinato livello di cache restituisce dati, vengono aggiornate tutti i livelli di cache superiori che hanno restuito un fallimento nella ricerca.
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class CacheManager {
    
    // TODO riscirvere la phpdoc, è un refuso

    /**
     * Costruttore della classe che deve necessariamente avere una configurazione in ingresso dei sistemi di cache da utilizzare.
     * @param CacheConfiguration $configuration la configurazione dei sistemi di cache
     * @see CacheConfiguration
     */
    protected abstract function __construct(?CacheConfiguration $configuration);
    
    
    
    public abstract function appendCache(Cache $cache):bool;
    
    /**
     * 
     */
    public abstract function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool;

    /**
     * 
     */
    public abstract function get(string $key): int|float|string|Cacheable|array|null;

    /**
     * 
     */
    public abstract function getRemainingTTL(string $key): array;

    /**
     * Metodo che cancella il valore di una chiave dalla cache.
     * @param string $key la chiave il cui il valore deve essere cancellato
     * @return bool true se è avvenuta una cancellazione, false altrimenti
     */
    public abstract function clear(string $key): bool;

    /**
     * Metodo che cancella tutte i valori contenuti nella cache, se il sistema di cache lo permette
     * @return bool always true
     * @throws ClearCacheDeniedException se la possibilità di cancellare tutta la cache è negata
     */
    public abstract function clearAllCache(): bool;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkIncrementToExpire limite valore massimo per aggiornare il ttl.
     * @return array il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkDecrementToExpire limite valore massimo per aggiornare il ttl.
     * @return array il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): array;

    /**
     * Metodo di factory, restituisce Il Sistema di cache in modalità Dev o in modalità Prod in base alla flag settata nell'enviroment di symfony
     * @param ?CacheConfiguration $cc Configurazione della cache da adottare.
     * @return CacheManager istanza della classe che gestisce la cache
     */
    public static function factory(?CacheConfiguration $cc = null, bool $dryMode = false): CacheManager {
        return !$dryMode  ? new CacheManagerImpl($cc) : new CacheManagerImplDryMode($cc);
}
}
