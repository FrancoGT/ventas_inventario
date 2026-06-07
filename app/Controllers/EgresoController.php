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

    public function index()
    {
        $data = [
            'titulo'   => 'Egresos',
            'userData' => $this->userData ?? null,
        ];

        return view('egresos/index', $data);
    }

    public function listar()
    {
        $egresos = $this->egresoModel->getActivos();
        $data = [];

        foreach ($egresos as $egreso) {
            $data[] = [
                'id_egreso'         => $egreso->id_egreso,
                'fecha'             => $egreso->fecha,
                'compra_mercaderia' => number_format((float) $egreso->compra_mercaderia, 2),
                'flete'             => number_format((float) $egreso->flete, 2),
                'descripcion'       => esc($egreso->descripcion),
                'acciones'          =>
                    '<button class="btn btn-sm btn-editar" data-id="' . $egreso->id_egreso . '">
                        <i class="fas fa-edit"></i>
                    </button> ' .
                    '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $egreso->id_egreso . '">
                        <i class="fas fa-trash"></i>
                    </button>'
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function guardar()
    {
        $rules = [
            'compra_mercaderia' => 'required|numeric|greater_than_equal_to[0]',
            'flete'             => 'required|numeric|greater_than_equal_to[0]',
            'descripcion'       => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors())
            ]);
        }

        $id = $this->egresoModel->guardar([
            'compra_mercaderia' => $this->request->getPost('compra_mercaderia'),
            'flete'             => $this->request->getPost('flete'),
            'descripcion'       => $this->request->getPost('descripcion') ?? '',
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Egreso guardado correctamente',
            'data'    => ['id' => $id]
        ]);
    }

    public function editar(int $id)
    {
        $egreso = $this->egresoModel->find($id);

        if (!$egreso) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Egreso no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'id_egreso'         => $egreso->id_egreso,
                'fecha'             => $egreso->fecha,
                'compra_mercaderia' => $egreso->compra_mercaderia,
                'flete'             => $egreso->flete,
                'descripcion'       => $egreso->descripcion,
            ]
        ]);
    }

    public function actualizar()
    {
        $rules = [
            'id_egreso'         => 'required|numeric',
            'compra_mercaderia' => 'required|numeric|greater_than_equal_to[0]',
            'flete'             => 'required|numeric|greater_than_equal_to[0]',
            'descripcion'       => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors())
            ]);
        }

        $id = (int) $this->request->getPost('id_egreso');

        if (!$this->egresoModel->find($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Egreso no encontrado'
            ]);
        }

        $this->egresoModel->actualizar($id, [
            'compra_mercaderia' => $this->request->getPost('compra_mercaderia'),
            'flete'             => $this->request->getPost('flete'),
            'descripcion'       => $this->request->getPost('descripcion') ?? '',
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Egreso actualizado correctamente'
        ]);
    }

    public function eliminar()
    {
        $id = (int) $this->request->getPost('id_egreso');

        if (!$this->egresoModel->find($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Egreso no encontrado'
            ]);
        }

        $this->egresoModel->eliminar($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Egreso eliminado correctamente'
        ]);
    }
}