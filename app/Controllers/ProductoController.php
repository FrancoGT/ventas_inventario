<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Models\StockModel;
use App\Models\StockMovimientoModel;

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
        $stockModel = new StockModel();
        $mapaStock = $stockModel->getMapaStock();
        $data = [];

        foreach ($productos as $producto) {
            $data[] = [
                'id_producto'   => $producto->id_producto,
                'codigo_barras' => esc($producto->codigo_barras),
                'nombre'        => esc($producto->nombre),
                'precio'        => number_format($producto->precio, 2),
                'stock'         => isset($mapaStock[$producto->id_producto]) ? (int)$mapaStock[$producto->id_producto]->stock_actual : 0,
                'acciones'      => 
                    '<button class="btn btn-sm btn-success btn-stock me-1" data-id="' . $producto->id_producto . '" title="Agregar stock"><i class="fas fa-boxes"></i></button> ' .
                    '<button class="btn btn-sm btn-editar" data-id="' . $producto->id_producto . '">
                        <i class="fas fa-edit"></i>
                    </button> ' .
                    '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $producto->id_producto . '">
                        <i class="fas fa-trash"></i>
                    </button>'
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }


    /**
     * AJAX: búsqueda de productos para autocomplete de ventas.
     */
    public function buscar()
    {
        $termino = trim((string) $this->request->getPost('termino'));

        if ($termino === '') {
            return $this->response->setJSON(['data' => []]);
        }

        $productos = $this->productoModel->buscar($termino);
        $data = [];

        foreach ($productos as $p) {
            $data[] = [
                'id_producto' => (int) $p->id_producto,
                'nombre' => $p->nombre,
                'codigo_barras' => $p->codigo_barras,
                'precio' => $p->precio
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

    public function agregarStock()
    {
        $id = (int)$this->request->getPost('id_producto');
        $cantidad = (int)$this->request->getPost('cantidad');

        if ($id <= 0 || $cantidad <= 0) {
            return $this->response->setJSON(['status'=>'error','message'=>'Cantidad inválida']);
        }

        if (!$this->productoModel->find($id)) {
            return $this->response->setJSON(['status'=>'error','message'=>'Producto no encontrado']);
        }

        $stock = new StockModel();
        $mov = new StockMovimientoModel();
        $stock->incrementar($id, $cantidad);
        $mov->registrarAjuste($id, 'INGRESO', $cantidad, 'Carga manual de stock');

        return $this->response->setJSON(['status'=>'success','message'=>'Stock actualizado correctamente']);
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