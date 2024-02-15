<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector as RectorRules;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        strictBooleans: true,
        // no privatization, naming, instanceOf, and earlyReturn
    )
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withPhpSets()
    ->withTypeCoverageLevel(29)
    ->withDeadCodeLevel(40)
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withSkip([
        // don't remove useless variables inside AR events
        // it's nice to keep them for editing later
        \Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class => [
            __DIR__.'/src/Model/*/Event/*',
        ],
        // we may not want the property to have a default value
        \Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class,
        \Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        // from set "codingStyle"
        \Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector::class,
        \Rector\CodingStyle\Rector\If_\NullableCompareToNullRector::class,
        \Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector::class,
        \Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector::class,
        \Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector::class,
        // from set "strictBooleans"
        \Rector\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector::class,
        \Rector\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector::class,
        // from set "codingStyle"
        \Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class,
        \Rector\CodeQuality\Rector\If_\CombineIfRector::class,
        \Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
    ])
;
