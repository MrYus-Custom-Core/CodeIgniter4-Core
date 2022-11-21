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
                'user_detail_user_address' => 'Jl. Raya Ya nda tau kog tanya saya',
                'user_detail_user_mobilephone' => '0899988775522',
                'user_detail_user_code' => 'U-221117-00001',
            ],
            [
                'user_detail_id' => 2,
                'user_detail_user_id' => 2,
                'user_detail_user_address' => 'Jl. Misterius yang gelap dan terlupakan',
                'user_detail_user_mobilephone' => '089123456789',
                'user_detail_user_code' => 'U-221117-00002',
            ],
            [
                'user_detail_id' => 3,
                'user_detail_user_id' => 3,
                'user_detail_user_address' => 'Jl. Sirkush banyak binatang buas berkeliaran',
                'user_detail_user_mobilephone' => '088123456789',
                'user_detail_user_code' => 'U-221117-00003',
            ],
            [
                'user_detail_id' => 4,
                'user_detail_user_id' => 4,
                'user_detail_user_address' => 'Jl. Sujak Kec Cingur',
                'user_detail_user_mobilephone' => '087123456789',
                'user_detail_user_code' => 'U-221117-00004',
            ],
        ];
        // Using Query Builder
        $this->db->table('user_detail')->insertBatch($data);
    }
}
