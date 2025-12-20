<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        // no privatization, naming, instanceOf, and earlyReturn
        // no deadCode and typeDeclarations as they're below
    )
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withPhpSets()
    ->withTypeCoverageLevel(29)
    ->withDeadCodeLevel(40)
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withSets([
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
    ])
    ->withSkip([
        // don't remove useless variables inside AR events
        // it's nice to keep them for editing later
        Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class => [
            __DIR__.'/src/Model/*/Event/*',
        ],
        // we may not want the property to have a default value
        Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class,
        Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        // from set "codingStyle"
        Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector::class,
        Rector\CodingStyle\Rector\If_\NullableCompareToNullRector::class,
        Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector::class,
        Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector::class,
        Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector::class,
        // from set "codingStyle"
        Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class,
        Rector\CodeQuality\Rector\If_\CombineIfRector::class,
        Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
        // from set "deadCode"
        Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        // from Symfony composer set
        // disabled because it rewrite the console commands to use attributes, but can't get this to work atm
        Rector\Symfony\Symfony73\Rector\Class_\InvokableCommandInputAttributeRector::class,
        // temporarily disabled because it adds newlines between traits
        Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector::class,
    ])
;
