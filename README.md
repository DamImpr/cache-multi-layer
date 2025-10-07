# cache multi layer
PHP library used for fast management of a single cache system, or management of multiple systems with priority levels.

The management of a single system works like a mask and allows transparent use of the cache by abstracting which one you want to use.

Multi-level management is conceived as a vertical hierarchy, where the highest priority is at the top, and lower priorities are gradually lower down.
Write operations are performed on all levels.
Read operations are performed by reading from the cache system with the highest priority, and if the data is not found, the next system is read. Whenever a read from a system is successful, the cache systems with higher priority that did not return any value are updated.

The currently implemented caches are:

- apcu
- redis
- memcache

## install

```bash
composer require damimpr/cache-multi-layer
```
## usage examples

### Single cache level

example with redis cache


```php

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;

/*
 * 
 * creates the redis instance located on the host “redis-server” 
 * with a default TTL of 60 seconds 
 */ 
$cache = Cache::factory(CacheEnum::REDIS, 60, ['server_address' => 'redis-server']);


// set new value
$x = 8;
$key = 'test_key';
$res = $cache->set($key, $x);

// get
$y = $cache->get($key); // the value of $y is 8
```

### Multi cache level

Example with apcu cache used with maximum priority and redis cache used with lower priority

```php
use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;

/*
 * creates the apcu instance as the highest priority cache and then
 * creates the redis instance located on the host ‘redis-server’ with a default TTL of 60 seconds with lower priority
 */
$cacheManager = CacheManager::factory();
$cacheManager->appendCache(Cache::factory(CacheEnum::APCU, 10));
$cacheManager->appendCache(Cache::factory(CacheEnum::REDIS, 65, ['server_address' => 'redis-server']));

/*
 * it's the same using cache configuration
 */
$cacheConfiguration = new CacheConfiguration();
$cacheConfiguration->appendCacheLevel(CacheEnum::APCU, 10);
$cacheConfiguration->appendCacheLevel(CacheEnum::REDIS, 65, ['server_address' => 'redis-server']);
$cacheManager = CacheManager::factory($cacheConfiguration);


// set new value
$x = 8;
$key = 'test_key';
$res = $cacheManager->set($key, $x);

sleep(15);


$y = $cacheManager->get($key); 
// the value of $y is 8 read from redis, and apcu, which had expired, has been refreshed
```

## contribuiting
