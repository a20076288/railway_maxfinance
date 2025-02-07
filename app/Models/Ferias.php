<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Notifications\FeriasSubmetidasNotification;

class Ferias extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data_inicio',
        'data_fim',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * Relação com o utilizador que submeteu o pedido de férias
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enviar notificação ao Diretor do Departamento quando um pedido de férias for submetido
     */
    protected static function booted()
    {
        static::created(function ($ferias) {
            $user = $ferias->user;

            if ($user) {
                // 🔹 Obter o diretor do departamento do utilizador que submeteu o pedido
                $diretor = $user->departamentos()->with('diretor')->get()->pluck('diretor')->first();
                if ($diretor) {
                    $diretor->notify(new FeriasSubmetidasNotification($ferias));
                }
            }
        });
    }
}
