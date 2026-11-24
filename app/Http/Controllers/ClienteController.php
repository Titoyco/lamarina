<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Localidad;
use App\Models\Provincia;
use App\Models\EstadoBanco;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Default de `clientes.tipo_haber` (esquema: BIGINT NOT NULL DEFAULT 1).
     * Si el operador deja el campo en blanco se aplica este valor en lugar de fallar.
     */
    private const TIPO_HABER_POR_DEFECTO = 1;

    public function index(Request $request)
    {
        $query = Cliente::with(['localidad.provincia', 'sucursal', 'autorizados', 'titulares']);

        // 1. Buscador por texto
        if ($request->filled('search')) {
            $palabras = array_filter(explode(' ', trim($request->search)));

            $query->where(function ($q) use ($palabras) {
                $q->where(function ($qTitular) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $qTitular->where(function ($subQ) use ($palabra) {
                            $subQ->where('nombre', 'LIKE', "%{$palabra}%")
                                ->orWhere('dni', 'LIKE', "%{$palabra}%")
                                ->orWhere('cuit', 'LIKE', "%{$palabra}%")
                                ->orWhere('nro_cuenta', 'LIKE', "%{$palabra}%")
                                ->orWhere('cbu', 'LIKE', "%{$palabra}%");
                        });
                    }
                })
                ->orWhereHas('autorizados', function ($qAutorizado) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $qAutorizado->where(function ($subQ) use ($palabra) {
                            $subQ->where('nombre', 'LIKE', "%{$palabra}%")
                                ->orWhere('dni', 'LIKE', "%{$palabra}%")
                                ->orWhere('cuit', 'LIKE', "%{$palabra}%");
                        });
                    }
                });
            });
        }

        // 2. Filtros secundarios
        if ($request->filled('id_sucursal')) {
            $query->where('id_sucursal', $request->id_sucursal);
        }

        if ($request->filled('id_localidad')) {
            $query->where('id_localidad', $request->id_localidad);
        }

        if ($request->filled('id_provincia')) {
            $query->whereHas('localidad', function ($q) use ($request) {
                $q->where('id_provincia', $request->id_provincia);
            });
        }

        if ($request->filled('suspendido') && $request->suspendido !== 'todos') {
            $query->where('suspendido', $request->suspendido);
        }

        // Filtro por la nueva columna estado_banco
        if ($request->filled('estado_banco')) {
            $query->where('estado_banco', $request->estado_banco);
        }
        
        // Saldo > 0
        if ($request->boolean('has_saldo')) {
            $query->where('saldo', '>', 0);
        }

        // Ordenamiento interactivo (Click-to-Sort conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'dni'        => 'clientes.dni',
            'nro_cuenta' => 'clientes.nro_cuenta',
            'nombre'     => 'clientes.nombre',
            'sucursal'   => 'sucursal',
            'localidad'  => 'localidad',
            'saldo'      => 'clientes.saldo',
        ];

        $sort = $request->get('sort', 'nombre');
        $direction = strtolower($request->get('direction', 'asc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'nombre';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if ($sort === 'sucursal') {
            $query->leftJoin('sucursales', 'clientes.id_sucursal', '=', 'sucursales.id')
                ->select('clientes.*')
                ->orderBy('sucursales.nombre', $direction);
        } elseif ($sort === 'localidad') {
            $query->leftJoin('localidades', 'clientes.id_localidad', '=', 'localidades.id')
                ->select('clientes.*')
                ->orderBy('localidades.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id') {
            $query->orderBy('clientes.id', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'clientes_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'DNI',
                    'CUIT',
                    'Nro Cuenta',
                    'Cliente',
                    'Sucursal',
                    'Localidad',
                    'Provincia',
                    'Direccion',
                    'Telefono',
                    'Email',
                    'Saldo Actual',
                    'Limite Credito',
                    'Estado',
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $c) {
                        fputcsv($handle, [
                            $c->dni,
                            $c->cuit ?? '-',
                            $c->nro_cuenta ?? '-',
                            $c->nombre,
                            $c->sucursal?->nombre ?? '-',
                            $c->localidad?->nombre ?? '-',
                            $c->localidad?->provincia?->nombre ?? '-',
                            $c->direccion ?? '-',
                            $c->telefono ?? '-',
                            $c->email ?? '-',
                            number_format((float)$c->saldo, 2, ',', ''),
                            number_format((float)($c->limite_credito ?? 0), 2, ',', ''),
                            $c->suspendido ? 'Suspendido' : 'Activo',
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $clientes = $query->paginate(15)->withQueryString();

        $sucursales   = Sucursal::orderBy('nombre')->get();
        $localidades  = Localidad::orderBy('nombre')->get();
        $provincias   = Provincia::orderBy('nombre')->get();
        $estadosBanco = EstadoBanco::orderBy('codigo')->get();

        return view('clientes.index', compact('clientes', 'sucursales', 'localidades', 'provincias', 'estadosBanco', 'sort', 'direction'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $localidades = Localidad::with('provincia')->orderBy('nombre')->get();
        $provincias = Provincia::with('localidades')->orderBy('nombre')->get();
        return view('clientes.create', compact('sucursales', 'localidades', 'provincias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|unique:clientes,dni|integer',
            'cuit' => 'nullable|string|max:20',
            'nro_cuenta' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:191',
            'id_sucursal' => 'required|exists:sucursales,id',
            'medio_pago_preferido' => 'required|integer|in:0,1',
            
            'cbu' => [
                'required_if:medio_pago_preferido,1',
                'nullable',
                'string',
                'size:22',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !$this->validarCBU($value)) {
                        $fail('El número de CBU ingresado no es válido (dígito verificador incorrecto).');
                    }
                },
            ],
            
            'tipo_haber' => 'nullable|integer|min:1',
            'limite' => 'required|numeric|min:0',
            'max_cuotas' => 'required|integer|min:1|max:24',
            'direccion' => 'required|string|max:191',
            'id_localidad' => 'required|exists:localidades,id',
            'email' => 'nullable|email|max:191',
            'tel1' => 'required|string|max:50',
            'tel2' => 'nullable|string|max:50',
            
            // Familiar / Referencia
            'familiar' => 'nullable|string|max:191',
            'tipo_familiar' => 'nullable|string|max:50',
            'direccion_familiar' => 'nullable|string|max:191',
            'id_localidad_familiar' => 'nullable|exists:localidades,id',
            'email_familiar' => 'nullable|email|max:191',
            'tel_familiar' => 'nullable|string|max:50',
            
            // Laborales
            'trabajo' => 'nullable|string|max:191',
            'direccion_trabajo' => 'nullable|string|max:191',
            'id_localidad_trabajo' => 'nullable|exists:localidades,id',
            'tel_trabajo' => 'nullable|string|max:50',
            'obs' => 'nullable|string',
        ], [
            'cbu.required_if' => 'El CBU es obligatorio cuando el medio de pago preferido es Débito en CBU.',
            'tipo_haber.integer' => 'El Tipo de Haber debe ser un número entero (ID de la categoría).',
            'tipo_haber.min' => 'El Tipo de Haber debe ser mayor o igual a 1.',
        ]);

        $data = $this->normalizarDatosCliente($validated);

        // Estado de alta: sin suspensión y sin estado bancario.
        $data['suspendido'] = $request->has('suspendido') ? 1 : 0;
        $data['estado_banco'] = null;
        $data['fecha_estado_banco'] = null;

        Cliente::create($data);

        return redirect()->route('clientes.index')
                        ->with('success', 'Cliente creado correctamente.');
    }

    public function update(Request $request, Cliente $cliente)
    {
        // Validamos la información (permitiendo que el DNI sea el mismo del cliente actual)
        $validated = $request->validate([
            'dni' => 'required|integer|unique:clientes,dni,' . $cliente->id,
            'cuit' => 'nullable|string|max:20',
            'nro_cuenta' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:191',
            'id_sucursal' => 'required|exists:sucursales,id',
            'medio_pago_preferido' => 'required|integer|in:0,1',
            
            // --- CBU REQUERIDO SI MEDIO DE PAGO ES 1 (DÉBITO) ---
            'cbu' => [
                'required_if:medio_pago_preferido,1',
                'nullable',
                'string',
                'size:22',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !$this->validarCBU($value)) {
                        $fail('El número de CBU ingresado no es válido (dígito verificador incorrecto).');
                    }
                },
            ],
            
            'tipo_haber' => 'nullable|integer|min:1',
            'limite' => 'required|numeric|min:0',
            'max_cuotas' => 'required|integer|min:1|max:24',
            'direccion' => 'required|string|max:191',
            'id_localidad' => 'required|exists:localidades,id',
            'email' => 'nullable|email|max:191',
            'tel1' => 'required|string|max:50',
            'tel2' => 'nullable|string|max:50',
            
            // Datos del Familiar / Referencia
            'familiar' => 'nullable|string|max:191',
            'tipo_familiar' => 'nullable|string|max:50',
            'direccion_familiar' => 'nullable|string|max:191',
            'id_localidad_familiar' => 'nullable|exists:localidades,id',
            'email_familiar' => 'nullable|email|max:191',
            'tel_familiar' => 'nullable|string|max:50',
            
            // Datos Laborales
            'trabajo' => 'nullable|string|max:191',
            'direccion_trabajo' => 'nullable|string|max:191',
            'id_localidad_trabajo' => 'nullable|exists:localidades,id',
            'tel_trabajo' => 'nullable|string|max:50',
            'obs' => 'nullable|string',
        ], [
            'cbu.required_if' => 'El CBU es obligatorio cuando el medio de pago preferido es Débito en CBU.',
            'tipo_haber.integer' => 'El Tipo de Haber debe ser un número entero (ID de la categoría).',
            'tipo_haber.min' => 'El Tipo de Haber debe ser mayor o igual a 1.',
        ]);

        $data = $this->normalizarDatosCliente($validated);

        // Manejo del checkbox de suspensión (0 o 1 estricto)
        $data['suspendido'] = $request->input('suspendido') == 1 ? 1 : 0;

        // Actualizamos el registro del legajo
        $cliente->update($data);

        if ($request->boolean('embed')) {
            return redirect()->route('clientes.show', ['cliente' => $cliente->id, 'embed' => 1])
                            ->with('success', 'Legajo de cliente actualizado correctamente.');
        }

        return redirect()->route('clientes.index')
                        ->with('success', 'Legajo de cliente actualizado correctamente.');
    }


    /**
     * Normaliza el payload validado del legajo antes de persistirlo.
     *
     * 1) `tipo_haber` es NOT NULL en BD (default 1): si el operador deja el campo en blanco,
     *    `nullable` deja pasar el vacío y el INSERT mandaría NULL -> viola la restricción.
     *    Se aplica el default del esquema en lugar de romper con un error 500.
     * 2) Los campos opcionales llegan como '' (input vacío) y se guardaban como cadena vacía
     *    en lugar de NULL, ensuciando los reportes y los `LIKE` de búsqueda.
     * 3) Sólo se persisten campos validados: se evita el mass-assignment de columnas
     *    del legajo como `saldo` o `estado_banco`, que no forman parte del formulario.
     */
    private function normalizarDatosCliente(array $validated): array
    {
        // Columnas NOT NULL del esquema `clientes` que deben llevar un valor siempre.
        $data = [
            'tipo_haber' => self::TIPO_HABER_POR_DEFECTO,
            'limite' => 0,
            'max_cuotas' => 3,
            'suspendido' => 0,
        ];

        $data = array_merge($data, $validated);

        // `tipo_haber` vacío -> default del esquema (BD: NOT NULL DEFAULT 1).
        if (blank($data['tipo_haber'])) {
            $data['tipo_haber'] = self::TIPO_HABER_POR_DEFECTO;
        }

        // Campos opcionales: '' -> NULL (nunca cadena vacía en BD).
        $opcionales = [
            'cuit', 'nro_cuenta', 'cbu', 'email', 'tel2', 'familiar', 'tipo_familiar', 'direccion_familiar',
            'id_localidad_familiar', 'email_familiar', 'tel_familiar', 'trabajo',
            'direccion_trabajo', 'id_localidad_trabajo', 'tel_trabajo', 'obs',
        ];

        foreach ($opcionales as $campo) {
            if (array_key_exists($campo, $data) && blank($data[$campo])) {
                $data[$campo] = null;
            }
        }

        return $data;
    }

    private function validarCBU($cbu)
    {
        // Verificar que sean 22 caracteres numéricos
        if (!preg_match('/^[0-9]{22}$/', $cbu)) {
            return false;
        }

        $bloque1 = substr($cbu, 0, 8);
        $bloque2 = substr($cbu, 8, 14);

        // Validar primer bloque (Banco, Sucursal y Dígito Verificador 1)
        $ponderacion1 = [7, 1, 3, 9, 7, 1, 3];
        $suma1 = 0;
        for ($i = 0; $i < 7; $i++) {
            $suma1 += (int)$bloque1[$i] * $ponderacion1[$i];
        }
        $digitoVerificador1 = (10 - ($suma1 % 10)) % 10;
        if ($digitoVerificador1 !== (int)$bloque1[7]) {
            return false;
        }

        // Validar segundo bloque (Cuenta y Dígito Verificador 2)
        $ponderacion2 = [3, 9, 7, 1, 3, 9, 7, 1, 3, 9, 7, 1, 3];
        $suma2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $suma2 += (int)$bloque2[$i] * $ponderacion2[$i];
        }
        $digitoVerificador2 = (10 - ($suma2 % 10)) % 10;
        if ($digitoVerificador2 !== (int)$bloque2[13]) {
            return false;
        }

        return true;
    }

    public function edit(Cliente $cliente)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $localidades = Localidad::with('provincia')->orderBy('nombre')->get();
        $provincias = Provincia::with('localidades')->orderBy('nombre')->get();

        return view('clientes.edit', compact('cliente', 'sucursales', 'localidades', 'provincias'));
    }

    public function destroy(Cliente $cliente)
    {
        // 1. Verificar si tiene créditos activos/históricos o saldo pendiente
        if ($cliente->tieneHistorial()) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque posee historial financiero/créditos o saldo pendiente.');
        }

        // 2. Verificar si es titular con firmas autorizadas vinculadas
        if ($cliente->autorizados()->exists()) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene adicionales autorizados vinculados. Desvincúlelos primero.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'El cliente fue eliminado del sistema correctamente.');
    }

    public function estadoCuenta(Cliente $cliente)
    {
        // Cargar créditos con sus cuotas y sucursales, y sus recibos de cobranza
        $cliente->load([
            'creditos.cuotas',
            'creditos.sucursal',
            'autorizados',
            'recibos' => function ($q) {
                $q->with(['sucursal', 'user'])->orderBy('fecha', 'desc')->orderBy('numero_recibo', 'desc');
            }
        ]);

        // Filtrar solo los créditos activos / abiertos
        $creditosAbiertos   = $cliente->creditos->whereNull('fecha_cancelacion');
        $creditosCancelados = $cliente->creditos->whereNotNull('fecha_cancelacion');

        // Extraer todas las cuotas asociadas únicamente a créditos activos
        $cuotasActivas = $creditosAbiertos->flatMap->cuotas;

        // Métricas exclusivas de Créditos Activos
        $totalValorBase   = $cuotasActivas->sum('valor_base');    // Total Deuda Base (Original)
        $totalIntereses   = $cuotasActivas->sum('intereses');     // Total Intereses
        $totalEntregado   = $cuotasActivas->sum('entrego');       // Total Entregado (Cobrado)
        $totalAPagar      = $cuotasActivas->sum('saldo_actual');    // Total a Pagar
        $totalRestante    = $totalValorBase + $totalIntereses - $totalEntregado - $totalAPagar;  // Total Restante / Saldo Pendiente
        $recibos          = $cliente->recibos;

        // Resumen financiero unificado de cuotas (Deuda total, atrasado, este mes, total a pagar y próximos 3 meses)
        $resumenCuotas = $cliente->calcularResumenCuotas();

        return view('clientes.estado-cuenta', compact(
            'cliente',
            'creditosAbiertos',
            'creditosCancelados',
            'totalValorBase',
            'totalIntereses',
            'totalEntregado',
            'totalAPagar',
            'totalRestante',
            'resumenCuotas',
            'recibos'
        ));
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['autorizados', 'novedades.user', 'sucursal']); // Cargar relaciones necesarias para la vista de detalle
        $resumenCuotas = $cliente->calcularResumenCuotas();
        return view('clientes.show', compact('cliente', 'resumenCuotas'));
    }

    public function storeNovedad(Request $request, Cliente $cliente)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000',
        ]);

        $cliente->novedades()->create([
            'user_id' => auth()->id(),
            'usuario_nombre' => auth()->user()?->name ?? 'Operador',
            'mensaje' => trim($request->mensaje),
            'origen_accion' => 'manual',
        ]);

        if ($request->boolean('embed')) {
            return redirect()->route('clientes.show', ['cliente' => $cliente->id, 'embed' => 1])
                ->with('success', 'Novedad registrada en el legajo correctamente.');
        }

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', 'Novedad registrada en el legajo correctamente.');
    }

    /**
     * Endpoint API JSON para búsqueda rápida de clientes (DNI, nombre, cuenta).
     */
    public function buscar(Request $request)
    {
        $term = trim($request->get('q', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::with('sucursal')
            ->where(function ($q) use ($term) {
                $q->where('dni', 'LIKE', "%{$term}%")
                  ->orWhere('nombre', 'LIKE', "%{$term}%")
                  ->orWhere('cuit', 'LIKE', "%{$term}%")
                  ->orWhere('nro_cuenta', 'LIKE', "%{$term}%");
            })
            ->orderByRaw('CASE WHEN dni = ? OR nro_cuenta = ? THEN 0 WHEN dni LIKE ? THEN 1 ELSE 2 END', [$term, $term, "{$term}%"])
            ->orderBy('nombre')
            ->limit(15)
            ->get(['id', 'id_sucursal', 'nombre', 'dni', 'nro_cuenta', 'cuit', 'saldo', 'suspendido', 'limite', 'max_cuotas']);

        $data = $clientes->map(function ($cl) {
            $estado = $cl->estadoCrediticio();
            return [
                'id' => $cl->id,
                'id_sucursal' => $cl->id_sucursal,
                'sucursal' => $cl->sucursal?->nombre ?? 'N/A',
                'nombre' => $cl->nombre,
                'dni' => $cl->dni,
                'cuit' => $cl->cuit,
                'nro_cuenta' => $cl->nro_cuenta,
                'saldo' => (float)$cl->saldo,
                'suspendido' => (bool)$cl->suspendido,
                'limite' => (float)($cl->limite ?? 0),
                'habilitado' => $estado['habilitado'],
                'tiene_mora' => $estado['tiene_mora'],
                'total_mora' => (float)$estado['total_mora'],
                'limite_disponible' => (float)$estado['limite_disponible'],
                'limite_total' => (float)$estado['limite_total'],
                'deuda_acumulada' => (float)$estado['deuda_acumulada'],
                'max_cuotas' => (int)$estado['max_cuotas'],
                'motivos_bloqueo' => $estado['motivos_bloqueo'],
            ];
        });

        return response()->json($data);
    }
}