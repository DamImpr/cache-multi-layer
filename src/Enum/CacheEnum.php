<?php

namespace CacheMultiLayer\Enum;

/**
 *
 * @author Damiano Improta <code@damianoimprota.dev>
 */
enum CacheEnum: int
{
    case APCU = 1;
    case REDIS = 2;
    case MEMCACHE = 3;
}
