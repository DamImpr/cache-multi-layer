<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
        ->in(__DIR__ . "/src")
        ->in(__DIR__ . "/tests")
        ->exclude('var')
        ->exclude('vendor')
;
return (new Config())
                ->setRules([
                    '@Symfony' => true,
                    'array_syntax' => ['syntax' => 'short'],
                ])
                ->setRiskyAllowed(false)
                ->setFinder($finder)
;
