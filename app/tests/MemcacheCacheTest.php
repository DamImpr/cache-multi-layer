<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Exception\CacheMissingConfigurationException;
use CacheMultiLayer\Service\Cache;
use Exception;
use Override;

/**
 * Description of ApcuCacheTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class MemcacheCacheTest extends AbstractCache
{

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCache(Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'memcache-server', 'port' => 11211]));
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

            switch ($errno) {
                case E_USER_WARNING:
                    return true; // return on warning connection
                case E_USER_NOTICE:
                    echo "[NOTICE]: ".$errstr . ' -> ' . $errfile . ':' . $errline;
                    return true;
            }

            throw new Exception($errstr . ' -> ' . $errfile . ':' . $errline, 0);
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
    public function testRemainingTTL(): void
    {
        parent::testRemainingTTL();
    }

    public function testMissingServer(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['port' => 11211]);
    }

    public function testMissingPort(): void
    {
        $this->expectException(CacheMissingConfigurationException::class);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'localhost']);
    }

    public function testConnectionNotFound(): void
    {
        $this->expectException(Exception::class);
        Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => '192.168.0.0', 'port' => 11211])->isConnected();
    }

    public function testEnum(): void
    {
        $this->doTestRealEnum(CacheEnum::MEMCACHE);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
         restore_error_handler();
        Cache::factory(CacheEnum::MEMCACHE, 60, ['server_address' => 'memcache-server', 'port' => 11211])->clearAllCache();
    }
}
