<?php

namespace CacheMultiLayer\Exception;

use Exception;
use Throwable;

/**
 *
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class CacheMissingConfigurationException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
