<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Headquarters;
use App\Models\Movement;
use App\Models\MovementDetail;
use App\Models\Storage3;
use App\Models\ProductPrice;

class RetouchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Movement::where('tipo', 4);

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

        // Filtrar por sede
        if ($request->has('sede_id') && $request->sede_id) {
            $query->where('headquarter_id', $request->sede_id);
        }

        // Obtener los movimientos con paginación
        $movements = $query->orderBy('date', 'desc')
                        ->orderBy('id', 'desc')
                        ->paginate(30);

        $total = MovementDetail::whereHas('movement', function ($query) {
            $query->where('tipo', 4);
        })->sum(DB::raw('quantity * unit_price'));

        $products = Product::where('estado', 0)->get();
        $sedes = Headquarters::where('estado', 0)->get();

        return view('retouch.index', compact('products', 'sedes', 'movements', 'total'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:products,id',
            'cantidad' => 'required|integer|min:1',
            'sede_id' => 'required|exists:headquarters,id',
            'fecha' => 'required|date',
            'turno' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        try{
            DB::transaction(function() use ($request){

                // Obtener el precio según producto y sede
                $productPrice = ProductPrice::where('product_id', $request->producto_id)
                    ->where('headquarter_id', $request->sede_id)
                    ->first();

                if (!$productPrice) {
                    throw new \Exception('No se encontró un precio asignado para este producto en la sede seleccionada.');
                }

                $unit_price = $productPrice->unit_price;

                $movement = Movement::create([
                    'headquarter_id' => $request->sede_id,
                    'date' => $request->fecha,
                    'estado' => 0,
                    'tipo' => 4,
                    'turno'=> $request->turno
                ]);
    
                MovementDetail::create([
                    'product_id' => $request->producto_id,
                    'movement_id' => $movement->id,
                    'quantity' => $request->cantidad,
                    'unit_price' => $unit_price
                ]);

                // RESTAR quantity en Storage3 de sede 
                $sede = Storage3::firstOrNew([
                    'headquarter_id' => $request->sede_id,
                    'product_id' => $request->producto_id,
                ]);
        
                if (!$sede->exists) {
                    $sede->quantity = 0;
                }
        
                if ($sede->quantity < $request->quantity) {
                    throw new \Exception('Stock insuficiente en sede.');
                }
        
                $sede->quantity -= $request->cantidad;
                $sede->save();

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Retoque registrado correctamente.',
                ]);
    
            });
            
            
        }catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar el retoque: ' . $e->getMessage(),
            ], 500);
        }

    }
}