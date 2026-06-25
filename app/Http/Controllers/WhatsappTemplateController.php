<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Services\WhatsappTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappTemplateController extends Controller
{
    public function __construct(private WhatsappTemplateService $service) {}

    private function guard(WhatsappAccount $account): void
    {
        abort_unless($account->team_id === Auth::user()->currentTeam->id, 404);
    }

    /** Pantalla de gestión de plantillas de una cuenta. */
    public function index(WhatsappAccount $account)
    {
        $this->guard($account);

        $result    = $this->service->listAll($account);
        $templates = $result['templates'] ?? [];
        $error     = $result['ok'] ? null : ($result['message'] ?? null);

        return view('whatsapp.templates.index', compact('account', 'templates', 'error'));
    }

    /** Crea una plantilla en Meta. */
    public function store(Request $request, WhatsappAccount $account)
    {
        $this->guard($account);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'category'    => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'    => 'required|string|max:10',
            'header_text' => 'nullable|string|max:60',
            'body'        => 'required|string|max:1024',
            'footer_text' => 'nullable|string|max:60',
            'examples'    => 'nullable|array',
            'examples.*'  => 'nullable|string|max:200',
            'buttons'     => 'nullable|array|max:3',
            'buttons.*'   => 'nullable|string|max:25',
        ], [
            'name.regex' => 'El nombre solo puede tener minúsculas, números y guiones bajos (ej: bienvenida_cliente).',
        ]);

        // Variables {{1}}, {{2}}… del cuerpo
        preg_match_all('/\{\{\s*(\d+)\s*\}\}/', $data['body'], $m);
        $vars = array_values(array_unique(array_map('intval', $m[1] ?? [])));
        sort($vars);

        $exampleValues = [];
        foreach ($vars as $n) {
            $val = $data['examples'][$n] ?? '';
            if ($val === '') {
                return back()->withInput()
                    ->with('flash.banner', "Falta un ejemplo para la variable {{{$n}}} del cuerpo.")
                    ->with('flash.bannerStyle', 'danger');
            }
            $exampleValues[] = $val;
        }

        // Componentes para Meta
        $components = [];

        if (!empty($data['header_text'])) {
            $components[] = ['type' => 'HEADER', 'format' => 'TEXT', 'text' => $data['header_text']];
        }

        $bodyComp = ['type' => 'BODY', 'text' => $data['body']];
        if (!empty($exampleValues)) {
            $bodyComp['example'] = ['body_text' => [$exampleValues]];
        }
        $components[] = $bodyComp;

        if (!empty($data['footer_text'])) {
            $components[] = ['type' => 'FOOTER', 'text' => $data['footer_text']];
        }

        $buttons = array_values(array_filter($data['buttons'] ?? [], fn($b) => trim((string) $b) !== ''));
        if (!empty($buttons)) {
            $components[] = [
                'type'    => 'BUTTONS',
                'buttons' => array_map(fn($b) => ['type' => 'QUICK_REPLY', 'text' => $b], $buttons),
            ];
        }

        $result = $this->service->create($account, [
            'name'       => $data['name'],
            'language'   => $data['language'],
            'category'   => $data['category'],
            'components' => $components,
        ]);

        if (!$result['ok']) {
            return back()->withInput()
                ->with('flash.banner', 'Meta rechazó la plantilla: ' . ($result['message'] ?? 'error'))
                ->with('flash.bannerStyle', 'danger');
        }

        return redirect()
            ->route('whatsapp.templates.index', $account)
            ->with('flash.banner', 'Plantilla enviada a Meta. Quedará en revisión (PENDING) hasta su aprobación.')
            ->with('flash.bannerStyle', 'success');
    }

    /** Elimina una plantilla por nombre. */
    public function destroy(Request $request, WhatsappAccount $account, string $name)
    {
        $this->guard($account);

        $result = $this->service->delete($account, $name);

        return redirect()->route('whatsapp.templates.index', $account)
            ->with('flash.banner', $result['ok'] ? 'Plantilla eliminada.' : ('No se pudo eliminar: ' . ($result['message'] ?? '')))
            ->with('flash.bannerStyle', $result['ok'] ? 'success' : 'danger');
    }
}
