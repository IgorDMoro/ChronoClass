<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator; // Importar a classe Validator
use App\Models\ProfessorHorarioDisponivel;
use App\Models\GradeHorario;
use App\Models\Sala;
use App\Models\Turma; // Já importado, mas reforçando

class StoreGradeHorarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'materia_id' => ['required', 'exists:materias,id'],
            'professor_id' => ['required', 'exists:professores,id'],
            'sala_id' => ['required', 'exists:salas,id'],
            'dia_semana' => [
                'required',
                'string',
                Rule::in(['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo']),
            ],
            'horario' => [
                'required',
                'string',
                'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            ],
            'turmas_ids' => ['required', 'array', 'min:1'],
            'turmas_ids.*' => ['integer', 'exists:turmas,id'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (isset($this->turmas_ids) && is_string($this->turmas_ids)) {
            $this->merge([
                'turmas_ids' => explode(',', $this->turmas_ids),
            ]);
        }
        if (isset($this->turmas_ids) && is_array($this->turmas_ids)) {
            $this->merge([
                'turmas_ids' => array_map('intval', $this->turmas_ids),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $professorId = $this->input('professor_id');
            $salaId = $this->input('sala_id');
            $diaSemana = $this->input('dia_semana');
            $horario = $this->input('horario');
            $turmasIds = $this->input('turmas_ids', []); // Garante que é um array

            // A. Validação de Disponibilidade do Professor
            // (Regra 5)
            if ($professorId && $diaSemana && $horario) {
                $professorDisponivel = ProfessorHorarioDisponivel::where('professor_id', $professorId)
                    ->where('dia_semana', $diaSemana)
                    ->where('horario', $horario)
                    ->exists();

                if (!$professorDisponivel) {
                    $validator->errors()->add('professor_id', 'O professor selecionado não está disponível neste dia e horário.');
                }
            }

            // B. Validação de Conflito de Professor
            // (Regra 4)
            if ($professorId && $diaSemana && $horario) {
                $conflitoProfessor = GradeHorario::where('professor_id', $professorId)
                    ->where('dia_semana', $diaSemana)
                    ->where('horario', $horario)
                    ->exists(); // Ajustar para o cenário de update: ->where('id', '!=', $this->route('grade_horario')->id)

                if ($conflitoProfessor) {
                    $validator->errors()->add('professor_id', 'Este professor já está ocupado com outra aula neste dia e horário.');
                }
            }

            // C. Validação de Conflito de Sala
            // (Regra 3)
            if ($salaId && $diaSemana && $horario) {
                $conflitoSala = GradeHorario::where('sala_id', $salaId)
                    ->where('dia_semana', $diaSemana)
                    ->where('horario', $horario)
                    ->exists(); // Ajustar para o cenário de update

                if ($conflitoSala) {
                    $validator->errors()->add('sala_id', 'Esta sala já está ocupada com outra aula neste dia e horário.');
                }
            }

            // D. Validação de Conflito de Turma
            // (Regra 7)
            if (!empty($turmasIds) && $diaSemana && $horario) {
                foreach ($turmasIds as $turmaId) {
                    $conflitoTurma = GradeHorario::whereHas('turmas', function ($query) use ($turmaId) {
                        $query->where('turmas.id', $turmaId);
                    })
                    ->where('dia_semana', $diaSemana)
                    ->where('horario', $horario)
                    ->exists(); // Ajustar para o cenário de update

                    if ($conflitoTurma) {
                        $turmaNome = Turma::find($turmaId)->nome ?? 'Turma Desconhecida';
                        $validator->errors()->add('turmas_ids', "A turma '{$turmaNome}' já possui uma aula agendada neste dia e horário.");
                        // Se um erro for encontrado, podemos parar o loop para não inundar com erros da mesma categoria
                        break;
                    }
                }
            }

            // E. Validação de Capacidade da Sala
            // (Regra 6)
            if ($salaId && !empty($turmasIds)) {
                $sala = Sala::find($salaId);
                if ($sala) {
                    $totalAlunosDasTurmas = Turma::whereIn('id', $turmasIds)->sum('num_alunos');

                    if ($totalAlunosDasTurmas > $sala->capacidade) {
                        $validator->errors()->add('sala_id', "A capacidade da sala ({$sala->capacidade} alunos) é insuficiente para as turmas selecionadas ({$totalAlunosDasTurmas} alunos).");
                    }
                }
            }

            // F. Validação de Consistência em Aulas Multiturma (Regra 8) - Opcional, se houver lógica específica
            // Se você quiser garantir que, se for uma aula multiturma, a matéria e o professor sejam os mesmos para todas as turmas,
            // essa lógica seria aplicada aqui. No nosso caso, como a 'GradeHorario' já tem materia_id e professor_id,
            // e as turmas são ANEXADAS a ESSA GradeHorario, essa regra já está implicitamente coberta pelo design.
            // Ou seja, uma única ocorrência de aula (um GradeHorario) terá apenas UM professor e UMA matéria.
            // As turmas apenas 'participam' dessa aula.

        });
    }

    /**
     * Add custom validation messages if needed.
     */
    public function messages(): array
    {
        $messages = parent::messages(); // Pega as mensagens padrão das rules

        // Adiciona ou sobrescreve mensagens customizadas para as validações 'after'
        $messages['professor_id.disponibilidade'] = 'O professor selecionado não está disponível neste dia e horário.';
        $messages['professor_id.conflito'] = 'Este professor já está ocupado com outra aula neste dia e horário.';
        $messages['sala_id.conflito'] = 'Esta sala já está ocupada com outra aula neste dia e horário.';
        $messages['turmas_ids.conflito_turma'] = 'Uma ou mais turmas selecionadas já possuem uma aula agendada neste dia e horário.';
        $messages['sala_id.capacidade_insuficiente'] = 'A capacidade da sala é insuficiente para as turmas selecionadas.';

        return $messages;
    }
}