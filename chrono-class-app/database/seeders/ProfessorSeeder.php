<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Professor;
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

        $matriculaInicial = 25001; // Matrícula inicial

        foreach ($professores as $nomeCompleto) {
            $partesNome = explode(' ', $nomeCompleto);
            $primeiroNome = Str::slug($partesNome[0]);
            $ultimoSobrenome = Str::slug(end($partesNome));

            $email = strtolower("{$primeiroNome}.{$ultimoSobrenome}@edu.unifil.br");
            
            // Gera um número de telefone aleatório no formato (43) 9XXXX-XXXX
            $telefone = '(43) 9' . rand(8000, 9999) . '-' . rand(1000, 9999);

            Professor::create([
                'nome' => $nomeCompleto,
                'email' => $email,
                'matricula' => (string) $matriculaInicial, // Converte para string para corresponder ao tipo da coluna
                'telefone' => $telefone,
            ]);

            $matriculaInicial++; // Incrementa para o próximo professor
        }
    }
}

