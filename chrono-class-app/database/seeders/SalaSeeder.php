<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sala;

class SalaSeeder extends Seeder
{
    public function run(): void
    {
        $salas = [
            // Campus Sede
            ['nome' => 'LAB 2', 'capacidade' => 39, 'campus' => 'campus sede'],
            ['nome' => 'LAB 3', 'capacidade' => 50, 'campus' => 'campus sede'],
            ['nome' => 'LAB 4', 'capacidade' => 39, 'campus' => 'campus sede'],
            ['nome' => 'LAB 6', 'capacidade' => 68, 'campus' => 'campus sede'],
            ['nome' => 'LAB 7', 'capacidade' => 68, 'campus' => 'campus sede'],
            ['nome' => 'LAB 8', 'capacidade' => 72, 'campus' => 'campus sede'],

            // Campus Ipolón
            ['nome' => 'Sala 1026', 'capacidade' => 40, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1027', 'capacidade' => 66, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1028', 'capacidade' => 30, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1029', 'capacidade' => 30, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1030', 'capacidade' => 40, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1031', 'capacidade' => 40, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1032', 'capacidade' => 65, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1033', 'capacidade' => 30, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1034', 'capacidade' => 40, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1035', 'capacidade' => 60, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1036', 'capacidade' => 60, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1037', 'capacidade' => 60, 'campus' => 'campus ipolon'],
            ['nome' => 'Sala 1038', 'capacidade' => 60, 'campus' => 'campus ipolon'],
        ];

        foreach ($salas as $sala) {
            Sala::create($sala);
        }
    }
}