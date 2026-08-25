<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta_pago (backup.sql).
 *
 * Columnas reales: id_venta_pago, id_venta, id_metodo_pago, monto.
 *
 * Una venta puede tener uno o más pagos (permite pagos mixtos), aunque
 * el flujo estándar del panel de ventas registra un único método de
 * pago por el monto total de la venta.
 */
class VentaPagoModel extends Model
{
    protected $table         = 'tbl_venta_pago';
    protected $primaryKey    = 'id_venta_pago';

    protected $allowedFields = [
        'id_venta',
        'id_metodo_pago',
        'monto',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    /**
     * Registra un pago para una venta.
     */
    public function guardar(int $idVenta, int $idMetodoPago, float $monto): int
    {
        $this->insert([
            'id_venta'       => $idVenta,
            'id_metodo_pago' => $idMetodoPago,
            'monto'          => $monto,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Reemplaza los pagos de una venta (usado al editar): elimina los
     * existentes y registra el nuevo.
     */
    public function reemplazar(int $idVenta, int $idMetodoPago, float $monto): int
    {
        $this->where('id_venta', $idVenta)->delete();
        return $this->guardar($idVenta, $idMetodoPago, $monto);
    }

    /**
     * Pagos de una venta (con nombre del método).
     */
    public function getPorVenta(int $idVenta): array
    {
        return $this->select('tbl_venta_pago.*, mp.nombre AS metodo_pago_nombre')
                    ->join('tbl_metodo_pago AS mp', 'mp.id_metodo_pago = tbl_venta_pago.id_metodo_pago')
                    ->where('tbl_venta_pago.id_venta', $idVenta)
                    ->findAll();
    }

    /**
     * Primer (o único) método de pago de una venta — para listados donde
     * se asume un solo método por venta.
     */
    public function getMetodoPrincipal(int $idVenta): ?object
    {
        return $this->select('tbl_venta_pago.*, mp.nombre AS metodo_pago_nombre')
                    ->join('tbl_metodo_pago AS mp', 'mp.id_metodo_pago = tbl_venta_pago.id_metodo_pago')
                    ->where('tbl_venta_pago.id_venta', $idVenta)
                    ->orderBy('tbl_venta_pago.id_venta_pago', 'ASC')
                    ->first();
    }

    /**
     * Mapa [id_venta => nombre_metodo_pago] para varias ventas
     * (usa el primer pago registrado por venta).
     *
     * @param int[] $idsVenta
     * @return array<int, string>
     */
    public function getMetodosPorVentas(array $idsVenta): array
    {
        if (empty($idsVenta)) {
            return [];
        }

        // NO usar GROUP BY aquí. En MySQL/Aiven con ONLY_FULL_GROUP_BY
        // seleccionar mp.nombre agrupando solo por id_venta provoca error SQL 1055.
        // Ordenamos por venta y por PK del pago, y conservamos el primer pago
        // de cada venta (que es la misma regla usada por getMetodoPrincipal()).
        $filas = $this->select('tbl_venta_pago.id_venta, tbl_venta_pago.id_venta_pago, mp.nombre AS metodo_pago_nombre')
                      ->join('tbl_metodo_pago AS mp', 'mp.id_metodo_pago = tbl_venta_pago.id_metodo_pago')
                      ->whereIn('tbl_venta_pago.id_venta', $idsVenta)
                      ->orderBy('tbl_venta_pago.id_venta', 'ASC')
                      ->orderBy('tbl_venta_pago.id_venta_pago', 'ASC')
                      ->findAll();

        $mapa = [];
        foreach ($filas as $fila) {
            $idVenta = (int) $fila->id_venta;
            if (!array_key_exists($idVenta, $mapa)) {
                $mapa[$idVenta] = $fila->metodo_pago_nombre;
            }
        }

        return $mapa;
    }
}
