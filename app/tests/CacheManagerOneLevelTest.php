<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;
use Override;

/**
 * Description of CacheManagerTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheManagerOneLevelTest extends AbstractCacheManager
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
    
    private static function getConfig(): CacheConfiguration
    {
        $cacheConfiguration = new CacheConfiguration();
        $cacheConfiguration->appendCacheLevel(CacheEnum::APCU, 60);
        return $cacheConfiguration;
    }
}
