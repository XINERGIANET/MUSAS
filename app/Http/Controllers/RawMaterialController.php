<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Storage;
use App\Models\Supplier;
use App\Models\Storage2;
use App\Models\UnidadMedida;
use App\Models\ProductCategory;
use App\Models\ProductProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mime\RawMessage;

class RawMaterialController extends Controller
{
    public function index()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $products = Product::with('productCategory')->where('estado', 0)->where('category_id', 1)
            ->orderBy('created_at', 'desc')    
            ->paginate(50);
        $categorias = Category::where('estado', 0)->get();
        $presentaciones = Presentation::all();
        $suppliers = Supplier::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 1)->get();
        return view('raw_materials.index', compact('products', 'unidadMedidas', 'presentaciones', 'categorias', 'suppliers', 'productCategory'));
    }

    public function create()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $suppliers = Supplier::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 1)->get();
        return view('raw_materials.create', compact('unidadMedidas', 'suppliers', 'productCategory'));
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
            'product_categorie_id' => 'nullable|integer|exists:product_categories,id',
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
                'product_categorie_id' => $request->product_categorie_id,
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

    public function shown($id)
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
        $product = Product::findOrFail($id);
        return view('raw_materials.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        try {
            // Validar los datos básicos
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'unit_price' => 'required|numeric|min:0',
                'unit_measure' => 'required|string|max:255',
                'product_category' => 'nullable|integer|exists:product_categories,id',
                'providers' => 'nullable|string', // JSON string
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error de validación: ' . $validator->errors()->first()
                ], 400);
            }

            DB::beginTransaction();

            $product = Product::findOrFail($id);
            
            // Actualizar datos básicos del producto
            $product->update([
                'nombre' => $request->product_name,
                'unit_price' => $request->unit_price,
                'unidad_medida' => $request->unit_measure,
                'product_categorie_id' => $request->product_category,
            ]);
            
            // Gestionar proveedores
            if ($request->has('providers') && !empty($request->providers)) {
                // Decodificar el JSON de proveedores
                $providers = json_decode($request->providers, true);
                
                if (is_array($providers)) {
                    // Obtener proveedores actuales
                    $currentProviderIds = $product->productProvider()
                        ->where('estado', 0)
                        ->pluck('supplier_id')
                        ->toArray();
                    
                    // Arrays para gestionar los cambios
                    $providersToKeep = [];
                    $providersToAdd = [];
                    $providersToDelete = [];
                    
                    // Procesar cada proveedor del request
                    foreach ($providers as $provider) {
                        if (!isset($provider['supplier_id'])) {
                            continue;
                        }
                        
                        $supplierId = (int)$provider['supplier_id'];
                        
                        if (isset($provider['to_delete']) && $provider['to_delete'] === true) {
                            // Solo eliminar si no es nuevo
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
                        $product->productProvider()
                            ->whereIn('supplier_id', $providersToDelete)
                            ->delete();
                    }
                    
                    // Determinar qué proveedores actuales ya no están en la nueva lista
                    $allNewProviderIds = array_merge($providersToKeep, $providersToAdd);
                    $providersToRemove = array_diff($currentProviderIds, $allNewProviderIds);
                    
                    if (!empty($providersToRemove)) {
                        $product->productProvider()
                            ->whereIn('supplier_id', $providersToRemove)
                            ->update(['estado' => 1]); // Soft delete
                    }
                    
                    // Agregar nuevos proveedores
                    foreach ($providersToAdd as $supplierId) {
                        // Verificar si ya existe (incluso si está eliminado)
                        $existingProvider = $product->productProvider()
                            ->where('supplier_id', $supplierId)
                            ->first();
                            
                        if ($existingProvider) {
                            // Si existe pero está eliminado, reactivarlo
                            $existingProvider->update(['estado' => 0]);
                        } else {
                            // Crear nuevo
                            $product->productProvider()->create([
                                'supplier_id' => $supplierId,
                                'estado' => 0,
                            ]);
                        }
                    }
                }
            } else {
                // Si no se envían proveedores, eliminar todos los actuales
                $product->productProvider()->update(['estado' => 1]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'Producto actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando producto: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($id);
            
            // Soft delete de los proveedores asociados
            ProductProvider::where('product_id', $id)->update(['estado' => 1]);
            
            // Soft delete del producto
            $product->update(['estado' => 1]);
            
            DB::commit();
            
            return redirect()->route('raw_materials.index')
                ->with('success', 'Materia Prima eliminada correctamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('raw_materials.index')
                ->with('error', 'Error al eliminar la materia prima.');
        }
    }

    // Método para obtener todos los proveedores (para AJAX)
    public function getAllSuppliers()
    {
        try {
            $suppliers = Supplier::where('estado', 0)
                ->select('id', 'razon_social', 'ruc', 'telefono')
                ->orderBy('razon_social')
                ->get();

            return response()->json([
                'status' => true,
                'suppliers' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo proveedores: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener proveedores',
                'suppliers' => []
            ], 500);
        }
    }

    protected function validateProduct(Request $request)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'presentation_id' => 'nullable|integer|exists:presentation,id',
            'observacion' => 'nullable|string|max:255',
            'unit_price' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'product_categorie_id' => 'nullable|integer|exists:product_categories,id',
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        $materials = Storage2::whereHas('product', function ($q) use ($query) {
            $q->where('nombre', 'like', "%$query%")
                ->where('category_id', '=', 1);
        })
            ->where('quantity', '>', 0)
            ->where('estado', 0)
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'nombre' => $item->product->nombre ?? 'Sin nombre',
                    'unidad_medida' => $item->product->unidad_medida ?? 'Sin unidad',
                    'quantity' => $item->quantity
                ];
            });

        return response()->json($materials);
    }

    public function filtrar(Request $request)
    {
        $query = $request->get('query');

        $products = Product::with(['presentation', 'productCategory'])
            ->where('category_id', 1)
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre', 'like', "%$query%")
                        ->orWhere('observacion', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['products' => $products]);
    }
}