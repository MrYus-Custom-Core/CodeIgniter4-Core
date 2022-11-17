<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserDetail extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_detail_id' => 1,
                'user_detail_user_id' => 1,
                'user_detail_user_address' => 'Jl. Sitimulyo No 55, Somokaton, Piyungan, Bantul, Yogyakarta',
                'user_detail_user_mobilephone' => '085729929508',
                'user_detail_user_code' => 'U-221117-00001',
            ],
            [
                'user_detail_id' => 2,
                'user_detail_user_id' => 2,
                'user_detail_user_address' => 'Jl. Misterius yang gelap dan terlupakan',
                'user_detail_user_mobilephone' => 'Mister Yus',
                'user_detail_user_code' => 'U-221117-00002',
            ],
            [
                'user_detail_id' => 3,
                'user_detail_user_id' => 3,
                'user_detail_user_address' => 'Jl. Sirkush banyak binatang buas berkeliaran',
                'user_detail_user_mobilephone' => 'Sir Kush',
                'user_detail_user_code' => 'U-221117-00003',
            ],
            [
                'user_detail_id' => 4,
                'user_detail_user_id' => 4,
                'user_detail_user_address' => 'Jl. Sujak Kec Cingur',
                'user_detail_user_mobilephone' => 'Sujak Cingur',
                'user_detail_user_code' => 'U-221117-00004',
            ],
        ];
        // Using Query Builder
        $this->db->table('user_detail')->insertBatch($data);
    }
}
