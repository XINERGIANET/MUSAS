<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Headquarters;
use App\Models\Movement;
use App\Models\MovementDetail;
use App\Models\Storage3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class TransformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response    
     */
    public function create(Request $request)
    {
        $query = Movement::where('tipo', 1);

        // Filtrar por sede
        if ($request->has('headquarter_id') && $request->headquarter_id) {
            $query->where('headquarter_id', $request->headquarter_id);
        }

        // Filtrar por fecha de inicio
        if ($request->has('start_date') && $request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }

        // Filtrar por fecha de fin
        if ($request->has('end_date') && $request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filtrar por turno
        if ($request->has('turno') && $request->turno != '') {
            $query->where('turno', $request->turno);
        }

        // Obtener las transformaciones con paginación
        $transformations = $query->orderBy('date', 'desc')->paginate(30);

        // Recuperar los productos, porciones y sedes
        $products = Product::where('category_id', 3)
            ->where('presentation_id', 1)
            ->where('estado', 0)
            ->get();

        $portions = Product::where('category_id', 3)
            ->where('presentation_id', 2)
            ->where('estado', 0)
            ->get();

        $headquarters = Headquarters::where('estado', 0)->get();

        return view('transformations.index', compact('products', 'headquarters', 'transformations', 'portions'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateTransformation($request);

        DB::beginTransaction();
        try {
            $movement = Movement::create([
                'headquarter_id' => $validatedData['headquarter_id'],
                'date' => $validatedData['date'],
                'estado' => 0,
                'turno' => $validatedData['turno'],
                'tipo' => 1 // transformación
            ]);

            $movement->movementDetails()->createMany([
                [
                    'product_id' => $validatedData['base'],
                    'quantity' => $validatedData['cant_base'],
                    'transformado' => 0,
                ],
                [
                    'product_id' => $validatedData['transformado'],
                    'quantity' => $validatedData['cant_transformado'],
                    'transformado' => 1,
                ],
            ]);

            // Ajustar stock en Storage3
            $headquarterId = $validatedData['headquarter_id'];

            // Producto base: restar stock
            $stockBase = Storage3::firstOrNew([
                'headquarter_id' => $headquarterId,
                'product_id' => $validatedData['base'],
            ]);

            if (!$stockBase->exists || $stockBase->quantity < $validatedData['cant_base']) {
                throw new \Exception('Stock insuficiente del producto base.');
            }

            $stockBase->quantity -= $validatedData['cant_base'];
            $stockBase->save();

            // Producto transformado: sumar stock
            $stockTransformado = Storage3::firstOrNew([
                'headquarter_id' => $headquarterId,
                'product_id' => $validatedData['transformado'],
            ]);

            if (!$stockTransformado->exists) {
                $stockTransformado->quantity = 0;
            }

            $stockTransformado->quantity += $validatedData['cant_transformado'];
            $stockTransformado->save();

            DB::commit();

            return redirect()->route('transformations.create')
                ->with('success', 'Transformación guardada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar transformación: ' . $e->getMessage()]);
        }
    }

    protected function validateTransformation(Request $request)
    {
        return $request->validate([
            'base' => 'required|integer|exists:products,id',
            'cant_base' => 'required|integer|min:1',
            'transformado' => 'required|integer|exists:products,id',
            'cant_transformado' => 'required|integer|min:1',
            'turno' => 'required|integer|in:0,1',
            'headquarter_id' => 'required|integer|exists:headquarters,id',
            'date' => 'required|string|max:255'
        ]);
    }
}
