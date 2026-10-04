<?php

declare(strict_types=1);

namespace FootballApi;

use FootballApi\Exceptions\AuthenticationException;
use FootballApi\Exceptions\FootballException;
use FootballApi\Exceptions\InvalidRequestException;
use FootballApi\Exceptions\NotFoundException;
use FootballApi\Exceptions\PlanException;
use FootballApi\Exceptions\RateLimitException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait MakesHttpRequests
{
    /**
     * @param  array<string, mixed>  $query
     * @return array{data: mixed, pagination: ?array<string, mixed>}
     */
    protected function get(string $path, array $query = [], ?string $ttlKey = null): array
    {
        if (trim($this->apiKey) === '') {
            throw new AuthenticationException('Missing API key.');
        }

        $cacheKey = config('football.cache.prefix').'|'.hash('sha256', $this->apiKey).'|GET|'.$path.'|'
            .json_encode($this->sorted($query));
        $useCache = $ttlKey !== null && (bool) config('football.cache.enabled');

        if ($useCache) {
            $cached = $this->cacheStore()->get($cacheKey);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $retries = 0;

        while (true) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->acceptJson()
                    ->timeout($this->timeout)
                    ->baseUrl($this->baseUrl)
                    ->get($path, $query);
            } catch (ConnectionException $e) {
                throw new FootballException(
                    $e->getMessage(),
                    errorCode: 'connection_failed',
                    previous: $e,
                );
            }

            if ($response->status() === 429) {
                $seconds = $this->retryAfterSeconds($response);

                if ($seconds === null
                    || $seconds > (int) config('football.retry.max_wait')
                    || $retries >= (int) config('football.retry.times')) {
                    throw $this->rateLimitException($response, $seconds);
                }

                $retries++;

                if ($seconds > 0) {
                    sleep($seconds);
                }

                continue;
            }

            break;
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new FootballException(
                'The API returned an unreadable response.',
                status: $response->status(),
                errorCode: 'invalid_response',
            );
        }

        $status = $response->status();

        if (! $response->successful()) {
            throw $this->errorFor($status, $body);
        }

        if ((int) ($body['success'] ?? 0) !== 1) {
            throw $this->errorFor($status, $body);
        }

        $payload = [
            'data' => $body['data'] ?? null,
            'pagination' => $body['pagination'] ?? null,
        ];

        if ($useCache) {
            $this->cacheStore()->put($cacheKey, $payload, (int) config('football.cache.ttl.'.$ttlKey));
        }

        return $payload;
    }

    /**
     * @param  array{data: mixed, pagination: ?array<string, mixed>}  $response
     * @return array<mixed>
     */
    protected function dataArray(array $response): array
    {
        if (! is_array($response['data'])) {
            throw new FootballException('The API response had no data.', errorCode: 'invalid_response');
        }

        return $response['data'];
    }

    /** @param  array<string, mixed>  $body */
    private function errorFor(int $status, array $body): FootballException
    {
        $error = is_array($body['error'] ?? null) ? $body['error'] : [];
        $code = $this->stringOrNull($error['code'] ?? null);

        $class = match (true) {
            $status === 401 => AuthenticationException::class,
            $status === 403 && $code === 'insufficient_plan' => PlanException::class,
            $status === 403 => AuthenticationException::class,
            $status === 404 => NotFoundException::class,
            $status === 400, $status === 422 => InvalidRequestException::class,
            default => FootballException::class,
        };

        return new $class(
            $this->stringOrNull($error['message'] ?? null) ?? 'HTTP '.$status,
            $status,
            $this->stringOrNull($error['type'] ?? null),
            $code,
            $this->stringOrNull($error['param'] ?? null),
            $this->stringOrNull($error['doc_url'] ?? null),
            $this->stringOrNull($error['request_id'] ?? null),
        );
    }

    private function rateLimitException(Response $response, ?int $retryAfter): RateLimitException
    {
        $body = $response->json();
        $error = is_array($body) && is_array($body['error'] ?? null) ? $body['error'] : [];

        return new RateLimitException(
            $this->stringOrNull($error['message'] ?? null) ?? 'HTTP 429',
            429,
            $this->stringOrNull($error['type'] ?? null),
            $this->stringOrNull($error['code'] ?? null),
            $this->stringOrNull($error['param'] ?? null),
            $this->stringOrNull($error['doc_url'] ?? null),
            $this->stringOrNull($error['request_id'] ?? null),
            null,
            $retryAfter,
            $this->intHeader($response, 'X-RateLimit-Limit'),
            $this->intHeader($response, 'X-RateLimit-Remaining'),
            $this->intHeader($response, 'X-RateLimit-Reset'),
        );
    }

    private function retryAfterSeconds(Response $response): ?int
    {
        $header = trim($response->header('Retry-After'));

        if ($header === '') {
            return null;
        }

        if (ctype_digit($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);

        return $timestamp === false ? null : max(0, $timestamp - time());
    }

    private function intHeader(Response $response, string $name): ?int
    {
        $value = trim($response->header($name));

        return ctype_digit($value) ? (int) $value : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    /** @return Repository */
    private function cacheStore()
    {
        $store = config('football.cache.store');

        return $store ? Cache::store((string) $store) : Cache::store();
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function sorted(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->sorted($value);
            }
        }

        ksort($values);

        return $values;
    }
}
