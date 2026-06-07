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
            'userData' => $this->userData,
        ];

        return view('egresos/index', $data);
    }

    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $egresos = $this->egresoModel->getParaDatatables();
        $data    = [];

        foreach ($egresos as $egreso) {
            $data[] = [
                'id_egreso'         => $egreso->id_egreso,
                'fecha'             => $egreso->fecha,
                'compra_mercaderia' => number_format((float) $egreso->compra_mercaderia, 2, '.', ''),
                'flete'             => number_format((float) $egreso->flete, 2, '.', ''),
                'descripcion'       => esc($egreso->descripcion),
            ];
        }

        return $this->response->setJSON([
            'data'            => $data,
            'recordsTotal'    => $this->egresoModel->contarActivos(),
            'recordsFiltered' => count($data),
        ]);
    }

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
            'descripcion'       => $this->request->getPost('descripcion') ?? '',
        ]);

        return $this->jsonSuccess('Egreso registrado correctamente.', [
            'id' => $id,
        ]);
    }

    public function obtener()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $id = (int) $this->request->getPost('id_egreso');

        if ($id <= 0) {
            return $this->jsonError('ID no válido');
        }

        $egreso = $this->egresoModel->obtenerPorId($id);

        if (!$egreso) {
            return $this->jsonError('Egreso no encontrado');
        }

        return $this->jsonSuccess('Egreso encontrado.', [
            'egreso' => $egreso,
        ]);
    }

    public function actualizar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $id = (int) $this->request->getPost('id_egreso');

        if ($id <= 0) {
            return $this->jsonError('ID no válido');
        }

        $egreso = $this->egresoModel->obtenerPorId($id);

        if (!$egreso) {
            return $this->jsonError('Egreso no encontrado');
        }

        $rules = [
            'compra_mercaderia' => 'required|numeric|greater_than_equal_to[0]',
            'flete'             => 'required|numeric|greater_than_equal_to[0]',
            'descripcion'       => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError(implode(', ', $this->validator->getErrors()));
        }

        $actualizado = $this->egresoModel->actualizarEgreso($id, [
            'compra_mercaderia' => $this->request->getPost('compra_mercaderia'),
            'flete'             => $this->request->getPost('flete'),
            'descripcion'       => $this->request->getPost('descripcion') ?? '',
        ]);

        if (!$actualizado) {
            return $this->jsonError('No se pudo actualizar el egreso.');
        }

        return $this->jsonSuccess('Egreso actualizado correctamente.');
    }

    public function eliminar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $id = (int) $this->request->getPost('id_egreso');

        if ($id <= 0) {
            return $this->jsonError('ID no válido');
        }

        $egreso = $this->egresoModel->obtenerPorId($id);

        if (!$egreso) {
            return $this->jsonError('Egreso no encontrado');
        }

        $eliminado = $this->egresoModel->eliminarEgreso($id);

        if (!$eliminado) {
            return $this->jsonError('No se pudo eliminar el egreso.');
        }

        return $this->jsonSuccess('Egreso eliminado correctamente.');
    }
}