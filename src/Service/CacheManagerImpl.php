<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Interface\Cacheable;

/**
 * Basic CacheManager implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class CacheManagerImpl extends CacheManager
{
    private array $caches = [];

    #[\Override]
    public function appendCache(Cache $cache): bool
    {
        if (!empty(array_filter($this->caches, fn (Cache $current): bool => $cache->getEnum() === $current->getEnum()))) {
            return false;
        }

        $this->caches[] = $cache;

        return true;
    }

    #[\Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $size = count($this->caches);
        if (0 === $size) {
            return false;
        }
        $res = [];
        for ($i = 0; $i < $size; ++$i) {
            $res[$i] = $this->caches[$i]->set($key, $val, $ttl);
        }

        return array_reduce($res, fn (bool $carry, bool $item) => $carry && $item, true);
    }

    #[\Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $data = null;
        $size = count($this->caches);
        for ($i = 0; $i < $size && null === $data; ++$i) {
            $data = $this->caches[$i]->get($key);
        }

        if (null === $data) {
            return null;
        }
        $ttlRemaining = $this->caches[$i - 1]->getRemainingTTL($key);
        for ($j = $i - 2; $j >= 0; --$j) {
            $ttl = min($this->caches[$j]->getTtl(), $ttlRemaining);
            $this->caches[$j]->set($key, $data, $ttl);
        }

        return $data;
    }

    #[\Override]
    public function clear(string $key): bool
    {
        $countDeleted = 0;
        $size = count($this->caches);
        for ($i = 0; $i < $size; ++$i) {
            $countDeleted += (int) $this->caches[$i]->clear($key);
        }

        return $countDeleted === $size;
    }

    #[\Override]
    public function clearAllCache(): bool
    {
        $countDeleted = 0;
        $size = count($this->caches);
        for ($i = 0; $i < $size; ++$i) {
            $countDeleted += (int) $this->caches[$i]->clearAllCache();
        }

        return $countDeleted === $size;
    }

    #[\Override]
    public function increment(string $key, ?int $ttl = null): array
    {
        $res = [];
        $size = count($this->caches);
        for ($i = 0; $i < $size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->increment($key, $ttl);
        }

        return $res;
    }

    #[\Override]
    public function getRemainingTTL(string $key): array
    {
        $res = [];
        $size = count($this->caches);
        for ($i = 0; $i < $size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->getRemainingTTL($key);
        }

        return $res;
    }

    #[\Override]
    public function decrement(string $key, ?int $ttl = null): array
    {
        $res = [];
        $size = count($this->caches);
        for ($i = 0; $i < $size; ++$i) {
            $res[$this->caches[$i]->getEnum()->name] = $this->caches[$i]->decrement($key, $ttl);
        }

        return $res;
    }

    protected function __construct(?CacheConfiguration $cacheConfiguration = null)
    {
        $this->caches = $cacheConfiguration?->getConfiguration() ?? [];
    }
}
