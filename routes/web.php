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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DealProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceConfigController;
use App\Http\Controllers\PaymentController;


Route::get('/', function () {
    return view('welcome');
});

Route::post('/locale', function (Request $request) {
    $data = $request->validate([
        'locale' => 'required|string|in:es,en,pt',
    ]);

    // Por sesión (cada usuario / navegador independiente) — evita que el cambio
    // afecte a todos los demás usuarios (lo que pasaba con Cache::forever).
    session(['locale' => $data['locale']]);
    app()->setLocale($data['locale']);

    return back();
})->name('locale.update');

// Webhook público de Culqi (sin auth, sin CSRF — excluido en bootstrap/app.php)
Route::post('/webhooks/culqi', [PaymentController::class, 'webhook'])->name('webhooks.culqi');

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

    // Productos en negociaciones
    Route::post('/pipelines/{pipeline}/deals/{deal}/products', [DealProductController::class, 'store'])->name('deals.products.store');
    Route::delete('/pipelines/{pipeline}/deals/{deal}/products/{dealProduct}', [DealProductController::class, 'destroy'])->name('deals.products.destroy');
    Route::get('/products/search', [DealProductController::class, 'productSearch'])->name('products.search');

    // Catálogo de productos
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    // Import CSV de productos
    Route::get('/products/import',          [ProductController::class, 'importForm'])->name('products.import.form');
    Route::get('/products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
    Route::post('/products/import',         [ProductController::class, 'importStore'])->name('products.import.store');

    // Import CSV de contactos
    Route::get('/contacts/import',          [ContactController::class, 'importForm'])->name('contacts.import.form');
    Route::get('/contacts/import/template', [ContactController::class, 'importTemplate'])->name('contacts.import.template');
    Route::post('/contacts/import',         [ContactController::class, 'importStore'])->name('contacts.import.store');

    // Facturación electrónica
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/download-xml', [InvoiceController::class, 'downloadXml'])->name('invoices.download-xml');
    Route::post('/invoices/{invoice}/sign', [InvoiceController::class, 'signOnly'])->name('invoices.sign');
    Route::post('/invoices/{invoice}/send-sunat', [InvoiceController::class, 'sendToSunat'])->name('invoices.send-sunat');
    Route::get('/pipelines/{pipeline}/deals/{deal}/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/pipelines/{pipeline}/deals/{deal}/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    // Configuración SUNAT por equipo
    Route::get('/invoice-config', [InvoiceConfigController::class, 'edit'])->name('invoice-config.edit');
    Route::put('/invoice-config', [InvoiceConfigController::class, 'update'])->name('invoice-config.update');

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

    // Pagos con Culqi
    Route::get('/teams/{team}/pagar',  [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/teams/{team}/pagar', [PaymentController::class, 'process'])->name('payments.process');


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

    // AI Assistant per account
    Route::get('/whatsapp/accounts/{account}/ai', [\App\Http\Controllers\WhatsappAiAssistantController::class, 'edit'])->name('whatsapp.ai.edit');
    Route::put('/whatsapp/accounts/{account}/ai', [\App\Http\Controllers\WhatsappAiAssistantController::class, 'update'])->name('whatsapp.ai.update');
    Route::delete('/whatsapp/accounts/{account}/ai', [\App\Http\Controllers\WhatsappAiAssistantController::class, 'destroy'])->name('whatsapp.ai.destroy');

    

    // Inbox
    Route::get('/whatsapp/inbox', [WhatsappInboxController::class, 'index'])->name('whatsapp.inbox.index');
    Route::get('/whatsapp/inbox/{conversation}', [WhatsappInboxController::class, 'show'])->name('whatsapp.inbox.show');
    Route::post('/whatsapp/inbox/{conversation}/send', [WhatsappInboxController::class, 'send'])->name('whatsapp.inbox.send');
    Route::get('/whatsapp/inbox/{conversation}/messages', [WhatsappInboxController::class, 'newMessages'])->name('whatsapp.inbox.messages');
    Route::get('/whatsapp/inbox/{conversation}/panel', [WhatsappInboxController::class, 'panel'])->name('whatsapp.inbox.panel');
    Route::post('/whatsapp/inbox/{conversation}/ai-toggle', [WhatsappInboxController::class, 'toggleAi'])->name('whatsapp.inbox.ai.toggle');
    Route::get('/whatsapp/sidebar-poll', [WhatsappInboxController::class, 'sidebarPoll'])->name('whatsapp.sidebar.poll');
    Route::post('/whatsapp/inbox/{conversation}/deal', [WhatsappInboxController::class, 'createDeal'])->name('whatsapp.inbox.deal.create');


    Route::resource('contacts', ContactController::class)->only(['index','create','store','edit','update','destroy']);

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


