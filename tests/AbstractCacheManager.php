<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;
use CacheMultiLayer\Service\CacheConfiguration;
use CacheMultiLayer\Service\CacheManager;
use CacheMultiLayer\Tests\Entity\Foo;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * Abstract class for unit testing a cache manager containing the tests that every class implementing a cache manager must undergo.
 * For each cache manager implemented, a specific class will be created that extends this one.
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
class AbstractCacheManager extends TestCase
{

    private ?CacheManager $cacheManager = null;
    private ?Foo $foo = null;

    final public function setCacheManager(?CacheManager $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }

    final public function getCacheManager(): ?CacheManager
    {
        return $this->cacheManager;
    }

    #[Override]
    protected function setUp(): void
    {
        $this->foo = (new Foo())
                ->setX(1)
                ->setY('bar')
                ->setZ([1, 2, 3, 'pino'])
                ->setFoo((new Foo())
                        ->setX(3)
                        ->setY('bar3')
                        ->setZ([3, null])
                        ->setFoo(null)
                )
        ;
    }

    public function testInteger(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->assertEquals($val, $x);
    }

    public function testFloat(): void
    {
        $x = 8.3;
        $key = 'test_float';
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->assertEquals($val, $x);
    }

    public function testString(): void
    {
        $x = 'foobar';
        $key = 'test_string';
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->assertEquals($val, $x);
    }

    public function testArray(): void
    {
        $x = [1, 2, 3];
        $key = 'test_array';
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->assertEquals($val, $x);
    }

    public function testClass(): void
    {
        $key = 'test_class';
        $res = $this->cacheManager->set($key, $this->foo);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->assertTrue($this->foo->equals($val));
    }

    public function testExpireTtl(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->cacheManager->set($key, $x, 2);
        $this->assertTrue($res);
        sleep(5);
        $val = $this->cacheManager->get($key);
        $this->assertNull($val);
    }

    public function testIncrDecr(): void
    {
        $key = 'test_incr';
        foreach ($this->cacheManager->increment($key) as $value) {
            $this->assertEquals(1, $value);
        }

        foreach ($this->cacheManager->increment($key) as $value) {
            $this->assertEquals(2, $value);
        }

        foreach ($this->cacheManager->increment($key) as $value) {
            $this->assertEquals(3, $value);
        }

        foreach ($this->cacheManager->decrement($key) as $value) {
            $this->assertEquals(2, $value);
        }

        foreach ($this->cacheManager->decrement($key) as $value) {
            $this->assertEquals(1, $value);
        }
    }

    public function testClear(): void
    {
        $key = 'test_clear';
        $x = 1;
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $resClear = $this->cacheManager->clear($key);
        $this->assertTrue($resClear);
        $val = $this->cacheManager->get($key);
        $this->assertNull($val);
    }

    public function testClearAllCache(): void
    {
        $key = 'test_clear';
        $key2 = 'test_clear2';
        $x = 1;
        $res = $this->cacheManager->set($key, $x);
        $res2 = $this->cacheManager->set($key2, $x);
        $this->assertTrue($res);
        $this->assertTrue($res2);
        $resClear = $this->cacheManager->clearAllCache();
        $this->assertTrue($resClear);
        $val = $this->cacheManager->get($key);
        $this->assertNull($val);
        $val2 = $this->cacheManager->get($key2);
        $this->assertNull($val2);
    }

    public function testEmptyCache(): void
    {
        $key = 'foo';
        $val = 'bar';
        $res = CacheManager::factory()->set($key, $val);
        $this->assertFalse($res);
    }

    public function testDuplicateCache(): void
    {
        $cc = CacheManager::factory();
        $appendTrue = $cc->appendCache(Cache::factory(CacheEnum::APCU, 10));
        $appendFalse = $cc->appendCache(Cache::factory(CacheEnum::APCU, 10));
        $this->assertTrue($appendTrue);
        $this->assertFalse($appendFalse);
    }

    public function testDuplicateConfig(): void
    {
        $cc = new CacheConfiguration();
        $appendTrue = $cc->appendCacheLevel(CacheEnum::APCU, 10);
        $appendFalse = $cc->appendCacheLevel(CacheEnum::APCU, 10);
        $this->assertTrue($appendTrue);
        $this->assertFalse($appendFalse);
    }

    public function testArrayDepth(): void
    {
        $x = [1, 2, 3, null, [
                1, 2, 3, null, [
                    1, 2, 3, null,
                ],
            ],
        ];
        $key = 'test_array_depth_manager';
        $res = $this->cacheManager->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cacheManager->get($key);
        $this->testRecursiveArray($x, $val);
    }

    public function testEmptyIncrement(): void
    {
        $key = 'test_empty_increment_manager';
        $expected = 1;
        $resultSet = $this->cacheManager->increment($key);
        foreach ($resultSet as $cacheKey => $actual) {
            $this->assertEquals($expected, $actual, "cache current " . $cacheKey);
        }
    }

    public function testEmptyDecrement(): void
    {
        $key = 'test_empty_decrement_manager';
        $expected = -1;
        $resultSet = $this->cacheManager->decrement($key);
        foreach ($resultSet as $cacheKey => $actual) {
            $this->assertEquals($expected, $actual, "cache current " . $cacheKey);
        }
    }

    public function testFailStringIncrement(): void
    {
        $key = 'test_fail_increment_manager';
        $value = 'foo';
        $this->cacheManager->set($key, $value);
        $resultSet = $this->cacheManager->increment($key);
        foreach ($resultSet as $cacheKey => $actual) {
            $this->assertFalse($actual, "cache current " . $cacheKey);
        }
    }

    public function testFailStringDecrement(): void
    {
        $key = 'test_fail_decrement_manager';
        $value = 'foo';
        $this->cacheManager->set($key, $value);
        $resultSet = $this->cacheManager->decrement($key);
        foreach ($resultSet as $cacheKey => $actual) {
            $this->assertFalse($actual, "cache current " . $cacheKey);
        }
    }

    public function testRemainingTTL(): void
    {
        $key = 'test_remaining_ttl_manager';
        $val = 1;
        $ttl = 10;
        $res = $this->cacheManager->set($key, $val, $ttl);
        $this->assertTrue($res);
        sleep(2);
        $resultSet = $this->cacheManager->getRemainingTTL($key);
        foreach ($resultSet as $cacheKey => $actual) {
            $this->assertNotNull($actual, "cache current " . $cacheKey);
            $this->assertLessThan(60, $actual, "cache current " . $cacheKey);
        }
    }

    private function testRecursiveArray(array $actual, array $expected): void
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            if (is_array($value)) {
                $this->testRecursiveArray($value, $actual[$key]);
            } else {
                $this->assertEquals($value, $actual[$key]);
            }
        }
    }
}
