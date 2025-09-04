<?php

namespace CaiqueBispo\Calendar\Provider;

use CaiqueBispo\Calendar\Calendar;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\File;

class CalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'calendar');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/calendar.php' => config_path('calendar.php'),
        ], 'config');

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/calendar'),
        ], 'views');

        // Publish assets (CSS/JS)
        $this->publishes([
            __DIR__ . '/../resources/js' => public_path('vendor/calendar'),
            __DIR__ . '/../resources/css' => public_path('vendor/calendar'),
        ], 'calendar-assets');

        // Publish all assets together
        $this->publishes([
            __DIR__ . '/../config/calendar.php' => config_path('calendar.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/calendar'),
            __DIR__ . '/../resources/js' => public_path('vendor/calendar'),
            __DIR__ . '/../resources/css' => public_path('vendor/calendar'),
        ], 'calendar-all');

        // Publish only views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/calendar'),
        ], 'calendar-views');

        // Publish only configuration
        $this->publishes([
            __DIR__ . '/../config/calendar.php' => config_path('calendar.php'),
        ], 'calendar-config');

        // Register Livewire component
        Livewire::component('calendar', Calendar::class);
    }

    public function register(): void
    {
        // Merge configurations
        $this->mergeConfigFrom(
            __DIR__ . '/../config/calendar.php',
            'calendar'
        );

        // Register installation commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // You can add custom commands here if needed
            ]);
        }
    }
}
