<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\CargoEnum;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Criar Empresas
        $empresa1 = Empresa::firstOrCreate(['nome' => 'Tech Solutions', 'nome_social' => 'Tech Solutions Ltd.', 'nif' => '123456789']);
        $empresa2 = Empresa::firstOrCreate(['nome' => 'Global Business', 'nome_social' => 'Global Business SA', 'nif' => '987654321']);

        // Criar Departamentos
        $departamento1 = Departamento::firstOrCreate(['nome' => 'Desenvolvimento', 'empresa_id' => $empresa1->id]);
        $departamento2 = Departamento::firstOrCreate(['nome' => 'Vendas', 'empresa_id' => $empresa2->id]);

        // Criar Superadmin
        $superadmin = User::firstOrCreate([
            'email' => 'superadmin@test.com',
        ], [
            'primeiro_nome' => 'Super',
            'ultimo_nome' => 'Admin',
            'data_nascimento' => '1990-01-01',
            'cargo' => CargoEnum::ADMINISTRACAO,
            'funcao' => 'Gestor de Sistema',
            'password' => Hash::make('1234'),
        ]);
        $superadmin->assignRole('superadmin');

        // Criar Administrador
        $admin = User::firstOrCreate([
            'email' => 'admin@test.com',
        ], [
            'primeiro_nome' => 'Admin',
            'ultimo_nome' => 'User',
            'data_nascimento' => '1992-06-15',
            'cargo' => CargoEnum::DIRECAO,
            'funcao' => 'Administrador',
            'password' => Hash::make('1234'),
        ]);
        $admin->assignRole('admin');

        // Criar Diretor
        $diretor = User::firstOrCreate([
            'email' => 'diretor@test.com',
        ], [
            'primeiro_nome' => 'Carlos',
            'ultimo_nome' => 'Silva',
            'data_nascimento' => '1985-03-22',
            'cargo' => CargoEnum::DIRECAO,
            'funcao' => 'Diretor de Desenvolvimento',
            'password' => Hash::make('1234'),
        ]);
        $diretor->assignRole('admin');
        $diretor->departamentos()->attach($departamento1->id); // Associar ao departamento Desenvolvimento

        // Criar Colaborador
        $colaborador = User::firstOrCreate([
            'email' => 'colaborador@test.com',
        ], [
            'primeiro_nome' => 'João',
            'ultimo_nome' => 'Costa',
            'data_nascimento' => '1998-07-22',
            'cargo' => CargoEnum::COLABORADOR,
            'funcao' => 'Assistente de Vendas',
            'password' => Hash::make('1234'),
        ]);
        $colaborador->assignRole('colaborador');
        $colaborador->departamentos()->attach($departamento2->id); // Associar ao departamento Vendas
    }
}
