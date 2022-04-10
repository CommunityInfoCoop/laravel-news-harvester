<?php

namespace CommunityInfoCoop\NewsHarvester;

use CommunityInfoCoop\NewsHarvester\Commands\CheckBulkFeedsCommand;
use CommunityInfoCoop\NewsHarvester\Commands\CheckFeedsCommand;
use CommunityInfoCoop\NewsHarvester\Commands\ExportFeedsCommand;
use CommunityInfoCoop\NewsHarvester\Commands\ImportFeedsCommand;
use CommunityInfoCoop\NewsHarvester\Commands\RefreshCrowdtangleFeedsCommand;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\FeedResource;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource;
use CommunityInfoCoop\NewsHarvester\Filament\Widgets\LatestNewsItems;
use CommunityInfoCoop\NewsHarvester\Filament\Widgets\NewsItemChart;
use CommunityInfoCoop\NewsHarvester\Filament\Widgets\StatsOverview;
use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Filament\PluginServiceProvider;

class NewsHarvesterServiceProvider extends PluginServiceProvider
{
    /**
     * Resources for use by Filament
     * @var array|string[]
     */
    protected array $resources = [
        SourceResource::class,
        FeedResource::class,
        NewsItemResource::class,
    ];

    protected array $widgets = [
        LatestNewsItems::class,
        NewsItemChart::class,
        StatsOverview::class,
    ];

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-news-harvester')
            ->hasConfigFile([
                'news-harvester',
                'filament',
                'feeds',
                'permission',
            ])
            ->hasViews()
            ->hasMigrations([
                'create_news_harvester_tables',
                'create_permission_tables',
            ])
            ->hasCommands([
                CheckFeedsCommand::class,
                CheckBulkFeedsCommand::class,
                ImportFeedsCommand::class,
                ExportFeedsCommand::class,
                RefreshCrowdtangleFeedsCommand::class,
            ]);
    }

    public function packageConfiguring(Package $package): void
    {
        parent::packageConfiguring($package);
        Event::listen(ServingFilament::class, [$this, 'registerFilamentAssets']);
    }

    public function packageBooted(): void
    {
        parent::packageBooted();
        if ('auto' === config('news-harvester.command_scheduling')) {
            $this->scheduleCommands();
        }
    }

    public function registerFilamentAssets(): void
    {
        Filament::registerTheme(mix('css/app.css'));
    }

    protected function scheduleCommands()
    {
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('newsharvest:check-feeds')->everyFifteenMinutes();
            $schedule->command('newsharvest:check-bulk-feeds')->hourly();
            $schedule->command('newsharvest:crowdtangle-feeds-refresh')->weekly();
        });
    }
}
