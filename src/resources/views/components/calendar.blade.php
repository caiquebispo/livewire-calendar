<div class="calendar-component w-full">
    <!-- Calendar header -->
    <div class="calendar-header flex items-center justify-between mb-4">
        <!-- Loading indicator for header -->
        @if ($isLoading)
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-200 dark:bg-blue-800 overflow-hidden">
                <div class="h-full bg-blue-600 dark:bg-blue-400 animate-pulse"></div>
            </div>
        @endif
        @if (isset($header))
            {{ $header }}
        @else
            <div class="flex items-center space-x-4">
                <button type="button" wire:click="previousMonth"
                    class="cursor-pointer p-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    {{ $this->calendarData["monthName"] }}</h2>
                <button type="button" wire:click="nextMonth"
                    class="cursor-pointer p-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Calendar grid -->
    <div class="calendar-grid overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 relative">
        <!-- Loading overlay -->
        @if ($isLoading)
            <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex flex-col items-center space-y-2">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 dark:border-blue-400"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Carregando...</span>
                </div>
            </div>
        @endif
        <!-- Weekday header -->
        <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-800">
            @foreach ($this->weekdays as $weekday)
                <div class="py-2 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $weekday }}
                </div>
            @endforeach
        </div>

        <!-- Calendar days -->
        <div class="calendar-weeks {{ $mobileView }} {{ $isLoading ? 'opacity-50 pointer-events-none' : '' }}">
            @foreach ($this->calendarData["weeks"] as $week)
                <div class="grid grid-cols-7 border-t border-gray-200 dark:border-gray-700">
                    @foreach ($week as $day)
                        <div
                            class="calendar-day min-h-[100px] p-2 border-r border-gray-200 dark:border-gray-700 last:border-r-0 relative
                                {{ $day["isCurrentMonth"] ? "" : "bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-600" }}
                                {{ $day["isToday"] ? "bg-blue-50 dark:bg-blue-900/20" : "" }}">
                            @php
                                $customDayCellView = $dayCellView ?? config("calendar.day_cell_view");
                            @endphp
                            @if ($customDayCellView)
                                @include($customDayCellView, [
                                    "date" => $day["date"],
                                    "dayNumber" => $day["day"],
                                    "isCurrentMonth" => $day["isCurrentMonth"],
                                    "isToday" => $day["isToday"],
                                    "events" => $day["events"],
                                    "maxItemsPerDay" => $maxItemsPerDay,
                                ])
                            @else
                                <!-- Day number -->
                                <div
                                    class="text-sm font-medium mb-1 {{ $day["isCurrentMonth"] ? "text-gray-900 dark:text-gray-100" : "text-gray-400 dark:text-gray-600" }}">
                                    {{ $day["day"] }}
                                </div>

                                <!-- Day events -->
                                @if (count($day["events"]) > 0)
                                    <div class="space-y-1">
                                        @foreach (array_slice($day["events"], 0, $maxItemsPerDay) as $event)
                                            <div wire:click="eventClicked({{ $event["id"] }})"
                                                class="text-xs p-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 truncate cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-700">
                                                {{ $event["title"] }}
                                            </div>
                                        @endforeach

                                        <!-- See more when events overflow -->
                                        @if (count($day["events"]) > $maxItemsPerDay)
                                            <button type="button" wire:click="openDayModal('{{ $day["date"] }}')"
                                                class="text-xs p-1 text-center w-full rounded bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                                + {{ count($day["events"]) - $maxItemsPerDay }} mais
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <!-- See more modal -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    wire:click="closeModal"></div>

                <!-- Modal centering helper -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal content -->
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    @if (isset($modal) && is_callable($modal))
                        {{ $modal(["date" => $selectedDay, "events" => $selectedDayEvents]) }}
                    @else
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                        id="modal-title">
                                        Eventos em {{ \Carbon\Carbon::parse($selectedDay)->format("d/m/Y") }}
                                    </h3>
                                    <div class="mt-4 space-y-2">
                                        @if (count($selectedDayEvents) > 0)
                                            @foreach ($selectedDayEvents as $event)
                                                <div wire:click="eventClicked({{ $event["id"] }})"
                                                    class="p-2 rounded bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-700">
                                                    {{ $event["title"] }}
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-gray-500 dark:text-gray-400">Nenhum evento para este dia.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeModal"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Fechar
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Responsive styles -->
    <style>
        /* Small screens (mobile) */
        @media (max-width: 640px) {
            .calendar-grid {
                font-size: 0.75rem;
            }

            .calendar-day {
                min-height: 80px;
                padding: 0.25rem;
            }

            /* Stack mode */
            .calendar-weeks.stack {
                display: flex;
                flex-direction: column;
                overflow-y: auto;
                max-height: 80vh;
            }

            /* Horizontal scroll mode */
            .calendar-weeks.scroll {
                overflow-x: auto;
                white-space: nowrap;
            }

            .calendar-weeks.scroll .grid {
                display: inline-grid;
                min-width: 100%;
            }
        }
    </style>
</div>
