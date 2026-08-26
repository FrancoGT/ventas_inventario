<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table            = 'tbl_producto';
    protected $primaryKey       = 'id_producto';

    protected $allowedFields    = [
        'nombre',
        'precio',
        'codigo_barras',
        'estado_producto',
    ];

    protected $returnType       = 'object';
    protected $useTimestamps    = false;

    // ----------------------------------------------------------------
    //  LISTADOS
    // ----------------------------------------------------------------

    /**
     * Productos activos para DataTables.
     */
    public function getActivos(): array
    {
        return $this->where('estado_producto', 1)
                    ->orderBy('id_producto', 'DESC')
                    ->findAll();
    }

    /**
     * Total de productos activos.
     */
    public function countActivos(): int
    {
        return $this->where('estado_producto', 1)->countAllResults();
    }

    /**
     * Todos los productos (sin filtro).
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    // ----------------------------------------------------------------
    //  CRUD
    // ----------------------------------------------------------------

    /**
     * Guardar nuevo producto.
     */
    public function guardar(array $data): int
    {
        $this->insert([
            'nombre'          => $data['nombre'],
            'precio'          => $data['precio'],
            'codigo_barras'   => $data['codigo_barras'],
            'estado_producto' => 1,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Actualizar producto.
     */
    public function actualizar(int $idProducto, array $data): bool
    {
        return $this->update($idProducto, $data);
    }

    /**
     * Eliminación lógica (soft delete manual).
     */
    public function eliminar(int $idProducto): bool
    {
        return $this->update($idProducto, ['estado_producto' => 0]);
    }

    // ----------------------------------------------------------------
    //  VALIDACIONES / BÚSQUEDAS
    // ----------------------------------------------------------------

    /**
     * Verifica si ya existe un producto activo con el mismo nombre O código de barras.
     * Útil para validar antes de insertar.
     */
    public function existeDuplicado(string $campo, string $valor, ?int $exceptoId = null): bool
    {
        $builder = $this->where($campo, $valor)
                        ->where('estado_producto', 1);

        if ($exceptoId !== null) {
            $builder->where('id_producto !=', $exceptoId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Recupera datos básicos de un producto (id, nombre, precio).
     */
    public function getDatosBasicos(int $idProducto): array
    {
        $producto = $this->select('id_producto, nombre, precio')
                         ->find($idProducto);

        if (!$producto) {
            return [
                'id_producto' => $idProducto,
                'nombre'      => '',
                'precio'      => '',
            ];
        }

        return [
            'id_producto' => $producto->id_producto,
            'nombre'      => $producto->nombre,
            'precio'      => $producto->precio,
        ];
    }
}