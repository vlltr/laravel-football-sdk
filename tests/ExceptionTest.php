<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Exceptions\FootballException;
use FootballApi\Exceptions\RateLimitException;

class ExceptionTest extends TestCase
{
    public function test_base_exception_keeps_the_api_error_fields(): void
    {
        $exception = new FootballException(
            'No fixture with id 99999.',
            status: 404,
            type: 'invalid_request',
            code: 'fixture_not_found',
            param: 'id',
            docUrl: 'https://5dollarfootballapi.com/docs/errors',
            requestId: 'req_a1b2c3d4e5f6',
        );

        $this->assertSame('No fixture with id 99999.', $exception->getMessage());
        $this->assertSame('fixture_not_found', $exception->code);
        $this->assertSame('req_a1b2c3d4e5f6', $exception->requestId);
    }

    public function test_rate_limit_exception_keeps_retry_headers(): void
    {
        $exception = new RateLimitException(
            'Slow down.',
            status: 429,
            code: 'rate_limited',
            retryAfter: 30,
            limit: 10,
            remaining: 0,
            reset: 1783814400,
        );

        $this->assertSame(30, $exception->retryAfter);
        $this->assertSame(0, $exception->remaining);
    }
}
