<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Presentation;
use App\Models\Category;
use App\Models\RawMaterial;
use App\Models\UnidadMedida;
use App\Models\ProductCategory;
use App\Models\ProductProvider;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Headquarters;
use App\Models\ProductPrice;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function index()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $products = Product::with('productCategory')->where('estado', 0)->where('category_id', 3)->paginate(15);
        $categorias = Category::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 3)->get();
        $presentaciones = Presentation::all();
        $headquarters = Headquarters::where('estado', 0)->get();
        return view('products.index', compact('products', 'unidadMedidas', 'presentaciones', 'categorias', 'productCategory', 'headquarters'));
    }

    public function create()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $categorias = Category::where('estado', 0)->get();
        $sedes = Headquarters::where('estado', 0)->get();
        $presentaciones = Presentation::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 3)->get();
        return view('products.create', compact('unidadMedidas', 'categorias', 'presentaciones', 'productCategory', 'sedes'));
    }


    public function store(Request $request)
    {
        // Decodificar detalles JSON enviados en 'headquarters'
        $details = json_decode($request->input('headquarters'), true);
        $request->merge(['details' => $details]);

        // Validar request sin product_id en detalles
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'presentation_id' => 'nullable|integer|exists:presentation,id',
            'observacion' => 'nullable|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'product_categorie_id' => 'nullable|integer|exists:product_categories,id',

            'details' => 'required|array|min:1',
            'details.*.headquarter_id' => 'required|integer|exists:headquarters,id',
            'details.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Crear el producto
            $product = Product::create([
                'nombre' => $request->nombre,
                'category_id' => $request->category_id,
                'presentation_id' => $request->presentation_id,
                'observacion' => $request->observacion,
                'unidad_medida' => $request->unidad_medida,
                'product_categorie_id' => $request->product_categorie_id,
                'estado' => 0,
            ]);

            // Crear relaciones con proveedores
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $product->productSede()->create([
                        'headquarter_id' => $detail['headquarter_id'],
                        'unit_price' => $detail['unit_price'],
                        'estado' => 0,
                    ]);
                }
            }

            DB::commit();


            return response()->json([
                'status' => true,
                'message' => 'Producto registrado correctamente.',
                'product_id' => $product->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar producto: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('estado', 0)->get();
        $presentaciones = Presentation::where('estado', 0)->get();
        return response()->json($product);
    }

    public function vps($id)
    {
        $priceSede = ProductPrice::with('headquarter')
            ->whereHas('headquarter', function($query) {
                $query->where('estado', 0);
            })
            ->where('product_id', $id)
            ->get();

        return response()->json([
            'details' => $priceSede,
        ]);
    }


    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        try{
            
        $validatedData = $this->validateProduct($request);

        $product = Product::findOrFail($id);
        $product->update($validatedData);

        // --- Manejo de precios por sede ---
        if ($request->has('sedes')) {
            // Elimina los precios que ya no están en el request
            $sedeIds = array_keys($request->sedes);
            ProductPrice::where('product_id', $id)
                ->whereNotIn('headquarter_id', $sedeIds)
                ->delete();

            // Actualiza o crea los precios enviados
            foreach ($request->sedes as $headquarter_id => $sedeData) {
                ProductPrice::updateOrCreate(
                    [
                        'product_id' => $id,
                        'headquarter_id' => $headquarter_id,
                    ],
                    [
                        'unit_price' => $sedeData['precio'],
                        'estado' => 0,
                    ]
                );
            }
        } else {
            // Si no se envían sedes, elimina todos los precios asociados
            ProductPrice::where('product_id', $id)->delete();
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'error' => 'Error al actualizar producto: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        ProductProvider::where('product_id', $id)->update(['estado' => 1]);
        $product->update(['estado' => 1]); // Cambiar estado a 1 (eliminado)
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    protected function validateProduct(Request $request)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'presentation_id' => 'nullable|integer|exists:presentation,id',
            'observacion' => 'nullable|string|max:255',
            'unit_price' => 'sometimes|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'product_categorie_id' => 'nullable|integer|exists:product_categories,id',
        ]);
    }

    public function buscarProducto(Request $request)
    {
        $query = $request->input('query');
        $itemType = $request->input('item_type');

        // Buscar en la tabla de productos finales
        $results = Product::where('nombre', 'LIKE', "%{$query}%")
            ->where('estado', 0) // Asegurarse de que el estado sea 0 (activo)
            ->where('category_id', $itemType)
            ->select('id', 'nombre', 'unidad_medida') // Seleccionar solo id y nombre
            ->limit(10) // Limitar resultados
            ->get();

        return response()->json($results);
    }


    public function buscarMiscelaneo(Request $request)
    {
        $q = $request->input('q');
        $productos = Product::whereHas('category', function($query) {
                $query->where('nombre', 'Misceláneo');
            })
            ->where('nombre', 'like', "%$q%")
            ->limit(10)
            ->get(['id', 'nombre']);
        return response()->json($productos);
    }

    public function import(Request $request)
    {


        $categoryId = $request->input('category_id');

        Excel::import(new ProductsImport($categoryId), $request->file('file'));

        return response()->json([
            'status' => true,
            'message' => 'Archivo importado correctamente.'
        ]);
    }

    public function excel(Request $request)
    {
        $categoryId = $request->input('category_id');
        return Excel::download(new ProductsExport($categoryId), 'Productos.xlsx');
    }

    public function pdf(Request $request)
    {
        $categoryId = $request->input('category_id');

        // Obtener los productos según la categoría
        $products = Product::where('estado', 0)
            ->where('category_id', $categoryId)
            ->get();

        // Generar el PDF usando la vista Blade
        $pdf = Pdf::loadView('products.pdf', compact('products'));

        // Descargar el archivo PDF
        return $pdf->download('Productos.pdf');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $products = Product::with('presentation')
            ->where('category_id', 3)
            ->where('nombre', 'like', '%' . $query . '%')
            ->where('estado', 0)
            ->limit(10)
            ->get(); // ← sin limitar los campos

        return response()->json($products);
    }

    public function filtro(Request $request)
    {
        $query = $request->get('query');

        $products = Product::with(['presentation', 'productCategory'])
            ->where('category_id', 3)
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nombre', 'like', "%$query%")
                        ->orWhereHas('presentation', function ($q2) use ($query) {
                            $q2->where('nombre', 'like', "%$query%");
                        });
                });
            })
            ->get();

        return response()->json(['products' => $products]);
    }

    public function checkExists(Request $request)
    {
        $exists = Product::where('nombre', $request->nombre)
                        ->where('category_id', $request->category_id)
                        ->exists();
        
        return response()->json(['exists' => $exists]);
    }
}