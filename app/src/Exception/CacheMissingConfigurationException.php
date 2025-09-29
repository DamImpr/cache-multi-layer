<?php


namespace CacheMultiLayer\Exception;

use Exception;
use Throwable;

/**
 * Description of CacheMissingConfigurationException
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheMissingConfigurationException  extends Exception{
    
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

    
}