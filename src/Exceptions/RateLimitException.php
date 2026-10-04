<?php

declare(strict_types=1);

namespace FootballApi\Exceptions;

use Throwable;

class RateLimitException extends FootballException
{
    public function __construct(
        string $message,
        ?int $status = null,
        ?string $type = null,
        ?string $errorCode = null,
        ?string $param = null,
        ?string $docUrl = null,
        ?string $requestId = null,
        ?Throwable $previous = null,
        public ?int $retryAfter = null,
        public ?int $limit = null,
        public ?int $remaining = null,
        public ?int $reset = null,
    ) {
        parent::__construct(
            $message,
            $status,
            $type,
            $errorCode,
            $param,
            $docUrl,
            $requestId,
            $previous,
        );
    }
}
