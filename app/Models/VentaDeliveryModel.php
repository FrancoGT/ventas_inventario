<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta_delivery (backup.sql).
 *
 * Columnas reales: id_venta (PK, FK -> tbl_venta), costo_delivery, direccion_entrega.
 *
 * Esta tabla corrige la lógica anterior de "delivery por producto":
 * ahora el costo de delivery es UN SOLO cargo para la venta completa,
 * no una columna repetida en cada línea de tbl_detalle_venta.
 *
 * Nota: tbl_detalle_venta conserva una columna costo_delivery heredada
 * (siempre en 0.00 en los datos semilla) que ya NO debe usarse; el
 * delivery real de la venta vive únicamente aquí.
 */
class VentaDeliveryModel extends Model
{
    protected $table         = 'tbl_venta_delivery';
    protected $primaryKey    = 'id_venta';

    protected $allowedFields = [
        'id_venta',
        'costo_delivery',
        'direccion_entrega',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    /**
     * Registra el delivery de una venta. Si el costo es 0 y no hay
     * dirección, de todas formas se guarda la fila para mantener la
     * relación 1 a 1 explícita (costo 0 = "sin delivery").
     */
    public function guardar(int $idVenta, float $costoDelivery = 0.00, ?string $direccionEntrega = null): bool
    {
        return $this->insert([
            'id_venta'          => $idVenta,
            'costo_delivery'    => $costoDelivery,
            'direccion_entrega' => $direccionEntrega,
        ]) !== false;
    }

    /**
     * Actualiza el delivery de una venta (usado al editar).
     */
    public function actualizar(int $idVenta, float $costoDelivery, ?string $direccionEntrega = null): bool
    {
        $existe = $this->find($idVenta);

        if (!$existe) {
            return $this->guardar($idVenta, $costoDelivery, $direccionEntrega);
        }

        return $this->update($idVenta, [
            'costo_delivery'    => $costoDelivery,
            'direccion_entrega' => $direccionEntrega,
        ]);
    }

    /**
     * Costo de delivery de una venta (0.00 si no tiene registro).
     */
    public function getCostoDelivery(int $idVenta): float
    {
        $fila = $this->find($idVenta);
        return $fila ? (float) $fila->costo_delivery : 0.00;
    }

    /**
     * Mapa [id_venta => costo_delivery] para varias ventas (listado eficiente).
     *
     * @param int[] $idsVenta
     * @return array<int, float>
     */
    public function getCostosPorVentas(array $idsVenta): array
    {
        if (empty($idsVenta)) {
            return [];
        }

        $filas = $this->whereIn('id_venta', $idsVenta)->findAll();
        $mapa  = [];

        foreach ($filas as $fila) {
            $mapa[(int) $fila->id_venta] = (float) $fila->costo_delivery;
        }

        return $mapa;
    }
}
