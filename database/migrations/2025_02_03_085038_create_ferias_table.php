<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Utilizador que pediu férias
            $table->date('data_inicio'); // Data de início das férias
            $table->date('data_fim'); // Data de fim das férias
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente'); // Estado da aprovação
            $table->text('observacoes')->nullable(); // Campo opcional para justificações
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ferias');
    }
};
