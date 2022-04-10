<?php

namespace CommunityInfoCoop\NewsHarvester\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use CommunityInfoCoop\NewsHarvester\Tests\Models\User;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use CommunityInfoCoop\NewsHarvester\NewsHarvesterServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CommunityInfoCoop\\NewsHarvester\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            NewsHarvesterServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);

        $migration = include __DIR__.'/../database/migrations/create_news_harvester_tables.php.stub';
        $migration->up();
    }
}
