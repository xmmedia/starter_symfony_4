<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withParallel()
    ->withCache(__DIR__.'/var/cache/rector')
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        if: true,
        earlyReturn: true,
        carbon: true,
        phpunitMockToStub: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
        // no typeDeclarationDocblocks, privatization, naming, namedArgs, instanceOf,
        // rectorPreset, phpunitCodeQuality, and phpunitNarrowAsserts
    )
    ->withAttributesSets(symfony: true, doctrine: true, phpunit: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withPhpSets()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/public',
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/tests',
    ])
    ->withImportNames(
        importNames: false,
        importDocBlockNames: false,
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withRootFiles()
    ->withSkip([
        // don't remove useless variables inside AR events
        // it's nice to keep them for editing later
        Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class => [
            __DIR__.'/src/Model/*/Event/*',
        ],
        // from the PHP sets
        // we may not want the property to have a default value
        Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class,
        // from set "codingStyle"
        Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector::class,
        Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector::class,
        Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector::class,
        Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector::class,
        // from set "codeQuality"
        Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class,
        // from set "if"
        Rector\CodeQuality\Rector\If_\CombineIfRector::class,
        Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
        Rector\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector::class,
        // from set "symfonyCodeQuality"
        // keep injecting services as controller action arguments
        Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector::class,
        // from set "deadCode"
        Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        // temporarily disabled because it adds newlines between traits
        Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector::class,
    ])
;
