# Laravel pacakge for News Harvester admin tools

[![Latest Version on Packagist](https://img.shields.io/packagist/v/communityinfocoop/laravel-news-harvester.svg?style=flat-square)](https://packagist.org/packages/communityinfocoop/laravel-news-harvester)
[![Total Downloads](https://img.shields.io/packagist/dt/communityinfocoop/laravel-news-harvester.svg?style=flat-square)](https://packagist.org/packages/communityinfocoop/laravel-news-harvester)

Laravel package that provides the [Community Info Coop](https://www.infodistricts.org)'s News Harvester admin tools.

## Requirements

* PHP 8.0
* [Laravel](https://laravel.com) 9.x

### Optional

* [CrowdTangle](https://www.crowdtangle.com) account

## Installation

You can install the package via composer:

```bash
composer require communityinfocoop/laravel-news-harvester
```

### Migrations, Configurations, Views

You should publish and run the migrations with:

```bash
php artisan vendor:publish --tag="news-harvester-migrations"
php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="tags-migrations"
php artisan migrate
```

Optionally, you can publish the config files (for the main package and also for the feed and Filament admin packages) with:

```bash
php artisan vendor:publish --tag="news-harvester-config"
```

This is the contents of the published config file:

```php
return [
    'display_time_zone' => 'America/New_York',
    'feeds' => [
        'fetch_timeout' => 15, // in seconds
        'check_frequency' => 60, // default, in minutes
    ],
    'select_options' => [
        'source_types' => [
            'news' => 'News Publisher',
            'government' => 'Government Entity',
            'school' => 'School',
            'business' => 'Business',
            'organization' => 'Organization',
            'group' => 'Social Group',
            'person' => 'Individual',
        ],
        'feed_types' => [
            'rss' => 'RSS Feed',
            'facebook_group' => 'Facebook Group',
            'facebook_page' => 'Facebook Page',
        ],
    ],
    'top_sources_tag' => 'Top',
    'modules' => [
        'crowdtangle' => [
            'api_token' => env('CROWDTANGLE_API_TOKEN', ''),
            'default_source_id' => null,
            'post_fetch_timeframe' => '2 HOUR',
        ],
    ],
    'command_scheduling' => 'auto',
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="news-harvester-views"
```

### Authorization

There are a few changes to make to the model you're using for Users that can login (probably `app/Models/User`):

Add a contract `FilamentUser`:

```php
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser {
    ...
}
```

Add a function `canAccessFilament()` to determine how a user will be allowed to access the admin dashboard, for example:

```php
public function canAccessFilament(): bool
{
    return str_ends_with($this->email, '@yourdomain.com');
}
```

If you don't already have a user created to login with, you can use a command provided by Filament:

```bash
php artisan make:filament-user
```

You can add additional user, role and permission management in your Laravel application as needed. We recommend these packages:

* [laravel-permission](https://github.com/spatie/laravel-permission) - Role and permission architecture
* [filament-user](https://github.com/3x1io/filament-user) - User resource management for Filament
* [filament-shield](https://github.com/bezhansalleh/filament-shield) - Role and permission management for Filament
* [filament-breezy](https://github.com/jeffgreco13/filament-breezy) - Simple login flow and profile management for Filament users

There is a custom permission used on the Feed resource, `refresh_feed`, which controls a user's ability to initiate a refresh of an RSS feed. If you use Filament Shield, you can add support by running:

```shell
artisan permission:create-permission refresh_feed
```

and then setting `'custom_permissions' => true` in your `filament-shield.php` config file before using the `shield:generate` command again.


If you generate authorization policies for the models provided by this package (Source, Feed, NewsItem), you will need to manually add those to your `AuthServiceProvider.php` file, for example:

```php
protected $policies = [
    'App\Models\User' => 'App\Policies\UserPolicy',
    'CommunityInfoCoop\NewsHarvester\Models\Source' => 'App\Policies\SourcePolicy',
    'CommunityInfoCoop\NewsHarvester\Models\Feed' => 'App\Policies\FeedPolicy',
    'CommunityInfoCoop\NewsHarvester\Models\NewsItem' => 'App\Policies\NewsItemPolicy',
];
```

### Command Scheduling

By default several commands will be automatically added to the command schedule: 

```php
$schedule->command('newsharvest:check-feeds')->everyFifteenMinutes();
$schedule->command('newsharvest:check-bulk-feeds')->hourly();
$schedule->command('newsharvest:crowdtangle-feeds-refresh')->weekly();
```

If you wish to disable this so you can set up the schedule manually, do so in the published config file:

```php
'command_scheduling' => 'off',
```

### Feed Importing

News Harvester can import a standard [OPML](https://en.wikipedia.org/wiki/OPML) feed file for fast setup of Feeds you want to monitor.

```bash
php artisan newsharvest:import-feeds my-rss-feeds.opml
```

Note that a new Source will be created for each new Feed, unless a source already exists with a name that matches the feed.

### CrowdTangle API Authorization

If you wish to use CrowdTangle's feed of Facebook Page and Facebook Group activity, configure an API key in `.env`:

```bash
CROWDTANGLE_API_TOKEN="yourtokengoeshere"
```

You can then run an initial import of your CrowdTangle-accessible Facebook pages and groups:

```bash
php artisan newsharvest:crowdtangle-feeds-refresh
```

## Usage

Log in at `/admin` using a user account authorized to access the admin dashboard as specified above. Manage some sources, feeds and news.

### Terminology

* Source: a publisher, organization, business, individual or other entity that shares news updates
* Feed: a specific feed of news updates such as an RSS feed, Facebook Group or Facebook Page
* News Item: an individual news item

### Feed Exporting

Export all RSS feeds to an OPML file:

```bash
php artisan newsharvest:export-feeds > my-rss-feeds.opml
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Issues and pull requests are welcome.

## Credits

- [Chris Hardie](https://github.com/ChrisHardie)
- [Filament project and developers](https://filamentphp.com)
- [All Contributors](../../contributors)

## License

TBD. Please see [License File](LICENSE.md) for more information.
