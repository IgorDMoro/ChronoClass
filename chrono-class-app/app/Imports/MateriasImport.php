<?php

namespace App\Imports;

use App\Models\Materia;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para ler os cabeçalhos

class MateriasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Verifica se a matéria já existe pelo código antes de tentar criar
        $materiaExistente = Materia::where('codigo', $row['cod'])->first();

        if ($materiaExistente) {
            // Se a matéria já existe, retorne null para pular esta linha
            return null;
        }

        // Se não existe, crie uma nova matéria
        return new Materia([
            'codigo'        => $row['cod'],
            'nome'          => $row['disciplina_de_origem_unidade_curricular'],
            'carga_horaria' => $row['ch'],
            'modalidade'    => $row['mod'],
            'comp_tipo'     => $row['comp'],
            'ensw_tipo'     => $row['ensw'],
        ]);
    }
}