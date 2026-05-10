<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledgeEntry;
use App\Models\WhatsappAccount;
use App\Models\WhatsappAiAssistant;
use App\Services\AiKnowledgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AiKnowledgeController extends Controller
{
    private function teamId(): int
    {
        return Auth::user()->currentTeam->id;
    }

    private function ensureAssistant(WhatsappAccount $account): WhatsappAiAssistant
    {
        abort_unless($account->team_id === $this->teamId(), 404);
        return WhatsappAiAssistant::where('whatsapp_account_id', $account->id)->firstOrFail();
    }

    public function index(WhatsappAccount $account)
    {
        $assistant = $this->ensureAssistant($account);

        $entries = AiKnowledgeEntry::where('whatsapp_ai_assistant_id', $assistant->id)
            ->orderByDesc('id')
            ->get(['id', 'source', 'title', 'original_filename', 'mime_type', 'size_bytes', 'is_active', 'created_at']);

        $totalChars = AiKnowledgeEntry::where('whatsapp_ai_assistant_id', $assistant->id)
            ->where('is_active', true)
            ->selectRaw('SUM(CHAR_LENGTH(content)) as total')
            ->value('total') ?? 0;

        return response()->json([
            'ok'       => true,
            'entries'  => $entries->map(fn($e) => [
                'id'                => $e->id,
                'source'            => $e->source,
                'title'             => $e->title,
                'original_filename' => $e->original_filename,
                'mime_type'         => $e->mime_type,
                'size_kb'           => round($e->size_bytes / 1024, 1),
                'is_active'         => (bool) $e->is_active,
                'created_at'        => $e->created_at?->format('d/m/Y H:i'),
            ]),
            'total_chars' => (int) $totalChars,
            'max_chars'   => AiKnowledgeService::MAX_PROMPT_CHARS,
        ]);
    }

    public function storeFile(Request $request, WhatsappAccount $account, AiKnowledgeService $svc)
    {
        $assistant = $this->ensureAssistant($account);

        $request->validate([
            'file'  => 'required|file|max:5120|mimes:txt,md,csv,log,pdf,docx',
            'title' => 'nullable|string|max:200',
        ]);

        $file = $request->file('file');
        $text = $svc->extractText($file);

        if (trim($text) === '') {
            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo extraer texto del archivo. Para PDF instala "pdftotext" o sube TXT/MD/DOCX.',
            ], 422);
        }

        $teamId = $this->teamId();
        $path   = $file->store("ai-knowledge/{$teamId}", 'public');

        AiKnowledgeEntry::create([
            'team_id'                  => $teamId,
            'whatsapp_ai_assistant_id' => $assistant->id,
            'source'                   => 'file',
            'title'                    => $request->input('title') ?: $file->getClientOriginalName(),
            'original_filename'        => $file->getClientOriginalName(),
            'mime_type'                => $file->getMimeType(),
            'size_bytes'               => $file->getSize(),
            'content'                  => $text,
            'storage_path'             => $path,
            'is_active'                => true,
        ]);

        return response()->json(['ok' => true]);
    }

    public function storeText(Request $request, WhatsappAccount $account)
    {
        $assistant = $this->ensureAssistant($account);

        $data = $request->validate([
            'title'   => 'required|string|max:200',
            'content' => 'required|string|max:200000',
        ]);

        AiKnowledgeEntry::create([
            'team_id'                  => $this->teamId(),
            'whatsapp_ai_assistant_id' => $assistant->id,
            'source'                   => 'text',
            'title'                    => $data['title'],
            'original_filename'        => null,
            'mime_type'                => 'text/plain',
            'size_bytes'               => strlen($data['content']),
            'content'                  => $data['content'],
            'is_active'                => true,
        ]);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, AiKnowledgeEntry $aiKnowledgeEntry)
    {
        abort_unless($aiKnowledgeEntry->team_id === $this->teamId(), 404);

        $data = $request->validate([
            'title'     => 'nullable|string|max:200',
            'content'   => 'nullable|string|max:200000',
            'is_active' => 'nullable|boolean',
        ]);

        $update = [];
        if (isset($data['title']))   $update['title']   = $data['title'];
        if (isset($data['content'])) {
            $update['content']    = $data['content'];
            $update['size_bytes'] = strlen($data['content']);
        }
        $update['is_active'] = $request->boolean('is_active', $aiKnowledgeEntry->is_active);

        $aiKnowledgeEntry->update($update);
        return response()->json(['ok' => true]);
    }

    public function destroy(AiKnowledgeEntry $aiKnowledgeEntry)
    {
        abort_unless($aiKnowledgeEntry->team_id === $this->teamId(), 404);

        if ($aiKnowledgeEntry->storage_path) {
            try { Storage::disk('public')->delete($aiKnowledgeEntry->storage_path); }
            catch (\Throwable $e) { /* ignore */ }
        }

        $aiKnowledgeEntry->delete();
        return response()->json(['ok' => true]);
    }

    public function show(AiKnowledgeEntry $aiKnowledgeEntry)
    {
        abort_unless($aiKnowledgeEntry->team_id === $this->teamId(), 404);

        return response()->json([
            'ok'      => true,
            'entry'   => [
                'id'        => $aiKnowledgeEntry->id,
                'title'     => $aiKnowledgeEntry->title,
                'content'   => $aiKnowledgeEntry->content,
                'is_active' => (bool) $aiKnowledgeEntry->is_active,
            ],
        ]);
    }
}
