<?php

namespace App\Service;

use Memcache;

/**
 * Description of MemCacheService
 *
 * @author Stefano Perrini <stefano.perrini@bidoo.com> aka La Matrigna
 */
final class MemCacheService {

    private static ?Memcache $instance = null;

    private function __construct() {
        
    }

    /**
     * Get connection pointer to MemCacheServer
     * @return Memcache or null
     */
    public static function getConnection(): ?Memcache {
        if (self::$instance == null) {
            self::$instance = self::doConnection();
        }
        return self::$instance;
    }
    
    /**
     * 
     * @return ?Memcache
     */
    private static function doConnection(): ?Memcache {
        $memcache_obj = new Memcache();
        $check = $memcache_obj->pconnect('memcache-server', $_SERVER['MEMCACHE_PORT']);

        if ($check === false) {
            return null;
        }
        return $memcache_obj;
    }

}
