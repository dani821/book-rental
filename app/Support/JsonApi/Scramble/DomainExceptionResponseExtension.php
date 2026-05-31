<?php

declare(strict_types=1);

namespace App\Support\JsonApi\Scramble;

use App\Exceptions\DomainException;
use Dedoc\Scramble\Support\Type\ObjectType as ExceptionType;
use Dedoc\Scramble\Support\Type\Type;
use ReflectionClass;
use ReflectionException;

final class DomainExceptionResponseExtension extends JsonApiExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ExceptionType
            && $type->name !== DomainException::class
            && $type->isInstanceOf(DomainException::class);
    }

    /**
     * @throws ReflectionException
     */
    protected function statusFor(ExceptionType $type): int
    {
        return $this->exception($type)->status();
    }

    /**
     * @throws ReflectionException
     */
    protected function titleFor(ExceptionType $type): string
    {
        return $this->exception($type)->title();
    }

    /**
     * @throws ReflectionException
     */
    private function exception(ExceptionType $type): DomainException
    {
        /** @var class-string<DomainException> $class */
        $class = $type->name;

        return new ReflectionClass($class)->newInstance();
    }
}
