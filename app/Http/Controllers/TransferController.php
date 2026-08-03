<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\MovementDetail;
use App\Models\Product;
use App\Models\Headquarters;
use App\Models\Storage3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function create(Request $request)
    {
        $query = Movement::where('tipo', 2);

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
        $transfers = $query->orderBy('date', 'desc')->paginate(30);

        // Recuperar los productos, porciones y sedes
        $products = Product::where('estado', 0)->get();

        $headquarters = Headquarters::where('estado', 0)->get();

        return view('transfers.index', compact('products', 'headquarters', 'transfers'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'headquarter_to_id' => 'required|exists:headquarters,id|different:headquarter_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'turno' => 'required|integer|in:0,1',
            'date' => 'required|date',
        ]);
    
        try {
            DB::beginTransaction();
    
            $traslado = Movement::create([
                'headquarter_id' => $request->headquarter_id,
                'headquarter_to_id' => $request->headquarter_to_id,
                'date' => $request->date,
                'estado' => 0,
                'turno' => $request->turno,
                'tipo' => 2,
            ]);
    
            MovementDetail::create([
                'movement_id' => $traslado->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'transformado' => 0,
            ]);
    
            // RESTAR quantity en Storage3 de sede ORIGEN
            $origen = Storage3::firstOrNew([
                'headquarter_id' => $request->headquarter_id,
                'product_id' => $request->product_id,
            ]);
    
            if (!$origen->exists) {
                $origen->quantity = 0;
            }
    
            // if ($origen->quantity < $request->quantity) {
            //     throw new \Exception('Stock insuficiente en sede origen.');
            // }
    
            $origen->quantity -= $request->quantity;
            $origen->save();
    
            // SUMAR quantity en Storage3 de sede DESTINO
            $destino = Storage3::firstOrNew([
                'headquarter_id' => $request->headquarter_to_id,
                'product_id' => $request->product_id,
            ]);
    
            if (!$destino->exists) {
                $destino->quantity = 0;
            }
    
            $destino->quantity += $request->quantity;
            $destino->save();
    
            DB::commit();
    
            return redirect()->route('transfers.create')->with('success', 'Traslado registrado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el traslado: ' . $e->getMessage()]);
        }
    }
    

    public function index()
    {
        $transfers = Movement::with(['movementDetails.product', 'headquarter', 'headquarterTo'])
            ->where('tipo', 2)
            ->orderBy('date', 'desc')
            ->get();

        return view('transfers.index', compact('transfers'));
    }
}
