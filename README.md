# Laravel Football SDK

An **unofficial** Laravel SDK for the 5DollarFootballAPI.

## Requirements

- PHP 8.2+ (64-bit)
- Laravel 12 or 13

## Installation

```bash
composer require football-api/laravel-sdk
```

Add your API key to `.env`:

```env
FOOTBALL_API_KEY=fb_live_your_key
```

## Usage

```php
use FootballApi\Facades\Football;

$live = Football::fixtures(status: 'live');
```

## Configuration

Publish the config file if you need to change the defaults:

```bash
php artisan vendor:publish --tag=football-config
```

## Attribution

Free, Community, and Education public apps must attribute 5DollarFootballAPI in the app itself. This package does not add attribution for you.

## Contributing

Development runs in Docker, so no local PHP or Composer is needed.

```bash
docker compose build sdk
docker compose run --rm sdk composer update --no-interaction --prefer-dist
docker compose run --rm sdk vendor/bin/pint
docker compose run --rm sdk vendor/bin/phpunit
```
