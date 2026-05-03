<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'team_id', 'deal_id', 'contact_id',
        'tipo_doc', 'serie', 'correlativo',
        'fecha_emision', 'fecha_vencimiento', 'moneda',
        'igv_porcentaje',
        'op_gravadas', 'op_exoneradas', 'op_inafectas', 'igv', 'total',
        'cliente_tipo_doc', 'cliente_num_doc', 'cliente_razon_social', 'cliente_direccion',
        'estado', 'hash', 'sunat_code', 'sunat_description', 'sunat_notes',
        'xml_path', 'cdr_path', 'observaciones',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
        'op_gravadas'       => 'float',
        'op_exoneradas'     => 'float',
        'op_inafectas'      => 'float',
        'igv'               => 'float',
        'total'             => 'float',
        'igv_porcentaje'    => 'float',
    ];

    public function team()    { return $this->belongsTo(Team::class); }
    public function deal()    { return $this->belongsTo(Deal::class); }
    public function contact() { return $this->belongsTo(Contact::class); }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('id');
    }

    public function getNumeroAttribute(): string
    {
        return $this->serie . '-' . str_pad($this->correlativo, 8, '0', STR_PAD_LEFT);
    }

    public function getTipoNombreAttribute(): string
    {
        return match ($this->tipo_doc) {
            '01' => 'Factura',
            '03' => 'Boleta de Venta',
            default => 'Comprobante',
        };
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'draft'     => 'Borrador',
            'signed'    => 'Firmado',
            'sent'      => 'Enviado',
            'accepted'  => 'Aceptado',
            'rejected'  => 'Rechazado',
            'cancelled' => 'Anulado',
            default     => $this->estado,
        };
    }
}
