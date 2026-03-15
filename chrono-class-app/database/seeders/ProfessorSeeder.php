<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\ProfessorHorarioDisponivel;
use Illuminate\Support\Str;

class ProfessorSeeder extends Seeder
{
    /**
     * Executa os seeds da base de dados.
     */
    public function run(): void
    {
        $professores = [
            'Adalberto Pereira Neris', 'Alisson Henrique dos Santos', 'Anderson Yoshiaki Iwazaki da Silva',
            'Bruna Thais Silva Sozzo', 'Bruno Henrique Coleto', 'Daniella Carolina Camargo Torelli',
            'Eder Diego de Oliveira', 'Edson Shinki Kaneshima', 'Felipe Nathan dos Anjos',
            'Fernando Murilo Lourenço Roque', 'Gustavo Taiji Naozuka', 'Igor da Silva Elias',
            'João Vitor da Costa Andrade', 'Luis Carlos Albuquerque da Silva', 'Luiz Fernando Pereira Nunes',
            'Marcelo Kioyassu Nakasse', 'Maicon Roger do Rosario', 'Marcelo Yukio Yamamoto',
            'Marcus Vinicius Santana Maziero', 'Mario Henrique Akihiko da Costa Adaniya', 'Ricardo Petri Silva',
            'Robson de Lacerda Zambroti', 'Sergio Akio Tanaka', 'Simone Sawasaki Tanaka',
            'Suzana Rezende Lemanski', 'Tania Camila Kochmanscky Goulart', 'Tiago Ravache',
            'Wilson Sanches', 'Guilherme Cardoso Agostinetti', 'Eron Ponce Pereira',
            'Fernando Nakagawa', 'Gustavo Queiroz Silveira',
        ];

        $diasSemana = ['segunda', 'terça', 'quarta', 'quinta', 'sexta'];
        $diasFimDeSemana = ['sábado'];
        $horariosSemana = ['19:00-20:30', '20:45-22:15'];
        $horariosFimDeSemana = ['08:00-09:30', '10:00-12:00'];

        $matriculaInicial = 25001;

        foreach ($professores as $nomeCompleto) {
            $partesNome = explode(' ', $nomeCompleto);
            $primeiroNome = Str::slug($partesNome[0]);
            $ultimoSobrenome = Str::slug(end($partesNome));

            $email = strtolower("{$primeiroNome}.{$ultimoSobrenome}@edu.unifil.br");
            $telefone = '(43) 9' . rand(8000, 9999) . '-' . rand(1000, 9999);

            $professor = Professor::create([
                'nome'      => $nomeCompleto,
                'email'     => $email,
                'matricula' => (string) $matriculaInicial,
                'telefone'  => $telefone,
            ]);

            // Gera disponibilidades aleatórias para dias da semana
            // Cada professor fica disponível em pelo menos 2 e no máximo 4 dias
            $diasDisponiveis = collect($diasSemana)->shuffle()->take(rand(2, 4));

            foreach ($diasDisponiveis as $dia) {
                // Para cada dia disponível, pode ter 1 ou os 2 blocos
                $horariosDisponiveis = collect($horariosSemana)->shuffle()->take(rand(1, 2));

                foreach ($horariosDisponiveis as $horario) {
                    ProfessorHorarioDisponivel::create([
                        'professor_id' => $professor->id,
                        'dia_semana'   => $dia,
                        'horario'      => $horario,
                    ]);
                }
            }

            // 40% de chance de ter disponibilidade no sábado
            if (rand(1, 10) <= 4) {
                $horariosSabado = collect($horariosFimDeSemana)->shuffle()->take(rand(1, 2));

                foreach ($horariosSabado as $horario) {
                    ProfessorHorarioDisponivel::create([
                        'professor_id' => $professor->id,
                        'dia_semana'   => 'sábado',
                        'horario'      => $horario,
                    ]);
                }
            }

            $matriculaInicial++;
        }
    }
}