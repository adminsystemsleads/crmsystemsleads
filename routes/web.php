<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteMensualController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TeamMemberProfileController;
use App\Http\Controllers\GastoImportController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\TeamLicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealCommentController;
use App\Http\Controllers\DealActivityController;
use App\Http\Controllers\WhatsappAccountController;
use App\Http\Controllers\WhatsappInboxController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\TeamModulesController;


Route::get('/', function () {
    return view('welcome');
});

Route::post('/locale', function (Request $request) {
    $data = $request->validate([
        'locale' => 'required|string|in:en,es,pt,fr,de',
    ]);
    Cache::forever('app:locale', $data['locale']);
    app()->setLocale($data['locale']);

    return back()->with('success', 'Idioma actualizado.');
})->name('locale.update');
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'team.license',
])->group(function () {

Route::get('/whatsapp/messages/{message}', [\App\Http\Controllers\WhatsappInboxController::class, 'messageJson'])
    ->name('whatsapp.messages.json');

Route::post('/pipelines/{pipeline}/deals/{deal}/comments', [DealCommentController::class, 'store'])
    ->name('deals.comments.store');

Route::post('/pipelines/{pipeline}/deals/{deal}/activities', [DealActivityController::class, 'store'])
    ->name('deals.activities.store');
    Route::get('/pipelines/{pipeline}/deals/create', [DealController::class, 'create'])->name('deals.create');
    Route::post('/pipelines/{pipeline}/deals',        [DealController::class, 'store'])->name('deals.store');
    Route::get('/pipelines/{pipeline}/deals/{deal}/edit', [DealController::class, 'edit'])->name('deals.edit');
    Route::put('/pipelines/{pipeline}/deals/{deal}',       [DealController::class, 'update'])->name('deals.update');
    Route::delete('/pipelines/{pipeline}/deals/{deal}',    [DealController::class, 'destroy'])->name('deals.destroy');

    // 🔹 mover negociación entre fases desde el Kanban
    Route::post('/pipelines/{pipeline}/deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');

     Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');
    Route::get('/pipelines/create', [PipelineController::class, 'create'])->name('pipelines.create');
    Route::post('/pipelines', [PipelineController::class, 'store'])->name('pipelines.store');
    Route::get('/pipelines/{pipeline}/edit', [PipelineController::class, 'edit'])->name('pipelines.edit');
    Route::put('/pipelines/{pipeline}', [PipelineController::class, 'update'])->name('pipelines.update');
    Route::delete('/pipelines/{pipeline}', [PipelineController::class, 'destroy'])->name('pipelines.destroy');

    // Fases (stages) del pipeline
    Route::post('/pipelines/{pipeline}/stages', [PipelineController::class, 'storeStage'])->name('pipelines.stages.store');
    Route::put('/pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'updateStage'])->name('pipelines.stages.update');
    Route::delete('/pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'destroyStage'])->name('pipelines.stages.destroy');

    // Kanban de negociaciones por pipeline
    Route::get('/pipelines/{pipeline}/kanban', [PipelineController::class, 'kanban'])->name('pipelines.kanban');
    
    // Módulos activos por equipo
    Route::get('/teams/{team}/modules', [TeamModulesController::class, 'edit'])->name('team.modules.edit');
    Route::put('/teams/{team}/modules', [TeamModulesController::class, 'update'])->name('team.modules.update');

    // Formulario / estado de licencia
    Route::get('/teams/{team}/licencia', [TeamLicenseController::class, 'show'])
        ->name('team.license.form');

    // Activar / renovar
    Route::post('/teams/{team}/licencia/activar', [TeamLicenseController::class, 'activate'])
        ->name('team.license.activate');


         // Rutas protegidas por licencia
    Route::middleware(['team.license'])->group(function () {
        Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/mis-finanzas', [FinanzasController::class, 'index'])
     ->name('finanzas.index');
    Route::get('/misfinanzas', function () {
    return view('misfinanzas.principal');
    })->name('misfinanzas');

    Route::get('/pagos', function () {
        return view('pagos.principal');
    })->name('pagos');

    Route::get('/transparencia', function () {
        return view('transparencia.principal');
    })->name('transparencia');

    Route::get('/importemensual', function () {
        return view('importacion.principal');
    })->name('importarReporte');


    Route::get('/whatsapp/accounts', [WhatsappAccountController::class, 'index'])->name('whatsapp.accounts.index');
    Route::get('/whatsapp/accounts/create', [WhatsappAccountController::class, 'create'])->name('whatsapp.accounts.create');
    Route::post('/whatsapp/accounts', [WhatsappAccountController::class, 'store'])->name('whatsapp.accounts.store');
    Route::get('/whatsapp/accounts/{account}/edit', [WhatsappAccountController::class, 'edit'])->name('whatsapp.accounts.edit');
    Route::put('/whatsapp/accounts/{account}', [WhatsappAccountController::class, 'update'])->name('whatsapp.accounts.update');
    Route::delete('/whatsapp/accounts/{account}', [WhatsappAccountController::class, 'destroy'])->name('whatsapp.accounts.destroy');

    

    // Inbox
    Route::get('/whatsapp/inbox', [WhatsappInboxController::class, 'index'])->name('whatsapp.inbox.index');
    Route::get('/whatsapp/inbox/{conversation}', [WhatsappInboxController::class, 'show'])->name('whatsapp.inbox.show');
    Route::post('/whatsapp/inbox/{conversation}/send', [WhatsappInboxController::class, 'send'])->name('whatsapp.inbox.send');


    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    Route::get('/pipelines/{pipeline}/permissions', [\App\Http\Controllers\PipelinePermissionController::class, 'edit'])
        ->name('pipelines.permissions.edit');

    Route::put('/pipelines/{pipeline}/permissions', [\App\Http\Controllers\PipelinePermissionController::class, 'update'])
        ->name('pipelines.permissions.update');

     // Mi perfil en el team actual (crear/editar en una sola pantalla)
    Route::get('/mi-perfil-unidad', [TeamMemberProfileController::class, 'edit'])->name('perfil-unidad.edit');
    Route::post('/mi-perfil-unidad', [TeamMemberProfileController::class, 'update'])->name('perfil-unidad.update');

    // (Opcional) Listado para admin del team actual
    Route::get('/mi-team/perfiles', [TeamMemberProfileController::class, 'index'])
        ->name('team.perfiles.index');

     Route::get('/gastos/importar', [GastoImportController::class, 'create'])
        ->name('gastos.import.create');

    Route::post('/gastos/importar', [GastoImportController::class, 'store'])
        ->name('gastos.import.store');
      Route::get('/gastos', [GastoController::class, 'index'])->name('gastos.index');
    Route::get('/gastos/{gasto}/edit', [GastoController::class, 'edit'])->name('gastos.edit');
    Route::put('/gastos/{gasto}', [GastoController::class, 'update'])->name('gastos.update');
    Route::delete('/gastos/{gasto}', [GastoController::class, 'destroy'])->name('gastos.destroy');

    Route::get('/transparencia-ia', [\App\Http\Controllers\TransparenciaIAController::class, 'index'])
        ->name('transparencia.ia.index');
    Route::post('/transparencia-ia/ask', [\App\Http\Controllers\TransparenciaIAController::class, 'ask'])
        ->name('transparencia.ia.ask');
    });

    
});


