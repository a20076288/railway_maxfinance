<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventoResource\Pages;
use App\Models\Evento;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\DeleteAction;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    /**
     * 🔹 Apenas o Superadmin pode aceder ao recurso
     */
    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('superadmin');
    }

    /**
     * 🔹 Formulário para criar/editar eventos
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do Evento')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo de Evento')
                    ->options([
                        'feriado' => 'Feriado',
                        'evento' => 'Evento da Empresa',
                    ])
                    ->default('evento')
                    ->required(),

                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->native(false)
                    ->locale('pt')
                    ->required(),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->native(false)
                    ->locale('pt')
                    ->required()
                    ->helperText('A data final deve ser igual ou posterior à data de início.'),

                // 🔹 Novo campo para associar múltiplas empresas ao evento
                Forms\Components\Select::make('empresas')
                    ->label('Empresas')
                    ->relationship('empresas', 'nome') // Relacionamento com o modelo Empresa
                    ->multiple() // Permitir múltiplas seleções
                    ->preload() // Carregar as opções previamente
                    ->searchable() // Permitir pesquisa por nome
                    ->required(),
            ]);
    }

    /**
     * 🔹 Tabela para listar eventos existentes
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'feriado' ? 'Feriado' : 'Evento'),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Início')
                    ->sortable()
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Fim')
                    ->sortable()
                    ->date('d/m/Y'),

                // 🔹 Nova coluna para exibir as empresas associadas ao evento
                Tables\Columns\TextColumn::make('empresas.nome')
                    ->label('Empresas')
                    ->sortable()
                    ->badge()
                    ->wrap(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),

                DeleteAction::make()
                    ->visible(fn ($record) => $record->tipo === 'evento'), // ❌ Bloqueia apagar feriados
            ])
            ->bulkActions([]);
    }

    /**
     * 🔹 Definir as páginas do recurso
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}
