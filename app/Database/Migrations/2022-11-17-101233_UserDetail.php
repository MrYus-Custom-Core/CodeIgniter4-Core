<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserDetail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_detail_id' => [
                'type'           => 'INT(11)',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_detail_user_id' => [
                'type'           => 'INT(11)',
                'unsigned'       => true,
            ],
            'user_detail_user_code' => [
                'type' => 'VARCHAR',
                'constraint'     => 10,
            ],
            'user_detail_user_address' => [
                'type' => 'VARCHAR',
                'constraint'     => 150,
            ],
            'user_detail_user_mobilephone' => [
                'type' => 'VARCHAR',
                'constraint'     => 20,
            ],
        ]);
        $this->forge->addKey('user_detail_id', true);
        $this->forge->addKey('user_detail_user_id');
        $this->forge->createTable('user_detail');
    }

    public function down()
    {
        $this->forge->dropTable('user_detail');
    }
}
