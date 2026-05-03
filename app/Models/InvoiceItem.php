<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'cod_producto', 'descripcion', 'unidad',
        'cantidad', 'precio_unitario', 'valor_unitario',
        'tip_afe_igv', 'igv_porcentaje', 'igv', 'total',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'precio_unitario' => 'float',
        'valor_unitario'  => 'float',
        'igv_porcentaje'  => 'float',
        'igv'             => 'float',
        'total'           => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
