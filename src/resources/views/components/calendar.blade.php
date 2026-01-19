<div 
    class="calendar-component w-full"
    x-data="calendarSwipe()"
    @touchstart.passive="handleTouchStart($event)"
    @touchend.passive="handleTouchEnd($event)"
>
    <!-- Calendar header -->
    <div class="calendar-header flex items-center justify-between mb-4 px-2 sm:px-0">
        <!-- Loading indicator for header -->
        @if ($isLoading)
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-200 dark:bg-blue-800 overflow-hidden">
                <div class="h-full bg-blue-600 dark:bg-blue-400 animate-pulse"></div>
            </div>
        @endif
        @if (isset($header))
            {{ $header }}
        @else
            <div class="flex items-center space-x-2 sm:space-x-4 w-full justify-center">
                <button type="button" wire:click="previousMonth"
                    class="cursor-pointer p-2 sm:p-2 min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 active:scale-95 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <h2 class="text-base sm:text-xl font-semibold text-gray-800 dark:text-gray-200 min-w-[140px] sm:min-w-[180px] text-center">
                    {{ $this->calendarData["monthName"] }}</h2>
                <button type="button" wire:click="nextMonth"
                    class="cursor-pointer p-2 sm:p-2 min-w-[44px] min-h-[44px] flex items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 active:scale-95 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Swipe hint for mobile -->
    <div class="sm:hidden text-center text-xs text-gray-400 dark:text-gray-500 mb-2">
        <span>← Deslize para navegar →</span>
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
        
        <!-- Weekday header - Responsive -->
        <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-800">
            @foreach ($this->weekdays as $weekday)
                <div class="py-2 sm:py-3 text-center text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{-- Mobile: show first letter only --}}
                    <span class="sm:hidden">{{ mb_substr($weekday, 0, 1) }}</span>
                    {{-- Desktop: show full abbreviation --}}
                    <span class="hidden sm:inline">{{ $weekday }}</span>
                </div>
            @endforeach
        </div>

        <!-- Calendar days -->
        <div class="calendar-weeks {{ $mobileView }} {{ $isLoading ? 'opacity-50 pointer-events-none' : '' }}">
            @foreach ($this->calendarData["weeks"] as $week)
                <div class="grid grid-cols-7 border-t border-gray-200 dark:border-gray-700">
                    @foreach ($week as $day)
                        <div
                            wire:click="openDayModal('{{ $day["date"] }}')"
                            class="calendar-day min-h-[60px] sm:min-h-[100px] p-1 sm:p-2 border-r border-gray-200 dark:border-gray-700 last:border-r-0 relative cursor-pointer
                                hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                                {{ $day["isCurrentMonth"] ? "" : "bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-600" }}
                                {{ $day["isToday"] ? "bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500 ring-inset" : "" }}">
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
        /* Base calendar styles */
        .calendar-component {
            touch-action: pan-y pinch-zoom;
        }

        /* Smooth transitions */
        .calendar-grid {
            transition: opacity 0.2s ease-in-out;
        }

        .calendar-day {
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .calendar-day:active {
            transform: scale(0.98);
        }

        /* Today indicator animation */
        .calendar-day.ring-2 {
            animation: today-pulse 2s ease-in-out infinite;
        }

        @keyframes today-pulse {
            0%, 100% { box-shadow: inset 0 0 0 2px rgb(59, 130, 246); }
            50% { box-shadow: inset 0 0 0 3px rgb(59, 130, 246); }
        }

        /* Small screens (mobile) */
        @media (max-width: 640px) {
            .calendar-grid {
                font-size: 0.75rem;
            }

            .calendar-day {
                min-height: 60px;
                padding: 0.25rem;
            }

            /* Event badges on mobile */
            .calendar-day .text-xs {
                font-size: 0.65rem;
                padding: 0.125rem 0.25rem;
            }

            /* Stack mode */
            .calendar-weeks.stack {
                display: flex;
                flex-direction: column;
                overflow-y: auto;
                max-height: 70vh;
            }

            /* Horizontal scroll mode */
            .calendar-weeks.scroll {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .calendar-weeks.scroll .grid {
                display: inline-grid;
                min-width: 100%;
            }
        }

        /* Tablet */
        @media (min-width: 641px) and (max-width: 1024px) {
            .calendar-day {
                min-height: 80px;
            }
        }

        /* Desktop */
        @media (min-width: 1025px) {
            .calendar-day {
                min-height: 100px;
            }
        }

        /* Dark mode adjustments */
        .dark .calendar-day:active {
            background-color: rgba(55, 65, 81, 0.8);
        }
    </style>

    <!-- Alpine.js Swipe Handler -->
    <script>
        function calendarSwipe() {
            return {
                touchStartX: 0,
                touchStartY: 0,
                swiping: false,
                
                handleTouchStart(event) {
                    this.touchStartX = event.touches[0].clientX;
                    this.touchStartY = event.touches[0].clientY;
                    this.swiping = true;
                },
                
                handleTouchEnd(event) {
                    if (!this.swiping) return;
                    
                    const touchEndX = event.changedTouches[0].clientX;
                    const touchEndY = event.changedTouches[0].clientY;
                    
                    const deltaX = touchEndX - this.touchStartX;
                    const deltaY = touchEndY - this.touchStartY;
                    
                    // Only trigger if horizontal swipe is significant and larger than vertical
                    if (Math.abs(deltaX) > 60 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
                        if (deltaX > 0) {
                            // Swipe right -> previous month
                            this.$wire.previousMonth();
                        } else {
                            // Swipe left -> next month
                            this.$wire.nextMonth();
                        }
                    }
                    
                    this.swiping = false;
                }
            }
        }
    </script>
</div>

