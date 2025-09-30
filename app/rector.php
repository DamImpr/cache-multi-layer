<?php 
declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
                ->withPaths([
                    __DIR__ . '/src',
                    __DIR__ . '/tests'
                ])
                ->withSkip([
                    //InlineClassRoutePrefixRector::class,
                    NewMethodCallWithoutParenthesesRector::class,
                    UnusedForeachValueToArrayKeysRector::class,
                    //RemoveUnusedForeachKeyRector::class,
                    //RemoveUselessParamTagRector::class,
//                    RemoveUselessReturnTagRector::class,
                    DeclareStrictTypesRector::class,
                    FinalizeTestCaseClassRector::class
                        //SimplifyUselessVariableRector::class
                ])
                ->withPreparedSets(
//                deadCode: true,
//                codeQuality: true,
                codingStyle: true,
                naming: true,
                //privatization: true,
                //typeDeclarations: true,
                rectorPreset: true
                )
                ->withPhpSets(php83: true)
                ->withPhpVersion(PhpVersion::PHP_83)
                ->withAttributesSets(symfony: true, doctrine: true)
                ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
                ->withSets(
                        [
                            LevelSetList::UP_TO_PHP_84
                        ]
                )
                ->withRules(
                        [
                        //ExplicitNullableParamTypeRector::class,
                        //AddOverrideAttributeToOverriddenMethodsRector::class,
                        //ReturnTypeFromStrictNativeCallRector::class
                        ]
                )
                ->withTypeCoverageLevel(50)
                ->withDeadCodeLevel(50)
                ->withCodeQualityLevel(50)
;