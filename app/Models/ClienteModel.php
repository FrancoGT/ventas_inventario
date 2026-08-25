<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_cliente (backup.sql)
 *
 * Columnas reales:
 *   id_cliente, nombres_apellidos, tipo_documento ENUM('DNI','RUC','SIN_DOCUMENTO'),
 *   numero_documento, telefono, direccion, status
 *
 * El cliente id=1 ("Publico General") es el cliente genérico usado por
 * defecto en tbl_venta_cliente cuando la venta no especifica uno.
 */
class ClienteModel extends Model
{
    protected $table         = 'tbl_cliente';
    protected $primaryKey    = 'id_cliente';

    protected $allowedFields = [
        'nombres_apellidos',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'direccion',
        'status',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    const ACTIVO   = 1;
    const INACTIVO = 0;

    /** ID del cliente genérico definido en backup.sql ("Publico General"). */
    const CLIENTE_GENERICO = 1;

    // ----------------------------------------------------------------
    //  LISTADOS
    // ----------------------------------------------------------------

    /**
     * Clientes activos para DataTables.
     */
    public function getParaDatatables(): array
    {
        return $this->where('status', self::ACTIVO)
                    ->orderBy('id_cliente', 'DESC')
                    ->findAll();
    }

    /**
     * Todos los clientes activos (para selects/autocomplete).
     */
    public function getActivos(): array
    {
        return $this->where('status', self::ACTIVO)
                    ->orderBy('nombres_apellidos', 'ASC')
                    ->findAll();
    }

    /**
     * Búsqueda de clientes por nombre o número de documento
     * (usado por el autocomplete del formulario de Nueva Venta).
     */
    public function buscar(string $termino, int $limite = 15): array
    {
        return $this->where('status', self::ACTIVO)
                    ->groupStart()
                        ->like('nombres_apellidos', $termino)
                        ->orLike('numero_documento', $termino)
                    ->groupEnd()
                    ->orderBy('nombres_apellidos', 'ASC')
                    ->limit($limite)
                    ->findAll();
    }

    // ----------------------------------------------------------------
    //  CRUD
    // ----------------------------------------------------------------

    public function guardar(array $data): int
    {
        $this->insert([
            'nombres_apellidos' => $data['nombres_apellidos'],
            'tipo_documento'    => $data['tipo_documento']  ?? 'SIN_DOCUMENTO',
            'numero_documento'  => $data['numero_documento'] ?? null,
            'telefono'          => $data['telefono']         ?? null,
            'direccion'         => $data['direccion']        ?? null,
            'status'            => self::ACTIVO,
        ]);

        return (int) $this->getInsertID();
    }

    public function actualizar(int $idCliente, array $data): bool
    {
        return $this->update($idCliente, [
            'nombres_apellidos' => $data['nombres_apellidos'],
            'tipo_documento'    => $data['tipo_documento']  ?? 'SIN_DOCUMENTO',
            'numero_documento'  => $data['numero_documento'] ?? null,
            'telefono'          => $data['telefono']         ?? null,
            'direccion'         => $data['direccion']        ?? null,
        ]);
    }

    /**
     * Eliminación lógica. El cliente genérico (id=1) no puede eliminarse.
     */
    public function eliminar(int $idCliente): bool
    {
        if ($idCliente === self::CLIENTE_GENERICO) {
            return false;
        }

        return $this->update($idCliente, ['status' => self::INACTIVO]);
    }

    // ----------------------------------------------------------------
    //  VALIDACIONES
    // ----------------------------------------------------------------

    public function existeDocumentoDuplicado(string $numeroDocumento, ?int $exceptoId = null): bool
    {
        if (empty($numeroDocumento)) {
            return false;
        }

        $builder = $this->where('numero_documento', $numeroDocumento)
                        ->where('status', self::ACTIVO);

        if ($exceptoId !== null) {
            $builder->where('id_cliente !=', $exceptoId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Garantiza que exista el cliente genérico "Publico General".
     * (Ya viene insertado en backup.sql, este método es solo defensivo).
     */
    public function obtenerOcrearGenerico(): int
    {
        $existe = $this->find(self::CLIENTE_GENERICO);

        if ($existe) {
            return self::CLIENTE_GENERICO;
        }

        $this->insert([
            'id_cliente'        => self::CLIENTE_GENERICO,
            'nombres_apellidos' => 'Publico General',
            'tipo_documento'    => 'SIN_DOCUMENTO',
            'numero_documento'  => null,
            'telefono'          => null,
            'direccion'         => null,
            'status'            => self::ACTIVO,
        ]);

        return self::CLIENTE_GENERICO;
    }

    /**
     * Devuelve datos básicos de un cliente (para mostrar en detalle/comprobante).
     */
    public function getDatosBasicos(int $idCliente): ?object
    {
        return $this->find($idCliente);
    }
}
