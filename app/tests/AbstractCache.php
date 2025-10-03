<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\Cache;
use CacheMultiLayer\Tests\Entity\Foo;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * Description of ApcuCacheTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
abstract class AbstractCache extends TestCase
{

    private ?Cache $cache = null;

    private ?Foo $foo = null;

    public final function setCache(?Cache $cache): void
    {
        $this->cache = $cache;
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        restore_error_handler();
    }

    #[Override]
    protected function setUp(): void
    {
        $this->foo = (new Foo())
                ->setX(1)
                ->setY("bar")
                ->setZ([1, 2, 3, "pino",])
                ->setFoo((new Foo())
                        ->setX(3)
                        ->setY("bar3")
                        ->setZ([3, null])
                        ->setFoo(null)
                )
        ;
    }

    public function testInteger(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cache->get($key);
        $this->assertEquals($val, $x);
    }

    public function testFloat(): void
    {
        $x = 8.3;
        $key = 'test_float';
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cache->get($key);
        $this->assertEquals($val, $x);
    }

    public function testString(): void
    {
        $x = "foobar";
        $key = 'test_string';
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cache->get($key);
        $this->assertEquals($val, $x);
    }

    public function testArray(): void
    {
        $x = [1, 2, 3];
        $key = 'test_array';
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->cache->get($key);
        $this->assertEquals($val, $x);
    }

    public function testClass(): void
    {
        $key = 'test_class';
        $res = $this->cache->set($key, $this->foo);
        $this->assertTrue($res);
        $val = $this->cache->get($key);
        $this->assertTrue($this->foo->equals($val));
    }

    public function testExpireTtl(): void
    {
        $x = 8;
        $key = 'test_integer';
        $res = $this->cache->set($key, $x, 2);
        $this->assertTrue($res);
        sleep(5);
        $val = $this->cache->get($key);
        $this->assertNull($val);
    }

    public function testIncrDecr(): void
    {
        $key = 'test_incr';
        $this->assertEquals(1, $this->cache->increment($key));
        $this->assertEquals(2, $this->cache->increment($key));
        $this->assertEquals(3, $this->cache->increment($key));
        $this->assertEquals(2, $this->cache->decrement($key));
        $this->assertEquals(1, $this->cache->decrement($key));
    }

    public function testClear(): void
    {
        $key = 'test_clear';
        $x = 1;
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        $resClear = $this->cache->clear($key);
        $this->assertTrue($resClear);
        $val = $this->cache->get($key);
        $this->assertNull($val);
    }

    public function testClearAllCache(): void
    {
        $key = 'test_clear';
        $key2 = 'test_clear2';
        $x = 1;
        $res = $this->cache->set($key, $x);
        $res2 = $this->cache->set($key2, $x);
        $this->assertTrue($res);
        $this->assertTrue($res2);
        $resClear = $this->cache->clearAllCache();
        $this->assertTrue($resClear);
        $val = $this->cache->get($key);
        $this->assertNull($val);
        $val2 = $this->cache->get($key2);
        $this->assertNull($val2);
    }

    public function testEmptyIncrement(): void
    {
        $key="test_empty_increment";
        $expected = 1;
        $actual = $this->cache->increment($key);
        $this->assertEquals($expected, $actual);
    }

    public function testEmptyDecrement(): void
    {
        $key="test_empty_decrement";
        $expected = -1;
        $actual = $this->cache->decrement($key);
        $this->assertEquals($expected, $actual);
    }

    public function testRemainingTTL(): void
    {
        $key = 'test_clear';
        $x = 1;
        $res = $this->cache->set($key, $x);
        $this->assertTrue($res);
        sleep(2);
        $ttl = $this->cache->getRemainingTTL($key);
        $this->assertNotNull($ttl);
        $this->assertLessThanOrEqual(60, $ttl);
    }

    public function testIsConnected(): void
    {
        $this->assertTrue($this->cache->isConnected());
    }

    public final function doTestRealEnum(CacheEnum $cacheEnum): void
    {
        $this->assertEquals($cacheEnum, $this->cache->getEnum());
    }

    public final function getCache(): ?Cache
    {
        return $this->cache;
    }
}
