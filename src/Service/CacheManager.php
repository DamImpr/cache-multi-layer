<?php

namespace App\Service\Cache;

use InvalidArgumentException;

/**
 * 
 * Classe gestore delle cache, si occupa di Salvare e Leggere i dati nei vari sistemi di cache.
 * La ricerca viene fatta partendo dal primo livello di cache, e in caso di fallimento viene fatta la ricerca
 * al prossimo livello. Quando un determinato livello di cache restituisce dati, vengono aggiornate tutti i livelli di cache superiori che hanno restuito un fallimento nella ricerca.
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class CacheManager {

    /**
     * Costruttore della classe che deve necessariamente avere una configurazione in ingresso dei sistemi di cache da utilizzare.
     * @param CacheConfiguration $configuration la configurazione dei sistemi di cache
     * @see CacheConfiguration
     */
    public abstract function __construct(CacheConfiguration $configuration);

    /**
     * Metodo che prende in ingresso un'instanza di Cacheable e la chiave, e popola l'oggetto con i dati in cache,
     * alla fine dell'esecuzione del metodo, se viene restitutio true l'oggetto passato in ingresso sarà popolato con tutti i dati presenti in tutti i livelli cache
     * @param Cacheable $object l'oggetto da popolare
     * @param string $key la chiave utilizzata per il salvataggio  in cache
     * @return bool true se la chiave è stata trovata e l'oggetto popolato, false se la chiave non è associata a nessun dato
     * @see Cacheable
     */
    public abstract function fetchObject(Cacheable $object, string $key): bool;

    /**
     * metodo che prende in ingresso un'istanza di Cacheable, la chiave, ed un ttl, e salva i dati in tutti i livelli di cache associando la chiave
     * per un tempo pari a ttl secondi. 
     * Se alla chiave passata in ingresso erano già presenti dati associati, quest'ultimi verranno sovrascritti
     * @param Cacheable $object oggetto da salvare in cache
     * @param string $key chiave da utilizzare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nella configurazione
     * @return void
     * @see Cacheable
     */
    public abstract function setObject(Cacheable $object, string $key, ?int $ttl = null): void;

    /**
     * Metodo che memorizza in cache una collezione di oggetti passata come array, persistendo tutti i dati serializzati degli ogetti della collezione con la chiave associata 
     * per ttl secondi 
     * Se alla chiave passata in ingresso erano già presenti dati associati, quest'ultimi verranno sovrascritti
     * @param array $collection la collezione da persistere
     * @param string $key la chiave da associare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nella configurazione
     * @return void
     * @throws \InvalidArgumentException se uno degli oggetti in collezione non implementa l'interfaccia Cacheable
     * @return void
     * @see Cacheable
     * @see \InvalidArgumentException
     */
    public abstract function setCollectionObject(array $collection, string $key, ?int $ttl = null): void;

    /**
     * Metodo che restituisce una collezione di oggetti associati alla chiave passata in ingresso.
     * Il tipo d'istanza degli oggetti deve essere passato in ingresso tramite la variabile $class.
     * Il metodo restituisce un array di oggetti nel caso siano presenti dati  associati alla chiave, oppure null in caso contrario.
     * @param string $class Il tipo di classe degli oggetti da collezionare e restituire
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?array array di oggetti in caso di successo, null altrimenti
     * @throws \InvalidArgumentException se il tipo $class non implementa Cacheable
     * @see Cacheable
     */
    public abstract function getCollectionObject(string $class, string $key): ?array;

    /**
     * Metodo che memorizza in cache una collezione di dati primitivi passati come array in tutti i livelli di cache, persistendo chiavi dell'array e valori così come passati.
     * @param array $collection la collezione da memorizzare in cache
     * @param string $key la chiave da associare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nella configurazione
     * @return void
     * @throws InvalidArgumentException se uno degli oggetti in collezione non è un tipo primitivo
     */
    public abstract function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void;

    /**
     * Metodo che restituisce una collezione di tipi primitivi associati alla chiave passata in ingresso, settando l'array con chiavi e valore come letti.
     * Il metodo restituisce un array nel caso siano presenti dati  associati alla chiave, oppure null altrimenti
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?array array di oggetti in caso di successo, null altrimenti
     */
    public abstract function getCollectionPrimitive(string $key): ?array;

    /**
     * Metodo che memorizza in cache una variabile di tipo intero.
     * @param int $var la variabile intera da memorizzare
     * @param string $key la chiave da associare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @return void
     */
    public abstract function setInteger(int $var, string $key, ?int $ttl = null): void;

    /**
     * Metodo che memorizza in cache una variabile di tipo float.
     * @param float $var la variabile float da memorizzare
     * @param string $key la chiave da associare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @return void
     */
    public abstract function setFloat(float $var, string $key, ?int $ttl = null): void;

    /**
     * Metodo che memorizza in cache una variabile di tipo stringa.
     * @param string $var la stringa da memorizzare
     * @param string $key la chiave da associare
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @return void
     */
    public abstract function setString(string $var, string $key, ?int $ttl = null): void;

    /**
     * metodo che restituisce una variabile intera memorizzata in cache associata alla chiave passata in ingresso,
     * oppure null se alla chiave non è associato nulla
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?int la variabile intera in caso di successo della ricerca, null altrimenti
     */
    public abstract function getInteger(string $key): ?int;

    /**
     * metodo che restituisce una variabile float memorizzata in cache associata alla chiave passata in ingresso,
     * oppure null se alla chiave non è associato nulla
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?float la variabile float in caso di successo della ricerca, null altrimenti
     */
    public abstract function getFloat(string $key): ?float;

    /**
     * metodo che restituisce una stringa memorizzata in cache associata alla chiave passata in ingresso,
     * oppure null se alla chiave non è associato nulla
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?string la stringa in caso di successo della ricerca, null altrimenti
     */
    public abstract function getString(string $key): ?string;

    /**
     * Metodo che cancella il valore di una chiave da tutti i livelli di cache.
     * @param string $key la chiave il cui il valore deve essere cancellato
     * @return bool true se è avvenuta una cancellazione ad almenu un livello, false altrimenti
     */
    public abstract function clear(string $key): bool;
    
    /**
     * Restituisce un array che ha come chiave l'enumerazione della cache e come valore il nuovo intero incrementato.
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkIncrementToExpire limite valore massimo per aggiornare il ttl.
     * @return array collezione avente come chiave l'enumerazione della cache e come valore il nuovo intero associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function increment(string $key, ?int $ttl = null,int $checkIncrementToExpire = 1) : array;

    /**
     * Metodo che cancella tutte i valori contenuti in tutti i livelli di cache, ove è permesso
     * @return bool always true
     * 
     */
    public abstract function clearAllCache(): bool;
    
    /**
     * retistuisce un array con tutti i ttl della chiave passata in ingresso indicizzati per enumerazione
     * @param string $key la chiave di cui si vuole conoscere i ttl
     * @return array collezione avente come chiave l'enumerazione della cache e come valore il ttl, oppure -1 se non è trovato nessun ttl
     */
    public abstract function getRemainingTTL(string $key): array;

    /**
     * Metodo di factory, restituisce Il Sistema di cache in modalità Dev o in modalità Prod in base alla flag settata nell'enviroment di symfony
     * @param ?CacheConfiguration $cc Configurazione della cache da adottare.
     * @return CacheManager istanza della classe che gestisce la cache
     */
    public static function factory(?CacheConfiguration $cc = null): CacheManager {
        if ($cc === null) {
            $cc = CacheConfiguration::defaultConfiguration();
        }
        return ((int) ($_SERVER['CACHE_ENABLE'])) === 1 ? new CacheManagerProd($cc) : new CacheManagerDev($cc);
    }

}
