<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_id' => 1,
                'user_active_bool' => '1',
                'user_username' => 'romihaidar',
                'user_slug' => 'romi-haidar',
                'user_name' => 'Romi Haidar',
                'user_email' => 'romihaidar@gmail.com',
                'user_password' => password_hash('123456', PASSWORD_BCRYPT),
                'user_created_datetime' => '2022-11-17 17:49:00'
            ],
            [
                'user_id' => 2,
                'user_active_bool' => '1',
                'user_username' => 'mryus',
                'user_slug' => 'mister-yus',
                'user_name' => 'Mister Yus',
                'user_email' => 'misteryus@gmail.com',
                'user_password' => password_hash('1234567', PASSWORD_BCRYPT),
                'user_created_datetime' => '2022-11-17 17:50:00'
            ],
            [
                'user_id' => 3,
                'user_active_bool' => '0',
                'user_username' => 'sirkush',
                'user_slug' => 'sir-kush',
                'user_name' => 'Sir Kush',
                'user_email' => 'sirkush@gmail.com',
                'user_password' => password_hash('12345678', PASSWORD_BCRYPT),
                'user_created_datetime' => '2022-11-17 17:51:00'
            ],
            [
                'user_id' => 4,
                'user_active_bool' => '1',
                'user_username' => 'sujakcingur',
                'user_slug' => 'sujak-cingur',
                'user_name' => 'Sujak Cingur',
                'user_email' => 'sujakcingur@gmail.com',
                'user_password' => password_hash('123456789', PASSWORD_BCRYPT),
                'user_created_datetime' => '2022-11-17 17:52:00'
            ],
        ];
        // Using Query Builder
        $this->db->table('user')->insertBatch($data);
    }
}
