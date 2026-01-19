<?php

namespace CaiqueBispo\Calendar;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Calendar extends Component
{
    /**
     * Array de eventos para exibir no calendário
     * Formato: [
     *   [
     *     "day" => "2025-09-04",
     *     "data" => [
     *       ["title" => "Reunião", "id" => 1],
     *       ["title" => "Entrega", "id" => 2]
     *     ]
     *   ],
     *   [...]
     * ]
     */
    public array $events = [];

    /**
     * Mês atual sendo exibido (formato Y-m)
     */
    public string $currentMonth;

    /**
     * Se true, carrega eventos ao mudar de mês
     */
    public bool $lazyLoadEvents = true;

    /**
     * Número máximo de itens visíveis por dia
     */
    public int $maxItemsPerDay = 2;

    /**
     * Dia selecionado para o modal "Ver mais"
     */
    public ?string $selectedDay = null;

    /**
     * Eventos do dia selecionado
     */
    public array $selectedDayEvents = [];

    /**
     * Modal aberto/fechado
     */
    public bool $isModalOpen = false;

    /**
     * Caminho do Blade parcial para customizar a célula do dia
     * Ex: 'calendar.day-cell'
     */
    public ?string $dayCellView = null;

    /**
     * Mobile view behavior: 'stack' or 'scroll'
     */
    public string $mobileView = 'stack';

    /**
     * Estado de carregamento durante navegação entre meses
     */
    public bool $isLoading = false;

    /**
     * View mode: 'month', 'week', or 'day'
     */
    public string $viewMode = 'month';

    /**
     * Selected date for week/day view
     */
    public string $selectedDate;

    /**
     * Listeners para eventos Livewire
     */
    protected $listeners = [
        'eventsLoaded' => 'handleEventsLoaded',
    ];

    /**
     * Inicializa o componente
     */
    public function mount(
        array $events = [],
        bool $lazyLoadEvents = null,
        int $maxItemsPerDay = null,
        string $dayCellView = null,
        string $mobileView = null,
        string $viewMode = null
    ): void {
        $this->events = $events;
        $this->currentMonth = Carbon::now()->format('Y-m');
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->viewMode = $viewMode ?? 'month';

        // Aplica configurações do arquivo config ou dos parâmetros
        $this->lazyLoadEvents = $lazyLoadEvents ?? config('calendar.lazy_load_events', true);
        $this->maxItemsPerDay = $maxItemsPerDay ?? config('calendar.max_items_per_day', 2);
        $this->dayCellView = $dayCellView;
        $this->mobileView = in_array(($mobileView ?? config('calendar.mobile_view', 'stack')), ['stack', 'scroll'], true)
            ? ($mobileView ?? config('calendar.mobile_view', 'stack'))
            : 'stack';
    }

    /**
     * Obtém o primeiro dia da semana (0 = Domingo, 1 = Segunda, etc.)
     */
    #[Computed]
    public function firstDayOfWeek(): int
    {
        return config('calendar.first_day_of_week', 0);
    }

    /**
     * Obtém os nomes dos dias da semana
     */
    #[Computed]
    public function weekdays(): array
    {
        $weekdays = config('calendar.weekdays', [
            'Dom',
            'Seg',
            'Ter',
            'Qua',
            'Qui',
            'Sex',
            'Sáb'
        ]);

        // Reorganiza os dias da semana com base no primeiro dia configurado
        if ($this->firstDayOfWeek > 0) {
            $weekdays = array_merge(
                array_slice($weekdays, $this->firstDayOfWeek),
                array_slice($weekdays, 0, $this->firstDayOfWeek)
            );
        }

        return $weekdays;
    }

    /**
     * Gera os dados do calendário para o mês atual
     */
    #[Computed]
    public function calendarData(): array
    {
        // Obtém o primeiro dia do mês atual
        $date = Carbon::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();

        // Ajusta para o primeiro dia da semana a ser exibido
        $startDate = $date->copy();
        $dayOfWeek = $startDate->dayOfWeek;
        $startDate->subDays(($dayOfWeek - $this->firstDayOfWeek + 7) % 7);

        // Obtém o último dia do mês
        $endDate = $date->copy()->endOfMonth();

        // Ajusta para o último dia da semana a ser exibido
        $dayOfWeek = $endDate->dayOfWeek;
        $endDate->addDays((6 - $dayOfWeek + $this->firstDayOfWeek) % 7);

        // Gera os dias do calendário
        $days = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('d'),
                'isCurrentMonth' => $currentDate->format('Y-m') === $this->currentMonth,
                'isToday' => $currentDate->isToday(),
                'events' => $this->getEventsForDay($currentDate->format('Y-m-d')),
            ];

