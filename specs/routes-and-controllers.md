# Rutas y Controladores — LaMarina Créditos

Alcance verificado: `routes/web.php`, `routes/auth.php` y
`app/Http/Controllers/{CreditoController, ClienteController, PresentacionController, EstadoBancoController, SucursalController}.php`.
Marcas: ⚠ = pendiente/inconsistencia verificada; ✅ = corregido en esta revisión (código + especificación).

## 0.0 Nota transversal: zona horaria y reloj del layout

- **Servidor:** `config/app.php` define `'timezone' => 'America/Argentina/Buenos_Aires'` y
  `locale/fallback_locale = 'es'`. Todas las marcas de tiempo del backend (`now()`, `fecha_credito`,
  `fecha_cobro`, `fecha_cancelacion`, `created_at`) se persisten en esa zona.
- **Cliente:** el **reloj operativo del header** (`layouts/app.blade.php`, ver `ui-guidelines.md` §2) se
  resuelve **íntegramente en el navegador** con Alpine.js + `setInterval` y `toLocaleTimeString('es-AR')`.
  No hay endpoint, ruta ni polling de reloj: el header no requiere ninguna ruta nueva.
- Consecuencia práctica: ambos relojes deben coincidir en pantalla. Si algún equipo opera con su
  navegador en otra zona horaria, el reloj del header discrepa de las fechas registradas por el servidor;
  ante ese caso, forzar la zona del navegador o cambiar `config('app.timezone')` — **no** el `x-data` del
  reloj, que refleja deliberadamente la hora local del operador.

## 0. Correcciones aplicadas en esta revisión (armonización con `models.md` / `ui-guidelines.md`)

Todos los puntos siguientes fueron **corregidos en el código** y reflejados en este documento y en
`specs/models.md`. La verificación de rutas se hizo con `php artisan route:list` (63 rutas, sin duplicados ni
métodos inexistentes) y `php -l` sobre cada archivo modificado.

| # | Inconsistencia detectada | Corrección aplicada |
|---|---|---|
| 1 | `creditos.presentacion_origen` (columna inexistente) usada en el cierre de lote | La fuente de verdad del lote origen es la FK relacional **`creditos.id_presentacion`**; el cierre ahora hace **INNER JOIN `presentaciones`** y filtra `(presentaciones.numero + cuotas.num_cuota) = ?` (§3.3). Documentado en `models.md` §2.1.1. |
| 2 | `CreditoController@store` guardaba `$request->id_cliente` en `id_autorizado` (**FK a `cliente_autorizados`**) | Se valida `id_autorizado` con `Rule::exists('cliente_autorizados','id')->where('id_titular', id_cliente)` y se persiste ese ID, o `NULL` si no viene (§2.1, §3.1). Se ajustó la cadena de vinculación (`Cliente::autorizados()` expone `pivot->id`; `create()` resuelve `ClienteAutorizado`). |
| 3 | `pagarCuota` devolvía `with('status')`, invisible para `<x-notification-modal />` | Ahora `back()->with('success', 'Pago registrado correctamente')` (§2.1, §3.1). |
| 4 | `POST /cambiar-sucursal` sin autenticación | Se agregó `->middleware('auth')` (§1.1, §2.5). |
| 5 | `sistema.presentaciones.cambiar` (GET) era **inalcanzable**: el `show` del `Route::resource` la capturaba como `{presentacion}` | La ruta ahora es **POST**, se declara **antes** del listado y el `Route::resource('presentaciones')` se reemplazó por la ruta explícita `GET /sistema/presentaciones` ⇒ `sistema.presentaciones.index` (§1.2). El formulario de la vista ya enviaba `POST` + `@csrf`. |
| 6 | 12 rutas apuntaban a métodos inexistentes (HTTP 500) | Comentadas/eliminadas en `routes/web.php`: `clientes.creditos`, `clientes.movimientos`, `creditos.edit/update/destroy`, `creditos.cuotas(.show/.edit/.update)`, `sucursales.cambiar`, y los endpoints `create/store/show/edit/update/destroy` del resource de presentaciones (§1.2). |
| 7 | `Presentacion::$fillable` no incluía las columnas reales ⇒ cierre de lote sin tasas y `DB::raw` con `NULL` | Se agregaron `interes_credito` / `interes_mora` y `$tasaMora` se castea a `float` (§3.3). |
| 8 | `redirect()->route('presentaciones.index')` (nombre inexistente) tras el `commit()` | Ahora `redirect()->route('sistema.presentaciones.index')` (§2.3, §3.3). |
| 9 | `Cuota::lote_presentacion` sumaba un *Modelo* a un entero (error fatal) y contradecía a `lote_calculado` | `lote_calculado` es el **accesor oficial**; `lote_presentacion` queda deprecado y delega en él (`models.md` §3.4). |
| 10 | `shadow-2xs` (utilidad de Tailwind v4) en dos vistas | Reemplazado por `shadow-sm` (`ui-guidelines.md` §0/§1.2/§14). |
| 11 | `resources/views/sistema/index.blade.php:17` enlazaba con `route('presentaciones.index')`, nombre **inexistente** (el real es `sistema.presentaciones.index`) ⇒ error 500 al abrir la tarjeta "Presentaciones" del dashboard de configuración | Corregido a `route('sistema.presentaciones.index')`. Se verificó que ninguna otra vista referencia rutas inexistentes (`clientes.creditos`, `clientes.movimientos`, `creditos.edit/update/destroy`, `creditos.cuotas.*`, `sucursales.cambiar`, `presentaciones.*`). |
| 12 | Alta de crédito sin validaciones de negocio en legajo ni presentación | Implementadas en `CreditoController@store`: presentación abierta (`fecha_fin IS NULL`), cliente no suspendido (`suspendido == false/0`), tope `cantidad_cuotas <= cliente.max_cuotas`, y disponible de crédito (`deudaAcumulada + importe_original <= cliente.limite_efectivo`) (§2.1, §3.1). |
| 13 | Tasa de financiación leía `presentacion->tasa_cuotas` (columna inexistente ⇒ 0%) | Corregido a `presentaciones.interes_credito` en `CreditoController@store` (§2.1, §3.1). |
| 14 | `pagarCuota` sin validación de request, sobrepagos y sin cierre automático de crédito | Validado con `monto` (`min:0.01`, `max:saldo_actual`), `medio` (`string|max:50`), acumulación `entrego += monto`, saldo `saldo_actual = max(0, saldo_actual - monto)` y auto-cierre del crédito (`fecha_cancelacion = now()`) al quedar en saldo 0 (§2.1, §3.1). |

## 1. Tabla de Rutas Web

### 1.1 Públicas / fuera del grupo `auth`

| Método | URI | Name | Controlador@Método | Middleware |
|---|---|---|---|---|
| GET | `/login` | `login` | closure → `view('auth.login')` (`web.php:26`) | (ninguno) ⚠ *código muerto: `auth.php` registra la misma URI+método después, por lo que la reemplaza en la colección de rutas; `php artisan route:list` muestra **una sola** entrada `/login` servida por el controlador Breeze. El closure nunca se ejecuta (se recomienda eliminarlo).* |
| GET | `/login` | `login` | `Auth\AuthenticatedSessionController@create` | `guest` (`auth.php`) — es la efectiva |
| POST | `/login` | — | `Auth\AuthenticatedSessionController@store` | `guest` |
| GET / POST | `/register` | `register` / — | `RegisteredUserController@create` / `@store` | `guest` |
| GET / POST | `/forgot-password` | `password.request` / `password.email` | `PasswordResetLinkController@create` / `@store` | `guest` |
| GET | `/reset-password/{token}` | `password.reset` | `NewPasswordController@create` | `guest` |
| POST | `/reset-password` | `password.store` | `NewPasswordController@store` | `guest` |
| POST | `/cambiar-sucursal` | `sucursal.update.global` | `SucursalController@updateGlobal` | `auth` ✅ *(declarada fuera del grupo `auth` pero con `->middleware('auth')` explícito; antes no exigía autenticación)* |
| GET | `/` | `dashboard` | closure → `view('dashboard')` | `auth` |

### 1.2 Grupo `auth` (`web.php:35-107`)

