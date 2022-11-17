<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'           => 'INT(11)',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_username' => [
                'type' => 'VARCHAR',
                'constraint'     => 50,
            ],
            'user_slug' => [
                'type' => 'VARCHAR',
                'constraint'     => 50,
            ],
            'user_name' => [
                'type' => 'VARCHAR',
                'constraint'     => 50,
            ],
            'user_email' => [
                'type' => 'VARCHAR',
                'constraint'     => 50,
            ],
            'user_password' => [
                'type' => 'VARCHAR',
                'constraint'     => 100,
            ],
            'user_active_bool' => [
                'type' => "ENUM('0','1')",
                'default'     => 1,
            ],
            'user_created_datetime' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'user_updated_datetime' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'user_deleted_datetime' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->createTable('user');
    }

    public function down()
    {
        $this->forge->dropTable('user');
    }
}
