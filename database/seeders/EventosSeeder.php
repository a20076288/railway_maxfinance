<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evento;
use Carbon\Carbon;

class EventosSeeder extends Seeder
{
    public function run(): void
    {
        $anoAtual = now()->year;

        // 🔹 Lista de feriados fixos em Portugal
        $feriadosFixos = [
            ['nome' => 'Ano Novo', 'data' => "{$anoAtual}-01-01"],
            ['nome' => 'Dia da Liberdade', 'data' => "{$anoAtual}-04-25"],
            ['nome' => 'Dia do Trabalhador', 'data' => "{$anoAtual}-05-01"],
            ['nome' => 'Dia de Portugal', 'data' => "{$anoAtual}-06-10"],
            ['nome' => 'Assunção de Nossa Senhora', 'data' => "{$anoAtual}-08-15"],
            ['nome' => 'Implantação da República', 'data' => "{$anoAtual}-10-05"],
            ['nome' => 'Dia de Todos os Santos', 'data' => "{$anoAtual}-11-01"],
            ['nome' => 'Restauração da Independência', 'data' => "{$anoAtual}-12-01"],
            ['nome' => 'Imaculada Conceição', 'data' => "{$anoAtual}-12-08"],
            ['nome' => 'Natal', 'data' => "{$anoAtual}-12-25"],
        ];

        // 🔹 Cálculo manual da Páscoa (sem precisar da extensão `calendar`)
        $pascoa = $this->calcularPascoa($anoAtual);

        // 🔹 Lista de feriados móveis (baseados na Páscoa)
        $feriadosMoveis = [
            ['nome' => 'Sexta-feira Santa', 'data' => $pascoa->copy()->subDays(2)->format('Y-m-d')],
            ['nome' => 'Domingo de Páscoa', 'data' => $pascoa->format('Y-m-d')],
            ['nome' => 'Corpo de Deus', 'data' => $pascoa->copy()->addDays(60)->format('Y-m-d')],
        ];

        // 🔹 Criar os feriados fixos e móveis na base de dados
        foreach (array_merge($feriadosFixos, $feriadosMoveis) as $feriado) {
            Evento::updateOrCreate(
                ['nome' => $feriado['nome'], 'data_inicio' => $feriado['data'], 'data_fim' => $feriado['data']],
                ['tipo' => 'feriado']
            );
        }

        // 🔹 Criar um exemplo de evento de empresa (pode ser gerido pelo Superadmin)
        Evento::updateOrCreate(
            ['nome' => 'Reunião Anual da Empresa', 'data_inicio' => "{$anoAtual}-03-15", 'data_fim' => "{$anoAtual}-03-15"],
            ['tipo' => 'evento']
        );
    }

    /**
     * 🔹 Função para calcular a data da Páscoa sem a extensão `calendar`
     */
    private function calcularPascoa(int $ano): Carbon
    {
        $a = $ano % 19;
        $b = intdiv($ano, 100);
        $c = $ano % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($ano, $mes, $dia);
    }
}
