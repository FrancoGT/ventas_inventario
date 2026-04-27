<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    /**
     * Mostrar formulario de login.
     */
    public function index()
    {
        // Si ya está logueado, redirigir al dashboard
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Procesar el inicio de sesión.
     */
    public function authenticate()
    {
        // Validar campos del formulario
        $rules = [
            'username' => [
                'rules'  => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required'   => 'El usuario es obligatorio.',
                    'min_length' => 'El usuario debe tener al menos 3 caracteres.',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[4]',
                'errors' => [
                    'required'   => 'La contraseña es obligatoria.',
                    'min_length' => 'La contraseña debe tener al menos 4 caracteres.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/login')
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        // Intentar autenticación
        $userModel = new UserModel();
        $username  = $this->request->getPost('username');
        $password  = $this->request->getPost('password');

        $user = $userModel->attemptLogin($username, $password);

        if (!$user) {
            return redirect()->to('/login')
                             ->withInput()
                             ->with('error', 'Usuario o contraseña incorrectos.');
        }

        // Crear sesión
        $this->session->set([
            'user_id'           => $user->id_user,
            'username'          => $user->username,
            'nombres_apellidos' => $user->nombres_apellidos,
            'isLoggedIn'        => true,
        ]);

        // Regenerar ID de sesión por seguridad
        $this->session->regenerate();

        // Redirigir al destino original o al dashboard
        $redirectUrl = $this->session->getTempdata('redirect_url') ?? '/dashboard';
        return redirect()->to($redirectUrl)
                         ->with('success', '¡Bienvenido, ' . $user->nombres_apellidos . '!');
    }

    /**
     * Cerrar sesión.
     */
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login')
                         ->with('success', 'Sesión cerrada correctamente.');
    }
}