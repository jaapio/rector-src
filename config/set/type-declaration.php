<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddReturnTypeDeclarationFromYieldsRector;
use Rector\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;
use Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        AddClosureReturnTypeRector::class,
        AddArrowFunctionReturnTypeRector::class,
        ParamTypeByMethodCallTypeRector::class,
        TypedPropertyFromAssignsRector::class,
        ReturnAnnotationIncorrectNullableRector::class,
        VarAnnotationIncorrectNullableRector::class,
        ParamAnnotationIncorrectNullableRector::class,
        AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        ReturnTypeFromStrictTypedPropertyRector::class,
        TypedPropertyFromStrictConstructorRector::class,
        ParamTypeFromStrictTypedPropertyRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
        ReturnTypeFromReturnNewRector::class,
        TypedPropertyFromStrictGetterMethodReturnTypeRector::class,
        AddMethodCallBasedStrictParamTypeRector::class,
        ArrayShapeFromConstantArrayReturnRector::class,
        ReturnTypeFromStrictBoolReturnExprRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
        ReturnTypeFromStrictScalarReturnExprRector::class,
        TypedPropertyFromStrictSetUpRector::class,
        ParamTypeByParentCallTypeRector::class,
        AddParamTypeSplFixedArrayRector::class,
        AddParamTypeBasedOnPHPUnitDataProviderRector::class,
        AddParamTypeFromPropertyTypeRector::class,
        AddReturnTypeDeclarationFromYieldsRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        ReturnTypeFromStrictConstantReturnRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        ReturnNeverTypeRector::class,
    ]);
};
