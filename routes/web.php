<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\Storage2Controller;
use App\Http\Controllers\Storage3Controller;
use App\Http\Controllers\Storage4Controller;
use App\Http\Controllers\StorageInsumoController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\FinishedProductController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\TransformationController;
use App\Http\Controllers\TransformationsReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMaterialController;
use App\Http\Controllers\WasteController;
use App\Http\Controllers\WasteReportController;
use App\Http\Controllers\RetouchController;
use App\Http\Controllers\RetouchReportController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransfersReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngresosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\RestauranteController;
use App\Models\Production;
use App\Http\Controllers\MiscelaneoController;
use Illuminate\Support\Facades\Artisan;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('/storage', function () {
    Artisan::call('storage:link');
});

Route::get('/', function () {
    return view('auth.index');
})->middleware('guest');


Route::get('/test', function () {
    return view('prueba');
})->name('test');

Route::get('/test-modal', function () {
    return view('test-modal');
})->name('test-modal')->middleware('auth');

Route::get('/dashboard-simple', function () {
    return view('dashboard-simple');
})->name('dashboard-simple')->middleware('auth');


Route::get('/token', function () {
    return response()->json(['token' => csrf_token()]);
});



Route::group(['middleware' => 'auth'], function () {

    Route::post('/user/set-turno', [UsuarioController::class, 'setTurno'])->name('user.setTurno');

    //rutas admin
    Route::middleware(['auth', 'role:admin|Xinergia'])->group(function () {
        //CRUD PRODUCTO

        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('/products/pdf', [ProductController::class, 'pdf'])->name('products.pdf');
        Route::get('products/excel', [ProductController::class, 'excel'])->name('products.excel');
        Route::resource('products', ProductController::class);
        Route::get('/productosede/{id}', [ProductController::class, 'vps'])->name('products.vps');


        Route::get('/buscar-producto', [ProductController::class, 'buscarProducto'])->name('buscarProducto.producto');
        Route::get('/production/pdf/normal/{startDate}/{endDate}/{sede?}', [ProductionController::class, 'pdf'])->name('production.pdf');
        Route::get('/production/pdf-resumen/{startDate}/{endDate}/{sede?}/{turno?}', [ProductionController::class, 'pdfResumen'])->name('production.pdf-resumen');
        Route::get('/production/pdf_personalized/{startDate}/{endDate}/{sede?}/{turno?}/{tipo?}', [ProductionController::class, 'pdf_personalized'])->name('production.pdf_personalized');
        Route::get('/production/pdf_summary/{startDate}/{endDate}/{sede?}/{turno?}/{tipo?}', [ProductionController::class, 'pdf_summary'])->name('production.pdf_summary');
        Route::get('/production/pdfanticipos/{startDate}/{endDate}/{sede?}', [ProductionController::class, 'pdfanticipos'])->name('production.pdfanticipos');
        Route::get('/ingresos/pdf/{startDate}/{endDate}/{categoria?}', [IngresosController::class, 'pdf'])->name('ingresos.pdf');
        Route::get('/ingresos/pdf-resumen', [IngresosController::class, 'pdfResumen'])->name('ingresos.pdf-resumen');
        Route::get('consumption/pdf-areas', [ConsumptionController::class, 'pdfAreas'])->name('consumption.pdf-areas');
        Route::get('/consumption/pdf', [ConsumptionController::class, 'pdf'])->name('consumption.pdf');
        Route::get('/consumption/pdf-resumen', [ConsumptionController::class, 'pdfResumen'])->name('consumption.pdf-resumen');
        Route::get('/wasteReport/pdf/{startDate}/{endDate}', [WasteReportController::class, 'pdf'])->name('wasteReport.pdf');
        Route::get('/retouchReport/pdf/{startDate}/{endDate}', [RetouchReportController::class, 'pdf'])->name('retouchReport.pdf');


        //CRUD CLIENTE
        Route::resource('clients', ClientController::class);

        //CRUD PERSONAL
        Route::resource('staff', StaffController::class);

        //CRUD MATERIA PRIMA
        Route::resource('raw_materials', RawMaterialController::class);
        Route::get('/nuevo/{id}', [RawMaterialController::class, 'shown'])->name('raw_materials.shown');

        //CRUD PROOVEDOR
        Route::resource('suppliers', SupplierController::class);
        Route::get('/buscar-proveedor', [SupplierController::class, 'buscar'])->name('buscar.proveedor');

        Route::get('/reports', function () {
            return view('reports.index');
        })->name('reports.index');

        Route::get('/main', function () {
            return view('dashboard.index');
        })->name('dashboard.index');

        //CRUD METODO DE PAGO
        Route::resource('payment_methods', PaymentMethodController::class);

        //CRUD USUARIO
        Route::resource('usuarios', UsuarioController::class);

        Route::resource('puestos', PuestoController::class);
        Route::get('/insumos/filtrar', [InsumoController::class, 'filtrar'])->name('insumos.filtrar');

        Route::resource('insumos', InsumoController::class);
        // En routes/web.php
        Route::get('/suppliersall', [SupplierController::class, 'getAllSuppliers'])->name('suppliersall');
        Route::get('/proovedor/{id}', [InsumoController::class, 'showp'])->name('insumos.showp');


        //CRUD ALMACEN
        Route::get('/storage1/search', [StorageController::class, 'searchAjax'])->name('storage1.search');
        Route::apiResource('storage1', StorageController::class);

        //CRUD ALMACEN
        Route::get('/storage2/search', [Storage2Controller::class, 'searchAjax'])->name('storage2.search');
        Route::apiResource('storage2', Storage2Controller::class);

        //CRUD ALMACEN
        Route::resource('storage4', Storage4Controller::class);

        Route::get('/storageInsumo/search', [StorageInsumoController::class, 'searchAjax'])->name('storageInsumo.search');
        Route::resource('storageInsumo', StorageInsumoController::class);

        Route::get('/storage/pdf/{categoria}', [StorageController::class, 'pdf'])->name('storage.pdf');

        //CRUD SEDE
        Route::resource('headquarters', HeadquartersController::class);

        //CRUD PRODUCTO FINALIZADO
        Route::resource('finished_products', FinishedProductController::class);
        Route::get('/nuevos/{id}', [FinishedProductController::class, 'shown'])->name('finished_products.shown');


        //CRUD PRODUCTO FINALIZADO
        Route::resource('unidad_medidas', UnidadMedidaController::class);

        //CRUD CATEGORIA
        Route::resource('category', CategoryController::class);



        Route::resource('ingresos', IngresosController::class);

        // Búsqueda de productos terminados
        Route::get('/search', [ProductController::class, 'search'])->name('products.search');

        //CONSUMO
        Route::post('/consumption/storeAjax', [StaffController::class, 'storeAjax'])->name('staff.storeAjax');
        Route::put('/consumptions/{id}', [ConsumptionController::class, 'update'])->name('consumptions.update');
        Route::delete('/consumption/{id}', [ConsumptionController::class, 'destroy'])->name('consumption.destroy');
        Route::resource('consumption', ConsumptionController::class);
        Route::get('/raw-materials/search', [RawMaterialController::class, 'search'])->name('raw-materials.search');
        Route::get('/raw-materials/filtrar', [RawMaterialController::class, 'filtrar'])->name('raw-materials.filtrar');

        Route::get('/finished-products/filtro', [FinishedProductController::class, 'filtro'])->name('finished-products.filtro');
        Route::get('/buscar-staff/filtro', [StaffController::class, 'filtro'])->name('buscar-staff.filtro');
        Route::get('/buscar-producto/filtro', [ProductController::class, 'filtro'])->name('buscar-producto.filtro');
        Route::get('/buscar-suppliers/filtro', [SupplierController::class, 'filtro'])->name('buscar-suppliers.filtro');
        Route::get('/buscar-users/filtro', [UsuarioController::class, 'filtro'])->name('buscar-users.filtro');
    });

    Route::post('/store-expensecash', [ExpenseController::class, 'storeExpenseCash'])->name('store.expensecash');
    //CRUD COMPRAS
    Route::get('/buscar-miscelaneo', [ProductController::class, 'buscarMiscelaneo'])->name('buscar.miscelaneo');

    // NUEVAS rutas para los PDFs con filtro de producto
    Route::get('purchases/excel', [PurchaseController::class, 'excel'])->name('purchases.excel');
    Route::get('/purchases/pdf/product', [PurchaseController::class, 'generatePDFProduct'])->name('purchases.pdfProduct');
    Route::get('/purchases/pdf/allproducts', [PurchaseController::class, 'generatePDFAllProducts'])->name('purchases.pdfAllProducts');
    Route::put('/{tipo}/{id}/estado', [PurchaseController::class, 'updateEstado'])->name('estado.update');
    Route::get('/registro/{tipo}/{id}/edit', [PurchaseController::class, 'edit'])->name('registro.edit');
    Route::put('/registro/{tipo}/{id}', [PurchaseController::class, 'update'])->name('registro.update');
    Route::get('/purchases/pdf_report', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
    Route::get('/purchases/pdf_report_general', [PurchaseController::class, 'pdf_general'])->name('purchases.pdfGeneral');
    Route::resource('purchases', PurchaseController::class);
    Route::get('/proveedor/{id}/productos', [PurchaseController::class, 'getProductosProveedor'])->name('proveedor.productos');
    Route::post('/purchases/saveproveedores', [PurchaseController::class, 'saveproveedores'])->name('savep');
    Route::post('/products/check', [ProductController::class, 'checkExists'])->name('products.check');


    //CAMBIAR SEDE
    Route::post('/cambiarSede', [UsuarioController::class, 'cambiarSede'])->name('user.cambiarSede');


    //rutas admin y sede--------------------------------------------

    Route::resource('transfers', TransferController::class);

    Route::get('/app', function () {
        return view('template.index');
    });

    Route::get('cashexpenses', [ExpenseController::class, 'expensecash'])->name('expenses.cash');
    Route::get('historycashexpenses', [ExpenseController::class, 'historyexpensecash'])->name('expenses.historycash');


    //INDICADORES
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

    //REPORTE DE TRANSFORMACION
    Route::get('transformations_report', [TransformationsReportController::class, 'index'])->name('transformations_report.index');

    //REPORTE DE TRANSLADO
    Route::get('transfers_report', [TransfersReportController::class, 'index'])->name('transfers_report.index');

    //REPORTE DE MERMA
    Route::get('waste_report', [WasteReportController::class, 'index'])->name('waste_report.index');

    //REPORTE DE RETOQUE
    Route::get('retouch_report', [RetouchReportController::class, 'index'])->name('retouch_report.index');

    //RETOQUE
    Route::get('retouch', [RetouchController::class, 'index'])->name('retouch.index');
    Route::post('retouch', [RetouchController::class, 'store'])->name('retouch.store');

    //MERMA
    Route::get('waste', [WasteController::class, 'index'])->name('waste.index');
    Route::post('waste', [WasteController::class, 'store'])->name('waste.store');

    //TRANSFORMACIONES
    Route::resource('transformations', TransformationController::class);

    //CUADRE DE STOCK
    Route::post('stock/guardar', [StockController::class, 'guardar'])->name('stock.guardar');
    Route::get('stock/inicial', [StockController::class, 'stockInicial'])->name('stock.inicial');
    Route::resource('stock', StockController::class);
    Route::get('/stock-inicial/{categoryId}', [StockMaterialController::class, 'stockAlmacen'])->name('stock.stockInicial');
    Route::post('/stock/guardar/{categoryId}', [StockMaterialController::class, 'stockAlmacenStore'])->name('stock.guardar');

    //CUADRE DE STOCK MATERIAL
    Route::post('stockMaterial/guardar', [StockMaterialController::class, 'guardar'])->name('stockMaterial.guardar');
    Route::get('stockMaterial/inicial', [StockMaterialController::class, 'stockInicial'])->name('stockMaterial.inicial');
    Route::resource('stockMaterial', StockMaterialController::class);

    //CRUD EGRESOS
    Route::resource('expenses', ExpenseController::class);

    Route::get('sales/detalles', [SaleController::class, 'detalleVentaAnticipada'])->name('sales.detalles');
    // POS
    Route::post('sales/registrar-pago', [SaleController::class, 'registrarPago'])->name('sales.registrar_pago');
    Route::get('sales/anticipated', [SaleController::class, 'anticipated'])->name('sales.anticipated');
    Route::post('/sales/entregar/{id}', [SaleController::class, 'confirmarEntrega'])->name('sales.entregar');
    Route::post('/sales/generar-comprobante', [SaleController::class, 'generarComprobanteAnticipado'])->name('sales.generar_comprobante');
    Route::get('/sales/validar-comprobante/{id}', [SaleController::class, 'verificarComprobante']);
    Route::get('/sunat/consultar', [SaleController::class, 'consultarSunat']);

    Route::post('anticipated_print', [SaleController::class, 'anticipated_print'])->name('anticipated_print');


    Route::post('/payment/update-method', [PaymentController::class, 'updateMethod'])->name('payment.updateMethod');

    Route::get('sales/pdfReport', [SaleController::class, 'pdfReporte'])->name('sales.pdfReport');
    Route::get('sales/getVoucherData', [SaleController::class, 'getVoucherData'])->name('sales.getVoucherData');
    Route::get('sales/historico', [SaleController::class, 'historico'])->name('sales.historico');
    Route::get('sales/delete', [SaleController::class, 'delete'])->name('sales.delete');
    Route::get('sales/details', [SaleController::class, 'details'])->name('sales.details');
    Route::post('sales/updateDetails', [SaleController::class, 'updateDetails'])->name('sales.updateDetails');
    Route::get('sales/restaurante', [SaleController::class, 'restaurante'])->name('sales.restaurante');
    Route::post('sales/subirFoto', [SaleController::class, 'subirFoto'])->name('sales.subirFoto');
    Route::get('sales/excel', [SaleController::class, 'excel'])->name('sales.excel');
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
    Route::get('sales/{sale}/pdf-detallado', [SaleController::class, 'pdfDetallado'])->name('sales.pdf_detallado');
    Route::resource('restaurante', RestauranteController::class);
    Route::post('/mesas/abrir/{id}', [SaleController::class, 'abrirMesa'])->name('mesas.abrir');
    Route::post('/orders/{orderId}/addproduct', [SaleController::class, 'addProductToOrder'])->name('orders.addProduct');
    Route::delete('/orders/{orderId}/removeproduct', [SaleController::class, 'removeProduct'])->name('orders.removeproduct');
    Route::post('/orders/confirm', [SaleController::class, 'confirmarPedido'])->name('orders.confirm');
    Route::post('/orders/preaccount', [SaleController::class, 'precuenta'])->name('orders.preaccount');
    Route::get('/ventas/anular', [SaleController::class, 'anular'])->name('sales.anular');



    Route::get('/mesas/pedido/{id}', [SaleController::class, 'verPedido'])->name('mesas.pedido');
    Route::post('/mesas/{id}/cerrar', [SaleController::class, 'cerrarMesa'])->name('mesas.cerrar');
    // Route::get('/ventas/ticket/{id}', [SaleController::class, 'ticketAnticipado'])->name('sales.ticket');

    //CRUD PRODUCCION
    Route::get('production/historicoDelivery', [ProductionController::class, 'historicoDelivery'])->name('production.historicoDelivery');
    Route::get('production/delivery', [ProductionController::class, 'delivery'])->name('production.delivery');
    Route::post('production/delivery', [ProductionController::class, 'storeDelivery'])->name('production.storeDelivery');
    Route::delete('production/delivery/{id}', [ProductionController::class, 'destroyDelivery'])->name('production.destroyDelivery');
    Route::get('production/historico', [ProductionController::class, 'historico'])->name('production.historico');
    Route::get('production/personalized', [ProductionController::class, 'personalized'])->name('production.personalized');
    Route::post('production/personalized', [ProductionController::class, 'storePersonalized'])->name('production.storePersonalized');
    Route::delete('production/personalized/{id}', [ProductionController::class, 'destroyPersonalized'])->name('production.destroyPersonalized');
    Route::get('production/dia', [ProductionController::class, 'sedeHoy'])->name('production.dia');
    Route::resource('production', ProductionController::class);

    //API
    Route::get('providers/api', [SupplierController::class, 'api'])->name('providers.api');
    Route::get('/productos/{id}/proveedores', [RawMaterialController::class, 'getSuppliers']);
    Route::get('/insumos/{id}/proveedores', [RawMaterialController::class, 'getSuppliersIn']);
    Route::get('/payments/pdf/agrupado', [PaymentController::class, 'pdfAgrupado'])->name('payment.pdfAgrupado');
    Route::get('/payments/pdf', [PaymentController::class, 'pdf'])->name('payment.pdf');
    Route::get('/payments/listar', [PaymentController::class, 'listar'])->name('payment.listar');
    Route::get('/payments/store', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/payments/index', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/cashClose', [PaymentController::class, 'cashClose'])->name('payment.cashClose');
    Route::get('/cashClose/historico', [PaymentController::class, 'cashCloseHistory'])->name('cashClose.historico');
    Route::post('/cashClose/pdf', [PaymentController::class, 'cashClosePDF'])->name('cashClose.pdf');
    Route::post('/storeCashClose', [PaymentController::class, 'storeCashClose'])->name('payment.storeCashClose');
    Route::post('/cambiarTurno', [UsuarioController::class, 'cambiarTurno'])->name('user.cambiarTurno');

    Route::get('/paloteo/pdf/{startDate}/{endDate}/{sede?}/{turno?}', [StockController::class, 'pdf'])->name('paloteo.pdf');
    Route::get('/paloteo/pdfGeneral/{startDate}/{endDate}/{sede?}/{turno?}', [StockController::class, 'pdfGeneral'])->name('paloteo.pdfGeneral');

    Route::get('/paloteoMaterial/pdf/{startDate}/{endDate}/{sede?}/{turno?}', [StockMaterialController::class, 'pdf'])->name('paloteoMaterial.pdf');
    Route::get('/paloteoMaterial/pdfGeneral/{startDate}/{endDate}', [StockMaterialController::class, 'pdfGeneral'])->name('paloteoMaterial.pdfGeneral');

    Route::get('/miscelaneo/filtrar', [MiscelaneoController::class, 'filtrar'])->name('miscelaneo.filtrar');
    Route::resource('miscelaneo', MiscelaneoController::class);

    Route::post('/arqueo/store', [PaymentController::class, 'arqueoStore'])->name('arqueo.store');

});
