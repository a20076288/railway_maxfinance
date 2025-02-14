<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeriasResource\Pages;
use App\Models\Ferias;
use App\Models\User;
use App\Models\CargoEnum;
use App\Models\Departamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\Action;

class FeriasResource extends Resource
{
    protected static ?string $model = Ferias::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationLabel(): string
    {
        return 'Férias';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Férias';
    }

    public static function getLabel(): ?string
    {
        return 'Férias';
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user->isSuperadmin() || $user->isAdmin() || $user->cargo === CargoEnum::DIRECAO;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->isSuperadmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->cargo === CargoEnum::DIRECAO) {
            $colaboradores_ids = User::whereHas('departamentos', function ($q) use ($user) {
                $q->whereIn('departamento_id', $user->departamentos->pluck('id'));
            })->pluck('id');

            return $query->whereIn('user_id', $colaboradores_ids);
        }

        return $query->where('id', -1);
    }

    public static function canApprove(): bool
    {
        $user = Auth::user();
        return $user->isSuperadmin() || $user->isAdmin() || $user->cargo === CargoEnum::DIRECAO;
    }

    public static function canReject(): bool
    {
        $user = Auth::user();
        return $user->isSuperadmin() || $user->isAdmin() || $user->cargo === CargoEnum::DIRECAO;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->isSuperadmin() || Auth::user()->isAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.primeiro_nome')
                    ->label('Utilizador')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Início')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Fim')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'pendente' => 'yellow',
                        'aprovado' => 'green',
                        'rejeitado' => 'red',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departamento')
                    ->label('Departamento')
                    ->options(function () {
                        $user = Auth::user();

                        if ($user->isSuperadmin() || $user->isAdmin()) {
                            return Departamento::all()->pluck('nome', 'id')->toArray();
                        }

                        return $user->departamentos->pluck('nome', 'id')->toArray();
                    })
                    ->query(function (Builder $query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('user.departamentos', function (Builder $q) use ($data) {
                                $q->where('departamento_id', $data['value']);
                            });
                        }
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                    ])
                    ->label('Estado'),

                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicio')->label('Data Inicial'),
                        Forms\Components\DatePicker::make('data_fim')->label('Data Final'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['data_inicio'],
                                fn ($q) => $q->whereDate('data_inicio', '>=', $data['data_inicio'])
                            )
                            ->when(
                                $data['data_fim'],
                                fn ($q) => $q->whereDate('data_fim', '<=', $data['data_fim'])
                            );
                    })
            ])
            ->actions([
                Action::make('Aprovar')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()->isSuperadmin() || Auth::user()->isAdmin() || Auth::user()->cargo === CargoEnum::DIRECAO)
                    ->action(fn (Ferias $ferias) => $ferias->update(['status' => 'aprovado'])),

                Action::make('Rejeitar')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()->isSuperadmin() || Auth::user()->isAdmin() || Auth::user()->cargo === CargoEnum::DIRECAO)
                    ->action(fn (Ferias $ferias) => $ferias->update(['status' => 'rejeitado'])),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->visible(fn () => Auth::user()->isSuperadmin() || Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->visible(fn () => Auth::user()->isSuperadmin()),
            ])
            ->defaultSort('data_inicio', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFerias::route('/'),
        ];
    }
}
