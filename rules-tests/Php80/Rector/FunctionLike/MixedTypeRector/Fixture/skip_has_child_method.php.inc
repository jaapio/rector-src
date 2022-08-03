<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\MixedTypeRector\Fixture;

class SkipHasChildMethod
{
    /**
     * @param mixed $param
     */
    public function run($param)
    {
    }
}

class SomeChild extends SkipHasChildMethod
{
    /**
     * @param int $param
     */
    public function run($param)
    {
    }
}