<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use App\Models\Ferias;
use App\Models\Evento;
use Filament\Forms;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarWidget extends FullCalendarWidget
{
    /**
     * Cache local para eventos (armazenado como array associativo para lookup rápido)
     */
    private array $eventosCache = [];

    /**
     * Define quem pode ver o widget.
     */
    public static function canView(): bool
    {
        return auth()->check();
    }

    /**
     * Buscar eventos da base de dados para mostrar no calendário.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // Otimização com eager loading e seleção de colunas necessárias
        $ferias = Ferias::query()
            ->with('user:id,primeiro_nome,ultimo_nome')
            ->select('id', 'data_inicio', 'data_fim', 'status', 'user_id')
            ->whereBetween('data_inicio', [$fetchInfo['start'], $fetchInfo['end']])
            ->where('user_id', auth()->id())
            ->get()
            ->map(fn (Ferias $ferias) => [
                'id'    => (string) $ferias->id,
                'title' => "Férias de {$ferias->user->primeiro_nome} {$ferias->user->ultimo_nome}",
                'start' => Carbon::parse($ferias->data_inicio)->format('Y-m-d'),
                'end'   => Carbon::parse($ferias->data_fim)->addDay()->format('Y-m-d'),
                'color' => match ($ferias->status) {
                    'aprovado'  => 'green',
                    'pendente'  => 'orange',
                    'rejeitado' => 'red',
                },
            ]);

        // Selecionar apenas os campos necessários na query dos eventos
        $eventos = Evento::query()
            ->select('id', 'nome', 'data_inicio', 'data_fim', 'tipo')
            ->whereBetween('data_inicio', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(fn (Evento $evento) => [
                'id'      => 'evento-' . (string) $evento->id,
                'title'   => $evento->nome,
                'start'   => Carbon::parse($evento->data_inicio)->format('Y-m-d'),
                'end'     => Carbon::parse($evento->data_fim)->format('Y-m-d'),
                'color'   => $evento->tipo === 'feriado' ? 'red' : 'blue',
                'display' => 'background',
            ]);

        return collect($ferias)->merge($eventos)->all();
    }

    /**
     * Definir ações no cabeçalho do calendário (botão "Marcar Férias").
     */
    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->model(Ferias::class)
                ->label('Marcar Férias')
                ->modalHeading('Marcar dias de Férias')
                ->mountUsing(function (Forms\Form $form, array $arguments) {
                    $start = Carbon::parse($arguments['start'] ?? now());
                    $end   = Carbon::parse($arguments['end'] ?? now()->addDay());

                    if ($this->isInvalidDate($start)) {
                        $start = $this->adjustToWorkday($start);
                        $end   = $this->adjustToWorkday($start->copy()->addDay());
                    }

                    $form->fill([
                        'data_inicio' => $start->format('Y-m-d'),
                        'data_fim'    => $end->format('Y-m-d'),
                    ]);
                })
                ->form([
                    Forms\Components\DatePicker::make('data_inicio')
                        ->required()
                        ->label('Data de Início')
                        ->native(false)
                        ->locale('pt')
                        ->minDate(now())
                        ->disabledDates(fn () => $this->getDisabledDates()),
                    Forms\Components\DatePicker::make('data_fim')
                        ->required()
                        ->label('Data de Fim')
                        ->native(false)
                        ->locale('pt')
                        ->minDate(now())
                        ->disabledDates(fn () => $this->getDisabledDates()),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $this->validatePeriod($data['data_inicio'], $data['data_fim']);
                    return array_merge($data, [
                        'user_id' => Auth::id(),
                        'status'  => 'pendente',
                    ]);
                }),
        ];
    }

    /**
     * Obter lista de dias bloqueados (fins de semana, feriados e férias já marcadas).
     *
     * Limita-se o processamento a um intervalo (ex: 3 meses) para melhorar o desempenho.
     */
    private function getDisabledDates(): array
    {
        $invalidDates = [];

        // Definir o intervalo a processar (exemplo: 3 meses a partir de hoje)
        $start  = now();
        $end    = now()->addMonths(3);
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $current) {
            if ($this->isInvalidDate($current)) {
                $invalidDates[] = $current->format('Y-m-d');
            }
        }

        // Obter os dias de férias já marcadas (apenas os campos necessários)
        $feriasMarcadas = Ferias::query()
            ->select('data_inicio', 'data_fim')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pendente', 'aprovado'])
            ->get()
            ->flatMap(fn ($ferias) => collect(
                Carbon::parse($ferias->data_inicio)
                    ->daysUntil(Carbon::parse($ferias->data_fim))
            )->map(fn ($d) => $d->format('Y-m-d')))
            ->toArray();

        return array_merge($invalidDates, $feriasMarcadas);
    }

    /**
     * Verifica se um dia é inválido (fim de semana, feriado ou evento).
     *
     * Utiliza um cache associativo para uma verificação mais eficiente.
     */
    private function isInvalidDate(Carbon $date): bool
    {
        // Se ainda não carregámos os eventos, buscamos e armazenamos na cache
        if (empty($this->eventosCache)) {
            $startYear = now()->startOfYear();
            $endYear   = now()->endOfYear();

            $eventos = Evento::query()
                ->select('data_inicio', 'data_fim', 'tipo')
                ->whereIn('tipo', ['feriado', 'evento'])
                ->where(function ($query) use ($startYear, $endYear) {
                    $query->whereBetween('data_inicio', [$startYear, $endYear])
                          ->orWhereBetween('data_fim', [$startYear, $endYear]);
                })
                ->get()
                ->flatMap(fn ($evento) => collect(
                    Carbon::parse($evento->data_inicio)
                        ->daysUntil(Carbon::parse($evento->data_fim))
                )->map(fn ($d) => $d->format('Y-m-d')))
                ->toArray();

            // Converter o array para um array associativo para lookup rápido
            $this->eventosCache = array_flip($eventos);
        }

        // Verifica se a data é fim de semana ou se existe na cache de eventos
        return $date->isWeekend() || isset($this->eventosCache[$date->format('Y-m-d')]);
    }

    /**
     * Ajusta automaticamente para o próximo dia útil disponível.
     */
    private function adjustToWorkday(Carbon $date): Carbon
    {
        while ($this->isInvalidDate($date)) {
            $date->addDay();
        }
        return $date;
    }

    /**
     * Valida o período de férias antes de ser criado.
     */
    private function validatePeriod(string $start, string $end): void
    {
        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        if ($startDate->gt($endDate)) {
            throw ValidationException::withMessages([
                'data_fim' => 'A data final não pode ser anterior à data inicial',
            ]);
        }

        $current = $startDate->copy();
        while ($current <= $endDate) {
            if ($this->isInvalidDate($current)) {
                throw ValidationException::withMessages([
                    'data_inicio' => 'O período selecionado contém dias não permitidos',
                    'data_fim'    => 'O período selecionado contém dias não permitidos',
                ]);
            }
            $current->addDay();
        }
    }
}
