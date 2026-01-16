<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Service\Cache;
use Exception;
use Override;
use Predis\Client;

/**
 * PREDIS unit test class implementation
 * @author Damiano Improta <code@damianoimprota.it> 
 */
class PRedisCacheTest extends AbstractCache
{

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCache(Cache::factory(CacheEnum::PREDIS, 60, ['server_address' => 'redis-server']));
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }

            return match ($errno) {
                E_USER_WARNING => true,
                default => throw new Exception($errstr . ' -> ' . $errfile . ':' . $errline, 0),
            };
        });
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

    #[\Override]
    public function testIsConnected(): void
    {
        parent::testIsConnected();
    }

    #[\Override]
    public function testRemainingTTL(): void
    {
        parent::testRemainingTTL();
    }

    #[\Override]
    public function testEmptyDecrement(): void
    {
        parent::testEmptyDecrement();
    }

    #[\Override]
    public function testEmptyIncrement(): void
    {
        parent::testEmptyIncrement();
    }

    public function testMissingServer(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::PREDIS, 60);
    }

    public function testConnectionNotFound(): void
    {
        $this->expectException(Exception::class);
        Cache::factory(CacheEnum::PREDIS, 60, ['server_address' => 'ip-no-redis'])->isConnected();
    }

    public function testEnum(): void
    {
        $this->doTestRealEnum(CacheEnum::PREDIS);
    }

    public function testConnectionPersistent(): void
    {
        $this->assertTrue(Cache::factory(CacheEnum::PREDIS, 60, ['server_address' => 'redis-server', 'persistent' => true])->isConnected());
    }

    public function testInstance(): void
    {
        $client = new Client([
            'host' => 'redis-server',
            'port' => 6379
        ]);
        Cache::factory(CacheEnum::PREDIS, 60, ['instance' => $client]);
        $this->assertTrue(true); //no exception throwns
    }

    public function testMissingInstance(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::PREDIS, 60, ['instance' => 5]);
    }
    
     public function testPrefix(): void
    {
        $val = 10; //maradona
        $key = "test_prefix";
        $cacheSamePrefix = Cache::factory(CacheEnum::PREDIS, 60, ['key_prefix' => '','server_address' => 'redis-server']);
        $cacheOtherPrefix = Cache::factory(CacheEnum::PREDIS, 10, ['key_prefix' => 'other_','server_address' => 'redis-server']);
        $this->getCache()->set($key, $val);
        $this->assertEquals($cacheSamePrefix->get($key), $val);
        $this->assertNull($cacheOtherPrefix->get($key));
    }
    
    #[\Override]
    public function testArrayDepth(): void
    {
        parent::testArrayDepth();
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        restore_error_handler();
        Cache::factory(CacheEnum::PREDIS, 60, ['server_address' => 'redis-server'])->clearAllCache();
    }
}