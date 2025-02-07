<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use App\Models\Ferias;
use App\Models\Evento;
use Filament\Forms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarWidget extends FullCalendarWidget
{
   public static function canView(): bool
{
    return auth()->check();
}

    /**
     * 🔹 Buscar eventos da base de dados para mostrar no calendário
     */
    public function fetchEvents(array $fetchInfo): array
{
    $ferias = Ferias::query()
        ->whereBetween('data_inicio', [$fetchInfo['start'], $fetchInfo['end']])
        ->where('user_id', auth()->id())
        ->get()
        ->map(fn (Ferias $ferias) => [
            'id' => (string) $ferias->id,
            'title' => "Férias de {$ferias->user->primeiro_nome} {$ferias->user->ultimo_nome}",
            'start' => Carbon::parse($ferias->data_inicio)->format('Y-m-d'), // 🔹 Agora sem horário
            'end' => Carbon::parse($ferias->data_fim)->addDay()->format('Y-m-d'),
            'color' => match ($ferias->status) {
                'aprovado' => 'green',
                'pendente' => 'orange',
                'rejeitado' => 'red',
            },
        ]);

    $eventos = Evento::query()
        ->whereBetween('data_inicio', [$fetchInfo['start'], $fetchInfo['end']])
        ->get()
        ->map(fn (Evento $evento) => [
            'id' => 'evento-' . (string) $evento->id, 
            'title' => $evento->nome,
            'start' => Carbon::parse($evento->data_inicio)->format('Y-m-d'), // 🔹 Agora sem horário
            'end' => Carbon::parse($evento->data_fim)->format('Y-m-d'),
            'color' => $evento->tipo === 'feriado' ? 'red' : 'blue',
            'display' => 'background',
        ]);

    return collect($ferias)->merge($eventos)->all();
}



    /**
     * 🔹 Definir ações no cabeçalho do calendário (botão "Marcar Férias")
     */
    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->model(Ferias::class)
                ->label('Marcar Férias')
                ->modalHeading('Marcar dias de Férias')
                ->mountUsing(
                    function (Forms\Form $form, array $arguments) {
                        $start = Carbon::parse($arguments['start'] ?? now());
                        $end = Carbon::parse($arguments['end'] ?? now()->addDays(1));

                        // Se o dia selecionado for inválido, ajusta para o próximo dia útil
                        if ($this->isInvalidDate($start)) {
                            $start = $this->adjustToWorkday($start);
                            $end = $this->adjustToWorkday($start->copy()->addDays(1));
                        }

                        $form->fill([
                            'data_inicio' => $start->format('Y-m-d'),
                            'data_fim' => $end->format('Y-m-d'),
                        ]);
                    }
                )
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
                        'status' => 'pendente',
                    ]);
                }),
        ];
    }

    /**
     * 🔹 Obter lista de dias bloqueados (fins de semana, feriados e férias já marcadas)
     */
    private function getDisabledDates(): array
    {
        $invalidDates = [];

        // Bloqueia fins de semana e feriados
        $current = now();
        $endDate = now()->addYear();
        while ($current <= $endDate) {
            if ($this->isInvalidDate($current)) {
                $invalidDates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        // Bloqueia dias de férias já marcados (pendentes ou aprovados)
        $feriasMarcadas = Ferias::where('user_id', Auth::id())
            ->whereIn('status', ['pendente', 'aprovado'])
            ->get()
            ->flatMap(fn ($ferias) =>
                collect(Carbon::parse($ferias->data_inicio)
                    ->daysUntil(Carbon::parse($ferias->data_fim))
                )->map->format('Y-m-d')
            )->toArray();

        return array_merge($invalidDates, $feriasMarcadas);
    }

    /**
     * 🔹 Verifica se um dia é inválido (fim de semana, feriado ou evento)
     */
    private function isInvalidDate(Carbon $date): bool
{
    // Bloqueia fins de semana
    if ($date->isWeekend()) {
        return true;
    }

    // Bloqueia feriados e eventos de empresa
    if (Evento::whereIn('tipo', ['feriado', 'evento']) // ✅ Agora verifica eventos e feriados
        ->whereDate('data_inicio', '<=', $date)
        ->whereDate('data_fim', '>=', $date)
        ->exists()) {
        return true;
    }

    return false;
}

    /**
     * 🔹 Ajusta automaticamente para o próximo dia útil disponível
     */
    private function adjustToWorkday(Carbon $date): Carbon
    {
        while ($this->isInvalidDate($date)) {
            $date->addDay();
        }
        return $date;
    }

    /**
     * 🔹 Valida o período de férias antes de ser criado
     */
    private function validatePeriod(string $start, string $end): void
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

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
                    'data_fim' => 'O período selecionado contém dias não permitidos',
                ]);
            }
            $current->addDay();
        }
    }
}
