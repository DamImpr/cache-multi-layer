# Cache multi layer
PHP library used for fast management of a single cache system, or management of multiple systems with priority levels.

The management of a single system works like a mask and allows transparent use of the cache by abstracting which one you want to use.

Multi-level management is conceived as a vertical hierarchy, where the highest priority is at the top, and lower priorities are gradually lower down.
Write operations are performed on all levels.
Read operations are performed by reading from the cache system with the highest priority, and if the data is not found, the next system is read. Whenever a read from a system is successful, the cache systems with higher priority that did not return any value are updated.

The currently implemented caches are:

- Apcu
- Redis
- Memcache

## Install

```bash
composer require damimpr/cache-multi-layer
```
## Usage examples

### Single cache

example with redis cache


```php

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;

/*
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


// it's the same using cache configuration
 
$cacheConfiguration = new CacheConfiguration();
$cacheConfiguration->appendCacheLevel(CacheEnum::APCU, 10);
$cacheConfiguration->appendCacheLevel(CacheEnum::REDIS, 65, ['server_address' => 'redis-server']);
$cacheManager = CacheManager::factory($cacheConfiguration);


// set new value
$x = 8;
$key = 'test_key';
$res = $cacheManager->set($key, $x);

//wait 15 seconds
sleep(15); 


$y = $cacheManager->get($key); 
// the value of $y is 8 read from redis, and apcu, which had expired, has been refreshed
```

## Contribuiting

If you would like to contribute to this library, there is a [docker](https://docs.docker.com/engine/install/) you can use in development.

The docker image has been developed to execute commands, which are executable through the [commands](commands) file, and are listed below:
- ```bash
  bash commands test-sw # Running the phpunit test suite
  ```
- ```bash
  bash commands update-vendor # Update composer packages
  ```
- ```bash
  bash commands php-cs-fixer # Format the code according to the PSR-12 standard using php-cs-fixer
  ```
- ```bash
  bash commands rector # Run powerful php tool useful for refactoring
  ```
- ```bash
  bash commands phpstan # Run powerful php tool useful finding bugs 
  ```
- ```bash
  bash commands sh # Run the sh shell in the container
  ```
## Credits
Powerful tools used:
-  [php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) for standard PSR-12 code formatting
-  [rector](https://github.com/rectorphp/rector) for code refactoring
-  [phpstan](https://phpstan.org/) for finding bugs