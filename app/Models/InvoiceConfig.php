<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceConfig extends Model
{
    protected $fillable = [
        'team_id', 'ruc', 'razon_social', 'nombre_comercial',
        'ubigeo', 'departamento', 'provincia', 'distrito',
        'urbanizacion', 'direccion', 'cod_pais',
        'sol_user', 'sol_password', 'certificate_pem',
        'ambiente', 'serie_factura', 'serie_boleta',
        'next_factura', 'next_boleta', 'test_mode',
    ];

    protected $casts = ['test_mode' => 'boolean'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function nextCorrelativo(string $tipoDoc): int
    {
        if ($tipoDoc === '01') {
            $n = $this->next_factura;
            $this->increment('next_factura');
            return $n;
        }
        $n = $this->next_boleta;
        $this->increment('next_boleta');
        return $n;
    }

    public function getSerie(string $tipoDoc): string
    {
        return $tipoDoc === '01' ? $this->serie_factura : $this->serie_boleta;
    }

    public function getEndpointUrl(): string
    {
        return $this->ambiente === 'produccion'
            ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
            : 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
    }
}
