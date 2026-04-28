<?php
/*

public function create()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Solo admin del team puede importar
        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        return view('gastos.importar');
    }

*/
// app/Http/Controllers/GastoImportController.php


namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\GastoMensual;
use App\Models\TeamMemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class GastoImportController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Solo admin del team puede importar
        if (! $user->hasTeamRole($team, 'admin')) {
            abort(403);
        }

        return view('gastos.importar');
    }
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $user = Auth::user();
        $team = $user->currentTeam;

        // Obtener todas las categorías del team (sin importar tipo)
        $categorias = Categoria::where('team_id', $team->id)->get();

        // Crear índice normalizado de categorías
        $catIndex = [];
        foreach ($categorias as $c) {
            $catIndex[$this->slugHeader($c->nombre)] = [
                'id' => $c->id,
                'nombre' => $c->nombre,
            ];
        }

        // Leer archivo Excel
        $collection = Excel::toCollection(null, $request->file('file'))->first();

        if (!$collection || $collection->count() === 0) {
            return back()->with('error', 'El archivo está vacío.');
        }

        // Obtener encabezados
        $headingsRow = (new HeadingRowImport)->toArray($request->file('file'))[0][0] ?? [];
        $headers = array_map(fn($h) => $this->slugHeader($h), $headingsRow);

        // Buscar columnas clave
        $colUnidad = array_search('unidad', $headers, true);
        $colMes = array_search('mes', $headers, true);
        $colAnio = array_search('anio', $headers, true);
        $colDescripcion = array_search('descripcion', $headers, true);

        if ($colMes === false || $colAnio === false) {
            return back()->with('error', 'El archivo debe contener columnas mes y año.');
        }

        // Detectar columnas de categorías
        $categoriaCols = [];
        foreach ($headers as $idx => $slug) {
            if (isset($catIndex[$slug])) {
                $categoriaCols[$idx] = $catIndex[$slug];
            }
        }

        if (empty($categoriaCols)) {
            return back()->with('error', 'No se encontró ninguna columna que coincida con las categorías registradas.');
        }

        $errores = [];
        $creados = 0;

        // Recorrer filas
        foreach ($collection->slice(1) as $rowIndex => $row) {

            $mesRaw = trim((string)($row[$colMes] ?? ''));
            $anio = (int)($row[$colAnio] ?? 0);

            $mesNum = $this->toMonthNumber($mesRaw);

            if ($mesNum < 1 || $mesNum > 12 || $anio < 2000 || $anio > date('Y') + 1) {
                $errores[] = "Fila ".($rowIndex+2).": mes o año inválido ($mesRaw, $anio).";
                continue;
            }

            $unidadVal = $colUnidad !== false ? trim((string)($row[$colUnidad] ?? '')) : '';
            $profileId = null;

            if ($unidadVal !== '') {
                $profileId = TeamMemberProfile::where('team_id', $team->id)
                    ->where('unidad', $unidadVal)
                    ->value('id');

                if (!$profileId) {
                    $errores[] = "Fila ".($rowIndex+2).": la unidad \"$unidadVal\" no existe en el condominio.";
                    continue;
                }
            }

            // Descripción (solo si no hay unidad)
            $descripcion = null;
            if ($profileId === null && $colDescripcion !== false) {
                $descripcion = trim((string)($row[$colDescripcion] ?? ''));
                if ($descripcion === '') $descripcion = null;
            }

            // Crear gastos por categoría
            foreach ($categoriaCols as $colIdx => $catInfo) {
                $val = $row[$colIdx] ?? null;
                $monto = is_numeric($val) ? floatval($val) : null;

                if ($monto !== null && $monto > 0) {
                    GastoMensual::create([
                        'user_id'                => $user->id,
                        'team_id'                => $team->id,
                        'team_member_profile_id' => $profileId, // null si es gasto general
                        'categoria_id'           => $catInfo['id'],
                        'mes'                    => $mesNum,
                        'año'                    => $anio,
                        'monto_pagar'            => $monto,
                        'pago_verificado'        => false,
                        'descripcion'            => $descripcion,
                    ]);
                    $creados++;
                }
            }
        }

        $msg = "Importación completada. Gastos creados: $creados.";
        if ($errores) {
            $msg .= " Algunos errores:\n- ".implode("\n- ", array_slice($errores, 0, 10));
            if (count($errores) > 10) $msg .= "\n(…y ".(count($errores)-10)." más)";
            return back()->with('warning', nl2br(e($msg)));
        }

        return back()->with('success', $msg);
    }

    // ---- Funciones auxiliares ---- //

    private function slugHeader(string $h): string
    {
        $h = trim(mb_strtolower($h, 'UTF-8'));
        $replacements = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'];
        $h = strtr($h, $replacements);
        $h = preg_replace('/[^a-z0-9]+/','_', $h);
        return trim($h, '_');
    }

    private function toMonthNumber(string $mes): int
    {
        $mes = trim(mb_strtolower($mes, 'UTF-8'));
        $map = [
            'enero'=>1,'1'=>1,'01'=>1,
            'febrero'=>2,'2'=>2,'02'=>2,
            'marzo'=>3,'3'=>3,'03'=>3,
            'abril'=>4,'4'=>4,'04'=>4,
            'mayo'=>5,'5'=>5,'05'=>5,
            'junio'=>6,'6'=>6,'06'=>6,
            'julio'=>7,'7'=>7,'07'=>7,
            'agosto'=>8,'8'=>8,'08'=>8,
            'setiembre'=>9,'septiembre'=>9,'9'=>9,'09'=>9,
            'octubre'=>10,'10'=>10,
            'noviembre'=>11,'11'=>11,
            'diciembre'=>12,'12'=>12,
        ];
        return $map[$mes] ?? 0;
    }

    private function mesNombre(int $n): string
    {
        $arr = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
            7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
        ];
        return $arr[$n] ?? (string)$n;
    }
}
