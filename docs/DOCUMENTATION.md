# Laravel Livewire Calendar - Documentação

## Índice

1. [Introdução](#introdução)
2. [Requisitos](#requisitos)
3. [Instalação](#instalação)
4. [Configuração](#configuração)
5. [Uso Básico](#uso-básico)
6. [Propriedades do Componente](#propriedades-do-componente)
7. [Eventos](#eventos)
8. [Personalização](#personalização)
9. [Responsividade](#responsividade)
10. [Exemplos](#exemplos)
11. [Suporte a Temas](#suporte-a-temas)
12. [Troubleshooting](#troubleshooting)

## Introdução

O Laravel Livewire Calendar é um componente de calendário simples e personalizável para Laravel, construído com Livewire 3 e TailwindCSS. Inspirado no FullCalendar, mas com foco em simplicidade e facilidade de personalização.

## Requisitos

- PHP 8.2 ou superior
- Laravel 10.0 ou superior
- Livewire 3.0 ou superior
- TailwindCSS 3.0 ou superior

## Instalação

### Via Composer

```bash
composer require caiquebispo/laravel-livewire-calendar
```

### Publicação de Assets

Publique os assets (CSS e JavaScript) do pacote:

```bash
php artisan vendor:publish --tag=calendar-assets
```

Publique o arquivo de configuração (opcional):

```bash
php artisan vendor:publish --tag=calendar-config
```

Publique as views para personalização (opcional):

```bash
php artisan vendor:publish --tag=calendar-views
```

## Configuração

Após publicar o arquivo de configuração, você pode personalizar várias opções no arquivo `config/calendar.php`:

```php
return [
    // Número máximo de itens a serem exibidos por dia antes de mostrar o botão "Ver mais"
    'max_items_per_day' => 3,
    
    // Ativar/desativar o carregamento lazy de eventos ao mudar de mês
    'lazy_load_events' => true,
    
    // Formato de data para exibição
    'date_format' => 'd',
    
    // Formato do mês para exibição no cabeçalho
    'month_format' => 'F Y',
    
    // Nomes dos dias da semana
    'weekday_names' => ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
    
    // Primeiro dia da semana (0 = Domingo, 1 = Segunda, etc.)
    'first_day_of_week' => 0,
    
    // Breakpoints de responsividade
    'responsive' => [
        'mobile' => 'max-width: 640px',
        'tablet' => 'max-width: 1024px',
    ],
    
    // Comportamento em dispositivos móveis ('stack' ou 'scroll')
    'mobile_view' => 'stack',
    
    // Paletas de cores para temas claro e escuro
    'colors' => [
        'light' => [
            'background' => '#ffffff',
            'text' => '#374151',
            'border' => '#e5e7eb',
            'today' => '#f3f4f6',
            'event' => '#3b82f6',
            'event_text' => '#ffffff',
        ],
        'dark' => [
            'background' => '#1f2937',
            'text' => '#f3f4f6',
            'border' => '#374151',
            'today' => '#111827',
            'event' => '#3b82f6',
            'event_text' => '#ffffff',
        ],
    ],
];
```

## Uso Básico

### Preparando os Eventos

Prepare um array de eventos no formato esperado pelo componente:

```php
$events = [
    [
        "day" => "2023-09-04",
        "data" => [
            ["title" => "Reunião", "id" => 1],
            ["title" => "Entrega", "id" => 2]
        ]
    ],
    [
        "day" => "2023-09-10",
        "data" => [
            ["title" => "Viagem", "id" => 3]
        ]
    ]
];
```

### Usando o Componente

No seu arquivo Blade, adicione o componente Livewire:

```blade
<livewire:calendar 
    :events="$events" 
    :lazy-load-events="true" 
    :max-items-per-day="2" 
/>
```

## Propriedades do Componente

| Propriedade | Tipo | Padrão | Descrição |
|-------------|------|--------|------------|
| events | array | [] | Array de eventos no formato especificado |
| lazyLoadEvents | boolean | true | Se verdadeiro, emite eventos ao mudar de mês para carregar novos dados |
| maxItemsPerDay | integer | 3 | Número máximo de itens a serem exibidos por dia antes de mostrar o botão "Ver mais" |
| currentMonth | string | mês atual | Mês inicial a ser exibido (formato: 'Y-m') |

## Eventos

O componente emite os seguintes eventos Livewire:

| Evento | Parâmetros | Descrição |
|--------|------------|------------|
| calendar:month-changed | year, month | Emitido quando o mês é alterado |
| calendar:day-clicked | date | Emitido quando um dia é clicado |
| calendar:event-clicked | eventId, date | Emitido quando um evento é clicado |

### Exemplo de Escuta de Eventos

```php
// No seu componente Livewire
public function mount()
{
    $this->listeners = [
        'calendar:month-changed' => 'loadEventsForMonth',
        'calendar:day-clicked' => 'handleDayClick',
        'calendar:event-clicked' => 'handleEventClick',
    ];
}

public function loadEventsForMonth($year, $month)
{
    // Carregar eventos para o mês especificado
    $this->events = YourEventModel::whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get()
        ->groupBy(function ($event) {
            return $event->date->format('Y-m-d');
        })
        ->map(function ($dayEvents, $day) {
            return [
                'day' => $day,
                'data' => $dayEvents->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                    ];
                })->toArray(),
            ];
        })
        ->values()
        ->toArray();
}
```

## Personalização

### Slots Disponíveis

O componente oferece vários slots para personalização:

| Slot | Descrição |
|------|------------|
| header | Personaliza o cabeçalho do calendário |
| day-header | Personaliza o cabeçalho de cada dia |
| day-content | Personaliza o conteúdo de cada dia |
| event | Personaliza a exibição de cada evento |
| modal-title | Personaliza o título do modal "Ver mais" |
| modal-content | Personaliza o conteúdo do modal "Ver mais" |
| modal-footer | Personaliza o rodapé do modal "Ver mais" |

### Exemplo de Uso de Slots

```blade
<livewire:calendar :events="$events">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">{{ $month }}</h2>
            <div>
                <button wire:click="previousMonth" class="btn btn-sm btn-outline">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button wire:click="nextMonth" class="btn btn-sm btn-outline">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </x-slot>
    
    <x-slot name="event">
        <div class="bg-indigo-600 text-white rounded px-2 py-1 text-sm mb-1 truncate">
            {{ $event['title'] }}
        </div>
    </x-slot>
    
    <x-slot name="modal-content">
        <div class="space-y-2">
            @foreach($dayEvents as $event)
                <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded">
                    <h3 class="font-bold">{{ $event['title'] }}</h3>
                    @if(isset($event['description']))
                        <p class="text-sm">{{ $event['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-slot>
</livewire:calendar>
```

## Responsividade

O componente é totalmente responsivo e se adapta a diferentes tamanhos de tela:

- **Desktop**: Exibe o calendário completo em formato de grade.
- **Tablet**: Ajusta o tamanho das células para caber na tela.
- **Mobile**: Pode alternar entre dois modos:
  - **Stack**: Empilha as semanas verticalmente.
  - **Scroll**: Permite rolagem horizontal para visualizar todos os dias.

### Configurando o Modo Mobile

Você pode configurar o modo de visualização mobile no arquivo de configuração ou diretamente no componente:

```blade
<livewire:calendar 
    :events="$events" 
    mobile-view="stack" 
/>
```

## Exemplos

O pacote inclui vários exemplos prontos para uso:

### Calendário com Carregamento Lazy

```blade
<!-- resources/views/examples/calendar-with-lazy-loading.blade.php -->
<livewire:calendar-example />
```

### Calendário com Modal Personalizado

```blade
<!-- resources/views/examples/calendar-with-custom-modal.blade.php -->
<livewire:calendar 
    :events="$events" 
    :max-items-per-day="2"
>
    <x-slot name="modal-title">
        <h3 class="text-lg font-bold flex items-center">
            <i class="fas fa-calendar-day mr-2"></i>
            Eventos do dia {{ $selectedDate }}
        </h3>
    </x-slot>
    
    <x-slot name="modal-content">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($dayEvents as $event)
                <div class="py-3">
                    <div class="font-medium">{{ $event['title'] }}</div>
                    @if(isset($event['time']))
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="far fa-clock mr-1"></i> {{ $event['time'] }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-slot>
    
    <x-slot name="modal-footer">
        <div class="flex justify-end space-x-2">
            <button 
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md"
                wire:click="closeModal"
            >
                Fechar
            </button>
            <button 
                class="px-4 py-2 bg-blue-500 text-white rounded-md"
                wire:click="addEvent"
            >
                Adicionar Evento
            </button>
        </div>
    </x-slot>
</livewire:calendar>
```

### Calendário Responsivo

```blade
<!-- resources/views/examples/responsive-calendar.blade.php -->
<livewire:responsive-calendar-example />
```

## Suporte a Temas

O componente suporta automaticamente temas claro e escuro baseados na classe `dark` na tag `<html>`. Não é necessária nenhuma configuração adicional se você já estiver usando o modo escuro do TailwindCSS.

### Ativando o Modo Escuro Manualmente

```javascript
// Ativar modo escuro
document.documentElement.classList.add('dark');

// Desativar modo escuro
document.documentElement.classList.remove('dark');

// Alternar modo escuro
document.documentElement.classList.toggle('dark');
```

## Troubleshooting

### Os eventos não aparecem no calendário

Verifique se o formato dos eventos está correto. Cada evento deve seguir este formato:

```php
[
    "day" => "YYYY-MM-DD",
    "data" => [
        ["title" => "Título do Evento", "id" => 1],
        // mais eventos...
    ]
]
```

### O calendário não está responsivo em dispositivos móveis

Certifique-se de que o TailwindCSS está configurado corretamente e que o viewport meta tag está presente no seu HTML:

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

### Os estilos não estão sendo aplicados

Verifique se você publicou os assets do pacote:

```bash
php artisan vendor:publish --tag=calendar-assets
```

E certifique-se de incluir os arquivos CSS e JavaScript no seu layout:

```html
<link href="{{ asset('vendor/calendar/calendar.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/calendar/calendar.js') }}"></script>
```

### O modo escuro não está funcionando

Verifique se o TailwindCSS está configurado para suportar o modo escuro e se a classe `dark` está sendo adicionada à tag `<html>` quando o modo escuro é ativado.