<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Exceptions\AuthenticationException;
use FootballApi\Exceptions\FootballException;
use FootballApi\Exceptions\InvalidRequestException;
use FootballApi\Exceptions\NotFoundException;
use FootballApi\Exceptions\PlanException;
use FootballApi\Exceptions\RateLimitException;
use FootballApi\Football;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HttpTransportTest extends TestCase
{
    private function client(string $apiKey = 'fb_live_test'): ProbeClient
    {
        return new ProbeClient(
            $apiKey,
            (string) config('football.base_url'),
            (int) config('football.timeout'),
        );
    }

    /** @return array<string, mixed> */
    private function error(string $code, ?string $requestId = 'req_123', string $message = 'Nope.'): array
    {
        return [
            'success' => 0,
            'error' => [
                'type' => 'api_error',
                'code' => $code,
                'message' => $message,
                'param' => 'id',
                'doc_url' => 'https://docs.example.com/errors/'.$code,
                'request_id' => $requestId,
            ],
        ];
    }

    public function test_empty_api_key_throws_without_sending(): void
    {
        Http::fake();

        try {
            (new ProbeClient('', 'https://api.5dollarfootballapi.com/v1', 10))->raw('fixtures');
            $this->fail('Expected AuthenticationException.');
        } catch (AuthenticationException $e) {
            $this->assertSame('Missing API key.', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_successful_get_sends_bearer_and_returns_data_and_pagination(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => 1,
                'data' => [['id' => 1]],
                'pagination' => ['page' => 1, 'total' => 1],
            ]),
        ]);

        $result = $this->client()->raw('v1/fixtures', ['page' => 1]);

        $this->assertSame([['id' => 1]], $result['data']);
        $this->assertSame(['page' => 1, 'total' => 1], $result['pagination']);

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer fb_live_test')
            && $request->hasHeader('Accept', 'application/json')
            && $request->method() === 'GET'
            && str_contains($request->url(), 'v1/fixtures'));
    }

    public function test_missing_pagination_is_null(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => ['a' => 1]])]);

        $result = $this->client()->raw('v1/status');

        $this->assertSame(['a' => 1], $result['data']);
        $this->assertNull($result['pagination']);
    }

    public function test_404_throws_not_found_with_error_fields(): void
    {
        Http::fake(['*' => Http::response($this->error('fixture_not_found'), 404)]);

        try {
            $this->client()->raw('v1/fixtures/1');
            $this->fail('Expected NotFoundException.');
        } catch (NotFoundException $e) {
            $this->assertSame('fixture_not_found', $e->errorCode);
            $this->assertSame('req_123', $e->requestId);
            $this->assertSame(404, $e->status);
            $this->assertSame('api_error', $e->type);
            $this->assertSame('id', $e->param);
            $this->assertSame('Nope.', $e->getMessage());
        }
    }

    public function test_401_throws_authentication_exception(): void
    {
        Http::fake(['*' => Http::response($this->error('invalid_api_key'), 401)]);

        $this->expectException(AuthenticationException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_403_insufficient_plan_throws_plan_exception(): void
    {
        Http::fake(['*' => Http::response($this->error('insufficient_plan'), 403)]);

        $this->expectException(PlanException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_403_other_code_throws_authentication_exception(): void
    {
        Http::fake(['*' => Http::response($this->error('forbidden'), 403)]);

        $this->expectException(AuthenticationException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_400_throws_invalid_request(): void
    {
        Http::fake(['*' => Http::response($this->error('bad_param'), 400)]);

        $this->expectException(InvalidRequestException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_422_throws_invalid_request(): void
    {
        Http::fake(['*' => Http::response($this->error('bad_param'), 422)]);

        $this->expectException(InvalidRequestException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_standings_not_available_is_invalid_request_with_error_code(): void
    {
        Http::fake(['*' => Http::response($this->error('standings_not_available'), 400)]);

        try {
            $this->client()->raw('v1/standings');
            $this->fail('Expected InvalidRequestException.');
        } catch (InvalidRequestException $e) {
            $this->assertSame('standings_not_available', $e->errorCode);
        }
    }

    public function test_500_throws_football_exception(): void
    {
        Http::fake(['*' => Http::response($this->error('server_error'), 500)]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected FootballException.');
        } catch (FootballException $e) {
            $this->assertSame(FootballException::class, $e::class);
            $this->assertSame(500, $e->status);
        }
    }

    public function test_error_without_body_message_uses_http_status(): void
    {
        Http::fake(['*' => Http::response(['success' => 0], 502)]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected FootballException.');
        } catch (FootballException $e) {
            $this->assertSame('HTTP 502', $e->getMessage());
            $this->assertNull($e->errorCode);
        }
    }

    public function test_2xx_with_unsuccessful_body_throws(): void
    {
        Http::fake(['*' => Http::response(['success' => 0, 'data' => []], 200)]);

        $this->expectException(FootballException::class);

        $this->client()->raw('v1/fixtures');
    }

    public function test_non_array_body_is_invalid_response(): void
    {
        Http::fake(['*' => Http::response('not json', 200)]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected FootballException.');
        } catch (FootballException $e) {
            $this->assertSame('invalid_response', $e->errorCode);
        }
    }

    public function test_connection_failure_becomes_football_exception(): void
    {
        $original = new ConnectionException('down');

        Http::fake(fn () => throw $original);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected FootballException.');
        } catch (FootballException $e) {
            $this->assertSame('connection_failed', $e->errorCode);
            $this->assertSame($original, $e->getPrevious());
        }
    }

    public function test_429_with_long_retry_after_throws_without_second_request(): void
    {
        Http::fake([
            '*' => Http::response($this->error('rate_limited'), 429, [
                'Retry-After' => '30',
                'X-RateLimit-Limit' => '10',
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => '1783814400',
            ]),
        ]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected RateLimitException.');
        } catch (RateLimitException $e) {
            $this->assertSame(30, $e->retryAfter);
            $this->assertSame(10, $e->limit);
            $this->assertSame(0, $e->remaining);
            $this->assertSame(1783814400, $e->reset);
            $this->assertSame('rate_limited', $e->errorCode);
            $this->assertSame(429, $e->status);
        }

        Http::assertSentCount(1);
    }

    public function test_429_with_http_date_beyond_cap_throws_without_second_request(): void
    {
        Http::fake([
            '*' => Http::response($this->error('rate_limited'), 429, [
                'Retry-After' => gmdate('D, d M Y H:i:s', time() + 7200).' GMT',
            ]),
        ]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected RateLimitException.');
        } catch (RateLimitException $e) {
            $this->assertGreaterThan(7000, $e->retryAfter);
        }

        Http::assertSentCount(1);
    }

    public function test_429_with_unreadable_retry_after_throws_with_null_retry_after(): void
    {
        Http::fake([
            '*' => Http::response($this->error('rate_limited'), 429, ['Retry-After' => 'soon-ish']),
        ]);

        try {
            $this->client()->raw('v1/fixtures');
            $this->fail('Expected RateLimitException.');
        } catch (RateLimitException $e) {
            $this->assertNull($e->retryAfter);
        }

        Http::assertSentCount(1);
    }

    public function test_429_with_zero_retry_after_retries_immediately(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push($this->error('rate_limited'), 429, ['Retry-After' => '0'])
                ->push(['success' => 1, 'data' => ['ok' => true]]),
        ]);

        $result = $this->client()->raw('v1/fixtures');

        $this->assertSame(['ok' => true], $result['data']);
        Http::assertSentCount(2);
    }

    public function test_429_with_one_second_retry_after_retries_once(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push($this->error('rate_limited'), 429, ['Retry-After' => '1'])
                ->push(['success' => 1, 'data' => ['ok' => true]]),
        ]);

        $result = $this->client()->raw('v1/fixtures');

        $this->assertSame(['ok' => true], $result['data']);
        Http::assertSentCount(2);
    }

    public function test_second_429_after_retry_throws(): void
    {
        Http::fake([
            '*' => Http::response($this->error('rate_limited'), 429, ['Retry-After' => '0']),
        ]);

        $this->expectException(RateLimitException::class);

        try {
            $this->client()->raw('v1/fixtures');
        } finally {
            Http::assertSentCount(2);
        }
    }

    public function test_ttl_key_caches_payload(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => [['id' => 1]]])]);

        $first = $this->client()->raw('v1/countries', [], 'countries');
        $second = $this->client()->raw('v1/countries', [], 'countries');

        $this->assertSame($first, $second);
        Http::assertSentCount(1);

        $key = config('football.cache.prefix').'|'.hash('sha256', 'fb_live_test').'|GET|v1/countries|'
            .json_encode([]);
        $stored = Cache::store()->get($key);

        $this->assertIsArray($stored);
        $this->assertArrayHasKey('data', $stored);
        $this->assertArrayHasKey('pagination', $stored);
        $this->assertSame([['id' => 1]], $stored['data']);
    }

    public function test_missing_data_on_single_resource_is_invalid_response(): void
    {
        Http::fake(['*' => Http::response(['success' => 1])]);

        try {
            (new Football('fb_live_test', (string) config('football.base_url'), 10))->status();
            $this->fail('Expected FootballException.');
        } catch (FootballException $e) {
            $this->assertSame('invalid_response', $e->errorCode);
        }
    }

    public function test_cache_key_includes_api_key(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => []])]);

        $this->client('fb_live_one')->raw('v1/countries', [], 'countries');
        $this->client('fb_live_two')->raw('v1/countries', [], 'countries');

        Http::assertSentCount(2);
    }

    public function test_cache_key_ignores_query_order(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => []])]);

        $this->client()->raw('v1/leagues', ['a' => 1, 'b' => 2], 'leagues');
        $this->client()->raw('v1/leagues', ['b' => 2, 'a' => 1], 'leagues');

        Http::assertSentCount(1);
    }

    public function test_disabled_cache_is_not_read_or_written(): void
    {
        config(['football.cache.enabled' => false]);
        Http::fake(['*' => Http::response(['success' => 1, 'data' => []])]);

        $this->client()->raw('v1/countries', [], 'countries');
        $this->client()->raw('v1/countries', [], 'countries');

        Http::assertSentCount(2);

        config(['football.cache.enabled' => true]);
        $this->client()->raw('v1/countries', [], 'countries');

        Http::assertSentCount(3);
    }

    public function test_null_ttl_key_does_not_cache(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => []])]);

        $this->client()->raw('v1/countries');
        $this->client()->raw('v1/countries');

        Http::assertSentCount(2);
    }
}
