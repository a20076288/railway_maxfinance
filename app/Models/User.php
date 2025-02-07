<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\CargoEnum; // Importar o ENUM
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // 🔹 Mantive tudo igual ao original

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

    public function departamentos(): BelongsToMany
    {
        return $this->belongsToMany(Departamento::class, 'departamento_user');
    }

    /**
     * Verifica se o utilizador é diretor de algum departamento
     */
    public function eDiretor(): bool
    {
        return Departamento::where('diretor_id', $this->id)->exists();
    }
}
