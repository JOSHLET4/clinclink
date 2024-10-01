<?php

namespace Database\Seeders;

use App\Models\AppointmentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = ['en cola', 'cancelado', 'aceptado', 'terminado'];
        foreach ($states as $state) {
            AppointmentStatus::create([
                'name' => $state
            ]);
        }
    }
}
