<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Enum\CacheEnum;
use CacheMultiLayer\Service\ApcuCache;
use Exception;
use Override;

/**
 * Description of ApcuCacheTest
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class ApcuCacheTest extends AbstractCache
{

    #[Override]
    public static function setUpBeforeClass(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline): false {
            // error was suppressed with the @-operator
            if (0 === error_reporting())
            {
                return false;
            }
            throw new Exception($errstr . ' -> ' . $errfile . ':' . $errline, 0);
//            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            self::checkBeforeClass();
        } catch (Exception) {
            echo PHP_EOL . "[APCU]" . PHP_EOL . " apc.enable_cli=1" . PHP_EOL;
            exit;
        }
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setCache(new ApcuCache(60));
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
    public function testIsConnected(): void
    {
        parent::testIsConnected();
    }

    public function testEnum(): void
    {
        $this->doTestRealEnum(CacheEnum::APCU);
    }

    /**
     * 
     * @return void
     * @throws Exception
     */
    private static function checkBeforeClass(): void
    {
        if (apcu_cache_info() === false)
        {
            throw new Exception("apcu cache info not loaded");
        }
    }
}