| Método | URI | Name | Controlador@Método | Middleware |
|---|---|---|---|---|
| GET | `/clientes` | `clientes.index` | `ClienteController@index` | `auth` |
| GET | `/clientes/nuevo` | `clientes.create` | `ClienteController@create` | `auth` |
| POST | `/clientes` | `clientes.store` | `ClienteController@store` | `auth` |
| GET | `/clientes/{cliente}` | `clientes.show` | `ClienteController@show` | `auth` |
| GET | `/clientes/{cliente}/editar` | `clientes.edit` | `ClienteController@edit` | `auth` |
| PUT | `/clientes/{cliente}` | `clientes.update` | `ClienteController@update` | `auth` |
| DELETE | `/clientes/{cliente}` | `clientes.destroy` | `ClienteController@destroy` | `auth` |
| GET | `/clientes/{cliente}/estado-cuenta` | `clientes.estado-cuenta` | `ClienteController@estadoCuenta` | `auth` |
| ❌ ~~GET~~ | ~~`/clientes/{cliente}/creditos`~~ | ~~`clientes.creditos`~~ | **RUTA ELIMINADA** (`ClienteController@creditos` no existe → 500). Alternativa funcional: `clientes.show` | — |
| ❌ ~~GET~~ | ~~`/clientes/{cliente}/movimientos`~~ | ~~`clientes.movimientos`~~ | **RUTA ELIMINADA** (`ClienteController@movimientos` no existe → 500). Alternativa: `clientes.estado-cuenta` | — |
| GET | `/creditos` | `creditos.index` | `CreditoController@index` | `auth` |
| GET | `/creditos/nuevo` | `creditos.create` | `CreditoController@create` | `auth` |
| POST | `/creditos` | `creditos.store` | `CreditoController@store` | `auth` |
| GET | `/creditos/{credito}` | `creditos.show` | `CreditoController@show` | `auth` |
| ❌ ~~GET~~ | ~~`/creditos/{credito}/editar`~~ | ~~`creditos.edit`~~ | **RUTA ELIMINADA** (`CreditoController@edit` no existe → 500) | — |
| ❌ ~~PUT~~ | ~~`/creditos/{credito}`~~ | ~~`creditos.update`~~ | **RUTA ELIMINADA** (`CreditoController@update` no existe → 500) | — |
| ❌ ~~DELETE~~ | ~~`/creditos/{credito}`~~ | ~~`creditos.destroy`~~ | **RUTA ELIMINADA** (`CreditoController@destroy` no existe → 500) | — |
| GET | `/creditos/{credito}/pagare` | `creditos.pagare` | `CreditoController@imprimirPagare` | `auth` |
| ❌ ~~GET~~ | ~~`/creditos/{credito}/cuotas`~~ | ~~`creditos.cuotas`~~ | **RUTA ELIMINADA** (`@cuotas` no existe → 500) | — |
| ❌ ~~GET~~ | ~~`/creditos/{credito}/cuotas/{cuota}`~~ | ~~`creditos.cuotas.show`~~ | **RUTA ELIMINADA** (`@showCuota` no existe → 500) | — |
| ❌ ~~GET~~ | ~~`/creditos/{credito}/cuotas/{cuota}/editar`~~ | ~~`creditos.cuotas.edit`~~ | **RUTA ELIMINADA** (`@editCuota` no existe → 500) | — |
| ❌ ~~PUT~~ | ~~`/creditos/{credito}/cuotas/{cuota}`~~ | ~~`creditos.cuotas.update`~~ | **RUTA ELIMINADA** (`@updateCuota` no existe → 500) | — |
| POST | `/creditos/{cuota}/pagar` | `creditos.pagar` | `CreditoController@pagarCuota` | `auth` |
| POST | `/creditos/cuotas/{cuota}/anular-pago` | `creditos.cuotas.anular-pago` | `CreditoController@anularPago` | `auth` ✅ Compensatoria: reabre cuota y crédito si estaba cancelado, asienta traza en `cliente_novedades` |
| DELETE | `/creditos/{credito}/anular` | `creditos.anular` | `CreditoController@anularCredito` | `auth` ✅ Compensatoria: elimina crédito y cuotas en cascada (bloqueado si posee pagos), asienta en `cliente_novedades` |
| GET | `/sistema` | `sistema.index` | closure → `view('sistema.index')` | `auth` |
| GET | `/sistema/auditoria` | `sistema.auditoria.index` | `AuditoriaController@index` | `auth` ✅ Auditoría transversal con filtros por modelo, evento y fechas |
| POST | `/sistema/auditoria/{audit}/rollback` | `sistema.auditoria.rollback` | `AuditoriaController@rollback` | `auth` ✅ Reversión de estado en BD con `transitionTo($audit, true)` |
| RESOURCE | `/sistema/usuarios` | `usuarios.*` | `UserController` (CRUD completo) | `auth` |
| RESOURCE | `/sistema/permisos` | `permisos.*` | `PermisoController` (CRUD completo) | `auth` |
| RESOURCE | `/sistema/sucursales` | `sucursales.*` | `SucursalController` | `auth` ⚠ métodos de resource vacíos (respuesta nula) |
| GET | `/sistema/presentaciones` | `sistema.presentaciones.index` | `PresentacionController@index` | `auth` ✅ *(sustituye a `Route::resource('presentaciones')`, que registraba `create/store/show/edit/update/destroy` sin método en el controlador)* |
| POST | `/sistema/presentaciones/cambiar` | `sistema.presentaciones.cambiar` | `PresentacionController@cambiar` | `auth` ✅ *(corregido: verbo **POST**, declarada **antes** del listado ⇒ ya no la captura `{presentacion}`; la vista ya enviaba `POST` + `@csrf`)* |
| ❌ ~~GET~~ | ~~`/sistema/sucursales/{sucursal}/cambiar`~~ | ~~`sucursales.cambiar`~~ | **RUTA ELIMINADA** (`SucursalController@cambiar` no existe → 500). El cambio de sucursal activa es `sucursal.update.global` (§1.1) | — |
| RESOURCE | `/sistema/sucursales` | `sistema.sucursales.*` | `SucursalController` (ABM completo) | `auth` ✅ Nombres con prefijo `sistema.` |
| GET | `/sistema/ubicaciones` | `sistema.ubicaciones.index` | `UbicacionController@index` | `auth` ✅ Vista consolidada provincias + localidades |
| POST | `/sistema/ubicaciones/provincias` | `sistema.ubicaciones.provincias.store` | `UbicacionController@storeProvincia` | `auth` |
| PUT | `/sistema/ubicaciones/provincias/{provincia}` | `sistema.ubicaciones.provincias.update` | `UbicacionController@updateProvincia` | `auth` |
| DELETE | `/sistema/ubicaciones/provincias/{provincia}` | `sistema.ubicaciones.provincias.destroy` | `UbicacionController@destroyProvincia` | `auth` (bloquea si tiene localidades) |
| POST | `/sistema/ubicaciones/localidades` | `sistema.ubicaciones.localidades.store` | `UbicacionController@storeLocalidad` | `auth` |
| PUT | `/sistema/ubicaciones/localidades/{localidad}` | `sistema.ubicaciones.localidades.update` | `UbicacionController@updateLocalidad` | `auth` |
| DELETE | `/sistema/ubicaciones/localidades/{localidad}` | `sistema.ubicaciones.localidades.destroy` | `UbicacionController@destroyLocalidad` | `auth` (bloquea si tiene clientes) |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | `auth` |
| PATCH | `/profile` | `profile.update` | `ProfileController@update` | `auth` |
| DELETE | `/profile` | `profile.destroy` | `ProfileController@destroy` | `auth` |
| GET | `/verify-email` | `verification.notice` | `EmailVerificationPromptController` (`__invoke`) | `auth` |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `VerifyEmailController` (`__invoke`) | `auth` |
| POST | `/email/verification-notification` | `verification.send` | `EmailVerificationNotificationController@store` | `auth` |
| GET | `/confirm-password` | `password.confirm` | `ConfirmablePasswordController@show` | `auth` |
| POST | `/confirm-password` | — | `ConfirmablePasswordController@store` | `auth` |
| PUT | `/password` | `password.update` | `PasswordController@update` | `auth` |
| POST | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | `auth` |

**Código muerto detectado:** `EstadoBancoController` (CRUD de `estados_banco`) **no está registrado en ninguna
ruta**, no existe `routes/api.php`, y sus `redirect()->route('estados-banco.index')` apuntan a un nombre de
ruta inexistente. El modelo `DevolucionBancaria` tampoco se usa (clase vacía, sin `$fillable` ni tabla explícita).

## 2. Controladores y Métodos

Sólo se listan los métodos **públicos** realmente presentes en el código (verificado por firma).

### 2.1 `CreditoController` (8 métodos)

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `index` | `Request` (`cliente_id`, `search`, `sucursal`, `tipo`, `estado`, `has_interes`, `has_entrego`, `has_saldo`) | ninguna | `Credito` (join `clientes`, `with cliente/sucursal/autorizado.cliente/presentacion`, `withSum` de `valor_base`, `entrego`, `intereses`, `saldo_actual`) + `Sucursal::all()`, subquery `Cuota::joinSub` para `$totalAPagar` y `$totalCreditos` | `view('creditos.index')` con `$totalCreditos`, `$totalAPagar`, `$creditos` · `paginate(15)→withQueryString()` |
| `show` | `Credito $credito` (route model binding) | ninguna | `Credito` (`load cliente, sucursal, autorizado.cliente, cuotas` ordenadas por `num_cuota`) | `view('creditos.show')` con `$totalValorBase`, `$totalIntereses`, `$totalEntrego`, `$totalSaldo` |
| `pagarCuota` | `Request $request` (`monto`, `medio`), `Cuota $cuota` | `monto` required\|numeric\|min:0.01\|max:{cuota.saldo_actual} · `medio` required\|string\|max:50 ✅ *(corregido)* | `DB::transaction`: `Cuota::update([entrego += monto, fecha_cobro=now(), medio_pago, saldo_actual = max(0, saldo_actual - monto)])`; si no restan cuotas con `saldo_actual > 0` en el crédito ⇒ `Credito::update([fecha_cancelacion=now()])` | `back()->with('success', 'Pago registrado correctamente')` ✅ *(corregido)* |
| `anularPago` | `Request $request` (`motivo`), `Cuota $cuota` | `motivo` required\|string\|min:5\|max:255 | `DB::transaction`: restaura `Cuota` (`saldo_actual += entrego`, `entrego = 0`, `fecha_cobro = null`, `medio_pago = null`), reabre `Credito` (`fecha_cancelacion = null`), crea registro en `cliente_novedades` del titular | `back()->with('success', 'Pago anulado y cuota reabierta correctamente.')` ✅ |
| `anularCredito` | `Request $request` (`motivo`), `Credito $credito` | `motivo` required\|string\|min:5\|max:255 · Valida ausencia de cuotas con `entrego > 0` | `DB::transaction`: crea novedad formal en `cliente_novedades` del titular y ejecuta `$credito->delete()` (eliminando cuotas por cascada de BD) | `redirect()->route('creditos.index')->with('success', ...)` ✅ |
| `create` | `Request` (`id_cliente` obligatorio de facto, `id_autorizado` opcional = **ID del registro en `cliente_autorizados`**) | **ninguna** (usa `findOrFail`; el autorizado se resuelve con `ClienteAutorizado::with('cliente')->where('id',…)->where('id_titular', cliente.id)->first()` ✅ corregido) | `Cliente` (con `sucursal`, `creditos.cuotas`, `novedades.user`, `autorizados`), `ClienteAutorizado`, `DB::table('presentaciones')->max('id')` | `view('creditos.create')` con `presentacionActual`, `vencimientos` (4 buckets), `limiteTotal`, `limiteDisponible`, `aFavor`, totales |
| `store` | `Request` | `id_cliente` required\|exists:clientes,id · `id_sucursal` required\|exists:sucursales,id · `id_presentacion` required\|exists:presentaciones,id · `tipo_credito` required\|in:C,D,R · `importe_original` required\|numeric\|min:1 · `cantidad_cuotas` required\|integer\|min:1 · `id_autorizado` nullable\|integer + `Rule::exists('cliente_autorizados','id')->where('id_titular', id_cliente)` ✅. **Reglas de negocio previas (BD):** a) `Presentacion::whereNull('fecha_fin')` (lote abierto); b) titular no suspendido (`!$cliente->suspendido`); c) `cantidad_cuotas <= cliente.max_cuotas`; d) cupo disponible `deudaAcumulada + importe_original <= cliente.limite_efectivo` ✅ | `Cliente::findOrFail`, `Presentacion::whereNull('fecha_fin')`, `SucursalIndice` (`firstOrCreate` por `id_sucursal`+`tipo_credito`, `increment('ultimo_indice')`), `Sucursal`, `Credito::create`, `Cuota::create` ×N — todo dentro de `DB::transaction` | `redirect()->route('creditos.index')->with('success')` |
| `imprimirPagare` | `Credito $credito` | ninguna | `Credito` (`load cliente, sucursal, autorizado.cliente, cuotas`) + `\NumberFormatter('es', SPELLOUT)` | `view('creditos.pagare')` con `totalMonto = cantidad_cuotas × valor_cuota_ref`, `concepto` (`R` ⇒ REFINANCIACION, resto ⇒ COMPRAS), `montoEnLetras` |

