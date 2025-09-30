<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Service\CacheManager;
use CacheMultiLayer\Tests\Entity\Foo;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * Description of AbstractCacheManager
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class AbstractCacheManager extends TestCase
{

    private ?CacheManager $cacheManager = null;

    private ?Foo $foo = null;

    public final function setCacheManager(?CacheManager $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }

    public final function getCacheManager(): ?CacheManager
    {
        return $this->cacheManager;
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
        $x = "foobar";
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
        foreach( $this->cacheManager->increment($key) as  $value){
            $this->assertEquals(1, $value);
        }

        foreach( $this->cacheManager->increment($key) as  $value){
            $this->assertEquals(2, $value);
        }

        foreach( $this->cacheManager->increment($key) as  $value){
            $this->assertEquals(3, $value);
        }

        foreach( $this->cacheManager->decrement($key) as  $value){
            $this->assertEquals(2, $value);
        }

        foreach( $this->cacheManager->decrement($key) as  $value){
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
    
    
}
