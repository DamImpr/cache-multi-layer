<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;
use Override;

/**
 * manager one level cache unit test class implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
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

    #[Override]
    public function testEmptyCache(): void
    {
        parent::testEmptyCache();
    }

    #[Override]
    public function testDuplicateCache(): void
    {
        parent::testDuplicateCache();
    }

    #[Override]
    public function testDuplicateConfig(): void
    {
        parent::testDuplicateConfig();
    }

    #[Override]
    public function testArrayDepth(): void
    {
        parent::testArrayDepth();
    }

    #[Override]
    public function testEmptyDecrement(): void
    {
        parent::testEmptyDecrement();
    }

    #[Override]
    public function testEmptyIncrement(): void
    {
        parent::testEmptyIncrement();
    }

    #[Override]
    public function testFailStringDecrement(): void
    {
        parent::testFailStringDecrement();
    }

    #[Override]
    public function testFailStringIncrement(): void
    {
        parent::testFailStringIncrement();
    }

    #[Override]
    public function testRemainingTTL(): void
    {
        parent::testRemainingTTL();
    }
    

    private static function getConfig(): CacheConfiguration
    {
        $cacheConfiguration = new CacheConfiguration();
        $cacheConfiguration->appendCacheLevel(CacheEnum::APCU, 60);

        return $cacheConfiguration;
    }
}
