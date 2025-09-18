<?php

namespace App\Service\Cache;

/**
 * Enumerazione che elenca le tipologie di cache disponibili
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
final class CacheEnum {
    /**
     * Cache APCU
     */
    CONST int APCU = 0;
    
    /**
     * Cache Redis
     */
    CONST int REDIS = 1;
    
    private function __construct() {
        //nothing to do 
    }
    
    

}
