<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_stock (backup.sql).
 *
 * Columnas reales: id_producto (PK, FK -> tbl_producto), stock_actual, stock_minimo.
 *
 * Esta tabla es la fuente de verdad del stock disponible por producto.
 * Los movimientos (ingresos/salidas/ajustes) se registran en
 * tbl_stock_movimiento (ver StockMovimientoModel) y deben mantener
 * sincronizado stock_actual.
 */
class StockModel extends Model
{
    protected $table         = 'tbl_stock';
    protected $primaryKey    = 'id_producto';

    protected $allowedFields = [
        'id_producto',
        'stock_actual',
        'stock_minimo',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    // No hay auto-increment (PK = id_producto), CI4 lo maneja bien con insert().

    // ----------------------------------------------------------------
    //  CONSULTAS
    // ----------------------------------------------------------------

    /**
     * Obtiene el stock actual de un producto. Si no existe registro,
     * se asume 0 (producto sin stock inicializado).
     */
    public function getStockActual(int $idProducto): int
    {
        $stock = $this->find($idProducto);
        return $stock ? (int) $stock->stock_actual : 0;
    }

    /**
     * Obtiene el registro completo de stock de un producto.
     */
    public function getPorProducto(int $idProducto): ?object
    {
        return $this->find($idProducto);
    }

    /**
     * Stock de todos los productos activos, indexado por id_producto.
     * Útil para mostrar el stock disponible en el formulario de ventas.
     *
     * @return array<int, object>
     */
    public function getMapaStock(): array
    {
        $filas = $this->findAll();
        $mapa  = [];

        foreach ($filas as $fila) {
            $mapa[(int) $fila->id_producto] = $fila;
        }

        return $mapa;
    }

    /**
     * Verifica si hay stock suficiente para la cantidad solicitada.
     */
    public function haySuficiente(int $idProducto, int $cantidad): bool
    {
        return $this->getStockActual($idProducto) >= $cantidad;
    }

    /**
     * Productos con stock por debajo (o igual) del mínimo.
     */
    public function getStockBajo(): array
    {
        return $this->select('tbl_stock.*, p.nombre, p.codigo_barras')
                    ->join('tbl_producto AS p', 'p.id_producto = tbl_stock.id_producto')
                    ->where('tbl_stock.stock_actual <= tbl_stock.stock_minimo')
                    ->where('p.estado_producto', 1)
                    ->findAll();
    }

    // ----------------------------------------------------------------
    //  OPERACIONES
    // ----------------------------------------------------------------

    /**
     * Asegura que exista un registro de stock para el producto
     * (defensivo, por si un producto nuevo no tiene fila en tbl_stock).
     */
    public function asegurarRegistro(int $idProducto, int $stockMinimo = 0): void
    {
        $existe = $this->find($idProducto);

        if (!$existe) {
            $this->insert([
                'id_producto'  => $idProducto,
                'stock_actual' => 0,
                'stock_minimo' => $stockMinimo,
            ]);
        }
    }

    /**
     * Incrementa el stock actual de un producto (INGRESO/AJUSTE positivo).
     */
    public function incrementar(int $idProducto, int $cantidad): bool
    {
        $this->asegurarRegistro($idProducto);

        return $this->set('stock_actual', "stock_actual + {$cantidad}", false)
                    ->where('id_producto', $idProducto)
                    ->update();
    }

    /**
     * Descuenta el stock actual de un producto (SALIDA por venta).
     * NO valida disponibilidad — esa validación se realiza antes,
     * en el Controller, para poder informar el error al usuario.
     */
    public function descontar(int $idProducto, int $cantidad): bool
    {
        $this->asegurarRegistro($idProducto);

        return $this->set('stock_actual', "stock_actual - {$cantidad}", false)
                    ->where('id_producto', $idProducto)
                    ->update();
    }

    /**
     * Revierte el stock (usado al anular una venta): repone la cantidad
     * que había sido descontada.
     */
    public function reponer(int $idProducto, int $cantidad): bool
    {
        return $this->incrementar($idProducto, $cantidad);
    }
}
