<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
                ->withPaths([
                    __DIR__ . '/src',
                    __DIR__ . '/tests',
                ])
                // uncomment to reach your current PHP version
                ->withPhpSets(php83: true)
                ->withPhpVersion(PhpVersion::PHP_83)
                ->withTypeCoverageLevel(0)
                ->withDeadCodeLevel(0)
                ->withCodeQualityLevel(0);
