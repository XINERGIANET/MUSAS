<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Storage2;
use App\Models\UnidadMedida;

class StorageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request)
    {
        $search = $request->input('buscar');

        // 1. Obtener productos filtrados (categoría 4 - insumos)
        $filteredProducts = Product::where('estado', 0)
            ->where('category_id', 1)
            ->when($search, fn($q) => $q->where('nombre', 'like', '%' . $search . '%'))
            ->get();

        // 2. Cargar todos los Storage2 reales existentes
        $storageReales = Storage2::with(['product:id,nombre,unit_price'])
            ->where('estado', 0)
            ->whereIn('product_id', $filteredProducts->pluck('id'))
            ->get();

        // 3. Crear registros virtuales si no existen
        $virtualStorage = [];
        $existingProductIds = $storageReales->pluck('product_id')->toArray();

        foreach ($filteredProducts as $product) {
            if (!in_array($product->id, $existingProductIds)) {
                $virtual = new Storage2([
                    'id' => null,
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'estado' => 0,
                    'created_at' => null,
                    'updated_at' => null
                ]);
                $virtual->product = $product;
                $virtualStorage[] = $virtual;
            }
        }

        $unidadMedidas = UnidadMedida::where('estado', 0)->get();

        // 4. Unir registros reales y virtuales
        $merged = $storageReales->concat(collect($virtualStorage))
            ->sortBy('product.nombre')
            ->values();

        // 5. Aplicar paginación manual
        $perPage = 30;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $pagedResults = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->slice(($currentPage - 1) * $perPage, $perPage),
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // 6. Calcular total (solo registros reales con stock)
        $total = $merged->sum(fn($item) => ($item->quantity ?? 0) * ($item->product->unit_price ?? 0));

        return view('storage1.index', [
            'storages' => $pagedResults,
            'total' => $total,
            'unidadMedidas' => $unidadMedidas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
            'unidad_medida' => 'required|string|max:255',
        ]);

        try {
            // Verificar si ya existe un producto con el mismo nombre en la misma categoría
            $existingProduct = Product::where('nombre', $request->nombre) // Corregido: usar product_name
                ->where('category_id', $request->category_id)
                ->first();

            if ($existingProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una materia prima con ese nombre en esta categoría'
                ], 422);
            }

            // Crear el nuevo producto
            $product = Product::create([
                'nombre' => $request->nombre, // Usar product_name del request
                'unidad_medida' => $request->unidad_medida,
                'unit_price' => 0,
                'category_id' => $request->category_id
            ]);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Materia prima creado exitosamente',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la materia prima: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Mostrar un registro específico
    public function show($id) 
    {
        $storage = Storage2::with('product')->findOrFail($id);

        return response()->json([
            'id' => $storage->id,
            'quantity' => $storage->quantity,
            'unit_price' => $storage->product->unit_price ?? 0, // en caso no tenga
            'product_name' => $storage->product->nombre ?? '',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
       $storages = Storage2::findOrFail($id);
        return view('storage1.index', compact('storages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity_out' => 'nullable|numeric|min:0',
            'unit_price_out' => 'nullable|numeric|min:0',
            'product_id' => 'required|exists:products,id'
        ]);

        $quantityOut = $request->input('quantity_out');
        $newPrice = $request->input('unit_price_out');
        $productId = $request->input('product_id');

        $wasModified = false;

        // Verificar si es un producto virtual (no existe en storage)
        if (empty($id) || $id === 'null' || $id === null || $id == 0 || $id === '0') {
            // CREAR nuevo registro para producto virtual
            $storage = new Storage2();
            $storage->product_id = $productId;
            $storage->quantity = $quantityOut ?? 0;
            $storage->estado = 0;
            $storage->created_at = now();
            $storage->updated_at = now();

            $wasModified = true;
        } else {
            // EDITAR registro existente (producto real)
            $storage = Storage2::findOrFail($id);

            // Actualizar cantidad si se proporcionó
            if (!is_null($quantityOut) && is_numeric($quantityOut) && $quantityOut >= 0) {
                $storage->quantity = $quantityOut;
                $wasModified = true;
            }
        }

        // Actualizar precio del producto (tanto para virtuales como reales)
        if (!is_null($newPrice) && is_numeric($newPrice) && $newPrice > 0) {
            $product = Product::findOrFail($productId);
            $product->unit_price = $newPrice;
            $product->save();
            $wasModified = true;
        }

        // Guardar el registro si hubo modificaciones
        if ($wasModified) {
            $storage->save();

            $message = empty($id) || $id === 'null' || $id === null || $id == 0 || $id === '0'
                ? 'Producto agregado al almacén correctamente.'
                : 'Registro actualizado correctamente.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('storage1.index')
                ->with('success', $message);
        }

        // Sin cambios
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'No se realizaron cambios.'
            ]);
        }

        return redirect()->route('storage1.index')
            ->with('info', 'No se realizaron cambios.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $storage = Storage2::findOrFail($id);
        $storage->delete();

        return redirect()->route('storage1.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function searchAjax(Request $request)
    {
        $query = $request->get('query');

        // 1. Filtrar productos de categoría 1 (no insumos) y estado 0
        $products = Product::where('estado', 0)
            ->where('category_id', 1)
            ->where('nombre', 'like', "%$query%")
            ->get();

        // 2. Traer los storage2 reales existentes
        $storagesReales = Storage2::where('estado', 0)
            ->whereIn('product_id', $products->pluck('id'))
            ->with('product:id,nombre,unit_price')
            ->get();

        // 3. Detectar productos sin Storage2 y crear virtuales
        $existingProductIds = $storagesReales->pluck('product_id')->toArray();
        $virtualStorages = [];

        foreach ($products as $product) {
            if (!in_array($product->id, $existingProductIds)) {
                $virtual = new Storage2([
                    'id' => null,
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'estado' => 0,
                ]);
                $virtual->product = $product;
                $virtualStorages[] = $virtual;
            }
        }

        // 4. Unir reales y virtuales y ordenar por nombre del producto
        $merged = $storagesReales->concat(collect($virtualStorages))
            ->sortBy(fn($item) => $item->product->nombre)
            ->values();

        // 5. Mapear la respuesta
        $results = $merged->map(function ($item) {
            $unitPrice = $item->product->unit_price ?? 0;
            $quantity = $item->quantity ?? 0;

            return [
                'id' => $item->id ?? 0,
                'product_id' => $item->product_id,
                'nombre' => $item->product->nombre,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ];
        });
        return response()->json($results);
    }

    public function pdf($categoria)
    {

        $cat_id=-1;
        switch ($categoria){
            case "insumos":
                $cat_id = 4;
                break;
            case "industrializados":
                $cat_id = 2;
                break;
            case "prima":
                $cat_id = 1;
                break;

        }

        $query = Storage2::with(['product'])
            ->whereHas('product', function ($q) use ($cat_id) {
                $q->where('category_id', $cat_id)
                ->where('estado', 0);
            });
        
        $storages = $query->get();

        $valorizado = $storages->sum(fn($item) => ($item->quantity ?? 0) * ($item->product->unit_price ?? 0));

        $pdf = Pdf::loadView('storage1.pdf', compact('storages','valorizado'));
        return $pdf->download('Almacén.pdf');
    }
}
