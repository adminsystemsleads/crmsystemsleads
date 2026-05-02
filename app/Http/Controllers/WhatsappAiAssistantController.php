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
            'is_active'        => 'boolean',
        ]);

        $assistant = WhatsappAiAssistant::firstOrNew([
            'whatsapp_account_id' => $account->id,
        ]);

        $assistant->team_id    = $team->id;
        $assistant->provider   = 'openai';
        $assistant->model      = $data['model'];
        $assistant->system_prompt    = $data['system_prompt'] ?? null;
        $assistant->temperature      = $data['temperature'];
        $assistant->max_tokens       = $data['max_tokens'];
        $assistant->context_messages = $data['context_messages'];
        $assistant->is_active        = $request->boolean('is_active');

        // Only update api_key if a new one was provided
        if (!empty($data['api_key'])) {
            $assistant->api_key = $data['api_key'];
        }

        $assistant->save();

        return redirect()->route('whatsapp.ai.edit', $account)
            ->with('status', 'Asistente IA guardado correctamente.');
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
