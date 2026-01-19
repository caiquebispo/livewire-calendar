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

    <!-- View Mode Toggle and Today Button -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4 px-2 sm:px-0">
        <!-- View Toggle Buttons -->
        <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="setViewMode('month')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors
                    {{ $viewMode === 'month' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Mês
            </button>
            <button type="button" wire:click="setViewMode('week')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium border-x border-gray-200 dark:border-gray-700 transition-colors
                    {{ $viewMode === 'week' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Semana
            </button>
            <button type="button" wire:click="setViewMode('day')"
                class="px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors
                    {{ $viewMode === 'day' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Dia
            </button>
        </div>

        <!-- Today Button -->
        <button type="button" wire:click="goToToday"
            class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg border border-blue-500 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
            Hoje
        </button>
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
        
        @if ($viewMode === 'month')
            <!-- MONTH VIEW -->
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
        @elseif ($viewMode === 'week')
            <!-- WEEK VIEW -->
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 text-center font-medium text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <button type="button" wire:click="previousPeriod" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span>{{ $this->weekData['weekRange'] }}</span>
                    <button type="button" wire:click="nextPeriod" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <!-- Time Grid - Week View -->
            <div class="flex flex-col h-[calc(100vh-350px)] min-h-[500px] overflow-y-auto relative bg-white dark:bg-gray-800">
                <!-- Days Header (Sticky) -->
                <div class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-30 min-w-[800px] sm:min-w-0">
                    <div class="w-16 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div> <!-- Time axis spacer -->
                    @foreach ($this->weekData['days'] as $day)
                        <div class="text-center py-2 text-xs sm:text-sm font-medium border-r border-gray-200 dark:border-gray-700 last:border-r-0 col-span-1
                            {{ $day['isToday'] ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                            <span class="block {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $day['dayName'] }}</span>
                            <span class="block text-lg font-semibold {{ $day['isToday'] ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300' }}">{{ $day['day'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-auto relative min-w-[800px] sm:min-w-0">
                    <!-- Time Axis -->
                    <div class="w-16 flex-none border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 z-20 sticky left-0">
                        @foreach ($this->gridHours as $hour)
                            <div class="h-[60px] text-right pr-2 text-xs text-gray-400 dark:text-gray-500 relative border-b border-gray-100 dark:border-gray-700/50">
                                <span class="relative -top-2">{{ $hour }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Days Columns -->
                    <div class="grid grid-cols-7 flex-auto relative">
                        <!-- Horizontal Grid Lines (Background) -->
                        <div class="absolute inset-0 z-0 select-none pointer-events-none col-span-7">
                            @foreach ($this->gridHours as $hour)
                                <div class="h-[60px] border-b border-gray-100 dark:border-gray-700/50 w-full"></div>
                            @endforeach
                        </div>
                        
                        <!-- Columns -->
                        @foreach ($this->weekData['days'] as $day)
                            <div class="relative border-r border-gray-100 dark:border-gray-700/50 last:border-r-0 h-full min-h-[1440px]" 
                                wire:click="selectDate('{{ $day['date'] }}')">
                                
                                <!-- Current Time Indicator (if today) -->
                                @if ($day['isToday'])
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $minutes = $now->hour * 60 + $now->minute;
                                        $percent = ($minutes / 1440) * 100;
                                    @endphp
                                    <div class="absolute w-full border-t-2 border-red-500 z-10 pointer-events-none" style="top: {{ $percent }}%">
                                        <div class="absolute -left-1 -top-1 w-2 h-2 bg-red-500 rounded-full"></div>
                                    </div>
                                @endif

                                <!-- Events -->
                                @foreach ($day['events'] as $event)
                                    <div wire:click.stop="eventClicked({{ $event['id'] }})"
                                        class="absolute left-0.5 right-1 rounded sm:rounded-md border text-[10px] sm:text-xs overflow-hidden cursor-pointer hover:shadow-md transition-all z-10 opacity-90 hover:opacity-100 hover:z-20
                                            {{ isset($event['color']) ? 'bg-'.$event['color'].'-100 border-'.$event['color'].'-200 text-'.$event['color'].'-800 dark:bg-'.$event['color'].'-900/60 dark:border-'.$event['color'].'-700 dark:text-'.$event['color'].'-200' : 'bg-blue-100 border-blue-200 text-blue-800 dark:bg-blue-900/60 dark:border-blue-700 dark:text-blue-200' }}"
                                        style="top: {{ $event['top'] }}; height: {{ $event['height'] }}; min-height: 20px;">
                                        <div class="p-0.5 sm:p-1 font-semibold truncate leading-tight">{{ $event['title'] }}</div>
                                        <div class="hidden sm:block px-1 truncate opacity-75 text-[9px]">{{ $event['start_formatted'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- DAY VIEW -->
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <button type="button" wire:click="previousPeriod" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="text-center">
                        <span class="block text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $this->dayData['dayName'] }}</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $this->dayData['fullDate'] }}</span>
                    </div>
                    <button type="button" wire:click="nextPeriod" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <!-- Time Grid - Day View -->
            <div class="flex flex-col h-[calc(100vh-300px)] min-h-[500px] overflow-y-auto relative bg-white dark:bg-gray-800">
                <div class="flex flex-auto relative">
                    <!-- Time Axis -->
                    <div class="w-16 flex-none border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 z-10 sticky left-0">
                        @foreach ($this->gridHours as $hour)
                            <div class="h-[60px] text-right pr-2 text-xs text-gray-400 dark:text-gray-500 relative border-b border-gray-100 dark:border-gray-700/50">
                                <span class="relative -top-2">{{ $hour }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Events Container -->
                    <div class="flex-auto relative min-w-0">
                        <!-- Horizontal Grid Lines -->
                        <div class="absolute inset-0 z-0 select-none pointer-events-none">
                            @foreach ($this->gridHours as $hour)
                                <div class="h-[60px] border-b border-gray-100 dark:border-gray-700/50 w-full"></div>
                            @endforeach
                        </div>

                        <!-- Current Time Indicator -->
                        @if ($this->dayData['isToday'])
                            @php
                                $now = \Carbon\Carbon::now();
                                $minutes = $now->hour * 60 + $now->minute;
                                $percent = ($minutes / 1440) * 100;
                            @endphp
                            <div class="absolute w-full border-t-2 border-red-500 z-20 pointer-events-none" style="top: {{ $percent }}%">
                                <div class="absolute -left-2 -top-1.5 w-3 h-3 bg-red-500 rounded-full"></div>
                            </div>
                        @endif

                        <!-- Events -->
                        @foreach ($this->dayData['events'] as $event)
                            <div wire:click.stop="eventClicked({{ $event['id'] }})"
                                class="absolute left-1 right-2 rounded-md border text-xs overflow-hidden cursor-pointer hover:shadow-md transition-all z-10
                                    {{ isset($event['color']) ? 'bg-'.$event['color'].'-100 border-'.$event['color'].'-200 text-'.$event['color'].'-800 dark:bg-'.$event['color'].'-900/40 dark:border-'.$event['color'].'-700 dark:text-'.$event['color'].'-200' : 'bg-blue-100 border-blue-200 text-blue-800 dark:bg-blue-900/40 dark:border-blue-700 dark:text-blue-200' }}"
                                style="top: {{ $event['top'] }}; height: {{ $event['height'] }}; min-height: 24px;">
                                <div class="p-1 font-semibold truncate">{{ $event['title'] }}</div>
                                <div class="px-1 truncate opacity-75 text-[10px]">{{ $event['start_formatted'] }} - {{ $event['end_formatted'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($viewMode === 'month')

        <!-- Calendar days -->
        <!-- Calendar days -->
        <div class="calendar-weeks {{ $mobileView }} {{ $isLoading ? 'opacity-50 pointer-events-none' : '' }}">
            @foreach ($this->calendarData["weeks"] as $weekData)
                @php
                    $week = $weekData['days'];
                    $multiDayEvents = $weekData['multiDayEvents'];
                @endphp
                <div class="relative border-t border-gray-200 dark:border-gray-700">
                    <!-- Week Grid Background (Click Targets & Day Numbers) -->
                    <div class="grid grid-cols-7 relative z-10">
                        @foreach ($week as $day)
                            <div
                                class="calendar-day min-h-[80px] sm:min-h-[120px] p-1 sm:p-2 border-r border-gray-200 dark:border-gray-700 last:border-r-0
                                    hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                                    {{ $day["isCurrentMonth"] ? "" : "bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-600" }}
                                    {{ $day["isToday"] ? "bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-500 ring-inset" : "" }}">
                                
                                <!-- Day Header -->
                                <div class="flex justify-between items-start">
                                    <div class="text-sm font-medium {{ $day["isCurrentMonth"] ? "text-gray-900 dark:text-gray-100" : "text-gray-400 dark:text-gray-600" }}">
                                        {{ $day["day"] }}
                                    </div>
                                    @if(count($day["events"]) > 0 && !isset($day['events'][0]['is_multiday']))
                                        <!-- Dot indicator for single events (optional or mixed view) -->
                                        <!-- Currently we render single events below, but filter multi-days -->
                                    @endif
                                </div>

                                <!-- Standard Events List (Single Day) -->
                                <div class="mt-6 space-y-1 relative z-20">
                                    @php
                                        // Filter out multi-day events from standard list to avoid duplication
                                        // (Assuming implementation marks standard events)
                                        // For now, only show events that are NOT multiday in the list
                                        $singleDayEvents = array_filter($day['events'], fn($e) => !($e['is_multiday'] ?? false));
                                    @endphp
                                    @foreach (array_slice($singleDayEvents, 0, 3) as $event)
                                        <div wire:click="eventClicked({{ $event["id"] }})"
                                            class="text-xs p-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 truncate cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-700">
                                            <span class="w-1.5 h-1.5 rounded-full {{ isset($event['color']) ? 'bg-'.$event['color'].'-500' : 'bg-blue-500' }} inline-block mr-1"></span>
                                            {{ $event["title"] }}
                                        </div>
                                    @endforeach
                                    
                                    @if (count($singleDayEvents) > 3)
                                        <button type="button" wire:click="openDayModal('{{ $day["date"] }}')"
                                            class="text-[10px] text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                            + {{ count($singleDayEvents) - 3 }} mais
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Multi-day Events Overlay -->
                    <div class="absolute top-[28px] left-0 right-0 grid grid-cols-7 z-20 pointer-events-none">
                        <!-- Rows -->
                        @foreach ($multiDayEvents as $event)
                            <div class="col-start-{{ $event['startCol'] }} col-span-{{ $event['span'] }} h-5 mb-1 px-1 pointer-events-auto"
                                style="margin-top: {{ $event['row'] * 24 }}px;">
                                <div wire:click="eventClicked({{ $event['id'] }})"
                                    class="h-full rounded text-xs px-2 flex items-center shadow-sm cursor-pointer whitespace-nowrap overflow-hidden
                                    {{ isset($event['color']) ? 'bg-'.$event['color'].'-500 text-white' : 'bg-blue-500 text-white' }}
                                    hover:brightness-95 transition">
                                    <span class="font-semibold">{{ $event['title'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- See more modal - Modern Design -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop overlay -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
            
            <!-- Modal container -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <!-- Modal content -->
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all">
                    <!-- Close button -->
                    <button type="button" wire:click="closeModal" 
                        class="absolute top-4 right-4 p-1 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    
                    <!-- Header -->
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">
                                    Eventos do dia
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($selectedDay)->translatedFormat('d \d\e F \d\e Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Events list -->
                    <div class="px-6 pb-4 max-h-[300px] overflow-y-auto">
                        @if (count($selectedDayEvents) > 0)
                            <div class="space-y-2">
                                @foreach ($selectedDayEvents as $event)
                                    <div wire:click="eventClicked({{ $event['id'] }})"
                                        class="p-3 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-200 dark:border-blue-700 cursor-pointer hover:shadow-md transition-shadow">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $event['title'] }}</span>
                                        </div>
                                        @if (isset($event['time']))
                                            <span class="ml-5 text-sm text-gray-500 dark:text-gray-400">{{ $event['time'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p>Nenhum evento para este dia</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
                        <button type="button" wire:click="closeModal"
                            class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 cursor-pointer">
                            Fechar
                        </button>
                    </div>
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

