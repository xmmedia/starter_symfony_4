<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector as RectorRules;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    // Define what rule sets will be applied
    // List of built-in Rector rules: https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md
    // Doctrine rules: https://github.com/rectorphp/rector-doctrine/blob/main/docs/rector_rules_overview.md
    // Symfony rules: https://github.com/rectorphp/rector-symfony/blob/main/docs/rector_rules_overview.md
    $config->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $config->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->import(SymfonySetList::SYMFONY_62);
    $config->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $config->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $config->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->rule(RectorRules\ArrowFunction\AddArrowFunctionReturnTypeRector::class);
    $config->rule(RectorRules\Closure\AddClosureReturnTypeRector::class);
    $config->rule(RectorRules\ClassMethod\AddMethodCallBasedStrictParamTypeRector::class);
    $config->rule(RectorRules\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector::class);
    $config->rule(RectorRules\ClassMethod\AddParamTypeFromPropertyTypeRector::class);
    $config->rule(RectorRules\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector::class);
    $config->rule(RectorRules\FunctionLike\AddReturnTypeDeclarationFromYieldsRector::class);
    $config->ruleWithConfiguration(RectorRules\ClassMethod\AddVoidReturnTypeWhereNoReturnRector::class, [
        RectorRules\ClassMethod\AddVoidReturnTypeWhereNoReturnRector::USE_PHPDOC => true,
    ]);
    $config->rule(RectorRules\ClassMethod\ParamTypeByMethodCallTypeRector::class);
    $config->rule(RectorRules\ClassMethod\ParamTypeByParentCallTypeRector::class);
    $config->rule(RectorRules\Param\ParamTypeFromStrictTypedPropertyRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromReturnDirectArrayRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromReturnNewRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromStrictConstantReturnRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromStrictNativeCallRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromStrictNewArrayRector::class);
    $config->rule(RectorRules\ClassMethod\ReturnTypeFromStrictTypedCallRector::class);
    $config->ruleWithConfiguration(RectorRules\Property\TypedPropertyFromAssignsRector::class, [
        RectorRules\Property\TypedPropertyFromAssignsRector::INLINE_PUBLIC => true,
    ]);
    $config->rule(RectorRules\Property\TypedPropertyFromStrictConstructorRector::class);
    $config->rule(RectorRules\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
    $config->rule(RectorRules\Property\TypedPropertyFromStrictSetUpRector::class);

    // get services (needed for register a single rule)
    $config->services();
};
