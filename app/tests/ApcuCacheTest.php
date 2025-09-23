<?php

namespace CacheMultiLayer\Tests;

use CacheMultiLayer\Service\ApcuCache;
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

    #[Override]
    protected function setUp(): void {
        $this->apcuCache = new ApcuCache(60);
    }

    #[\Override]
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
        $x = 5;
        $key = 'test_integer';
        $res = $this->apcuCache->set($key, $x);
        $this->assertTrue($res);
        $val = $this->apcuCache->get($key);
        $this->assertEquals($val, $x);
    }
}
