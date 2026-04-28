<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReporteMensualController extends Controller
{
    public function create()
    {
        return view('pages.importar-reporte-mensual'); // crea esta vista
    }
}
