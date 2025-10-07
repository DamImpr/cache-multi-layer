<?php

namespace CacheMultiLayer\Exception;

use Exception;
use Throwable;

/**
 *
 * Exception thrown when some mandatory configurations are missing during cache creation
 * @author Damiano Improta <code@damianoimprota.dev>
 */
class CacheMissingConfigurationException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
