<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class ReportsController extends Controller
{
    public function index()
    {
        $totalCompras = 0;
        $totalVentas = 0;

        $purchases = Purchase::with('details')->where('estado', 0)->get();
        $sales = Sale::with('details')->get();

        foreach ($purchases as $purchase) {
            $totalCompras += $purchase->details->sum('subtotal');
        }

        foreach ($sales as $sale) {
            $totalVentas += $sale->details->sum('subtotal');
        }

        $utilidadBruta = $totalVentas - $totalCompras;

        // Ventas por sede
        $ventasPorSede = Sale::with(['headquarter', 'details'])
            ->get()
            ->groupBy('headquarter_id')
            ->map(function ($ventas) {
                $total = 0;
                foreach ($ventas as $venta) {
                    $total += $venta->details->sum('subtotal');
                }

                return [
                    'sede' => $ventas->first()->headquarter->nombre ?? 'Sin Sede',
                    'total' => $total,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Ventas mensuales
        $ventasMensuales = DB::table('sales')
            ->selectRaw("MONTH(fecha) as mes, SUM(total) as total")
            ->groupBy(DB::raw("MONTH(fecha)"))
            ->pluck('total', 'mes');

        // Compras mensuales corregidas (sumar subtotales de detalles)
        $comprasPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            $comprasPorMes[$i] = 0;
        }

        foreach ($purchases as $purchase) {
            $mes = date('n', strtotime($purchase->date));
            $comprasPorMes[$mes] += $purchase->details->sum('subtotal');
        }

        // Formato para ApexCharts
        $ventas = [];
        $compras = [];
        for ($i = 1; $i <= 12; $i++) {
            $ventas[] = $ventasMensuales[$i] ?? 0;
            $compras[] = $comprasPorMes[$i] ?? 0;
        }


        $mediosPago = Payment::with('paymentMethod')
            ->selectRaw('payment_method_id, SUM(monto) as total')
            ->groupBy('payment_method_id')
            ->get()
            ->filter(fn($p) => $p->paymentMethod)
            ->map(function ($p) {
                return collect([
                    'nombre' => $p->paymentMethod->nombre,
                    'total' => (float) $p->total,
                ]);
            });

        $totalPagos = $mediosPago->sum('total');

        return view('reports.index', compact('totalCompras', 'totalVentas', 'utilidadBruta', 'ventasPorSede', 'ventas', 'compras', 'mediosPago', 'totalPagos'));
    }
}
