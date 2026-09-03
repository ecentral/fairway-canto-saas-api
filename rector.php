<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Rector\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
    ])
    ->withSkip([
        AddTypeToConstRector::class,
        ArrowFunctionDelegatingCallToFirstClassCallableRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        ExplicitNullableParamTypeRector::class,
        ForeachToArrayAnyRector::class,
        NewMethodCallWithoutParenthesesRector::class,
        ReadOnlyClassRector::class,
        ReadOnlyPropertyRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
        StringableForToStringRector::class,
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withCache(cacheDirectory: __DIR__ . '/var/cache/rector');
