<?php

namespace Database\Seeders;

use App\Globals\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LadaSeeder extends Seeder
{
    public function run(): void
    {
        // abbr = abreviatura usada para ordenar y mostrar en el selector
        $ladas = [
            ['abbr' => 'AR', 'country' => 'Argentina',              'code' => '54'],
            ['abbr' => 'BO', 'country' => 'Bolivia',                'code' => '591'],
            ['abbr' => 'BR', 'country' => 'Brasil',                 'code' => '55'],
            ['abbr' => 'BZ', 'country' => 'Belice',                 'code' => '501'],
            ['abbr' => 'CL', 'country' => 'Chile',                  'code' => '56'],
            ['abbr' => 'CO', 'country' => 'Colombia',               'code' => '57'],
            ['abbr' => 'CR', 'country' => 'Costa Rica',             'code' => '506'],
            ['abbr' => 'DO', 'country' => 'Republica Dominicana',   'code' => '1809'],
            ['abbr' => 'DO', 'country' => 'Republica Dominicana',   'code' => '1829'],
            ['abbr' => 'DO', 'country' => 'Republica Dominicana',   'code' => '1849'],
            ['abbr' => 'EC', 'country' => 'Ecuador',                'code' => '593'],
            ['abbr' => 'GF', 'country' => 'Guayana Francesa',       'code' => '594'],
            ['abbr' => 'GT', 'country' => 'Guatemala',              'code' => '502'],
            ['abbr' => 'GY', 'country' => 'Guyana',                 'code' => '592'],
            ['abbr' => 'HN', 'country' => 'Honduras',               'code' => '504'],
            ['abbr' => 'HT', 'country' => 'Haiti',                  'code' => '509'],
            ['abbr' => 'JM', 'country' => 'Jamaica',                'code' => '1876'],
            ['abbr' => 'MX', 'country' => 'Mexico',                 'code' => '52'],
            ['abbr' => 'MX', 'country' => 'Mexico WhatsApp',        'code' => '521'],
            ['abbr' => 'NI', 'country' => 'Nicaragua',              'code' => '505'],
            ['abbr' => 'PA', 'country' => 'Panama',                 'code' => '507'],
            ['abbr' => 'PE', 'country' => 'Peru',                   'code' => '51'],
            ['abbr' => 'PR', 'country' => 'Puerto Rico',            'code' => '1787'],
            ['abbr' => 'PY', 'country' => 'Paraguay',               'code' => '595'],
            ['abbr' => 'SR', 'country' => 'Surinam',                'code' => '597'],
            ['abbr' => 'SV', 'country' => 'El Salvador',            'code' => '503'],
            ['abbr' => 'US', 'country' => 'Estados Unidos / Canada','code' => '1'],
            ['abbr' => 'UY', 'country' => 'Uruguay',                'code' => '598'],
            ['abbr' => 'VE', 'country' => 'Venezuela',              'code' => '58'],
        ];

        foreach ($ladas as $lada) {
            DB::table('ladas')->updateOrInsert(
                ['code' => $lada['code']],
                [
                    'country' => $lada['country'],
                    'label'   => sprintf('%s (+%s)', $lada['abbr'], $lada['code']),
                    'status'  => Status::ACTIVE,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
