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
     * Inicializa o componente
     */
    public function mount(
        array $events = [],
        bool $lazyLoadEvents = null,
        int $maxItemsPerDay = null,
        string $dayCellView = null,
        string $mobileView = null
    ): void {
        $this->events = $events;
        $this->currentMonth = Carbon::now()->format('Y-m');

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

        return [
            'weeks' => $weeks,
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
     * Navega para o mês anterior
     */
    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->subMonth()
            ->format('Y-m');

        if ($this->lazyLoadEvents) {
            $this->dispatch('calendar:month-changed', month: $this->currentMonth);
        }
    }

    /**
     * Navega para o próximo mês
     */
    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->addMonth()
            ->format('Y-m');

        if ($this->lazyLoadEvents) {
            $this->dispatch('calendar:month-changed', month: $this->currentMonth);
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
     * Renderiza o componente
     */
    public function render(): View
    {
        return view('calendar::components.calendar');
    }
}