**Detalle del alta (cálculo de cuotas — `store`):**

0. **Validaciones de negocio preventivas:**
   - Presentación activa: se verifica `Presentacion::whereNull('fecha_fin')->find($request->id_presentacion)`. Si está cerrada, arroja `ValidationException` en `id_presentacion`.
   - Cliente habilitado: se verifica que `cliente.suspendido` sea falso o 0; de lo contrario falla con error *"El cliente se encuentra suspendido para operar créditos"*.
   - Límite de cuotas: se valida `cantidad_cuotas <= cliente.max_cuotas`.
   - Límite crediticio: se calcula `deudaAcumulada = sum(saldo_actual)` de cuotas impagas en créditos abiertos del titular. Se comprueba que `(deudaAcumulada + importe_original) <= cliente.limite_efectivo`. Si se excede, falla informando límite, deuda exigible y disponible.
1. `$indice->increment('ultimo_indice')` ⇒ correlativo por (sucursal, tipo).
2. `$codigoCredito = $sucursal->codigo_sucursal . $tipo_credito . $nuevoNumeroIndice` (ej. `IC125`) → columna `creditos.credito`.
3. `$interesPorcentaje = $presentacion->interes_credito ?? 0` ✅ *(corregido: la columna real en `presentaciones` es `interes_credito`, no la inexistente `tasa_cuotas`)* → interés directo simple:
   `$valorCuotaRef = round($importe_original * (1 + $interesPorcentaje/100) / $cantidad_cuotas, 2)`.
4. Cabecera `Credito` con `id_autorizado = $request->input('id_autorizado') ?: null` ✅ *(corregido)*: `Credito::autorizado()` es `belongsTo(ClienteAutorizado::class,'id_autorizado')`, por lo que **antes** se guardaba un `clientes.id` en una FK a `cliente_autorizados` (autorizado inexistente/incoherente); ahora la FK recibe sólo el ID validado de la autorización del titular, o `NULL` si el crédito es del propio titular.
5. Cascada: por cada `i` en `1..cantidad_cuotas` crea `Cuota(num_cuota=i, valor_base=valorCuotaRef, intereses=0, entrego=0, saldo_actual=valorCuotaRef, fecha_ultimo_envio=null, estado_bancario=null)`.
6. No se persiste `fecha_vencimiento` por cuota (pendiente de diseño). El **lote origen no necesita persistirse aparte**: ya vive en la FK `creditos.id_presentacion` (§3.1); no existe la columna `presentacion_origen`.

**Vinculación de autorizados (corregida ✅):** el formulario envía `id_autorizado` = **`cliente_autorizados.id`**
(en `clientes.index` se arma con `$autorizado->pivot->id`; `Cliente::autorizados()` ahora expone ese `pivot->id`);
`create()` lo resuelve con `ClienteAutorizado::with('cliente')->where('id', …)->where('id_titular', $cliente->id)->first()`
(sólo autorizaciones vigentes del titular) y lo muestra en `creditos/create` como `$autorizado->cliente->nombre`;
`store()` lo **persiste** previa validación `Rule::exists('cliente_autorizados','id')->where('id_titular', id_cliente)`.
Un crédito del propio titular se guarda con `id_autorizado = NULL`.

### 2.2 `ClienteController` (8 métodos públicos + 1 privado)

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `index` | `Request` (`search`, `id_sucursal`, `id_localidad`, `id_provincia`, `suspendido`, `estado_banco`, `has_saldo`) | ninguna | `Cliente` (`with localidad.provincia, sucursal, autorizados, titulares`), `Sucursal`, `Localidad`, `Provincia`, `EstadoBanco` | `view('clientes.index')` · `orderBy('nombre')->paginate(15)->withQueryString()` |
| `create` | — | ninguna | `Sucursal::all()`, `Localidad::all()` | `view('clientes.create')` |
| `store` | `Request` | `dni` required\|unique:clientes,dni\|integer · `nombre` required\|max:191 · `id_sucursal` required\|exists · `medio_pago_preferido` required\|in:0,1 · `cbu` required_if:medio_pago_preferido,1 + size:22 + closure `validarCBU` · `tipo_haber` nullable\|integer\|min:1 · `limite` required\|numeric\|min:0 · `max_cuotas` required\|in:1,2,3,4 · `direccion` required\|max:191 · `id_localidad` required\|exists · `email` nullable\|email · `tel1` required · `tel2` nullable · familiar (`familiar`, `tipo_familiar`, `direccion_familiar`, `id_localidad_familiar`, `email_familiar`, `tel_familiar`) · laborales (`trabajo`, `direccion_trabajo`, `id_localidad_trabajo`, `tel_trabajo`) · `obs` — mensajes custom para `cbu.required_if`, `tipo_haber.integer` y `tipo_haber.min` | `$data = ClienteController::normalizarDatosCliente($validated)` (privado) + `suspendido` + `estado_banco=null` + `fecha_estado_banco=null` → `Cliente::create($data)` | `redirect()->route('clientes.index')->with('success')` |
| `update` | `Request`, `Cliente $cliente` | idénticas a `store` pero `dni` required\|unique:clientes,dni,**{id}** | `normalizarDatosCliente($validated)` + `suspendido` normalizado (`== 1 ? 1 : 0`) → `$cliente->update($data)` | `redirect()->route('clientes.index')->with('success')` |
| `edit` | `Cliente $cliente` | ninguna | `Sucursal::all()`, `Localidad::orderBy('nombre')` | `view('clientes.edit')` |
| `destroy` | `Cliente $cliente` | regla de negocio: si `$cliente->creditos()->exists()` **no borra** | `Cliente` (`creditos`) | `redirect()->route('clientes.index')->with('error')` o `->with('success')` |
| `estadoCuenta` | `Cliente $cliente` | ninguna | `Cliente` (`load creditos.cuotas, creditos.sucursal, autorizados`) | `view('clientes.estado-cuenta')` con totales de **créditos abiertos**: `totalValorBase`, `totalIntereses`, `totalEntregado`, `totalAPagar`, `totalRestante = base + intereses − entregado − aPagar` |
| `show` | `Cliente $cliente` | ninguna | `Cliente` (`load autorizados, novedades.user, sucursal`) | `view('clientes.show')` |
| `validarCBU` (**private**) | `string $cbu` | `preg_match('/^[0-9]{22}$/')` + dígitos verificadores BCRA: bloque1 `[7,1,3,9,7,1,3]`, bloque2 `[3,9,7,1,3,9,7,1,3,9,7,1,3]`, DV = `(10 − suma % 10) % 10` | — | `bool` |

Notas: `store`/`update` usan `$request->all()` (mass assignment sin `$validated`); el flag `suspendido` se
normaliza manualmente porque el checkbox no llega cuando está desmarcado. `estado_banco`/`fecha_estado_banco`
se fuerzan a `null` en el alta y **sólo** se cambian fuera de este controlador (hoy: nunca, ver §3.3).

### 2.3 `PresentacionController` (2 métodos)

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `index` | — | ninguna | `User::with('sucursal')`, `Presentacion::orderBy('numero','desc')->paginate(15)`, `$actual = ...->first()` | `view('sistema.presentaciones.index')` |
| `cambiar` | `Request` (**POST**) | `interes_credito` required\|numeric\|min:0 · `interes_mora` required\|numeric\|min:0 | `Presentacion` (`whereNull('fecha_fin')`), `DB::table('cuotas')` + INNER JOIN `creditos` **+ INNER JOIN `presentaciones` por `creditos.id_presentacion`** ✅, `Carbon` | `redirect()->route('sistema.presentaciones.index')` ✅ *(corregido; antes apuntaba al nombre inexistente `presentaciones.index`)* o `back()->with('error')` |

### 2.4 `EstadoBancoController` (6 métodos, **sin rutas**)

`index` (`Request->search` por `codigo`/`descripcion`, `paginate(15)`), `create`, `store`
(`codigo` required\|max:10\|unique:estados_banco,codigo + `descripcion` required\|max:191, con
`strtoupper(trim())`), `edit`, `update` (misma regla con `unique...,{id}`), `destroy`.
Modelo único: `EstadoBanco` (`$table = 'estados_banco'`). Vistas referenciadas: `estados_banco.*` (no enrutadas).

### 2.5 `SucursalController` (9 métodos — ABM completo ✅)

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `updateGlobal` | `Request` | `id_sucursal` required\|exists:sucursales,id | — (escribe `session(['sucursal_activa_id' => …])` + `session()->save()`) | `back()` — ruta `POST /cambiar-sucursal` (`sucursal.update.global`) exige `auth` ✅ |
| `index` | — | ninguna | `Sucursal::withCount(['clientes','creditos'])->orderBy('nombre')->paginate(15)` | `view('sistema.sucursales.index')` con `$sucursales` |
| `create` | — | ninguna | — | `view('sistema.sucursales.create')` |
| `store` | `Request` | `nombre` required\|string\|max:100 · `codigo_sucursal` required\|string\|max:10\|unique:sucursales,codigo_sucursal · `direccion` nullable\|string\|max:191 | `Sucursal::create` (con `strtoupper(trim())` sobre el código) | `redirect()->route('sistema.sucursales.index')->with('success')` |
| `show` | `string $id` | ninguna | — | `redirect()` a `sistema.sucursales.edit` (no hay vista de detalle propia) |
| `edit` | `Sucursal $sucursale` (route model binding) | ninguna | `Sucursal` | `view('sistema.sucursales.edit')` con `$sucursal` |
| `update` | `Request`, `Sucursal $sucursale` | `nombre` required\|string\|max:100 · `codigo_sucursal` required\|string\|max:10 + `Rule::unique('sucursales','codigo_sucursal')->ignore($sucursal->id)` · `direccion` nullable\|string\|max:191 | `Sucursal::update` | `redirect()->route('sistema.sucursales.index')->with('success')` |
| `destroy` | `Sucursal $sucursale` | **regla de negocio**: no elimina si `$sucursal->clientes()->exists()` o `$sucursal->creditos()->exists()` | `Sucursal::delete` | `redirect()->route('sistema.sucursales.index')->with('error', 'No se puede eliminar la sucursal porque tiene registros vinculados.')` o `->with('success')` |
| `cambiar` | — | — | **no existe**; la ruta `sucursales.cambiar` fue eliminada ✅ | — |

