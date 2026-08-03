<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\StorageInsumo;
use App\Models\Product;
use App\Models\ProductProvider;
use App\Models\Supplier;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MiscelaneoController extends Controller
{

    public function index()
    {
        
        $misc = Product::with('productCategory')->where('estado', 0)->where('category_id', 5)
            ->orderBy('created_at', 'desc')    
            ->paginate(10);

        return view('miscelaneos.index', compact('misc'));
    }


    public function create()
    {
        return view('miscelaneos.create');
    }

    public function store(Request $request)
    {

        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Crear el producto
            $product = Product::create([
                'nombre' => $request->nombre,
                'category_id' => $request->category_id,
                'unidad_medida' => $request->unidad_medida,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Producto registrado correctamente.',
                'data' => [
                    'product' => $product,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar el producto: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'ID inválido');
        }

        $product = Product::with(['productCategory', 'productProvider.supplier'])->findOrFail($id);

        return response()->json([
            'product' => $product
        ]);
    }


    
    public function showp($id)
    {
        $productProvider = ProductProvider::with('supplier')
            ->where('estado', 0)
            ->where('product_id', $id)
            ->get();

        return response()->json([
            'details' => $productProvider,
        ]);
    }

    public function edit($id)
    {
        // $insumo = Product::findOrFail($id);
        // return view('insumos.edit', compact('insumo'));
    }


    public function update(Request $request, $id)
    {
        try {
            $insumo = Product::findOrFail($id);
            
            // Actualizar datos básicos del producto
            $insumo->update([
                'nombre' => $request->product_name,
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Producto actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error actualizando producto: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $insumo = Product::findOrFail($id);
        $insumo->update(['estado' => 1]);
        return redirect()->route('miscelaneo.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    protected function validateInsumo(Request $request)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'unit_price' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
        ]);
    }

    public function filtrar(Request $request)
    {
        $query = $request->get('query');

        $miscelaneos = Product::where('category_id', 5)
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where('nombre', 'like', "%$query%");
            })
            ->paginate(10); 

        return response()->json([
            'miscelaneos' => $miscelaneos->items(), 
            'pagination' => [
                'current_page' => $miscelaneos->currentPage(), 
                'last_page' => $miscelaneos->lastPage(), 
                'per_page' => $miscelaneos->perPage(), 
            ]
        ]);
    }
}
