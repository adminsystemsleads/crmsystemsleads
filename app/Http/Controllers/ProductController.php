<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            'is_active'   => 'nullable|boolean',
            // file en lugar de image: no requiere GD/Imagick. Validamos por mimes y tamaño.
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $imagePath = $this->storeUploadedImage($request, $team->id);

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

        return redirect()->route('products.index')->with('success', 'Producto creado.');
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
            'is_active'   => 'nullable|boolean',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'remove_image'=> 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image') && $product->image_path) {
            try { Storage::disk('public')->delete($product->image_path); }
            catch (\Throwable $e) { Log::warning('No se pudo borrar imagen anterior: ' . $e->getMessage()); }
            $product->image_path = null;
        }

        $newPath = $this->storeUploadedImage($request, $product->team_id);
        if ($newPath !== null) {
            // Borrar imagen anterior si había
            if ($product->image_path) {
                try { Storage::disk('public')->delete($product->image_path); }
                catch (\Throwable $e) { Log::warning('No se pudo borrar imagen anterior: ' . $e->getMessage()); }
            }
            $product->image_path = $newPath;
        }

        $product->fill([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'unit'        => $data['unit'] ?? 'unidad',
            'price'       => $data['price'],
            'currency'    => $data['currency'],
            'is_active'   => $request->boolean('is_active', true),
        ])->save();

        return redirect()->route('products.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        abort_unless($product->team_id === $this->currentTeam()->id, 404);
        if ($product->image_path) {
            try { Storage::disk('public')->delete($product->image_path); }
            catch (\Throwable $e) { Log::warning('No se pudo borrar imagen: ' . $e->getMessage()); }
        }
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    /**
     * Sube la imagen al disco public. Devuelve la ruta o null si no había/falló.
     * Lanza ValidationException si el archivo subió pero el storage falló.
     */
    private function storeUploadedImage(Request $request, int $teamId): ?string
    {
        if (!$request->hasFile('image')) return null;

        $file = $request->file('image');
        if (!$file || !$file->isValid()) {
            // Posible falla por tamaño php.ini upload_max_filesize / post_max_size
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image' => 'La imagen no se pudo procesar. Puede que exceda el límite del servidor.',
            ]);
        }

        try {
            $path = $file->store("products/{$teamId}", 'public');
            if (!$path) {
                throw new \RuntimeException('Storage devolvió una ruta vacía.');
            }
            return $path;
        } catch (\Throwable $e) {
            Log::error('Error guardando imagen del producto: ' . $e->getMessage());
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image' => 'No se pudo guardar la imagen en el servidor: ' . $e->getMessage(),
            ]);
        }
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

        $first = fread($handle, 3);
        if ($first !== "\xEF\xBB\xBF") rewind($handle);

        $sample = fgets($handle);
        $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? ';' : ',';
        rewind($handle);
        if ($first === "\xEF\xBB\xBF") fread($handle, 3);

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

            $row   = array_pad($row, count($headers), null);
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
