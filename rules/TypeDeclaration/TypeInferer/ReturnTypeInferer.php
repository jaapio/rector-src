<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInfererTypeInferer;
use Rector\TypeDeclaration\TypeNormalizer;

/**
 * @deprecated
 * @todo Split into many narrow-focused rules
 */
final class ReturnTypeInferer
{
    public function __construct(
        private readonly TypeNormalizer $typeNormalizer,
        private readonly ReturnedNodesReturnTypeInfererTypeInferer $returnedNodesReturnTypeInfererTypeInferer,
        private readonly GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private readonly PhpVersionProvider $phpVersionProvider,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function inferFunctionLike(ClassMethod|Function_|Closure $functionLike): Type
    {
        $isSupportedStaticReturnType = $this->phpVersionProvider->isAtLeastPhpVersion(
            PhpVersionFeature::STATIC_RETURN_TYPE
        );

        $originalType = $this->returnedNodesReturnTypeInfererTypeInferer->inferFunctionLike($functionLike);
        if ($originalType instanceof MixedType) {
            return new MixedType();
        }

        $type = $this->typeNormalizer->normalizeArrayTypeAndArrayNever($originalType);

        // in case of void, check return type of children methods
        if ($type instanceof MixedType) {
            return new MixedType();
        }

        $type = $this->verifyStaticType($type, $isSupportedStaticReturnType);
        if (! $type instanceof Type) {
            return new MixedType();
        }

        $type = $this->verifyThisType($type, $functionLike);
        if (! $type instanceof Type) {
            return new MixedType();
        }

        // normalize ConstStringType to ClassStringType
        $resolvedType = $this->genericClassStringTypeNormalizer->normalize($type);
        return $this->resolveTypeWithVoidHandling($functionLike, $resolvedType);
    }

    public function verifyStaticType(Type $type, bool $isSupportedStaticReturnType): ?Type
    {
        if ($this->isStaticType($type)) {
            /** @var TypeWithClassName $type */
            return $this->resolveStaticType($isSupportedStaticReturnType, $type);
        }

        if ($type instanceof UnionType) {
            return $this->resolveUnionStaticTypes($type, $isSupportedStaticReturnType);
        }

        return $type;
    }

    public function verifyThisType(Type $type, FunctionLike $functionLike): ?Type
    {
        if (! $type instanceof ThisType) {
            return $type;
        }

        $class = $this->betterNodeFinder->findParentType($functionLike, Class_::class);
        $objectType = $type->getStaticObjectType();
        $objectTypeClassName = $objectType->getClassName();

        if (! $class instanceof Class_) {
            return $type;
        }

        if ($this->nodeNameResolver->isName($class, $objectTypeClassName)) {
            return $type;
        }

        return new MixedType();
    }

    private function resolveTypeWithVoidHandling(ClassMethod|Function_|Closure $functionLike, Type $resolvedType): Type
    {
        if ($resolvedType instanceof VoidType) {
            $hasReturnValue = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped(
                $functionLike,
                static function (Node $subNode): bool {
                    if (! $subNode instanceof Return_) {
                        return false;
                    }

                    return $subNode->expr instanceof Expr;
                }
            );

            if ($hasReturnValue) {
                return new MixedType();
            }
        }

        if ($resolvedType instanceof UnionType) {
            $benevolentUnionTypeIntegerType = $this->resolveBenevolentUnionTypeInteger($functionLike, $resolvedType);
            if ($benevolentUnionTypeIntegerType instanceof IntegerType) {
                return $benevolentUnionTypeIntegerType;
            }
        }

        return $resolvedType;
    }

    private function resolveBenevolentUnionTypeInteger(
        ClassMethod|Function_|Closure $functionLike,
        UnionType $unionType
    ): UnionType|IntegerType {
        $types = $unionType->getTypes();
        $countTypes = count($types);

        if ($countTypes !== 2) {
            return $unionType;
        }

        if (! ($types[0] instanceof IntegerType && $types[1]->isString()->yes())) {
            return $unionType;
        }

        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Return_::class);
        $returnsWithExpr = array_filter($returns, static fn (Return_ $return): bool => $return->expr instanceof Expr);

        if ($returns !== $returnsWithExpr) {
            return $unionType;
        }

        if ($returnsWithExpr === []) {
            return $unionType;
        }

        foreach ($returnsWithExpr as $returnWithExpr) {
            /** @var Expr $expr */
            $expr = $returnWithExpr->expr;
            $type = $this->nodeTypeResolver->getType($expr);

            if (! $type instanceof BenevolentUnionType) {
                return $unionType;
            }
        }

        return $types[0];
    }

    private function isStaticType(Type $type): bool
    {
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        return $type->getClassName() === ObjectReference::STATIC;
    }

    private function resolveUnionStaticTypes(UnionType $unionType, bool $isSupportedStaticReturnType): UnionType|null
    {
        $resolvedTypes = [];
        $hasStatic = false;

        foreach ($unionType->getTypes() as $unionedType) {
            if ($this->isStaticType($unionedType)) {
                /** @var FullyQualifiedObjectType $unionedType */
                $classReflection = $this->reflectionProvider->getClass($unionedType->getClassName());

                $resolvedTypes[] = new ThisType($classReflection);
                $hasStatic = true;
                continue;
            }

            $resolvedTypes[] = $unionedType;
        }

        if (! $hasStatic) {
            return $unionType;
        }

        // has static, but it is not supported
        if (! $isSupportedStaticReturnType) {
            return null;
        }

        return new UnionType($resolvedTypes);
    }

    private function resolveStaticType(
        bool $isSupportedStaticReturnType,
        TypeWithClassName $typeWithClassName
    ): ?ThisType {
        if (! $isSupportedStaticReturnType) {
            return null;
        }

        $classReflection = $typeWithClassName->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        return new ThisType($classReflection);
    }
}
