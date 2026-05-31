<?php

declare(strict_types=1);

namespace App\Support\JsonApi;

use App\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class JsonApiExceptionRenderer
{
    public function __construct(private Application $app) {}

    /**
     * Render the throwable as a JSON:API error document. Returns null for an unexpected,
     * unmapped error while debugging so the framework's default (debug) handler can surface
     * the stack trace; in production such errors still receive the generic 500 envelope.
     */
    public function render(Throwable $e): ?JsonResponse
    {
        $mapped = $this->map($e);

        if ($mapped === null) {
            if (! $this->app->isProduction() && $this->app->hasDebugModeEnabled()) {
                return null;
            }

            $mapped = [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [new JsonApiError('500', 'Server Error', 'An unexpected error occurred.')],
            ];
        }

        [$status, $errors] = $mapped;

        $response = new JsonResponse(
            ['errors' => array_map(static fn (JsonApiError $error): array => $error->toArray(), $errors)],
            $status,
            ['Content-Type' => 'application/vnd.api+json'],
        );

        if ($e instanceof HttpExceptionInterface) {
            $response->headers->add($e->getHeaders());
            $response->headers->set('Content-Type', 'application/vnd.api+json');
        }

        return $response;
    }

    /**
     * @return array{0: int, 1: array<int, JsonApiError>}|null
     */
    private function map(Throwable $e): ?array
    {
        return match (true) {
            $e instanceof ValidationException => [
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->validationErrors($e),
            ],
            $e instanceof AuthenticationException => [
                Response::HTTP_UNAUTHORIZED,
                [new JsonApiError('401', 'Unauthorized', $e->getMessage() ?: 'Unauthenticated.')],
            ],
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => [
                Response::HTTP_FORBIDDEN,
                [new JsonApiError('403', 'Forbidden', $e->getMessage() ?: 'This action is unauthorized.')],
            ],
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => [
                Response::HTTP_NOT_FOUND,
                [new JsonApiError('404', 'Not Found', 'The requested resource was not found.')],
            ],
            $e instanceof DomainException => [
                $e->status(),
                [new JsonApiError((string) $e->status(), $e->title(), $e->getMessage() ?: null)],
            ],
            $e instanceof HttpExceptionInterface => [
                $e->getStatusCode(),
                [new JsonApiError(
                    (string) $e->getStatusCode(),
                    Response::$statusTexts[$e->getStatusCode()] ?? 'HTTP Error',
                    $e->getMessage() ?: null,
                )],
            ],
            default => null,
        };
    }

    /**
     * @return array<int, JsonApiError>
     */
    private function validationErrors(ValidationException $e): array
    {
        $errors = [];

        foreach ($e->errors() as $field => $messages) {
            $detail = is_array($messages) ? ($messages[0] ?? null) : $messages;

            $errors[] = new JsonApiError(
                '422',
                'Unprocessable Entity',
                is_string($detail) ? $detail : null,
                '/'.$field,
            );
        }

        return $errors;
    }
}
