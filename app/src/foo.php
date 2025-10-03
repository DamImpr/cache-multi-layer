<?php

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

require '../vendor/autoload.php';

$cacheSamePrefix = Cache::factory(CacheEnum::APCU, 60, ['key_prefix' => 'pre_']);
$cacheOtherPrefix = Cache::factory(CacheEnum::APCU, 10, ['key_prefix' => 'other_']);


$cacheSamePrefix->set('mammt', 10);
