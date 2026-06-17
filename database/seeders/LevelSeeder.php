<?php

namespace Database\Seeders;

use App\Models\Operation\Level;
use App\Models\Ecclesiastes\Diocese;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer ID de diócesis disponible
        $diocese = Diocese::first();

        if (!$diocese) {
            // Si no hay diócesis, crear una temporal o lanzar error
            $diocese = Diocese::create([
                'name' => 'Diócesis Temporal',
                'description' => 'Diócesis temporal para seeding'
            ]);
        }

        $dioceseId = $diocese->id;

        $levels = [
            // Grupo 1: Amarillo (Descubro a mi Papá Dios)
            [
                'name' => 'Nivel 1',
                'description' => 'Descubro a mi Papá Dios',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 2',
                'description' => 'Descubro a mi Papá Dios',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 3',
                'description' => 'Descubro a mi Papá Dios',
                'diocese_id' => $dioceseId
            ],

            // Grupo 2: Naranja (Jesús vive entre nosotros)
            [
                'name' => 'Nivel 4',
                'description' => 'Jesús vive entre nosotros',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 5',
                'description' => 'Jesús vive entre nosotros',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 6',
                'description' => 'Jesús vive entre nosotros',
                'diocese_id' => $dioceseId
            ],

            // Grupo 3: Azul (Por el espíritu conozco y vivo mi fe)
            [
                'name' => 'Nivel 7',
                'description' => 'Por el espíritu conozco y vivo mi fe',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 8',
                'description' => 'Por el espíritu conozco y vivo mi fe',
                'diocese_id' => $dioceseId
            ],
            [
                'name' => 'Nivel 9',
                'description' => 'Por el espíritu conozco y vivo mi fe',
                'diocese_id' => $dioceseId
            ]
        ];

        // Usar insert para múltiples registros :cite[1]:cite[3]
        Level::insert($levels);
    }
}