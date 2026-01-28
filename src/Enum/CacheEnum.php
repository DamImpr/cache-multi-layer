<?php

namespace CacheMultiLayer\Enum;

/**
 *
 * @author Damiano Improta <code@damianoimprota.it>
 */
enum CacheEnum: int
{
    case APCU = 1;
    case REDIS = 2;
    case MEMCACHE = 3;
    case PREDIS = 4;
}
