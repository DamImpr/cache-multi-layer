<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Exception\ClearCacheDeniedException;
use CacheMultiLayer\Interface\Cacheable;
use Override;

/**

 * Sistema di cache in produzione, segue fedelmente la gerarchia di livelli passata nella configurazione
 * per la documentazione dei metodi, si rimanda alla classe astratta CacheManager
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see CacheManager
 */
class CacheManagerProd extends CacheManager {

    /**
     * le caches lette dalla configurazione
     */
    private array $caches = [];
    private array $priority = [];

    /**
     * dimensione dell'array $caches, mantenuta qui per poter avere l'informazione in tempo costante O(1)
     */
    private int $size = 0;

    public function __construct(CacheConfiguration $configuration) {
        $this->caches = $configuration->getConfiguration();
        $this->priority = $configuration->getPriorityList();
        $this->size = count($this->caches);
    }

    /**
     * 
     */
    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool {
        if ($this->size === 0) {
            return false;
        }
        for ($i = 0; $i < $this->size; $i++) {
            $this->caches[$i]->set($key, $val, $ttl);
        }
        return true;
    }

    /**
     * 
     */
    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === false; $i++) {
            $data = $this->caches[$i]->get($key);
        }
        if ($data === null) {
            return null;
        }
        for ($j = $i - 2; $j >= 0; $j--) {
            $this->caches[$i]->set($key, $data);
        }
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $key): bool {
        $res = false;
        for ($i = 0; $i < $this->size; $i++) {
            $tmp = $this->caches[$i]->clear($key);
            if ($tmp === true) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool {
        for ($i = 0; $i < $this->size; $i++) {
            try {
                $this->caches[$i]->clearAllCache();
            } catch (ClearCacheDeniedException) {
                //skip
            }
        }
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null, int $checkIncrementToExpire = 1): array {
        $res = [];
        for ($i = 0; $i < $this->size; $i++) {
            $res[$this->priority[$i]] = $this->caches[$i]->increment($key, $ttl, $checkIncrementToExpire);
        }
        return $res;
    }

    /**
     * 
     * {@inheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): array {
        $res = [];
        for ($i = 0; $i < $this->size; $i++) {
            $res[$this->priority[$i]] = $this->caches[$i]->getRemainingTTL($key);
        }
        return $res;
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null, int $checkDecrementToExpire = 1): array {
        $res = [];
        for ($i = 0; $i < $this->size; $i++) {
            $res[$this->priority[$i]] = $this->caches[$i]->decrement($key, $ttl, $checkDecrementToExpire);
        }
        return $res;
    }
}
