<?php

use Illuminate\Database\Seeder;
use App\IncidentType;

class IncidentTypesTableSeeder extends Seeder
{
    public $incidentTypes = [
        [
            'id' => 1,
            'label' => 'IT_OTHER',
            'code' => 'OTH',
            'name' => 'Altul'
        ],
        [
            'id' => 2,
            'label' => 'IT_OBSERVERS',
            'code' => 'ELE',
            'name' => 'Probleme legate de campania electorală'
        ],
        [
            'id' => 3,
            'label' => 'IT_MEDIA',
            'code' => 'MED',
            'name' => 'Discurs instigator la ură'
        ],
        [
            'id' => 4,
            'label' => 'IT_BRIBE',
            'code' => 'MIT',
            'name' => 'Vot multiplu/Mită electorală'
        ],
        [
            'id' => 5,
            'label' => 'BUILDING',
            'code' => 'NBE',
            'name' => 'Nereguli legate de funcționarea administrației electorale'
        ],
        [
            'id' => 7,
            'label' => 'LOCATION',
            'code' => 'OBP',
            'name' => 'Vot în străinătate'
        ],
        [
            'id' => 8,
            'label' => 'IT_ELEC_TURISM',
            'code' => 'TEL',
            'name' => 'Transport organizat de alegători'
        ],
        [
            'id' => 9,
            'label' => 'IT_PUBLIC_FOUNDS',
            'code' => 'FEL',
            'name' => 'Abuz de resurse publice'
        ],
        [
            'id' => 11,
            'label' => 'POLICE',
            'code' => 'OPN',
            'name' => 'Presiuni din partea autorităților locale'
        ],
        [
            'id' => 12,
            'label' => 'VOTE',
            'code' => 'NUM',
            'name' => 'Probleme în zilele votului'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->incidentTypes as $incidentType) {
            IncidentType::create($incidentType);
        }
    }

}
