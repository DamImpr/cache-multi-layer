<?php

namespace CacheMultiLayer\Enum;

/**
 *
 * @author Damiano Improta <code@damianoimprota.dev> aka Drizella
 */
enum CacheEnum {
    case APCU;
    case REDIS;
    case MEMCACHE;
}
