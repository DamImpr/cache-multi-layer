<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Exception\ClearCacheDeniedException;
use CacheMultiLayer\Interface\Cacheable;
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
     * @param int $ttl time to live specificato in secondi
     * @param array $configuration = []
     * @throws InvalidArgumentException se il valore di ttl non è un numero positivo
     * @throws CacheMissingConfigurationException
     */
    protected function __construct(int $ttl, array $configuration = []) {
        if ($ttl <= 0) {
            throw new InvalidArgumentException("ttl must be positive, not like your life");
        }

        $this->assertConfig($configuration);
        $this->ttl = $ttl;
    }

    /**
     * getter della attributo $ttl
     */
    public function getTtl(): int {
        return $this->ttl;
    }

    public abstract function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool;

    public abstract function get(string $key): int|float|string|Cacheable|array|null;

    public abstract function getRemainingTTL(string $key): ?int;

    /**
     * Metodo che cancella il valore di una chiave dalla cache.
     * @param string $key la chiave il cui il valore deve essere cancellato
     * @return bool true se è avvenuta una cancellazione, false altrimenti
     */
    public abstract function clear(string $key): bool;

    /**
     * Metodo che cancella tutte i valori contenuti nella cache, se il sistema di cache lo permette
     * @throws ClearCacheDeniedException se la possibilità di cancellare tutta la cache è negata
     */
    public abstract function clearAllCache(): bool;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param int|null $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkIncrementToExpire limite valore massimo per aggiornare il ttl.
     * @return int il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): int;

    /**
     * @param string $key chiave il cui valore intero deve essere incrementato.
     * @param int|null $ttl time to live specificato in secondi, nel caso non specificato, viene utilizzato quello passato nel costruttore.
     * @param int $checkDecrementToExpire limite valore massimo per aggiornare il ttl.
     * @return int il nuovo valore associato alla chiave, nel caso di fallimento restituisce il valore 0 
     */
    public abstract function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): int;

    public abstract function isConnected(): bool;
    
    public abstract function getEnum():CacheEnum;
    
    /**
     * Metodo di factory di un sistema di cache, dove attraverso un enumerazione
     * e il ttl da associare, restituisce la specifica classe di Cache.
     * Anche se specificata come intero, non passare numeri, ma usare le costanti di CacheEnum.
     * @param CacheEnum $cacheEnum Enumerazione da passare
     * @param int $ttl ttl della cache
     * @return Cache Sistema di cache associato all'enumerazione
     * @throws InvalidArgumentException Nel caso non ci sia nessun sistema di cache associato all'enumerazione passata in ingresso
     * @throws CacheMissingConfigurationException
     * @see CacheEnum
     */
    public static function factory(CacheEnum $cacheEnum, int $ttl, array $configuration = []): Cache {
        return match ($cacheEnum) {
            CacheEnum::APCU => new ApcuCache($ttl, $configuration),
            CacheEnum::REDIS => new RedisCache($ttl, $configuration)
        };
    }


    /**
     * metodo che restituisce il ttl giusta da usare tra quello passato in ingresso e quello della classe
     * @param ?int $ttl ttl passato in ingresso
     * @return int ttl da utilizzare
     */
    protected function getTtlToUse(?int $ttl): int {
        return ($ttl ?? -1) < 0 ? $this->ttl : $ttl;
    }

    protected function assertConfig(array $configuration): void {
        $mandatoryKeys = $this->getMandatoryConfig();
        if (!empty(array_diff_key($mandatoryKeys, array_keys($configuration)))) {
            throw new CacheMissingConfigurationException(implode(',', $mandatoryKeys) . " are mandatory configurations");
        }
    }
    
    protected abstract function getMandatoryConfig():array;
    
    
    protected final function serializeVal(int|float|string|Cacheable $val): int|float|string|array
    {
        if ($val instanceof Cacheable)
        {
            return ['__cacheable' => 1, '__class' => $val::class, '__data' => $val->serialize()];
        }

        return $val;
    }

    protected final function unserializeValArray(array $val): array|Cacheable
    {
        $res = [];
        if (array_key_exists('__cacheable', $val))
        {
            $res = new $val['__class']();
            $res->unserialize($val['__data']);
        } else
        {
            foreach ($val as $key => $value)
            {
                $res[$key] = is_array($value) ? $this->unserializeValArray($value) : $value;
            }
        }

        return $res;
    }

    protected final function serializeValArray(array $val): array
    {
        $res = [];
        foreach ($val as $key => $value)
        {
            $res[$key] = is_array($value) ? $this->serializeValArray($value) : $this->serializeVal($value);
        }

        return $res;
    }

    
    /**
     * ttl in secondi da associare al sistema di cache
     */
    private readonly int $ttl;
}
