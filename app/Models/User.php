<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\CargoEnum;
use App\Models\Departamento;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'primeiro_nome',
        'ultimo_nome',
        'email',
        'data_nascimento',
        'cargo',
        'funcao',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'data_nascimento' => 'date',
        'cargo' => CargoEnum::class,
    ];

    /**
     * 🔹 Mutator para `name` (Usado no Filament)
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->primeiro_nome} {$this->ultimo_nome}"
        );
    }

    /**
     * 🔹 Relação muitos-para-muitos com Departamentos
     */
    public function departamentos(): BelongsToMany
    {
        return $this->belongsToMany(Departamento::class, 'departamento_user');
    }

    /**
     * 🔹 Verifica se o utilizador é Superadmin
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * 🔹 Verifica se o utilizador é Administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * 🔹 Verifica se o utilizador é Colaborador
     */
    public function isColaborador(): bool
    {
        return $this->hasRole('colaborador');
    }

    /**
     * 🔹 Verifica se o utilizador é Diretor de algum departamento
     */
    public function eDiretor(): bool
    {
        return Departamento::where('diretor_id', $this->id)->exists();
    }
}
