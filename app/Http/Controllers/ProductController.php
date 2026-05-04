<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store("products/{$team->id}", 'public');
        }

        Product::create([
            'team_id'     => $team->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'unit'        => $data['unit'] ?? 'unidad',
            'price'       => $data['price'],
            'currency'    => $data['currency'],
            'is_active'   => $request->boolean('is_active', true),
            'image_path'  => $imagePath,
        ]);

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
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image'=> 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $product->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store("products/{$product->team_id}", 'public');
        }

        $product->fill([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'unit'        => $data['unit'] ?? 'unidad',
            'price'       => $data['price'],
            'currency'    => $data['currency'],
            'is_active'   => $request->boolean('is_active', true),
        ])->save();

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        abort_unless($product->team_id === $this->currentTeam()->id, 404);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    /* ============ IMPORT CSV ============ */

    public function importForm()
    {
        return view('products.import');
    }

    public function importTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_productos.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            // BOM para Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'description', 'unit', 'price', 'currency', 'is_active']);
            fputcsv($out, ['Laptop HP 15"', 'Laptop con 8GB RAM y 256GB SSD', 'unidad', '2499.00', 'PEN', '1']);
            fputcsv($out, ['Servicio de instalación', 'Instalación a domicilio', 'servicio', '150.00', 'PEN', '1']);
            fputcsv($out, ['Mouse inalámbrico', '', 'unidad', '49.90', 'PEN', '1']);
            fclose($out);
        }, 200, $headers);
    }

    public function importStore(Request $request)
    {
        $team = $this->currentTeam();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'No se pudo abrir el archivo.');
        }

        // Detectar y descartar BOM
        $first = fread($handle, 3);
        if ($first !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detectar separador (coma o punto y coma)
        $sample = fgets($handle);
        $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? ';' : ',';
        rewind($handle);
        if ($first !== "\xEF\xBB\xBF") {
            rewind($handle);
        } else {
            fread($handle, 3);
        }

        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (!$headerRow) {
            fclose($handle);
            return back()->with('error', 'CSV vacío o inválido.');
        }
        $headers = array_map(fn($h) => strtolower(trim($h)), $headerRow);

        $created = 0;
        $errors  = [];
        $line    = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $row = array_pad($row, count($headers), null);
            $assoc = array_combine($headers, $row);

            $name = trim($assoc['name'] ?? '');
            if ($name === '') {
                $errors[] = "Línea {$line}: falta el nombre.";
                continue;
            }

            $price = (float) str_replace(',', '.', $assoc['price'] ?? '0');
            if ($price < 0) {
                $errors[] = "Línea {$line}: precio inválido.";
                continue;
            }

            Product::create([
                'team_id'     => $team->id,
                'name'        => $name,
                'description' => trim($assoc['description'] ?? '') ?: null,
                'unit'        => trim($assoc['unit'] ?? '') ?: 'unidad',
                'price'       => $price,
                'currency'    => strtoupper(trim($assoc['currency'] ?? 'PEN')) ?: 'PEN',
                'is_active'   => in_array(strtolower(trim($assoc['is_active'] ?? '1')), ['1', 'true', 'sí', 'si', 'yes']),
            ]);
            $created++;
        }
        fclose($handle);

        $msg = "Se importaron {$created} productos.";
        if ($errors) {
            $msg .= ' Errores: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('products.index')->with('success', $msg);
    }
}
