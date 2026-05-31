<?php

declare(strict_types=1);

namespace App\Support\JsonApi\Scramble;

use Dedoc\Scramble\Support\Type\ObjectType as ExceptionType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FrameworkExceptionResponseExtension extends JsonApiExceptionToResponseExtension
{
    /**
     * @var array<class-string, array{status: int, title: string}>
     */
    private const array MAP = [
        ValidationException::class => ['status' => 422, 'title' => 'Unprocessable Entity'],
        AuthenticationException::class => ['status' => 401, 'title' => 'Unauthorized'],
        AuthorizationException::class => ['status' => 403, 'title' => 'Forbidden'],
        AccessDeniedHttpException::class => ['status' => 403, 'title' => 'Forbidden'],
        ModelNotFoundException::class => ['status' => 404, 'title' => 'Not Found'],
        NotFoundHttpException::class => ['status' => 404, 'title' => 'Not Found'],
    ];

    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ExceptionType && $this->lookup($type) !== null;
    }

    protected function statusFor(ExceptionType $type): int
    {
        $meta = $this->lookup($type);

        return $meta === null ? 500 : $meta['status'];
    }

    protected function titleFor(ExceptionType $type): string
    {
        $meta = $this->lookup($type);

        return $meta === null ? 'Server Error' : $meta['title'];
    }

    /**
     * @return array{status: int, title: string}|null
     */
    private function lookup(ExceptionType $type): ?array
    {
        foreach (self::MAP as $class => $meta) {
            if ($type->isInstanceOf($class)) {
                return $meta;
            }
        }

        return null;
    }
}
