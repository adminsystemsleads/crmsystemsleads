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

    /** Devuelve (JSON) las plantillas APROBADAS de una cuenta para el selector de envío masivo. */
    public function forAccount(WhatsappAccount $account)
    {
        $this->guard($account);

        $result = $this->service->listTemplates($account, true);
        if (!($result['ok'] ?? false)) {
            return response()->json(['ok' => false, 'message' => $result['message'] ?? 'Error', 'templates' => []], 200);
        }

        $templates = array_map(function ($t) {
            $comps  = collect($t['components'] ?? []);
            $header = $comps->firstWhere('type', 'HEADER');
            $body   = $comps->firstWhere('type', 'BODY');
            $footer = $comps->firstWhere('type', 'FOOTER');
            $btns   = $comps->firstWhere('type', 'BUTTONS');

            $bodyText = $body['text'] ?? '';
            preg_match_all('/\{\{\s*(\d+)\s*\}\}/', $bodyText, $m);
            $varCount = count(array_unique($m[1] ?? []));

            return [
                'name'      => $t['name'] ?? '',
                'language'  => $t['language'] ?? '',
                'category'  => $t['category'] ?? '',
                'body'      => $bodyText,
                'var_count' => $varCount,
                'header'    => $header ? [
                    'format'    => strtoupper($header['format'] ?? 'TEXT'),
                    'text'      => $header['text'] ?? '',
                    'media_url' => $header['example']['header_handle'][0] ?? null,
                ] : null,
                'footer'    => $footer['text'] ?? '',
                'buttons'   => array_map(fn($b) => ['text' => $b['text'] ?? ''], $btns['buttons'] ?? []),
            ];
        }, $result['templates'] ?? []);

        return response()->json(['ok' => true, 'templates' => array_values($templates)]);
    }

    /** Crea una plantilla en Meta. */
    public function store(Request $request, WhatsappAccount $account)
    {
        $this->guard($account);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'category'    => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language'    => 'required|string|max:10',
            'header_type'   => 'nullable|in:NONE,IMAGE,VIDEO,DOCUMENT,LOCATION',
            'header_text'   => 'nullable|string|max:60',
            'header_sample' => 'nullable|file|max:102400',
            'body'        => 'required|string|max:1024',
            'footer_text' => 'nullable|string|max:60',
            'examples'    => 'nullable|array',
            'examples.*'  => 'nullable|string|max:200',
            'buttons'         => 'nullable|array|max:3',
            'buttons.*.type'  => 'nullable|in:QUICK_REPLY,URL,PHONE_NUMBER',
            'buttons.*.text'  => 'nullable|string|max:25',
            'buttons.*.value' => 'nullable|string|max:2000',
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

        $headerType = $data['header_type'] ?? 'NONE';
        if ($headerType === 'NONE') {
            if (!empty($data['header_text'])) {
                $components[] = ['type' => 'HEADER', 'format' => 'TEXT', 'text' => $data['header_text']];
            }
        } elseif ($headerType === 'LOCATION') {
            $components[] = ['type' => 'HEADER', 'format' => 'LOCATION'];
        } else {
            // IMAGE / VIDEO / DOCUMENT: Meta requiere una muestra subida vía Resumable Upload.
            $headerComp = ['type' => 'HEADER', 'format' => $headerType];

            if ($request->hasFile('header_sample')) {
                $file = $request->file('header_sample');
                $up   = $this->service->uploadSample(
                    $account,
                    $file->getRealPath(),
                    $file->getMimeType() ?: $file->getClientMimeType(),
                    $file->getClientOriginalName()
                );

                if (!$up['ok']) {
                    return back()->withInput()
                        ->with('flash.banner', 'No se pudo subir la muestra del archivo: ' . ($up['message'] ?? 'error'))
                        ->with('flash.bannerStyle', 'danger');
                }

                $headerComp['example'] = ['header_handle' => [$up['handle']]];
            } else {
                return back()->withInput()
                    ->with('flash.banner', 'Para un encabezado de ' . strtolower($headerType) . ' debes subir un archivo de muestra.')
                    ->with('flash.bannerStyle', 'danger');
            }

            $components[] = $headerComp;
        }

        $bodyComp = ['type' => 'BODY', 'text' => $data['body']];
        if (!empty($exampleValues)) {
            $bodyComp['example'] = ['body_text' => [$exampleValues]];
        }
        $components[] = $bodyComp;

        if (!empty($data['footer_text'])) {
            $components[] = ['type' => 'FOOTER', 'text' => $data['footer_text']];
        }

        $btns = [];
        foreach (($data['buttons'] ?? []) as $b) {
            $type = $b['type'] ?? null;
            $text = trim((string) ($b['text'] ?? ''));
            if (!$type || $text === '') continue;

            if ($type === 'QUICK_REPLY') {
                $btns[] = ['type' => 'QUICK_REPLY', 'text' => $text];
            } elseif ($type === 'URL') {
                $url = trim((string) ($b['value'] ?? ''));
                if ($url !== '') $btns[] = ['type' => 'URL', 'text' => $text, 'url' => $url];
            } elseif ($type === 'PHONE_NUMBER') {
                $phone = trim((string) ($b['value'] ?? ''));
                if ($phone !== '') $btns[] = ['type' => 'PHONE_NUMBER', 'text' => $text, 'phone_number' => $phone];
            }
        }
        if (!empty($btns)) {
            $components[] = ['type' => 'BUTTONS', 'buttons' => $btns];
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
