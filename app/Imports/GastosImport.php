<?php

// app/Imports/GastosImport.php
namespace App\Imports;

use App\Models\GastoMensual;
use App\Models\Categoria;
use App\Models\TeamMemberProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GastosImport implements ToCollection, WithHeadingRow
{
    protected $team;
    protected int $importadas = 0;
    protected array $saltadas = [];
    protected array $errores = [];

    /**
     * @param  \App\Models\Team  $team
     */
    public function __construct($team)
    {
        $this->team = $team;
    }

    public function collection(Collection $rows)
    {
        // Esperamos columnas: unidad, categoria, mes, anio, monto, estado_pago (pagado|pendiente), codigopago, dia_pago (YYYY-MM-DD), link_vaucher
        foreach ($rows as $index => $row) {

            try {
                $unidad       = trim((string)($row['unidad'] ?? ''));
                $categoriaNom = trim((string)($row['categoria'] ?? ''));
                $mes          = trim((string)($row['mes'] ?? ''));
                $anio         = (int)($row['anio'] ?? 0);
                $monto        = (float)($row['monto'] ?? 0);
                $estadoPago   = strtolower(trim((string)($row['estado_pago'] ?? 'pendiente')));
                $codigopago   = trim((string)($row['codigopago'] ?? ''));
                $diaPago      = trim((string)($row['dia_pago'] ?? ''));
                $linkVaucher  = trim((string)($row['link_vaucher'] ?? ''));

                // Validaciones mínimas
                if ($unidad === '' || $categoriaNom === '' || $mes === '' || $anio <= 0 || $monto < 0) {
                    $this->saltadas[] = [
                        'fila' => $index + 2, // +2 por el heading row
                        'motivo' => 'Campos obligatorios faltantes o inválidos (unidad, categoria, mes, anio, monto).',
                        'data' => $row->toArray(),
                    ];
                    continue;
                }

                // Buscar perfil por unidad y team
                $perfil = TeamMemberProfile::where('team_id', $this->team->id)
                    ->where('unidad', $unidad)
                    ->first();

                if (! $perfil) {
                    $this->saltadas[] = [
                        'fila' => $index + 2,
                        'motivo' => "Unidad '{$unidad}' no encontrada en este team.",
                        'data' => $row->toArray(),
                    ];
                    continue;
                }

                // Obtener/crear categoría (sugerencia: en tu app real, que sea por team)
                // Si tu tabla 'categorias' tiene team_id, cámbialo así:
                // $categoria = Categoria::firstOrCreate(['team_id' => $this->team->id, 'nombre' => $categoriaNom]);
                $categoria = Categoria::firstOrCreate(['nombre' => $categoriaNom, 'team_id' => $this->team->id]);

                // Parse pago verificado
                $pagoVerificado = in_array($estadoPago, ['pagado', '1', 'si', 'sí', 'true', 'pagado(s)']);

                // Crear el gasto
                GastoMensual::create([
                    'user_id'  => $perfil->user_id,            // dueño del perfil
                    'team_id'  => $this->team->id,
                    'team_member_profile_id' => $perfil->id,   // <-- vínculo clave
                    'categoria_id' => $categoria->id,
                    'mes' => $mes,
                    'año' => $anio,
                    'codigopago' => $codigopago ?: null,
                    'dia_pago' => $diaPago ?: null,
                    'link_vaucher' => $linkVaucher ?: null,
                    'monto_pagar' => $monto,
                    'pago_verificado' => $pagoVerificado,
                ]);

                $this->importadas++;

            } catch (\Throwable $e) {
                $this->errores[] = [
                    'fila' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];
            }
        }
    }

    public function getImportadas(): int
    {
        return $this->importadas;
    }

    public function getSaltadas(): array
    {
        return $this->saltadas;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }
}
