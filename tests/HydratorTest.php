<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Exceptions\FootballException;
use FootballApi\Resources\Scoreline;
use FootballApi\Resources\Team;
use FootballApi\Support\Contract;
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

    public function test_scoreline_leaves_absent_halves_null(): void
    {
        $score = Scoreline::fromArray(['home' => 1, 'away' => 0]);

        $this->assertSame(1, $score->home);
        $this->assertNull($score->halfHome);
    }
}
