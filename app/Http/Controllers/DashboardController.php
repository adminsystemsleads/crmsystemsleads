<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\WhatsappConversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $team = Auth::user()->currentTeam;

        if (!$team) {
            return view('dashboard', [
                'metrics' => null,
            ]);
        }

        $teamId = $team->id;
        $now    = now();
        $startMonth = $now->copy()->startOfMonth();

        /* ===== KPIs principales ===== */

        $contactsTotal  = Contact::where('team_id', $teamId)->count();
        $contactsMonth  = Contact::where('team_id', $teamId)->where('created_at', '>=', $startMonth)->count();

        $dealsTotal     = Deal::where('team_id', $teamId)->count();
        $dealsOpen      = Deal::where('team_id', $teamId)->where('status', 'open')->count();
        $dealsWon       = Deal::where('team_id', $teamId)->where('status', 'won')->count();
        $dealsLost      = Deal::where('team_id', $teamId)->where('status', 'lost')->count();
        $dealsMonth     = Deal::where('team_id', $teamId)->where('created_at', '>=', $startMonth)->count();

        // Monto ganado en el mes (todas las monedas separadas)
        $wonByCurrency = Deal::where('team_id', $teamId)
            ->where('status', 'won')
            ->where('updated_at', '>=', $startMonth)
            ->select('currency', DB::raw('COALESCE(SUM(amount),0) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($r) => [$r->currency ?: 'PEN' => (float) $r->total]);

        $conversationsTotal = WhatsappConversation::where('team_id', $teamId)->count();
        $conversationsOpen  = WhatsappConversation::where('team_id', $teamId)->where('status', 'open')->count();

        /* ===== Negociaciones por pipeline + fase ===== */

        $pipelines = Pipeline::where('team_id', $teamId)
            ->where('is_active', true)
            ->with(['stages' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        // Contar deals abiertos por stage_id en un solo query
        $dealsByStage = Deal::where('team_id', $teamId)
            ->where('status', 'open')
            ->select('stage_id', DB::raw('COUNT(*) as c'), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->groupBy('stage_id')
            ->get()
            ->keyBy('stage_id');

        $funnels = $pipelines->map(function ($pipeline) use ($dealsByStage) {
            $stages = $pipeline->stages->map(function ($s) use ($dealsByStage) {
                $row = $dealsByStage->get($s->id);
                return [
                    'id'    => $s->id,
                    'name'  => $s->name,
                    'color' => $s->color ?? '#6366f1',
                    'count' => $row ? (int) $row->c : 0,
                    'total' => $row ? (float) $row->total : 0,
                    'is_won'  => (bool) $s->is_won,
                    'is_lost' => (bool) $s->is_lost,
                ];
            });

            return [
                'id'            => $pipeline->id,
                'name'          => $pipeline->name,
                'total_deals'   => $stages->sum('count'),
                'stages'        => $stages,
                'max_count'     => max(1, $stages->max('count')),
                'kanban_url'    => route('pipelines.kanban', $pipeline),
            ];
        });

        /* ===== Top contactos con más negociaciones (top 5) ===== */
        $topContacts = Contact::where('team_id', $teamId)
            ->withCount('deals')
            ->orderByDesc('deals_count')
            ->limit(5)
            ->get(['id', 'name', 'phone', 'company']);

        /* ===== Negociaciones recientes (últimas 8) ===== */
        $recentDeals = Deal::where('team_id', $teamId)
            ->with(['stage:id,name,color', 'contact:id,name', 'pipeline:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        $metrics = [
            'contacts' => [
                'total' => $contactsTotal,
                'month' => $contactsMonth,
            ],
            'deals' => [
                'total' => $dealsTotal,
                'open'  => $dealsOpen,
                'won'   => $dealsWon,
                'lost'  => $dealsLost,
                'month' => $dealsMonth,
                'won_by_currency' => $wonByCurrency,
            ],
            'conversations' => [
                'total' => $conversationsTotal,
                'open'  => $conversationsOpen,
            ],
            'funnels'      => $funnels,
            'top_contacts' => $topContacts,
            'recent_deals' => $recentDeals,
        ];

        return view('dashboard', compact('metrics'));
    }
}
