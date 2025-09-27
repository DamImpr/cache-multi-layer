<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Service\RedisCache;
use Override;

/**
 * Description of ApcuCacheTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class RedisCacheTest extends AbstractCache {

    #[Override]
    protected function setUp(): void {
        parent::setUp();
        $this->setCache(new RedisCache(60, ['server_address' => 'redis-server', 'port' => 6379]));
    }

    #[Override]
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
    }

    #[Override]
    public function testArray(): void {
        parent::testArray();
    }

    #[Override]
    public function testClass(): void {
        parent::testClass();
    }

    #[Override]
    public function testClear(): void {
        parent::testClear();
    }

    #[Override]
    public function testClearAllCache(): void {
        parent::testClearAllCache();
    }

    #[Override]
    public function testExpireTtl(): void {
        parent::testExpireTtl();
    }

    #[Override]
    public function testFloat(): void {
        parent::testFloat();
    }

    #[Override]
    public function testIncrDecr(): void {
        parent::testIncrDecr();
    }

    #[Override]
    public function testInteger(): void {
        parent::testInteger();
    }

    #[Override]
    public function testString(): void {
        parent::testString();
    }
    
    #[\Override]
    public function testIsConnected(): void {
        parent::testIsConnected();
    }
    
    public function testMissingServer():void{
        $this->expectException(CacheMissingConfigurationException::class);
        (new RedisCache(1,['port' => 6397]));
    }
    
    public function testMissingPort():void{
        $this->expectException(CacheMissingConfigurationException::class);
        (new RedisCache(1,['server_address' => 'localhost']));
    }
    
    public function testConnectionNotFound():void{
        $isConnected = (new RedisCache(1,['server_address' => 'ip-no-redis','port' => 6397]))->isConnected();
        $this->assertFalse($isConnected);
    }
}