⚠ **Nota de esquema:** la columna `sucursales.direccion` (`varchar(191)`, nullable) fue agregada por la
migración `2026_09_27_230928_add_direccion_to_sucursales_table`, y `codigo_sucursal` se ensanchó de
`varchar(1)` a `varchar(10)` (`2026_09_27_231133_alter_codigo_sucursal_in_sucursales_table`) para admitir
los códigos de hasta 10 caracteres que valida el ABM.

### 2.6 `UbicacionController` (7 métodos — Zonas Geográficas ✅)

Vista única consolidada: panel izquierdo (40%) con el catálogo de provincias y panel derecho (60%) con el
data-grid de localidades filtrable.

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `index` | `Request` (`search`, `id_provincia`) | ninguna | `Localidad::with('provincia')` con `when(search → LIKE nombre/codigo_postal)` y `when(id_provincia)`, `orderBy('nombre')->paginate(20)->withQueryString()`; `Provincia::withCount('localidades')->orderBy('nombre')->get()` | `view('sistema.ubicaciones.index')` con `$localidades`, `$provincias` |
| `storeProvincia` | `Request` | `nombre` required\|string\|max:100\|unique:provincias,nombre | `Provincia::create` | `back()->with('success')` |
| `updateProvincia` | `Request`, `Provincia $provincia` | `nombre` required\|string\|max:100 + `Rule::unique('provincias','nombre')->ignore($provincia->id)` | `Provincia::update` | `back()->with('success')` |
| `destroyProvincia` | `Provincia $provincia` | **regla de negocio**: no elimina si `$provincia->localidades()->exists()` | `Provincia::delete` | `back()->with('error', 'No se puede eliminar la provincia porque tiene localidades asociadas.')` o `->with('success')` |
| `storeLocalidad` | `Request` | `id_provincia` required\|exists:provincias,id · `nombre` required\|string\|max:191 · `codigo_postal` nullable\|string\|max:10 | `Localidad::create` (normaliza `nombre` con `ucwords(strtolower(trim()))`) | `back()->with('success')` |
| `updateLocalidad` | `Request`, `Localidad $localidad` | idéntica a `storeLocalidad` | `Localidad::update` | `back()->with('success')` |
| `destroyLocalidad` | `Localidad $localidad` | **regla de negocio**: no elimina si tiene clientes en `id_localidad`, `id_localidad_familiar` o `id_localidad_trabajo` | `Localidad::delete` | `back()->with('error', 'No se puede eliminar la localidad porque tiene clientes asociados.')` o `->with('success')` |

⚠ **Rutas eliminadas:** `GET /sistema/provincias` (`provincias.index`) y `GET /sistema/localidades`
(`localidades.index`) — reemplazadas por la vista consolidada `GET /sistema/ubicaciones`.

### 2.7 `AuditoriaController` (2 métodos)

| Método | Parámetros | Validación | Modelos | Retorno |
|---|---|---|---|---|
| `index` | `Request` (`auditable_type`, `event`, `record_id`, `desde`, `hasta`) | `auditable_type` in:... · `event` in:created,updated,deleted · `desde`/`hasta` date | `OwenIt\Auditing\Models\Audit` (with `user`), paginado (20) | `view('sistema.auditoria.index')` con `$audits`, `$auditables`, `$events` |
| `rollback` | `Audit $audit` | valida `$audit->event === 'updated'` y que `$audit->auditable` exista | Modelo auditable (`Cliente`, `Credito`, `Cuota`, `Presentacion`) | `$auditable->transitionTo($audit, true)` + `save()` → `back()->with('success', ...)` |

## 3. Flujos Operativos Críticos

### 3.1 Alta y liquidación de créditos

**Alta (`POST /creditos` → `CreditoController@store`)** — secuencia real:

1. `validate()`: cliente/sucursal/presentación existentes, `tipo_credito ∈ {C,D,R}`, `importe_original ≥ 1`,
   `cantidad_cuotas ≥ 1`.
2. **Validaciones de negocio preventivas (BD):**
   - Presentación activa: se busca `Presentacion::whereNull('fecha_fin')->find(id_presentacion)`; si ya está cerrada se aborta con error de validación.
   - Cliente habilitado: se verifica `!$cliente->suspendido`; si está suspendido se retorna el mensaje *"El cliente se encuentra suspendido para operar créditos"*.
   - Límite de cuotas: se valida `cantidad_cuotas <= cliente.max_cuotas`.
   - Límite crediticio: se acumula la deuda exigible (`sum(saldo_actual)` de cuotas impagas en créditos abiertos) y se valida que `(deudaAcumulada + importe_original) <= cliente.limite_efectivo`.
3. `DB::transaction`: correlativo `SucursalIndice` (por `id_sucursal` + `tipo_credito`)
   → `increment('ultimo_indice')` → código `codigo_sucursal . tipo . indice` (ej. `IC125`).
4. Tasa: `Presentacion::whereNull('fecha_fin')->find(...)->interes_credito` (interés directo simple) ✅ *(corregido: la columna real en `presentaciones` es `interes_credito`, no la inexistente `tasa_cuotas`)*.
   `valor_cuota_ref = round(importe_original × (1 + tasa/100) / cantidad_cuotas, 2)`.
5. `Credito::create` + una `Cuota` por mes (`num_cuota` 1..N) con `valor_base = saldo_actual = valor_cuota_ref`,
   `intereses = entrego = 0`, `estado_bancario = null`, `fecha_ultimo_envio = null`.
6. `redirect()->route('creditos.index')->with('success')`.

⚠ **Huecos verificados del alta:** no se guarda fecha de vencimiento por cuota (la exigibilidad futura se deriva
del lote: `presentaciones.numero + cuotas.num_cuota`, ver §3.2/§3.3 — el lote origen ya vive en la FK
`creditos.id_presentacion`, **no** en una columna `presentacion_origen`); la cuota de referencia no contempla mora
ni redondeo de la última cuota (todas iguales ⇒ descuadre de centavos).
✅ **Corregido en esta revisión:**
- `id_autorizado` recibe el ID validado de `cliente_autorizados` (§2.1, §3.1).
- Se validan `cliente.limite_efectivo`, `cliente.max_cuotas`, `cliente.suspendido` y presentación activa (`fecha_fin IS NULL`).
- Tasa de financiación migrada a `presentaciones.interes_credito`.

**Vista previa del alta (`GET /creditos/nuevo?id_cliente=&id_autorizado=` → `create`)**:
`$presentacionActual = session('presentacion_actual') ?? DB::table('presentaciones')->max('id') ?? 102` ⚠ — nadie
escribe `presentacion_actual` (sólo se escribe `sucursal_activa_id` en `SucursalController@updateGlobal`), así que
siempre cae al **max(id)** aunque el resto del sistema numera por `presentaciones.numero` (ej. 500/510). Construye
`$vencimientos[1..4]` agrupando cuotas con saldo por lote
(`presentacionCuota = credito.presentacion.numero + cuota.num_cuota`), descartando las que ya no deben nada
(`saldoPendiente = max(0, valor_base − entrego)`), y calcula `limiteDisponible = max(0, (cliente.limite ?? 800000) − Σ importe_original de créditos abiertos)`.

**Liquidación / cobro (`POST /creditos/{cuota}/pagar`)**
(`CreditoController@pagarCuota`):

```php
$validated = $request->validate([
    'monto' => ['required', 'numeric', 'min:0.01', 'max:' . $cuota->saldo_actual],
    'medio' => ['required', 'string', 'max:50'],
]);

DB::transaction(function () use ($cuota, $monto, $validated) {
    $cuota->update([
        'entrego'      => (float) $cuota->entrego + $monto,
        'fecha_cobro'  => now(),
        'medio_pago'   => $validated['medio'],
        'saldo_actual' => max(0, (float) $cuota->saldo_actual - $monto),
    ]);

    $credito = $cuota->credito;
    if ($credito && ! $credito->cuotas()->where('saldo_actual', '>', 0)->exists()) {
        $credito->update(['fecha_cancelacion' => now()]);
    }
});

return back()->with('success', 'Pago registrado correctamente');
```

✅ **Corregido en esta revisión:** validación de request (`monto` mínimo 0.01 y tope en `saldo_actual`, evitando negativos/cero y sobrepagos), pago acumulativo (`entrego += monto`), saldo no negativo, cierre automático del crédito (`fecha_cancelacion = now()`) al cancelarse la totalidad de sus cuotas, y flash `success` para `<x-notification-modal />`.

**Cancelación:** `CreditoController@pagarCuota` ahora escribe automáticamente `fecha_cancelacion = now()` cuando un crédito salda su última cuota impaga ✅. En reportes y consultas se lee para separar abiertos/cancelados (`creditos.index`, `clientes.estadoCuenta`). Los totales de estado de cuenta se calculan **en memoria** (`$creditosAbiertos->flatMap->cuotas`), no en SQL.

**Transacciones compensatorias (anulación y reversión):**
1. **Anulación de Pago (`POST /creditos/cuotas/{cuota}/anular-pago` → `CreditoController@anularPago`):**
   - Requiere un motivo formal (`min:5`, `max:255`).
   - Verifica que la cuota tenga `entrego > 0` o `fecha_cobro !== null`.
   - Dentro de `DB::transaction`:
     - Repone el monto cobrado al saldo exigible: `saldo_actual += entrego`, resetea `entrego = 0`, `fecha_cobro = null`, `medio_pago = null`.
     - Si el crédito padre estaba cancelado (`fecha_cancelacion !== null`), lo reabre seteando `fecha_cancelacion = null`.
     - Registra una novedad crítica en `cliente_novedades` del titular (`tipo = 0`) detallando número de cuota, crédito, monto revertido, operador responsable y motivo.
2. **Anulación de Crédito (`DELETE /creditos/{credito}/anular` → `CreditoController@anularCredito`):**
   - Requiere motivo formal (`min:5`, `max:255`).
   - Valida estrictamente que **ninguna** cuota del crédito tenga pagos registrados (`entrego > 0`); de lo contrario rechaza la operación exigiendo revertir los pagos primero.
   - En `DB::transaction`: asienta novedad en `cliente_novedades` del titular y ejecuta `$credito->delete()`, eliminando en cascada el plan de cuotas en base de datos.
