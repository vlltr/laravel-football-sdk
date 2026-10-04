<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use Carbon\CarbonImmutable;
use FootballApi\Support\Hydrator;

final readonly class Fixture
{
    public function __construct(
        public int $id,
        public LeagueSummary $league,
        public Team $home,
        public Team $away,
        public CarbonImmutable $kickoffAt,
        public int $kickoffTs,
        public string $status,
        public ?string $statusReason,
        public ?string $statusCode,
        public ?int $round,
        public ?int $leagueSeasonId,
        public Scoreline $goals,
        public Scoreline $corners,
        public Cards $cards,
        public ?FixtureOdds $odds,
        /** @var list<Event>|null */
        public ?array $events,
        public ?MatchStatistics $statistics,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'id' => ['type' => 'int', 'required' => true],
            'league' => ['class' => LeagueSummary::class, 'required' => true],
            'teams' => [
                'flatten' => true,
                'shape' => [
                    'home' => ['class' => Team::class, 'required' => true],
                    'away' => ['class' => Team::class, 'required' => true],
                ],
            ],
            'kickoff_utc' => ['type' => 'datetime', 'required' => true, 'as' => 'kickoffAt'],
            'kickoff_ts' => ['type' => 'int', 'required' => true, 'as' => 'kickoffTs'],
            'status' => ['type' => 'string', 'required' => true],
            'status_reason' => ['type' => 'string', 'as' => 'statusReason'],
            'status_code' => ['type' => 'string', 'as' => 'statusCode'],
            'round' => ['type' => 'int'],
            'league_season_id' => ['type' => 'int', 'as' => 'leagueSeasonId'],
            'goals' => ['class' => Scoreline::class, 'required' => true],
            'corners' => ['class' => Scoreline::class, 'required' => true],
            'cards' => ['class' => Cards::class, 'required' => true],
            'odds' => ['class' => FixtureOdds::class],
            'events' => ['list' => Event::class],
            'statistics' => ['class' => MatchStatistics::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
