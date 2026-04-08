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
                $this->memcached = new \Memcached();
                $this->memcached->addServer($configuration['server_address'], $configuration['port'] ?? 11211);
            }
            $this->memcached->setOption(Memcached::OPT_COMPRESSION, $configuration['compress'] ?? false);
            $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, $configuration['opt_binary_protocol'] ?? false);
        }
    }

    #[Override]
    public function clear(string $key): bool
    {
        return $this->memcached->delete($this->getEffectiveKey($key)) && $this->memcached->delete($this->getKeyTtl($key));
    }

    #[Override]
    public function clearAllCache(): bool
    {
        return $this->memcached->flush();
    }

    #[Override]
    public function decrement(string $key, ?int $ttl = null): int|false
    {
        $ttlToUse = $this->getTtlToUse($ttl);
        return $this->doIncrementDecrement($key, -1, $ttlToUse);
    }

    #[Override]
    public function get(string $key): int|float|string|Cacheable|array|null
    {
        $val = $this->memcached->get($this->getEffectiveKey($key));
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            return null;
        }
        $valDecoded = json_decode((string) $val, true);
        if (false === $valDecoded && json_last_error() !== JSON_ERROR_NONE) {
            $valDecoded = $val;
        }
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
        $expires = $this->memcached->get($this->getKeyTtl($this->getEffectiveKey($key)));
        if ($expires === null || $expires === 0) {
            return $expires;
        }
        $remaining = $expires - time();
        return ($remaining > 0) ? $remaining : null;
    }

    #[Override]
    public function increment(string $key, ?int $ttl = null): int|false
    {
        $ttlToUse = $this->getTtlToUse($ttl);
        return $this->doIncrementDecrement($key, 1, $ttlToUse);
    }

    #[Override]
    public function isConnected(): bool
    {
        $version = $this->memcached->getVersion();
        return $version !== false && !in_array("0.0.0", $version);
    }

    #[Override]
    public function set(string $key, int|float|string|Cacheable|array $val, ?int $ttl = null): bool
    {
        $keyVal = $this->getEffectiveKey($key);
        $keyTtl = $this->getKeyTtl($key);
        $ttlToUse = $this->getTtlToUse($ttl);
        $exipresAt = $ttlToUse > 0 ? time() + $ttlToUse : 0;
        $data = is_array($val) ? $this->serializeValArray($val) : $this->serializeVal($val);
        $dataStore = is_array($data) ? json_encode($data) : $data;
        return $this->memcached->set($keyVal, $dataStore, $ttlToUse) && $this->memcached->getResultCode() === Memcached::RES_SUCCESS && $this->memcached->set($keyTtl, $exipresAt, $ttlToUse) && $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
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
        if (($configuration['persistent'] ?? false) && !($configuration['persistentId'] ?? false)) {
            throw new CacheMissingConfigurationException('memcached persistent needs persistentId');
        }
    }

    private function getKeyTtl(string $key): string
    {
        return $key . ":expires";
    }

    private function doIncrementDecrement(string $key, int $step, int $expire): int|false
    {
        $retries = 5;
        $res = false;
        for ($i = 0; $i < $retries && $res === false; $i++) {
            $res = $this->handleCas($key, $step, $expire);
        }
        return $res;
    }

    private function handleCas(string $key, int $step, int $expire): int|false
    {
        $result = $this->memcached->get(key: $key, get_flags: Memcached::GET_EXTENDED);
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            if ($this->memcached->add($key, (string) $step, $expire)) {
                return $step;
            }
            return false;
        }
        $new = (int) $result['value'] + $step;
        if ($this->memcached->cas($result['cas'], $key, (string) $new)) {
            return $new;
        }
        return false;
    }
}
