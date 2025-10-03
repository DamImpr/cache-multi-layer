<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Exception\ClearCacheDeniedException;
use CacheMultiLayer\Interface\Cacheable;
use Override;

/**

 * Sistema di cache in produzione, segue fedelmente la gerarchia di livelli passata nella configurazione
 * per la documentazione dei metodi, si rimanda alla classe astratta CacheManager
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 * @see AbstractCacheManager
 */
class CacheManagerImpl extends CacheManager
{
    /**
     * le caches lette dalla configurazione
     */
    private array $caches = [];

    /**
     * dimensione dell'array $caches, mantenuta qui per poter avere l'informazione in tempo costante O(1)
     */
    private int $size = 0;

    protected function __construct(?CacheConfiguration $cacheConfiguration = null)
    {
        $this->caches = $cacheConfiguration?->getConfiguration() ?? [];
        $this->size = count($this->caches);
    }

    #[\Override]
    public function appendCache(Cache $cache): bool
    {
        if (!empty(array_filter($this->caches, fn (Cache $current): bool => $cache->getEnum() === $current->getEnum()))) {
            return false;
        }

        $this->caches[$this->size++] = $cache;
        return true;
    }

    /**
     *
     */
    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        if ($this->size === 0) {
            return false;
        }

        for ($i = 0; $i < $this->size; ++$i) {
            $this->caches[$i]->set($key, $val, $ttl);
        }

        return true;
    }

    /**
     *
     */
    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $i = 0;
        $data = null;
        for ($i = 0; $i < $this->size && $data === null; ++$i) {
            $data = $this->caches[$i]->get($key);
        }

        if ($data === null) {
            return null;
        }

        for ($j = $i - 2; $j >= 0; --$j) {
            $this->caches[$j]->set($key, $data);
        }

        return $data;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $key): bool
    {
        $countDeleted = 0;
        for ($i = 0; $i < $this->size; ++$i) {
            $countDeleted += (int) $this->caches[$i]->clear($key);
        }

        return $countDeleted === $this->size;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function clearAllCache(): bool
    {
        $countDeleted = 0;
        for ($i = 0; $i < $this->size; ++$i) {
            try {
                $countDeleted += (int) $this->caches[$i]->clearAllCache();
            } catch (ClearCacheDeniedException) {
                //skip
            }
        }

        return $countDeleted === $this->size;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function increment(string $key, ?int $ttl = null): array
    {
        $res = [];
        for ($i = 0; $i < $this->size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->increment($key, $ttl);
        }

        return $res;
    }

    /**
     *
     * {@inheritDoc}
     */
    #[Override]
    public function getRemainingTTL(string $key): array
    {
        $res = [];
        for ($i = 0; $i < $this->size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->getRemainingTTL($key);
        }

        return $res;
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null): array
    {
        $res = [];
        for ($i = 0; $i < $this->size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->decrement($key, $ttl);
        }

        return $res;
    }
}
