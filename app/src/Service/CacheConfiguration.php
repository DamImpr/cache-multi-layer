<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use InvalidArgumentException;

/**
 * 
 * Classe che rappresenta la configurazione dei livelli di cache che viene
 * utilizzata poi da CacheManager per la gestione dei dati nei vari livelli.
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheConfiguration {

    /**
     * configurazione della cache.
     * La priorità è basta sulla posizione dell'oggetto nell'array, dove la posizione 0 è la priorità più alta
     */
    private array $configuration = [];

   
    /**
     * livello attuale della cache settata, utilizzata durante l'append delle cache.
     */
    private int $currentLevel = 0;

    /**
     * Array utilizzato per tracciare le cache già settate e controllare che non ci siano diversi livelli della stessa cache
     */
    private array $setted = [];

    /**
     * metodo che setta il livello successivo di cache
     * @param CacheEnum $cacheEnum l'enumerazione usata per indicare il sistema di cache, tramite l'enumerazione conservata nella classe CacheEnum
     * @param int $ttl Time to live espresso in secondi
     * @see CacheEnum
     * @throws InvalidArgumentException nel caso sia già stato settato il sistema di cache passato
     */
    public function appendCacheLevel(CacheEnum $cacheEnum, int $ttl, array $configuration = []): bool {
        if (array_key_exists($cacheEnum->value, $this->setted)) {
            return false;
        }

        $this->configuration[$this->currentLevel] = $this->factoryCache($cacheEnum, $ttl, $configuration);
        ++$this->currentLevel;
        $this->setted[$cacheEnum->value] = true;
        return true;
    }

    /**
     * restituisce la configurazione della cache, dove partendo da zero si ha il primo livello e con lo spostarsi nelle celle dell'array a destra i livelli successivi.
     * @return array la configurazione 
     * @see Cache
     */
    public function getConfiguration(): array {
        return $this->configuration;
    }

    

    /**
     *
     * @throws InvalidArgumentException
     */
    private function factoryCache(CacheEnum $cacheEnum, int $ttl, array $configuration): Cache {
        return Cache::factory($cacheEnum, $ttl, $configuration);
    }
}
