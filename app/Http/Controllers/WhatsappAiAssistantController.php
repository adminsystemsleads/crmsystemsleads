<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Models\WhatsappAiAssistant;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappAiAssistantController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    public function edit(WhatsappAccount $account)
    {
        $team = $this->currentTeam();
        abort_unless($account->team_id === $team->id, 404);

        $assistant = WhatsappAiAssistant::where('whatsapp_account_id', $account->id)->first();
        $models    = OpenAiService::availableModels();

        return view('whatsapp.ai.edit', compact('account', 'assistant', 'models'));
    }

    public function update(Request $request, WhatsappAccount $account)
    {
        $team = $this->currentTeam();
        abort_unless($account->team_id === $team->id, 404);

        $data = $request->validate([
            'model'            => 'required|string|max:100',
            'api_key'          => 'nullable|string|max:500',
            'system_prompt'    => 'nullable|string|max:4000',
            'temperature'      => 'required|numeric|min:0|max:2',
            'max_tokens'       => 'required|integer|min:50|max:4000',
            'context_messages' => 'required|integer|min:1|max:50',
            'is_active'        => 'nullable|boolean',
            'function_calling_enabled' => 'nullable|boolean',
            'capture_contact'  => 'nullable|boolean',
            'capture_deal'     => 'nullable|boolean',
            'capture_custom'   => 'nullable|boolean',
        ]);

        $assistant = WhatsappAiAssistant::firstOrNew([
            'whatsapp_account_id' => $account->id,
        ]);

        // Asegurar campos obligatorios
        $assistant->team_id  = $team->id;
        $assistant->provider = 'openai';

        $assistant->model            = $data['model'];
        $assistant->system_prompt    = $data['system_prompt'] ?? null;
        $assistant->temperature      = $data['temperature'];
        $assistant->max_tokens       = $data['max_tokens'];
        $assistant->context_messages = $data['context_messages'];
        $assistant->is_active        = $request->boolean('is_active');
        $assistant->function_calling_enabled = $request->boolean('function_calling_enabled');
        $assistant->capture_config   = [
            'contact' => $request->boolean('capture_contact', true),
            'deal'    => $request->boolean('capture_deal', true),
            'custom'  => $request->boolean('capture_custom', true),
        ];

        // API key: solo se actualiza si se ingresó una nueva.
        // Si el registro es nuevo y no se ingresó nada, permitimos guardar
        // (la columna debe ser nullable — ver migración).
        if (!empty($data['api_key'])) {
            $assistant->api_key = $data['api_key'];
        } elseif (!$assistant->exists) {
            $assistant->api_key   = null;
            $assistant->is_active = false; // sin api key, no puede operar
        }

        try {
            $assistant->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI assistant save failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar la configuración: ' . $e->getMessage());
        }

        $msg = 'Asistente IA guardado correctamente.';
        if (empty($assistant->api_key)) {
            $msg .= ' ⚠ Falta la API Key de OpenAI — el asistente no podrá responder hasta que la agregues.';
        }

        return redirect()->route('whatsapp.ai.edit', $account)->with('status', $msg);
    }

    public function destroy(WhatsappAccount $account)
    {
        $team = $this->currentTeam();
        abort_unless($account->team_id === $team->id, 404);

        WhatsappAiAssistant::where('whatsapp_account_id', $account->id)->delete();

        return redirect()->route('whatsapp.ai.edit', $account)
            ->with('status', 'Asistente IA eliminado.');
    }
}
