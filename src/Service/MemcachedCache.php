<?php

namespace CacheMultiLayer\Service;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Interface\Cacheable;
use Memcached;
use Override;

/**
 * 
 * @author Damiano Improta <code@damianoimprota.it> aka Drizella
 */
class MemcachedCache extends Cache
{
    private Memcached $memcached;

    protected function __construct(int $ttl, array $configuration = [])
    {
        parent::__construct($ttl, $configuration);
        if (array_key_exists('instance', $configuration)) {
            $this->memcached = $configuration['instance'];
        } else {
            if (array_key_exists('persistent', $configuration) && $configuration['persistent']) {
                $this->memcached = new \Memcached($configuration['persistentId']);
            } else {
                $this->memcached->addServer($configuration['server_address'], $configuration['port'] ?? 11211);
            }
            $this->memcached->setOption(Memcached::OPT_COMPRESSION, array_key_exists('persistent', $configuration) && $configuration['persistent']);
        }
    }

    #[Override]
    public function clear(string $key): bool
    {
        return $this->memcached->delete($this->getEffectiveKey($key));
    }

    #[Override]
    public function clearAllCache(): bool
    {
        
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        
    }

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcached->get($this->getEffectiveKey($key));
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            return null;
        }

        $valDecoded = json_decode((string) $val, true);

        return is_array($valDecoded) ? $this->unserializeVal($valDecoded) : $valDecoded;
    }

    #[Override]
    public function getEnum(): CacheEnum
    {
        return CacheEnum::MEMCACHED;
    }

    #[Override]
    public function getRemainingTTL(string $key): ?int
    {
        
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        
    }

    #[Override]
    public function isConnected(): bool
    {
        
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        return  $this->memcached->set($this->getEffectiveKey($key), $data, $ttl) && $this->memcached->getResultCode() === Memcached::RES_STORED;
    }

    #[Override]
    protected function checkInstanceIsCorrect(object $instance): bool
    {
        return $instance instanceof Memcached;
    }

    #[Override]
    protected function getMandatoryConfig(): array
    {
        return [
            'server_address',
        ];
    }

    #[\Override]
    protected function assertConfig(array $configuration): void
    {
        parent::assertConfig($configuration);
        if (array_key_exists("persistent", $configuration) && $configuration['persistent']) {
            
        }
    }
}
