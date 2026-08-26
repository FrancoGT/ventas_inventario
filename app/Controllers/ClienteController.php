<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class ClienteController extends BaseController
{
    protected ClienteModel $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function index()
    {
        return view('clientes/index', [
            'titulo'   => 'Clientes',
            'userData' => $this->userData,
        ]);
    }

    public function listar()
    {
        $clientes = $this->clienteModel->getParaDatatables();
        $data = [];

        foreach ($clientes as $c) {
            $acciones = '<button class="btn btn-sm btn-editar" data-id="' . $c->id_cliente . '">
                            <i class="fas fa-edit"></i>
                         </button>
                         <button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $c->id_cliente . '">
                            <i class="fas fa-trash"></i>
                         </button>';

            $data[] = [
                'id_cliente'        => $c->id_cliente,
                'nombres_apellidos' => esc($c->nombres_apellidos),
                'tipo_documento'    => esc($c->tipo_documento),
                'numero_documento'  => esc($c->numero_documento ?? '—'),
                'telefono'          => esc($c->telefono ?? '—'),
                'acciones'          => $acciones,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * AJAX: búsqueda de clientes por nombre/documento, usada por el
     * autocomplete del formulario de Nueva Venta.
     */
    public function buscar()
    {
        $termino = (string) $this->request->getPost('termino');

        if (trim($termino) === '') {
            return $this->response->setJSON(['data' => []]);
        }

        $clientes = $this->clienteModel->buscar($termino);
        $data = [];

        foreach ($clientes as $c) {
            $data[] = [
                'id_cliente'        => $c->id_cliente,
                'nombres_apellidos' => $c->nombres_apellidos,
                'tipo_documento'    => $c->tipo_documento,
                'numero_documento'  => $c->numero_documento,
                'telefono'          => $c->telefono,
                'direccion'         => $c->direccion,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function guardar()
    {
        $rules = [
            'nombres_apellidos' => 'required|min_length[2]',
            'tipo_documento'    => 'required|in_list[DNI,RUC,SIN_DOCUMENTO]',
            'numero_documento'  => 'permit_empty|max_length[20]',
            'telefono'          => 'permit_empty|max_length[20]',
            'direccion'         => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $numeroDocumento = $this->request->getPost('numero_documento');

        if ($numeroDocumento && $this->clienteModel->existeDocumentoDuplicado($numeroDocumento)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Ya existe un cliente con ese número de documento',
            ]);
        }

        $id = $this->clienteModel->guardar([
            'nombres_apellidos' => $this->request->getPost('nombres_apellidos'),
            'tipo_documento'    => $this->request->getPost('tipo_documento'),
            'numero_documento'  => $numeroDocumento ?: null,
            'telefono'          => $this->request->getPost('telefono') ?: null,
            'direccion'         => $this->request->getPost('direccion') ?: null,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Cliente guardado correctamente',
            'data'    => ['id' => $id],
        ]);
    }

    public function editar(int $id)
    {
        $cliente = $this->clienteModel->find($id);

        if (!$cliente) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'id_cliente'        => $cliente->id_cliente,
                'nombres_apellidos' => $cliente->nombres_apellidos,
                'tipo_documento'    => $cliente->tipo_documento,
                'numero_documento'  => $cliente->numero_documento,
                'telefono'          => $cliente->telefono,
                'direccion'         => $cliente->direccion,
            ],
        ]);
    }

    public function actualizar()
    {
        $rules = [
            'id_cliente'        => 'required|numeric',
            'nombres_apellidos' => 'required|min_length[2]',
            'tipo_documento'    => 'required|in_list[DNI,RUC,SIN_DOCUMENTO]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $id = (int) $this->request->getPost('id_cliente');

        if (!$this->clienteModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        $numeroDocumento = $this->request->getPost('numero_documento');

        if ($numeroDocumento && $this->clienteModel->existeDocumentoDuplicado($numeroDocumento, $id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Ya existe otro cliente con ese número de documento',
            ]);
        }

        $this->clienteModel->actualizar($id, [
            'nombres_apellidos' => $this->request->getPost('nombres_apellidos'),
            'tipo_documento'    => $this->request->getPost('tipo_documento'),
            'numero_documento'  => $numeroDocumento ?: null,
            'telefono'          => $this->request->getPost('telefono') ?: null,
            'direccion'         => $this->request->getPost('direccion') ?: null,
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Cliente actualizado correctamente']);
    }

    public function eliminar()
    {
        $id = (int) $this->request->getPost('id_cliente');

        if ($id === ClienteModel::CLIENTE_GENERICO) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'No se puede eliminar el cliente genérico "Publico General"',
            ]);
        }

        if (!$this->clienteModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cliente no encontrado']);
        }

        $this->clienteModel->eliminar($id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Cliente eliminado correctamente']);
    }
}
