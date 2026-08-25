<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_comprobante (backup.sql).
 *
 * Columnas reales:
 *   id_comprobante, id_venta, tipo_comprobante ENUM('BOLETA','FACTURA','TICKET'),
 *   serie (default 'B001'), numero, fecha_emision.
 *   UNIQUE(serie, numero).
 *
 * Es el registro que materializa el número comercial de una venta
 * (ej. "B001-000001"), usando la serie/número obtenidos de
 * CorrelativoModel.
 */
class ComprobanteModel extends Model
{
    protected $table         = 'tbl_comprobante';
    protected $primaryKey    = 'id_comprobante';

    protected $allowedFields = [
        'id_venta',
        'tipo_comprobante',
        'serie',
        'numero',
        'fecha_emision',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    const TIPO_BOLETA  = 'BOLETA';
    const TIPO_FACTURA  = 'FACTURA';
    const TIPO_TICKET  = 'TICKET';

    /**
     * Emite (crea) el comprobante para una venta, usando el correlativo
     * de la serie indicada. Debe llamarse dentro de una transacción,
     * junto con CorrelativoModel::siguienteNumero().
     */
    public function emitir(int $idVenta, int $numero, string $serie = CorrelativoModel::SERIE_DEFAULT, string $tipo = self::TIPO_BOLETA): int
    {
        $this->insert([
            'id_venta'         => $idVenta,
            'tipo_comprobante' => $tipo,
            'serie'            => $serie,
            'numero'           => $numero,
            'fecha_emision'    => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Obtiene el comprobante de una venta (una venta tiene, como máximo,
     * un comprobante activo).
     */
    public function getPorVenta(int $idVenta): ?object
    {
        return $this->where('id_venta', $idVenta)
                    ->orderBy('id_comprobante', 'DESC')
                    ->first();
    }

    /**
     * Devuelve el número comercial formateado ("B001-000001") de una venta,
     * o null si la venta no tiene comprobante emitido.
     */
    public function getNumeroComercial(int $idVenta): ?string
    {
        $comprobante = $this->getPorVenta($idVenta);

        if (!$comprobante) {
            return null;
        }

        return CorrelativoModel::formatear($comprobante->serie, (int) $comprobante->numero);
    }

    /**
     * Mapa [id_venta => numero_comercial] para varias ventas a la vez
     * (usado en el listado para evitar N consultas).
     *
     * @param int[] $idsVenta
     * @return array<int, string>
     */
    public function getNumerosComercialesPorVentas(array $idsVenta): array
    {
        if (empty($idsVenta)) {
            return [];
        }

        $filas = $this->whereIn('id_venta', $idsVenta)->findAll();
        $mapa  = [];

        foreach ($filas as $fila) {
            $mapa[(int) $fila->id_venta] = CorrelativoModel::formatear($fila->serie, (int) $fila->numero);
        }

        return $mapa;
    }
}
