<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Muhammad Aslam',   'father_name' => 'Muhammad Akbar',  'mobile' => '03001001001', 'cnic' => '37405-0000001-1', 'home_address' => 'House 1, Saddar, Rawalpindi'],
            ['name' => 'Abdul Rehman',     'father_name' => 'Abdul Rahim',      'mobile' => '03002002002', 'cnic' => '37405-0000002-1', 'home_address' => 'House 2, Westridge, Rawalpindi'],
            ['name' => 'Ghulam Mustafa',   'father_name' => 'Ghulam Hussain',   'mobile' => '03003003003', 'cnic' => '37405-0000003-1', 'home_address' => 'House 3, Rawat, Rawalpindi'],
            ['name' => 'Iqbal Ahmed',      'father_name' => 'Shakeel Ahmed',    'mobile' => '03004004004', 'cnic' => '37405-0000004-1', 'home_address' => 'House 4, Chaklala, Rawalpindi'],
            ['name' => 'Rashid Mahmood',   'father_name' => 'Rasheed Ahmed',    'mobile' => '03005005005', 'cnic' => '37405-0000005-1', 'home_address' => 'House 5, Pindora, Rawalpindi'],
            ['name' => 'Naveed Akhtar',    'father_name' => 'Nawab Akhtar',     'mobile' => '03006006006', 'cnic' => '37405-0000006-1', 'home_address' => 'House 6, Satellite Town, Rawalpindi'],
            ['name' => 'Shahid Latif',     'father_name' => 'Abdul Latif',      'mobile' => '03007007007', 'cnic' => '37405-0000007-1', 'home_address' => 'House 7, Khayaban, Rawalpindi'],
            ['name' => 'Ejaz Hussain',     'father_name' => 'Ghazanfar Hussain', 'mobile' => '03008008008', 'cnic' => '37405-0000008-1', 'home_address' => 'House 8, Dhok Ratta, Rawalpindi'],
            ['name' => 'Kamran Ali',       'father_name' => 'Shaukat Ali',      'mobile' => '03009009009', 'cnic' => '37405-0000009-1', 'home_address' => 'House 9, Morgah, Rawalpindi'],
            ['name' => 'Pervaiz Khan',     'father_name' => 'Noor Khan',        'mobile' => '03010010010', 'cnic' => '37405-0000010-1', 'home_address' => 'House 10, Gulzar, Rawalpindi'],
            ['name' => 'Zulfiqar Ahmed',   'father_name' => 'Sardar Ahmed',     'mobile' => '03011011011', 'cnic' => '37405-0000011-1', 'home_address' => 'House 11, Peoples Colony, Rawalpindi'],
            ['name' => 'Waqar Hussain',    'father_name' => 'Anwar Hussain',    'mobile' => '03012012012', 'cnic' => '37405-0000012-1', 'home_address' => 'House 12, Dheri Hassanabad, Rawalpindi'],
            ['name' => 'Nadeem Ishaq',     'father_name' => 'Muhammad Ishaq',   'mobile' => '03013013013', 'cnic' => '37405-0000013-1', 'home_address' => 'House 13, New Katarian, Rawalpindi'],
            ['name' => 'Farrukh Butt',     'father_name' => 'Riaz Ahmed Butt',  'mobile' => '03014014014', 'cnic' => '37405-0000014-1', 'home_address' => 'House 14, Humayun Road, Rawalpindi'],
            ['name' => 'Waheed Anwar',     'father_name' => 'Anwar Sadiq',      'mobile' => '03015015015', 'cnic' => '37405-0000015-1', 'home_address' => 'House 15, Tench Bhatta, Rawalpindi'],
            ['name' => 'Shafiq Ahmed',     'father_name' => 'Rafiq Ahmed',      'mobile' => '03016016016', 'cnic' => '37405-0000016-1', 'home_address' => 'House 16, Arya Mohalla, Rawalpindi'],
            ['name' => 'Liaqat Mehmood',   'father_name' => 'Sardar Mehmood',   'mobile' => '03017017017', 'cnic' => '37405-0000017-1', 'home_address' => 'House 17, Harley Street, Rawalpindi'],
            ['name' => 'Hamid Raza',       'father_name' => 'Raza Hussain',     'mobile' => '03018018018', 'cnic' => '37405-0000018-1', 'home_address' => 'House 18, Bank Road, Rawalpindi'],
            ['name' => 'Saeed Akhter',     'father_name' => 'Akhter Ali',       'mobile' => '03019019019', 'cnic' => '37405-0000019-1', 'home_address' => 'House 19, Lalkurti, Rawalpindi'],
            ['name' => 'Tariq Javed',      'father_name' => 'Javed Iqbal',      'mobile' => '03020020020', 'cnic' => '37405-0000020-1', 'home_address' => 'House 20, Rawalpindi Cantt'],
        ];

        foreach ($customers as $data) {
            Customer::create($data);
        }
    }
}
