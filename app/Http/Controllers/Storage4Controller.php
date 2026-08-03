<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use App\Models\ProductPrice;
use App\Models\Storage3;
use App\Models\Product;
use App\Models\Presentation;
use App\Models\UnidadMedida;
use App\Models\Category;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Storage4Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'nullable|exists:headquarters,id'
        ]);

        $search = $request->input('search');
        $headquarterId = $request->input('headquarter_id');
        $productId = $request->input('product_id');
        $presentationId = $request->input('presentation_id');

        $activeHeadquarters = Headquarters::where('estado', 0)->pluck('id')->toArray();

        // Si se especifica una sede, validar que esté activa, sino usar todas las activas
        if ($headquarterId && in_array($headquarterId, $activeHeadquarters)) {
            $headquarterIds = [$headquarterId];
        } else {
            // Usar solo las sedes activas en lugar de hardcodear [1, 2, 3, 8, 9]
            $headquarterIds = $activeHeadquarters;
        }

        // 1. Obtener productos filtrados
        $filteredProducts = \App\Models\Product::where('estado', 0)
            ->whereIn('category_id', [2, 3])
            ->when($search, fn($q) => $q->where('nombre', 'like', '%' . $search . '%'))
            ->when($productId, fn($q) => $q->where('id', $productId))
            ->when($presentationId, fn($q) => $q->where('presentation_id', $presentationId))
            ->with(['presentation:id,nombre'])
            ->get();

        // 2. Cargar todos los Storage3 existentes
        $storageReales = \App\Models\Storage3::with([
            'headquarter:id,nombre',
            'product:id,nombre,unidad_medida,presentation_id',
            'product.presentation:id,nombre'
        ])
            ->where('estado', 0)
            ->whereIn('headquarter_id', $headquarterIds)
            ->whereIn('product_id', $filteredProducts->pluck('id'))
            ->get();

        // 3. Obtener precios por sede y producto
        $precios = \App\Models\ProductPrice::where('estado', 0)
            ->whereIn('headquarter_id', $headquarterIds)
            ->whereIn('product_id', $filteredProducts->pluck('id'))
            ->get()
            ->keyBy(fn($p) => $p->headquarter_id . '-' . $p->product_id);

        // 4. Crear registros virtuales si no existen
        $virtualStorage = [];
        $headquarters = \App\Models\Headquarters::whereIn('id', $headquarterIds)->get()->keyBy('id');
        $existingKeys = $storageReales->map(fn($s) => $s->headquarter_id . '-' . $s->product_id)->toArray();

        foreach ($filteredProducts as $product) {
            foreach ($headquarterIds as $hqId) {
                $key = $hqId . '-' . $product->id;
                if (!in_array($key, $existingKeys)) {
                    $virtual = new \App\Models\Storage3([
                        'id' => null,
                        'headquarter_id' => $hqId,
                        'product_id' => $product->id,
                        'quantity' => 0,
                        'estado' => 0,
                        'created_at' => null,
                        'updated_at' => null
                    ]);
                    $virtual->product = $product;
                    $virtual->headquarter = $headquarters[$hqId];
                    $virtualStorage[] = $virtual;
                }
            }
        }

        // 5. Unir, asignar precios reales y paginar
        $merged = $storageReales->concat(collect($virtualStorage))
            ->sortBy([['headquarter_id', 'asc'], ['product_id', 'asc']])
            ->values();

        foreach ($merged as $item) {
            $key = $item->headquarter_id . '-' . $item->product_id;
            $item->unit_price_real = $precios[$key]->unit_price ?? 0;
        }

        $perPage = 30;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedResults = new LengthAwarePaginator(
            $merged->slice(($currentPage - 1) * $perPage, $perPage),
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // 6. Calcular total con los precios reales
        $total = $merged->sum(fn($item) => $item->unit_price_real * ($item->quantity ?? 0));

        // 7. Datos para los filtros
        $products = Product::select('id', 'nombre')->where('estado', 0)->get();
        $presentations = Presentation::select('id', 'nombre')->where('estado', 0)->get();
        $headquartersList = Headquarters::select('id', 'nombre')->where('estado', 0)->get();

        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $categorias = Category::where('estado', 0)->get();
        $sedes = Headquarters::where('estado', 0)->get();
        $presentaciones = Presentation::where('estado', 0)->get();
        $productCategory = ProductCategory::where('estado', 0)->where('category_id', 3)->get();

        return view('storage4.index', [
            'storageData' => $pagedResults,
            'headquarters' => $headquartersList,
            'selectedHeadquarter' => $headquarterId,
            'total' => $total,
            'products' => $products,
            'presentations' => $presentations,
            'unidadMedidas' => $unidadMedidas,
            'categorias' => $categorias,
            'presentaciones' => $presentaciones,
            'productCategory' => $productCategory,
            'sedes' => $sedes
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $storage = Storage3::with('product')->findOrFail($id);

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
        $storages = Storage3::findOrFail($id);
        return view('storage3.index', compact('storages'));
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
            'unit_price_out' => 'nullable|numeric|min:0.01',
            'product_id' => 'required|exists:products,id',
            'headquarter_id' => 'required|exists:headquarters,id'
        ]);

        $quantityOut = $request->input('quantity_out');
        $newPrice = $request->input('unit_price_out');
        $productId = $request->input('product_id');
        $headquarterId = $request->input('headquarter_id');

        $wasModified = false;

        // 1. Buscar o crear Storage3
        if ($id === 'null' || $id === null) {
            $storage = new \App\Models\Storage3();
            $storage->product_id = $productId;
            $storage->headquarter_id = $headquarterId;
            $storage->estado = 0;
        } else {
            $storage = \App\Models\Storage3::findOrFail($id);
        }

        // 2. Setear cantidad
        if (!is_null($quantityOut) && is_numeric($quantityOut) && $quantityOut >= 0) {
            $storage->quantity = $quantityOut;
            $wasModified = true;
        }

        

        // 3. Guardar o actualizar precio en product_price
        if (!is_null($newPrice) && is_numeric($newPrice)) {
            $priceRecord = \App\Models\ProductPrice::firstOrNew([
                'product_id' => $productId,
                'headquarter_id' => $headquarterId
            ]);
            $priceRecord->unit_price = $newPrice;
            $priceRecord->estado = 0;
            $priceRecord->save();

            $wasModified = true;
        }

        // 4. Guardar el registro
        if ($wasModified) {
            $storage->save();
            return redirect()->route('storage4.index')->with('success', 'Registro actualizado correctamente.');
        }

        return redirect()->route('storage4.index')->with('info', 'No se realizaron cambios.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $storage = Storage3::findOrFail($id);
        $storage->delete();

        return redirect()->route('storage4.index')
            ->with('success', 'Registro eliminado correctamente.');
        
    }
}
