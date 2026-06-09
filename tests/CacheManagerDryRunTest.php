<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;
use CacheMultiLayer\Tests\Entity\Foo;

/**
 * dry run manager unit test class implementation.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class CacheManagerDryRunTest extends AbstractCacheManager
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCacheManager(CacheManager::factory(self::getConfig(), true));
    }

    #[\Override]
    public function testInteger(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testFloat(): void
    {
        $x = 8.3;
        $key = 'test_float';
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testString(): void
    {
        $x = 'foobar';
        $key = 'test_string';
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testArray(): void
    {
        $x = [1, 2, 3];
        $key = 'test_array';
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testClass(): void
    {
        $key = 'test_class';
        $res = $this->getCacheManager()->set($key, new Foo());
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testExpireTtl(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->getCacheManager()->set($key, $x, 2);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testIncrDecr(): void
    {
        $key = 'test_incr';
        $this->assertEquals([], $this->getCacheManager()->increment($key));
        $this->assertEquals([], $this->getCacheManager()->decrement($key));
    }

    #[\Override]
    public function testClear(): void
    {
        $key = 'test_clear';
        $x = 1;
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $resClear = $this->getCacheManager()->clear($key);
        $this->assertTrue($resClear);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
    }

    #[\Override]
    public function testClearAllCache(): void
    {
        $key = 'test_clear';
        $key2 = 'test_clear2';
        $x = 1;
        $res = $this->getCacheManager()->set($key, $x);
        $res2 = $this->getCacheManager()->set($key2, $x);
        $this->assertTrue($res);
        $this->assertTrue($res2);
        $resClear = $this->getCacheManager()->clearAllCache();
        $this->assertTrue($resClear);
        $val = $this->getCacheManager()->get($key);
        $this->assertNull($val);
        $val2 = $this->getCacheManager()->get($key2);
        $this->assertNull($val2);
    }

    #[\Override]
    public function testArrayDepth(): void
    {
        $x = [1, 2, 3, null, [
            1, 2, 3, null, [
                1, 2, 3, null,
            ],
        ],
        ];
        $key = 'test_array_depth';
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        $val = $this->getCacheManager()->get($key);
        $this->assertEmpty($val);
    }

    #[\Override]
    public function testDuplicateCache(): void
    {
        parent::testDuplicateCache();
    }

    #[\Override]
    public function testDuplicateConfig(): void
    {
        parent::testDuplicateConfig();
    }

    #[\Override]
    public function testEmptyCache(): void
    {
        parent::testEmptyCache();
    }

    #[\Override]
    public function testEmptyIncrement(): void
    {
        $key = 'test_empty_increment';
        $resultSet = $this->getCacheManager()->increment($key);
        $this->assertEmpty($resultSet);
    }

    #[\Override]
    public function testEmptyDecrement(): void
    {
        $key = 'test_empty_decrement';
        $resultSet = $this->getCacheManager()->decrement($key);
        $this->assertEmpty($resultSet);
    }

    #[\Override]
    public function testFailStringIncrement(): void
    {
        $key = 'test_fail_increment';
        $value = 'foo';
        $this->getCacheManager()->set($key, $value);
        $resultSet = $this->getCacheManager()->increment($key);
        $this->assertEmpty($resultSet);
    }

    #[\Override]
    public function testFailStringDecrement(): void
    {
        $key = 'test_fail_decrement';
        $value = 'foo';
        $this->getCacheManager()->set($key, $value);
        $resultSet = $this->getCacheManager()->decrement($key);
        $this->assertEmpty($resultSet);
    }

    #[\Override]
    public function testRemainingTTL(): void
    {
        $key = 'test_remaining_ttl';
        $x = 1;
        $res = $this->getCacheManager()->set($key, $x);
        $this->assertTrue($res);
        sleep(2);
        $resultSet = $this->getCacheManager()->getRemainingTTL($key);
        $this->assertEmpty($resultSet);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        CacheManager::factory(self::getConfig())->clearAllCache();
    }

    private static function getConfig(): CacheConfiguration
    {
        $cacheConfiguration = new CacheConfiguration();
        $cacheConfiguration->appendCacheLevel(CacheEnum::APCU, 10);
        $cacheConfiguration->appendCacheLevel(CacheEnum::REDIS, 65, ['server_address' => 'redis-server', 'port' => 6379]);

        return $cacheConfiguration;
    }
}
