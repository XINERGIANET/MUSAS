<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Headquarters;
use App\Models\Movement;
use App\Models\MovementDetail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TransfersReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $shift = $request->shift;

        $products = Product::where('estado', 0)->get();

        /** @var \App\Models\Usuario $user */
        $user = auth()->user();


        $movementsQuery = Movement::with([
            'movementDetails.product',
            'headquarter',
            'headquarter_to'
        ])
            ->where('tipo', 2);

        if ($user->hasRole('adminSede')) {
            $movementsQuery->where('headquarter_id', $user->sede_id);
        }

        $movements = $movementsQuery
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($shift !== null && $shift !== '', fn($q) => $q->where('turno', $shift))
            ->paginate(30);

        return view('transfers_report.index', compact('products', 'movements', 'user'));
    }


    public function pdf($beginDate, $endDate)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';

        $products = Product::where('estado', 0)->get();

        $movements = Movement::with('movementDetails.product')->where('tipo', 3)
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('date', '<=', $end_date);
            })
            ->get();

        $pdf = Pdf::loadView('waste_report.pdf', compact('products', 'movements'));
        return $pdf->download('MermaReporte.pdf');
    }
}
