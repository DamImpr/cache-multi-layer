<?php

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;


require_once '../vendor/autoload.php';


$memcache = Cache::factory(CacheEnum::MEMCACHE, 60,['server_address' => 'memcache-server', 'port' => 11211]);


var_dump($memcache->increment('chiave di prova'));