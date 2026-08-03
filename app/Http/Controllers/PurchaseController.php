<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Storage;
use App\Models\Supplier;
use App\Models\Usuario;
use App\Models\Headquarters;
use App\Models\RawMaterial;
use App\Models\PaymentMethod;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Storage2;
use App\Models\StorageInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductProvider;
use App\Models\UnidadMedida;
use Barryvdh\DomPDF\Facade\Pdf;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchasesExport;
use App\Exports\ExpensesExport;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $tipo = $request->input('tipo', 'compra'); // 'compra', 'egreso', o 'egresoSede'
        $suppliers = Supplier::where('estado', 0)->get();
        $headquarters = Headquarters::where('estado', 0)->get(); 
        $users = Usuario::where('activo', 1)->get();  
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $supplier_id = $request->supplier_id;
        $productId = $request->product_id;
        $sede_id = $request->sede_id;
        $user_id = $request->user_id;

        // Caso 'egreso'
        if ($tipo === 'egreso') {
            $query = Expense::with('details')
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                ->when($productId, function($q) use ($productId) {
                    $q->whereHas('details', function ($q2) use ($productId) {
                        $q2->where('product_id', '=', $productId);
                    });
                })
                ->whereNull('sede_id')  
                ->whereNull('user_id')  
                ->orderBy('date', 'desc');

            $expenses = $query->paginate(30);

            $allExpenses = Expense::with('details')
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                ->when($productId, function($q) use ($productId) {
                    $q->whereHas('details', function ($q2) use ($productId) {
                        $q2->where('product_id', '=', $productId);
                    });
                })
                ->whereNull('sede_id') 
                ->whereNull('user_id') 
                ->get();  

            $total = 0;
            foreach ($allExpenses as $expense) {
                if ($expense->estado == 0) {  
                    $total += $expense->details->where('estado', 0)->sum('subtotal');
                }
            }

            $paymentMethods = PaymentMethod::where('estado', 0)->get();

            $products = Product::with('category')
                ->whereIn('category_id', [5])
                ->where('estado', 0)
                ->get();

            return view('purchases.index', [
                'tipo' => 'egreso',
                'purchases' => $expenses,
                'total' => $total, 
                'suppliers' => $suppliers,
                'title' => 'Gastos Diarios Generales',
                'subtitle' => 'Listado de egresos registrados',
                'paymentMethods' => $paymentMethods,
                'products' => $products,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'supplier_id' => $supplier_id,
                'product_id' => $productId,
            ]);
        } 

        // Caso 'egresoSede'
        elseif ($tipo === 'egresoSede') {
            $query = Expense::with(['details', 'user', 'sede']) 
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($sede_id, fn($q) => $q->where('sede_id', $sede_id))
                ->when($user_id, fn($q) => $q->where('user_id', $user_id))
                ->when($productId, function($q) use ($productId) {
                    $q->whereHas('details', function ($q2) use ($productId) {
                        $q2->where('product_id', '=', $productId);
                    });
                })
                ->whereNotNull('sede_id')  
                ->whereNotNull('user_id')  
                ->orderBy('date', 'desc');

            $expenses = $query->paginate(30);

            $allExpenses = Expense::with('details')
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($productId, function($q) use ($productId) {
                $q->whereHas('details', function ($q2) use ($productId) {
                    $q2->where('product_id', '=', $productId);
                });
            })
            ->whereNotNull('sede_id') 
            ->whereNotNull('user_id') 
            ->get();

            $total = 0;
            foreach ($allExpenses as $expense) {
                if ($expense->estado == 0) {  
                    $total += $expense->details->where('estado', 0)->sum('subtotal');
                }
            }

            $paymentMethods = PaymentMethod::where('estado', 0)->get();

            $products = Product::with('category')
                ->whereIn('category_id', [5])
                ->where('estado', 0)
                ->get();

            return view('purchases.index', [
                'tipo' => 'egresoSede',
                'purchases' => $expenses,
                'total' => $total, 
                'suppliers' => $suppliers,
                'title' => 'Gastos Diarios por Sede',
                'subtitle' => 'Listado de egresos registrados por sede',
                'paymentMethods' => $paymentMethods,
                'products' => $products,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'sede_id' => $sede_id,
                'user_id' => $user_id,
                'product_id' => $productId,
                'headquarters' => $headquarters,
                'users' => $users, 
            ]);
        }

        // Caso 'compra'
        else {
            $query = Purchase::with('details')
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                ->when($productId, function($q) use ($productId){
                    $q->whereHas('details', function ($q2) use ($productId){
                        $q2->where('product_id', '=', $productId);
                    });
                })
                ->orderBy('date', 'desc');

            $purchases = $query->paginate(30);

            $allPurchases = Purchase::with('details')
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                ->when($productId, function($q) use ($productId){
                    $q->whereHas('details', function ($q2) use ($productId){
                        $q2->where('product_id', '=', $productId);
                    });
                })
                ->get();

            $total = 0;
            foreach ($allPurchases as $purchase) {
                if ($purchase->estado == 0) {  
                    $total += $purchase->details->where('estado', 0)->sum('subtotal');
                }
            }

            $paymentMethods = PaymentMethod::where('estado', 0)->get();

            $products = Product::with('category')
                ->whereIn('category_id', [1, 2, 4])
                ->where('estado', 0)
                ->get();

            return view('purchases.index', [
                'tipo' => 'compra',
                'purchases' => $purchases,
                'total' => $total, 
                'suppliers' => $suppliers,
                'title' => 'Compras',
                'subtitle' => 'Listado de compras registradas',
                'paymentMethods' => $paymentMethods,
                'products' => $products,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'supplier_id' => $supplier_id,
                'product_id' => $productId,
            ]);
        }
    }

    public function show(Request $request, $id)
    {
        $tipo = $request->input('tipo'); // 'compra' o 'egreso'

        if (!in_array($tipo, ['compra', 'egreso'])) {
            return response()->json([
                'status' => false,
                'error'  => 'Tipo inválido. Debe ser "compra" o "egreso".'
            ], 400);
        }

        if ($tipo === 'compra') {
            $registro = Purchase::with('details.product')->findOrFail($id);
        } else {
            $registro = Expense::with('details.product')->findOrFail($id);
        }

        return response()->json([
            'status'  => true,
            'details' => $registro->details,
        ]);
    }

    public function showDetails(Request $request, $id)
    {
        $tipo = $request->query('tipo'); // 'compra' o 'egreso'

        if ($tipo === 'compra') {
            $purchase = Purchase::with('details.product')->findOrFail($id);
            return response()->json([
                'status' => true,
                'tipo' => 'compra',
                'details' => $purchase->details,
            ]);
        }

        if ($tipo === 'egreso') {
            $expense = Expense::with('details.product')->findOrFail($id);
            return response()->json([
                'status' => true,
                'tipo' => 'egreso',
                'details' => $expense->details,
            ]);
        }

        return response()->json([
            'status' => false,
            'error' => 'Tipo inválido. Debe ser "compra" o "egreso".',
        ], 400);
    }

    public function edit($tipo, $id)
    {
        if (!in_array($tipo, ['compra', 'egreso'])) {
            return response()->json([
                'status' => false,
                'error'  => 'Tipo inválido. Debe ser "compra" o "egreso".'
            ], 400);
        }

        if ($tipo === 'compra') {
            $registro = Purchase::with(['details', 'supplier'])->findOrFail($id);
        } else {
            $registro = Expense::with(['details', 'supplier'])->findOrFail($id);
        }

        return response()->json([
            'status'   => true,
            'registro' => $registro
        ]);
    }

    public function updateEstado($tipo, $id)
    {
        if (!in_array($tipo, ['compra', 'egreso'])) {
            return response()->json([
                'status' => false,
                'error'  => 'Tipo inválido. Debe ser "compra" o "egreso".'
            ], 400);
        }

        if ($tipo === 'compra') {
            $registro = Purchase::findOrFail($id);
            $redirectURL = url('/purchases') . '?tipo=compra';
        } else {
            $registro = Expense::findOrFail($id);
            $redirectURL = url('/purchases') . '?tipo=egreso';
        }

        $registro->estado = 1;
        $registro->save();

        return redirect($redirectURL)->with('success', 'Registro eliminado correctamente.');
    }

    public function create(Request $request)
    {
        $tipo = $request->input('tipo', 'compra');
        $title = $tipo === 'egreso' ? 'Gastos Diarios' : 'Compras';
        $subtitle = $tipo === 'egreso' ? 'Registro de nuevo gasto' : 'Registro de nueva compra';

        $unidadMedidas = UnidadMedida::where('estado', 0)->get();
        $paymentMethods = PaymentMethod::where('estado', 0)->get();
        $suppliers = Supplier::select('id', 'razon_social')->where('estado', 0)->get();

        // Verificar el tipo y cargar los datos correspondientes
        if ($tipo === 'egreso') {
            $products = Product::with('category')
                ->whereIn('category_id', [5])
                ->where('estado', 0)
                ->get();

            $categories = Category::whereIn('id', [5])->where('estado', 0)->get();

            return view('purchases.create', compact(
                'tipo', 'title', 'subtitle', 'paymentMethods', 'suppliers', 'products', 'categories', 'unidadMedidas'
            ));
        } else {
            $products = Product::with('category')
                ->whereIn('category_id', [1, 2, 4])
                ->where('estado', 0)
                ->get();

            $categories = Category::whereIn('id', [1, 2, 4])->where('estado', 0)->get();

            return view('purchases.create', compact(
                'tipo', 'title', 'subtitle', 'paymentMethods', 'suppliers', 'products', 'categories', 'unidadMedidas'
            ));
        }
    }

    public function store(Request $request)
    {
        $tipo = $request->input('tipo'); // 'compra' o 'egreso'
        $details = json_decode($request->input('products'), true);

        $validator = Validator::make(array_merge($request->all(), ['details' => $details]), [
            'tipo'                   => 'required|in:compra,egreso',
            'tipo_comprobante'       => 'required|numeric|min:1',
            'invoice_number'         => 'nullable|string',
            'payment_method_id'      => 'required|exists:payment_methods,id',
            'date'                   => 'required|date',
            'supplier_id'            => 'nullable|exists:suppliers,id',
            'details'                => 'required|array|min:1',
            'details.*.quantity'     => 'required|numeric|min:0.01',
            'details.*.price'        => 'required|numeric|min:0',
            'details.*.subtotal'     => 'required|numeric|min:0',
            'details.*.unidad_medida' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error'  => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $processedDetails = [];

            foreach ($details as $detail) {
                if ($detail['quantity'] <= 0) continue;

                $productId = $detail['product_id'];

                if ($tipo === 'compra') {
                    // Permitir creación de productos nuevos
                    if ($productId < 0) {
                        if (!isset($detail['category_id'], $detail['nombre'])) {
                            throw new \Exception('Faltan datos del producto nuevo');
                        }

                        $existingProduct = Product::where('nombre', $detail['nombre'])
                            ->where('category_id', $detail['category_id'])
                            ->first();

                        if ($existingProduct) {
                            $productId = $existingProduct->id;
                        } else {
                            $newProduct = Product::create([
                                'nombre'        => $detail['nombre'],
                                'unidad_medida' => $detail['unidad_medida'],
                                'category_id'   => $detail['category_id'],
                                'unit_price'    => $detail['price'],
                                'estado'        => 0,
                                'created_at'    => now(),
                            ]);
                            $productId = $newProduct->id;
                        }
                    }
                } elseif ($tipo === 'egreso') {
                    // En egresos no se aceptan productos nuevos
                    if ($productId < 0) {
                        if (!isset($detail['category_id'], $detail['nombre'])) {
                            throw new \Exception('Faltan datos del producto nuevo');
                        }

                        $existingProduct = Product::where('nombre', $detail['nombre'])
                            ->where('category_id', $detail['category_id'])
                            ->first();

                        if ($existingProduct) {
                            $productId = $existingProduct->id;
                        } else {
                            $newProduct = Product::create([
                                'nombre'        => $detail['nombre'],
                                'category_id'   => $detail['category_id'],
                                'unit_price'    => $detail['price'],
                                'estado'        => 0,
                                'unidad_medida' => 'Unidad',
                                'created_at'    => now(),
                            ]);
                            $productId = $newProduct->id;
                        }
                    }
                }

                $processedDetails[] = [
                    'product_id'  => $productId,
                    'quantity'    => $detail['quantity'],
                    'unit_price'  => $detail['price'],
                    'subtotal'    => $detail['subtotal'],
                ];
            }

            if (empty($processedDetails)) {
                throw new \Exception('No hay detalles válidos para procesar');
            }

            if ($tipo === 'compra') {
                $compra = Purchase::create([
                    'tipo_comprobante'   => $request->tipo_comprobante,
                    'invoice_number'     => $request->invoice_number,
                    'payment_method_id'  => $request->payment_method_id,
                    'date'               => $request->date,
                    'supplier_id'        => $request->supplier_id,
                ]);

                foreach ($processedDetails as $detail) {
                    $compra->details()->create($detail);

                    Product::where('id', $detail['product_id'])
                        ->update(['unit_price' => $detail['unit_price']]);

                    Storage2::firstOrCreate(
                        ['product_id' => $detail['product_id']],
                        ['quantity' => 0]
                    )->increment('quantity', $detail['quantity']);
                }

            } elseif ($tipo === 'egreso') {
                $compra = Expense::create([
                    'tipo_comprobante'   => $request->tipo_comprobante,
                    'invoice_number'     => $request->invoice_number,
                    'payment_method_id'  => $request->payment_method_id,
                    'date'               => $request->date,
                    'supplier_id'        => $request->supplier_id,
                ]);

                foreach ($processedDetails as $detail) {
                    $compra->details()->create($detail);

                    Product::where('id', $detail['product_id'])
                        ->update(['unit_price' => $detail['unit_price']]);

                    Storage2::firstOrCreate(
                        ['product_id' => $detail['product_id']],
                        ['quantity' => 0]
                    )->increment('quantity', $detail['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => ucfirst($tipo) . ' registrada correctamente.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error'  => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveproveedores(Request $request)
    {
        $validatedData = $this->validateSupplier($request);

        $proveedor = Supplier::create(array_merge($validatedData, ['estado' => 0]));

        return response()->json(['success' => true, 'provider' => $proveedor]);
    }

    protected function validateSupplier(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'ruc' => 'required|string|max:20',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'dias_pago' => 'nullable|integer|min:0',
        ]);
    }

    // Actualizar una compra existente
    public function update(Request $request, $tipo, $id)
    {
        if (!in_array($tipo, ['compra', 'egreso'])) {
            return response()->json([
                'status' => false,
                'message' => 'Tipo inválido. Debe ser "compra" o "egreso".'
            ], 400);
        }

        // Selección dinámica del modelo según el tipo
        $modelo = $tipo === 'compra' ? Purchase::class : Expense::class;

        $registro = $modelo::findOrFail($id);
        $registro->update($request->all());

        return response()->json([
            'status'  => true,
            'message' => ucfirst($tipo) . ' actualizada correctamente.',
        ]);
    }

    // Eliminar una compra
    public function destroy($id)
    {
        // Implementar lógica de eliminación si es necesario
    }

    public function getProductosProveedor($id)
    {
        $productos = [];

        $query = ProductProvider::with('product.category')
            ->where('supplier_id', $id)
            ->whereHas('product.category', function ($q) {
                $q->whereIn('category_id', [1, 2, 4]);
            })
            ->where('estado', 0)
            ->get();

        foreach ($query as $value) {
            $productos[] = [
                'table_id' => $value->id,
                'product_id' => $value->product_id,
                'id' => $value->product->id,
                'nombre' => $value->product->nombre,
                'category' => [
                    'nombre' => $value->product->category->nombre ?? 'Sin categoría'
                ]
            ];
        }

        return response()->json($productos);
    }

    public function pdf(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'compra'); 
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $supplier_id = $request->supplier_id;

            $supplierName = '';
            $totalGeneral = 0;

            if ($tipo === 'egreso') {
                $query = Expense::with(['details.product', 'supplier', 'paymentMethod'])
                    ->where('estado', 0) 
                    ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                    ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                    ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                    ->orderBy('date', 'desc');


                $records = $query->get();

                foreach ($records as $expense) {
                    $totalGeneral += $expense->details->sum('subtotal');
                }

                if ($supplier_id && $records->count() > 0) {
                    $supplier = $records->first()->supplier ?? null;
                    $supplierName = $supplier ? $supplier->razon_social : '';
                }

                $title = 'Reporte de Egresos';
                $subtitle = 'Listado de egresos registrados';
            } else {
                $query = Purchase::with(['details.product', 'supplier', 'paymentMethod'])
                    ->where('estado', 0) 
                    ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                    ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                    ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                    ->orderBy('date', 'desc');


                $records = $query->get();

                foreach ($records as $purchase) {
                    $totalGeneral += $purchase->details->sum('subtotal');
                }

                if ($supplier_id && $records->count() > 0) {
                    $supplier = $records->first()->supplier ?? null;
                    $supplierName = $supplier ? $supplier->razon_social : '';
                }

                $title = 'Reporte de Compras';
                $subtitle = 'Listado de compras registradas';
            }

            $data = [
                'tipo' => $tipo,
                'purchases' => $records,
                'totalGeneral' => $totalGeneral,
                'title' => $title,
                'subtitle' => $subtitle,
                'filters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplierName
                ]
            ];

            $pdf = Pdf::loadView('purchases.pdf', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function pdf_general(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'compra');
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $supplier_id = $request->supplier_id;

            $totalGeneral = 0;
            $supplierName = '';
            $purchases = collect();

            if ($tipo === 'egreso') {
                $purchases = \DB::table('expenses')
                    ->join('expense_details', 'expenses.id', '=', 'expense_details.expense_id')
                    ->leftJoin('suppliers', 'expenses.supplier_id', '=', 'suppliers.id')
                    ->select(
                        \DB::raw("COALESCE(suppliers.razon_social, 'Sin proveedor') as razon_social"),
                        \DB::raw('SUM(expense_details.subtotal) as total')
                    )
                    ->where('expenses.estado', 0)
                    ->when($start_date, fn($q) => $q->whereDate('expenses.date', '>=', $start_date))
                    ->when($end_date, fn($q) => $q->whereDate('expenses.date', '<=', $end_date))
                    ->when($supplier_id, fn($q) => $q->where('expenses.supplier_id', $supplier_id))
                    ->groupBy('suppliers.razon_social')
                    ->get();

            } else {
                $purchases = \DB::table('purchases')
                    ->join('purchase_details', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->select(
                        \DB::raw("COALESCE(suppliers.razon_social, 'Sin proveedor') as razon_social"),
                        \DB::raw('SUM(purchase_details.subtotal) as total')
                    )
                    ->where('purchases.estado', 0)
                    ->when($start_date, fn($q) => $q->whereDate('purchases.date', '>=', $start_date))
                    ->when($end_date, fn($q) => $q->whereDate('purchases.date', '<=', $end_date))
                    ->when($supplier_id, fn($q) => $q->where('purchases.supplier_id', $supplier_id))
                    ->groupBy('suppliers.razon_social')
                    ->get();
            }

            // Calcular total
            $totalGeneral = $purchases->sum('total');

            if ($supplier_id && $purchases->count() > 0) {
                $supplier = Supplier::find($supplier_id);
                $supplierName = $supplier ? $supplier->razon_social : '';
            }

            $data = [
                'tipo' => $tipo,
                'purchases' => $purchases,
                'totalGeneral' => $totalGeneral,
                'filters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplierName
                ]
            ];

            $pdf = Pdf::loadView('purchases.pdf_general', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_general_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function generatePDFProduct(Request $request)
    {
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $supplierId = $request->supplier_id;
            $productId = $request->product_id;
            $tipo = $request->input('tipo', 'compra');

            if (!$productId) {
                return response()->json(['error' => 'ID de producto requerido'], 400);
            }

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            // Elegir el modelo base según el tipo
            if ($tipo === 'egreso') {
                $query = Expense::with(['supplier', 'details.product'])->where('estado', 0);
            } else {
                $query = Purchase::with(['supplier', 'details.product'])->where('estado', 0);
            }

            // Filtros
            if ($startDate) $query->whereDate('date', '>=', $startDate);
            if ($endDate)   $query->whereDate('date', '<=', $endDate);
            if ($supplierId) $query->where('supplier_id', $supplierId);

            $records = $query->get();

            $productData = $this->getProductSummary($records, $productId);

            if (empty($productData['details'])) {
                $productData = [
                    'total_quantity' => 0,
                    'total_subtotal' => 0,
                    'details' => [],
                    'message' => 'No hay registros para este producto en el período seleccionado'
                ];
            }

            $data = [
                'productData' => $productData,
                'product' => $product,
                'tipo' => $tipo,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filters' => $request->all()
            ];

            Log::info('PDF Product generado', [
                'tipo' => $tipo,
                'product_id' => $productId,
                'nombre' => $product->nombre,
                'detalles' => count($productData['details'])
            ]);

            $pdf = PDF::loadView('purchases.pdf_product', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_' . $tipo . '_' . strtolower(str_replace(' ', '_', $product->nombre)) . '_' . date('Y-m-d') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating PDF Product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }


    public function generatePDFAllProducts(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'compra'); 

            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $supplierId = $request->supplier_id;

            $query = ($tipo === 'egreso'
                ? Expense::with(['supplier', 'details.product'])
                : Purchase::with(['supplier', 'details.product'])
            )->where('estado', 0);

            if ($startDate) {
                $query->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date', '<=', $endDate);
            }
            if ($supplierId) {
                $query->where('supplier_id', $supplierId);
            }

            $records = $query->get();

            $allProductsSummary = $this->getAllProductsSummary($records);

            if (empty($allProductsSummary)) {
                $allProductsSummary = [];
                $totalGeneral = 0;
                $message = 'No hay ' . ($tipo === 'egreso' ? 'egresos' : 'compras') . ' registradas en el período seleccionado';
            } else {
                $totalGeneral = array_sum(array_column($allProductsSummary, 'total_subtotal'));
                $message = null;
            }

            $data = [
                'productsSummary' => $allProductsSummary,
                'totalGeneral' => $totalGeneral,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filters' => $request->all(),
                'message' => $message,
                'tipo' => $tipo, 
            ];

            $pdf = PDF::loadView('purchases.pdf_all_products', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'reporte_todos_los_productos_' . $tipo . '_' . date('Y-m-d') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating PDF All Products: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar corregido para obtener resumen de un producto específico
    private function getProductSummary($purchases, $productId)
    {
        $totalQuantity = 0;
        $totalSubtotal = 0;
        $details = [];

        foreach ($purchases as $purchase) {
            if ($purchase->details && $purchase->details->count() > 0) {
                foreach ($purchase->details as $detail) {
                    // Verificar que el detalle tiene los datos necesarios
                    if (!$detail || $detail->estado != 0) {
                        continue;
                    }

                    $detailProductId = $detail->product_id ?? $detail->insumo_id ?? null;
                    
                    if ($detailProductId == $productId) {
                        $totalQuantity += $detail->quantity ?? 0;
                        $totalSubtotal += $detail->subtotal ?? 0;
                        
                        $details[] = [
                            'purchase_date' => $purchase->date,
                            'supplier' => $purchase->supplier->razon_social ?? 'Sin proveedor',
                            'invoice_number' => $purchase->invoice_number ?? '---',
                            'quantity' => $detail->quantity ?? 0,
                            'unit_price' => $detail->unit_price ?? 0,
                            'subtotal' => $detail->subtotal ?? 0
                        ];
                    }
                }
            }
        }

        return [
            'total_quantity' => $totalQuantity,
            'total_subtotal' => $totalSubtotal,
            'details' => $details
        ];
    }

    // Método auxiliar corregido para obtener resumen de todos los productos
    private function getAllProductsSummary($purchases)
    {
        $productsSummary = [];

        foreach ($purchases as $purchase) {
            if ($purchase->details && $purchase->details->count() > 0) {
                foreach ($purchase->details as $detail) {
                    // Verificar que el detalle tiene los datos necesarios
                    if (!$detail || $detail->estado != 0) {
                        continue;
                    }

                    $product = $detail->product ?? $detail->insumo ?? null;
                    $productName = $product ? $product->nombre : 'Producto sin nombre';
                    $productId = $detail->product_id ?? $detail->insumo_id ?? 'unknown';

                    // Validar que tenemos un ID válido
                    if (!$productId || $productId === 'unknown') {
                        continue;
                    }

                    if (!isset($productsSummary[$productId])) {
                        $productsSummary[$productId] = [
                            'name' => $productName,
                            'total_quantity' => 0,
                            'total_subtotal' => 0,
                            'details' => []
                        ];
                    }

                    $productsSummary[$productId]['total_quantity'] += $detail->quantity ?? 0;
                    $productsSummary[$productId]['total_subtotal'] += $detail->subtotal ?? 0;
                    
                    $productsSummary[$productId]['details'][] = [
                        'purchase_date' => $purchase->date,
                        'supplier' => $purchase->supplier->razon_social ?? 'Sin proveedor',
                        'invoice_number' => $purchase->invoice_number ?? '---',
                        'quantity' => $detail->quantity ?? 0,
                        'unit_price' => $detail->unit_price ?? 0,
                        'subtotal' => $detail->subtotal ?? 0
                    ];
                }
            }
        }

        // Ordenar por nombre de producto
        uasort($productsSummary, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $productsSummary;
    }

    public function excel(Request $request)
    {
        // Obtener las fechas de la solicitud
        $tipo = $request->input('tipo', 'compra');
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        try {
            if ($tipo === 'compra') {
                return Excel::download(new PurchasesExport($start_date, $end_date), 'Compras.xlsx');
            }elseif ($tipo === 'egreso') {
                return Excel::download(new ExpensesExport($start_date, $end_date), 'Egresos.xlsx');
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
