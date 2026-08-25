<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta_cliente (backup.sql).
 *
 * Columnas reales: id_venta (PK, FK -> tbl_venta), id_cliente (default 1, FK -> tbl_cliente).
 *
 * Relación 1 a 1 entre venta y cliente. Si no se especifica cliente,
 * la estructura ya contempla el valor por defecto 1 (Publico General).
 */
class VentaClienteModel extends Model
{
    protected $table         = 'tbl_venta_cliente';
    protected $primaryKey    = 'id_venta';

    protected $allowedFields = [
        'id_venta',
        'id_cliente',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    /**
     * Asocia (crea) el cliente de una venta.
     */
    public function asociar(int $idVenta, int $idCliente = ClienteModel::CLIENTE_GENERICO): bool
    {
        return $this->insert([
            'id_venta'   => $idVenta,
            'id_cliente' => $idCliente ?: ClienteModel::CLIENTE_GENERICO,
        ]) !== false;
    }

    /**
     * Actualiza el cliente asociado a una venta (usado al editar).
     */
    public function actualizar(int $idVenta, int $idCliente): bool
    {
        $existe = $this->find($idVenta);

        if (!$existe) {
            return $this->asociar($idVenta, $idCliente);
        }

        return $this->update($idVenta, ['id_cliente' => $idCliente]);
    }

    /**
     * Obtiene el id_cliente asociado a una venta.
     */
    public function getIdClientePorVenta(int $idVenta): int
    {
        $fila = $this->find($idVenta);
        return $fila ? (int) $fila->id_cliente : ClienteModel::CLIENTE_GENERICO;
    }

    /**
     * Cliente + venta con JOIN (datos completos del cliente de una venta).
     */
    public function getClienteDeVenta(int $idVenta): ?object
    {
        return $this->select('tbl_venta_cliente.id_venta, c.*')
                    ->join('tbl_cliente AS c', 'c.id_cliente = tbl_venta_cliente.id_cliente')
                    ->where('tbl_venta_cliente.id_venta', $idVenta)
                    ->first();
    }

    /**
     * Mapa [id_venta => objeto cliente] para varias ventas (listado eficiente).
     *
     * @param int[] $idsVenta
     * @return array<int, object>
     */
    public function getClientesPorVentas(array $idsVenta): array
    {
        if (empty($idsVenta)) {
            return [];
        }

        $filas = $this->select('tbl_venta_cliente.id_venta, c.id_cliente, c.nombres_apellidos, c.tipo_documento, c.numero_documento, c.telefono, c.direccion')
                      ->join('tbl_cliente AS c', 'c.id_cliente = tbl_venta_cliente.id_cliente')
                      ->whereIn('tbl_venta_cliente.id_venta', $idsVenta)
                      ->findAll();

        $mapa = [];
        foreach ($filas as $fila) {
            $mapa[(int) $fila->id_venta] = $fila;
        }

        return $mapa;
    }
}
