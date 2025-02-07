<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade'); // Departamento pertence a uma empresa
            $table->foreignId('diretor_id')->nullable()->constrained('users')->onDelete('set null'); // Diretor do departamento
            $table->timestamps();
        });

        // Tabela pivot para associar múltiplos responsáveis ao departamento
        Schema::create('departamento_responsavel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Responsáveis do departamento
        });

        // Tabela pivot para associar utilizadores a múltiplos departamentos
        Schema::create('departamento_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Funcionários dentro do departamento
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento_user');
        Schema::dropIfExists('departamento_responsavel');
        Schema::dropIfExists('departamentos');
    }
};
