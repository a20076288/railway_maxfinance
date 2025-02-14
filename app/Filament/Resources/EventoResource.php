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
use Filament\Tables\Actions\DeleteBulkAction;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('superadmin');
    }

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
                    ->locale('pt_PT')
                    ->required(),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->native(false)
                    ->locale('pt_PT')
                    ->required()
                    ->helperText('A data final deve ser igual ou posterior à data de início')
                    ->afterOrEqual('data_inicio')
                    ->validationMessages([
                        'after_or_equal' => 'A data deve ser igual ou posterior à :date',
                    ]),

                Forms\Components\Select::make('empresas')
                    ->label('Empresas do Evento')
                    ->relationship('empresas', 'nome')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required(fn ($get) => $get('tipo') === 'evento')
                    ->placeholder('Selecione uma ou mais empresas')
                    ->validationMessages([
                        'required' => 'Tem de selecionar uma ou mais empresas para criar este evento',
                    ]),
            ]);
    }

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

                Tables\Columns\TextColumn::make('empresas.nome')
                    ->label('Empresas')
                    ->sortable()
                    ->badge()
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                DeleteAction::make()
                    ->visible(fn ($record) => true), 
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Eliminar selecionados'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}