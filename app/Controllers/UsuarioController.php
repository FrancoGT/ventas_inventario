<?php

namespace App\Controllers;

use App\Models\UserModel;

class UsuarioController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Vista del perfil.
     */
    public function perfil()
    {
        $data = [
            'titulo'   => 'Mi Perfil',
            'userData' => $this->userData,
        ];

        return view('usuario/perfil', $data);
    }

    /**
     * AJAX: Obtener datos del perfil.
     */
    public function datos()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $userId = $this->session->get('user_id');
        $perfil = $this->userModel->getPerfil($userId);

        if (!$perfil) {
            return $this->jsonError('Usuario no encontrado.', 404);
        }

        return $this->jsonSuccess('OK', [
            'id_user'           => $perfil->id_user,
            'username'          => $perfil->username,
            'nombres_apellidos' => $perfil->nombres_apellidos,
        ]);
    }

    /**
     * AJAX: Actualizar perfil.
     */
    public function actualizar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $userId = $this->session->get('user_id');

        $rules = [
            'nombres_apellidos' => 'required|min_length[3]|max_length[255]',
            'password_actual'   => 'permit_empty',
            'password_nuevo'    => 'permit_empty|min_length[4]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError(implode(', ', $this->validator->getErrors()));
        }

        // Datos a actualizar
        $dataUpdate = [
            'nombres_apellidos' => $this->request->getPost('nombres_apellidos'),
        ];

        // Si quiere cambiar contraseña
        $passActual = $this->request->getPost('password_actual');
        $passNuevo  = $this->request->getPost('password_nuevo');

        if (!empty($passNuevo)) {
            if (empty($passActual)) {
                return $this->jsonError('Debe ingresar su contraseña actual para cambiarla.');
            }

            // Verificar contraseña actual
            $user = $this->userModel->find($userId);
            if (!password_verify($passActual, $user->password)) {
                return $this->jsonError('La contraseña actual es incorrecta.');
            }

            $dataUpdate['password'] = password_hash($passNuevo, PASSWORD_BCRYPT);
        }

        // Actualizar
        $this->userModel->update($userId, $dataUpdate);

        // Actualizar sesión
        $this->session->set('nombres_apellidos', $dataUpdate['nombres_apellidos']);

        return $this->jsonSuccess('Perfil actualizado correctamente.');
    }
}