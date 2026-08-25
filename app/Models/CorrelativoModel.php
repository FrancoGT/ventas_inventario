<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_correlativo (backup.sql).
 *
 * Columnas reales: serie (PK), ultimo_numero.
 * Semilla: ('B001', 0).
 *
 * Este modelo es el mecanismo REAL de numeración comercial de ventas
 * (correlativo por serie), usado junto con tbl_comprobante para generar
 * números tipo "B001-000001". No se debe usar el id_venta interno como
 * número visible al usuario.
 */
class CorrelativoModel extends Model
{
    protected $table         = 'tbl_correlativo';
    protected $primaryKey    = 'serie';

    protected $allowedFields = [
        'serie',
        'ultimo_numero',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    /** Serie por defecto definida en backup.sql. */
    const SERIE_DEFAULT = 'B001';

    /**
     * Obtiene y AVANZA atómicamente el siguiente número de una serie.
     *
     * IMPORTANTE: debe ejecutarse dentro de una transacción de BD para
     * evitar condiciones de carrera (dos ventas obteniendo el mismo
     * número). El UPDATE ... SET ultimo_numero = ultimo_numero + 1 es
     * seguro bajo bloqueo de fila InnoDB dentro de una transacción.
     */
    public function siguienteNumero(string $serie = self::SERIE_DEFAULT): int
    {
        $db = \Config\Database::connect();

        // Asegurar que la serie exista.
        $existe = $this->find($serie);
        if (!$existe) {
            $this->insert(['serie' => $serie, 'ultimo_numero' => 0]);
        }

        // Incremento atómico protegido por bloqueo de fila (requiere transacción activa).
        $db->query('UPDATE tbl_correlativo SET ultimo_numero = ultimo_numero + 1 WHERE serie = ?', [$serie]);

        $fila = $this->find($serie);

        return (int) $fila->ultimo_numero;
    }

    /**
     * Formatea el número comercial completo: "B001-000001".
     */
    public static function formatear(string $serie, int $numero): string
    {
        return $serie . '-' . str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }
}
