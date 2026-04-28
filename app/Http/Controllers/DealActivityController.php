<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealActivityController extends Controller
{
    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $data = $request->validate([
            'type'    => 'required|string|in:call,meeting,task',
            'subject' => 'required|string|max:255',
            'due_at'  => 'nullable|date',
            'notes'   => 'nullable|string|max:5000',
        ]);

        DealActivity::create([
            'deal_id' => $deal->id,
            'user_id' => Auth::id(),
            'type'    => $data['type'],
            'subject' => $data['subject'],
            'due_at'  => $data['due_at'] ?? null,
            'status'  => 'open',
            'notes'   => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Actividad creada.');
    }
}
