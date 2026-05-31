<?php

declare(strict_types=1);

namespace App\Support\JsonApi\Scramble;

use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Type\ObjectType as ExceptionType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

abstract class JsonApiExceptionToResponseExtension extends ExceptionToResponseExtension
{
    private const string ERROR_SCHEMA = 'JsonApiError';

    abstract public function shouldHandle(Type $type): bool;

    abstract protected function statusFor(ExceptionType $type): int;

    abstract protected function titleFor(ExceptionType $type): string;

    public function toResponse(Type $type): ?Response
    {
        if (! $type instanceof ExceptionType) {
            return null;
        }

        $body = (new ObjectType)
            ->addProperty('errors', (new ArrayType)->setItems($this->errorReference()))
            ->setRequired(['errors']);

        return Response::make($this->statusFor($type))
            ->setDescription($this->titleFor($type))
            ->setContent('application/vnd.api+json', Schema::fromType($body));
    }

    public function reference(ExceptionType $type): Reference
    {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }

    private function errorReference(): Reference
    {
        if (! $this->components->hasSchema(self::ERROR_SCHEMA)) {
            $this->components->addSchema(self::ERROR_SCHEMA, Schema::fromType($this->errorObject()));
        }

        return $this->components->getSchemaReference(self::ERROR_SCHEMA);
    }

    private function errorObject(): ObjectType
    {
        $source = (new ObjectType)
            ->addProperty('pointer', (new StringType)->example('/email'))
            ->setRequired(['pointer']);

        return (new ObjectType)
            ->addProperty('status', (new StringType)->example('422'))
            ->addProperty('title', (new StringType)->example('Unprocessable Entity'))
            ->addProperty('detail', (new StringType)->nullable(true)->example('The email field is required.'))
            ->addProperty('source', $source->setDescription('Present for validation errors; a JSON Pointer into the request body.'))
            ->setRequired(['status', 'title']);
    }
}
