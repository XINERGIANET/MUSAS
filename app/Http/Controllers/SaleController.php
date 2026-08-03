<?php

namespace App\Http\Controllers;

use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Headquarters;
use App\Models\Sale;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use App\Models\Storage3;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\Fpdf;
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Svg\Tag\Rect;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $userSede = Auth::user()->sede_id;

        $products = Product::active()
            ->whereIn('category_id', [2, 3])
            ->whereHas('productSede', function ($q) use ($userSede) {
                $q->where('estado', 0)->where('headquarter_id', $userSede);
            })
            ->with([
                'productSede' => function ($q) use ($userSede) {
                    $q->where('estado', 0)->where('headquarter_id', $userSede);
                },
                'storage3s' => function ($q) use ($userSede) {
                    $q->where('estado', 0)->where('headquarter_id', $userSede);
                }
            ])
            ->get();

        $processPanVarios = function ($collection) {
            $panVarios = $collection->first(function ($item) {
                return strtolower($item->nombre) === 'pan varios';
            });

            if ($panVarios) {
                $collection = $collection->filter(function ($item) use ($panVarios) {
                    return $item->id !== $panVarios->id;
                });

                $nombresYPrecios = [
                    'Pan 0.30' => 0.30,
                    'Pan 0.50' => 0.50,
                    'Pan 1'    => 1.00,
                    'Pan 2'    => 2.00,
                    'Pan 5'    => 5.00,
                ];

                foreach ($nombresYPrecios as $nombre => $precioFijo) {
                    $clon = clone $panVarios;
                    $clon->nombre = $nombre;
                    $clon->precio = $precioFijo;
                    $clon->precio_fijo = true;
                    $collection->push($clon);
                }
            }

            // ← AGREGAR ESTA LÍNEA para re-indexar la colección
            return $collection->values();
        };

        $products = $processPanVarios($products);

        // Precios por sede
        $productSitePrices = ProductPrice::where('estado', 0)->get();

        // Función para obtener precio según sede
        $getPrecioPorSede = function ($productId) use ($userSede, $productSitePrices) {
            $match = $productSitePrices->first(function ($item) use ($productId, $userSede) {
                return $item->product_id == $productId && $item->headquarter_id == $userSede;
            });

            return $match ? $match->unit_price : 0;
        };

        // Asignar precio_final y stock a productos
        $products->each(function ($p) use ($getPrecioPorSede) {
            $p->precio_fijo = $p->precio_fijo ?? false;
            $p->precio_final = $p->precio_fijo ? $p->precio : $getPrecioPorSede($p->id);
            
            // Agregar stock del almacén
            $p->stock_cantidad = $p->storage3s->sum('quantity');
        });

        // Obtener categorías con productos filtrados por sede
        $productCategory = ProductCategory::query()
            ->whereHas('productos', function ($q) use ($userSede) {
                $q->where('estado', 0)
                    ->whereHas('productSede', function ($q2) use ($userSede) {
                        $q2->where('estado', 0)
                            ->where('headquarter_id', $userSede);
                    });
            })
            ->with(['productos' => function ($q) use ($userSede) {
                $q->whereHas('productSede', function ($q2) use ($userSede) {
                    $q2->where('estado', 0)
                        ->where('headquarter_id', $userSede);
                })
                    ->with([
                        'productSede' => function ($q2) use ($userSede) {
                            $q2->where('estado', 0)
                                ->where('headquarter_id', $userSede);
                        },
                        'storage3s' => function ($q2) use ($userSede) {
                            $q2->where('estado', 0)->where('headquarter_id', $userSede);
                        }
                    ]);
            }])
            ->get();

        // Procesar Pan Varios + precios finales en cada categoría
        foreach ($productCategory as $category) {
            $category->setRelation('productos', $processPanVarios($category->productos));

            $category->productos->each(function ($p) use ($getPrecioPorSede) {
                $p->precio_fijo = $p->precio_fijo ?? false;
                $p->precio_final = $p->precio_fijo ? $p->precio : $getPrecioPorSede($p->id);
                
                // Agregar stock del almacén
                $p->stock_cantidad = $p->storage3s->sum('quantity');
            });
        }

        $paymentMethod = PaymentMethod::where('estado', 0)->get();
        $sedes = Headquarters::where('estado', 0)->get();

        return view('sales.index', compact('products', 'productSitePrices', 'productCategory', 'userSede', 'paymentMethod', 'sedes'));
    }



    public function guardarFoto($foto, $sale_id)
    {
        $disk = \Storage::disk('public');
        $dir = $disk->path('fotos');
        foreach (glob($dir . "/{$sale_id}.*") as $file) {
            @unlink($file);
        }

        $extension = $foto->getClientOriginalExtension();
        $filename = $sale_id . '.' . $extension;
        $path = $foto->storeAs('fotos', $filename, 'public');
        Sale::where('id', $sale_id)->update(['foto' => $path]);
        return $path;
    }


    public function store(Request $request)
    {   
        // Validaciones básicas antes de la transacción
        $validator = Validator::make($request->all(), [
            'type_sale' => 'required|numeric',
            'voucher_type' => 'required|string|in:Boleta,Factura,Ticket',
            'document'     => 'nullable|numeric',
            'client'       => 'nullable|string',
            'telefono'     => 'nullable|string|max:15',
            'sede_recojo'  => 'nullable|integer|exists:headquarters,id',
            'fecha'        => 'required|date',
            'total'        => 'required|numeric',
            'products'     => 'required',
            'monto'        => 'required|array',
            'fecha_entrega' => 'nullable|date',
            'direccion'    => 'nullable|string',
            'referencia'   => 'nullable|string',
            'observacion'  => 'nullable|string',
            'hora_entrega' => 'nullable|string',
            'status' => 'required|numeric',
            'foto' => 'nullable|mimes:jpg,jpeg,png,webp|max:4096',
        ]);


        // Validaciones condicionales
        $validator->sometimes('document', 'nullable|digits:8', function($r) { return $r->voucher_type === 'Boleta'; });
        $validator->sometimes('document', 'nullable|digits:11', function($r) { return $r->voucher_type === 'Factura'; });
        $validator->sometimes('client', 'required|string', function($r) { return $r->voucher_type === 'Factura'; });
        $validator->sometimes('direccion', 'nullable|string', function($r) { return $r->voucher_type === 'Factura'; });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error'  => $validator->errors()->first()
            ], 422);
        }

        try {
            usleep(rand(0, 10)*200000); // Espera aleatoria entre 0 y 2 segundos (en saltos de 0.2s)
            $response = DB::transaction(function () use ($request) {

                $documento = $request->document ?? null;
                $cliente_id = null;
                $cliente_nombre = "varios";
                $foto = $request->file('foto');

                if ($documento) {
                    $clienteEncontrado = Client::where('ruc_dni', $documento)->first();

                    if ($clienteEncontrado) {
                        $cliente_id = $clienteEncontrado->id;
                        $cliente_nombre = $clienteEncontrado->nombre;
                    } else {
                        $nuevoCliente = Client::create([
                            'ruc_dni' => $documento,
                            'nombre' => $request->client,
                            'estado' => 0
                        ]);
                        $cliente_id = $nuevoCliente->id;
                        $cliente_nombre = $nuevoCliente->nombre;
                    }
                } else {
                    // Si no hay documento pero el usuario ingresó un nombre, usar ese nombre
                    if ($request->client && trim($request->client) !== '') {
                        $cliente_nombre = $request->client;
                    }
                }

                $type_sale = $request->type_sale ?? null;
                $user_id   = auth()->user()->id; // Usar el usuario autenticado
                $status = $request->status ?? null;
                $fecha_entrega = $request->fecha_entrega ?? null;
                $direccion = $request->direccion ?? null;
                $referencia = $request->referencia ?? null;
                $observacion = $request->observacion ?? null;
                $telefono = $request->telefono ?? null;
                $sede_recojo = $request->sede_recojo ?? null;
                $hora_entrega = $request->hora_entrega ?? null;
                $total = floatval($request->total);
                $fecha = $request->fecha;
                $sede_id = auth()->user()->sede_id ?? null;
                $turno = auth()->user()->turno ?? null;
                $restaurant = $request->restaurant;
                $products = is_string($request->products) ? json_decode($request->products, true) : $request->products;

                $venta = Sale::create([
                    'type_sale'      => $type_sale,
                    'user_id'        => $user_id,
                    'voucher_type'   => $request->voucher_type,
                    'total'          => $total,
                    'fecha'          => $fecha,
                    'client_id'      => $cliente_id,
                    'cliente'        => $cliente_nombre,
                    'telefono'       => $telefono,
                    'sede_recojo'    => $sede_recojo,
                    'hora_entrega'   => $hora_entrega,
                    'fecha_entrega'  => $fecha_entrega,
                    'direccion'      => $direccion,
                    'referencia'     => $referencia,
                    'observacion'    => $observacion,
                    'headquarter_id' => $sede_id,
                    'turno'          => $turno,
                    'estado'         => 0,
                    'restaurant'     => $restaurant,
                    'status'         => $status,
                ]);

                $sale_id = $venta->id;

                if ($foto != null) {
                    $path = $this->guardarFoto($foto, $sale_id);
                }

                foreach ($request->monto as $metodo_id => $monto) {
                    if ($monto !== null && $monto !== '' && floatval($monto) != 0) {
                        Payment::create([
                            'sale_id'           => $venta->id,
                            'payment_method_id' => $metodo_id,
                            'fecha'             => $fecha,
                            'turno'             => auth()->user()->turno,   
                            'estado'            => 0,
                            'user_id'           => auth()->user()->id,
                            'monto'             => floatval($monto),
                        ]);
                    }
                }
                // Guardar detalles de la venta
                $productosParaAgrupar = [238]; // Reemplaza con los IDs reales de los panes

                // Separar productos que se agrupan vs productos individuales
                $productosAgrupados = [];
                $productosIndividuales = [];

                foreach ($products as $product) {
                    $id = $product['id'];
                    $cantidad = floatval($product['cantidad']);
                    $precio = floatval($product['precio']);
                    $subtotal = $cantidad * $precio;

                    if (in_array($id, $productosParaAgrupar)) {
                        // Agrupar productos específicos (panes) - SOLO SUMAR SUBTOTALES
                        if (!isset($productosAgrupados[$id])) {
                            $productosAgrupados[$id] = [
                                'product_id' => $id,
                                'total_subtotal' => 0,
                            ];
                        }

                        $productosAgrupados[$id]['total_subtotal'] += $subtotal;
                    } else {
                        // Mantener productos individuales sin agrupar
                        $productosIndividuales[] = [
                            'product_id' => $id,
                            'sale_id'    => $venta->id,
                            'quantity'   => $cantidad,
                            'unit_price' => $precio,
                            'subtotal'   => $subtotal,
                            'estado'     => 0,
                        ];
                    }
                }

                // Crear detalles de venta para productos agrupados
                foreach ($productosAgrupados as $item) {
                    SaleDetail::create([
                        'product_id' => $item['product_id'],
                        'sale_id'    => $venta->id,
                        'quantity'   => 1, // Siempre 1 para productos agrupados
                        'unit_price' => $item['total_subtotal'], // El subtotal total como precio unitario
                        'subtotal'   => $item['total_subtotal'], // Mismo valor que unit_price
                        'estado'     => 0,
                    ]);
                }

                // Crear detalles de venta para productos individuales
                foreach ($productosIndividuales as $detalle) {
                    SaleDetail::create($detalle);
                }

                // REDUCIR STOCK: Solo para ventas normales (type_sale = 0), no para anticipadas
                if ($type_sale == 0) {
                    // Reducir stock para productos agrupados
                    foreach ($productosAgrupados as $item) {
                        $this->reducirStockProducto($item['product_id'], 1, $sede_id);
                    }
                    
                    // Reducir stock para productos individuales
                    foreach ($productosIndividuales as $detalle) {
                        $this->reducirStockProducto($detalle['product_id'], $detalle['quantity'], $sede_id);
                    }
                }

                // Si es Boleta o Factura, enviamos a SUNAT
                $pdf_url = null;
                $detraction_text = null;
                // En tu método store, después de crear la venta:
                if (in_array($request->voucher_type, ['Boleta', 'Factura'])) {
                    $sunatResponse = $this->sendInvoice($venta);

                    if (!$sunatResponse['status']) {
                        throw new \Exception('Error al enviar a SUNAT: ' . $sunatResponse['console']);
                    }

                    $pdf_url = $sunatResponse['pdf'];
                    $detraction_text = $sunatResponse['detraction_text'];
                } elseif ($request->voucher_type === 'Ticket') {
                    // Generar número correlativo interno para Ticket
                    $numeroInterno = $this->generarNumeroTicket();
                    $venta->update(['number' => $numeroInterno]);

                    // No hay PDF ni texto de detracción para Ticket
                    $pdf_url = null;
                    $detraction_text = null;
                }

                // ...dentro del método store...
                $metodos_pago = [];
                foreach ($request->monto as $metodo_id => $monto) {
                    if ($monto !== null && $monto !== '' && floatval($monto) != 0) {
                        $metodo = PaymentMethod::find($metodo_id);
                        $nombreMetodo = $metodo ? $metodo->nombre : 'Método';
                        $metodos_pago[] = [
                            'nombre' => $nombreMetodo,
                            'monto'  => floatval($monto),
                        ];
                    }
                }

                // Cargar la relación del usuario para la respuesta
                $venta->load('usuario');

                // Respuesta exitosa
                return response()->json([
                    'status'  => true,
                    'message' => 'Venta registrada correctamente.',
                    'sale_id' => $venta->id,
                    'venta'   => [
                        'id'            => $venta->id,
                        'user_id'       => $venta->user_id,
                        'usuario'       => $venta->usuario, // Incluir toda la información del usuario
                        'number'        => $venta->number,
                        'cliente'       => $cliente_nombre,
                        'documento'     => $documento ?? '-',
                        'fecha'         => $fecha,
                        'fecha_entrega' => $fecha_entrega ?? '-',
                        'direccion'     => $direccion ?? '-',
                        'productos'     => $products,
                        'total'         => $total,
                        'metodos_pago'  => $metodos_pago, // <-- aquí el array correcto
                        'pagado'        => collect($request->monto)->sum(),
                    ],
                    'pdf'            => $pdf_url,
                    'detraction_text' => $detraction_text,
                ], 201);
            });

            return $response;
        } catch (\Throwable $e) {
            Log::error('❌ Error en store(): ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error'  => 'Error al registrar venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generarNumeroTicket()
    {
        // Usa transacción para evitar conflictos en concurrencia
        return DB::transaction(function () {
            // Bloquea la fila para actualizar el número
            $registro = DB::table('correlativos')->where('tipo', 'Ticket')->lockForUpdate()->first();

            if (!$registro) {
                // Si no existe registro, crea uno
                DB::table('correlativos')->insert([
                    'tipo' => 'Ticket',
                    'numero' => 1
                ]);
                return 'TICKET-00000001';
            }

            $nuevoNumero = $registro->numero + 1;

            DB::table('correlativos')
                ->where('tipo', 'Ticket')
                ->update(['numero' => $nuevoNumero]);

            // Formatea el número con ceros a la izquierda y prefijo
            return 'TICKET-' . str_pad($nuevoNumero, 8, '0', STR_PAD_LEFT);
        });
    }


    public function sendInvoice(Sale $sale)
    {
        $url = config('apisunat.url') . '/personas/lastDocument';
        $personaId = config('apisunat.id');
        $personaToken = config('apisunat.token.prod');

        $catalog = [
            'Boleta' => [
                'InvoiceTypeCode' => '03',
                'PartyIdentification' => '1',
                'serie' => 'B001'
            ],
            'Factura' => [
                'InvoiceTypeCode' => '01',
                'PartyIdentification' => '6',
                'serie' => 'F001'
            ]
        ];

        if (!isset($catalog[$sale->voucher_type])) {
            return [
                'status' => false,
                'console' => 'Tipo de comprobante no soportado para envío a SUNAT.'
            ];
        }

        $cat = $catalog[$sale->voucher_type];

        // Datos del emisor (tu empresa)
        $ruc = config('ruc.number');
        $name = 'MUSAS PASTELERIA S.R.L.';
        $address = 'AV. JOSE BALTA NRO. 054 P.J. CHINO ZAMORA CHICLAYO CHICLAYO LAMBAYEQUE';

        $client = optional($sale->client);

        $type = $cat['InvoiceTypeCode'];
        $serie = $cat['serie'];

        // Consultar último correlativo SUNAT
        $respUltimo = Http::post($url, [
            'personaId' => $personaId,
            'personaToken' => $personaToken,
            'type' => $type,
            'serie' => $serie
        ]);

        if ($respUltimo->failed()) {
            return [
                'status' => false,
                'console' => 'Error al consultar último correlativo: ' . $respUltimo->body()
            ];
        }

        $responseObj = $respUltimo->object();
        $number = trim($responseObj->suggestedNumber ?? '');

        if (!$number || !is_numeric($number)) {
            return [
                'status' => false,
                'console' => 'No se recibió correlativo válido desde SUNAT.'
            ];
        }

        $number = str_pad($number, 8, "0", STR_PAD_LEFT);

        // Cálculo de montos
        $total = round(floatval($sale->total), 2);
        $subtotal = round($total / 1.18, 2); // IGV 18% en Perú
        $igv = round($total - $subtotal, 2);

        $data = [
            'personaId' => $personaId,
            'personaToken' => $personaToken,
            'fileName' => "{$ruc}-{$type}-{$serie}-{$number}",
            'documentBody' => [
                'cbc:UBLVersionID' => ['_text' => '2.1'],
                'cbc:CustomizationID' => ['_text' => '2.0'],
                'cbc:ID' => ['_text' => "{$serie}-{$number}"],
                'cbc:IssueDate' => [
                    '_text' => now()->format('Y-m-d')
                ],
                'cbc:IssueTime' => [
                    '_text' => now()->format('H:i:s')
                ],
                'cbc:InvoiceTypeCode' => [
                    '_attributes' => ['listID' => '0101'],
                    '_text' => $type
                ],
                'cbc:Note' => [],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:AccountingSupplierParty' => [
                    'cac:Party' => [
                        'cac:PartyIdentification' => [
                            'cbc:ID' => [
                                '_attributes' => ['schemeID' => '6'],
                                '_text' => $ruc
                            ]
                        ],
                        'cac:PartyLegalEntity' => [
                            'cbc:RegistrationName' => ['_text' => $name],
                            'cac:RegistrationAddress' => [
                                'cbc:AddressTypeCode' => ['_text' => '0000'],
                                'cac:AddressLine' => ['cbc:Line' => ['_text' => $address]]
                            ]
                        ]
                    ]
                ],
                'cac:AccountingCustomerParty' => [
                    'cac:Party' => [
                        'cac:PartyIdentification' => [
                            'cbc:ID' => [
                                '_attributes' => ['schemeID' => $cat['PartyIdentification']],
                                '_text' => $client->ruc_dni ?? '00000000'
                            ]
                        ],
                        'cac:PartyLegalEntity' => [
                            'cbc:RegistrationName' => ['_text' => $client->nombre ?? 'CLIENTE VARIOS']
                        ]
                    ]
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $igv
                    ],
                    'cac:TaxSubtotal' => [
                        'cbc:TaxableAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $subtotal
                        ],
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igv
                        ],
                        'cac:TaxCategory' => [
                            'cac:TaxScheme' => [
                                'cbc:ID' => ['_text' => '1000'],
                                'cbc:Name' => ['_text' => 'IGV'],
                                'cbc:TaxTypeCode' => ['_text' => 'VAT']
                            ]
                        ]
                    ]
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotal
                    ],
                    'cbc:TaxInclusiveAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $total
                    ],
                    'cbc:AllowanceTotalAmount' => [],
                    'cbc:PayableAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $total
                    ]
                ],
                'cac:InvoiceLine' => [],
            ]
        ];

        // Manejo de términos de pago para Facturas
        if ($sale->voucher_type == 'Factura') {
            // Siempre establecer como "Contado"
            $data['documentBody']['cac:PaymentTerms'] = [[
                "cbc:ID" => ["_text" => "FormaPago"],
                "cbc:PaymentMeansID" => ["_text" => "Contado"]
            ]];
        }

        // Detracción para factura > S/700
        $detraction_text = '';
        if ($sale->voucher_type == 'Factura' && $total >= 700) {
            $detraction = round($total * 0.12, 2);
            $detraction_text = "Detracción: Nro. Cta. Banco de la Nación: 00-250-053223, Porcentaje: 12.00, Monto: S/{$detraction}";

            $data['documentBody']['cbc:InvoiceTypeCode']['_attributes']['listID'] = '1001';
            $data['documentBody']['cbc:Note'][] = [
                '_text' => 'OPERACIÓN SUJETA A DETRACCIÓN',
                '_attributes' => ['languageLocaleID' => '2006']
            ];
            $data['documentBody']['cac:PaymentTerms'][] = [
                'cbc:ID' => ['_text' => 'Detraccion'],
                'cbc:PaymentMeansID' => ['_text' => '022'],
                'cbc:PaymentPercent' => ['_text' => '12'],
                'cbc:Amount' => [
                    '_attributes' => ['currencyID' => 'PEN'],
                    '_text' => $detraction
                ]
            ];
            $data['documentBody']['cac:PaymentMeans'][] = [
                'cbc:ID' => ['_text' => 'Detraccion'],
                'cbc:PaymentMeansCode' => ['_text' => '001'],
                'cac:PayeeFinancialAccount' => [
                    'cbc:ID' => ['_text' => '00250053223']
                ]
            ];
        }

        // Detalle de productos (InvoiceLine) - Adaptado a tu estructura
        $details = $sale->details()->where('unit_price', '>', 0)->get();

        if ($details->isEmpty()) {
            // Si no hay detalles específicos, crear una línea general
            $data['documentBody']['cac:InvoiceLine'][] = [
                'cbc:ID' => ['_text' => 1],
                'cbc:InvoicedQuantity' => [
                    '_attributes' => ['unitCode' => 'NIU'],
                    '_text' => 1
                ],
                'cbc:LineExtensionAmount' => [
                    '_attributes' => ['currencyID' => 'PEN'],
                    '_text' => $subtotal
                ],
                'cac:PricingReference' => [
                    'cac:AlternativeConditionPrice' => [
                        'cbc:PriceAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $total
                        ],
                        'cbc:PriceTypeCode' => ['_text' => '01']
                    ]
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $igv
                    ],
                    'cac:TaxSubtotal' => [
                        'cbc:TaxableAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $subtotal
                        ],
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igv
                        ],
                        'cac:TaxCategory' => [
                            'cbc:Percent' => ['_text' => 18],
                            'cbc:TaxExemptionReasonCode' => ['_text' => '10'],
                            'cac:TaxScheme' => [
                                'cbc:ID' => ['_text' => '1000'],
                                'cbc:Name' => ['_text' => 'IGV'],
                                'cbc:TaxTypeCode' => ['_text' => 'VAT']
                            ]
                        ]
                    ]
                ],
                'cac:Item' => [
                    'cbc:Description' => ['_text' => 'Venta general']
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotal
                    ]
                ]
            ];
        } else {
            // Usar los detalles específicos de la venta
            $i = 1;
            foreach ($details as $detail) {
                $price = round($detail->unit_price, 2);
                $cost = round($price / 1.18, 2); // Precio sin IGV
                $quantity = $detail->quantity;
                $totalLine = round($price * $quantity, 2);
                $subtotalLine = round($totalLine / 1.18, 2);
                $igvLine = round($totalLine - $subtotalLine, 2);

                $data['documentBody']['cac:InvoiceLine'][] = [
                    'cbc:ID' => ['_text' => $i],
                    'cbc:InvoicedQuantity' => [
                        '_attributes' => ['unitCode' => 'NIU'],
                        '_text' => $quantity
                    ],
                    'cbc:LineExtensionAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotalLine
                    ],
                    'cac:PricingReference' => [
                        'cac:AlternativeConditionPrice' => [
                            'cbc:PriceAmount' => [
                                '_attributes' => ['currencyID' => 'PEN'],
                                '_text' => $price
                            ],
                            'cbc:PriceTypeCode' => ['_text' => '01']
                        ]
                    ],
                    'cac:TaxTotal' => [
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igvLine
                        ],
                        'cac:TaxSubtotal' => [
                            [
                                'cbc:TaxableAmount' => [
                                    '_attributes' => ['currencyID' => 'PEN'],
                                    '_text' => $subtotalLine
                                ],
                                'cbc:TaxAmount' => [
                                    '_attributes' => ['currencyID' => 'PEN'],
                                    '_text' => $igvLine
                                ],
                                'cac:TaxCategory' => [
                                    'cbc:Percent' => ['_text' => 18],
                                    'cbc:TaxExemptionReasonCode' => ['_text' => '10'],
                                    'cac:TaxScheme' => [
                                        'cbc:ID' => ['_text' => '1000'],
                                        'cbc:Name' => ['_text' => 'IGV'],
                                        'cbc:TaxTypeCode' => ['_text' => 'VAT']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'cac:Item' => [
                        'cbc:Description' => ['_text' => optional($detail->product)->nombre ?? 'Producto']
                    ],
                    'cac:Price' => [
                        'cbc:PriceAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $cost
                        ]
                    ]
                ];

                $i++;
            }
        }

        // Enviar a SUNAT
        $urlSend = config('apisunat.url') . '/personas/v1/sendBill';
        $source = Http::post($urlSend, $data);
        $response = $source->object();

        if ($source->failed()) {
            return [
                'status' => false,
                'console' => $response->error->message ?? 'Error desconocido al enviar a SUNAT'
            ];
        }

        $documentId = $response->documentId;
        $filename = "{$ruc}-{$type}-{$serie}-{$number}";

        $url = config('apisunat.url') . "/documents/{$documentId}/getPDF/ticket80mm/{$filename}.pdf";

        // Actualizar la venta con los datos de SUNAT
        $sale->update([
            'voucher_id' => $documentId,
            'voucher_file' => $filename . '.pdf',
            'number' => "{$serie}-{$number}"
        ]);

        return [
            'status' => true,
            'pdf' => $url,
            'detraction_text' => $detraction_text
        ];
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $venta = Sale::with([
            'client:id,nombre,ruc_dni',
            'details.product:id,nombre',
            'payments.paymentMethod:id,nombre',
            'headquarter:id,nombre'
        ])->findOrFail($id);

        return response()->json($venta);
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->estado = 1;
        $sale->save();

        if($sale->voucher_type == 'Boleta' || $sale->voucher_type == 'Factura'){
            $voidResponse = $this->void(request(), $sale);

            // Si la respuesta es un JSON response, obtén el contenido
            $voidData = $voidResponse->getData();

            if (isset($voidData->status) && $voidData->status === false) {
                return response()->json([
                    'status' => false,
                    'error' => 'La venta se eliminó, pero no se pudo enviar a sunat (anulación)',
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Venta eliminada correctamente.'
        ]);
    }

    public function anticipated(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $sede = $request->headquarter_id;
        $number = $request->number;
        $client = $request->client;
        $turno = $request->turno;

        // Obtener las sedes activas
        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', 0)
            ->orderBy('nombre')
            ->get();

        // Consulta principal de ventas anticipadas - mostrar todas las anticipadas
        $consulta = Sale::with('client', 'details', 'payments', 'sedeRecojo')
            ->whereNotNull('fecha_entrega') // Solo ventas anticipadas
            ->where('estado', 0)
            ->where('status', 1) //no entregada, si está entregada pasa al histórico
            ->when($number, function ($query) use ($number) {
                $query->where('number', 'like', '%'.$number.'%');
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('fecha_entrega', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('fecha_entrega', '<=', $end_date);
            })
            ->when($sede, function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            ->when($client, function ($query) use ($client) {
                $query->where('client_id', $client);
            })
            ->when(isset($turno), function ($query) use ($turno) {
                $query->where('turno', $turno);
            })
            ->orderBy('fecha', 'desc');

        $anticipadas = $consulta->get();

        $paymentMethod = PaymentMethod::where('estado', 0)->get();

        // Obtener productos activos para el modal de edición
        $products = Product::active()
            ->with(['storage3s' => function ($q) {
                $q->where('estado', 0);
            }])
            ->where('estado', 0)
            ->where('category_id', 3)
            ->orderBy('nombre')
            ->get();

        return view('sales.anticipated', compact('sedes', 'anticipadas', 'paymentMethod', 'products'));
    }


   public function historico(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('Xinergia');

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $numero_comprobante = $request->input('numero_comprobante');
        $nombre_cliente = $request->input('nombre_cliente');
        $voucher_type = $request->input('voucher_type');
        $turno = $request->input('turno');
        $payment_method_id = $request->input('payment_method_id');
        $type_sale = $request->input('type_sale');
        $filtrarRestaurante = $type_sale === 'restaurant';
        $user_id = $request->input('user_id');

        $type_sale_arr = explode('-', $type_sale);
        $type_sale_arr = array_filter($type_sale_arr, fn($v) => $v !== '' && $v !== 'restaurant');

        $sede = $request->filled('headquarter_id')
            ? $request->input('headquarter_id')
            : (!$isAdmin ? ($user->headquarter->id ?? null) : null);

        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', 0)
            ->orderBy('nombre')
            ->get();

        $users = Usuario::select('id', 'email')
            ->where('activo', 1)
            ->orderBy('email')
            ->get();

        $paymentMethod = PaymentMethod::where('estado', 0)->get();

        $consulta = Sale::query()
            ->where('estado', 0)
            ->when($start_date, fn($q) => $q->whereDate('fecha', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('fecha', '<=', $end_date))
            ->when($sede, fn($q) => $q->where('headquarter_id', $sede))
            ->when($filtrarRestaurante, fn($q) => $q->where('restaurant', 1)->where('type_sale', '0000'))
            ->when(!$filtrarRestaurante && $type_sale_arr, fn($q) => $q->whereIn('type_sale', $type_sale_arr))
            ->when($user_id, fn($q) => $q->where('user_id', $user_id))
            ->when($numero_comprobante, fn($q) => $q->where('number', 'like', "%$numero_comprobante%"))
            ->when($nombre_cliente, fn($q) => $q->where('cliente', 'like', "%$nombre_cliente%"))
            ->when($voucher_type, fn($q) => $q->where('voucher_type', $voucher_type))
            ->when(isset($turno), fn($q) => $q->where('turno', $turno))
            ->when($payment_method_id, function ($q) use ($payment_method_id) {
                $q->whereHas('payments', fn($q2) => $q2->where('payment_method_id', $payment_method_id));
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        $total = $consulta->sum('total');

        $total_pagos = Payment::query()
            ->where('estado', 0)
            ->when($start_date, fn($q) => $q->whereDate('fecha', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('fecha', '<=', $end_date))
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', $turno))
            ->when($payment_method_id, fn($q) => $q->where('payment_method_id', $payment_method_id))
            ->when($user_id, fn($q) => $q->where('user_id', $user_id))
            ->whereHas('sale', function ($q) use ($sede, $type_sale_arr, $filtrarRestaurante, $numero_comprobante, $nombre_cliente, $voucher_type, $user_id) {
                $q->when($sede, fn($q2) => $q2->where('headquarter_id', $sede))
                ->when($filtrarRestaurante, fn($q2) => $q2->where('restaurant', 1)->where('type_sale', '0000'))
                ->when(!$filtrarRestaurante && $type_sale_arr, fn($q2) => $q2->whereIn('type_sale', $type_sale_arr))
                ->when($numero_comprobante, fn($q2) => $q2->where('number', 'like', "%$numero_comprobante%"))
                ->when($nombre_cliente, fn($q2) => $q2->where('cliente', 'like', "%$nombre_cliente%"))
                ->when($voucher_type, fn($q2) => $q2->where('voucher_type', $voucher_type));
            })
            ->sum('monto');

        $anticipadas = $consulta->paginate(15);
        $anticipadas->appends($request->all());

        return view('sales.historico', compact(
            'sedes', 'anticipadas', 'sede', 'users', 'user_id',
            'start_date', 'end_date', 'isAdmin', 'paymentMethod',
            'voucher_type', 'type_sale', 'turno', 'total', 'total_pagos',
            'payment_method_id'
        ));
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('Xinergia');
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Si se ha enviado un filtro, úsalo. Si no, y no es admin, usa su sede automáticamente
        $sede = $request->filled('sede_id')
            ? $request->sede_id
            : (!$isAdmin ? ($user->headquarter->id ?? null) : null);

        // Lista de sedes
        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', 0)
            ->orderBy('nombre')
            ->get();

        $consulta = Sale::query()
            ->where('estado', 1)
            ->when($start_date, fn($query) => $query->whereDate('fecha', '>=', $start_date))
            ->when($end_date, fn($query) => $query->whereDate('fecha', '<=', $end_date))
            ->when($sede, fn($query) => $query->where('headquarter_id', $sede))
            ->orderBy("fecha", "desc")
            ->orderBy("id", "desc");

        $anticipadas = $consulta->paginate(10);

        return view('sales.delete', compact('sedes', 'anticipadas', 'sede', 'start_date', 'end_date', 'isAdmin'));
    }

    public function anular(Request $request)
    {
        try {
            $sale_id = $request->sale_id;

            // 1. Buscar la venta
            $venta = Sale::findOrFail($sale_id);

            if ($venta->estado !== 0) {
                return response()->json([
                    'status' => false,
                    'error' => 'La venta ya fue anulada anteriormente.'
                ]);
            }

            DB::transaction(function () use ($venta) {
                // 2. Cambiar estado en tabla SALES
                $venta->estado = 1;
                $venta->save();

                // 3. Cambiar estado en tabla PAYMENTS asociados a esa venta
                Payment::where('sale_id', $venta->id)
                    ->where('estado', 0)
                    ->update(['estado' => 1]);

                // 4. Obtener productos y restaurar stock
                $detalles = SaleDetail::where('sale_id', $venta->id)->get();

                foreach ($detalles as $detalle) {
                    $this->restaurarStockProducto(
                        $detalle->product_id,
                        $detalle->quantity,
                        $venta->headquarter_id
                    );
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Venta anulada, stock restaurado y pagos desactivados correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al anular venta: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'error' => 'Error inesperado al anular la venta: ' . $e->getMessage()
            ]);
        }
    }


    private function restaurarStockProducto($productId, $cantidadRestaurar, $sedeId)
    {
        $stockRecords = Storage3::where('product_id', $productId)
            ->where('headquarter_id', $sedeId)
            ->where('estado', 0)
            ->orderBy('id', 'asc')
            ->get();

        if ($stockRecords->isNotEmpty()) {
            // Aumentar cantidad en el primer registro
            $stock = $stockRecords->first();
            $stock->quantity += $cantidadRestaurar;
            $stock->save();
        } else {
            // Si no hay registro existente, crear uno nuevo
            Storage3::create([
                'product_id' => $productId,
                'headquarter_id' => $sedeId,
                'quantity' => $cantidadRestaurar,
                'estado' => 0
            ]);
        }
    }


    public function details(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            
            // Obtener la venta con todas sus relaciones
            $sale = Sale::with([
                'client', 
                'headquarter', 
                'sedeRecojo',
                'details.product',
                'payments.paymentMethod'
            ])->findOrFail($sale_id);
            
            // Mapear los productos
            $productos = $sale->details->map(function ($detail) {
                return [
                    'id' => $detail->product_id,
                    'nombre' => $detail->product->nombre ?? 'Producto',
                    'precio' => round($detail->unit_price, 2),
                    'cantidad' => round($detail->quantity, 2),
                    'subtotal' => round($detail->subtotal, 2),
                ];
            });

            // Mapear los pagos
            $pagos = $sale->payments->map(function ($payment) {
                return [
                    'metodo_pago' => $payment->paymentMethod->nombre ?? 'N/A',
                    'monto' => round($payment->monto, 2),
                    'fecha' => $payment->created_at->format('d/m/Y H:i'),
                ];
            });

            // Información de la venta
            $ventaInfo = [
                'id' => $sale->id,
                'fecha' => $sale->fecha->format('d/m/Y H:i:s'),
                'cliente' => $sale->client->nombre ?? $sale->cliente ?? 'Varios',
                'fecha_entrega' => $sale->fecha_entrega,
                'hora_entrega' => $sale->hora_entrega,
                'sede' => $sale->sedeRecojo->nombre ?? '-',
                'sede_id' => $sale->sede_recojo,
                'direccion' => $sale->direccion,
                'referencia' => $sale->referencia,
                'observacion' => $sale->observacion,
                'total' => round($sale->total, 2),
                'saldo' => round($sale->saldo(), 2),
                'telefono' => $sale->telefono,
                'voucher_type' => $sale->voucher_type,
                'number' => $sale->number,
            ];

            // Retorna los detalles en formato JSON
            return response()->json([
                'status' => true,
                'productos' => $productos,
                'pagos' => $pagos,
                'venta' => $ventaInfo,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al obtener detalles de venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateDetails(Request $request)
    {
        try {
            // Si productos viene como JSON string, decodificarlo
            $productos = $request->productos;
            if (is_string($productos)) {
                $productos = json_decode($productos, true);
            }

            // Debug: Log de los datos recibidos
            Log::info('Datos recibidos en updateDetails:', [
                'productos' => $productos,
                'sale_id' => $request->sale_id,
                'sede_id' => $request->sede_id,
                'sede_id_type' => gettype($request->sede_id),
                'sede_id_empty' => empty($request->sede_id),
                'telefono' => $request->telefono,
                'fecha_entrega' => $request->fecha_entrega,
                'hora_entrega' => $request->hora_entrega,
                'total' => $request->total,
                'total_type' => gettype($request->total),
                'total_empty' => empty($request->total),
                'has_foto' => $request->hasFile('foto'),
                'all_request' => $request->all()
            ]);

            // Crear una nueva instancia de request con productos decodificados
            $requestData = $request->all();
            $requestData['productos'] = $productos;
            $request->merge($requestData);

            $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'telefono' => 'nullable|string|max:20',
                'sede_id' => 'nullable|integer|exists:headquarters,id',
                'fecha_entrega' => 'nullable|date',
                'hora_entrega' => 'nullable|string|max:40',
                'direccion' => 'nullable|string|max:255',
                'referencia' => 'nullable|string|max:255',
                'observacion' => 'nullable|string|max:500',
                'productos' => 'required|array',
                'productos.*.id' => 'required|exists:products,id,estado,0',
                'productos.*.precio' => 'required|numeric|min:0',
                'productos.*.cantidad' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0.01',
                'foto' => 'nullable|mimes:jpg,jpeg,png,webp|max:4096'
            ]);

            DB::beginTransaction();

            $sale = Sale::findOrFail($request->sale_id);

            // Manejar la foto si se proporciona
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $this->guardarFoto($foto, $sale->id);
            }

            // Actualizar los campos de la venta
            $sale->update([
                'telefono' => $request->telefono,
                'sede_recojo' => $request->filled('sede_id') ? (int) $request->sede_id : null,
                'fecha_entrega' => $request->fecha_entrega,
                'hora_entrega' => $request->hora_entrega,
                'direccion' => $request->direccion,
                'referencia' => $request->referencia,
                'observacion' => $request->observacion,
                'total' => (float) $request->total
            ]);

            // Debug: Log después de actualizar
            Log::info('Sale actualizada:', [
                'sale_id' => $sale->id,
                'sede_recojo' => $sale->sede_recojo,
                'telefono' => $sale->telefono,
                'fecha_entrega' => $sale->fecha_entrega,
                'hora_entrega' => $sale->hora_entrega,
                'total' => $sale->total,
                'total_original' => $request->total,
                'total_type' => gettype($request->total)
            ]);

            // Eliminar detalles existentes
            $sale->details()->delete();

            // Crear nuevos detalles
            foreach ($productos as $producto) {
                $sale->details()->create([
                    'product_id' => $producto['id'],
                    'unit_price' => $producto['precio'],
                    'quantity' => $producto['cantidad'],
                    'subtotal' => $producto['precio'] * $producto['cantidad']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detalles actualizados correctamente',
                'sale' => $sale
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function subirFoto(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            $request->validate([
                'foto' => 'required|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $foto = $request->file('foto');

            $path = $this->guardarFoto($foto, $sale_id);

            return response()->json(['path' => $path, 'success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar foto: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function restaurante()
    {
        // 4) Sede del usuario logueado
        $userSede = Auth::user()->sede_id;
        // 1) Productos activos por sede, filtrados por categoría
        $products = Product::active()
            ->whereIn('category_id', [2, 3])
            ->whereHas('productSede', function ($q) use ($userSede) {
                $q->where('estado', 0)->where('headquarter_id', $userSede);
            })
            ->with([
                'productSede' => function ($q) use ($userSede) {
                    $q->where('estado', 0)->where('headquarter_id', $userSede);
                },
                'storage3s' => function ($q) use ($userSede) {
                    $q->where('estado', 0)
                        ->where('headquarter_id', $userSede);
                }
            ])
            ->get();


        $processPanVarios = function ($collection) {
            $panVarios = $collection->first(function ($item) {
                return strtolower($item->nombre) === 'pan varios';
            });

            if ($panVarios) {
                $collection = $collection->filter(function ($item) use ($panVarios) {
                    return $item->id !== $panVarios->id;
                });

                $nombresYPrecios = [
                    'Pan 0.30' => 0.30,
                    'Pan 0.50' => 0.50,
                    'Pan 1'    => 1.00,
                    'Pan 2'    => 2.00,
                    'Pan 5'    => 5.00,
                ];

                foreach ($nombresYPrecios as $nombre => $precioFijo) {
                    $clon = clone $panVarios;
                    $clon->nombre = $nombre;
                    $clon->precio = $precioFijo;
                    $clon->precio_fijo = true;
                    $collection->push($clon);
                }
            }

            // ← AGREGAR ESTA LÍNEA para re-indexar la colección
            return $collection->values();
        };

        $products = $processPanVarios($products);

        $productSitePrices = ProductPrice::where('estado', 0)->get();

        // Función para obtener precio según sede
        $getPrecioPorSede = function ($productId) use ($userSede, $productSitePrices) {
            $match = $productSitePrices->first(function ($item) use ($productId, $userSede) {
                return $item->product_id == $productId && $item->headquarter_id == $userSede;
            });

            return $match ? $match->unit_price : 0;
        };

        // Asignar precio_final y stock a productos
        $products->each(function ($p) use ($getPrecioPorSede) {
            $p->precio_fijo = $p->precio_fijo ?? false;
            $p->precio_final = $p->precio_fijo ? $p->precio : $getPrecioPorSede($p->id);
            
            // Agregar stock del almacén
            $p->stock_cantidad = $p->storage3s->sum('quantity');
        });


       // Obtener categorías con productos filtrados por sede
        $productCategory = ProductCategory::query()
            ->whereHas('productos', function ($q) use ($userSede) {
                $q->where('estado', 0)
                    ->whereHas('productSede', function ($q2) use ($userSede) {
                        $q2->where('estado', 0)
                            ->where('headquarter_id', $userSede);
                    });
            })
            ->with(['productos' => function ($q) use ($userSede) {
                $q->whereHas('productSede', function ($q2) use ($userSede) {
                    $q2->where('estado', 0)
                        ->where('headquarter_id', $userSede);
                })
                    ->with([
                        'productSede' => function ($q2) use ($userSede) {
                            $q2->where('estado', 0)
                                ->where('headquarter_id', $userSede);
                        },
                        'storage3s' => function ($q2) use ($userSede) {
                            $q2->where('estado', 0)->where('headquarter_id', $userSede);
                        }
                    ]);
            }])
            ->get();

        // Procesar Pan Varios + precios finales en cada categoría
        foreach ($productCategory as $category) {
            $category->setRelation('productos', $processPanVarios($category->productos));

            $category->productos->each(function ($p) use ($getPrecioPorSede) {
                $p->precio_fijo = $p->precio_fijo ?? false;
                $p->precio_final = $p->precio_fijo ? $p->precio : $getPrecioPorSede($p->id);
                
                // Agregar stock del almacén
                $p->stock_cantidad = $p->storage3s->sum('quantity');
            });
        }
        
        // 5) Métodos de pago
        $paymentMethod = PaymentMethod::where('estado', 0)->get();

        // 6) Mesas (solo para restaurante)
        $mesas = Table::all();

        // 7) Vista restaurante
        return view('sales.restaurante', compact(
            'mesas',
            'products',
            'productSitePrices',
            'productCategory',
            'userSede',
            'paymentMethod'
        ));
    }

    public function abrirMesa($id)
    {
        $mesa = Table::findOrFail($id);

        if ($mesa->status === 'libre') {
            $mesa->update([
                'status' => 'ocupada',
                'opened_at' => now(),
            ]);

            $order = Order::create([
                'table_id' => $mesa->id,
                'estado' => 'abierto'
            ]);
        } else {
            $order = $mesa->order; // en caso ya exista
        }

        return response()->json([
            'success' => true,
            'mesa_id' => $mesa->id,
            'opened_at' => $mesa->opened_at,
            'order_id' => $order->id ?? null,
            'mesa' => [
                'id' => $mesa->id,
                'name' => $mesa->name,
                'status' => $mesa->status
            ]
        ]);
    }

    public function addProductToOrder(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'product_id'      => 'required|integer|exists:products,id',
                'cantidad'        => 'required|numeric|min:0',
                'precio_unitario' => 'required|numeric|min:0',
                'nombre'          => 'nullable|string|max:100',
                'sumar'           => 'nullable|boolean',   // <- NUEVO
            ]);

            $order = Order::findOrFail($orderId);

            // Clave compuesta como en tu diseño (product_id + nombre opcional)
            $key = [
                'order_id'   => $orderId,
                'product_id' => (int) $validated['product_id'],
            ];
            if (!empty($validated['nombre'])) {
                $key['nombre'] = $validated['nombre'];
            } else {
                // si no usas nombre para este producto, asegúrate que sea null
                $key['nombre'] = null;
            }

            // Busca si ya existe el detalle
            $detail = OrderDetail::where($key)->first();

            $cantidadNueva   = (float) $validated['cantidad'];
            $precioUnitario  = (float) $validated['precio_unitario'];
            $sumar           = $request->boolean('sumar'); // true cuando es click en botón

            if ($sumar) {
                // SUMAR cantidad (clicks de botón)
                if ($detail) {
                    $detail->cantidad        = (float) $detail->cantidad + $cantidadNueva;
                    $detail->precio_unitario = $precioUnitario; // si quieres, actualiza el PU
                    $detail->save();
                } else {
                    $detail = OrderDetail::create([
                        'order_id'        => $orderId,
                        'product_id'      => (int) $validated['product_id'],
                        'nombre'          => $key['nombre'], // puede ser null
                        'cantidad'        => $cantidadNueva,
                        'precio_unitario' => $precioUnitario,
                    ]);
                }
            } else {
                // SOBREESCRIBIR cantidad (edición desde el input)
                $detail = OrderDetail::updateOrCreate(
                    $key,
                    [
                        'cantidad'        => $cantidadNueva,
                        'precio_unitario' => $precioUnitario,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $sumar ? 'Cantidad sumada correctamente' : 'Producto actualizado correctamente',
                'data'    => $detail,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al agregar producto al pedido: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function removeProduct(Request $request, $orderId)
    {
        try {
            $productId = $request->input('product_id');

            // Aquí tu lógica para eliminar el producto del pedido
            // Por ejemplo:
            OrderDetail::where('order_id', $orderId)
                ->where('product_id', $productId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verPedido($id)
    {
        $mesa = Table::with(['order.orderdetails.product'])->findOrFail($id);

        if (!$mesa->order) {
            return response()->json([
                'success' => false,
                'message' => 'No hay pedido abierto para esta mesa.'
            ], 404);
        }

       $productos = $mesa->order->orderdetails->map(function ($detalle) {
            $nombre = ($detalle->product_id == 238 && !empty($detalle->nombre))
                ? $detalle->nombre              
                : $detalle->product->nombre;   

            return [
                'id'         => $detalle->product_id,
                'nombre'     => $nombre,
                'cantidad'   => $detalle->cantidad,
                'precio'     => $detalle->precio_unitario,
                'confirmado' => $detalle->confirmado,
            ];
        });


        Log::info('Pedido cargado', [
            'mesa_id' => $id,
            'productos' => $productos
        ]);


        return response()->json([
            'success' => true,
            'order_id' => $mesa->order->id,
            'productos' => $productos
        ]);
    }

    public function cerrarMesa($id)
    {
        try {
            $mesa = Table::with('order.orderdetails')->findOrFail($id);

            if ($mesa->order) {
                // Eliminar detalles
                $mesa->order->orderdetails()->delete();

                // Eliminar la orden
                $mesa->order()->delete();
            }

            // Liberar mesa
            $mesa->update([
                'status' => 'libre',
                'opened_at' => null,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al cerrar mesa: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cerrar la mesa.']);
        }
    }

    public function confirmarPedido(Request $request)
    {
        try {
            $order_id = $request->order_id;
            $order = Order::where('id', $order_id)
                ->firstOrFail();
            $not_confirmed = $order->orderdetails()
                ->with('product')
                ->where('confirmado', 0) // Solo detalles no confirmados
                ->get();

            //Updatear productos confirmados y orden
            $order->orderdetails()
                ->where('confirmado', 0)
                ->update(['confirmado' => 1]);

            return response()->json(['success' => true,
                'status' => true,
                'table' => $order->table->name,
                'order_id' => $order->id,
                'details' => $not_confirmed->count() > 0 ? $not_confirmed : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cerrar mesa: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al confirmar pedidos.']);
        }
    }

    public function precuenta(Request $request)
    {
        try {
            $order_id = $request->order_id;
            $order = Order::where('id', $order_id)
                ->firstOrFail();

            $details = $order->orderdetails()
                ->with('product')
                ->get();

            return response()->json(['success' => true,
                'status' => true,
                'table' => $order->table->name,
                'order_id' => $order->id,
                'details' => $details->count() > 0 ? $details : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar precuenta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar precuenta.']);
        }
    }

    // public function ticketAnticipado($id)
    // {
    //     $venta = Sale::with(['client', 'details.product', 'payments'])->findOrFail($id);
    //     return view('sales.ticket', compact('venta'));
    // }

    public function pdf(Request $request, Sale $sale)
    {
        // Solo funciona para boletas y facturas
        if (!in_array($sale->voucher_type, ['Boleta', 'Factura'])) {
            abort(404, 'Este formato de PDF solo está disponible para Boletas y Facturas');
        }

        // Cargar relaciones necesarias
        $sale->load(['client', 'details.product', 'payments.paymentMethod', 'sedeRecojo']);

        // Detectar tipo de venta desde el modelo
        $tipoVenta  = intval($sale->type_sale); // 0 = directa, 1 = anticipada, 3 = delivery anticipada
        $anticipada = ($tipoVenta === 1);
        $delivery   = ($tipoVenta === 3); // Solo delivery anticipada

        $fpdf = new Fpdf('P', 'mm', [80, 297]);
        $fpdf->AddPage();
        $fpdf->AddFont('Courier', '');
        $fpdf->AddFont('Courier', 'B');
        $fpdf->SetMargins(3, 3, 3);
        $fpdf->SetFillColor(255, 255, 255);

        // Sin imagen logo para mantener el formato similar al de impresión
        $fpdf->Ln(5);
        $fpdf->SetFont('Courier', 'B', 10);

        // Cabecera similar al formato de impresión
        $fpdf->Cell(74, 3, 'MUSAS PASTELERIA S.R.L.', 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 8);
        $fpdf->Cell(74, 3, 'RUC: 20611061618', 0, 1, 'C');
        $fpdf->MultiCell(74, 3, 'AV. JOSE BALTA NRO. 054 P.J. CHINO ZAMORA CHICLAYO CHICLAYO LAMBAYEQUE', 0, 'C');
        $fpdf->Cell(74, 3, '================================', 0, 1, 'C');
        
        // Título del documento
        $fpdf->SetFont('Courier', 'B', 9);
        $tipoDocumento = $sale->voucher_type === 'Boleta' ? 'BOLETA DE VENTA ELECTRONICA' : 'FACTURA ELECTRONICA';
        $fpdf->Cell(74, 3, utf8_decode($tipoDocumento), 0, 1, 'C');
        $fpdf->Cell(74, 3, ($sale->number ?? ''), 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 8);
        
        // Información del cliente
        $clienteNombre = optional($sale->client)->nombre ?? $sale->cliente ?? 'VARIOS';
        $fpdf->Cell(74, 3, utf8_decode('NOMBRE: ' . $clienteNombre), 0, 1);
        
        $tipoDoc = $sale->voucher_type === 'Factura' ? 'RUC' : 'DNI';
        $numeroDoc = optional($sale->client)->ruc_dni ?? 'N/A';
        $fpdf->Cell(74, 3, utf8_decode($tipoDoc . ': ' . $numeroDoc), 0, 1);
        
        $fpdf->Cell(74, 3, utf8_decode('EMISION: ' . $sale->fecha), 0, 1);
        $fpdf->Cell(74, 3, 'MONEDA: SOL (PEN)', 0, 1);
        
        // Tipo de venta
        $tipo = '0000';
        if ($anticipada) {
            $tipo = '0001';
        } elseif ($tipoVenta === 3) {
            $tipo = '0003'; // Delivery anticipado
        }
        $fpdf->Cell(74, 3, utf8_decode('TIPO: ' . $tipo), 0, 1);

        // Formas de pago
        if ($sale->payments->count() > 0) {
            $fpdf->Cell(74, 3, 'FORMAS DE PAGO:', 0, 1);
            foreach ($sale->payments as $payment) {
                $nombreMetodo = $payment->paymentMethod->nombre ?? 'Método';
                $fpdf->Cell(74, 3, utf8_decode($nombreMetodo . ': S/ ' . number_format($payment->monto, 2)), 0, 1);
            }
        }

        // Información adicional para ventas anticipadas o delivery anticipadas
        if ($anticipada || $tipoVenta === 3) { // Incluir delivery anticipadas
            $fpdf->Cell(74, 3, utf8_decode('FECHA ENTREGA: ' . ($sale->fecha_entrega ?? 'No especificada')), 0, 1);
            
            // Calcular saldo pendiente para anticipadas y delivery anticipadas
            $totalPagado = $sale->payments->sum('monto');
            $saldoPendiente = $sale->total - $totalPagado;
            $fpdf->Cell(74, 3, utf8_decode('SALDO PENDIENTE: S/ ' . number_format($saldoPendiente, 2)), 0, 1);
            
            // Para delivery anticipado, mostrar hora de entrega
            if ($tipoVenta === 3 && $sale->hora_entrega) {
                $fpdf->Cell(74, 3, utf8_decode('HORA ENTREGA: ' . $sale->hora_entrega), 0, 1);
            }
        }

        $fpdf->Cell(74, 3, '--------------------------------', 0, 1);

        // Cabecera de productos (formato similar al de impresión)
        $fpdf->Cell(74, 3, 'CANT | DESCRIPCION | P.UNIT | TOTAL', 0, 1);
        $fpdf->Cell(74, 3, '--------------------------------', 0, 1);

        // Productos
        foreach ($sale->details as $detail) {
            $cantidad = number_format($detail->quantity, 2);
            $precio = number_format($detail->unit_price, 2);
            $total = number_format($detail->unit_price * $detail->quantity, 2);
            
            // Línea del producto
            $fpdf->Cell(74, 3, utf8_decode($cantidad . ' | ' . (optional($detail->product)->nombre ?? '')), 0, 1);
            $fpdf->Cell(74, 3, utf8_decode('     | S/ ' . $precio . ' | S/ ' . $total), 0, 1);
        }

        $fpdf->Cell(74, 3, '--------------------------------', 0, 1);

        // Totales
        if (floatval($sale->discount ?? 0) > 0) {
            $fpdf->Cell(74, 3, 'DESCUENTO: S/ ' . number_format($sale->discount, 2), 0, 1, 'R');
        }
        
        $subtotal = round($sale->total / 1.18, 2);
        $igv = round($sale->total - $subtotal, 2);
        $fpdf->Cell(74, 3, 'SUBTOTAL: S/ ' . number_format($subtotal, 2), 0, 1, 'R');
        $fpdf->Cell(74, 3, 'IGV (18%): S/ ' . number_format($igv, 2), 0, 1, 'R');
    
        
        $fpdf->SetFont('Courier', 'B', 8);
        $fpdf->Cell(74, 3, 'TOTAL: S/ ' . number_format($sale->total, 2), 0, 1, 'R');
        $fpdf->SetFont('Courier', '', 8);
        $fpdf->Ln();

        // Monto en letras
        $formatter = new NumeroALetras();
        $texto = $formatter->toInvoice($sale->total, 2, 'soles');
        $fpdf->MultiCell(74, 3, utf8_decode('SON: ' . $texto), 0, 'L');
        $fpdf->Ln();

        // Información adicional para ventas anticipadas
        if ($anticipada) {
            $fpdf->SetFont('Courier', 'B', 8);
            $fpdf->Cell(74, 3, 'INFORMACION ADICIONAL:', 0, 1);
            $fpdf->SetFont('Courier', '', 8);
            
            if ($sale->telefono) {
                $fpdf->Cell(74, 3, utf8_decode('TELEFONO: ' . $sale->telefono), 0, 1);
            }
            
            if ($sale->sedeRecojo) {
                $fpdf->Cell(74, 3, utf8_decode('SEDE RECOJO: ' . $sale->sedeRecojo->nombre), 0, 1);
            }
            
            if ($sale->direccion) {
                $fpdf->Cell(74, 3, utf8_decode('DIRECCION: ' . $sale->direccion), 0, 1);
            }
            
            if ($sale->referencia) {
                $fpdf->Cell(74, 3, utf8_decode('REFERENCIA: ' . $sale->referencia), 0, 1);
            }
            
            if ($sale->observacion) {
                $fpdf->MultiCell(74, 3, utf8_decode('OBSERVACION: ' . $sale->observacion), 0, 'L');
            }
            $fpdf->Ln();
        }

        // Información adicional para ventas delivery
        if ($delivery) {
            $fpdf->SetFont('Courier', 'B', 8);
            $fpdf->Cell(74, 3, utf8_decode('INFORMACION DELIVERY ANTICIPADO:'), 0, 1);
            $fpdf->SetFont('Courier', '', 8);
            
            if ($sale->telefono) {
                $fpdf->Cell(74, 3, utf8_decode('TELEFONO: ' . $sale->telefono), 0, 1);
            }
            
            if ($sale->direccion) {
                $fpdf->Cell(74, 3, utf8_decode('DIRECCION: ' . $sale->direccion), 0, 1);
            }
            
            if ($sale->referencia) {
                $fpdf->Cell(74, 3, utf8_decode('REFERENCIA: ' . $sale->referencia), 0, 1);
            }
            
            if ($sale->observacion) {
                $fpdf->MultiCell(74, 3, utf8_decode('OBSERVACION: ' . $sale->observacion), 0, 'L');
            }
            
            // Para delivery anticipado, también mostrar información de recojo
            if ($sale->sedeRecojo) {
                $fpdf->Cell(74, 3, utf8_decode('SEDE RECOJO: ' . $sale->sedeRecojo->nombre), 0, 1);
            }
            
            $fpdf->Ln();
        }

        // Pie del documento
        $fpdf->Cell(74, 3, '--------------------------------', 0, 1, 'C');
        
        // Leyenda final según tipo de venta
        if ($anticipada) {
            $leyenda = $sale->voucher_type === 'Factura' ? 'FACTURA ANTICIPADA' : 'BOLETA ANTICIPADA';
            $fpdf->MultiCell(74, 3, utf8_decode($leyenda . ' - Gracias por su preferencia'), 0, 'C');
        } elseif ($delivery) {
            $leyenda = $sale->voucher_type === 'Factura' ? 'FACTURA DELIVERY' : 'BOLETA DELIVERY';
            $fpdf->MultiCell(74, 3, utf8_decode($leyenda . ' - Gracias por su preferencia'), 0, 'C');
        } else {
            $fpdf->MultiCell(74, 3, utf8_decode('Gracias por su preferencia'), 0, 'C');
        }

        $fpdf->Cell(74, 3, '--------------------------------', 0, 1, 'C');
        $fpdf->Cell(74, 3, 'MUSAS PASTELERIA S.R.L.', 0, 1, 'C');
        $fpdf->Cell(74, 3, utf8_decode('Implementado por xinergia.net'), 0, 1, 'C');
        
        // Configurar headers para PDF
        $filename = $sale->voucher_type . '_' . ($sale->number ? str_replace('-', '_', $sale->number) : $sale->id) . '.pdf';
        
        return response($fpdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function pdfReporte(Request $request)
    {
        try{
            // Obtener las fechas de la solicitud
            $user = auth()->user();
            $isAdmin = $user->hasRole('admin') || $user->hasRole('Xinergia');

            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $numero_comprobante = $request->input('numero_comprobante');
            $nombre_cliente = $request->input('nombre_cliente');
            $voucher_type = $request->input('voucher_type');
            $turno = $request->input('turno');
            $payment_method_id = $request->input('payment_method_id');
            $type_sale = $request->input('type_sale');
            $filtrarRestaurante = $type_sale === 'restaurant';
            $user_id = $request->input('user_id');

            // Controlar el caso de delivery
            $type_sale_arr = explode('-', $type_sale);
            $type_sale_arr = array_filter($type_sale_arr, fn($v) => $v !== '');

            // Si se ha enviado un filtro, úsalo. Si no, y no es admin, usa su sede automáticamente
            $headquarter_id = $request->filled('headquarter_id')
                ? $request->input('headquarter_id')
                : (!$isAdmin ? ($user->headquarter->id ?? null) : null);

            //para filtros
            $turno_text = ($turno == 0) ? "Mañana" : (($turno == 1) ? "Tarde" : null);
            $payment_method = $payment_method_id ? optional(PaymentMethod::find($payment_method_id))->nombre : null;
            $headquarter = $headquarter_id ? optional(Headquarters::find($headquarter_id))->nombre : null;
            $type_sale_map = [
                '0000' => 'Directa',
                '0001' => 'Anticipada',
                '0000-0001' => 'Directa y anticipada',
                '0002-0003' => 'Delivery',
                'restaurant' => 'Restaurante',
            ];
            $type_sale_text = $type_sale && isset($type_sale_map[$type_sale]) ? $type_sale_map[$type_sale] : null;


            $consulta = Sale::query()
                ->where('estado', 0)
                ->when($start_date, fn($q) => $q->whereDate('fecha', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('fecha', '<=', $end_date))
                ->when($headquarter_id, fn($q) => $q->where('headquarter_id', $headquarter_id))
                ->when($filtrarRestaurante, fn($q) => $q->where('restaurant', 1)->where('type_sale', '0000'))
                ->when(!$filtrarRestaurante && $type_sale_arr, fn($q) => $q->whereIn('type_sale', $type_sale_arr))
                ->when($user_id, fn($q) => $q->where('user_id', $user_id))
                ->when($numero_comprobante, fn($q) => $q->where('number', 'like', "%$numero_comprobante%"))
                ->when($nombre_cliente, fn($q) => $q->where('cliente', 'like', "%$nombre_cliente%"))
                ->when($voucher_type, fn($q) => $q->where('voucher_type', $voucher_type))
                ->when(isset($turno), fn($q) => $q->where('turno', $turno))
                ->when($payment_method_id, function ($q) use ($payment_method_id) {
                    $q->whereHas('payments', fn($q2) => $q2->where('payment_method_id', $payment_method_id));
                })
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc');
            
            $sales = $consulta->get();

            $totalGeneral = $consulta->sum('total');

            $data = [
                    'sales' => $sales,
                    'totalGeneral' => $totalGeneral,
                    'filters' => [
                        'Desde' => $start_date,
                        'Hasta' => $end_date,
                        'Número' => $numero_comprobante,
                        'Cliente' => $nombre_cliente,
                        'Tipo de comprobante' => $voucher_type,
                        'Turno' => $turno_text,
                        'Método de pago' => $payment_method,
                        'Tipo de venta' => $type_sale_text,
                        'Sede' => $headquarter
                    ]
                ];

            $pdf = Pdf::loadView('sales.pdf', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar pdf: ' . $e->getMessage(),
            ], 500);
        }
    
    }


    public function registrarPago(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|exists:payment_methods,nombre',
        ]);

        try {
            DB::beginTransaction();

            $venta = Sale::findOrFail($request->sale_id);
            $montoPagado = Payment::where('sale_id', $venta->id)->sum('monto');
            $saldoPendiente = $venta->total - $montoPagado;

            if ($saldoPendiente <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Esta venta ya está completamente pagada.'
                ], 400);
            }

            $montoPago = floatval($request->monto);
            if ($montoPago > $saldoPendiente) {
                return response()->json([
                    'status' => false,
                    'message' => 'El monto ingresado excede el saldo pendiente.'
                ], 422);
            }

            $metodo = PaymentMethod::whereRaw('UPPER(nombre) = ?', [strtoupper($request->metodo)])->first();
            if (!$metodo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no válido.'
                ], 422);
            }

            Payment::create([
                'sale_id' => $venta->id,
                'payment_method_id' => $metodo->id,
                'monto' => $montoPago,
                'fecha' => now(),
                'estado' => 0,
                'user_id' => auth()->user()->id,
                'turno' => auth()->user()->turno,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pago registrado correctamente.',
                'nuevo_saldo' => $venta->total - ($montoPagado + $montoPago)
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Error en registrarPago(): ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error al registrar el pago.'
            ], 500);
        }
    }

    public function confirmarEntrega($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = Sale::with('details')->find($id);

                if (!$sale) {
                    return response()->json(['status' => false, 'message' => 'Venta no encontrada']);
                }

                // Verificar que sea una venta anticipada (type_sale = 1) y que no esté ya entregada
                if ($sale->type_sale == 1 && $sale->status == 1) {
                    // Reducir stock al confirmar la entrega de venta anticipada
                    foreach ($sale->details as $detail) {
                        $this->reducirStockProducto($detail->product_id, $detail->quantity, $sale->headquarter_id);
                    }
                }

                $sale->status = 0; // Marcar como entregada
                $sale->save();

                return response()->json(['status' => true, 'message' => 'Entrega confirmada y stock actualizado']);
            });
        } catch (\Exception $e) {
            Log::error('Error en confirmarEntrega: ' . $e->getMessage());
            return response()->json([
                'status' => false, 
                'message' => 'Error al confirmar entrega: ' . $e->getMessage()
            ], 500);
        }
    }


    public function showVentaAnticipada($id)
    {
        try {
            $venta = Sale::with([
                'client:id,nombre,ruc_dni',
                'details' => function ($query) {
                    $query->with('product:id,nombre');
                },
                'payments' => function ($query) {
                    $query->with('paymentMethod:id,nombre');
                }
            ])->findOrFail($id);

            return response()->json([
                'status' => true,
                'venta' => [
                    'id'            => $venta->id,
                    'type_sale'     => $venta->type_sale,
                    'voucher_type'  => $venta->voucher_type,
                    'number'        => $venta->number,
                    'fecha'         => $venta->fecha,
                    'fecha_entrega' => $venta->fecha_entrega,
                    'direccion'     => $venta->direccion,
                    'referencia'    => $venta->referencia,
                    'observacion'   => $venta->observacion,
                    'turno'         => $venta->turno,
                    'estado'        => $venta->estado,
                    'status'        => $venta->status,
                    'total'         => $venta->total,
                    'cliente'       => optional($venta->client)->nombre,
                    'documento'     => optional($venta->client)->ruc_dni,

                    'productos'     => $venta->details->map(function ($detalle) {
                        return [
                            'id'        => $detalle->product_id,
                            'nombre'    => optional($detalle->product)->nombre,
                            'cantidad'  => $detalle->quantity,
                            'precio'    => $detalle->unit_price,
                            'subtotal'  => $detalle->subtotal,
                        ];
                    }),

                    'pagos'         => $venta->payments->map(function ($pago) {
                        return [
                            'monto'         => $pago->monto,
                            'fecha'         => $pago->fecha,
                            'metodo_pago'   => optional($pago->paymentMethod)->nombre,
                        ];
                    }),

                    'saldo'         => $venta->saldo()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al mostrar venta anticipada: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'error'  => 'Venta no encontrada o error interno.'
            ], 500);
        }
    }

    public function generarComprobanteAnticipado(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'tipo_comprobante' => 'required|in:boleta,factura,ticket',
            'document' => 'nullable|string|max:11',
            'client' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:255'
        ]);

        $sale = Sale::with(['details.product', 'client'])->find($request->sale_id);

        if ($sale->saldo() > 0) {
            return response()->json(['status' => false, 'message' => 'La venta aún tiene saldo pendiente.']);
        }

        if ($sale->numero) {
            return response()->json(['status' => false, 'message' => 'Ya se generó el comprobante.']);
        }

        $client = null;
        if ($request->document) {
            $client = \App\Models\Client::where('ruc_dni', $request->document)->first();

            if ($client) {
                if ($request->client && $client->nombre !== $request->client) {
                    $client->nombre = $request->client;
                    $client->save();

                    $sale->client_id = $client->id;
                    $sale->save();
                    $sale->load('client');
                }
            } else {
                // Si no existe, lo crea
                $client = \App\Models\Client::create([
                    'ruc_dni' => $request->document,
                    'nombre'  => $request->client,
                    'estado'  => 0
                ]);
            }
        }

        // Asocia el cliente encontrado o creado a la venta
        if ($client) {
            $sale->client_id = $client->id;
        } else if ($request->client) {
            // Si no hay documento pero sí nombre, podrías buscar por nombre exacto,
        }

        // Actualiza la observación si hay cambios
        if ($request->observacion) {
            $sale->observacion = $request->observacion;
        }

        try {
            if ($request->tipo_comprobante === 'ticket') {
                $sale->number = $this->generarNumeroTicket();
                $sale->voucher_type = 'Ticket';
                $sale->save();

                return response()->json([
                    'status' => true,
                    'url_pdf' => $respuesta['pdf'] ?? null,
                    'venta' => $sale,
                    'productos' => $sale->details->map(function ($item) {
                        return [
                            'nombre'   => $item->product->nombre ?? 'Sin nombre', // ✅ CAMBIO AQUÍ
                            'precio'   => (float) $item->unit_price,
                            'cantidad' => (float) $item->quantity,
                            'subtotal' => (float) $item->subtotal
                        ];
                    })->values(),
                    'tipo_comprobante' => strtolower($sale->voucher_type)
                ]);
            } else {
                $sale->voucher_type = ucfirst($request->tipo_comprobante);
                $sale->save();

                $sale->load('client');
                $respuesta = $this->sendInvoice($sale);


                return response()->json([
                    'status' => true,
                    'url_pdf' => $respuesta['pdf'] ?? null,
                    'venta' => $sale,
                    'productos' => $sale->details->map(function ($item) {
                        return [
                            'nombre'   => $item->product->nombre ?? 'Sin nombre', // ✅ CAMBIO AQUÍ
                            'precio'   => (float) $item->unit_price,
                            'cantidad' => (float) $item->quantity,
                            'subtotal' => (float) $item->subtotal
                        ];
                    })->values(),

                    'tipo_comprobante' => strtolower($sale->voucher_type)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al generar el comprobante: ' . $e->getMessage(),
            ]);
        }
    }
    public function verificarComprobante($id)
    {
        $sale = Sale::find($id);

        if (!$sale) {
            return response()->json(['existe' => false]);
        }

        // Solo devuelve true si tiene un comprobante asociado
        $existe = !is_null($sale->voucher_id);

        return response()->json(['existe' => $existe]);
    }

    /**
     * Reducir stock en Storage3 para un producto específico en una sede
     */
    private function reducirStockProducto($productId, $cantidadVendida, $sedeId)
    {
        // Obtener registros de stock del producto en la sede, ordenados por cantidad descendente
        $stockRecords = Storage3::where('product_id', $productId)
            ->where('headquarter_id', $sedeId)
            ->where('estado', 0)
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'desc')
            ->get();

        $cantidadRestante = $cantidadVendida;

        foreach ($stockRecords as $record) {
            if ($cantidadRestante <= 0) {
                break;
            }

            if ($record->quantity >= $cantidadRestante) {
                // Este registro tiene suficiente stock para completar la venta
                $record->quantity -= $cantidadRestante;
                $record->save();
                $cantidadRestante = 0;
            } else {
                // Este registro no tiene suficiente stock, usamos todo lo que tiene
                $cantidadRestante -= $record->quantity;
                $record->quantity = 0;
                $record->save();
            }
        }

        // Si aún queda cantidad por descontar, log de advertencia
        if ($cantidadRestante > 0) {
            Log::warning("Stock insuficiente para el producto ID: {$productId}. Cantidad faltante: {$cantidadRestante}");
        }
    }

    public function pdfDetallado(Request $request, Sale $sale)
    {
        // Cargar relaciones necesarias
        $sale->load(['client', 'usuario', 'details.product', 'payments.paymentMethod', 'headquarter', 'sedeRecojo']);
        
        $fpdf = new Fpdf('P', 'mm', [80, 297]);
        $fpdf->AddPage();
        $fpdf->AddFont('Courier', '');
        $fpdf->AddFont('Courier', 'B');
        $fpdf->SetMargins(3, 3, 3);
        $fpdf->SetFillColor(255, 255, 255);

        // Encabezado sin logo
        $fpdf->Ln(5);
        $fpdf->SetFont('Courier', 'B', 10);
        $fpdf->Cell(74, 3, 'MUSAS PASTELERIA S.R.L.', 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 8);
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        
        // Título
        $fpdf->SetFont('Courier', 'B', 9);
        $fpdf->Cell(74, 3, 'DETALLE DE VENTA ANTICIPADA', 0, 1, 'C');
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        
        $fpdf->SetFont('Courier', '', 8);
        
        // Información básica
        $fpdf->Cell(74, 3, utf8_decode('N° DE COMPROBANTE: ' . $sale->number), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('SEDE: ' . ($sale->headquarter->nombre ?? 'No especificada')), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('USUARIO: ' . ($sale->usuario->email ?? 'Usuario')), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('FECHA: ' . $sale->fecha), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('FECHA ENTREGA: ' . ($sale->fecha_entrega ?? 'No especificada') . ' ' . ($sale->hora_entrega ?? '--:--')), 0, 1);
        // ...existing code...
        
        // Cliente
        $clienteNombre = $sale->cliente ?? ($sale->client->nombre ?? 'VARIOS');
        $fpdf->Cell(74, 3, utf8_decode('CLIENTE: ' . $clienteNombre), 0, 1);
        
        // Teléfono si existe
        if ($sale->telefono) {
            $fpdf->Cell(74, 3, utf8_decode('TELEFONO: ' . $sale->telefono), 0, 1);
        }
        
        // Sede de recojo si existe
        if ($sale->sedeRecojo) {
            $fpdf->Cell(74, 3, utf8_decode('SEDE RECOJO: ' . $sale->sedeRecojo->nombre), 0, 1);
        }
        
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        
        // Información de pagos
        $fpdf->SetFont('Courier', 'B', 8);
        $fpdf->Cell(74, 3, 'INFORMACION DE PAGOS', 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 8);
        
        $totalVenta = $sale->total;
        $totalPagado = $sale->payments->sum('monto');
        $saldoPendiente = $totalVenta - $totalPagado;
        
        $fpdf->Cell(74, 3, utf8_decode('TOTAL VENTA: S/ ' . number_format($totalVenta, 2)), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('TOTAL PAGADO: S/ ' . number_format($totalPagado, 2)), 0, 1);
        $fpdf->Cell(74, 3, utf8_decode('SALDO PENDIENTE: S/ ' . number_format($saldoPendiente, 2)), 0, 1);
        
        $fpdf->Ln(2);
        
        // Métodos de pago
        if ($sale->payments->count() > 0) {
            $fpdf->SetFont('Courier', 'B', 8);
            $fpdf->Cell(74, 3, 'METODOS DE PAGO:', 0, 1);
            $fpdf->SetFont('Courier', '', 8);
            
            foreach ($sale->payments as $payment) {
                $metodo = $payment->paymentMethod->nombre ?? 'No especificado';
                $monto = number_format($payment->monto, 2);
                $fpdf->Cell(74, 3, utf8_decode($metodo . ': S/ ' . $monto), 0, 1);
            }
        } else {
            $fpdf->Cell(74, 3, 'No hay pagos registrados', 0, 1);
        }
        
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        
        // Productos
        $fpdf->SetFont('Courier', 'B', 8);
        $fpdf->Cell(74, 3, 'PRODUCTOS', 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 7);
        
        // Encabezados de tabla
        $fpdf->Cell(8, 3, utf8_decode('CANT'), 0);
        $fpdf->Cell(32, 3, utf8_decode('PRODUCTO'), 0);
        $fpdf->Cell(17, 3, utf8_decode('P.U.'), 0);
        $fpdf->Cell(17, 3, utf8_decode('TOTAL'), 0, 1);
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        
        $total = 0;
        foreach ($sale->details as $detail) {
            $nombreProducto = substr($detail->product->nombre ?? 'Producto', 0, 15);
            
            $fpdf->Cell(8, 3, number_format($detail->quantity, 0), 0);
            $fpdf->Cell(32, 3, utf8_decode($nombreProducto), 0);
            $fpdf->Cell(17, 3, 'S/ ' . number_format($detail->unit_price, 2), 0);
            $fpdf->Cell(17, 3, 'S/ ' . number_format($detail->subtotal, 2), 0, 1);
            $total += $detail->subtotal;
        }
        
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        $fpdf->SetFont('Courier', 'B', 8);
        $fpdf->Cell(57, 3, 'TOTAL:', 0, 0, 'R');
        $fpdf->Cell(17, 3, 'S/ ' . number_format($total, 2), 0, 1, 'R');
        
        $fpdf->Ln(3);
        
        // Información adicional del cliente
        $fpdf->SetFont('Courier', 'B', 8);
        $fpdf->Cell(74, 3, 'INFORMACION ADICIONAL', 0, 1, 'C');
        $fpdf->SetFont('Courier', '', 7);
        
        if ($sale->direccion) {
            $fpdf->Cell(74, 3, utf8_decode('DIRECCION: ' . $sale->direccion), 0, 1);
        }
        
        if ($sale->referencia) {
            $fpdf->Cell(74, 3, utf8_decode('REFERENCIA: ' . $sale->referencia), 0, 1);
        }
        
        if ($sale->observacion) {
            $fpdf->Cell(74, 3, utf8_decode('OBSERVACION:'), 0, 1);
            $fpdf->MultiCell(74, 3, utf8_decode($sale->observacion), 0, 'L');
        }
        
        $fpdf->Ln(5);
        
        // Pie de página
        $fpdf->SetFont('Courier', '', 7);
        $fpdf->Cell(74, 3, '-------------------------------------------', 0, 1, 'C');
        $fpdf->Cell(74, 3, 'Gracias por su preferencia', 0, 1, 'C');
        $fpdf->Cell(74, 3, 'MUSAS PASTELERIA S.R.L.', 0, 1, 'C');
        $fpdf->Cell(74, 3, utf8_decode('Implementado por xinergia.net'), 0, 1, 'C');
        
        // Configurar headers para PDF
        $filename = 'venta_anticipada_' . $sale->id . '.pdf';
        
        return response($fpdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function anticipated_print (Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            
            // Obtener la venta con todas sus relaciones
            $sale = Sale::with([
                'client', 
                'headquarter', 
                'sedeRecojo',
                'usuario', // Agregar relación con usuario
                'details.product',
                'payments.paymentMethod'
            ])->findOrFail($sale_id);
            
            // Mapear los productos
            $productos = $sale->details->map(function ($detail) {
                return [
                    'nombre' => $detail->product->nombre ?? 'Producto',
                    'precio' => round($detail->unit_price, 2),
                    'cantidad' => round($detail->quantity, 2),
                    'subtotal' => round($detail->subtotal, 2),
                ];
            });

            // Mapear los pagos
            $pagos = $sale->payments->map(function ($payment) {
                return [
                    'metodo_pago' => $payment->paymentMethod->nombre ?? 'N/A',
                    'monto' => round($payment->monto, 2),
                    'fecha' => $payment->created_at->format('d/m/Y H:i'),
                ];
            });

            $tipo = "";
            $type_sale = $sale->type_sale;

            if ($type_sale == 0){
                $tipo = "Venta directa";
            }else if($type_sale == 1){
                $tipo = "Venta anticipada";
            }else if($type_sale == 2 || $type_sale == 3){
                $tipo = "Venta delivery";
            }

            // Información de la venta
            $ventaInfo = [
                'id' => $sale->id,
                'cliente' => $sale->client->nombre ?? $sale->cliente ?? 'Varios',
                'document' => $sale->client->ruc_dni ?? '00000000',
                'tipo' => $tipo,
                'type_sale' => $sale->type_sale,
                'fecha' => Carbon::parse($sale->fecha)->format('d/m/Y H:i:s'),
                'fecha_entrega' => $sale->fecha_entrega,
                'sede' => $sale->headquarter->nombre ?? '-',
                'direccion' => $sale->direccion,
                'referencia' => $sale->referencia,
                'observacion' => $sale->observacion,
                'total' => round($sale->total, 2),
                'saldo' => round($sale->saldo(), 2),
                'telefono' => $sale->telefono,
                'sede_recojo' => $sale->sedeRecojo->nombre ?? 'No especificado',
                'user_id' => $sale->usuario->email ?? 'No especificado', // Usar solo email del usuario
                'voucher_type' => $sale->voucher_type,
                'number' => $sale->number,
                'ticket_number' => $sale->ticket_number,
                'hora_entrega' => $sale->hora_entrega,
            ];

            return response()->json([
                'status' => true,
                'productos' => $productos,
                'pagos' => $pagos,
                'venta' => $ventaInfo,
                'now' => now()->format('d/m/Y H:i:s'),
                'user' => ['name' => Auth::user()->email ?? 'Usuario'],
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al obtener datos para impresión: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function void(Request $request, Sale $sale)
    {
        $url = config('apisunat.url') . '/personas/v1/voidBill';
        $personaId = config('apisunat.id');
        $personaToken = config('apisunat.token.prod');

        if (!$sale->voucher_id) {
            return response()->json(['status' => true, 'errror' => 'El documentid es requerido']);
        }

        $response = Http::post($url, [
            'personaId' => $personaId,
            'personaToken' => $personaToken,
            'documentId' => $sale->voucher_id,
            'reason' => 'Prueba'
        ]);

        $data = $response->object();

        if ($response->failed()) {
            return response()->json(['status' => false, 'error' => $data->error->message]);
        }

        return response()->json(['status' => true, 'message' => 'Se envió la anulación a SUNAT']);
    }

    public function excel(Request $request)
    {
        // Obtener las fechas de la solicitud
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('Xinergia');

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $numero_comprobante = $request->input('numero_comprobante');
        $nombre_cliente = $request->input('nombre_cliente');
        $voucher_type = $request->input('voucher_type');
        $turno = $request->input('turno');
        $payment_method_id = $request->input('payment_method_id');
        $type_sale = $request->input('type_sale');

        // Controlar el caso de delivery
        $type_sale_arr = explode('-', $type_sale);
        $type_sale_arr = array_filter($type_sale_arr, fn($v) => $v !== '');

        // Si se ha enviado un filtro, úsalo. Si no, y no es admin, usa su sede automáticamente
        $sede = $request->filled('headquarter_id')
            ? $request->input('headquarter_id')
            : (!$isAdmin ? ($user->headquarter->id ?? null) : null);



        try {
            return Excel::download(new SalesExport($start_date, $end_date, $numero_comprobante, $nombre_cliente, $voucher_type, $turno, $sede, $payment_method_id, $type_sale_arr), 'Ventas.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detalleVentaAnticipada(Request $request){
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Obtener ventas tipo 1 (anticipada) y 3 (delivery anticipada) con sus detalles y productos
        $ventas = Sale::with(['details.product'])
            ->whereIn('type_sale', [1, 2, 3])
            ->where('estado', 0)
            ->where('status', 1)
            ->orderBy('fecha_entrega');

        // Si hay filtro de fechas, aplicarlo
        if ($start_date) {
            $ventas->whereDate('fecha_entrega', '>=', $start_date);
        }
        if ($end_date) {
            $ventas->whereDate('fecha_entrega', '<=', $end_date);
        }

        $ventas = $ventas->get();

        // Agrupar productos por fecha_entrega y nombre de producto, sumando la cantidad
        $agrupados = [];
        foreach ($ventas as $venta) {
            foreach ($venta->details as $detalle) {
                $fecha = $venta->fecha_entrega;
                $producto = $detalle->product->nombre ?? 'Sin nombre';
                $key = $fecha . '|' . $producto;
                if (!isset($agrupados[$key])) {
                    $agrupados[$key] = [
                        'fecha_entrega' => $fecha,
                        'producto' => $producto,
                        'cantidad' => 0,
                    ];
                }
                $agrupados[$key]['cantidad'] += $detalle->quantity;
            }
        }

        $detalles = array_values($agrupados);
        $totalProductos = array_sum(array_column($detalles, 'cantidad'));

        return view('sales.detalles', compact('start_date', 'end_date', 'totalProductos', 'detalles'));
    }

    public function pagos(Request $request){
        return view('sales.pagos');
    }

    public function getVoucherData(Request $request){
        try{
            
            $voucher_id = $request->voucher_id;
            $type = $request->type; 

            // cdr solo da en producción! en dev no
            if (!in_array($type,['xml','cdr'])){ //si no es xml ni cdr que lance error
                return response()->json(['status' => false, 'message' => 'Type incorrecto']);
            }

            $response = $this->getInvoiceById($voucher_id);
            $data = $response->getData(true);

            // Manejo de error
            if (isset($data['status']) && $data['status'] === false) {
                return response()->json(['status' => false, 'error' => $data['error'] ?? 'Error desconocido']);
            }

            // Excepción para CDR no disponible
            if ($type === 'cdr' && (empty($data['data']['cdr']) || !filter_var($data['data']['cdr'], FILTER_VALIDATE_URL))) {
                return response()->json([
                    'status' => false,
                    'error' => 'El CDR solo estara disponible cuando el comprobante sea aceptado por SUNAT.'
                ])->header('Content-Type', 'application/json; charset=UTF-8');
            }


           return redirect()->away($data['data'][$type]);

        }catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'error' => 'Error al obtener información del comprobante: ' . $e->getMessage(),
            ], 500);

        }
    }

    public function getInvoiceById($id){
        $url = config('apisunat.url') . '/documents/'.$id.'/getById';

        Log::error('url: ' . $url);

        $response = Http::get($url);
        $data = $response->object();
        if ($response->failed()) {
            return response()->json(['status' => false, 'error' => $data->error->message]);
        }

        return response()->json(['status' => true, 'data' => $response->json()]);
    } 

    public function consultarSunat(Request $request)
{
    $doc = $request->query('doc');

    if (!$doc || (strlen($doc) !== 8 && strlen($doc) !== 11)) {
        return response()->json([
            'success' => false,
            'message' => 'Documento inválido'
        ], 422);
    }

    $urlBase = config('apisunat.url');
    $personaId = config('apisunat.id');
    $personaToken = config('apisunat.token.prod');

    try {
        if (strlen($doc) === 8) {
            $url = "$urlBase/personas/$personaId/getDNI?dni=$doc&personaToken=$personaToken";
        } else {
            $url = "$urlBase/personas/$personaId/getRUC?ruc=$doc&personaToken=$personaToken";
        }

        $response = Http::get($url);

        // ✅ LOG TEMPORAL
        \Log::info('Consulta a API Sunat/Reniec', [
            'url' => $url,
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'data' => $response->json('data')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener información de SUNAT/RENIEC'
            ], $response->status());
        }

    } catch (\Exception $e) {
        // ✅ LOG ERROR
        \Log::error('Error al consultar Sunat', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage()
        ], 500);
    }
}

}