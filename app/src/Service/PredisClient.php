<?php

namespace App\Service;

use Predis\Client;

/**
 * Description of PredisClient
 *
 * @author Stefano Perrini <perrini.stefano@gmail.com> aka La Matrigna
 */
class PredisClient {

    private static ?Client $client = null;

    private function __construct() {
        
    }

    public static function getInstance(): Client {
        if (self::$client === null) {
            self::$client = new Client("tcp://redis-server:6379?persistent=redis01");
        }
        return self::$client;
    }
}
