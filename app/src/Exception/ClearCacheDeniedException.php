<?php

declare(strict_types=1);

namespace CacheMultiLayer\Exception;

use Exception;
use Throwable;

/**
 * 
 * Exception lanciata dalla cache quando viene lanciato il comando di cancellazione di tutte le chiavi dalla cache quando quest'opzione è vietata
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
class ClearCacheDeniedException extends Exception
{

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}
