<?php

namespace Database\Seeders;

use App\Models\Turma;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $turmas = [
            [
                'nome' => 'CC/ENSW - 4º Ano (Rep: Renata)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2022,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2023 (Rep: Jamilly Camaratto)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2023,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC/ENSW - E1-2023 Mista (Rep: Luigi Castoldi)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2023,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC/ENSW - E2-2023 Mista (Rep: Giovanna Kubo)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2023,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC/ES - E3-2023 Mista (Rep: Marcelly Vale)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2023,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC - E1-2024 Turma A (Rep: Kawan Shigueo)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2024,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2024 Turma A (Rep: Pedro Rafael)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2024,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2024 Turma B+C (Rep: Victor Andrade)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2024,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC - E2-2024 (Rep: Gabriel Seichi)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2024,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E3-2024 (Rep: Daniel Berto)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2024,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC - E1-2025 Turma A (Rep: Sofia e Maisa)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2025,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2025 Turma A (Rep: João Filipe)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2025,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC/ES - E3-2025 Mista (Rep: Roger e Artur)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2025,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2025 Turma B (Rep: Gabriel Yuki)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2025,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'CC - E1-2026 Turma A (Rep: Paulo José)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2026,
                'bimestre_entrada' => 'B1',
            ],
            [
                'nome' => 'ENSW - E1-2026 Turma A (Rep: Pedro Ruffo)',
                'periodo' => 'Noturno',
                'ano_entrada' => 2026,
                'bimestre_entrada' => 'B1',
            ],
        ];

        foreach ($turmas as $turma) {
            Turma::create($turma);
        }
    }
}