3. **Auditoría y Rollback de Estado (`AuditoriaController@rollback`):**
   - Integrado con `owen-it/laravel-auditing` en los modelos clave (`Cliente`, `Credito`, `Cuota`, `Presentacion`).
   - Ante eventos `updated`, permite al operador revertir el estado del registro a los valores previos auditados mediante `$audit->auditable->transitionTo($audit, true)` persistido con `$auditable->save()`.

### 3.2 Generación de lote bancario (P10→P0, tope y ranking de cuotas)

**Estado: NO IMPLEMENTADO.** Verificado por búsqueda en `app/` y `routes/`: no existen las cadenas `P10`, `R02`
ni `ACE` en código (sólo el comentario de `Cuota::$fillable`), no hay controlador, servicio, comando, job ni
ruta de "lote"/"rendición", no hay `routes/api.php` y no hay exportador. El botón *EXPORTAR EXCEL* de la vista
`creditos/index` es decorativo (sin `wire:`/`@click`/ruta).

**Piezas ya preparadas en el modelo (base para implementarlo):**

| Pieza | Definición real |
|---|---|
| `Presentacion` = **lote** | `numero` ("Número de lote o presentación, ej. 500, 510"), `fecha_inicio`, `fecha_fin`, **`interes_credito`**, **`interes_mora`** (columnas reales, ya presentes en `$fillable` ✅; `tasa_cuotas`/`tasa_mora` no existen como columnas). Lote abierto ⇔ `fecha_fin IS NULL`. |
| Lote de una cuota | `Cuota::getLoteCalculadoAttribute()` ⇒ `credito.presentacion.numero + cuota.num_cuota` (⭐ **definición canónica**, usada por `CreditoController@create`). `Cuota::getLotePresentacionAttribute()` ⇒ ✅ **DEPRECADO**: ahora delega en `lote_calculado` (antes sumaba el objeto relación a un entero ⇒ error fatal) y sólo se conserva por compatibilidad. |
| Código de gestión | `Cuota::getCodigoGestionAttribute()` ⇒ `credito.full_identificador . num_cuota` (ej. `C0001234TW1`); `Credito::getFullIdentificadorAttribute()` ⇒ `tipo + pad(credito,7,'0') + sucursal` (ej. `C0001234TW`, `C0001234IN`, `R0001234TW`, `C0001234US`). Se usan como clave de envío/conciliación. |
| Campos bancarios de la cuota | `banco_envio`, `fecha_ultimo_envio`, `estado_bancario` (comentado "ACE, R10, R08, etc."), `obs_devolucion`, `referencia_pago`. Ninguno se escribe hoy salvo `fecha_ultimo_envio = null` / `estado_bancario = null` en el alta. |
| Catálogo de estados | Tabla/modelo `EstadoBanco` (`codigo`, `descripcion`), consumida en `clientes.index`; su CRUD (`EstadoBancoController`) está huérfano. No hay FK ni validación `in:` de los códigos usados en `cuotas.estado_bancario`. |

**Tope de presentación (lo único parametrizado hoy):**

- `clientes.limite` (con fallback duro `800000` en `CreditoController@create`) y
  `limiteDisponible = max(0, limite − Σ importe_original de créditos abiertos)`; se muestra en pantalla pero
  **no se valida** en `store`.
- `clientes.max_cuotas ∈ {1,2,3,4}` (validado en el legajo, **no aplicado** al alta de crédito).
- No existe un tope por lote/presentación (máximo de cuotas o importe a presentar), ni ranking por antigüedad de
  décadas: la única priorización existente es el agrupamiento en 4 buckets de `create()` (vencidas/actual, +1, +2, +3).

**Recomendación de diseño (mínima, respetando lo existente):** derivar el lote origen por la FK
`creditos.id_presentacion` (INNER JOIN con `presentaciones`) junto con `Cuota.num_cuota`, ordenar la presentación
por lote ASC y `num_cuota ASC` (equivalente a P10→P0: décadas = lote/10, la más antigua primero) y topar el lote
con dos columnas nuevas en `presentaciones` (ej. `tope_cuotas`/`tope_importe`). ✅ Ya **no** hace falta persistir
`creditos.presentacion_origen`: la definición de lote quedó unificada en `Cuota::lote_calculado` y
`PresentacionController@cambiar` consulta el lote origen con el JOIN correcto (ver §3.3).

### 3.3 Procesamiento de rendiciones y reversas (ACE / R10 / R02, saldo y suspensión)

**Estado: NO IMPLEMENTADO.** No existe controlador ni ruta de rendición/reversa, `DevolucionBancaria` es una clase
vacía y **ningún** punto del código escribe `cuotas.estado_bancario`, `cuotas.banco_envio`, `cuotas.obs_devolucion`,
`clientes.estado_banco` ni `clientes.fecha_estado_banco`. Por lo tanto los estados ACE/R10/R02 sólo existen como
catálogo (`estados_banco`) y como columna libre en `cuotas`.

**Lo único que hoy modifica saldos es `PresentacionController@cambiar` (cierre de lote), que además tiene fallas
críticas verificadas:**

```php
$presentacionActual = Presentacion::whereNull('fecha_fin')->orderBy('numero','desc')->first();
if (!$presentacionActual) return back()->with('error', 'No hay una presentación abierta actualmente.');

$diasTranscurridos = Carbon::parse($presentacionActual->fecha_inicio)->diffInDays(now());
if ($diasTranscurridos < 7) return back()->with('error', "No se puede cerrar. Han pasado solo {$diasTranscurridos} días...");

DB::beginTransaction();
$ahora = now();
$tasaMora = (float) ($presentacionActual->interes_mora ?? $request->interes_mora);  // ✅ cast a float (antes NULL ⇒ SQL inválido)
$presentacionActual->update(['fecha_fin' => $ahora]);                               // 1) cierra lote
Presentacion::create([... 'interes_credito', 'interes_mora' ...]);                  // 2) abre lote n+1 (columnas ya en $fillable ✅)
DB::table('cuotas')->where('saldo_actual','>',0)->update([                          // 3) mora sobre impagas
    'intereses'    => DB::raw("intereses + (saldo_actual * {$tasaMora})"),
    'saldo_actual' => DB::raw("saldo_actual + (saldo_actual * {$tasaMora})"),
]);
DB::table('cuotas')
    ->join('creditos', 'cuotas.id_credito', '=', 'creditos.id')
    ->join('presentaciones', 'creditos.id_presentacion', '=', 'presentaciones.id')  // ✅ lote ORIGEN por FK relacional
    ->whereRaw('(presentaciones.numero + cuotas.num_cuota) = ?', [$nuevaPresentacion->numero])
    ->where('cuotas.saldo_actual','=',0)                                             // 4) vuelve exigibles las del nuevo ciclo
    ->update(['cuotas.saldo_actual' => DB::raw('cuotas.valor_base')]);
DB::commit();
return redirect()->route('sistema.presentaciones.index')->with('success', ...);     // ✅ nombre real
```

| # | Falla detectada | Estado tras la corrección |
|---|---|---|
| 1 | La ruta `sistema.presentaciones.cambiar` era **inalcanzable**: `Route::resource('presentaciones')` registraba `GET presentaciones/{presentacion}` (show) antes, y `cambiar` quedaba capturado como `{presentacion}` ⇒ llamaba a `PresentacionController@show`, que **no existe**. | ✅ **CORREGIDO**: la ruta es `POST /sistema/presentaciones/cambiar`, declarada **antes** del listado, y el `Route::resource('presentaciones')` fue sustituido por la ruta explícita `GET /sistema/presentaciones`. El cierre de lote es alcanzable. |
| 2 | Estaba declarada como **GET con efectos de escritura** (sin CSRF, sin confirmación server-side). | ✅ **CORREGIDO** (POST + CSRF). La confirmación `onsubmit` de la vista sigue vigente. *Pendiente: autorización server-side por permiso/rol.* |
| 3 | `interes_credito` / `interes_mora` no estaban en `Presentacion::$fillable` ⇒ el `create()` descartaba las tasas y el lote quedaba con tasas `NULL`. | ✅ **CORREGIDO**: ambas columnas reales fueron agregadas a `$fillable`. ⚠ *Pendiente: `CreditoController@store` sigue leyendo `tasa_cuotas` (que no existe) ⇒ interés de financiación = 0; debe migrarse a `interes_credito`.* |
| 4 | `$presentacionActual->interes_mora` devolvía `null` y se interpolaba en `DB::raw("... * {$tasaMora})")` ⇒ `(saldo_actual * )` (SQL inválido) ⇒ excepción y `rollBack()`. | ✅ **CORREGIDO**: `$tasaMora = (float) ($presentacionActual->interes_mora ?? $request->interes_mora)`. La mora se aplica siempre. |
| 5 | La exigibilidad usaba `creditos.presentacion_origen` (**columna inexistente**). | ✅ **CORREGIDO**: INNER JOIN `presentaciones` por `creditos.id_presentacion` + `(presentaciones.numero + cuotas.num_cuota) = ?`. |
| 6 | `redirect()->route('presentaciones.index')` apuntaba a un nombre inexistente ⇒ error 500 **después** del `commit()`. | ✅ **CORREGIDO**: `redirect()->route('sistema.presentaciones.index')`. |
| 7 | `$tasaMora` se toma del lote que se cierra, no del nuevo; los parámetros validados (`interes_mora`) no se usaban. | ⚠ **PENDIENTE (decisión de negocio)**: se mantiene la tasa del lote que se cierra, con respaldo en `$request->interes_mora`. Queda documentado que la mora del período cerrado usa la tasa vigente de ese período. |

**Definición de lote (unificada ✅):** lote = `presentaciones.numero` del **lote origen** (FK relacional
`creditos.id_presentacion`) `+ Cuota.num_cuota`, con cuotas numeradas desde 1. Es exactamente la definición del
accesor oficial `Cuota::lote_calculado`, de modo que el código, la consulta de cierre y las especificaciones
(`models.md` §2.1.1 y §3.4) quedan alineados. **No existe** (ni se requiere) la columna `creditos.presentacion_origen`.

**Reversas y suspensión preventiva — diseño sugerido (no existe código):** al recibir una rendición, mapear el
código devuelto por el banco a `cuotas.estado_bancario` (`codigo` de `estados_banco`); una reversa debería reponer
el saldo (`saldo_actual += entrego`, `entrego = 0`, limpiar `fecha_cobro`/`medio_pago`), guardar el motivo en
`obs_devolucion` y, según política (ej. N reversas en M lotes), setear `clientes.suspendido = 1` junto con
`clientes.estado_banco` + `fecha_estado_banco` — que son exactamente los campos que `ClienteController@store`
fuerza a `null` y el filtro de `clientes.index` ya sabe leer.

