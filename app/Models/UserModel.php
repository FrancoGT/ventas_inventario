<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // Tabla asociada
    protected $table            = 'tbl_users';
    protected $primaryKey       = 'id_user';
    
    // Campos permitidos para insert/update masivo
    protected $allowedFields    = [
        'username',
        'password',
        'nombres_apellidos',
        'estado_usuario',
    ];

    // Tipo de retorno: objetos
    protected $returnType       = 'object';

    // Sin timestamps automáticos (tu tabla no los tiene)
    protected $useTimestamps    = false;

    // ----------------------------------------------------------------
    //  AUTENTICACIÓN
    // ----------------------------------------------------------------

    /**
     * Intenta autenticar un usuario.
     * Devuelve el registro del usuario o null.
     */
    public function attemptLogin(string $username, string $password): ?object
    {
        $user = $this->where('username', $username)
                     ->where('estado_usuario', 1)
                     ->first();

        if (!$user) {
            return null;
        }

        // Si ya usas password_hash() en la BD:
        if (!password_verify($password, $user->password)) {
            return null;
        }

        // Si aún guardas contraseñas en texto plano o md5 (temporal):
        // if ($user->password !== md5($password)) {
        //     return null;
        // }

        return $user;
    }

    /**
     * Obtiene un usuario por username.
     */
    public function getByUsername(string $username): ?object
    {
        return $this->where('username', $username)->first();
    }

    // ----------------------------------------------------------------
    //  PERFIL
    // ----------------------------------------------------------------

    /**
     * Obtiene datos del perfil por ID.
     */
    public function getPerfil(int $idUser): ?object
    {
        return $this->select('id_user, username, nombres_apellidos, id_persona')
                    ->find($idUser);
    }

    /**
     * Obtiene nombre completo por ID.
     */
    public function getFullName(int $idUser): string
    {
        $user = $this->select('nombres_apellidos')
                     ->find($idUser);

        return $user ? $user->nombres_apellidos : '';
    }

    // ----------------------------------------------------------------
    //  ACTUALIZACIÓN
    // ----------------------------------------------------------------

    /**
     * Actualiza datos del usuario y persona asociada.
     */
    public function actualizarPerfil(array $dataUser, array $dataPersona): bool
    {
        $db = \Config\Database::connect();

        // Actualizar usuario
        $this->update($dataUser['id_user'], $dataUser);

        // Actualizar persona
        $db->table('tbl_persona')
           ->where('id_persona', $dataPersona['id_persona'])
           ->update([
               'nombres'   => $dataPersona['nombres'],
               'apellidos' => $dataPersona['apellidos'],
           ]);

        return true;
    }
}