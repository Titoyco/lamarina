# Especificación de Diseño Visual y UI: Data-Grid Operativo

Sistema **LaMarina Créditos** — Laravel + Blade + Tailwind CSS v3 + Alpine.js.

Este documento define los lineamientos visuales, componentes y estándares de diseño del frontend.
Su objetivo es maximizar la densidad de información, eliminar espacios en blanco innecesarios y
optimizar el rendimiento diario de los operadores de caja y administración (estilo Excel / PowerBI).

**Fuente de verdad:** `resources/views/**/*.blade.php`. Todo lo que figura acá fue verificado contra
el código real. Si una vista contradice este documento, la vista manda y este documento se corrige.

---

## 0. Correcciones aplicadas en esta revisión

La versión anterior de este documento describía patrones que **no existen en el código**. Detalle:

| Afirmación anterior | Realidad verificada en `resources/views` |
|---|---|
| Cliente suspendido con `bg-[#ffa086]` | No existe en ninguna vista. Se usa `bg-rose-50/60 hover:bg-rose-100/80` en la fila y el chip `bg-rose-700 text-white` (`clientes/index.blade.php:97,112`). |
| `shadow-2xs` en la cinta de KPIs | El proyecto usa **Tailwind v3.4.19** (ver §1.2); `shadow-2xs` es de Tailwind v4 y **no se compila** (`public/build/assets/*.css` no contiene `.shadow-2xs`). Lo correcto es `shadow-sm`. ✅ **Corregido en esta revisión**: las 2 ocurrencias (`clientes/show.blade.php:226`, `creditos/create.blade.php:214`) ya usan `shadow-sm`. |
| `hover:bg-blue-50/60` en filas | Real: `hover:bg-blue-50/40` (`clientes/index`, `creditos/index`, `creditos/show`, `clientes/estado-cuenta`). |
| `hover:brightness-95` en filas suspendidas | No existe en ninguna vista. |
| Encabezado de tabla `text-[11px]` | Real: `text-[10px]` + `bg-slate-100 text-slate-700 border-b border-slate-300`. |
| Cinta KPI `md:grid-cols-5` con tarjetas `shadow-2xs` | Real: `grid grid-cols-2 lg:grid-cols-5 gap-2 mb-4`, tarjetas `bg-white p-2 rounded-md border border-slate-200 shadow-sm`, label `text-[9px]` (`clientes/estado-cuenta.blade.php:27-48`). |
| Primario corporativo = `blue-900` / `blue-950` para botones | Header = `bg-blue-800`; botones de acción = `bg-blue-600 hover:bg-blue-700`; `blue-900/950` se reserva para la tarjeta KPI "A Vencer", cabecera "Control Operativo" y títulos legacy. |
| Divisores con `divide-x` en las filas | Real: `divide-y divide-slate-200` en `<tbody>` y `border-r border-slate-200/60` en cada `<td>`. |
| `max-w-xs truncate` en celdas de texto | Real: anchos fijos (`w-24`, `w-28`, `w-32`, `max-w-[150px]`) + `truncate` con `title="..."`. |
| (No se documentaban) | Formularios, layout maestro, toasts, paginador, empty states, módulo de novedades, layout de 2 columnas y documento imprimible (pagaré). Se agregan en §5 a §13. |

**Correcciones de código que impactan la UI (aplicadas en la misma revisión):**

- ✅ `CreditoController@pagarCuota` devolvía `back()->with('status', 'Pago registrado correctamente')`; el toast
  global (`<x-notification-modal />`, §9) sólo lee `success` y `error`, por lo que la confirmación de cobro **no se
  veía**. Ahora usa `with('success', 'Pago registrado correctamente')`.
- ✅ La ruta `sistema.presentaciones.cambiar` pasó a **POST** y se declara antes del listado (antes el `show` del
  resource la capturaba como `{presentacion}`): el botón "Cerrar presentación" de
  `sistema/presentaciones/index.blade.php:142` ahora funciona. No hay que cambiar clases ni estructura, sólo
  recordar que la confirmación es `onsubmit="return confirm(...)"` (no modal Alpine).
- ✅ `shadow-2xs` → `shadow-sm` (§1.2, §14).
- ✅ La tarjeta "Presentaciones" de `sistema/index.blade.php:17` enlazaba a `route('presentaciones.index')`
  (nombre inexistente) y rompía con 500; ahora apunta a `route('sistema.presentaciones.index')`. Es el mismo
  patrón que el redirect del controlador de cierre de lote: **siempre usar el prefijo `sistema.`** para las rutas
  de ese módulo (el `Route::resource` estaba dentro del grupo `sistema`, por lo que sus nombres eran
  `sistema.presentaciones.*` / `sistema.sucursales.*`).

---

## 1. Stack Tecnológico y Build

### 1.1 Arquitectura

