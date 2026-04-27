<?php

namespace App\Controllers;

use App\Models\ProductoModel;

class ProductoController extends BaseController
{
    protected ProductoModel $productoModel;

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Productos'
        ];
        return view('productos/index', $data);
    }

    public function listar()
    {
        $productos = $this->productoModel->getActivos();
        $data = [];

        foreach ($productos as $producto) {
            $data[] = [
                'id_producto'   => $producto->id_producto,
                'codigo_barras' => esc($producto->codigo_barras),
                'nombre'        => esc($producto->nombre),
                'precio'        => number_format($producto->precio, 2),
                'acciones'      => 
                    '<button class="btn btn-sm btn-warning btn-editar" data-id="' . $producto->id_producto . '">
                        <i class="fas fa-edit"></i>
                    </button> ' .
                    '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $producto->id_producto . '">
                        <i class="fas fa-trash"></i>
                    </button>'
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function guardar()
    {
        $rules = [
            'nombre'        => 'required|min_length[2]',
            'precio'        => 'required|numeric|greater_than[0]',
            'codigo_barras' => 'required|min_length[1]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors())
            ]);
        }

        $nombre = $this->request->getPost('nombre');
        $codigoBarras = $this->request->getPost('codigo_barras');

        if ($this->productoModel->existeDuplicado('nombre', $nombre)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ya existe un producto con ese nombre'
            ]);
        }

        if ($this->productoModel->existeDuplicado('codigo_barras', $codigoBarras)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ya existe un producto con ese código de barras'
            ]);
        }

        $id = $this->productoModel->guardar([
            'nombre'        => $nombre,
            'precio'        => $this->request->getPost('precio'),
            'codigo_barras' => $codigoBarras,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Producto guardado correctamente',
            'data' => ['id' => $id]
        ]);
    }

    public function editar(int $id)
    {
        $producto = $this->productoModel->find($id);

        if (!$producto) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'id_producto'   => $producto->id_producto,
                'nombre'        => $producto->nombre,
                'precio'        => $producto->precio,
                'codigo_barras' => $producto->codigo_barras,
            ]
        ]);
    }

    public function actualizar()
    {
        $rules = [
            'id_producto'   => 'required|numeric',
            'nombre'        => 'required|min_length[2]',
            'precio'        => 'required|numeric|greater_than[0]',
            'codigo_barras' => 'required|min_length[1]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors())
            ]);
        }

        $id = (int) $this->request->getPost('id_producto');
        $nombre = $this->request->getPost('nombre');
        $codigoBarras = $this->request->getPost('codigo_barras');

        if (!$this->productoModel->find($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ]);
        }

        if ($this->productoModel->existeDuplicado('nombre', $nombre, $id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ya existe otro producto con ese nombre'
            ]);
        }

        if ($this->productoModel->existeDuplicado('codigo_barras', $codigoBarras, $id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ya existe otro producto con ese código de barras'
            ]);
        }

        $this->productoModel->actualizar($id, [
            'nombre'        => $nombre,
            'precio'        => $this->request->getPost('precio'),
            'codigo_barras' => $codigoBarras,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Producto actualizado correctamente'
        ]);
    }

    public function eliminar()
    {
        $id = (int) $this->request->getPost('id_producto');

        if (!$this->productoModel->find($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ]);
        }

        $this->productoModel->eliminar($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Producto eliminado correctamente'
        ]);
    }
}