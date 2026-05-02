<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\Pipeline;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealProductController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $team = $this->currentTeam();
        abort_unless($deal->team_id === $team->id && $deal->pipeline_id === $pipeline->id, 404);

        $data = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'name'       => 'required|string|max:255',
            'unit'       => 'nullable|string|max:50',
            'quantity'   => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount'   => 'nullable|numeric|min:0|max:100',
            'notes'      => 'nullable|string|max:500',
        ]);

        // Si seleccionó un producto del catálogo, verificar que pertenece al team
        if (!empty($data['product_id'])) {
            $product = Product::where('id', $data['product_id'])
                ->where('team_id', $team->id)
                ->firstOrFail();
        }

        DealProduct::create([
            'deal_id'    => $deal->id,
            'product_id' => $data['product_id'] ?? null,
            'name'       => $data['name'],
            'unit'       => $data['unit'] ?? 'unidad',
            'quantity'   => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount'   => $data['discount'] ?? 0,
            'notes'      => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Producto agregado.');
    }

    public function destroy(Pipeline $pipeline, Deal $deal, DealProduct $dealProduct)
    {
        $team = $this->currentTeam();
        abort_unless($deal->team_id === $team->id && $dealProduct->deal_id === $deal->id, 404);

        $dealProduct->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    public function productSearch(Request $request)
    {
        $team = $this->currentTeam();
        $q    = $request->query('q', '');

        $products = Product::where('team_id', $team->id)
            ->where('is_active', true)
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'price', 'unit', 'currency']);

        return response()->json($products);
    }
}