            $currentDate->addDay();
        }

        // Organiza os dias em semanas
        $weeks = array_chunk($days, 7);

        // Calculate multi-day event layouts for each week
        $weeksWithEvents = [];
        foreach ($weeks as $week) {
            $weekStart = Carbon::parse($week[0]['date']);
            $weekEnd = Carbon::parse($week[6]['date'])->endOfDay();
            
            // Gather all multi-day events active in this week
            // Note: This logic iterates all events; optimization needed for production with thousands of events
            $weekEvents = [];
            foreach ($this->events as $dayEvent) {
                foreach ($dayEvent['data'] as $event) {
                    if (!isset($event['end_time'])) continue;
                    
                    $evtStart = Carbon::parse($event['start_time']);
                    $evtEnd = Carbon::parse($event['end_time']);
                    
                    // Check intersection
                    if ($evtStart <= $weekEnd && $evtEnd >= $weekStart) {
                        // Calculate start/end columns (0-6)
                        $startCol = 0;
                        if ($evtStart > $weekStart) {
                            $startCol = $evtStart->diffInDays($weekStart);
                        }
                        
                        $endCol = 6;
                        if ($evtEnd < $weekEnd) {
                            $endCol = $evtEnd->diffInDays($weekStart); // diffInDays is absolute, check direction
                            // Use diffInDays directly from weekStart
                            $endCol = $weekStart->diffInDays($evtEnd);
                        }

                        // Determine if it spans multiple days
                        $span = $endCol - $startCol + 1;
                        $isMultiDay = $span > 1 || isset($event['is_multiday']);
                        
                        if ($isMultiDay) {
                            // Find suitable row (visual stacking)
                            $row = 0;
                            // Need to track occupied rows for this week. 
                            // This is complex. Simplified approach: auto-increment for demo.
                            // Real implementation: keep track of occupied slots per row [0,0,0,0,0,0,0]
                            
                            $weekEvents[] = [
                                'id' => $event['id'],
                                'title' => $event['title'],
                                'color' => $event['color'] ?? 'blue',
                                'startCol' => $startCol + 1, // CSS Grid is 1-based
                                'span' => $span,
                                'isMultiDay' => true
                            ];
                        }
                    }
                }
            }

            // Simple collision detection (greedy) to assign rows
            // Sort by start col, then span desc
            usort($weekEvents, function($a, $b) {
                if ($a['startCol'] == $b['startCol']) return $b['span'] <=> $a['span'];
                return $a['startCol'] <=> $b['startCol'];
            });

            // Allocate rows
            // Grid of occupied cells: Matrix[row][col] = true/false
            $gridMatrix = []; 
            foreach ($weekEvents as &$evt) {
                $row = 0;
                while (true) {
                    $collision = false;
                    for ($c = $evt['startCol']; $c < $evt['startCol'] + $evt['span']; $c++) {
                        if (isset($gridMatrix[$row][$c])) {
                            $collision = true;
                            break;
                        }
                    }
                    
                    if (!$collision) {
                        // Place here
                        $evt['row'] = $row;
                        for ($c = $evt['startCol']; $c < $evt['startCol'] + $evt['span']; $c++) {
                            $gridMatrix[$row][$c] = true;
                        }
                        break;
                    }
                    $row++;
                }
            }

            $weeksWithEvents[] = [
                'days' => $week,
                'multiDayEvents' => $weekEvents
            ];
        }

        return [
            'weeks' => $weeksWithEvents,
            'monthName' => Carbon::createFromFormat('Y-m', $this->currentMonth)->format(config('calendar.month_format', 'F Y')),
        ];
    }

    /**
     * Obtém os eventos para um dia específico
     */
    protected function getEventsForDay(string $date): array
    {
        foreach ($this->events as $event) {
            if ($event['day'] === $date) {
                return $event['data'];
            }
        }

        return [];
    }

    /**
     * Set the calendar view mode
     */
    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['month', 'week', 'day']) ? $mode : 'month';
    }

    /**
     * Go to today
     */
    public function goToToday(): void
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->currentMonth = Carbon::now()->format('Y-m');
        
        if ($this->lazyLoadEvents) {
            $this->events = [];
            $this->dispatch('calendar:month-changed', month: $this->currentMonth);
        }
    }

    /**
     * Computed property for week data
     */
    #[Computed]
    public function weekData(): array
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::SUNDAY);
        
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('d'),
                'dayName' => $date->format('D'),
                'isCurrentMonth' => $date->format('Y-m') === $this->currentMonth,
                'isToday' => $date->isToday(),
                // Use new method with position data
                'events' => $this->getEventsForDayWithPosition($date->format('Y-m-d')),
            ];
        }
        
        return [
            'days' => $days,
            'weekRange' => $startOfWeek->format('d M') . ' - ' . $startOfWeek->copy()->addDays(6)->format('d M Y'),
        ];
    }

    /**
     * Computed property for day data
     */
    #[Computed]
    public function dayData(): array
    {
        $date = Carbon::parse($this->selectedDate);
        
        return [
            'date' => $date->format('Y-m-d'),
            'dayName' => $date->format('l'),
            'fullDate' => $date->format('d F Y'),
            'isToday' => $date->isToday(),
            // Use new method with position data
            'events' => $this->getEventsForDayWithPosition($date->format('Y-m-d')),
        ];
    }
    
    /**
     * Select a specific date
     */
    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->dispatch('calendar:day-clicked', date: $date);
    }

    /**
     * Navigate to previous period based on view mode
     */
    public function previousPeriod(): void
    {
        if ($this->viewMode === 'week') {
            $this->selectedDate = Carbon::parse($this->selectedDate)->subWeek()->format('Y-m-d');
        } elseif ($this->viewMode === 'day') {
            $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
        }
    }

    /**
     * Navigate to next period based on view mode
     */
    public function nextPeriod(): void
    {
        if ($this->viewMode === 'week') {
            $this->selectedDate = Carbon::parse($this->selectedDate)->addWeek()->format('Y-m-d');
        } elseif ($this->viewMode === 'day') {
            $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->format('Y-m-d');
        }
    }

    /**
     * Computed property for grid hours (00:00 - 23:00)
     */
    #[Computed]
    public function gridHours(): array
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }
        return $hours;
    }

    /**
     * Calculate event position and dimensions for the time grid
     */
    public function calculateEventPosition(array $event): array
    {
        $start = Carbon::parse($event['start_time'] ?? $event['time'] ?? '00:00');
        $end = isset($event['end_time']) 
            ? Carbon::parse($event['end_time']) 
            : $start->copy()->addHour(); // Default to 1h duration
            
        // Start minutes from midnight
        $startMinutes = $start->hour * 60 + $start->minute;
        $endMinutes = $end->hour * 60 + $end->minute;
        
        // Duration in minutes
        $duration = $endMinutes - $startMinutes;
        
        // Calculate percentages (1440 minutes in a day)
        $top = ($startMinutes / 1440) * 100;
        $height = ($duration / 1440) * 100;
        
        return [
            'top' => "{$top}%",
            'height' => "{$height}%",
            'start_formatted' => $start->format('H:i'),
            'end_formatted' => $end->format('H:i'),
            'duration_formatted' => $start->diffForHumans($end, true, true),
        ];
    }
    
    /**
     * Normalize event data with position info
     */
    protected function getEventsForDayWithPosition(string $date): array
    {
        $events = $this->getEventsForDay($date);
        
        return array_map(function ($event) {
            $position = $this->calculateEventPosition($event);
            return array_merge($event, $position);
        }, $events);
    }

    /**
     * Navega para o mês anterior
     */
    public function previousMonth(): void
    {
        $this->setLoadingState(true);
        
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->subMonth()
            ->format('Y-m');

        if ($this->lazyLoadEvents) {
            // Limpa eventos atuais antes de carregar novos
            $this->events = [];
            $this->dispatch('calendar:month-changed', month: $this->currentMonth);
        } else {
            $this->setLoadingState(false);
        }
    }

    /**
     * Navega para o próximo mês
     */
    public function nextMonth(): void
    {
        $this->setLoadingState(true);
        
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->addMonth()
            ->format('Y-m');

        if ($this->lazyLoadEvents) {
            // Limpa eventos atuais antes de carregar novos
            $this->events = [];
            $this->dispatch('calendar:month-changed', month: $this->currentMonth);
        } else {
            $this->setLoadingState(false);
        }
    }

    /**
     * Abre o modal com os eventos de um dia específico
     */
    public function openDayModal(string $date): void
    {
        $this->selectedDay = $date;
        $this->selectedDayEvents = $this->getEventsForDay($date);
        $this->isModalOpen = true;

        $this->dispatch('calendar:day-clicked', date: $date);
    }

    /**
     * Fecha o modal
     */
    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }

    /**
     * Dispara evento quando um evento é clicado
     */
    public function eventClicked(int $eventId): void
    {
        $this->dispatch('calendar:event-clicked', eventId: $eventId);
    }

    /**
     * Define o estado de carregamento
     */
    public function setLoadingState(bool $loading): void
    {
        $this->isLoading = $loading;
    }

    /**
     * Método para ser chamado quando os eventos são carregados externamente
     * Deve ser chamado pelo componente pai após carregar os eventos
     */
    public function eventsLoaded(array $events = []): void
    {
        $this->events = $events;
        $this->setLoadingState(false);
    }

    /**
     * Handler para o evento eventsLoaded vindo do componente pai
     */
    public function handleEventsLoaded($events = []): void
    {
        $this->events = $events;
        $this->setLoadingState(false);
    }

    /**
     * Renderiza o componente
     */
    public function render(): View
    {
        return view('calendar::components.calendar');
    }
}
