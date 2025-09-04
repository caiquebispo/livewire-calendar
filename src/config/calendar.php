<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Calendar Display Settings
    |--------------------------------------------------------------------------
    */

    // Maximum number of visible events per day (additional events will be shown in the "View more" modal)
    'max_items_per_day' => 2,

    // If true, loads events when changing months (emits calendar:month-changed event)
    'lazy_load_events' => true,

    // Date format used for day display
    'date_format' => 'd',

    // Date format used for month display
    'month_format' => 'F Y',

    // Weekday names (can be customized for other languages)
    'weekdays' => [
        'Sun',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat'
    ],

    // First day of the week (0 = Sunday, 1 = Monday, etc.)
    'first_day_of_week' => 0,

    // Optional Blade view to customize the day cell (e.g., 'calendar.custom-day-cell')
    'day_cell_view' => null,

    /*
    |--------------------------------------------------------------------------
    | Responsiveness Settings
    |--------------------------------------------------------------------------
    */

    // Breakpoints for responsiveness
    'breakpoints' => [
        'sm' => 640,  // Small screens (mobile)
        'md' => 768,  // Tablets
        'lg' => 1024, // Desktops
        'xl' => 1280, // Large screens
    ],

    // Behavior on small screens
    'mobile_view' => 'stack', // Options: 'stack' (stack weeks) or 'scroll' (horizontal scroll)

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    */

    // Light theme colors
    'light_theme' => [
        'background' => 'bg-white',
        'text' => 'text-gray-800',
        'border' => 'border-gray-200',
        'today' => 'bg-blue-50',
        'event' => 'bg-blue-100 text-blue-800',
        'hover' => 'hover:bg-gray-50',
    ],

    // Dark theme colors
    'dark_theme' => [
        'background' => 'dark:bg-gray-800',
        'text' => 'dark:text-gray-200',
        'border' => 'dark:border-gray-700',
        'today' => 'dark:bg-blue-900',
        'event' => 'dark:bg-blue-800 dark:text-blue-100',
        'hover' => 'dark:hover:bg-gray-700',
    ],
];