## 4. Resumen de Riesgos Priorizados

### 4.1 Corregido en esta revisión (detalle en §0)

- **12 rutas muertas** que respondían 500 (clientes.creditos/movimientos, creditos.edit/update/destroy,
  creditos.cuotas.*, sucursales.cambiar y endpoints del resource de presentaciones) — eliminadas.
- **Cierre de lote operativo** (§3.3): ruta alcanzable (POST con CSRF), tasas en `$fillable`, mora con SQL válido,
  JOIN al lote origen por `id_presentacion` y redirect al nombre correcto.
- **`id_autorizado` con FK coherente** (`cliente_autorizados.id`) y cadena de vinculación completa
  (`clientes.index` → `creditos.create` → `store`).
- **`pagarCuota` con flash `success`**, compatible con `<x-notification-modal />`.
- **`POST /cambiar-sucursal` protegido con `auth`**; `GET /login` lo sirve el controlador Breeze (el closure de
  `web.php` quedó como código muerto).
- **`shadow-2xs` → `shadow-sm`** en `clientes/show.blade.php:226` y `creditos/create.blade.php:214` (Tailwind v3).
- **Definición de lote unificada** (`Cuota::lote_calculado`; `lote_presentacion` deprecado) y documentada en
  `models.md` §2.1.1/§3.4.
- **Alta de crédito con controles de límite y validaciones de negocio** (§2.1, §3.1): valida presentación vigente/abierta
  (`fecha_fin IS NULL`), cliente titular no suspendido (`!$cliente->suspendido`), límite de cuotas
  (`cantidad_cuotas <= cliente.max_cuotas`) y límite crediticio disponible (`deudaAcumulada + importe_original <= cliente.limite_efectivo`).
- **Cobro de cuotas consistente y auto-cierre de crédito** (`pagarCuota`, §2.1, §3.1): validación explícita de `monto`
  (`min:0.01`, `max:saldo_actual`) y `medio` (`string|max:50`), entrega acumulativa (`entrego += monto`), saldo neto
  con piso en cero (`saldo_actual = max(0, saldo_actual - monto)`) y cierre atómico del crédito (`fecha_cancelacion = now()`)
  cuando no restan cuotas con saldo exigible.
- **Tasa de financiación real del lote** (`store`, §2.1, §3.1): migrada de `presentacion->tasa_cuotas` (columna inexistente
  que resultaba en 0% de interés) a la columna real `presentaciones.interes_credito`.

### 4.2 Riesgos pendientes priorizados

1. **Sin vencimiento explícito por cuota**: la exigibilidad depende sólo del lote
   (`presentaciones.numero + cuotas.num_cuota`).
2. **Autorización**: sólo `auth`; no hay permisos/roles verificados en backend (incluido el cierre de lote, que la
   vista condiciona por permiso pero el servidor no valida).
3. **Código huérfano**: `EstadoBancoController` sin rutas (con `redirect()->route('estados-banco.index')` inexistente),
   `DevolucionBancaria` vacío, `resources/views/sistema/presentaciones/cambiar.blade.php` vacío, botón EXPORTAR EXCEL
   decorativo, `SucursalController` con stubs de resource y closure `/login` muerto en `web.php`.

**Sin implementar (y sin andamiaje):** generación de lote bancario P10→P0, tope por presentación, ranking de cuotas
por décadas, procesamiento de rendiciones y reversas ACE/R10/R02, imputación estricta de cobranza a intereses antes que capital, suspensión preventiva automática y `routes/api.php`.


---

## 5. Módulo Ventas / Facturación (POS) & Control de Stock

### 5.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/ventas` | `ventas.index` | `VentaController@index` | `auth` | Listado histórico de ventas con KPIs y filtros |
| GET | `/ventas/crear` | `ventas.create` | `VentaController@create` | `auth` | Terminal de Facturación / Punto de Venta (POS) |
| POST | `/ventas` | `ventas.store` | `VentaController@store` | `auth` | Emisión atómica de factura y descuento de stock |
| GET | `/ventas/buscar-producto` | `ventas.buscar-producto` | `VentaController@buscarProducto` | `auth` | Búsqueda por código, barra o descripción con stock local |
| POST | `/ventas/ajuste-recuento-rapido` | `ventas.ajuste-recuento-rapido` | `VentaController@ajusteRecuentoRapido` | `auth` | Regularización inmediata de stock físico en mostrador |
| GET | `/ventas/clientes/{cliente}/info-credito` | `ventas.cliente-info-credito` | `VentaController@infoCreditoCliente` | `auth` | Consulta de disponible, cuotas y mora del cliente |
| GET | `/ventas/{venta}` | `ventas.show` | `VentaController@show` | `auth` | Detalle de comprobante emitido |
| GET | `/ventas/{venta}/imprimir` | `ventas.imprimir` | `VentaController@imprimir` | `auth` | Impresión de ticket térmico 80mm o factura A4 |
| POST | `/ventas/{venta}/anular` | `ventas.anular` | `VentaController@anular` | `auth` | Anulación de venta, restitución de stock y anulación de crédito |

### 5.2 Reglas de Negocio en Facturación (POS)
1. **Obligatoriedad de Caja Abierta:** Antes de procesar cualquier venta, `VentaController@store` valida que exista una sesión de caja abierta para la sucursal activa (`CajaSesion::activaParaSucursal($sucursalId)`). Si no hay caja abierta o quedó una caja de jornada anterior sin cerrar, la venta se bloquea retornando HTTP 422 con flag `requiere_apertura = true`.
2. **Prevención de Stock Negativo:**
   - Todo producto con `controla_stock = true` es verificado contra `productos_stock` de la sucursal emisora.
   - Si el stock disponible es menor a la cantidad solicitada, el sistema no emite la factura y alerta al operador.
   - **Corrección Interactiva por Recuento Físico:** El modal del facturador permite ingresar la cantidad real contada en góndola/mostrador mediante `ventas.ajuste-recuento-rapido`. Esto registra un `StockMovimiento` de tipo `recuento_fisico` y habilita la facturación en el instante.
3. **Facturación con Crédito Personal:**
   - Si el medio de pago es `credito_personal`, se exige cliente registrado.
   - Se valida que el cliente no esté suspendido ni exceda su límite de crédito disponible ni su cuota máxima.
   - Crea automáticamente el registro en `creditos` y sus `cuotas` correspondientes asociadas al número de factura.

---

## 6. Módulo Control de Cajas, Turnos, Arqueos y Cierre Z

### 6.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/cajas` | `cajas.index` | `CajaController@index` | `auth` | Panel principal de control de turnos y Cierres Z |
| GET | `/cajas/estado-actual` | `cajas.estado-actual` | `CajaController@estadoActual` | `auth` | Endpoint JSON con el estado de la caja de la sucursal activa |
| POST | `/cajas/abrir` | `cajas.abrir` | `CajaController@abrir` | `auth` | Apertura de caja e ingreso del fondo inicial en efectivo |
| POST | `/cajas/cerrar-anterior-y-abrir` | `cajas.cerrar-anterior-y-abrir` | `CajaController@cerrarAnteriorYAbrir` | `auth` | Arqueo automático de caja olvidada y apertura de la nueva |
| GET | `/cajas/reportes/historico-medios` | `cajas.historico-medios` | `CajaController@historicoMedios` | `auth` | Reporte consolidado histórico por medios de pago |
| GET | `/cajas/{caja}` | `cajas.show` | `CajaController@show` | `auth` | Detalle integral de una jornada: medios de pago, arqueo, movimientos y ventas |
| GET | `/cajas/{caja}/cerrar` | `cajas.cerrar` | `CajaController@cerrarForm` | `auth` | Pantalla de Arqueo Físico y Cierre Z |
| POST | `/cajas/{caja}/cerrar` | `cajas.cerrar.store` | `CajaController@cerrar` | `auth` | Confirmación de Cierre Z y cálculo de fallo de caja |
| GET | `/cajas/{caja}/comprobante` | `cajas.comprobante` | `CajaController@comprobante` | `auth` | Comprobante imprimible de Cierre Z (Ticket 80mm y A4) |
| POST | `/cajas/{caja}/movimientos` | `cajas.movimientos.store` | `CajaController@storeMovimiento` | `auth` | Registro de ingreso o retiro manual con motivo obligatorio |
| GET | `/cajas/{caja}/movimientos` | `cajas.movimientos.json` | `CajaController@movimientosJson` | `auth` | Historial JSON de movimientos de la sesión activa |

### 6.1.1 Comandos de Consola y Migración de Datos Históricos
- `php artisan app:import-cajas-mdb`: Lee tablas `CAJA`, `MOVIMIENTO` y `MOVIMIENTO_PAGO` desde `db.mdb` (Access COM/OLEDB), genera las sesiones de caja con apertura, cierre, desglose de medios de pago, e inserta movimientos de caja.
- `php artisan app:export-cajas-sql`: Exporta las tablas `cajas_sesiones` y `cajas_movimientos` a `database/data/cajas_historicas.sql` (1.8 MB) para su despliegue en servidores Linux que no cuentan con drivers de MS Access.
- `php artisan app:import-cajas-sql`: Ejecuta el volcado SQL en el servidor de producción y vincula 137.000+ ventas existentes con sus respectivas sesiones de caja de forma indexada en ~10 segundos.

### 6.2 Flujo Operativo y Fórmulas de Arqueo
1. **Apertura de Turno:**
   - Al comenzar el día, el operador registra el `monto_inicial_efectivo` (fondo de cambio).
   - Queda registrada la fecha, hora y el usuario que abrió la caja (`id_usuario_apertura`).
2. **Control de Jornada Anterior Pendiente:**
   - Si quedó una caja abierta de días previos (`!$caja->esDeHoy()`), el sistema bloquea el facturador obligando a efectuar el arqueo y cierre de la jornada anterior antes de permitir ventas nuevas.
3. **Movimientos Manuales Justificados (Ingresos y Retiros):**
   - **Retiros / Egresos:** Para pagos menores, compras de insumos, fletes o retiro de valores. Requiere obligatoriamente `motivo` justificado y `monto > 0`.
   - **Ingresos:** Para aporte adicional de sencillo o cobros pendientes manuales.
   - Ambos flujos se auditan con usuario, fecha, comprobante opcional y motivo visible en el Cierre Z.
