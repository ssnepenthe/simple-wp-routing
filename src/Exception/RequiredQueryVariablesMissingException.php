<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use RuntimeException;
use Throwable;

class RequiredQueryVariablesMissingException extends RuntimeException implements RewriteInvocationExceptionInterface
{
    protected array $missingQueryVariables;

    /**
     * @param string[] $missingQueryVariables
     */
    public function __construct(
        array $missingQueryVariables,
        string $message = '',
        ?Throwable $previous = null,
        int $code = 0
    ) {
        $this->missingQueryVariables = $missingQueryVariables;

        parent::__construct($message, $code, $previous);
    }

    public function toHttpException(): HttpExceptionInterface
    {
        return new BadRequestHttpException(
            'Missing required query variables: ' . implode(', ', $this->missingQueryVariables),
            $this
        );
    }
}
