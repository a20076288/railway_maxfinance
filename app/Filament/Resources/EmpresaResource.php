<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Models\Empresa;
use App\Models\CargoEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    /**
     *  Mostrar a secção apenas para Superadmin
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
     * Formulário para Criar/Editar Empresas
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->required(),
                TextInput::make('nome_social')
                    ->required(),
                TextInput::make('nif')
                    ->required(),
            ]);
    }

    /**
     * Tabela para Listar Empresas
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome'),
                Tables\Columns\TextColumn::make('nome_social'),
                Tables\Columns\TextColumn::make('nif'),
            ])
            ->actions([
                EditAction::make()->visible(fn () => self::canViewAny()),
                DeleteAction::make()->visible(fn () => self::canViewAny()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->visible(fn () => self::canViewAny()),
            ]);
    }

    /**
     * Relações (se houverem no futuro)
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Definição das Páginas do Filament
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
