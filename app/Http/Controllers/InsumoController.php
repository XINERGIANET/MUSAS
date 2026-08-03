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

class InsumoController extends Controller
{

    public function index()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $insumo = Product::with('productCategory')->where('estado', 0)->where('category_id', 4)
            ->orderBy('created_at', 'desc')    
            ->paginate(5);
        $categorias = Category::where('estado', 0)->get();
        $suppliers = Supplier::where('estado', 0)->get();

        return view('insumos.index', compact('insumo', 'unidadMedidas', 'categorias', 'suppliers'));
    }


    public function create()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $suppliers = Supplier::where('estado', 0)->get();
        return view('insumos.create', compact('unidadMedidas', 'suppliers'));
    }

    public function store(Request $request)
    {
        // Decodificar proveedores
        $providerArray = json_decode($request->input('provider'), true);
        $request->merge(['details' => $providerArray]);

        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'unit_price' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'details' => 'nullable|array',
            'details.*.supplier_id' => 'required|exists:suppliers,id',
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
                'unit_price' => $request->unit_price,
            ]);

            // Crear relaciones con proveedores
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $product->productProvider()->create([
                        'supplier_id' => $detail['supplier_id'],
                        'estado' => 0,
                    ]);
                }
            }

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
            'product' => $product,
            'productProviders' => $product->productProvider,
            'productCategory' => $product->productCategory,
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
        $insumo = Product::findOrFail($id);
        return view('insumos.edit', compact('insumo'));
    }


    public function update(Request $request, $id)
    {
        try {
            $insumo = Product::findOrFail($id);
            
            // Actualizar datos básicos del producto
            $insumo->update([
                'nombre' => $request->product_name,
                'unit_price' => $request->unit_price,
                'unidad_medida' => $request->unit_measure,
            ]);
            
            // Gestionar proveedores
            if ($request->has('providers')) {
                // Decodificar el JSON de proveedores
                $providers = json_decode($request->providers, true);
                
                if (is_array($providers)) {
                    // Obtener todos los proveedores actuales del producto
                    $currentProviders = $insumo->productProvider()->get();
                    
                    // Arrays para gestionar los cambios
                    $providersToKeep = [];
                    $providersToAdd = [];
                    $providersToDelete = [];
                    
                    // Procesar cada proveedor del request
                    foreach ($providers as $provider) {
                        $supplierId = $provider['supplier_id'];
                        
                        if (isset($provider['to_delete']) && $provider['to_delete'] === true) {
                            // Marcar para eliminar solo si no es nuevo
                            if (!isset($provider['is_new']) || !$provider['is_new']) {
                                $providersToDelete[] = $supplierId;
                            }
                        } else {
                            // Mantener o agregar proveedor
                            if (isset($provider['is_new']) && $provider['is_new'] === true) {
                                $providersToAdd[] = $supplierId;
                            } else {
                                $providersToKeep[] = $supplierId;
                            }
                        }
                    }
                    
                    // Eliminar proveedores marcados para eliminar
                    if (!empty($providersToDelete)) {
                        $insumo->productProvider()
                            ->whereIn('supplier_id', $providersToDelete)
                            ->delete();
                    }
                    
                    // Eliminar proveedores que ya no están en la lista
                    $allProvidersToKeep = array_merge($providersToKeep, $providersToAdd);
                    $currentProviderIds = $currentProviders->pluck('supplier_id')->toArray();
                    
                    $providersToRemove = array_diff($currentProviderIds, $allProvidersToKeep);
                    if (!empty($providersToRemove)) {
                        $insumo->productProvider()
                            ->whereIn('supplier_id', $providersToRemove)
                            ->delete();
                    }
                    
                    // Agregar nuevos proveedores
                    foreach ($providersToAdd as $supplierId) {
                        $insumo->productProvider()->firstOrCreate([
                            'supplier_id' => $supplierId
                        ]);
                    }
                } else {
                    // Si providers está vacío o no es válido, eliminar todos los proveedores
                    $insumo->productProvider()->delete();
                }
            }
            
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
        ProductProvider::where('product_id', $id)->update(['estado' => 1]);
        $insumo->update(['estado' => 1]);
        return redirect()->route('insumos.index')
            ->with('success', 'Insumo eliminado correctamente.');
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

        $insumos = Product::with(['productProvider.supplier'])
            ->where('category_id', 4)
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['insumos' => $insumos]);
    }
}