4. **Fórmula de Efectivo Teórico Esperado:**
   $$\text{Efectivo Teórico} = \text{Fondo Inicial} + \text{Ventas Efectivo} + \text{Ingresos Manuales} - \text{Retiros Manuales}$$
5. **Cierre Z y Fallo de Caja:**
   - El cajero cuenta el dinero físico billete por billete (`monto_final_efectivo_real`).
   - El sistema calcula:
     $$\text{Diferencia} = \text{Efectivo Real Contado} - \text{Efectivo Teórico Esperado}$$
   - Si $\text{Diferencia} > 0 \rightarrow$ Sobrante de Caja.
   - Si $\text{Diferencia} < 0 \rightarrow$ Faltante de Caja (Fallo de Caja).
6. **Comprobante Oficial de Cierre Z:**
   - Emite documento imprimible en formato ticket térmico de 80mm y en hoja A4.
   - Incluye membrete fiscal de la sucursal, rango horario, operador de apertura y cierre, primer y último ticket de la jornada, totalizadores discriminados por medio de pago, detalle de movimientos manuales con motivo, y arqueo final.

---

## 7. Módulo Copias de Seguridad (Backup y Restauración)

### 7.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Permiso Requerido |
|---|---|---|---|---|---|
| GET | `/sistema/backup` | `sistema.backup.index` | `BackupController@index` | `auth` | Cualquier usuario logueado |
| POST | `/sistema/backup` | `sistema.backup.create` | `BackupController@create` | `auth` | Cualquier usuario logueado |
| GET | `/sistema/backup/{filename}/download` | `sistema.backup.download` | `BackupController@download` | `auth` | Cualquier usuario logueado |
| POST | `/sistema/backup/restore` | `sistema.backup.restore` | `BackupController@restore` | `auth` | **Exclusivo Admin / Superuser** |

### 7.2 Protocolo de Seguridad en Copias de Seguridad
- **Generación de Respaldo:** `BackupService` genera un volcado SQL íntegro de la base de datos MySQL (estructura y datos completos), empaquetado y comprimido en formato `.zip` dentro de `storage/app/backups`.
- **Restauración Protegida con Auto-Backup Preventivo:**
  - Solo los usuarios administradores o el `superuser` tienen autorización para restaurar.
  - Antes de ejecutar cualquier sobreescritura de la base de datos, el sistema genera automáticamente un respaldo preventivo de emergencia (`pre_restore_backup_*.zip`).
  - Si la restauración falla o se interrumpe, la base de datos puede reponerse inmediatamente desde el archivo preventivo generado.

---

## 8. Seguridad Transversal, Autenticación y Despliegue en Servidor

### 8.1 Blindaje de Acceso y Login Seguro
- Se removió por completo de la vista de login (`auth/login.blade.php`) cualquier lista desplegable, sugerencia o selector de usuarios y contraseñas.
- El ingreso al sistema exige credenciales auténticas (`username` y contraseña Bcrypt).
- Todas las vistas y APIs del sistema (`/sistema/*`, `/ventas/*`, `/cajas/*`, `/recibos/*`, `/clientes/*`, `/creditos/*`) se encuentran bajo middleware `auth`.

### 8.2 Aislamiento de Superusuario (`superuser`)
- Se configuró la cuenta especial de rescate `superuser`.
- La lista de administración de usuarios filtra y oculta automáticamente al `superuser` para evitar que usuarios comunes detecten su existencia o modifiquen su perfil.
- Solo el `superuser` y administradores acreditados pueden ejecutar restauraciones de bases de datos.

### 8.3 Forzado de HTTPS en Producción
- En `app/Providers/AppServiceProvider.php`, se configuró la directiva de seguridad:
  ```php
  if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
      URL::forceScheme('https');
  }
  ```
- Garantiza que todas las URLs, formularios `@csrf`, scripts y hojas de estilo se sirvan con protocolo seguro HTTPS en el servidor de producción (`https://app.tiendaslamarina.com.ar`).

---

## 9. Módulo Medios de Pago (ABM Consolidado)

### 9.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/sistema/medios-pago` | `sistema.medios-pago.index` | `MedioPagoController@index` | `auth` | Listado consolidado con conteo de movimientos |
| GET | `/sistema/medios-pago/crear` | `sistema.medios-pago.create` | `MedioPagoController@create` | `auth` | Formulario de alta de nuevo medio |
| POST | `/sistema/medios-pago` | `sistema.medios-pago.store` | `MedioPagoController@store` | `auth` | Valida y almacena un nuevo medio de pago |
| GET | `/sistema/medios-pago/{medioPago}/editar` | `sistema.medios-pago.edit` | `MedioPagoController@edit` | `auth` | Formulario de edición y configuración |
| PUT | `/sistema/medios-pago/{medioPago}` | `sistema.medios-pago.update` | `MedioPagoController@update` | `auth` | Actualiza propiedades, recargos y colores |
| POST / PATCH | `/sistema/medios-pago/{medioPago}/toggle-activo` | `sistema.medios-pago.toggle-activo` | `MedioPagoController@toggleActivo` | `auth` | Habilita o inhabilita rápidamente el medio |
| DELETE | `/sistema/medios-pago/{medioPago}` | `sistema.medios-pago.destroy` | `MedioPagoController@destroy` | `auth` | Eliminación segura (solo si tiene 0 movimientos) |

### 9.2 Lógica de Negocio y Reglas de Integridad
1. **Regla de Eliminación Segura:**
   - La acción `destroy` computa `$totalMovs = $cantVentas + $cantCuotas + $cantRecibos`.
   - Si `$totalMovs > 0`, la petición es rechazada con código de redirección y mensaje flash de advertencia detallando exactamente cuántas ventas, pagos de cuotas y recibos dependen de dicho registro.
   - Si `$totalMovs === 0`, el registro se elimina de forma limpia.
2. **Activación / Desactivación Rápida:**
   - Para impedir el cobro mediante un medio con historial sin romper la integridad referencial, el usuario puede alternar `activo = false` mediante `toggle-activo`.
   - Los medios inactivos no se renderizan en el mostrador POS ni en los selectores de cobranza, pero sus estadísticas históricas continúan disponibles en el Resumen de Caja y reportes.
3. **Exportación e Importación:**
   - Se actualizó `app:export-cajas-sql` para exportar la tabla `medios_pago` junto a las sesiones de caja y movimientos hacia `database/data/cajas_historicas.sql`.

---

## 10. Módulo Catálogo, Variantes (Talle y Color), Recepción Ágil y Código de Barras (CODE128)

### 10.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/productos` | `productos.index` | `ProductoController@index` | `auth` | Catálogo maestro unificado con filtros por talle, color, proveedor y stock |
| GET | `/productos/nuevo` | `productos.create` | `ProductoController@create` | `auth` | Pantalla ágil de recepción de mercadería y alta de producto |
| POST | `/productos` | `productos.store` | `ProductoController@store` | `auth` | Almacena nuevo producto unificando código interno y código de barras CODE128 |
| GET | `/productos/buscar-por-proveedor` | `productos.buscar-por-proveedor` | `ProductoController@buscarPorProveedor` | `auth` | Búsqueda dinámica de artículos por proveedor, código fábrica o descripción (AJAX) |
| GET | `/productos/verificar-codigo` | `productos.verificar-codigo` | `ProductoController@verificarCodigo` | `auth` | Verificación en tiempo real de disponibilidad del código CODE128 (AJAX) |
| POST | `/productos/ingreso-mercaderia` | `productos.ingreso-mercaderia` | `ProductoController@ingresoMercaderiaRapido` | `auth` | Ingreso ultra-rápido: suma stock si el código existe o da de alta nueva variante automáticamente |
| POST | `/productos/ingreso-stock-rapido` | `productos.ingreso-stock-rapido` | `ProductoController@ingresoStockRapido` | `auth` | Ingreso de unidades a artículo existente y disparo de impresión térmica |
| GET | `/productos/traslado` | `productos.traslado` | `StockMovimientoController@trasladoForm` | `auth` | Formulario de traslado entre sucursales con edición de variantes |
| POST | `/productos/traslado` | `productos.traslado.store` | `StockMovimientoController@trasladoStore` | `auth` | Registra traslado, actualiza talle/color/fábrica y regenera barcode opcional |
| GET | `/productos/buscar-traslado` | `productos.buscar.traslado` | `StockMovimientoController@buscarParaTraslado` | `auth` | Búsqueda asistida de productos para traslado |
| POST | `/productos/verificar-stock-traslado` | `productos.verificar.stock.traslado` | `StockMovimientoController@verificarStock` | `auth` | Validación de disponibilidad física en sucursal origen |
| GET | `/productos/movimientos` | `productos.movimientos` | `StockMovimientoController@index` | `auth` | Historial de auditoría de movimientos y reemisión de etiquetas |
| GET | `/productos/{producto}` | `productos.show` | `ProductoController@show` | `auth` | Ficha técnica del producto, stock multi-sucursal y variantes |
| GET | `/productos/{producto}/editar` | `productos.edit` | `ProductoController@edit` | `auth` | Formulario de edición de datos maestros del producto |
| PUT | `/productos/{producto}` | `productos.update` | `ProductoController@update` | `auth` | Actualización de datos maestros y variantes |
| DELETE | `/productos/{producto}` | `productos.destroy` | `ProductoController@destroy` | `auth` | Eliminación lógica (SoftDeletes) |
| POST | `/productos/{producto}/ajuste` | `productos.ajuste` | `ProductoController@ajusteStock` | `auth` | Ajuste directo de existencias físicas por recuento |
| GET | `/productos/{producto}/etiqueta` | `productos.etiqueta` | `ProductoController@imprimirEtiqueta` | `auth` | Generación de etiqueta térmica (50×25mm) en SVG CODE128 con `autoprint` |

### 10.2 Flujo Operativo de Recepción Ágil en `/productos/nuevo`
1. **Unificación de Código:** No existe código interno independiente. Se utiliza el código de barras en simbología CODE128 como identificador maestro (`codigo = codigo_barra`).
2. **Selección del Proveedor:** Al seleccionar el proveedor (o darlo de alta en el modal emergente asistido), su legajo define los primeros 3 dígitos del código (`prov(3)`).
3. **Búsqueda Dinámica:** Filtra en tiempo real los artículos cargados del proveedor por código de fábrica, código de barra, descripción, talle o color.
4. **Bifurcación del Flujo:**
   - **Artículo Existente:** Si la prenda ya existe en el catálogo, se cargan las unidades que ingresan físicamente, se actualiza el stock en la sucursal activa y, si está tildada la opción, se imprimen automáticamente las etiquetas térmicas con `autoprint=1`.
   - **Nuevo Artículo:** Si no existe, se cargan descripción, código de fábrica, talle (2 caracteres normalizados) y color (2 caracteres normalizados). El visor en tiempo real compone el código `prov(3) + talle(2) + color(2) + fab(...)`, verifica que no esté asignado a otra prenda, registra el artículo con stock inicial e imprime las etiquetas.

