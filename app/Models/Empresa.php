<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'nome_social',
        'nif',
    ];

    public function eventos()
    {
        return $this->belongsToMany(Evento::class, 'empresa_evento');
    }

}
