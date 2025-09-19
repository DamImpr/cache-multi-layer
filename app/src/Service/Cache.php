<?php

namespace App\Service;

use App\Enum\CacheEnum;
use App\Exception\CacheMissingConfigurationException;
use App\Exception\ClearCacheDeniedException;
use App\Interface\Cacheable;
use InvalidArgumentException;

/**
 * 
 * Classe di servizio che rappresenta un generico sistema di cache.
 * I metodi che offre consentono di salvare e recuperare un singolo oggetto oppure un intera collezione 
 * passata come array. Il salvataggio ed il recupero avviene attraverso la serializzazione e la deserializzazione di un oggetto, associando chiave e ttl in secondi che deve essere settato nella costruzione della classe.
 * 
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class Cache {

   

    /**
     * La costruzione di ogni cache prevede obbligatoriamente la specifica del "TimeToLive"
     * @param ?int $ttl time to live specificato in secondi
     * @throws InvalidArgumentException se il valore di ttl non è un numero positivo
     * @throws CacheMissingConfigurationException
     */
    public function __construct(int $ttl,array $configuration = []) {
        if ($ttl <= 0) {
            throw new InvalidArgumentException("ttl must be positive, not like your life");
        }
        $this->ttl = $ttl;
    }

    /**
     * getter della attributo $ttl
     * @return int
     */
    public function getTtl(): int {
        return $this->ttl;
    }

    /**
     * Metodo che prende in ingresso un'instanza di Cacheable e la chiave, e popola l'oggetto con i dati in cache,
     * alla fine dell'esecuzione del metodo, se viene restituito true l'oggetto passato in ingresso sarà popolato con tutti i dati presenti in cache
     * @param Cacheable $object l'oggetto da popolare
     * @param string $key la chiave utilizzata per il salvataggio  in cache
     * @return bool true se la chiave è stata trovata e l'oggetto popolato, false se la chiave non è associata a nessun dato
     * @see Cacheable
     */
    public abstract function fetchObject(Cacheable $object, string $key): bool;

    /**
     * metodo che prende in ingresso un'istanza di Cacheable, la chiave, ed un ttl, e salva i dati in cache associando la chiave
     * per un tempo pari a ttl secondi. 
     * Se alla chiave passata in ingresso erano già presenti dati associati, quest'ultimi verranno sovrascritti
     * @param Cacheable $object oggetto da salvare in cache
     * @param string $key chiave da utilizzare
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore
     * @return void
     * @see Cacheable
     */
    public abstract function setObject(Cacheable $object, string $key, ?int $ttl  = null ): void;

    /**
     * Metodo che memorizza in cache una collezione di oggetti passata come array, persistendo tutti i dati serializzati degli oggetti della collezione con la chiave associata 
     * per ttl secondi 
     * Se alla chiave passata in ingresso erano già presenti dati associati, quest'ultimi verranno sovrascritti
     * @param array $collection la collezione da persistere
     * @param string $key la chiave da associare
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore
     * @return void
     * @throws InvalidArgumentException se uno degli oggetti in collezione non implementa l'interfaccia Cacheable
     * @see Cacheable
     * @see InvalidArgumentException
     */
    public abstract function setCollectionObject(array $collection, string $key, ?int $ttl = null): void;

    /**
     * Metodo che restituisce una collezione di oggetti associati alla chiave passata in ingresso.
     * Il tipo d'istanza degli oggetti deve essere passato in ingresso tramite la variabile $class.
     * Il metodo restituisce un array di oggetti nel caso siano presenti dati  associati alla chiave, oppure null in caso contrario
     * @param string $class Il tipo di classe degli oggetti da collezionare e restituire
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?array array di oggetti in caso di successo, null altrimenti
     * @throws InvalidArgumentException se il tipo $class non implementa Cacheable
     * @see Cacheable
     */
    public abstract function getCollectionObject(string $class, string $key): ?array;

    /**
     * Metodo che memorizza in cache una collezione di dati primitivi passati come array, persistendo chiavi dell'array e valori così come passati.
     * @param array $collection la collezione da memorizzare in cache
     * @param string $key la chiave da associare
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @return void
     * @throws InvalidArgumentException se uno degli oggetti in collezione non è un tipo primitivo
     */
    public abstract function setCollectionPrimitive(array $collection, string $key, ?int $ttl = null): void;

    /**
     * Metodo che restituisce una collezione di tipi primitivi associati alla chiave passata in ingresso, settando l'array con chiavi e valore come letti.
     * 
     * Il metodo restituisce un array nel caso siano presenti dati  associati alla chiave, oppure null in caso contrario
     * @param string $key la chiave utilizzata per la ricerca in cache
     * @return ?array array di oggetti in caso di successo, null altrimenti
     */
    public abstract function getCollectionPrimitive(string $key): ?array;
    
    /**
     * Metodo che memorizza in cache una variabile di tipo intero.
     * @param int $var la variabile intera da memorizzare
     * @param string $key la chiave da associare
     * @param ?int $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @return void
     */
    public abstract function setInteger(int $var, string $key, ?int $ttl = null): void;

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
     * @return int il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int;
    
    
    /**
     * Restituisce il ttl associato alla chiave
     * @param string $key la chiave di cui si vuole conoscere il ttl
     * @return int il ttl in secondi,null se non settato
     * 
     */
    public abstract function getRemainingTTL(string $key): ?int;

    /**
     * Metodo che controlla se una variabile contiene un tipo primitivo ( stringa, intero, decimale o booleano )
     * @param int|string|bool|float|double $var variabile da controllare
     * @return void
     * @throws InvalidArgumentException l'eccezione che viene lanciata in caso di fallimento
     */
    public function assertPrimitive($var): void {        
        if (!is_string($var) && !is_int($var) && !is_bool($var) && !is_float($var) && !is_float($var)) {
            throw new InvalidArgumentException("valore non primitivio inatteso");
        }
    }

    /**
     * Metodo che controlla se un'istanza di qualunque oggetto implementa l'interfaccia Cacheable.
     * In caso di successo non fa niente, in caso di fallimento lanca un eccezione.
     * @param object $object l'oggetto sottoposto al controllo
     * @return void 
     * @throws InvalidArgumentException l'eccezione che viene lanciata in caso di fallimento.
     */
    protected function assertObject(object $object): void {
        if (!is_a($object, Cacheable::class)) {
            throw new InvalidArgumentException($object::class . ' does not implements Cacheable');
        }
    }

    /**
     * Metodo che controlla se una classe, passata come stringa, di qualunque oggetto implementa l'interfaccia Cacheable.
     * In caso di successo non fa niente, in caso di fallimento lanca un eccezione.
     * @param string $class  la classe sottoposta al controllo
     * @return void
     * @throws InvalidArgumentException l'eccezione che viene lanciata in caso di fallimento.
     */
    protected function assertClass(string $class): void {
        if (!in_array(Cacheable::class, class_implements($class))) {
            throw new InvalidArgumentException($class . ' does not implements Cacheable');
        }
    }

    /**
     * metodo che restituisce il ttl giusta da usare tra quello passato in ingresso e quello della classe
     * @param ?int $ttl ttl passato in ingresso
     * @return int ttl da utilizzare
     */
    protected function checkTTL(?int $ttl): int {
        return ($ttl ?? -1) < 0 ? $this->getTtl() : $ttl;
    }

    /**
     * Metodo di factory di un sistema di cache, dove attraverso un enumerazione
     * e il ttl da associare, restituisce la specifica classe di Cache.
     * Anche se specificata come intero, non passare numeri, ma usare le costanti di CacheEnum.
     * @param int $enum Enumerazione da passare
     * @param ?int $ttl ttl della cache
     * @return Cache Sistema di cache associato all'enumerazione
     * @throws InvalidArgumentException Nel caso non ci sia nessun sistema di cache associato all'enumerazione passata in ingresso
     * @see CacheEnum
     */
    public static function factory(int $enum, int $ttl): Cache {
        return match ($enum) {
            CacheEnum::APCU => new ApcuCache($ttl),
            CacheEnum::REDIS => new RedisCache($ttl),
            default => throw new InvalidArgumentException("Cache not found"),
        };
    }
    
    
     /**
     * ttl in secondi da associare al sistema di cache
     */
    private readonly int $ttl;

}
