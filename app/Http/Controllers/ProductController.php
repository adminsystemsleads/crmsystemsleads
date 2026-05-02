<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    public function index()
    {
        $products = Product::where('team_id', $this->currentTeam()->id)
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $team = $this->currentTeam();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit'        => 'nullable|string|max:50',
            'price'       => 'required|numeric|min:0',
            'currency'    => 'required|string|size:3',
            'is_active'   => 'boolean',
        ]);

        Product::create(array_merge($data, [
            'team_id'   => $team->id,
            'unit'      => $data['unit'] ?? 'unidad',
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Producto creado.');
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->team_id === $this->currentTeam()->id, 404);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit'        => 'nullable|string|max:50',
            'price'       => 'required|numeric|min:0',
            'currency'    => 'required|string|size:3',
            'is_active'   => 'boolean',
        ]);

        $product->update(array_merge($data, [
            'unit'      => $data['unit'] ?? 'unidad',
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        abort_unless($product->team_id === $this->currentTeam()->id, 404);
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }
}
