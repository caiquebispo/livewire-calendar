# Livewire Calendar

Lightweight and customizable calendar component for Laravel Livewire 3, styled with TailwindCSS. Focused on simplicity, extensibility, and DX.
<p align="center">
  <a href="https://packagist.org/packages/caiquebispo/livewire-calendar"><img src="http://poser.pugx.org/caiquebispo/livewire-calendar/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/livewire-calendar"><img src="http://poser.pugx.org/caiquebispo/livewire-calendar/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/caiquebispo/livewire-calendar"><img src="http://poser.pugx.org/caiquebispo/livewire-calendar/v/unstable" alt="Latest Unstable Version"></a>
  <a href="https://packagist.org/packages/caiquebispo/livewire-calendar"><img src="http://poser.pugx.org/caiquebispo/livewire-calendar/license" alt="License"></a>
  <a href="https://packagist.org/packages/caiquebispo/livewire-calendar"><img src="http://poser.pugx.org/caiquebispo/livewire-calendar/require/php" alt="PHP Version Require"></a>
</p>
## Requisitos

- PHP 8.2+
- Laravel 10+
- Livewire 3+
- TailwindCSS 3+

## Installation

```bash
composer require caiquebispo/livewire-calendar
```

## Publishing

You can publish assets, views and config:

```bash
# Publish all
php artisan vendor:publish --provider="CaiqueBispo\Calendar\Provider\CalendarServiceProvider"

# Only config
php artisan vendor:publish --provider="CaiqueBispo\Calendar\Provider\CalendarServiceProvider" --tag="config"

# Only views
php artisan vendor:publish --provider="CaiqueBispo\Calendar\Provider\CalendarServiceProvider" --tag="views"

# Only assets
php artisan vendor:publish --provider="CaiqueBispo\Calendar\Provider\CalendarServiceProvider" --tag="assets"
```

## Basic usage

```php
// In your controller or Livewire component
public function render()
{
    $events = [
        [
            "day" => "2025-09-04",
            "data" => [
                ["title" => "Reunião", "id" => 1],
                ["title" => "Entrega", "id" => 2]
            ]
        ],
        [
            "day" => "2025-09-10",
            "data" => [
                ["title" => "Viagem", "id" => 3]
            ]
        ]
    ];
    
    return view('your-view', ['events' => $events]);
}
```

```blade
<!-- In your Blade -->
<livewire:calendar 
    :events="$events" 
    :lazy-load-events="true" 
    :max-items-per-day="2" 
/>
```

## Customization

The calendar offers multiple customization points:

### Props

- `events`: Event array
- `lazy-load-events`: If true, emits `calendar:month-changed` when month changes (useful for lazy loading)
- `max-items-per-day`: Max visible items per day (rest shown in modal)
- `day-cell-view`: Blade view path to customize day cell (e.g. `calendar.custom-day-cell`)
- `mobile-view`: Mobile behavior for weeks layout: `stack` (default) or `scroll`

### Slots

Available slots:

```blade
<livewire:calendar :events="$events">
    <!-- Custom header -->
    <x-slot:header>
        <!-- Conteúdo personalizado do cabeçalho -->
    </x-slot:header>
    
    <!-- The old day slot was replaced by day-cell-view. Prefer the Blade partial via `day-cell-view` or `calendar.day_cell_view`. -->
    
    <!-- Custom "see more" modal -->
    <x-slot:modal>
        <!-- Conteúdo personalizado do modal -->
    </x-slot:modal>
</livewire:calendar>
```

### Customizing day cell via Blade partial

You can provide a view to render each day cell. Two options:

1) Pass on component usage:

```blade
<livewire:calendar
    :events="$events"
    day-cell-view="calendar.custom-day-cell"
    mobile-view="scroll"
/>
```

2) Define globally in package config (`day_cell_view`):

```php
// packages/caiquebispo/livewire-calendar/src/config/calendar.php
'day_cell_view' => 'calendar.custom-day-cell',
// 'mobile_view' => 'scroll',
```

The view receives: `date`, `dayNumber`, `isCurrentMonth`, `isToday`, `events`, `maxItemsPerDay`.

Example in `resources/views/calendar/custom-day-cell.blade.php`:

```blade
<div class="flex flex-col h-full">
    <div class="text-sm font-semibold mb-1">{{ $dayNumber }}</div>
    <div class="space-y-1">
        @foreach(array_slice($events, 0, $maxItemsPerDay) as $event)
            <div wire:click="eventClicked({{ $event['id'] }})" class="text-xs p-1 rounded bg-green-100 text-green-800 truncate cursor-pointer">
                {{ $event['title'] }}
            </div>
        @endforeach
        @if(count($events) > $maxItemsPerDay)
            <button type="button" wire:click="openDayModal('{{ $date }}')" class="text-xs p-1 text-center w-full rounded bg-gray-100 text-gray-700">
                + {{ count($events) - $maxItemsPerDay }} mais
            </button>
        @endif
    </div>
</div>
```

## Events

Emitted Livewire events:

- `calendar:month-changed`: when the month changes (useful for lazy loading)
- `calendar:day-clicked`: when a day is clicked
- `calendar:event-clicked`: when an event is clicked

## Dark mode

Fully Tailwind dark mode compatible via `dark` class on the root element.

## Licença

Este pacote é open-source e está disponível sob a [licença MIT](LICENSE.md).