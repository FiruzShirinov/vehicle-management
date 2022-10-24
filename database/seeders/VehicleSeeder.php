<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Vehicle::insert([
            [
                'make' => 'Dodge',
                'model' => 'Charger R/T',
                'year' => 1970,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'make' => 'Nissan',
                'model' => 'Skyline GT-R',
                'year' => 2002,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'make' => 'Nissan',
                'model' => '240SX',
                'year' => 1997,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'make' => 'Aston Martin',
                'model' => 'DB9',
                'year' => 2008,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'make' => 'Acura',
                'model' => 'NSX',
                'year' => 2002,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
