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

    public static function getNavigationLabel(): string
    {
        return 'Utilizadores';
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->isSuperadmin();
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
                        CargoEnum::ADMINISTRACAO->value               => 'Administração',
                        CargoEnum::DIRECAO->value                     => 'Direção',
                        CargoEnum::RESPONSAVEL_DEPARTAMENTO->value     => 'Responsável Departamento',
                        CargoEnum::RESPONSAVEL_FUNCAO->value           => 'Responsável Função',
                        CargoEnum::COLABORADOR->value                  => 'Colaborador',
                    ])
                    ->required(),

                TextInput::make('funcao')
                    ->label('Função na Empresa')
                    ->nullable(),

                MultiSelect::make('departamentos')
                    ->label('Departamentos')
                    ->relationship('departamentos', 'nome')
                    ->required()
                    ->preload(),

                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->required()
                    ->default(fn () => Role::where('name', 'colaborador')->first()?->id),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->nullable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->required(fn ($record) => $record === null)
                    ->helperText('Se não inserir uma nova password, a password atual do utilizador será mantida.'),
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

                Tables\Columns\TextColumn::make('departamentos.nome')
                    ->label('Departamentos')
                    ->sortable()
                    ->getStateUsing(fn (User $record) => implode(', ', $record->departamentos->pluck('nome')->toArray())),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable()
                    ->getStateUsing(fn (User $record) => $record->getRoleNames()->join(', ')),
            ])
            ->actions([
                EditAction::make()->visible(fn () => Filament::auth()->user()->isSuperadmin()),
                DeleteAction::make()->visible(fn () => Filament::auth()->user()->isSuperadmin()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->visible(fn () => Filament::auth()->user()->isSuperadmin()),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'primeiro_nome'   => $data['primeiro_nome'],
            'ultimo_nome'     => $data['ultimo_nome'],
            'email'           => $data['email'],
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cargo'           => $data['cargo'],
            'funcao'          => $data['funcao'] ?? null,
            'password'        => bcrypt($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['departamentos'])) {
            $user->departamentos()->sync($data['departamentos']);
        }

        return $user->toArray();
    }

    public static function mutateFormDataBeforeSave(array $data, Model $record): array
    {
        $updateData = [
            'primeiro_nome'   => $data['primeiro_nome'],
            'ultimo_nome'     => $data['ultimo_nome'],
            'email'           => $data['email'],
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cargo'           => $data['cargo'],
            'funcao'          => $data['funcao'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        $record->update($updateData);

        if (!empty($data['roles'])) {
            $record->syncRoles($data['roles']);
        }

        if (!empty($data['departamentos'])) {
            $record->departamentos()->sync($data['departamentos']);
        }

        return $record->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
