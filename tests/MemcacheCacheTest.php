<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Service\Cache;
use Exception;
use Memcache;
use Override;

/**
 * MEMCACHE unit test class implementation
 *
 * @author Damiano Improta <code@damianoimprota.dev> 
 */
class MemcacheCacheTest extends AbstractCache
{

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCache(Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'memcache-server']));
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

//            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
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
    public function testEmptyDecrement(): void
    {
        parent::testEmptyDecrement();
    }

    #[\Override]
    public function testEmptyIncrement(): void
    {
        parent::testEmptyIncrement();
    }

    #[\Override]
    public function testRemainingTTL(): void
    {
        parent::testRemainingTTL();
    }

    public function testMissingServer(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::MEMCACHE, 60);
    }

    public function testConnectionNotFound(): void
    {
        $this->expectException(Exception::class);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'ip-no-memcache'])->isConnected();
    }

    public function testInstance(): void
    {
        $memcache = new Memcache();
        $memcache->connect('memcache-server', 11211);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['instance' => $memcache]);
        $this->assertTrue(true); //no exception throwns
    }

    public function testMissingInstance(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['instance' => 5]);
    }

    public function testEnum(): void
    {
        $this->doTestRealEnum(CacheEnum::MEMCACHE);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        restore_error_handler();
        Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'memcache-server'])->clearAllCache();
    }
}