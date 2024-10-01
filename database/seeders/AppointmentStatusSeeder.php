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
        $statuses = ['en cola', 'cancelado', 'aceptado', 'terminado'];
        foreach ($statuses as $status) {
            AppointmentStatus::create([
                'name' => $status
            ]);
        }
    }
}
