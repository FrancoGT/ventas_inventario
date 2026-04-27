<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigratePasswords extends BaseCommand
{
    protected $group       = 'Tools';
    protected $name        = 'migrate:passwords';
    protected $description = 'Migra contraseña de admin de MD5 a Bcrypt.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Nueva contraseña en texto plano
        $nuevaPassword = 'admin'; // O la que quieras usar

        // Hashear con Bcrypt
        $hashedPassword = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        // Actualizar el usuario admin
        $db->table('tbl_users')
           ->where('username', 'admin')
           ->update(['password' => $hashedPassword]);

        CLI::write('✅ Contraseña de admin migrada exitosamente', 'green');
        CLI::write('   Nueva contraseña: ' . $nuevaPassword, 'cyan');
        CLI::write('⚠️  Hash generado: ' . $hashedPassword, 'yellow');
    }
}