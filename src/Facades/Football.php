<?php

declare(strict_types=1);

namespace FootballApi\Facades;

use Illuminate\Support\Facades\Facade;

class Football extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FootballApi\Football::class;
    }
}
