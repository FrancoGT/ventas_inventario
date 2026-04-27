<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Type casting para que Intelephense reconozca los métodos
        /** @var IncomingRequest $request */
        
        $session = session();

        if (!$session->get('user_id')) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'Sesión expirada. Inicie sesión nuevamente.',
                    ]);
            }

            $session->setTempdata('redirect_url', current_url(), 300);
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}