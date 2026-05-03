<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceConfig;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPago;
use Greenter\Model\Sale\Invoice as GInvoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Illuminate\Support\Facades\Storage;

class GreenterService
{
    public function buildAndSign(Invoice $invoice, InvoiceConfig $config): array
    {
        $invoice->load('items');

        $see = new See();
        $see->setService($config->getEndpointUrl());
        $see->setCertificate($config->certificate_pem ?? '');

        if ($config->sol_user && $config->sol_password) {
            $see->setCredentials(
                $config->ruc . $config->sol_user,
                $config->sol_password
            );
        }

        $company = (new Company())
            ->setRuc($config->ruc)
            ->setRazonSocial($config->razon_social)
            ->setNombreComercial($config->nombre_comercial ?? $config->razon_social)
            ->setAddress(
                (new Address())
                    ->setUbigueo($config->ubigeo)
                    ->setDepartamento(mb_strtoupper($config->departamento))
                    ->setProvincia(mb_strtoupper($config->provincia))
                    ->setDistrito(mb_strtoupper($config->distrito))
                    ->setUrbanizacion($config->urbanizacion ?? '-')
                    ->setDireccion(mb_strtoupper($config->direccion))
                    ->setCodPais($config->cod_pais)
            );

        $client = (new Client())
            ->setTipoDoc($invoice->cliente_tipo_doc)
            ->setNumDoc($invoice->cliente_num_doc)
            ->setRznSocial(mb_strtoupper($invoice->cliente_razon_social));

        if ($invoice->cliente_direccion) {
            $client->setAddress(mb_strtoupper($invoice->cliente_direccion));
        }

        $details = $invoice->items->map(function ($item) {
            $qty        = (float) $item->cantidad;
            $valorUnit  = (float) $item->valor_unitario;
            $precioUnit = (float) $item->precio_unitario;
            $igvItem    = round($valorUnit * $qty * ($item->igv_porcentaje / 100), 2);
            $valorVenta = round($valorUnit * $qty, 2);

            return (new SaleDetail())
                ->setCodProducto($item->cod_producto ?: 'ZZZ9999999AA')
                ->setUnidad($item->unidad ?: 'NIU')
                ->setDescripcion(mb_strtoupper($item->descripcion))
                ->setCantidad($qty)
                ->setMtoValorUnitario($valorUnit)
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv((float) $item->igv_porcentaje)
                ->setIgv($igvItem)
                ->setTipAfeIgv($item->tip_afe_igv ?: '10')
                ->setMtoPrecioUnitario($precioUnit)
                ->setMtoValorVenta($valorVenta)
                ->setMtoTotalImpuestos($igvItem);
        })->toArray();

        $total = (float) $invoice->total;

        $formaPago = (new FormaPago())
            ->setMoneda($invoice->moneda)
            ->setTotal($total);

        $gInvoice = (new GInvoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($invoice->tipo_doc)
            ->setSerie($invoice->serie)
            ->setCorrelativo((string) $invoice->correlativo)
            ->setFechaEmision($invoice->fecha_emision->toDateTime())
            ->setFormaPago($formaPago)
            ->setMoneda($invoice->moneda)
            ->setCompany($company)
            ->setClient($client)
            ->setDetails($details)
            ->setMtoOperGravadas((float) $invoice->op_gravadas)
            ->setMtoIGV((float) $invoice->igv)
            ->setValueSumImpuestos((float) $invoice->igv)
            ->setSubTotal($total)
            ->setMtoImpVenta($total)
            ->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue(mb_strtoupper($this->numberToWords($total, $invoice->moneda))),
            ]);

        // Sign
        $xml = $see->sign($gInvoice);

        // Store XML
        $dir  = "invoices/{$invoice->team_id}";
        $name = "{$invoice->serie}-{$invoice->correlativo}.xml";
        Storage::put("{$dir}/{$name}", $xml);
        $invoice->update([
            'xml_path' => "{$dir}/{$name}",
            'estado'   => 'signed',
        ]);

        return ['see' => $see, 'gInvoice' => $gInvoice, 'xml' => $xml];
    }

    public function sendToSunat(Invoice $invoice, InvoiceConfig $config): array
    {
        ['see' => $see, 'gInvoice' => $gInvoice] = $this->buildAndSign($invoice, $config);

        if (!$config->sol_user || !$config->sol_password || !$config->certificate_pem) {
            return ['success' => false, 'message' => 'Configura RUC, usuario SOL y certificado antes de enviar.'];
        }

        $result = $see->send($gInvoice);

        if (!$result->isSuccess()) {
            $invoice->update(['estado' => 'sent', 'sunat_description' => $result->getError()]);
            return ['success' => false, 'message' => $result->getError()];
        }

        $cdr = $see->getFactory()->getCdrResponse($result->getCdrZip());

        $dir  = "invoices/{$invoice->team_id}";
        $cdrName = "{$invoice->serie}-{$invoice->correlativo}-CDR.xml";
        Storage::put("{$dir}/{$cdrName}", $result->getCdrZip());

        $accepted = (int) $cdr->getCode() === 0;
        $invoice->update([
            'estado'            => $accepted ? 'accepted' : 'rejected',
            'sunat_code'        => $cdr->getCode(),
            'sunat_description' => $cdr->getDescription(),
            'sunat_notes'       => implode(' | ', $cdr->getNotes() ?? []),
            'cdr_path'          => "{$dir}/{$cdrName}",
        ]);

        return [
            'success'     => $accepted,
            'code'        => $cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes'       => $cdr->getNotes(),
        ];
    }

    private function numberToWords(float $amount, string $currency): string
    {
        $moneda = $currency === 'USD' ? 'DÓLARES AMERICANOS' : 'SOLES';
        $int    = (int) floor($amount);
        $cents  = (int) round(($amount - $int) * 100);

        return "SON {$int} CON {$cents}/100 {$moneda}";
    }
}
