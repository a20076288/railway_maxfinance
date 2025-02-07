<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ferias;
use App\Models\User;

class FeriasSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Ferias::create([
                'user_id' => $user->id,
                'data_inicio' => now()->addDays(rand(1, 30)),
                'data_fim' => now()->addDays(rand(31, 60)),
                'status' => 'aprovado',
                'observacoes' => 'Férias de teste para ' . $user->primeiro_nome,
            ]);
        }
    }
}
