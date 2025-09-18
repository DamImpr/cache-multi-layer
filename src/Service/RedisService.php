<?php

namespace App\Service\Cache;

use Redis;

/**
 * Classe che rappresenta la chiamata alla cache redis, implementata con il Singleton Pattern, in modo da 
 * avere una singola connessione a redis e impedire sovraccaricamenti lato server con connessioni multiple
 *
 * @author Stefano Perrini <stefano.perrini@bidoo.com>
 */
class RedisService extends Redis {

    /**
     * instanza unica di questa classe.
     */
    private static ?\App\Service\Cache\RedisService $instance = null;

    /**
     * costruttore privato per consentire di instanziare la classe solo all'interno dei propri metodi
     */
    private function __construct() {
        $this->pconnect("redis-server", $_SERVER['REDIS_PORT']);
    }

    /**
     * Resistutisce l'unica istanza di RedisService del software.
     * @return RedisService l'unica istanza della classe
     */
    public static function getInstance(): RedisService {
        if (self::$instance === null) {
            self::$instance = new RedisService();
        }
        return self::$instance;
    }

    /**
     * distruttore della classe, chiude la connessione a Redis
     */
    public function __destruct() {
        $this->close();
    }

}
