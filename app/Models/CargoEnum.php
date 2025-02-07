<?php

namespace App\Models;

enum CargoEnum: string
{
    case ADMINISTRACAO = 'Administração';
    case DIRECAO = 'Direção';
    case RESPONSAVEL_DEPARTAMENTO = 'Responsável Departamento';
    case RESPONSAVEL_FUNCAO = 'Responsável Função';
    case COLABORADOR = 'Colaborador';

    /**
     * Retorna uma lista com todos os cargos
     */
    public static function all(): array
    {
        return [
            self::ADMINISTRACAO,
            self::DIRECAO,
            self::RESPONSAVEL_DEPARTAMENTO,
            self::RESPONSAVEL_FUNCAO,
            self::COLABORADOR,
        ];
    }
}
