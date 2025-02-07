<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['feriado', 'evento']);
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->timestamps();
        });

        // Criar a tabela pivot para associar eventos a múltiplas empresas
        Schema::create('empresa_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
