<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Service\ApcuCache;
use CacheMultiLayer\Tests\Entity\Foo;
use Override;
use PHPUnit\Framework\TestCase;
use TheSeer\Tokenizer\Exception;

/**
 * Description of ApcuCacheTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class ApcuCacheTest extends TestCase {

    private ?ApcuCache $apcuCache = null;
    private ?Foo $foo = null;
    #[Override]
    protected function setUp(): void {
        $this->apcuCache = new ApcuCache(60);
        $this->foo = new Foo(1, "bar", [1,"foo",new Foo(2, "bar2", [2,null], null)], new Foo(3, "bar3", [3,null], null));
    }

    #[Override]
    public static function setUpBeforeClass(): void {
        set_error_handler(function ($errno, $errstr, $errfile, $errline): false {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            throw new \Exception($errstr.' -> '.$errfile.':'.$errline, 0);
//            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            apcu_cache_info();
        } catch (Exception $ex) {
            echo PHP_EOL . $ex->getMessage() . PHP_EOL;
            echo PHP_EOL . "[APCU]" . PHP_EOL . " apc.enable_cli=1" . PHP_EOL;
            exit;
        }
    }

    public function testInteger(): void {
        $x = 8;
        $key = 'test_integer';
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key);
        $this->assertEquals($val, $x);
    }
    
    
    public function testFloat(): void {
        $x = 8.3;
        $key = 'test_float';
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key);
        $this->assertEquals($val, $x);
    }
    
    public function testString(): void {
        $x = "foobar";
        $key = 'test_string';
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key);
        $this->assertEquals($val, $x);
    }
    
    public function testArray(): void {
        $x = [1,2,3];
        $key = 'test_array';
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key);
        $this->assertEquals($val, $x);
    }
    
    public function testClass():void{
        $key = 'test_class';
        $res = $this->apcuCache->set($key, $this->foo);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key, Foo::class);
        $this->assertEquals($val->serialize(), $this->foo->serialize());
    }
    
    public function testExpireTtl():void{
        $x = 8;
        $key = 'test_integer';
        $res = $this->apcuCache->set($key, $x,2);
        $this->assertTrue($res);
        sleep(5);
        $val = $this->apcuCache->get($key);
        $this->assertNull($val);
    }
    
    public function testIncrDecr():void{
        $key = 'test_incr';
        $this->assertEquals(1, $this->apcuCache->increment($key));
        $this->assertEquals(2, $this->apcuCache->increment($key));
        $this->assertEquals(3, $this->apcuCache->increment($key));
        $this->assertEquals(2, $this->apcuCache->decrement($key));
        $this->assertEquals(1, $this->apcuCache->decrement($key));
    }
    
    public function testClear():void{
        $key = 'test_clear';
        $x = 1;
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $resClear = $this->apcuCache->clear($key);
        $this->assertTrue($resClear);
        $val = $this->apcuCache->get($key);
        $this->assertNull($val);
    }
    public function testClearAllCache():void{
        $key = 'test_clear';
        $key2 = 'test_clear2';
        $x = 1;
        $res = $this->apcuCache->set($key, $x);
        $res2 = $this->apcuCache->set($key2, $x);
        $this->assertTrue($res);
        $this->assertTrue($res2);
        $resClear = $this->apcuCache->clearAllCache();
        $this->assertTrue($resClear);
        $val = $this->apcuCache->get($key);
        $this->assertNull($val);
        $val2 = $this->apcuCache->get($key2);
        $this->assertNull($val2);
    }
}
