<?php

namespace App\Service\Cache;

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
     * Array che tiene traccia della priorità impostata al singolo livello di cache.
     * la chiave dell'array è la priorità, il valore dell'array è l'enumerazione
     * @see CacheEnum
     */
    private array $priority  = [];
    
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
     * @param int $enum l'enumerazione usata per indicare il sistema di cache, tramite l'enumerazione conservata nella classe CacheEnum
     * @param int $ttl Time to live espresso in secondi
     * @return void
     * @see CacheEnum
     * @throws InvalidArgumentException nel caso sia già stato settato il sistema di cache passato
     */
    public function appendCacheLevel(int $enum, int $ttl): void {
        $this->check($enum);
        $this->configuration[$this->currentLevel] = $this->factoryCache($enum, $ttl);
        $this->priority[$this->currentLevel] = $enum;
        $this->currentLevel++;
        $this->setted[$enum] = true;
    }
    
    

    /**
     * Configurazione di default messa con 
     * 1° livello : APCU ttl = 30 min
     * 2° livello : Redis ttl = 2 h
     * @return CacheConfiguration
     */
    public static function defaultConfiguration(): CacheConfiguration {
        $cc = new CacheConfiguration();
        $cc->appendCacheLevel(CacheEnum::APCU, $_SERVER['APCU_TTL']);
        $cc->appendCacheLevel(CacheEnum::REDIS, $_SERVER['REDIS_TTL']);
        return $cc;
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
     * restituisce la lista della cache ordinate per priorità, dove partendo da zero si ha il primo livello e con lo spostarsi nelle celle dell'array a destra i livelli successivi.
     * @return array la configurazione 
     * @see Cache
     */
    public function getPriorityList(): array{
        return $this->priority;
    }
    
    public function factoryCache(int $enum,int $ttl) : Cache {
        return Cache::factory($enum, $ttl);
    }
    
    /**
     * Funzione per il controllo della cache duplicata, nel caso di duplicazione lancia \InvalidArgumentException
     * @param int $enum Enumerazione da controllare
     * @return void
     * @throws InvalidArgumentException nel caso di duplicazione
     */
    private function check(int $enum) : void{
        if (array_key_exists($enum, $this->setted)) {
            throw new InvalidArgumentException("cache already exists");
        }
    }
}
