<?php

declare(strict_types=1);

namespace CacheMultiLayer\Enum;

/**
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
enum CacheEnum:int {
    case APCU = 1;
    case REDIS = 2;
}
