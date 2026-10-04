<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Exceptions\FootballException;
use FootballApi\Resources\Scoreline;
use FootballApi\Resources\Team;
use FootballApi\Support\Contract;
use FootballApi\Support\Hydrator;
use PHPUnit\Framework\AssertionFailedError;

class HydratorTest extends TestCase
{
    public function test_unknown_json_key_is_ignored_at_runtime(): void
    {
        $team = Team::fromArray(['id' => 7, 'name' => 'Arsenal', 'extra' => true]);

        $this->assertSame(7, $team->id);
        $this->assertSame('Arsenal', $team->name);
    }

    public function test_contract_fails_on_an_unknown_key(): void
    {
        $this->expectException(AssertionFailedError::class);

        Contract::assertMapped(
            ['id' => 7, 'name' => 'Arsenal', 'extra' => true],
            Team::keys(),
        );
    }

    public function test_missing_required_key_throws(): void
    {
        $this->expectException(FootballException::class);

        try {
            Team::fromArray(['name' => 'Arsenal']);
        } catch (FootballException $exception) {
            $this->assertSame('invalid_response', $exception->errorCode);
            throw $exception;
        }
    }

    public function test_list_item_that_is_not_an_array_throws_invalid_response(): void
    {
        try {
            Hydrator::read(['teams' => ['nope']], ['teams' => ['list' => Team::class, 'required' => true]]);
            $this->fail('Expected FootballException.');
        } catch (FootballException $exception) {
            $this->assertSame('invalid_response', $exception->errorCode);
        }
    }

    public function test_unparseable_datetime_throws_invalid_response(): void
    {
        try {
            Hydrator::read(['at' => 'not a date at all'], ['at' => ['type' => 'datetime', 'required' => true]]);
            $this->fail('Expected FootballException.');
        } catch (FootballException $exception) {
            $this->assertSame('invalid_response', $exception->errorCode);
        }
    }

    public function test_non_scalar_int_throws_invalid_response(): void
    {
        try {
            Team::fromArray(['id' => [1], 'name' => 'Arsenal']);
            $this->fail('Expected FootballException.');
        } catch (FootballException $exception) {
            $this->assertSame('invalid_response', $exception->errorCode);
        }
    }

    public function test_scoreline_leaves_absent_halves_null(): void
    {
        $score = Scoreline::fromArray(['home' => 1, 'away' => 0]);

        $this->assertSame(1, $score->home);
        $this->assertNull($score->halfHome);
    }
}
