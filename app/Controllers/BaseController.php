<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Helpers cargados automáticamente en todos los controladores.
     */
    protected $helpers = ['url', 'form', 'text'];

    /**
     * Sesión disponible en todos los controladores.
     */
    protected $session;

    /**
     * Datos del usuario logueado.
     */
    protected array $userData = [];

    /**
     * Constructor — se ejecuta antes de cada método del controlador.
     */
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Iniciar sesión
        $this->session = session();

        // Cargar datos del usuario si está logueado
        if ($this->session->get('user_id')) {
            $this->userData = [
                'user_id'           => $this->session->get('user_id'),
                'username'          => $this->session->get('username'),
                'nombres_apellidos' => $this->session->get('nombres_apellidos'),
            ];
        }
    }

    /**
     * Respuesta JSON estandarizada para éxito.
     */
    protected function jsonSuccess(string $message = 'Operación exitosa', array $data = [], int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Respuesta JSON estandarizada para error.
     */
    protected function jsonError(string $message = 'Error', int $code = 400)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => false,
            'message' => $message,
        ]);
    }
}