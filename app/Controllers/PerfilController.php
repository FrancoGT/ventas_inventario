<?php

namespace App\Controllers;

class PerfilController extends BaseController
{
    public function index()
    {
        return view('perfil/index', [
            'titulo'   => 'Mi Perfil',
            'userData' => $this->userData ?? null,
        ]);
    }
}