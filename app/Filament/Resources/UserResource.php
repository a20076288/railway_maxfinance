<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Departamento;
use App\Models\CargoEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MultiSelect;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Filament\Facades\Filament;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->can('manage-users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('primeiro_nome')
                    ->label('Primeiro Nome')
                    ->required(),

                TextInput::make('ultimo_nome')
                    ->label('Último Nome')
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),

                DatePicker::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->nullable(),

                Select::make('cargo')
                    ->label('Cargo')
                    ->options([
                        CargoEnum::ADMINISTRACAO->value => 'Administração',
                        CargoEnum::DIRECAO->value => 'Direção',
                        CargoEnum::RESPONSAVEL_DEPARTAMENTO->value => 'Responsável Departamento',
                        CargoEnum::RESPONSAVEL_FUNCAO->value => 'Responsável Função',
                        CargoEnum::COLABORADOR->value => 'Colaborador',
                    ])
                    ->required(),

                TextInput::make('funcao')
                    ->label('Função na Empresa')
                    ->nullable(),

                // ✅ Seleção de múltiplos departamentos
                MultiSelect::make('departamentos')
                    ->label('Departamentos')
                    ->relationship('departamentos', 'nome')
                    ->required(),

                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->required()
                    ->default(fn () => Role::where('name', 'colaborador')->first()?->id),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->required(fn ($record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('primeiro_nome')
                    ->label('Primeiro Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ultimo_nome')
                    ->label('Último Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->sortable(),

                // ✅ Mostrar departamentos corretamente
                Tables\Columns\TextColumn::make('departamentos.nome')
                    ->label('Departamentos')
                    ->sortable()
                    ->getStateUsing(fn (User $record) => implode(', ', $record->departamentos->pluck('nome')->toArray())),

                // ✅ Mostrar roles corretamente
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable()
                    ->getStateUsing(fn (User $record) => $record->getRoleNames()->join(', ')),
            ])
            ->actions([
                EditAction::make()->visible(fn () => Filament::auth()->user()->can('manage-users')),
                DeleteAction::make()->visible(fn () => Filament::auth()->user()->can('manage-users')),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->visible(fn () => Filament::auth()->user()->can('manage-users')),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'primeiro_nome' => $data['primeiro_nome'],
            'ultimo_nome' => $data['ultimo_nome'],
            'email' => $data['email'],
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cargo' => $data['cargo'],
            'funcao' => $data['funcao'] ?? null,
            'password' => bcrypt($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['departamentos'])) {
            $user->departamentos()->sync($data['departamentos']); // ✅ Salvar departamentos na BD
        }

        return $user->toArray();
    }

    public static function mutateFormDataBeforeSave(array $data, Model $record): array
    {
        $record->update([
            'primeiro_nome' => $data['primeiro_nome'],
            'ultimo_nome' => $data['ultimo_nome'],
            'email' => $data['email'],
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cargo' => $data['cargo'],
            'funcao' => $data['funcao'] ?? null,
        ]);

        if (!empty($data['roles'])) {
            $record->syncRoles($data['roles']);
        }

        if (!empty($data['departamentos'])) {
            $record->departamentos()->sync($data['departamentos']); // ✅ Atualizar departamentos
        }

        return $record->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
