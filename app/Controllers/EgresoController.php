<?php

namespace App\Controllers;

use App\Models\EgresoModel;

class EgresoController extends BaseController
{
    protected EgresoModel $egresoModel;

    public function __construct()
    {
        $this->egresoModel = new EgresoModel();
    }

    /**
     * Vista principal de egresos.
     */
    public function index()
    {
        $data = [
            'titulo'   => 'Egresos',
            'userData' => $this->userData,
        ];

        return view('egresos/index', $data);
    }

    /**
     * AJAX: Listar egresos.
     */
    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $egresos = $this->egresoModel->getParaDatatables();
        $data    = [];

        foreach ($egresos as $egreso) {
            $data[] = [
                'id_egreso'        => $egreso->id_egreso,
                'fecha'            => $egreso->fecha,
                'compra_mercaderia' => number_format($egreso->compra_mercaderia, 2),
                'flete'            => number_format($egreso->flete, 2),
                'descripcion'      => esc($egreso->descripcion),
            ];
        }

        return $this->response->setJSON([
            'data'            => $data,
            'recordsTotal'    => $this->egresoModel->countAllResults(false),
            'recordsFiltered' => count($data),
        ]);
    }

    /**
     * AJAX: Guardar egreso.
     */
    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $rules = [
            'compra_mercaderia' => 'required|numeric|greater_than_equal_to[0]',
            'flete'             => 'required|numeric|greater_than_equal_to[0]',
            'descripcion'       => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError(implode(', ', $this->validator->getErrors()));
        }

        $id = $this->egresoModel->guardar([
            'compra_mercaderia' => $this->request->getPost('compra_mercaderia'),
            'flete'             => $this->request->getPost('flete'),
            'descripcion'       => $this->request->getPost('descripcion'),
        ]);

        return $this->jsonSuccess('Egreso registrado correctamente.', ['id' => $id]);
    }
}