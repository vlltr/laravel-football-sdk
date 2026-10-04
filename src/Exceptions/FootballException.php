<?php

declare(strict_types=1);

namespace FootballApi\Exceptions;

use RuntimeException;
use Throwable;

class FootballException extends RuntimeException
{
    public function __construct(
        string $message,
        public ?int $status = null,
        public ?string $type = null,
        public ?string $errorCode = null,
        public ?string $param = null,
        public ?string $docUrl = null,
        public ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
