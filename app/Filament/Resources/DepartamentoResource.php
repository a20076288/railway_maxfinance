<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartamentoResource\Pages;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use App\Models\CargoEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class DepartamentoResource extends Resource
{
    protected static ?string $model = Departamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * 🔹 Mostrar a secção apenas para Superadmin
     */
    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user->cargo === CargoEnum::ADMINISTRACAO;
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return self::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return self::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return self::canViewAny();
    }

    public static function canView(Model $record): bool
    {
        return self::canViewAny();
    }

    /**
     * 🔹 Formulário para Criar/Editar Departamentos
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->label('Nome do Departamento')
                    ->required(),

                Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(Empresa::pluck('nome', 'id')->toArray())
                    ->searchable()
                    ->required(),

                Select::make('diretor_id')
                    ->label('Diretor')
                    ->options(User::where('cargo', CargoEnum::DIRECAO)->pluck('primeiro_nome', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->preload(), // Carregar automaticamente os valores

                Select::make('responsaveis')
                    ->label('Responsáveis')
                    ->multiple()
                    ->options(User::pluck('primeiro_nome', 'id')->toArray())
                    ->searchable()
                    ->preload(), // Carregar automaticamente os valores
            ]);
    }

    /**
     * 🔹 Tabela para Listar Departamentos
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('empresa.nome')
                    ->label('Empresa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('diretor.primeiro_nome')
                    ->label('Diretor')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make()->visible(fn () => self::canViewAny()),
                DeleteAction::make()->visible(fn () => self::canViewAny()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->visible(fn () => self::canViewAny()),
            ]);
    }

    /**
     * 🔹 Relações (se houverem no futuro)
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * 🔹 Definição das Páginas do Filament
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartamentos::route('/'),
            'create' => Pages\CreateDepartamento::route('/create'),
            'edit' => Pages\EditDepartamento::route('/{record}/edit'),
        ];
    }
}
