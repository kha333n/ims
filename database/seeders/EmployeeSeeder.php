<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $saleMen = [
            ['name' => 'Tariq Mehmood', 'phone' => '03001111111', 'commission_percent' => 2],
            ['name' => 'Asif Iqbal',    'phone' => '03002222222', 'commission_percent' => 2],
            ['name' => 'Nasir Hussain', 'phone' => '03003333333', 'commission_percent' => 3],
        ];

        foreach ($saleMen as $data) {
            Employee::create(array_merge($data, ['type' => 'sale_man']));
        }

        $recoveryMen = [
            ['name' => 'Khalid Pervez', 'phone' => '03011111111', 'area' => 'Saddar',    'rank' => 'Senior', 'salary' => 3000000],
            ['name' => 'Imran Khan',    'phone' => '03022222222', 'area' => 'Rawat',     'rank' => 'Junior', 'salary' => 2500000],
            ['name' => 'Jamil Ahmed',   'phone' => '03033333333', 'area' => 'Westridge', 'rank' => 'Senior', 'salary' => 3000000],
            ['name' => 'Saleem Raza',   'phone' => '03044444444', 'area' => 'Chaklala',  'rank' => 'Junior', 'salary' => 2500000],
        ];

        foreach ($recoveryMen as $data) {
            Employee::create(array_merge($data, ['type' => 'recovery_man']));
        }
    }
}