### 10.3 Traslados Inter-Sucursales y Regeneración de Etiquetas
1. Al trasladar mercadería entre sucursales, el operador puede completar variantes faltantes (`codigo_proveedor`, `talle`, `color`).
2. Puede optar por **mantener el código original** o marcar **"Generar / Actualizar Código de Barra (CODE128)"** para recalcular la estructura.
3. Si el código de barras cambia, el sistema emite una notificación destacada en la pantalla de movimientos con acceso directo para reimprimir las etiquetas térmicas de las prendas trasladadas.

### 10.4 Comando de Consolidación e Importación de Proveedores
- **Firma:** `php artisan app:consolidar-proveedores-productos`
- **Funcionalidad:**
  1. Extrae de `creditos/db.mdb` (tabla `PERSONAS`) los proveedores faltantes con saneamiento de caracteres UTF-8 e inserción directa en `proveedores`.
  2. Lee la tabla `PRODUCTOS` de Access y actualiza en un solo paso masivo indexado el campo `proveedor_id` de los 15.138 productos en MySQL `lamarina_v4`.

---

## 11. Módulo Puntos de Venta y Facturación Híbrida (Hasar 2G vs ARCA WSFE)

### 11.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/sistema/puntos-venta` | `sistema.puntos-venta.index` | `PuntoVentaController@index` | `auth` | Data-Grid compacta con filtros por sucursal y tipo de emisión |
| GET | `/sistema/puntos-venta/create` | `sistema.puntos-venta.create` | `PuntoVentaController@create` | `auth` | Formulario reactivo Alpine.js para alta de punto de venta |
| POST | `/sistema/puntos-venta` | `sistema.puntos-venta.store` | `PuntoVentaController@store` | `auth` | Almacena punto de venta con validaciones compuestas y carga de certificados ARCA |
| GET | `/sistema/puntos-venta/{puntoVenta}/edit` | `sistema.puntos-venta.edit` | `PuntoVentaController@edit` | `auth` | Formulario de edición con precarga y prueba de conexión ARCA |
| PUT | `/sistema/puntos-venta/{puntoVenta}` | `sistema.puntos-venta.update` | `PuntoVentaController@update` | `auth` | Actualización de datos maestros, certificados y parámetros técnicos |
| DELETE | `/sistema/puntos-venta/{puntoVenta}` | `sistema.puntos-venta.destroy` | `PuntoVentaController@destroy` | `auth` | Eliminación de punto de venta |
| GET | `/sistema/puntos-venta/{puntoVenta}/descargar-config` | `sistema.puntos-venta.descargar-config` | `PuntoVentaController@descargarConfig` | `auth` | Descarga de archivo `config.json` listo para el puente local Windows |
| POST | `/sistema/puntos-venta/{puntoVenta}/probar-arca` | `sistema.puntos-venta.probar-arca` | `PuntoVentaController@probarArca` | `auth` | Prueba en vivo de autenticación WSAA y estado de servidores WSFE |
| POST | `/sistema/puntos-venta/seleccionar-terminal` | `sistema.puntos-venta.seleccionar-terminal` | `PuntoVentaController@seleccionarTerminal` | `auth` | Persiste en sesión el punto de venta activo del navegador/terminal |
| GET | `/sistema/puntos-venta/activos-sucursal` | `sistema.puntos-venta.activos-sucursal` | `PuntoVentaController@activosPorSucursal` | `auth` | Listado JSON de bocas activas de la sucursal para el selector de cabecera |

### 11.2 Métodos y Lógica de Negocio

#### `index(Request $request)`
- Filtra por `id_sucursal` y `tipo_emision` (`fiscal_hasar` / `arca_wsfe`).
- Eager loading de `sucursal`.
- Ordena por `id_sucursal` y `numero`.
- Paginación: 15 registros por página conservando parámetros (`withQueryString()`).

#### `store(Request $request)` / `update(Request $request, PuntoVenta $puntoVenta)`
- Valida regla de unicidad compuesta: `Rule::unique('puntos_venta')->where(fn($q) => $q->where('id_sucursal', $request->id_sucursal))`.
- Si `tipo_emision === 'fiscal_hasar'`:
  - `hasar_ip`: `required|ipv4`
  - `hasar_puerto`: `required|integer|min:1|max:65535`
  - `puerto_local`: `required|integer|min:1024|max:65535`
  - `modelo`: `nullable|string|max:50` (default `SMH/PT-250F`)
  - Genera JSON en columna `configuracion`.
- Si `tipo_emision === 'arca_wsfe'`:
  - `ambiente`: `required|in:homologacion,produccion`
  - `concepto`: `nullable|integer|in:1,2,3` (default `1`)
  - Genera JSON en columna `configuracion`.

#### `descargarConfig(PuntoVenta $puntoVenta)`
- Valida que `tipo_emision === 'fiscal_hasar'`.
- Construye el payload:
  ```json
  {
    "punto_venta_id": 1,
    "punto_venta_numero": 3,
    "sucursal": "Trelew - Centro",
    "puerto_local": 8585,
    "hasar_ip": "192.168.1.150",
    "hasar_puerto": 2000,
    "modelo": "SMH/PT-250F"
  }
  ```
---

## 12. Módulo de Ventas, Facturación y Descuentos (`VentaController`)

### 12.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/ventas` | `ventas.index` | `VentaController@index` | `auth` | Historial completo de ventas con filtros avanzados y orden interactivo |
| GET | `/ventas/crear` | `ventas.create` | `VentaController@create` | `auth` | Punto de Venta (POS Mostrador) para escaneo y cobro ágil |
| POST | `/ventas` | `ventas.store` | `VentaController@store` | `auth` | Registro de venta atómica, persistencia de `descuento`, stock y crédito |
| GET | `/ventas/{venta}` | `ventas.show` | `VentaController@show` | `auth` | Detalle modal/completo de venta con ítems y pagos |
| GET | `/ventas/exportar` | `ventas.exportar` | `VentaController@exportar` | `auth` | Exportación a Excel con respeto estricto de filtros aplicados y orden |

### 12.2 Descuentos por Artículo, Promociones 2x1 y Descuento Global
- **Descuentos por Renglón (`venta_items.descuento_porcentaje` y `venta_items.descuento`):**
  - Cada ítem permite definir un porcentaje de descuento (0% a 100%).
  - **Promociones 2x1:** Botón de un clic `[2x1]` para bonificar el 100% de la prenda y botón `[+2x1]` para duplicar como unidad bonificada con decremento de stock y subtotal $0,00.
  - El subtotal de la línea refleja el monto neto: $\text{subtotal} = (\text{cantidad} \times \text{precio\_unitario}) - \text{descuento}$.
- **Descuento Global en Factura (`ventas.descuento`):**
  - El facturador POS (`ventas/crear`) permite aplicar un descuento comercial adicional sobre el total del comprobante.
  - Persiste en `ventas.descuento` (equivalente a `MOVIMIENTO.descuento` de Access).
- **Cálculo Financiero:** $\text{Total Neto} = \text{Subtotal} + \text{Recargo} - \text{Descuento}$. Si la venta se abona con Crédito Personal, las cuotas se generan sobre el saldo neto resultante.
- **Comprobantes e Impresión:** Se visualiza discriminado en mostrador, en el visor de registradora, en el ticket térmico y en la factura fiscal A4.

---

## 13. Módulo de Métricas y Tableros BI (`MetricasController`)

### 13.1 Tabla de Rutas

| Método | URI | Name | Controlador@Método | Middleware | Descripción |
|---|---|---|---|---|---|
| GET | `/metricas` | `metricas.index` | `MetricasController@index` | `auth` | Tablero ejecutivo estilo PowerBI con KPIs, slicers de ventas y créditos |
| GET | `/metricas/exportar-excel` | `metricas.exportarExcel` | `MetricasController@exportarExcel` | `auth` | Exportación analítica tabular a Excel/CSV con filtros de período y sucursal |

### 13.2 Lógica de Negocio y Métricas
- **Filtros Temporales:** Rango `fecha_desde` y `fecha_hasta` con presets inmediatos (*Hoy*, *Últimos 7 días*, *Este Mes*, *Mes Anterior*, *Año Actual*, *Histórico Total*).
- **Métricas de Ventas:**
  - Facturación bruta, total de descuentos concedidos y cobranza neta.
  - Cantidad total de prendas/artículos comercializados y ticket promedio.
  - Tablas resumen multidimensionales: por Categoría, Subcategoría, Fabricante/Proveedor, Sucursal, Medio de Pago y Tipo de Comprobante.
- **Métricas de Cartera de Créditos:**
  - Total valor base otorgado, total en cuotas con interés, total efectivamente cobrado, deuda remanente exigible y ratio de recupero (%).
  - Filtro por fecha de emisión y por sucursal.

---

## 14. Estándar Transversal: Click-to-Sort y Exportación Fiel a Excel

### 14.1 Ordenamiento Interactivo (Click-to-Sort)
Implementado de forma homogénea en todos los controladores de listado del sistema:
- `VentaController`, `ClienteController`, `CreditoController`, `CajaController`, `ReciboController`, `ProveedorController`, `RemitoProveedorController`, `ProductoController`, `MetricasController`, `AuditoriaController`, `SucursalController`, `MedioPagoController`, `PuntoVentaController`.
- **Alternancia:** Al pulsar el encabezado, conmuta entre `asc` y `desc`.
- **Preservación:** Mediante `request()->query()`, se conservan intactos todos los parámetros de búsqueda, fechas, sucursales y filtros.
- **Paginación Segura:** Reseteo a `page=1` en cada cambio de columna para evitar páginas inexistentes.

### 14.2 Exportación a Excel con Filtros Activos
- **Regla Estricta:** La descarga refleja con exactitud el resultado de la consulta visible en pantalla.
- **Backend:** Clonación de consulta (`clone $query`) antes de paginar, aplicando idénticas cláusulas `WHERE`, `JOIN` y `ORDER BY`.
- **Formato:** Archivos CSV delimitados por punto y coma (`;`), cabeceras en español y cabecera BOM UTF-8 (`\xEF\xBB\xBF`) para compatibilidad nativa e instantánea con Microsoft Excel.
