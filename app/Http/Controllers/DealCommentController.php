<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealComment;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealCommentController extends Controller
{
    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        // Aquí podrías verificar que el deal pertenece al pipeline y al team actual si quieres

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        DealComment::create([
            'deal_id' => $deal->id,
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        return back()->with('status', 'Comentario agregado.');
    }
}
