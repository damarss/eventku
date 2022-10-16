<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'auto_increment' => true,
            ], 'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ], 'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ], 'password' => [
                'type' => 'TEXT'
            ], 'name' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ], 'role' => [
                'type' => 'ENUM',
                'constraint' => array('admin', 'user'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
