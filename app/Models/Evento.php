<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'nome',
        'tipo', // pode ser 'feriado' ou 'evento'
        'data_inicio',
        'data_fim'
    ];

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_evento');
    }

}
