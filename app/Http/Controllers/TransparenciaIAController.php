<?php
// app/Http/Controllers/TransparenciaIAController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AiSqlAnswerService;

class TransparenciaIAController extends Controller
{
    public function index(Request $request)
    {
        return view('transparencia_ia.index', [
            'q'    => $request->q,
            'rows' => [],
            'sql'  => null,
            'answer' => null,
        ]);
    }

    public function ask(Request $request, \App\Services\AiSqlAnswerService $svc)
{
    $request->validate([
        'q' => 'required|string|max:1000',
    ]);

    // Llamamos al servicio usando el método 'answer'
    $result = $svc->answer([
        'question' => $request->q,
    ]);

    return view('transparencia_ia.index', [
        'q'        => $request->q,
        'rows'     => $result['rows'],
        'sql'      => $result['sql'],
        'answer'   => $result['answer'],
        'raw_json' => $result['raw_json'] ?? null,
    ]);
}

}
