<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FinishedProduct;
use App\Models\Presentation;
use App\Models\Headquarters;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UnidadMedida;
use App\Models\ProductCategory;
use App\Models\ProductProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinishedProductController extends Controller
{
    // Mostrar todas las sedes
    public function index()
    {
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $products =  Product::with('productCategory')->where('estado', 0)
            ->where('category_id', 2)->paginate(5);
        $categorias = Category::where('estado', 0)->get();
        $presentaciones = Presentation::all();
        $suppliers = Supplier::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 2)->get();
        return view('finished_products.index', compact('products', 'suppliers', 'unidadMedidas', 'presentaciones','categorias', 'productCategory'));
    }

    // Mostrar el formulario para crear una nueva sede
    public function create()
    {
        $sedes = Headquarters::where('estado', 0)->get();
        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $suppliers = Supplier::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 2)->get();
        return view('finished_products.create', compact('unidadMedidas', 'suppliers', 'productCategory','sedes'));
    }

    // Guardar una nueva sede
    public function store(Request $request)
    {
        // Decodificar proveedores
        $providerArray = json_decode($request->input('provider'), true);
        $priceArray = json_decode($request->input('headquarters'), true);
        $request->merge(['details_price' => $priceArray]);
        $request->merge(['details' => $providerArray]);

        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            //'unit_price' => 'required|string|max:255',
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
                //'unit_price' => $request->unit_price,
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

            //precio por sede
            if ($request->has('details_price')) {
                foreach ($request->details_price as $detail) {
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
        // Cargar el producto con sus relaciones (productCategory y productProvider.supplier)
         $product = Product::with([
            'productCategory',
            'productProvider.supplier',
            'productSede.headquarter' // <== Añadido aquí
        ])->findOrFail($id);

        return response()->json([
            'product' => $product,
            'productProviders' => $product->productProvider,
            'productCategory' => $product->productCategory,
            'prices' => $product->productSede // <== Esto se usará en el modal
        ]);
    }
    
    public function productSede()
    {
        return $this->hasMany(\App\Models\ProductPrice::class, 'product_id')->with('headquarter');
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
        return view('finished_products.edit', compact('product'));
    }


    // Actualizar una sede
    public function update(Request $request, $id)
    {
        $validatedData = $this->validateProduct($request);

        $product = Product::findOrFail($id);
        $product->update($validatedData);

        if ($request->has('prices')) {
            foreach ($request->input('prices') as $priceData) {
                if (isset($priceData['headquarter_id']) && isset($priceData['unit_price'])) {
                    $product->productSede()->updateOrCreate(
                        [
                            'headquarter_id' => $priceData['headquarter_id']
                        ],
                        [
                            'unit_price' => $priceData['unit_price'],
                            'estado' => 0
                        ]
                    );
                }
            }
        }

        return redirect()->route('finished_products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar un producto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        ProductProvider::where('product_id', $id)->update(['estado' => 1]);
        $product->update(['estado' => 1]); // Cambiar estado a 1 (eliminado)
        return redirect()->route('finished_products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }


    protected function validateProduct(Request $request)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'presentation_id' => 'nullable|integer|exists:presentation,id',
            /* 'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'supplier2_id' => 'nullable|integer|exists:suppliers,id', */
            'observacion' => 'nullable|string|max:255',
            'unit_price' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'product_categorie_id' => 'nullable|integer|exists:product_categories,id',

            'prices' => 'nullable|array',
            'prices.*.headquarter_id' => 'required|exists:headquarters,id',
            'prices.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');

        try {
            $products = FinishedProduct::where('name', 'like', "%{$query}%")
                ->select('id', 'name')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos'
            ], 500);
        }
    }

    // Búsqueda de productos terminados
    public function search(Request $request)
    {
        $query = $request->get('query');

        $products = FinishedProduct::where('estado', 0)
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%$query%")
                  ->orWhere('presentacion', 'like', "%$query%");
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function filtro(Request $request)
    {
        $query = $request->get('query');

        $products = Product::with(['presentation', 'productCategory'])
            ->where('category_id', 2)
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['products' => $products]);
    }
}