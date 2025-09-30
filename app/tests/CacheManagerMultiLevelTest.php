<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;
use Override;

/**
 * Description of CacheManagerTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheManagerMultiLevelTest extends AbstractCacheManager
{

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCacheManager(CacheManager::factory(self::getConfig()));
    }

    #[Override]
    public function testArray(): void
    {
        parent::testArray();
    }

    #[Override]
    public function testClass(): void
    {
        parent::testClass();
    }

    #[Override]
    public function testClear(): void
    {
        parent::testClear();
    }

    #[Override]
    public function testClearAllCache(): void
    {
        parent::testClearAllCache();
    }

    #[Override]
    public function testExpireTtl(): void
    {
        parent::testExpireTtl();
    }

    #[Override]
    public function testFloat(): void
    {
        parent::testFloat();
    }

    #[Override]
    public function testIncrDecr(): void
    {
        parent::testIncrDecr();
    }

    #[Override]
    public function testInteger(): void
    {
        parent::testInteger();
    }

    #[Override]
    public function testString(): void
    {
        parent::testString();
    }

    public function testUpdateHighestLevels(): void
    {
        $key = 'test_update_highest';
        $val = 10;
        $apcuCache = Cache::factory(CacheEnum::APCU, 3);
        $redisCache = Cache::factory(CacheEnum::REDIS, 65, ['server_address' => 'redis-server', 'port' => 6379]);
        $cc = CacheManager::factory();
        $cc->appendCache($apcuCache);
        $cc->appendCache($redisCache);
        $cc->set($key, $val);
        sleep(7);
        $this->assertNull($apcuCache->get($key));
        $actual = $cc->get($key);
        $this->assertEquals($val, $actual);
        $this->assertEquals($val,$apcuCache->get($key));
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        CacheManager::factory(self::getConfig())->clearAllCache();
    }

    private static function getConfig(): CacheConfiguration
    {
        $cc = new CacheConfiguration();
        $cc->appendCacheLevel(CacheEnum::APCU, 10);
        $cc->appendCacheLevel(CacheEnum::REDIS, 65, ['server_address' => 'redis-server', 'port' => 6379]);
        return $cc;
    }
}
