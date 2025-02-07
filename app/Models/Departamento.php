<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'empresa_id',
        'diretor_id',
    ];

    /**
     * 🔹 Cada departamento pertence a uma empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * 🔹 O diretor do departamento (um utilizador)
     */
    public function diretor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diretor_id');
    }

    /**
     * 🔹 Responsáveis pelo departamento (muitos utilizadores)
     */
    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'departamento_responsavel');
    }

    /**
     * 🔹 Utilizadores que pertencem ao departamento (colaboradores)
     */
    public function colaboradores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'departamento_user');
    }
}