- **Laravel Blade + Alpine.js + Tailwind CSS**, compilado con **Vite**.
- `layouts/app.blade.php` y `layouts/guest.blade.php` inyectan los assets con:
  `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- **Sin SPA**: no se usa React ni Inertia. Render en servidor; Alpine sólo para reactividad local.
- Todo el código nuevo debe ser utilidades nativas de Tailwind (nada de CSS externo ni frameworks de UI).

### 1.2 Versión de Tailwind (crítico)

| Archivo | Estado real |
|---|---|
| `tailwind.config.js` | Config v3 (`theme.extend.fontFamily.sans = Figtree`), `plugins: [forms]`. |
| `postcss.config.js` | `tailwindcss` + `autoprefixer` (pipeline clásico v3). |
| `resources/css/app.css` | `@tailwind base; @tailwind components; @tailwind utilities;` (sintaxis v3). |
| `vite.config.js` | Sólo `laravel()`; **no** registra `@tailwindcss/vite`. |
| `node_modules/tailwindcss` | **3.4.19** (la dependencia `@tailwindcss/vite@^4.0.0` de `package.json` está instalada pero sin uso). |

**Reglas derivadas:**

1. Prohibido usar utilidades de Tailwind **v4** (`shadow-2xs`, `shadow-xs`, `inset-shadow-*`,
   `size-*`, sintaxis `@theme`). No se compilan y quedan como clases muertas.
   ✅ **Estado: 0 ocurrencias en `resources/views`** (las 2 que había en `clientes/show.blade.php:226` y
   `creditos/create.blade.php:214` fueron migradas a `shadow-sm`).
2. Sombras disponibles: `shadow-sm` (el estándar del sistema), `shadow-md`, `shadow-lg`, `shadow-xl`,
   `shadow-2xl` (sólo toast y modales legacy).
3. Para valores arbitrarios ya en uso y válidos: `text-[8px]`, `text-[9px]`, `text-[10px]`,
   `text-[11px]`, `text-[10px]`, `w-[66.6%]`, `h-[350px]`, `max-w-[150px]`, `z-[9999]`, `max-w-[90%]`.

### 1.3 Alpine.js

- `resources/js/app.js` importa Alpine, lo expone en `window.Alpine` y ejecuta `Alpine.start()`.
- **Además**, `layouts/app.blade.php:9` carga Alpine por CDN:
  `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>`.
  Es una doble carga heredada: mantener una sola fuente. `layouts/guest.blade.php` **no** carga
  Alpine, por lo que las vistas de `auth/*` no pueden usar `x-data`.
- Patrones válidos en vistas autenticadas: `x-data`, `x-model`, `x-text`, `x-for`, `@click`,
  `x-show`, `x-transition:*`, `$watch`, `x-on:open-modal.window`.
- Los **filtros no usan Alpine**: se resuelven con `onchange="this.form.submit()"` sobre el `<form>`
  GET del ribbon (ver §5.2).
- Formato de moneda en cliente: `Number(x).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})`.

### 1.4 Tipografía base

- Familia `font-sans` = **Figtree** (`tailwind.config.js`). Se carga desde `fonts.bunny.net` sólo en
  `layouts/guest.blade.php`; el layout autenticado no declara el `<link>` de la fuente.
- Font **monoespaciada** (`font-mono`) obligatoria para todo valor monetario, DNI, código de crédito,
  fecha/hora y porcentaje de interés.
- `tabular-nums` obligatorio en toda cifra monetaria dentro de grillas y KPIs **y en todo valor que se
  refresca en vivo, como el reloj operativo del header** (ver §2): sin ancho de dígito fijo, el contenido
  "bailaría" en cada actualización.

---

## 2. Layout Maestro (`layouts/app.blade.php`)

Todas las vistas operativas usan `<x-app-layout>` (16 vistas). No crear HTML completo para pantallas
internas: siempre extender el layout.

```blade
<x-app-layout>
    <!-- contenido -->
</x-app-layout>
```

### 2.1 Estructura real

```html
<body class="bg-gray-100 font-sans antialiased">
    <nav class="bg-blue-800 border-b border-blue-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-8">
            <div class="flex justify-between h-16 items-center relative">
                <!-- Izquierda: logo en tarjeta blanca -->
                <div class="inline-flex items-center bg-white rounded-2xl hover:bg-slate-50 transition-all px-4 py-2 shadow-sm border border-slate-100">
                    <img src="{{ asset('img/logo.png') }}" class="h-9 w-auto mr-3 object-contain" alt="Logo">
                </div>

                <!-- Centro absoluto: título del sistema -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="text-blue-200 text-lg font-black italic tracking-widest uppercase">
                        SISTEMA DE CRÉDITOS
                    </span>
                </div>

                <!-- Derecha: reloj operativo + usuario + sucursal activa + logout -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 text-white text-right z-10">

                    <!-- Reloj operativo (tiempo real en el cliente, sin polling al servidor) -->
                    <div class="hidden md:flex flex-col items-end pr-3 border-r border-blue-700/60 leading-tight select-none mr-3"
                         x-data="{
                             hora: '',
                             fecha: '',
                             actualizar() {
                                 const now = new Date();
                                 this.hora = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                                 const opcionesFecha = { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' };
                                 this.fecha = now.toLocaleDateString('es-AR', opcionesFecha).replace('.', '');
                             },
                             init() {
                                 this.actualizar();
                                 setInterval(() => this.actualizar(), 1000);
                             }
                         }">
                        <span class="text-[10px] text-blue-200 font-bold uppercase tracking-wider font-mono" x-text="fecha"></span>
                        <span class="text-xs font-mono font-bold tabular-nums text-white" x-text="hora"></span>
                    </div>

                    <p class="text-[10px] uppercase text-blue-300 font-black leading-none mb-1 tracking-tighter">Usuario</p>
                    <p class="text-sm font-bold italic">{{ Auth::user()->name }}</p>

                    <!-- Selector de sucursal global: se autogestiona -->
                    <form action="{{ route('sucursal.update.global') }}" method="POST" autocomplete="off">
                        @csrf
                        <select name="id_sucursal" onchange="this.form.submit()"
                            class="bg-blue-800 text-white text-sm font-bold border-none p-0 focus:ring-0 cursor-pointer hover:text-blue-200 transition-colors">
                            <!-- @foreach(\App\Models\Sucursal::all() as $suc) -->
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-2 max-w-[90%] mx-auto px-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </div>
    </main>

    <x-notification-modal />
</body>
```

Notas de implementación:

- El ancho útil de trabajo es **90% del viewport** (`max-w-[90%]`), no `max-w-7xl` fijo.
- **Reloj operativo (tiempo real en el cliente):** la barra superior incluye, como primer elemento del
  contenedor derecho, un reloj en vivo con día/fecha en la línea superior y hora con segundos en la
  inferior. Es **puramente cliente**: se resuelve con Alpine.js + `setInterval`, **sin polling al
  servidor** (cero latencia de backend, cero peticiones por segundo, funciona aunque el servidor no
  responda). Patrón canónico:

  ```html
  <div class="hidden md:flex flex-col items-end pr-3 border-r border-blue-700/60 leading-tight select-none mr-3"
       x-data="{
           hora: '', fecha: '',
           actualizar() {
               const now = new Date();
               this.hora = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
               this.fecha = now.toLocaleDateString('es-AR', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' }).replace('.', '');
           },
           init() { this.actualizar(); setInterval(() => this.actualizar(), 1000); }
       }">
      <span class="text-[10px] text-blue-200 font-bold uppercase tracking-wider font-mono" x-text="fecha"></span>
      <span class="text-xs font-mono font-bold tabular-nums text-white" x-text="hora"></span>
  </div>
  ```

  Reglas del reloj:
  1. **Zona horaria y locale `es-AR`** vía `toLocaleTimeString` / `toLocaleDateString` (no `date()` de PHP,
     no UTC manual): la hora es la del navegador del operador, que es la del mostrador del cajón.
  2. **`hour12: false`** ⇒ reloj de 24 h (`00:00`–`23:59`), coherente con `format('H:i')` usado en grillas.
  3. El `.replace('.', '')` elimina el punto abreviado de los días en español (`"mié, 27/09/2026"`,
     nunca `"mié., 27/09/2026"`).
  4. `init()` renderiza **de inmediato** y luego refresca cada `1000 ms`: evita el segundo en blanco
     mientras Alpine hidrata.
  5. Responsive: `hidden md:flex` ⇒ en móvil el reloj se oculta y el header no se desbuela
     (el bloque derecho global ya es `hidden sm:flex`).
  6. `select-none` + `tabular-nums`: los dígitos no saltan de ancho al cambiar de `9` a `10` segundos
     ni al pasar de `:59` a `:00`.
  7. Sin `x-cloak`: el estado inicial es cadena vacía y el contenedor se posiciona por clases, no por
     contenido.
- El selector de sucursal activa es **global**: vive sólo en el header, hace `POST` a
  `sucursal.update.global` y se envía solo con `onchange`. No duplicar selectores de sucursal en
  cada pantalla (excepto filtros de listado, que son sólo lectura).
- La sucursal activa se lee desde `config('app.sucursal_activa.id')`.
- El toast global `<x-notification-modal />` ya está incluido: no hacer `@if(session('success'))`
  duplicado en las vistas nuevas (ver §11).
- Usar `<x-app-layout>` (no `<x-layouts.app>`): no mezclar ambos estilos.

---

## 3. Tipografía y Alineación Numérica

| Elemento | Clases Tailwind (reales) | Propósito |
|---|---|---|
| Moneda / Importes en grilla | `text-right font-mono font-bold tabular-nums` (`font-black` si es saldo exigible) | Decimales y miles alineados en columna contable. |
| Códigos / Identificadores | `font-mono font-bold` (+`text-blue-700` si es link, `text-[11px]`) | Lectura veloz de códigos compuestos (`R00001234T1`, DNI, CBU). |
| DNI en ficha / legajo | `font-mono font-bold text-blue-900 text-xs tabular-nums` | Identificación unívoca del titular. |
| Fechas y horas | `font-mono` + `format('d/m/Y')` y hora en línea aparte `format('H:i')` | Evita ambigüedad y mantiene la columna angosta. |
| **Reloj operativo del header** | `text-[10px] text-blue-200 font-bold uppercase tracking-wider font-mono` (fecha) + `text-xs font-mono font-bold tabular-nums text-white` (hora) | `font-mono` + `tabular-nums` **obligatorios**: el reloj refresca cada segundo y sin ancho fijo los dígitos se desplazan en cada tick (`:59` → `:00`). `leading-tight` + `select-none` para dos líneas compactas. Patrón completo en §2. |
| Texto de grilla (cuerpo) | `text-xs font-sans` (secundarios en `text-[10px]`/`text-[9px]`; `text-[8px]` sólo en chips) | Máxima densidad sin fatiga visual. |
| Encabezado de tabla | `bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300` | Diferenciación de columnas con mínima altura. |
| Encabezado de panel/sección | `font-bold text-[10px] text-slate-700 uppercase tracking-wider` | Barra de título del panel. |
| Título de página | `text-xl font-bold text-slate-800 tracking-tight` | Jerarquía visual. |
| Label de campo | `block text-[9px] font-bold text-slate-500 uppercase mb-0.5` | Alta densidad en formularios. |
| Label de KPI / dato técnico | `text-[9px] font-bold text-slate-500 uppercase` | Cinta de KPIs y fichas. |

Formato numérico (server-side, obligatorio):

```blade
{{-- Importes: 2 decimales, miles con punto --}}
$ {{ number_format($credito->total_saldo_actual ?? 0, 2, ',', '.') }}

{{-- Porcentajes de interés (se guardan como fracción, ej. 0.10) --}}
{{ $presentacion->interes_credito * 100 }} %
```

---

## 4. Paleta de Colores y Semántica de Estados

### 4.1 Identidad institucional

| Rol | Clases reales |
|---|---|
| Header / navegación | `bg-blue-800 border-b border-blue-900 shadow-lg` |
| Título del sistema (header) | `text-blue-200 font-black italic tracking-widest uppercase` |
| Etiquetas del header | `text-[10px] uppercase text-blue-300 font-black tracking-tighter` |
| Botón primario de acción (alta, buscar, guardar) | `bg-blue-600 hover:bg-blue-700 text-white` |
| Botón secundario / cancelar | `text-slate-500 hover:text-slate-800` · `bg-slate-200 hover:bg-slate-300 text-slate-700` |
| Botón de confirmación (edición / cierre de ciclo) | `bg-emerald-600 hover:bg-emerald-700 text-white` |
| Bloque destacado oscuro (KPI, panel de control) | `bg-blue-900 border-blue-950` · `bg-blue-950 text-blue-200` |
| Link de dato clave (ID crédito, DNI) | `text-blue-700 hover:underline` |
| Fondo de trabajo | `bg-gray-100` en `<body>` (herencia Breeze) + paneles `bg-slate-50`, `bg-slate-100/70` |
| Contenedores y tarjetas | `bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden` (toolbars: `border-slate-200`) |
| Zonas anidadas / atenuadas | `bg-slate-50/70`, `bg-slate-50/50`, `bg-slate-100/70` |

> **Prohibido** usar HEX arbitrarios (`bg-[#ffa086]`, etc.): la paleta es 100% utilidades de Tailwind.
> El único HEX del proyecto es `#E2E2E2` en el CSS de impresión de `creditos/pagare.blade.php` (§13).

### 4.2 Semántica operativa de estados

| Estado | Clases Tailwind | Dónde |
|---|---|---|
| **Cliente suspendido — fila** | `bg-rose-50/60 hover:bg-rose-100/80` | `clientes/index.blade.php` |
| **Cliente suspendido — chip** | `bg-rose-700 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase` | `clientes/index.blade.php` |
| **Cliente suspendido — banner** | `bg-rose-50 border border-rose-200 text-rose-800` + `⛔ CLIENTE SUSPENDIDO` | `clientes/show`, `clientes/estado-cuenta` |
| **Cliente activo** | `bg-emerald-100 text-emerald-800 border border-emerald-200` | `clientes/show` |
| **Firma autorizada (sub-fila)** | `bg-slate-50/70 hover:bg-slate-100/90 border-l-2 border-l-blue-400` + chip `bg-slate-300 text-slate-700` con `AUT` | `clientes/index.blade.php` |
| **Crédito abierto** | chip `bg-amber-100 text-amber-800 border border-amber-200` (`ABIERTO` / `En Curso`) | `creditos/index`, `creditos/show`, `sistema/presentaciones/index` |
| **Crédito cancelado** | fila `bg-slate-50/60 text-slate-500` + ID con `line-through`; chip `bg-slate-200 text-slate-500 border border-slate-300` | `creditos/index`, `creditos/show` |
| **Presentación activa** | fila `bg-blue-50/50 hover:bg-blue-100/50`; chip `bg-amber-100 text-amber-800 border border-amber-200` | `sistema/presentaciones/index` |
| **Presentación cerrada** | chip `bg-slate-200 text-slate-500 border border-slate-300` | `sistema/presentaciones/index` |
| **Intereses (bordó)** | `text-red-800` (grillas) · `text-red-700` (presentaciones) | Todas las grillas |
| **A pagar / saldo exigible (verde)** | `text-emerald-700 font-black`; contenedor `bg-emerald-50 border-emerald-200` / `bg-emerald-100` | Grillas y KPIs |
| **Valor base / entregó (neutro)** | `text-slate-900` | Todas las grillas |
| **Deuda acumulada (alerta)** | `bg-amber-100 p-2 rounded` — en `creditos/create.blade.php:187` el borde quedó en `border-emerald-200` (inconsistencia a unificar) | `creditos/create` |
| **Aviso informativo** | `bg-amber-50 border border-amber-200 text-slate-700 italic` | `clientes/show` |
| **Error inline** | `<x-input-error class="mt-1 text-[10px]" />` → `text-sm text-red-600` | Formularios |

---

## 5. Patrón de Página y Toolbar de Filtros

Toda pantalla operativa (`clientes/*`, `creditos/*`, `sistema/presentaciones`) se compone en este orden:
**header compacto → toolbar de filtros → grilla o panel → paginador**.

### 5.1 Header compacto de página

```html
<div class="flex items-center justify-between py-2 border-b border-slate-200 mb-3">
    <div class="flex items-center space-x-3">
        <!-- Botón volver (sólo en vistas de detalle/alta) -->
        <a href="{{ url()->previous() }}"
           class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">…</svg>
        </a>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center space-x-2">
            <span>Cartera de Créditos</span>
            <span class="text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                Monitoreo y Estados de Cuenta
            </span>
        </h1>
    </div>

    <!-- Acción principal a la derecha (opcional) -->
    <a href="{{ route('clientes.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center space-x-1 shadow-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">…</svg>
        <span>NUEVO CLIENTE</span>
    </a>
</div>
```

- El `<h1>` lleva **siempre** el badge de contexto (`text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200`).
- Botón de exportación (créditos): `bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md text-xs font-bold shadow-sm`.
- Botón "Reimprimir Pagaré": variante neutra `bg-white border border-slate-300 text-slate-700 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm hover:bg-slate-50 uppercase`.

### 5.6 Flujo de Recepción e Ingreso de Mercadería

El sistema provee dos modalidades complementarias y conectadas entre sí:

#### A. Recepción Unitaria / Prenda a Prenda (`/productos/nuevo`)
- **Objetivo**: Máxima velocidad de tipeo al etiquetar o incorporar prendas individuales o variantes.
- **Sin AJAX continuo**: Al escribir talle, color o código de fábrica no se disparan peticiones de red ni banners invasivos. La concatenación CODE128 (`Prov(3c) + Talle(2c) + Color(2c) + Fabrica`) se proyecta localmente en un visor segmentado.
- **Validación al Continuar**:
  1. Si se seleccionó una prenda base y **no se modificaron** sus atributos (mismo talle, color y código de fábrica): se incrementa el stock de forma directa e inmediata sin modales.
  2. Si se modificó algún atributo o es un artículo nuevo: al pulsar "Continuar", se efectúa una única consulta AJAX:
     - **Código Libre**: abre modal de confirmación mostrando el código CODE128 resultante, talle, color, fábrica y unidades a ingresar.
     - **Código Ocupado**: abre modal exhibiendo los datos del artículo existente (descripción, talle, color, stock actual) y consulta si se desea sumar las unidades a este o cancelar para modificar.

#### B. Carga por Factura / Remito del Proveedor (`/remitos/nuevo`)
- **Objetivo**: Carga estructurada de facturas o remitos de proveedores con múltiples ítems.
- **Acceso Directo desde Productos**: En el índice de productos (`/productos`), junto a `+ NUEVO ARTÍCULO`, se ubica el botón destacado `INGRESO DE MERCADERÍA` (verde esmeralda) que conduce directamente a esta pantalla.
- **Encabezado**: Fecha de recepción, Número de Remito/Factura, Proveedor emisor y Sucursal destino.
- **Carga en Etapas (Facturas Parciales)**:
  - En el encabezado se dispone de un selector dinámico con las facturas/remitos con estado `parcial` pendientes.
  - Al seleccionar una (o ingresar desde el botón `✏️ Continuar Carga` en el listado o detalle del remito), se precargan los datos de cabecera y la totalidad de los renglones previamente guardados.
  - **Ajuste de Stock por Diferencia (Delta)**: Cada renglón visualiza la cantidad guardada previamente y el delta en vivo. Al guardar la modificación o completar el comprobante, el controlador calcula la diferencia exacta ($\Delta = Q_{\text{nueva}} - Q_{\text{previa}}$) por cada producto, incrementando o reduciendo el stock físico únicamente por la variación y registrando el movimiento de auditoría correspondiente sin duplicar unidades.
- **Búsqueda Estricta por Proveedor**: La búsqueda de artículos filtra exclusivamente dentro del catálogo del proveedor seleccionado (por código interno/barra, código de fabricante o descripción).
- **Altas Dinámicas sin perder renglones**:
  - **Artículo Nuevo**: botón "+ Nuevo Artículo en Catálogo" abre un modal para dar de alta el producto con talle, color y código de fábrica (generando el código estructurado CODE128) y lo agrega automáticamente a la tabla del remito.
  - **Proveedor Nuevo**: botón "+ Alta Rápida de Proveedor" abre un modal para registrar el proveedor con su legajo correlativo vía AJAX, incorporándolo a la lista y seleccionándolo sin recargar la página ni perder renglones ingresados.

---

### 5.2 Ribbon de búsqueda y filtros

```html
<div class="bg-slate-50 p-2.5 rounded-md border border-slate-200 mb-4 shadow-sm">
    <form method="GET" action="{{ route('creditos.index') }}" class="space-y-2">
        <div class="flex gap-2">
            <div class="relative flex-grow">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nro crédito, cliente o autorizado..."
                       class="w-full text-xs py-1.5 px-3 rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-md shadow-sm transition">
                Buscar
            </button>
            @if(request()->anyFilled(['search', 'sucursal', 'tipo', 'estado']))
                <a href="{{ route('creditos.index') }}"
                   class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center">
                    Limpiar
                </a>
            @endif
        </div>

        <!-- Filtros secundarios separados por una línea superior -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1.5 border-t border-slate-200/70">
            <select name="sucursal" onchange="this.form.submit()"
                    class="text-xs py-1 px-2 border-slate-300 rounded-md bg-white font-medium text-slate-600">
                <!-- opciones -->
            </select>
        </div>
    </form>
</div>
```

Reglas del ribbon:

1. Fondo `bg-slate-50`, padding `p-2.5`, `rounded-md border border-slate-200`, `mb-4`, `shadow-sm`.
2. Todos los `<select>` de filtro usan `text-xs py-1 px-2 border-slate-300 rounded-md bg-white font-medium text-slate-600`
   y se envían solos con `onchange="this.form.submit()"`. **No** se usa Alpine para filtrar.
3. El bloque de filtros secundarios se separa con `pt-1.5 border-t border-slate-200/70` y grid
   `grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2` (clientes) o `sm:grid-cols-3` (créditos).
4. Checkboxes financieros (`Saldo > 0`, `Interés > 0`, `A pagar > 0`):
   `input.rounded border-slate-400 text-blue-600 focus:ring-blue-500 h-3 w-3` dentro de
   `label.flex items-center space-x-1.5 cursor-pointer hover:text-blue-700 transition-colors`.
5. El botón "Limpiar" sólo se renderiza si hay filtros activos (`request()->anyFilled([...])`).
6. Si hay un filtro contextual activo (p. ej. `cliente_id`), mostrarlo como banda informativa:
   `bg-blue-50 border-b border-blue-200 px-3 py-1.5` con texto `text-[10px] text-blue-800 font-bold uppercase`
   y link `✕ Quitar filtro` (`text-[10px] text-blue-600 hover:text-blue-900 font-black`).
7. Los filtros contextuales deben sobrevivir al paginado: conservarlos en `input type="hidden"` y en
   `$collection->appends(request()->query())->links()`.

---

## 6. Grilla de Datos (Data-Grid estilo Excel)

### 6.1 Contenedor y tabla

```html
<div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-4">
    <table class="w-full text-left border-collapse font-sans text-xs">
        <thead>
            <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300">
                <th class="py-1.5 px-3 border-r border-slate-200 w-24">ID Gestión</th>
                <th class="py-1.5 px-3 border-r border-slate-200 w-24 text-center">Fecha</th>
                <th class="py-1.5 px-3 border-r border-slate-200">Cliente</th>
                <th class="py-1.5 px-3 border-r border-slate-200 text-right w-24">Valor Base</th>
                <th class="py-1.5 px-3 border-r border-slate-200 text-right w-28">A Pagar</th>
                <th class="py-1.5 px-3 text-center w-20">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <tr class="bg-white hover:bg-blue-50/40 text-slate-800 transition-colors">
                <td class="py-1.5 px-3 border-r border-slate-200/60">
                    <a href="#" class="font-mono font-bold text-blue-700 hover:underline text-[11px]">R00001234T1</a>
                </td>
                <td class="py-1.5 px-2 text-center font-mono border-r border-slate-200/60">
                    <div class="text-[10px] font-bold text-slate-700">27/09/2026</div>
                    <div class="text-[9px] text-slate-400">15:42</div>
                </td>
                <td class="py-1.5 px-3 border-r border-slate-200/60 max-w-[150px] truncate">
                    <div class="font-bold uppercase text-[11px] truncate" title="PEREZ JUAN CARLOS">PEREZ JUAN CARLOS</div>
                    <div class="text-[9px] font-bold text-slate-400">DNI: 796855</div>
                </td>
                <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 tabular-nums text-slate-900">
                    $ 45.200,00
                </td>
                <td class="py-1.5 px-3 text-right font-mono font-black border-r border-slate-200/60 tabular-nums text-emerald-700">
                    $ 48.816,00
                </td>
                <td class="py-1 px-2 text-center">
                    <a href="#" class="inline-flex items-center bg-slate-100 border border-slate-200 hover:bg-blue-600 hover:text-white text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-sm">
                        Detalle →
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### 6.2 Reglas (verificadas)

| Regla | Valor |
|---|---|
| Contenedor | `bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-4` |
| `<table>` | `w-full text-left border-collapse font-sans text-xs` |
| `<thead> <tr>` | `bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300` (variante `bg-slate-50` en `creditos/show` y `presentaciones/index`) |
| `<th>` | `py-1.5 px-3 border-r border-slate-200` + `text-right`/`text-center` + ancho fijo `w-24`, `w-28`, `w-32` o `w-[260px]` |
| `<tbody>` | `divide-y divide-slate-200` |
| `<tr>` normal | `bg-white hover:bg-blue-50/40 text-slate-800 transition-colors` |
| `<tr>` cerrada | `bg-slate-50/60 text-slate-500` |
| `<tr>` suspendida | `bg-rose-50/60 hover:bg-rose-100/80` |
| `<td>` | `py-1.5 px-3 border-r border-slate-200/60` — **la última celda va sin `border-r`** |
| Celda de dos líneas | primera línea `text-[10px] font-bold text-slate-700`, segunda `text-[9px] text-slate-400` |
| Truncado | `truncate` + `title="..."` con ancho fijo (`max-w-[150px]`), nunca `max-w-xs` por defecto |
| Acción en fila | `inline-flex items-center bg-slate-100 border border-slate-200 hover:bg-blue-600 hover:text-white text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-sm` |
| Acciones con icono | `p-0.5 text-slate-400 hover:text-{blue|emerald|rose}-600 hover:bg-{color}-100 rounded transition-all` con `svg.w-3.5 h-3.5` |
| Columna de acciones | `py-1 px-2 text-center` (sin bordes internos) |
| Fila de firma autorizada | `bg-slate-50/70 hover:bg-slate-100/90 text-[11px] border-b border-slate-200 border-l-2 border-l-blue-400`, DNI indentado con `pl-8` y prefijo `└` |

### 6.3 Empty state y paginador

```html
<!-- Empty state: siempre con colspan = cantidad de columnas -->
<tr>
    <td colspan="8" class="py-8 text-center text-slate-400 font-medium text-xs italic bg-slate-50">
        No se encontraron créditos registrados.
    </td>
</tr>
```

```blade
{{-- Paginador dentro del contenedor de la grilla --}}
<div class="px-2 py-1 bg-slate-50 border-t border-slate-200 text-xs">
    {{ $creditos->links() }}
</div>

{{-- Paginador fuera del contenedor (clientes/index) --}}
<div class="px-1 text-xs">
    {{ $clientes->appends(request()->query())->links() }}
</div>
```

- Prohibido usar `p-6`, `py-4`, `gap-6` o `mb-8` en grillas operativas: la densidad se logra con
  `py-1.5`, `px-3`, `p-2.5`, `gap-2`/`gap-3`.
- Usar `divide-y` en el `tbody` y `border-r` por celda; **no** replicar bordes con `divide-x` en las filas.
- Toda fila que represente un crédito debe exponer el código con `full_identificador` como link a
  `creditos.show` (columna "ID Gestión").

---

## 7. Cinta Horizontal de KPIs

Los resúmenes financieros se presentan como una cinta densa arriba de la grilla, nunca como tarjetas
cuadradas grandes que empujen la tabla fuera del campo visual.

```html
<!-- clientes/estado-cuenta.blade.php -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-2 mb-4">
    <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
        <span class="text-[9px] font-bold text-slate-500 uppercase">Total Deuda Base</span>
        <span class="font-mono font-black text-slate-900 text-sm tabular-nums">$ 350.000,00</span>
    </div>

    <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
        <span class="text-[9px] font-bold text-slate-500 uppercase">Total Intereses</span>
        <span class="font-mono font-black text-red-800 text-sm tabular-nums">$ 35.000,00</span>
    </div>

    <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
        <span class="text-[9px] font-bold text-slate-500 uppercase">Total Entregado</span>
        <span class="font-mono font-black text-slate-900 text-sm tabular-nums">$ 265.680,00</span>
    </div>

    <!-- KPI destacado en verde: saldo exigible -->
    <div class="bg-emerald-50 p-2 rounded-md border border-emerald-200 shadow-sm flex flex-col justify-between">
        <span class="text-[9px] font-bold text-emerald-800 uppercase">Total a Pagar</span>
        <span class="font-mono font-black text-emerald-700 text-sm tabular-nums">$ 385.000,00</span>
    </div>

    <!-- KPI destacado en azul oscuro: lo que queda por vencer -->
    <div class="bg-blue-900 p-2 rounded-md border border-blue-950 shadow-sm flex flex-col justify-between">
        <span class="text-[9px] font-bold text-blue-200 uppercase">A Vencer</span>
        <span class="font-mono font-black text-white text-sm tabular-nums">$ 96.250,00</span>
    </div>
</div>
```

Reglas:

1. Grid: `grid grid-cols-2 lg:grid-cols-5 gap-2 mb-4` (en `creditos/show` la cinta es una sola fila de
   3 bloques con `grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4`).
2. Tarjeta base: `bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between`.
3. Label: `text-[9px] font-bold text-slate-500 uppercase`.
4. Valor: `font-mono font-black text-sm tabular-nums`.
5. Dos KPIs se destacan con contenedor propio: **verde** para lo exigible
   (`bg-emerald-50 border-emerald-200` / `bg-emerald-100`) y **azul oscuro** para lo que falta vencer
   (`bg-blue-900 border-blue-950`, label `text-blue-200`, valor `text-white`).
6. Los KPIs se recalculan server-side sumando los créditos del cliente. No repetir la cinta con
   valores distintos en dos pantallas si el cálculo es el mismo.

---

## 8. Layouts de Dos Columnas

### 8.1 Legajo / Punto de Venta (66.6% / 33.3%)

Se usa en `clientes/show.blade.php` y `creditos/create.blade.php`.

```html
<div class="flex flex-col lg:flex-row gap-3 items-start">
    <!-- Columna principal -->
    <div class="w-full lg:w-[66.6%] space-y-3">
        <!-- Grillas de créditos, cuotas, autorizados, vencimientos -->
    </div>

    <!-- Columna lateral -->
    <div class="w-full lg:w-[33.3%] space-y-3">
        <!-- Ficha filiatoria -->
        <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
                <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">Datos Filiatorios</h2>
            </div>
            <div class="p-3 grid grid-cols-2 gap-3 text-xs">
                <div class="col-span-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Nombre Completo</span>
                    <span class="font-bold text-slate-800 text-xs">PEREZ JUAN CARLOS</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">DNI</span>
                    <span class="font-mono font-bold text-blue-900 text-xs tabular-nums">796855</span>
                </div>
            </div>
        </div>

        <!-- Módulo de novedades (§12) -->
    </div>
</div>
```

- `creditos/create.blade.php` usa la misma proporción con `gap-4` y `space-y-4`.
- `sistema/presentaciones/index.blade.php` usa **70% / 30%** (`lg:w-[70%]` / `lg:w-[30%]`), con la
  columna derecha `sticky top-4` (panel de control de ciclo).
- La columna lateral **nunca** queda como contenedor vacío: si el módulo no aplica, no se renderiza.

### 8.2 Panel de sección (bloque estándar de formularios)

```html
<div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-3">
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
        <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">1. Datos Personales y Sucursal</h3>
    </div>
    <div class="p-3 grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- campos -->
    </div>
</div>
```

- Cabecera de panel destacada en azul oscuro sólo para el panel de control crítico:
  `bg-blue-950 px-3 py-2` + `h2.text-[10px] text-blue-200 uppercase tracking-wider`.
- En paneles con acción a la derecha (p. ej. "✏️ Editar"): `px-3 py-1.5 flex justify-between items-center`
  y el link con `text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase`.

---

## 9. Formularios, Controles y Botoneras

### 9.1 Campo de texto estándar

```html
<div class="md:col-span-1">
    <label for="dni" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">DNI (Sin puntos)</label>
    <input type="number" id="dni" name="dni" value="{{ old('dni', $cliente->dni ?? '') }}" required
           class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
    <x-input-error :messages="$errors->get('dni')" class="mt-1 text-[10px]" />
</div>
```

| Elemento | Clases reales |
|---|---|
| `<label>` | `block text-[9px] font-bold text-slate-500 uppercase mb-0.5` |
| `<input>` / `<select>` / `<textarea>` | `block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm` |
| DNI, CUIL/CUIT, teléfonos | agregar `font-mono` |
| Nombre / razón social | agregar `uppercase` |
| Importes en carga | `w-full text-sm font-mono font-black rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white shadow-sm` |
| Campo calculado / readonly | `bg-slate-50` en lugar de `bg-white` |
| Error de validación | `<x-input-error :messages="$errors->get('campo')" class="mt-1 text-[10px]" />` |
| Textarea de novedad | `w-full text-[11px] rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 p-1.5 resize-none` |
| Checkbox | `rounded border-slate-400 text-blue-600 focus:ring-blue-500 h-3 w-3` |
| Checkbox en fila de tabla | `rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-3 w-3` |
| Selector de sucursal (filtro) | `text-xs py-1 px-2 border-slate-300 rounded-md bg-white font-medium text-slate-600` |

Se usa `old()` en **todos** los campos y `$errors->get()` para mostrar el error: nunca se pierde lo
cargado por el usuario.

### 9.2 Botonera fija de formulario

```html
<div class="bg-slate-50 p-3 rounded-md border border-slate-300 flex items-center justify-end space-x-3 shadow-sm">
    <a href="{{ route('clientes.index') }}"
       class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase px-3 py-1.5">Cancelar</a>

    {{-- Alta --}}
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] py-1.5 px-6 rounded uppercase transition-all shadow-sm">
        Registrar Cliente
    </button>

    {{-- Edición: mismo lugar, color verde --}}
    {{-- class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] py-1.5 px-6 rounded uppercase transition-all shadow-sm" --}}
</div>
```

- Convención: **azul** = crear/operar, **verde** = guardar cambios/confirmar ciclo, **rojo/rose** = eliminar.
- Acciones destructivas en grillas: `form` con `@csrf` + `@method('DELETE')` y
  `onclick="return confirm('¿Seguro que deseas eliminar este registro?')"`.
- Los formularios largos se dividen en paneles numerados (`1. Datos Personales y Sucursal`,
  `2. Ubicación y Contacto`, `3. Laboral y Bancaria`) usando el bloque de §8.2, y se incluyen desde un
  único archivo parcial (`@include('clientes.form')`) compartido por `create` y `edit`.
- Los formularios con totales reactivos usan Alpine en el contenedor raíz:
  `x-data="{ valorVenta: 0, aFavor: 0, cuotasSeleccionadas: 1, get subtotal() {...} }"` y muestran los
  cálculos con `x-text` (nunca reimplementar en JS el redondeo del backend).

---

## 10. Chips, Microlabels y Densidad

```html
<!-- Chip de estado -->
<span class="bg-amber-100 text-amber-800 text-[9px] font-black px-1.5 py-0.5 rounded border border-amber-200 uppercase">ABIERTO</span>

<!-- Chip sin borde (estado menor) -->
<span class="bg-slate-300 text-slate-700 text-[8px] font-bold px-1 py-0.5 rounded uppercase leading-none">AUT</span>

<!-- Contador en cabecera de panel -->
<span class="bg-slate-300 text-slate-700 font-bold text-[9px] px-1.5 py-0.5 rounded">3</span>

<!-- Chip de contexto en el header de página -->
<span class="text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Ciclos de Facturación</span>
```

Escala tipográfica permitida (nada por debajo de `text-[8px]`):

| Tamaño | Uso |
|---|---|
| `text-xs` (12px) | Body de grilla, inputs, botones principales. |
| `text-[11px]` | Códigos de crédito, botones de acción pequeños, botones de submit. |
| `text-[10px]` | Headers de tabla/panel, fechas, segundas líneas. |
| `text-[9px]` | Labels de campo y de KPI, chips de estado, fechas secundarias. |
| `text-[8px]` | Sólo chips/etiquetas menores (`AUT`, `CERRADA`). |

Pesos: `font-bold` y `font-black` son la norma (el sistema es de alto contraste); `font-normal` se usa
sólo en el texto del badge del header y en textos secundarios (`text-slate-400`, `italic`).

---

## 11. Alertas, Banners y Notificaciones

### 11.1 Toast global (mecanismo principal)

`layouts/app.blade.php` incluye `<x-notification-modal />` al final del `<body>`. El componente
(`components/notification-modal.blade.php`) lee `session('success')` y `session('error')`, se
auto-cierra a los 5 s y se puede cerrar manualmente.

```html
<div x-data="{ show: false, message: '', type: 'success' }"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     class="fixed top-5 right-5 z-[9999] max-w-sm w-full bg-white shadow-2xl rounded-2xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-2"
     :class="type === 'success' ? 'border-emerald-500 bg-emerald-50/50' : 'border-red-500 bg-red-50/50'">
    <!-- icono success/error + título + mensaje + botón cerrar -->
</div>
```

- Para notificar desde el backend basta con `return redirect()->route(...)->with('success', '...')`
  o `->with('error', '...')`. **No** duplicar banners manuales en cada vista.
- Colores del toast: éxito `border-emerald-500` / `text-emerald-800`, error `border-red-500` / `text-red-800`.
- Contenedor flotante: `fixed top-5 right-5 z-[9999]` (nunca taparlo con modales; los modales usan `z-50`,
  excepto el componente `modal.blade.php` que usa `z-50` sobre overlay `bg-gray-500 opacity-75`).

### 11.2 Banners inline (vistas legacy o advertencias persistentes)

```html
<!-- Error -->
<div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-md shadow-sm mb-4 flex items-center space-x-2">
    <span class="font-black text-xs uppercase block">⛔ ERROR:</span>
    <span class="text-xs font-bold">{{ session('error') }}</span>
</div>

<!-- Éxito -->
<div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-md shadow-sm mb-4 flex items-center space-x-2">
    <span class="font-black text-xs uppercase block">✅ ÉXITO:</span>
    <span class="text-xs font-bold">{{ session('success') }}</span>
</div>
```

Se usan sólo cuando el aviso debe quedar visible mientras se trabaja (p. ej. `sistema/presentaciones/index`).
En pantallas nuevas, preferir el toast global.

### 11.3 Confirmaciones destructivas

Se resuelven con dialog nativo, no con modal propio:

```html
<form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="inline"
      onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
    @csrf @method('DELETE')
    <button type="submit" class="p-0.5 text-slate-400 hover:text-rose-600 hover:bg-rose-100 rounded transition-all">…</button>
</form>
```

Mensajes de confirmación en mayúsculas y con la consecuencia explícita, como en el cierre de ciclo:
`"ATENCIÓN: Se cerrará la presentación actual, se aplicarán los intereses a los morosos y se volverán exigibles las nuevas cuotas. ¿Estás absolutamente seguro de continuar?"`.

---

## 12. Módulo de Novedades (historial operativo)

Aparece en `clientes/show.blade.php` (alto `h-[350px]`) y `creditos/create.blade.php` (alto `h-[380px]`).

```html
<div class="bg-white rounded-md border border-slate-300 shadow-sm flex flex-col h-[350px]">
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
        <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">Historial / Novedades</h2>
        <span class="bg-slate-300 text-slate-700 font-bold text-[9px] px-1.5 py-0.5 rounded">3</span>
    </div>

    <div class="flex-1 overflow-y-auto p-2 space-y-1.5 bg-slate-50/50">
        <div class="p-2 rounded text-[11px] border-l-2 shadow-sm bg-white border border-slate-200 border-l-rose-500">
            <div class="flex justify-between items-center mb-1">
                <div class="flex items-center space-x-1.5">
                    <span class="font-bold text-slate-800 uppercase text-[9px]">ADMIN</span>
                    <span class="text-[8px] font-bold px-1 rounded uppercase bg-slate-100 text-slate-500 border border-slate-200">LLAMADO</span>
                </div>
                <span class="text-[9px] font-mono text-slate-400">27/09/2026 15:42</span>
            </div>
            <p class="text-slate-600 leading-snug">Se contactó al cliente, promete pago el viernes.</p>
        </div>

        <div class="h-full flex items-center justify-center">
            <span class="text-[10px] text-slate-400 font-medium italic">Sin novedades registradas.</span>
        </div>
    </div>

    <div class="border-t border-slate-200 p-2 bg-slate-50">
        <form action="#" method="POST" class="flex flex-col space-y-1.5">
            @csrf
            <textarea name="mensaje" rows="2" placeholder="Nueva novedad..."
                      class="w-full text-[11px] rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 p-1.5 resize-none" required></textarea>
            <button type="button"
                    class="bg-blue-800 hover:bg-blue-900 text-white font-bold text-[10px] py-1.5 px-3 rounded uppercase transition-all shadow-sm w-full">
                Registrar
            </button>
        </form>
    </div>
</div>
```

Reglas:

1. Alto fijo (`h-[350px]` / `h-[380px]`) con scroll interno (`flex-1 overflow-y-auto`): el panel nunca
   debe crecer y empujar el resto de la página.
2. Color del borde izquierdo según el tipo de novedad: `border-l-rose-500` (tipo `0`, reclamo/negativo)
   y `border-l-emerald-500` (resto, gestión positiva).
3. Chips de autor y método: `text-[8px]`/`text-[9px]` `font-bold uppercase`, autor en `text-slate-800`,
   método en `bg-slate-100 text-slate-500 border border-slate-200`.
4. Fecha `d/m/Y H:i` en `font-mono text-[9px] text-slate-400`.
5. La caja de carga vive dentro del panel, separada con `border-t border-slate-200 p-2 bg-slate-50`.
6. `shadow-sm` (Tailwind v3). **No** `shadow-2xs`.

---

## 13. Documento Imprimible (Pagaré)

`creditos/pagare.blade.php` es la **excepción** a todo lo anterior: es un HTML autónomo para impresión
(no extiende el layout, no usa Tailwind, no usa Vite).

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pagaré - Crédito #{{ $credito->full_identificador }}</title>
    <script type="text/javascript">
        function imprimir() { setTimeout(function(){ window.print(); }, 500); }
    </script>
    <style type="text/css">
        @media print { @page { size: 210mm 297mm; margin: 15px; } }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
        .contenedor { width: 780px; margin: 0 auto; }
        .tabla-border { width: 100%; border-collapse: collapse; }
        .tabla-border td, .tabla-border th { border: 1px solid #ccc; padding: 4px; }
        .bg-gris { background-color: #E2E2E2; font-size: 10px; text-align: center; font-weight: bold; }
        .texto-manuscrito { font-family: "Brush Script MT", cursive, serif; font-size: 13.5px; line-height: 22px; text-align: justify; }
        .box-firma { border: 2px solid #E2E2E2; text-align: center; vertical-align: bottom; height: 80px; font-size: 10px; padding: 5px; }
        hr.linea-punteada { border: none; border-top: 2px dashed #000; margin: 20px 0; }
    </style>
</head>
<body onload="imprimir();">
    <div class="contenedor">...</div>
</body>
</html>
```

Reglas:

1. Se abre con `target="_blank"` desde `creditos/show` (`📄 Reimprimir Pagaré`).
2. CSS propio con nombres en español (`.contenedor`, `.bg-gris`, `.texto-manuscrito`, `.box-firma`),
   tipografía Arial 12px y ancho fijo 780px.
3. La impresión se dispara con `onload="imprimir()"` + `window.print()` con `setTimeout` de 500 ms.
4. Este archivo es el **único** lugar permitido para HEX (`#E2E2E2`) y estilos inline.
5. No migrar este documento a Tailwind: la impresión depende de márgenes y medidas absolutas.

---

## 14. Zonas Legacy y Deuda Visual

Las siguientes vistas **no** siguen (todavía) el sistema de diseño. Son restos de Breeze o pantallas
poco frecuentes; sirven como referencia de lo que **no** hay que copiar en pantallas operativas:

| Zona | Archivos | Qué tienen distinto |
|---|---|---|
| Autenticación | `auth/login.blade.php` (HTML propio), `auth/{register,forgot-password,reset-password,confirm-password,verify-email}` | `<x-guest-layout>`, `rounded-xl`/`rounded-lg`, `text-gray-*`, `focus:ring-blue-600`, `<x-text-input>`, `<x-primary-button>` (gris `bg-gray-800` + `indigo`). |
| Perfil | `profile/edit.blade.php` + `profile/partials/*` | Componentes Breeze puros (`<x-input-label>`, `<x-text-input>`, `<x-primary-button>`, `<x-danger-button>`, `<x-modal>`). |
| Dashboard | `dashboard.blade.php` | Tarjetas `rounded-3xl shadow-xl p-8`, títulos `text-4xl font-black text-blue-950`, hover `bg-blue-600` a bloque completo. |
| Usuarios | `sistema/usuarios/index.blade.php`, `sistema/usuarios/create.blade.php` | `rounded-3xl`, `p-6`/`p-8`, `text-3xl font-black`, inputs `px-5 py-3.5 rounded-2xl`. |
| Ubicaciones | `sistema/ubicaciones/{provincias,localidades}.blade.php` | `rounded-3xl`, `px-4 py-2`, encabezados en `italic`, `text-sm`, `bg-blue-100 rounded-full`. |
| Sistema (home) | `sistema/index.blade.php` | Grilla de tarjetas `p-4` con `hover:ring-1`; es el único "dashboard" aceptable del sistema, pero su tipografía (`text-3xl`) es legacy. |

Componentes disponibles en `resources/views/components/`:

| Componente | Uso hoy |
|---|---|
| `x-app-layout` | Layout operativo (obligatorio en pantallas internas). |
| `x-guest-layout` | Sólo `auth/*`. |
| `x-notification-modal` | Toast global (ya incluido en el layout). |
| `x-input-error` | Errores de validación en formularios operativos. |
| `x-input-label`, `x-text-input`, `x-primary-button`, `x-secondary-button`, `x-danger-button`, `x-checkbox` | Sólo `auth/*` y `profile/*` (estilos Breeze). No usarlos en pantallas operativas. |
| `x-modal` | Modal accesible con focus-trap (`x-on:open-modal.window` / `x-on:close-modal.window`); hoy se usa en `profile`. |
| `x-dropdown`, `x-dropdown-link`, `x-nav-link`, `x-responsive-nav-link`, `x-application-logo`, `x-auth-session-status` | Navegación Breeze (el header real del sistema no los usa). |

**Deuda visual conocida (a corregir cuando se toque cada archivo):**

**Deuda visual corregida en esta revisión:**

1. ✅ `shadow-2xs` (inexistente en Tailwind v3) → `shadow-sm` en `clientes/show.blade.php:226` y
   `creditos/create.blade.php:214`. **Ya no quedan usos de `shadow-2xs` en las vistas.**
2. ✅ Cierre de lote: `sistema.presentaciones.cambiar` es ahora **POST** y se declara **antes** del listado, por lo
   que funciona (antes el `show` del resource la capturaba como `{presentacion}`). El formulario de
   `sistema/presentaciones/index.blade.php:142` (POST + `@csrf` + confirmación `onsubmit`) es el correcto.
3. ✅ `CreditoController@pagarCuota` dejó de usar el flash `status` y ahora usa `success`, por lo que el pago
   confirmado sí se muestra en `<x-notification-modal />` (§9).

**Deuda visual conocida (a corregir cuando se toque cada archivo):**
1. Doble carga de Alpine (bundle + CDN) → `layouts/app.blade.php:9`.
2. `bg-gray-100` en `<body>` conviviendo con la paleta `slate` del contenido.
3. `border-emerald-200` sobre fondo `bg-amber-100` en `creditos/create.blade.php:187`.
4. `resources/views/sistema/presentaciones/cambiar.blade.php` está **vacío** (0 bytes) y ya no corresponde a
   ninguna ruta GET: `sistema.presentaciones.cambiar` es `POST` al controlador, así que el archivo no se usa y
   no debe tomarse como referencia de UI (la referencia real es `sistema/presentaciones/index.blade.php`).
5. El botón "EXPORTAR EXCEL" en `creditos/index.blade.php` es decorativo (no tiene `wire:`/`@click`/ruta).

---

## 15. Checklist de Validación de Interfaz (para el agente)

Antes de dar por terminada una vista, verificar:

**Estructura**
- [ ] Extiende `<x-app-layout>` (excepto `auth/*`, `creditos/pagare` y `layouts/*`).
- [ ] Usa el header compacto de §5.1 con el badge de contexto en el `<h1>`.
- [ ] Orden: header → ribbon de filtros → grilla/paneles → paginador.
- [ ] Layouts de dos columnas con `lg:w-[66.6%]` / `lg:w-[33.3%]` (o `70/30` en presentaciones).
- [ ] **El reloj del layout maestro no se duplica en ninguna vista**: se hereda de `layouts/app.blade.php`
      (ver §2). No agregar relojes, timers ni `setInterval` sueltos en las pantallas.

**Densidad y espaciado**
- [ ] Nada de `p-6`, `py-4`, `gap-6`, `mb-8`, `rounded-3xl` en pantallas operativas.
- [ ] Celdas `py-1.5 px-3`, toolbars `p-2.5`, paneles `p-3`, gaps `gap-2`/`gap-3`.
- [ ] Grilla con `divide-y` en `<tbody>` + `border-r border-slate-200/60` por celda (última sin borde).
- [ ] Empty state con `colspan` correcto y `bg-slate-50`.

**Tipografía y números**
- [ ] Todo importe, DNI, código y fecha en `font-mono`.
- [ ] Todo importe con `tabular-nums` y `text-right`.
- [ ] Formato `$ {{ number_format($v, 2, ',', '.') }}`; fechas `d/m/Y`.
- [ ] Escala tipográfica dentro de `text-xs` … `text-[8px]`.

**Color y semántica**
- [ ] Sin HEX arbitrarios ni estilos inline (sólo permitidos en `pagare.blade.php`).
- [ ] Intereses `text-red-800`; saldo exigible `text-emerald-700`; neutros `text-slate-900`.
- [ ] Estado suspendido con `rose` (fila `bg-rose-50/60`, chip `bg-rose-700 text-white`).
- [ ] Estados abierto/activo con `amber`; cerrado/cancelado con `slate` + `line-through` en el código.

**Tailwind v3**
- [ ] Prohibido `shadow-2xs`, `shadow-xs`, `inset-shadow-*`, `size-*`, `@theme` (no compilan).
- [ ] Sombras: `shadow-sm` por defecto; `shadow-2xl` sólo en toast/modales.
- [ ] Usar `focus:border-blue-500 focus:ring-blue-500` en inputs operativos (no `indigo`).

**Interacción**
- [ ] Filtros GET con `onchange="this.form.submit()"`; sin Alpine para filtrado.
- [ ] Alpine sólo para reactividad local (simulador de cuotas, modal, toast).
- [ ] Confirmación con `onsubmit="return confirm(...)"` en acciones destructivas.
- [ ] Notificación de resultado con `->with('success'|'error', ...)`; sin banners duplicados.
- [ ] Paginado con `->appends(request()->query())->links()` cuando hay filtros.

**Accesibilidad y datos**
- [ ] `title="..."` en textos truncados y en botones de icono.
- [ ] `sr-only` en botones sólo-icono cuando no hay `title` (ver `notification-modal`).
- [ ] Todo campo de formulario con `old()` y su `<x-input-error>`.
- [ ] No usar `<x-text-input>` / `<x-primary-button>` (paleta Breeze) en pantallas operativas.

---

## 16. Anexo: Cómo Verificar este Documento

Las afirmaciones de esta guía se validaron con estos comandos (ejecutables desde la raíz del proyecto):

```powershell
# 1. Paleta y utilidades realmente usadas en las vistas
Get-ChildItem -Recurse resources\views -Filter *.blade.php |
    Select-String -Pattern 'bg-blue-(600|700|800|900|950)|\bshadow-2xs\b|\bshadow-sm\b' -AllMatches |
    ForEach-Object { $_.Matches.Value } | Group-Object | Sort-Object Count -Descending

# 2. HEX / estilos inline (debe devolver sólo creditos/pagare.blade.php)
Get-ChildItem -Recurse resources\views -Filter *.blade.php |
    Select-String -Pattern '#[0-9a-fA-F]{6}|style="background'

# 3. Componentes Blade realmente usados
Get-ChildItem -Recurse resources\views -Filter *.blade.php |
    Select-String -Pattern '<x-[a-zA-Z0-9._-]+' -AllMatches |
    ForEach-Object { $_.Matches.Value } | Group-Object | Sort-Object Count -Descending

# 4. Version real de Tailwind
(Get-Content node_modules\tailwindcss\package.json -Raw | ConvertFrom-Json).version

# 5. Confirmar que ya no quedan utilidades de Tailwind v4 en las vistas (debe salir vacío)
Get-ChildItem -Recurse resources\views -Filter *.blade.php |
    Select-String -Pattern 'shadow-2xs|shadow-xs|inset-shadow-|@theme'

# 6. Confirmar que shadow-2xs NO se compila (sin resultados = clase muerta)
Select-String -Path public\build\assets\*.css -Pattern 'shadow-2xs'

# 7. Componentes Breeze fuera de auth/profile (debería salir vacío)
Get-ChildItem -Recurse resources\views -Filter *.blade.php |
    Select-String -Pattern 'x-text-input|x-primary-button|x-danger-button'
```

Archivos de referencia por sección:

| Sección | Archivos que la respaldan |
|---|---|
| §1 | `package.json`, `tailwind.config.js`, `postcss.config.js`, `resources/css/app.css`, `vite.config.js`, `resources/js/app.js`, `public/build/assets/*.css` |
| §2, §11.1 | `layouts/app.blade.php`, `layouts/guest.blade.php`, `components/notification-modal.blade.php`, `components/modal.blade.php` |
| §3, §4 | `clientes/estado-cuenta.blade.php`, `creditos/index.blade.php`, `creditos/show.blade.php`, `sistema/presentaciones/index.blade.php` |
| §5, §6 | `clientes/index.blade.php`, `creditos/index.blade.php`, `creditos/show.blade.php` |
| §7, §8 | `clientes/estado-cuenta.blade.php`, `clientes/show.blade.php`, `creditos/create.blade.php`, `sistema/presentaciones/index.blade.php` |
| §9, §10 | `clientes/form.blade.php`, `clientes/create.blade.php`, `clientes/edit.blade.php`, `creditos/create.blade.php` |
| §11.2, §11.3 | `sistema/presentaciones/index.blade.php`, `clientes/index.blade.php` |
| §12 | `clientes/show.blade.php`, `creditos/create.blade.php` |
| §13 | `creditos/pagare.blade.php`, `creditos/show.blade.php` |
| §14 | `auth/*`, `profile/*`, `dashboard.blade.php`, `sistema/usuarios/*`, `sistema/ubicaciones/*`, `sistema/index.blade.php`, `components/*` |
| §15 | `creditos/index.blade.php`, `.agents/rules/table_sorting.md` |

---

## 15. Regla General de UI: Ordenamiento Interactivo en Tablas de Resultado (Click-to-Sort)

En todas las tablas de resultado y grillas de datos del sistema (Créditos, Clientes, Recibos, Cobranzas, etc.), los encabezados de columnas ordenables **deben permitir cambiar el orden de la lista haciendo clic**, alternando entre **ascendente (`asc`)** y **descendente (`desc`)** con cada clic consecutivo.

### Requisitos Mandatarios:
1. **Alternancia de Orden (Toggle):** Clic en la columna activa invierte la dirección (`asc` <-> `desc`). Clic en otra columna la selecciona con dirección inicial.
2. **Preservación de Filtros:** La URL generada en los enlaces `<th>` debe fusionar `request()->query()` para nunca perder los filtros o parámetros de búsqueda vigentes, reseteando `page=1`.
3. **Indicador Visual:** Flecha destacada `▲` (ascendente) o `▼` (descendente) en la columna activa; icono tenue `⇅` en columnas inactivas con hover de color.
4. **Sincronización:** Tanto la paginación como la exportación a Excel / CSV y las impresiones deben aplicar exactamente el mismo criterio de ordenamiento seleccionado en pantalla.


---

## 17. Componentes de UI: Control de Caja, Turnos y Cierres Z

### 17.1 Banner de Estado de Caja en el Facturador (POS)
Ubicado en la cabecera de `resources/views/ventas/create.blade.php`:
- **🟢 Caja Abierta en Curso:** Muestra el número de turno, sucursal, fondo inicial, total facturado en efectivo y los badges dinámicos de `Ingresos (+)` y `Retiros (-)`.
- **⚠️ Caja de Jornada Anterior Pendiente:** Fondo ámbar (`bg-amber-50 border-amber-300 dark:bg-amber-950/60`), bloquea la facturación y ofrece el botón directo para realizar el Cierre Z pendiente o el cierre-apertura simultáneo.
- **🔒 Caja Cerrada:** Fondo grisáceo, bloquea la carga de artículos y presenta el botón `[🟢 Abrir Caja]` para iniciar el turno con el fondo en efectivo.

### 17.2 Modal de Movimiento Manual de Efectivo (Ingreso / Retiro)
Componente Alpine.js accesible tanto desde el POS como desde el panel de cajas:
- **Pestañas de Selección:**
  - `💸 Retiro / Gasto`: Resalta en tonos rojizos (`bg-rose-600`), pensado para pago de fletes, insumos o egresos.
  - `💰 Ingreso Extra`: Resalta en tonos esmeralda (`bg-emerald-600`), para cobranzas manuales o sencillo adicional.
- **Campo Motivo Obligatorio:** Input de texto con validación requerida. No permite registrar movimientos anónimos.
- **Historial en Vivo:** Muestra la lista de movimientos registrados en la sesión actual con usuario, hora y comprobante.

### 17.3 Pantalla de Arqueo y Cierre Z (`cajas/cerrar.blade.php`)
- **Desglose Teórico del Sistema:** Muestra el cálculo transparente:
  $$\text{Fondo Inicial} + \text{Ventas Efectivo} + \text{Ingresos} - \text{Retiros} = \text{Efectivo Teórico}$$
- **Ingreso de Efectivo Contado:** Campo de alta visibilidad para que el cajero digite el dinero físico presente en el cajón.
- **Cálculo de Diferencia / Fallo:** Con reactividad Alpine.js, calcula en tiempo real si existe sobrante (color celeste/azul) o faltante (color rojo/rosa) antes de confirmar.

### 17.4 Impresión Fiscal de Cierre Z (Ticket 80mm y A4)
La vista `cajas/comprobante.blade.php` soporta impresión dual mediante directivas CSS `@page`:
- **Ticket Térmico (80mm):** `@page { size: 80mm auto; margin: 2mm; }`, tipografía monoespaciada de alta densidad, divisores discontinuos, listado de movimientos justificados y arqueo final.
- **Formato A4:** `@page { size: A4 portrait; margin: 15mm; }`, diseño institucional para archivo contable o presentación administrativa.

---

## 18. Componentes de UI: Facturador POS y Control de Stock

### 18.1 Alerta Interactiva de Falta de Stock
- Cuando un artículo tiene stock en cero o insuficiente, el buscador y el agregado al carrito alertan al operador mediante un diálogo modal o banner preventivo.
- Permite invocar la ventana de **Ajuste Rápido por Recuento Físico**: el operador ingresa la cantidad que ve físicamente en el local y el sistema la ajusta en el acto, permitiendo facturar sin generar saldos negativos.

---

## 19. Componentes de UI: Copias de Seguridad (Backup y Restauración)

### 19.1 Panel de Copias de Seguridad (`sistema/backup/index.blade.php`)
- **Listado de Respaldos:** Grilla con nombre de archivo, tamaño en MB, fecha y hora de creación, botón de descarga directa en `.zip` y botón de restauración.
- **Botón `[💾 Crear Copia de Seguridad Ahora]`:** Genera el dump completo de la BD de forma inmediata.
- **Modal de Confirmación de Restauración:** Diálogo de máxima advertencia que aclara que el sistema creará automáticamente un auto-backup preventivo antes de reemplazar la base de datos, solicitando confirmación explícita del administrador.

---

## 20. Componentes de UI: Recepción de Mercadería, Catálogo de Productos y Etiquetas Térmicas (50×25mm)

### 20.1 Pantalla de Recepción y Alta de Mercadería (`productos/create.blade.php`)
- **Aislamiento Mandatorio de Alpine.js en `<script>`:**
  - Toda la lógica reactiva, normalización de talles/colores, búsquedas fetch y validaciones asíncronas deben declararse dentro de una función JavaScript en un bloque `<script>` dedicado (ej: `function ingresoMercaderia() { return { ... }; }`) e instanciarse como `x-data="ingresoMercaderia()"`.
  - **Prohibido:** Declarar lógica extensa con operadores relacionales (`<`, `>`, `=>`) dentro de atributos HTML directos (`x-data="{ ... }"`), ya que el tokenizador HTML del navegador interpreta el caracter `>` como el cierre prematuro de la etiqueta `<div>` y expone el código como texto plano visible.
- **Flujo Ágil de Variantes y Aviso en Tiempo Real:**
  - Al seleccionar un artículo base del proveedor (ej: `010001200755` GUARDAPOLVO ALFORZADO T. 12 SARMIENTO), si sus columnas de talle, color o código de fábrica estaban vacías en la base de datos histórica, el sistema los desglosa automáticamente desde el código de barras (`010` = prov, `00` = talle, `12` = color/segmento, `00755` = código fábrica 5 dígitos) y precarga todos los campos.
  - **Preservación de Identidad Base:** Mientras el usuario no modifique los atributos de variante, el sistema conserva exactamente el código de barras original (`010001200755`) y muestra el aviso verde `🟢 AVISO: Prenda / Variante Existente en Catálogo`, permitiendo **sumar unidades directamente al stock de la prenda seleccionada**.
  - **Detección Dinámica de Nueva Variante:** Al cambiar el talle o el color (ej: a talle 14 o color verde), se activa en tiempo real el cálculo del nuevo código CODE128 y se verifica asíncronamente:
    - **Variante Existente:** Si la nueva combinación ya existía previamente en catálogo, se mantiene en verde para sumar stock.
    - **Nueva Variante / Producto:** Si la combinación es nueva, se despliega el aviso azul (`✨ AVISO: Nueva Prenda / Variante No Existente`) y el botón conmuta a azul para dar de alta el nuevo artículo.
  - El botón de envío adapta su texto, ícono y color en tiempo real según el caso.
- **Visor Interactivo CODE128 en Tiempo Real:**
  - Desglosa visualmente los 4 segmentos del código continuo:
    `[Prov: 3c]` + `[Talle: 2c]` + `[Color: 2c]` + `[Fábrica]` = `[Código Resultante]`.
  - Normaliza reactivamente los talles numéricos (01 a 70) o letras (0S, 0M, 0L, XL, 2X, etc.) y los colores a 2 caracteres representativos.
- **Verificación Asíncrona de Unicidad:**
  - Monitorea el código generado contra `/productos/verificar-codigo`. Si el código ya existe en otra prenda, deshabilita el botón de alta y muestra un banner rojo con el nombre de la prenda existente y un botón de redirección para sumar stock a ese artículo.
- **Búsqueda Dinámica de Existentes:**
  - Si el operador escribe en el buscador de artículos del proveedor, se listan los productos coincidentes mostrando stock local, talle, color y precio, permitiendo con un clic ingresar unidades y emitir etiquetas térmicas sin duplicar el registro.

### 20.2 Catálogo y Grilla de Stock (`productos/index.blade.php`)
- Cada fila del catálogo renderiza badges compactos con la tríada de variantes:
  - `Talle: [Talle]` (badge color ámbar)
  - `Color: [Color]` (badge color violeta)
  - `Fab: [Cód. Fábrica]` (badge color azul / monoespaciado)
- La barra de filtros incorpora selectores específicos por Talle y por Color, además del filtro consolidado por Proveedor.

### 20.3 Traslados entre Sucursales (`productos/traslado.blade.php`)
- Cada renglón de mercadería seleccionada para traslado dispone de un panel desplegable **"Completar / Modificar datos"** para cargar `codigo_proveedor`, `talle` y `color` faltantes.
- Incorpora el checkbox **"Generar / Actualizar Código de Barra (CODE128)"**:
  - Si no está marcado: conserva estrictamente el código de barras preexistente de la prenda.
  - Si está marcado: recalcula el código continuo CODE128 con los 2 caracteres de talle y 2 de color sin perder la identidad del artículo.
- Si se alteró algún código de barras, la pantalla de movimientos ([`productos/movimientos.blade.php`](file:///c:/wamp64/www/laravel/creditos/resources/views/productos/movimientos.blade.php)) presenta un aviso con acceso directo para reimprimir las etiquetas de los productos trasladados.

### 20.4 Estándar Físico de Etiqueta Térmica 50mm × 25mm (`productos/etiqueta.blade.php`)
La vista de etiquetas térmicas respeta una geometría milimétrica para impresoras Zebra / Xprinter:
- **Directiva CSS de Página:** `@page { size: 50mm 25mm; margin: 0; }`
- **Contenedor:** Ancho `50mm`, alto `25mm`, sin márgenes externos y con `page-break-after: always`.
- **Cabecera Institucional:** Nombre de la tienda / sucursal y precio de venta destacado en negrita.
- **Descripción:** Limitada tipográficamente para evitar desbordes físicos.
- **Variantes:** Fila compacta `T: [Talle] • C: [Color] • Fab: [Cód. Fab]`.
- **Código de Barras:** Generado en SVG mediante `Picqer\Barcode\BarcodeGeneratorSVG` bajo la simbología `TYPE_CODE_128` (con fallback a `TYPE_CODE_39`).
- **Pie:** Código alfanumérico visible con asteriscos `* [CODIGO] *`.
- **Disparo Automático:** Si la URL recibe `autoprint=1`, ejecuta automáticamente `window.print()` tras 400ms de renderizado.

---

## 21. Componentes de UI: Tablero de Control y Métricas BI (Estilo PowerBI - `/metricas`)

### 21.1 Cinta Superior de Filtros y Presets Temporales
- **Selectores de Rango:** Inputs de tipo fecha para `fecha_desde` y `fecha_hasta` acoplados con un selector multi-sucursal.
- **Botones de Presets Inmediatos:**
  - `Hoy`: Establece desde/hasta con la fecha de hoy.
  - `7 Días`: Últimos 7 días móviles.
  - `Este Mes`: Del primer al último día del mes en curso.
  - `Mes Anterior`: Mes calendario inmediato anterior.
  - `Año Actual`: Desde el 1 de enero hasta la fecha.
  - `Histórico`: Sin límite de fecha inicial.
- **Botón `[📥 EXPORTAR EXCEL]`:** Descarga el consolidado analítico en formato tabular respetando los filtros activos.

### 21.2 Cinta de Indicadores Clave (KPIs)
Tarjetas de alta densidad de información con fondos sutiles y bordes contrastados:
1. **Ventas Brutas:** Facturación acumulada antes de bonificaciones.
2. **Descuentos Otorgados:** Total de bonificaciones aplicadas en mostrador (`ventas.descuento`).
3. **Cobro Neto:** Importe real cobrado por ventas en el período.
4. **Prendas Vendidas:** Sumatoria de cantidades de prendas/artículos comercializados.
5. **Ticket Promedio:** Importe promedio por venta.
6. **Comprobantes:** Total de transacciones emitidas.

### 21.3 Matrices Analíticas de Ventas (Slicers Interactivas)
Tablas compactas tipo PowerBI con cabeceras ordenables, barras de progreso o insignias porcentuales de participación:
- **Por Categoría y Subcategoría:** Prendas, Importe y % de participación.
- **Por Fabricante / Proveedor:** Volumen por marca o proveedor.
- **Por Sucursal:** Rendimiento geográfico (Ushuaia San Martín, Antártida, Trelew Centro, Interior).
- **Por Medio de Pago:** Efectivo, Débito, Crédito bancario, Transferencia y Crédito Personal.
- **Por Tipo de Comprobante:** Facturas A, B, Tickets y Remitos.

### 21.4 Panel de Créditos y Cobranzas
- **Tarjetas de Cartera:** Valor Base Otorgado, Total Financiado en Cuotas, Total Cobrado en Período, Deuda Exigible y Tasa de Recupero (%).
- **Grilla por Sucursal:** Evolución del crédito por plaza comercial y mora asociada.

---

## 22. Estándar Transversal de Tablas: Click-to-Sort y Exportación Fiel a Excel

### 22.1 Ordenamiento Interactivo Homogéneo (Click-to-Sort)
Todas las tablas operativas del sistema implementan el patrón interactivo de cambio de orden:
- **Estructura del Enlace en Cabecera:**
  ```blade
  <a href="{{ route('modulo.index', array_merge(request()->query(), [
      'sort' => 'columna',
      'direction' => ($sort === 'columna' && $direction === 'asc') ? 'desc' : 'asc',
      'page' => 1
  ])) }}" class="flex items-center gap-1 group">
      <span>Título</span>
      @if($sort === 'columna')
          <span class="text-blue-600 dark:text-blue-400 font-black">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
      @else
          <span class="text-slate-300 dark:text-slate-600 group-hover:text-slate-400">⇅</span>
      @endif
  </a>
  ```
- **Preservación Total de Contexto:** Nunca se eliminan los parámetros de búsqueda o filtros activos al hacer clic en un encabezado.
- **Reseteo a Página 1:** Evita inconsistencias al cambiar el orden cuando el usuario se encuentra en páginas avanzadas.

### 22.2 Exportación a Excel / CSV con Filtros Aplicados
- **Botón Estándar:** Cada vista de listado incorpora un botón `[📥 EXPORTAR EXCEL]` claramente identificado.
- **Respeto de Consulta:** La exportación clona la consulta activa en el controlador (`clone $query`), respetando los filtros de fecha, estados, categorías, texto buscado y ordenamiento seleccionado.
- **Compatibilidad Windows/Excel:** Los archivos se generan con cabecera BOM UTF-8 (`\xEF\xBB\xBF`) y delimitador punto y coma (`;`), asegurando que Excel en español abra directamente el archivo con las columnas separadas y caracteres acentuados legibles.

---

## 23. Arquitectura de Navegación y Menú Principal

### 23.1 Sub-barra de Navegación Superior (`layouts/app.blade.php`)
Barra horizontal responsiva con acceso inmediato y resaltado de ruta activa (`routeIs`):
- `🛒 POS Mostrador` (`ventas.create`)
- `🧾 Ventas` (`ventas.index`)
- `💵 Cajas & Turnos` (`cajas.index`)
- `👥 Clientes` (`clientes.index`)
- `💳 Créditos` (`creditos.index`)
- `📦 Stock & Catálogo` (`productos.index`)
- `📄 Compras & Remitos` (`remitos.index`)
- `📊 Métricas BI` (`metricas.index`)

### 23.2 Panel de Control de Inicio (`dashboard.blade.php`)
Cuadrícula de tarjetas operativas para acceso a todos los módulos:
- Punto de Venta (POS)
- **Ventas y Facturación** (Histórico y reimpresión de facturas)
- Control de Caja (Cierre Z)
- Catálogo y Stock Multi-Sede
- Compras y Remitos de Proveedores
- Clientes y Legajos Crediticios
- Créditos y Cuotas
- Recibos y Cobranzas
- **Métricas & BI** (PowerBI)
- Sistema y Auditoría Operativa

---

## 24. Componentes de UI: Descuentos por Renglón y Promociones 2x1 en Mostrador (POS)

### 24.1 Grilla de Detalle de Factura (`ventas/create.blade.php`)
- **Columna `% Desc / Promo`**:
  - Input interactivo compacto de porcentaje (`0%` a `100%`).
  - Botón de conmutación rápida `[2x1]`: asigna automáticamente 100% de bonificación al artículo y recalcula en tiempo real el neto en `$ 0,00`.
- **Botón de Duplicación `[+2x1]`**:
  - Ubicado en la columna de acciones de cada fila.
  - Duplica la prenda seleccionada como un nuevo renglón con `cantidad: 1`, `descuento_porcentaje: 100%` y `subtotal: $ 0,00`, asegurando la disminución de inventario de ambas unidades.
- **Visor de Subtotal por Renglón**:
  - Si el artículo tiene bonificación: muestra el importe neto a cobrar en verde negrita, el precio original tachado en gris, y un badge distintivo (`2x1 Gratis` para 100% o `-XX%` para descuentos parciales).
- **Visor Registradora y Totales de Factura**:
  - Discrimina claramente: `PROMOS: -$ [Total Items]` y `DESC FACT: -$ [Total Factura]`.
  - El gran total refleja con exactitud matemática el importe a cobrar al cliente.
