# Manual de Usuario Integral del Sistema — Tiendas La Marina

Bienvenido al **Manual de Usuario Operativo de Tiendas La Marina**. Esta guía ha sido diseñada para cajeros, vendedores, encargados de sucursal, personal de depósito y administradores. Describe de manera exhaustiva y paso a paso cada una de las tareas del sistema, organizadas por módulos tal como se presentan en el menú principal de navegación.

---

## Tabla de Contenidos

1. [Guía Rápida y Navegación General](#guía-rápida-y-navegación-general)
2. [Capítulo 1: Módulo Facturación y Punto de Venta (POS) — [Facturar]](#capítulo-1-módulo-facturación-y-punto-de-venta-pos--facturar)
3. [Capítulo 2: Módulo Ventas e Historial de Operaciones — [Ventas]](#capítulo-2-módulo-ventas-e-historial-de-operaciones--ventas)
4. [Capítulo 3: Módulo Control de Cajas, Turnos y Cierres Z — [Cajas]](#capítulo-3-módulo-control-de-cajas-turnos-y-cierres-z--cajas)
5. [Capítulo 4: Módulo Clientes, Cuentas Corrientes y Legajos — [Clientes]](#capítulo-4-módulo-clientes-cuentas-corrientes-y-legajos--clientes)
6. [Capítulo 5: Módulo Créditos Personales y Lotes de Presentación — [Créditos]](#capítulo-5-módulo-créditos-personales-y-lotes-de-presentación--créditos)
7. [Capítulo 6: Módulo Stock, Catálogo de Productos y Código de Barras — [Stock]](#capítulo-6-módulo-stock-catálogo-de-productos-y-código-de-barras--stock)
8. [Capítulo 7: Módulo Compras y Remitos de Proveedores — [Compras]](#capítulo-7-módulo-compras-y-remitos-de-proveedores--compras)
9. [Capítulo 8: Módulo Métricas y Business Intelligence — [Métricas]](#capítulo-8-módulo-métricas-y-business-intelligence--métricas)
10. [Capítulo 9: Módulo Recibos de Cobranza — [Recibos]](#capítulo-9-módulo-recibos-de-cobranza--recibos)
11. [Capítulo 10: Configuración del Sistema, Puntos de Venta y Servidores Fiscales ARCA — [Configuración]](#capítulo-10-configuración-del-sistema-puntos-de-venta-y-servidores-fiscales-arca--configuración)
12. [Capítulo 11: Perfil de Usuario, Seguridad y Preferencias — [Mi Perfil]](#capítulo-11-perfil-de-usuario-seguridad-y-preferencias--mi-perfil)

---

## Guía Rápida y Navegación General

### 1. Acceso al Sistema
1. Abra su navegador web (Google Chrome, Microsoft Edge o Mozilla Firefox).
2. Ingrese a la dirección web del sistema:
   - **En local / mostrador de sucursal:** `http://localhost/laravel/creditos/public/login` (o la IP asignada a su servidor local).
   - **En servidor de producción:** `https://app.tiendaslamarina.com.ar/login`.
3. Ingrese su **Nombre de Usuario o Correo Electrónico** y su **Contraseña**.
4. Presione **Iniciar Sesión**. Por motivos de seguridad y auditoría, las contraseñas están fuertemente encriptadas y cada acción queda registrada con su usuario.

### 2. Elementos de la Barra Superior de Navegación
- **Logo Institucional (Inicio):** Al presionar el logo de La Marina regresa a la pantalla principal.
- **Reloj Operativo:** Muestra la fecha y hora oficial del servidor en tiempo real.
- **Mi Perfil:** Muestra su nombre de usuario. Al hacer clic puede cambiar su clave o actualizar sus datos personales.
- **Sucursal Activa:** 
  - Si es operador/cajero, visualiza la sucursal asignada a su terminal con un indicador verde.
  - Si es Administrador, dispone de un selector desplegable para alternar de sucursal en cualquier momento (`Trelew - Centro`, `Ushuaia`, etc.).
- **Punto de Venta Local (Terminal):** Permite asignar la boca de expendio fiscal (Hasar o ARCA digital) configurada para la computadora física donde está trabajando.
- **Modo Claro / Modo Oscuro:** Botón con icono de luna/sol para conmutar el contraste visual del sistema según la iluminación de la tienda (su preferencia se recuerda en el equipo).
- **Cerrar Sesión:** Cierra su turno de trabajo de forma segura.

### 3. Mapa de Atajos de Teclado Globales (Teclas Rápidas)
El sistema está optimizado para operarse 100% mediante teclado numérico y teclas de función en mostrador:
| Tecla | Acción en el Facturador (POS) |
|---|---|
| `F2` | Buscar o Cambiar Cliente (Paso 1). |
| `F3` | Foco en buscador de artículos por Código de Barras / Descripción (Paso 2). |
| `F4` | Foco en Monto Genérico / Venta Rápida (Sucursal Trelew). |
| `F5` / `F8` | Abrir ventana de Crédito Personal y planes de cuotas (Paso 4). |
| `F10` o `ENTER` | Abrir ventana de Cobro / Finalizar Venta y Emitir Comprobante (Paso 5). |
| `ESC` | Cerrar modales activos, cancelar búsquedas o iniciar una nueva venta tras imprimir. |
| `TAB` | Desplazamiento ágil entre campos de texto. |

---

## Capítulo 1: Módulo Facturación y Punto de Venta (POS) — [Facturar]

El módulo de Punto de Venta está optimizado para la velocidad en mostrador. Permite despachar ventas con código de barras, crédito de la casa, promociones, recuento rápido de mercadería y emisión de múltiples tipos de comprobantes fiscales y comerciales.

---

### Tarea 1.1: Iniciar una Venta en Mostrador
- **Ubicación:** Menú principal > **Facturar** (`/ventas/crear`).
- **Paso a Paso:**
  1. Al ingresar a la pantalla, el sistema sitúa automáticamente el cursor en el buscador de productos (código de barras).
  2. En el encabezado superior observará los datos de la sucursal activa, la fecha del día y el estado de la caja.
  3. Por defecto, la venta se inicia configurada para **Consumidor Final (CF)** y con comprobante predeterminado **Factura B**.
- **Regla de Negocio:**
  - El sistema exige que la **Caja de la Sucursal esté Abierta** en la jornada de hoy. Si la caja está cerrada, se desplegará automáticamente la ventana para ingresar el fondo inicial antes de permitir la primera factura (ver Tarea 3.1).

---

### Tarea 1.2: Cargar Productos a la Venta
- **Paso a Paso:**
  - **Método A (Lector de Código de Barras):**
    1. Asegúrese de que el cursor esté en el campo `Código de Barras / Búsqueda (F3)`.
    2. Pase la prenda o producto frente a la pistola lectora láser.
    3. El artículo se agregará instantáneamente a la grilla con cantidad `1`. Si pasa la misma prenda nuevamente, la cantidad se incrementará automáticamente.
  - **Método B (Búsqueda por Nombre o Código Interno):**
    1. Escriba las primeras letras del producto (ej: `jean`, `remera`, `campera`, `medias`).
    2. Se desplegará una lista con sugerencias en tiempo real mostrando: Descripción, Talle/Color, Stock actual en la sucursal y Precio de venta.
    3. Con las flechas del teclado `▲` / `▼` elija el producto y presione `ENTER` para agregarlo.
  - **Método C (Monto Genérico — Venta Rápida en Trelew):**
    1. Presione `F4` para ir a la casilla `Monto Genérico ($)`.
    2. Digite el importe en pesos (ej: `15000`) y presione `ENTER`.
    3. Se agregará una línea con el artículo *"VARIOS - ARTICULO GENERICO"* con el importe ingresado.

---

### Tarea 1.3: Modificar Cantidades o Eliminar Artículos
- **Paso a Paso:**
  - **Para cambiar la cantidad:** En la columna *Cant.* de la tabla, modifique el número con el teclado o use los botones de ajuste. El total de la línea y el subtotal de la factura se recalcularán al instante.
  - **Para quitar un artículo:** Haga clic sobre el botón rojo con icono de cesto de basura `🗑️` ubicado en el extremo derecho del renglón.

---

### Tarea 1.4: Aplicar Descuentos por Artículo y Promociones
- **Objetivo:** Aplicar rebajas específicas a una prenda puntual (por ejemplo, liquidación por fin de temporada, prenda con detalle o promoción comercial).
- **Paso a Paso:**
  1. En el renglón del artículo deseado, ubique el campo **% Desc.**.
  2. Escriba el porcentaje de descuento que desea otorgar (por ejemplo `20` para un 20%, o `50` para mitad de precio).
  3. El sistema calculará automáticamente el monto bonificado en pesos (reflejado en el detalle de la venta) y actualizará el subtotal neto a cobrar.
- **Regla de Negocio:**
  - El descuento por ítem se almacena en la base de datos tanto en porcentaje como en su equivalente en pesos para garantizar la conciliación histórica de la facturación y alimentar las métricas comerciales.

---

### Tarea 1.5: Aplicar Descuento Comercial Global sobre la Factura
- **Objetivo:** Aplicar una bonificación general sobre el total final del ticket o factura.
- **Paso a Paso:**
  1. En el panel lateral derecho (Totalizador), ubique el campo **Descuento Global ($)**.
  2. Digite el importe neto que desea descontar en pesos (ej: `$1.000,00`).
  3. El total a cobrar disminuirá en ese valor exacto. Si el cliente abona con Crédito Personal, las cuotas se calcularán sobre el saldo neto resultante.

---

### Tarea 1.6: Cambiar el Tipo de Comprobante (Factura A, B, C, Remito, Presupuesto, NC)
- **Objetivo:** Emitir un documento comercial o fiscal diferente con los mismos productos que ya tiene cargados en pantalla, sin necesidad de volver a cargarlos.
- **Paso a Paso:**
  1. En la parte superior de la sección de cliente, ubique la botonera compacta de comprobantes:
     - `[Factura B]` *(Por defecto)*: Factura para Consumidor Final. Si la sucursal es Ushuaia, se emitirá automáticamente con la leyenda de exención de IVA (Ley 19.640).
     - `[Factura A]`: Para empresas y Responsables Inscriptos. **Exige** que el cliente seleccionado tenga CUIT válido de 11 dígitos. En pantalla observará el desglose en tiempo real de **Neto Gravado** e **IVA 21%**.
     - `[Factura C]`: Para operaciones bajo régimen simplificado.
     - `[Remito]`: Comprobante de entrega de mercadería. **Descuenta el stock físico** de las prendas, pero **NO se factura fiscalmente en ese momento** (queda registrado para facturarse después).
     - `[Presupuesto]`: Cotización previa. **NO descuenta stock** (permite emitirlo aunque no haya mercadería física en el local) y **NO tiene validez fiscal**.
     - `[Nota de Crédito]`: Ajuste o devolución de mercadería. **Reingresa el stock físico** al inventario de la sucursal y emite la Nota de Crédito fiscal.
  2. Haga clic sobre el botón deseado. El sistema actualizará el rótulo del comprobante y ajustará el comportamiento del inventario y la emisión.

---

### Tarea 1.7: Asignar un Cliente Registrado (`F2`)
- **Paso a Paso:**
  1. Presione la tecla `F2` o haga clic en el botón `Buscar Cliente (F2)`.
  2. Escriba el DNI, CUIT, Apellido o Nombre del cliente.
  3. A medida que escribe aparecerán las coincidencias. Seleccione al cliente deseado con las flechas y presione `ENTER`.
  4. En pantalla se cargarán:
     - Nombre completo y DNI/CUIT.
     - Estado crediticio: Semáforo verde *(Habilitado)*, amarillo *(Próximo al límite)* o rojo *(Bloqueado / En mora)*.
     - Límite de crédito asignado y saldo disponible para compras.
  5. **Para volver a Consumidor Final:** Si desea deseleccionar al cliente, haga clic en el enlace rojo `✕ Cambiar Cliente (F2)`.

---

### Tarea 1.8: Regularizar Stock por Recuento Físico en Mostrador
- **Situación:** El sistema alerta que un producto tiene stock `0` o insuficiente, pero usted tiene la prenda física en la mano en el mostrador.
- **Paso a Paso:**
  1. Al intentar agregar la prenda con stock insuficiente, se abrirá la ventana modal de **Ajuste por Recuento Rápido**.
  2. Verifique la cantidad real de prendas que tiene físicamente en el local.
  3. Ingrese la cantidad contada en el casillero `Cantidad Real en Stock`.
  4. El motivo vendrá precargado como *"Recuento físico previo a facturación en mostrador"*.
  5. Presione el botón verde **Confirmar y Regularizar Stock**.
  6. El sistema registrará un movimiento de inventario de tipo `recuento_fisico` y agregará la prenda a la factura inmediatamente, sin hacer esperar al cliente.

---

### Tarea 1.9: Cobrar con Medios de Pago Contado y Electrónicos
- **Paso a Paso:**
  1. En el panel derecho de Medios de Pago, seleccione la forma de abono:
     - **Efectivo:** Presione `ENTER` o haga clic en `Pagar Ahora`. Ingrese el dinero recibido en `Paga con:` (puede usar los botones rápidos de `$2.000`, `$5.000`, `$10.000`, `$20.000`). En pantalla verá el **Vuelto exacto a entregar**.
     - **Tarjeta de Débito / Tarjeta de Crédito:** Si la pasarela o posnet lo requiere, digite el número de cupón o transacción en el casillero de referencia.
     - **Transferencia Bancaria / QR:** Ingrese el código de comprobante o referencia bancaria.
  2. Presione el botón verde **Emitir Factura** (o el botón correspondiente al comprobante elegido).

---

### Tarea 1.10: Cobrar con Crédito Personal (La Marina)
- **Paso a Paso:**
  1. Asegúrese de tener seleccionado un cliente registrado (ver Tarea 1.7).
  2. Seleccione el medio de pago **Crédito Personal**.
  3. Presione `ENTER` o `F5`/`F8` para abrir la ventana de condiciones de financiación.
  4. Elija el plan de cuotas:
     - `1 pago`: Sin interés.
     - `2 cuotas`, `3 cuotas` o `4 cuotas`: El sistema calculará en tiempo real el valor de cada cuota fija aplicando la tasa vigente del lote activo de la sucursal.
  5. El sistema comprobará que el importe no supere el disponible del cliente ni exceda su número máximo de cuotas autorizadas.
  6. **Autorización Excepcional por Vendedor:** Si el cliente supera levemente su límite pero el encargado autoriza la compra, puede marcar la casilla `Autorización excepcional por vendedor`. La venta se procesará y quedará registrada en el legajo del cliente con el nombre del usuario responsable.
  7. Presione **Confirmar Crédito y Facturar**.

---

### Tarea 1.11: Emisión Fiscal, CAE de ARCA e Impresión
- **Paso a Paso:**
  1. Una vez confirmada la operación, el sistema ejecuta de forma atómica:
     - El alta de la venta y numeración correlativa.
     - El descuento de stock de los productos.
     - Si el punto de venta es **ARCA Digital (WSFE)**, se conecta con los servidores de la Agencia de Recaudación para obtener el **CAE (Código de Autorización Electrónico)** y la fecha de vencimiento con el código QR oficial.
     - Si el punto de venta es **Hasar**, envía la orden de impresión fiscal al controlador de la terminal.
  2. Se abrirá la ventana modal de **Venta Exitosa** mostrando la vista previa del comprobante.
  3. Dispone de botones rápidos para imprimir:
     - `🖨️ Ticket (80mm)`: Impresión térmica inmediata para entregar al cliente.
     - `📄 Hoja A4`: Factura comercial completa en duplicado.
     - `📋 Pagaré`: (Si fue a crédito personal) Imprime el pagaré con las fechas de vencimiento de las cuotas para que el cliente lo firme.
  4. Presione la tecla `ESC` o haga clic en `✕ Nueva Venta` para reiniciar el facturador y atender al siguiente cliente.

---

## Capítulo 2: Módulo Ventas e Historial de Operaciones — [Ventas]

Permite auditar todas las operaciones comerciales, aplicar filtros multidimensionales, exportar a Excel, ver comprobantes y anular transacciones.

---

### Tarea 2.1: Consultar el Historial de Ventas
- **Ubicación:** Menú principal > **Ventas** (`/ventas`).
- **Descripción:**
  - En la parte superior visualizará tarjetas con los totales del período en consulta: **Total Facturado en Pesos** y **Cantidad de Operaciones**.
  - La grilla muestra: Número de Comprobante, Fecha y Hora, Sucursal, Cliente, Medio de Pago, Crédito Asociado, Monto Total, Estado (`Completada`, `Remito`, `Presupuesto`, `Anulada`) y accesos de impresión.

---

### Tarea 2.2: Filtrar Ventas por Múltiples Criterios
- **Paso a Paso:**
  1. Utilice la barra de filtros superior:
     - **Buscador General:** Ingrese parte del número de comprobante (ej: `0002-00000001`), nombre del cliente o documento.
     - **Sucursal / Región:** Filtre por sucursal específica o por jurisdicción completa (`Tierra del Fuego` o `Chubut`).
     - **Medio de Pago:** Elija Efectivo, Débito, Crédito o Crédito Personal.
     - **Tipo de Comprobante:** Filtre exclusivamente Facturas B, Facturas A, Facturas C, Remitos, Presupuestos o Notas de Crédito.
     - **Fechas Desde / Hasta:** Defina el período calendario exacto.
  2. Haga clic en **Filtrar**. La tabla y los totales se actualizarán al instante.
  3. Para restablecer todos los filtros, presione el botón gris `✕`.

---

### Tarea 2.3: Cambiar el Orden de las Tablas (Click-to-Sort)
- **Paso a Paso:**
  1. Haga clic sobre cualquier encabezado de columna que desee ordenar: *Comprobante*, *Fecha / Hora*, *Sucursal*, *Cliente*, *Medio de Pago* o *Total*.
  2. Al primer clic se ordenará de forma ascendente `▲`; al segundo clic cambiará a descendente `▼`.
  3. Sobre la tabla aparecerá una pastilla azul informando el orden activo. Puede hacer clic en *"Restablecer orden predeterminado"* para volver a la fecha más reciente.

---

### Tarea 2.4: Exportar el Listado Filtrado a Excel / CSV
- **Paso a Paso:**
  1. Aplique los filtros deseados en la barra superior (por ejemplo, todas las ventas de la semana en efectivo).
  2. Haga clic en el botón verde **📥 Exportar a Excel**.
  3. Se descargará un archivo `.csv` / `.xlsx` estructurado que contiene **exactamente las filas resultantes de su búsqueda**, respetando las fechas y filtros elegidos.

---

### Tarea 2.5: Ver el Detalle Completo de una Venta (`ventas.show`)
- **Paso a Paso:**
  1. En la fila de la venta deseada, haga clic sobre el icono de ojo `👁️` o sobre el número de comprobante.
  2. Accederá a la ficha completa de la venta, donde se detallan:
     - Datos fiscales del emisor y receptor.
     - Si posee CAE de ARCA, su código de 14 dígitos y fecha de vencimiento.
     - Lista completa de artículos con cantidades, precios unitarios, descuentos aplicados y subtotales.
     - Desglose de impuestos (Neto Gravado, IVA 21% o Exención Ley 19.640).
     - Datos del crédito y cuotas asociadas si fue abonada a crédito.

---

### Tarea 2.6: Facturar un Remito Pendiente
- **Objetivo:** Emitir la factura fiscal oficial de mercadería que previamente salió del local con un Remito.
- **Paso a Paso:**
  1. En el listado de ventas, localice el remito (estará identificado con la etiqueta amarilla `Remito`).
  2. Ingrese a ver el detalle del comprobante.
  3. En la parte superior derecha verá el botón **`🧾 Facturar Remito`**.
  4. En la ventana emergente, seleccione el tipo de factura que emitirá (`Factura B`, `Factura A` o `Factura C`).
  5. Presione **Confirmar Emisión**.
  6. El sistema solicitará la autorización a ARCA, asignará el nuevo número de factura fiscal y marcará el remito como completado.
- **Regla de Negocio:**
  - Este procedimiento **NO descuenta stock nuevamente**, ya que la mercadería fue descontada físicamente cuando se emitió el remito original.

---

### Tarea 2.7: Cargar un Presupuesto al Facturador
- **Objetivo:** Concretar la venta de una cotización entregada con anterioridad a un cliente.
- **Paso a Paso:**
  1. Ubique el comprobante del presupuesto en el listado de ventas.
  2. Ingrese al detalle y presione el botón violeta **`🛒 Cargar en Facturador`**.
  3. El sistema lo redirigirá a la pantalla de cobro del POS (`/ventas/crear`) con **todos los productos, cantidades y el cliente cargados automáticamente**.
  4. Seleccione el medio de pago y emita la factura.

---

### Tarea 2.8: Anular una Venta y Generar Nota de Crédito
- **Paso a Paso:**
  1. Ingrese al detalle de la venta que necesita anular.
  2. En el encabezado, presione el botón rojo **`🚫 Anular Venta`**.
  3. En la ventana emergente, escriba de manera obligatoria el **Motivo de la anulación** (mínimo 5 caracteres; ej: *"Cliente devuelve prenda por talle incorrecto"*).
  4. Haga clic en **Confirmar Anulación**.
- **Acciones que realiza el sistema automáticamente:**
  - Si los productos descontaron stock, **las unidades se reintegran de forma automática** al inventario de la sucursal registrando un movimiento de devolución.
  - Si fue a crédito personal y no registra cuotas cobradas, el crédito y su plan de cuotas se anulan.
  - Si la venta tenía CAE de ARCA, se generará la correspondiente **Nota de Crédito Electrónica** oficial.
  - El estado de la venta cambiará a `Anulada`.

---

### Tarea 2.9: Reimprimir Comprobantes (Ticket y A4)
- **Paso a Paso:**
  - Desde el listado de ventas o desde la ficha de detalle, dispone de dos botones de impresión:
    - **Ticket (80mm):** Abre la versión térmica para rollos de caja con formato compacto y código QR.
    - **Hoja A4:** Abre la factura en tamaño carta/A4 con membrete, tabla discriminada de IVA y talón de firma.
  - Presione `Ctrl + P` o haga clic en el botón verde superior `Imprimir` para enviar a la impresora.

---

## Capítulo 3: Módulo Control de Cajas, Turnos y Cierres Z — [Cajas]

Garantiza la trazabilidad del efectivo, el control estricto de los fondos de caja y la auditoría al finalizar el turno comercial.

---

### Tarea 3.1: Realizar la Apertura de Caja al Iniciar el Turno
- **Ubicación:** Menú principal > **Cajas** (`/cajas`) o mediante la ventana automática que aparece al ingresar a Facturar.
- **Paso a Paso:**
  1. Al iniciar la jornada, el sistema informará que no hay una caja abierta para la sucursal.
  2. Haga clic en **Abrir Caja de Turno**.
  3. Ingrese el **Monto Inicial en Efectivo** (fondo fijo o cambio recibido para comenzar el día; ej: `$20.000,00`).
  4. Ingrese una observación si lo considera necesario.
  5. Presione **Confirmar Apertura de Caja**.
  6. La caja quedará habilitada y se autorizará de inmediato la emisión de tickets y facturas.

---

### Tarea 3.2: Regularizar Cajas de Jornadas Anteriores sin Cerrar
- **Situación:** El operador del día anterior olvidó realizar el Cierre Z antes de retirarse.
- **Regla del Sistema:** Por seguridad contable, no se puede facturar en un día nuevo arrastrando la caja de una jornada anterior.
- **Paso a Paso:**
  1. El sistema mostrará una pantalla de bloqueo con el mensaje: *"La caja abierta pertenece a una jornada anterior (DD/MM/AAAA). Debe realizar el Cierre Z de la jornada previa"*.
  2. Presione el botón **Cerrar Jornada Anterior y Abrir Nueva Caja**.
  3. El sistema registrará el arqueo y cierre del turno pendiente e iniciará la sesión de la jornada actual solicitando su fondo inicial.

---

### Tarea 3.3: Registrar un Retiro o Egreso Manual de Dinero
- **Objetivo:** Registrar salidas de dinero en efectivo de la gaveta para pagos inmediatos (ej: flete, insumos de limpieza, café, adelanto de sueldo menor).
- **Paso a Paso:**
  1. En el panel de control de caja, ubique la sección **Movimientos de Caja**.
  2. Haga clic en el botón rojo **Registrar Retiro / Egreso**.
  3. Ingrese el **Monto en Pesos** a retirar.
  4. Seleccione el **Motivo / Concepto** e ingrese una descripción detallada obligatoria.
  5. (Opcional) Digite el número de comprobante o factura del gasto.
  6. Presione **Guardar Retiro**. El saldo teórico de efectivo en caja se descontará inmediatamente.

---

### Tarea 3.4: Registrar un Ingreso Extraordinario de Dinero
- **Objetivo:** Registrar entrada de dinero a la caja no proveniente de ventas de mostrador (ej: reposición de cambio/sencillo por parte del encargado o cobranza externa).
- **Paso a Paso:**
  1. En la sección Movimientos de Caja, presione el botón azul **Registrar Ingreso Extra**.
  2. Ingrese el importe en pesos y el motivo detallado.
  3. Presione **Guardar Ingreso**. El monto se sumará al saldo teórico de efectivo.

---

### Tarea 3.5: Realizar el Arqueo y Cierre Z de Caja
- **Objetivo:** Contar el dinero físico al terminar el turno, comparar contra el sistema y cerrar la sesión.
- **Paso a Paso:**
  1. Ingrese a **Cajas** y presione el botón amarillo **Realizar Arqueo y Cierre Z**.
  2. En pantalla se exhibirá el cálculo teórico:
     $$\text{Efectivo Teórico} = \text{Fondo Inicial} + \text{Ventas Efectivo} + \text{Ingresos Manuales} - \text{Retiros}$$
  3. En el casillero **Efectivo Físico Contado**, ingrese la suma total de billetes y monedas que realmente hay en la gaveta.
  4. En tiempo real, el sistema calculará la **Diferencia (Fallo de Caja)**:
     - `$0,00`: Caja exacta.
     - Valor positivo en verde: **Sobrante de caja**.
     - Valor negativo en rojo: **Faltante de caja**.
  5. Verifique también los totales computados para Tarjetas, Transferencias y Crédito Personal.
  6. Ingrese las observaciones de cierre que considere pertinentes.
  7. Presione el botón rojo **Confirmar Cierre Z de Caja**.
  8. La sesión quedará cerrada, inhabilitando nuevas ventas hasta la próxima apertura.

---

### Tarea 3.6: Imprimir el Comprobante de Cierre Z
- **Paso a Paso:**
  1. Una vez cerrado el turno, el sistema ofrecerá dos opciones de impresión:
     - **Ticket Cierre Z (80mm):** Resumen en tira térmica para colocar junto con el dinero rendido.
     - **Planilla A4 Cierre Z:** Documento contable detallado con firma del cajero y del supervisor para la administración central.

---

## Capítulo 4: Módulo Clientes, Cuentas Corrientes y Legajos — [Clientes]

Gestiona la base de clientes de la tienda, su ficha personal, líneas de crédito y personas autorizadas.

---

### Tarea 4.1: Buscar Clientes en el Directorio
- **Ubicación:** Menú principal > **Clientes** (`/clientes`).
- **Paso a Paso:**
  1. En el campo de búsqueda escriba el DNI, CUIT, Apellido o Nombre del cliente.
  2. Puede filtrar por estado crediticio: *Todos*, *Habilitados*, *En Mora* o *Suspendidos*.
  3. La tabla listará los clientes con su saldo adeudado y su límite de crédito.

---

### Tarea 4.2: Dar de Alta un Nuevo Cliente
- **Paso a Paso:**
  1. En la esquina superior derecha presione el botón **+ Nuevo Cliente**.
  2. Complete los campos obligatorios:
     - **Nombre y Apellido** (o Razón Social).
     - **Tipo y Número de Documento:** Ingrese DNI para personas particulares, o CUIT (11 dígitos continuos) si el cliente requerirá Facturas tipo A.
     - **Teléfono y Celular:** Esenciales para avisos de vencimiento de cuotas.
     - **Correo Electrónico.**
     - **Domicilio Completo:** Calle, número, piso/depto, barrio y localidad.
     - **Sucursal de Origen:** Localidad donde abre la cuenta.
     - **Límite de Crédito Inicial:** Importe máximo que el cliente puede adeudar (ej: `$150.000,00`).
  3. Presione **Guardar Cliente**.

---

### Tarea 4.3: Modificar y Actualizar Datos de un Cliente
- **Paso a Paso:**
  1. En la fila del cliente haga clic en el botón azul de edición `✏️`.
  2. Modifique la dirección, teléfonos o CUIT.
  3. Presione **Actualizar Datos**.

---

### Tarea 4.4: Consultar el Legajo Integral del Cliente (`clientes.show`)
- **Paso a Paso:**
  1. Haga clic sobre el nombre del cliente o sobre el icono de legajo `📋`.
  2. Se abrirá la ficha completa donde podrá consultar:
     - **Margen de Crédito:** Total asignado, saldo utilizado y disponible en tiempo real.
     - **Deuda Exigible:** Cuotas vencidas o por vencer con sus fechas límite.
     - **Historial de Compras:** Comprobantes emitidos al cliente.
     - **Historial de Créditos y Cuotas:** Estado de cada cuota (Paga, Pendiente, En mora).
     - **Muro de Novedades y Auditoría:** Registro cronológico de llamados, acuerdos de pago y observaciones comerciales.

---

### Tarea 4.5: Modificar el Límite de Crédito de un Cliente
- **Paso a Paso:**
  1. Ingrese a la ficha del cliente.
  2. En la tarjeta de crédito presione **Modificar Límite**.
  3. Ingrese el nuevo monto autorizado y escriba la justificación (ej: *"Aumento por buen comportamiento de pago e ingresos demostrables"*).
  4. Presione **Guardar Nuevo Límite**.

---

### Tarea 4.6: Cargar Novedades o Notas de Cobranza al Legajo
- **Paso a Paso:**
  1. En la ficha del cliente, diríjase a la sección **Novedades del Legajo**.
  2. Escriba la nota (ej: *"Cliente se comunica telefónicamente; promete pasar a cancelar cuota 2 el próximo viernes"*).
  3. Presione **Agregar Comentario**. La nota quedará fechada con su usuario para que cualquier cajero la lea en mostrador.

---

### Tarea 4.7: Administrar Personas Autorizadas para Comprar
- **Objetivo:** Habilitar a familiares (cónyuge, hijos, etc.) para retirar mercadería a cuenta del titular.
- **Paso a Paso:**
  1. En el legajo del cliente, ubique la pestaña **Personas Autorizadas**.
  2. Haga clic en **+ Agregar Autorizado**.
  3. Ingrese: Nombre y Apellido, DNI, Teléfono y Parentesco.
  4. Presione **Guardar Autorizado**. En el mostrador, cuando el autorizado se presente con su DNI, el cajero podrá validar la autorización inmediatamente.

---

## Capítulo 5: Módulo Créditos Personales y Lotes de Presentación — [Créditos]

Gestiona la cartera de financiación propia de la empresa, cobro de cuotas, cálculo de intereses y emisión de pagarés.

---

### Tarea 5.1: Consultar la Cartera de Créditos
- **Ubicación:** Menú principal > **Créditos** (`/creditos`).
- **Descripción:**
  - Visualice el listado de todos los créditos otorgados por la empresa.
  - Filtre por número de crédito, cliente, sucursal o estado (`Al día`, `En mora`, `Cancelado`).

---

### Tarea 5.2: Cobrar una Cuota de Crédito en Mostrador
- **Paso a Paso:**
  1. Ingrese a la ficha del crédito desde el listado o busque al cliente.
  2. En la tabla de cuotas observará las cuotas del 1 al 4 con su vencimiento, valor base, intereses devengados y saldo.
  3. Marque la casilla de la cuota que el cliente desea pagar.
  4. Seleccione el medio de pago con el que abona (Efectivo, Débito, Transferencia).
  5. Presione **Procesar Cobranza y Emitir Recibo**.
  6. El sistema imputará el pago cancelando primero recargos e intereses y luego capital, emitiendo el **Recibo Oficial de Cobranza** para el cliente.

---

### Tarea 5.3: Imprimir o Reimprimir el Pagaré de Crédito
- **Paso a Paso:**
  1. En la ficha del crédito, presione el botón dorado **`📋 Pagaré de Crédito`**.
  2. Se generará el documento legal en formato A4 donde constan las firmas, montos en letras y números, fechas de vencimiento de las cuotas y condiciones contractuales de Tiendas La Marina.
  3. Envíe a imprimir para su firma física por parte del titular.

---

### Tarea 5.4: Gestionar Lotes y Presentaciones de Créditos
- **Objetivo:** Controlar las presentaciones mensuales que agrupan los créditos para su rendición bancaria y seguimiento por lote.
- **Paso a Paso:**
  1. En la vista de Créditos, acceda a la pestaña **Lotes / Presentaciones**.
  2. Verifique la presentación activa de la sucursal y la tasa de interés vigente normalizada (ej: 6% mensual).
  3. Los nuevos créditos se vincularán automáticamente al lote activo hasta que el administrador cierre la presentación e inicie el ciclo siguiente.

---

## Capítulo 6: Módulo Stock, Catálogo de Productos y Código de Barras — [Stock]

Control integral de mercaderías, costos, precios de venta al público, categorías y etiquetado con código de barras.

---

### Tarea 6.1: Consultar el Catálogo e Inventario
- **Ubicación:** Menú principal > **Stock** (`/productos`).
- **Paso a Paso:**
  1. Use el buscador para ubicar productos por código de barras, código de artículo o descripción (ej: `4010`, `Jean Oxford`, `Remera estampada`).
  2. Filtre por **Categoría** (Hombre, Mujer, Niños, Accesorios), por **Fabricante/Proveedor** o por **Disponibilidad de Stock**.
  3. La grilla muestra el stock consolidado y el precio de venta actualizado.

---

### Tarea 6.2: Dar de Alta un Nuevo Producto
- **Paso a Paso:**
  1. Presione el botón verde **+ Nuevo Producto**.
  2. Complete los campos de la ficha:
     - **Código Interno** y **Código de Barras:** Digite el código EAN o dispare con la pistola sobre la etiqueta original de fábrica.
     - **Descripción:** Nombre claro de la prenda (ej: *"Remera Jersey Manga Corta Estampada"*).
     - **Rubro / Categoría y Subcategoría:** Clasificación para ordenamiento y reportes de métricas.
     - **Fabricante / Proveedor:** Marca o taller confeccionista.
     - **Precio de Costo ($):** Monto abonado al proveedor.
     - **Margen de Ganancia (%) o Precio de Venta ($):** Al ingresar el margen deseado, el sistema calcula el precio sugerido de venta al público.
     - **Controlar Stock:** Marque esta casilla si la prenda debe descontarse físicamente con cada venta.
  3. Presione **Guardar Producto**.

---

### Tarea 6.3: Modificar Precios y Datos de un Producto
- **Paso a Paso:**
  1. En el listado de productos, presione el icono azul de edición `✏️`.
  2. Actualice el precio de venta o modifique la descripción.
  3. Presione **Guardar Cambios**. El nuevo precio estará disponible de inmediato en todos los mostradores.

---

### Tarea 6.4: Consultar Stock Multisucursal de una Prenda
- **Paso a Paso:**
  1. En el catálogo de productos, haga clic sobre el botón de existencias de un producto.
  2. Se abrirá una ventana mostrando la cantidad disponible en cada local de la empresa:
     - Sucursal Trelew (Centro)
     - Sucursal Ushuaia (San Martín)
     - Depósito Central
  3. Esto le permite responderle inmediatamente al cliente si una prenda o talle está disponible en otra sucursal.

---

### Tarea 6.5: Imprimir Etiquetas con Código de Barras
- **Objetivo:** Imprimir etiquetas adhesivas para colocar en las prendas antes de exhibirlas en el salón de ventas.
- **Paso a Paso:**
  1. En el listado de productos, marque la casilla del producto o productos que desea etiquetar.
  2. Presione el botón **🏷️ Imprimir Etiquetas**.
  3. En la ventana de configuración:
     - **Formato de Salida:** Elija entre **Rollo Térmico Continuo** (impresora de etiquetas zebra/pos) o **Planilla A4 con Grilla**.
     - **Cantidad de Etiquetas por Producto:** Digite cuántas prendas va a marcar (ej: `12` para una docena de remeras).
  4. Presione **Generar e Imprimir**. Se abrirá el documento listo con el código de barras legible por escáner, nombre de la tienda, talle, descripción y precio de venta.

---

## Capítulo 7: Módulo Compras y Remitos de Proveedores — [Compras]

Ingreso formal de mercaderías enviadas por proveedores mayoristas, actualización de costos e incremento automático del inventario.

---

### Tarea 7.1: Registrar el Ingreso de un Remito de Proveedor
- **Ubicación:** Menú principal > **Compras** (`/remitos`).
- **Paso a Paso:**
  1. Presione el botón **+ Nuevo Remito de Compra**.
  2. Ingrese los datos de cabecera:
     - **Proveedor:** Seleccione la fábrica o distribuidor mayorista.
     - **Número de Remito / Factura del Proveedor:** Número que figura en el papel de entrega.
     - **Fecha de Recepción.**
     - **Sucursal de Destino:** Local físico donde se descarga la mercadería.
  3. Presione **Crear Remito**.

---

### Tarea 7.2: Cargar Artículos al Remito de Compra
- **Paso a Paso:**
  1. En el detalle del remito, busque los productos que llegaron en el paquete.
  2. Para cada producto ingrese:
     - Cantidad de unidades recibidas.
     - Precio de costo unitario pactado.
     - Precio de venta al público sugerido.
  3. Presione **Agregar Renglón**. Repita para todos los artículos que vinieron en el envío.

---

### Tarea 7.3: Confirmar el Remito e Impactar en el Stock
- **Paso a Paso:**
  1. Revise que la cantidad total de prendas coincida con el bulto físico recibido.
  2. Presione el botón verde **Confirmar e Ingresar al Stock**.
  3. El sistema sumará automáticamente las unidades al stock de la sucursal receptora y actualizará los costos del catálogo. El remito quedará registrado como histórico auditado.

---

## Capítulo 8: Módulo Métricas y Business Intelligence — [Métricas]

Panel analítico ejecutivo estilo PowerBI para la dirección y gerencia. Permite evaluar la rentabilidad, ventas por rubro, cobros y descuentos aplicados.

---

### Tarea 8.1: Interpretar los Indicadores Clave (KPIs)
- **Ubicación:** Menú principal > **Métricas** (`/metricas`).
- **Indicadores Principales en Pantalla:**
  - **Facturación Bruta:** Total vendido a precios de lista.
  - **Total Descuentos:** Suma de todas las bonificaciones otorgadas (descuentos por ítem, promociones y descuentos globales de factura).
  - **Facturación Neta:** Dinero real comercializado: $\text{Facturación Neta} = \text{Bruta} - \text{Descuentos}$.
  - **Total Prendas Vendidas:** Volumen físico de prendas comercializadas.
  - **Ticket Promedio:** Gasto promedio por cliente por operación.
  - **Cobranzas de Crédito:** Dinero recuperado de la cartera de crédito personal.

---

### Tarea 8.2: Filtrar Métricas por Período de Tiempo
- **Paso a Paso:**
  1. En la botonera rápida superior elija uno de los presets predefinidos:
     - `Hoy` | `Últimos 7 días` | `Este Mes` | `Mes Anterior` | `Año Actual` | `Histórico Completo`.
  2. O bien elija un rango exacto con los selectores de calendario `Fecha Desde` y `Fecha Hasta`.
  3. Todas las tablas, gráficos y totales se recalcularán automáticamente en milisegundos.

---

### Tarea 8.3: Analizar Tablas Multidimensionales de Rendimiento
- **Secciones Disponibles:**
  - **Por Categoría y Subcategoría:** Identifique qué sectores generan mayor rentabilidad (ej: Dama, Caballero, Blanco, Calzado).
  - **Por Fabricante / Proveedor:** Muestra qué marcas tienen mayor rotación en las tiendas.
  - **Por Sucursal:** Comparativa en paralelo entre Trelew y Ushuaia.
  - **Por Medio de Pago:** Distribución porcentual entre efectivo, tarjetas de débito/crédito y cuenta corriente.
  - **Por Tipo de Comprobante:** Volumen de Facturas B, Facturas A, Remitos y Notas de Crédito.

---

### Tarea 8.4: Exportar las Métricas a Planilla de Cálculo
- **Paso a Paso:**
  1. Aplique los filtros temporales y de sucursal que desea reportar.
  2. Presione el botón **📥 Exportar Métricas a Excel**.
  3. Se descargará un archivo estructurado con todos los cuadros tabulados para armar reportes de directorio o balances contables.

---

## Capítulo 9: Módulo Recibos de Cobranza — [Recibos]

Auditoría y control de los comprobantes emitidos por pagos de clientes sobre sus cuentas de crédito.

---

### Tarea 9.1: Consultar Recibos Emitidos
- **Ubicación:** Menú principal > **Recibos** (`/recibos`).
- **Descripción:** Muestra la lista de todos los recibos de cobro confeccionados, indicando fecha, número de recibo, cliente, importe cobrado y cajero que recibió el dinero.

---

### Tarea 9.2: Reimprimir un Recibo de Cobranza
- **Paso a Paso:**
  1. Busque el recibo por nombre de cliente o número.
  2. Haga clic en **Imprimir Ticket** (para rollo de 80mm) o **Imprimir A4**.
  3. Entregue la constancia al cliente si la extravió.

---

## Capítulo 10: Configuración del Sistema, Puntos de Venta y Servidores Fiscales ARCA — [Configuración]

Administración técnica de controladores fiscales, facturación digital ante la Agencia de Recaudación y Control Aduanero (ARCA/AFIP) y respaldos.

---

### Tarea 10.1: Administrar Puntos de Venta
- **Ubicación:** Menú superior derecho > Puntos de Venta (`/sistema/puntos-venta`).
- **Paso a Paso:**
  1. Visualice las bocas de expendio dadas de alta por sucursal.
  2. Cada punto de venta tiene asignada su modalidad:
     - `fiscal_hasar`: Conectado a controlador físico Hasar 2G en la red local.
     - `arca_wsfe`: Facturación electrónica digital directa mediante Web Services de AFIP.
  3. Puede editar la IP, puerto o crear un nuevo punto de venta para un nuevo puesto de trabajo.

---

### Tarea 10.2: Configurar Facturación Digital ARCA (WSAA + WSFEv1)
- **Requisitos de Certificados:**
  1. Obtenga en la web de AFIP el Certificado Digital X.509 (`.crt`) y la Clave Privada (`.key`) vinculada al CUIT correspondiente.
  2. Almacene los archivos de forma segura dentro de la carpeta protegida:
     - `storage/app/arca/arca_homo.crt` y `arca_homo.key` *(Ambiente de prueba / homologación)*.
     - `storage/app/arca/arca_prod.crt` y `arca_prod.key` *(Ambiente real de producción)*.
  3. El sistema nunca sube estos archivos a repositorios públicos por directiva de seguridad.
  4. Los tokens de autorización (TA) y tickets de acceso se generan y renuevan automáticamente en `storage/app/arca/tokens/`.

---

### Tarea 10.3: Probar Conexión y Emisión Digital en Homologación
- **Paso a Paso:**
  1. En la lista de Puntos de Venta, ubique aquel con modalidad `arca_wsfe`.
  2. Presione el botón **Probar Emisión Homologación**.
  3. El sistema consultará el estado de los servidores de ARCA (`AppServer`, `DbServer`, `AuthServer`), obtendrá el último comprobante autorizado (`GetLastVoucher`) y generará una factura de prueba solicitando CAE.
  4. En pantalla se exhibirá el resultado exitoso con el número de CAE de 14 dígitos otorgado por el organismo fiscal.

---

### Tarea 10.4: Asignar el Punto de Venta en la Terminal de Trabajo
- **Paso a Paso:**
  1. En la barra superior, haga clic en el icono de impresora / terminal fiscal.
  2. En la ventana emergente elija qué punto de venta corresponde a la computadora física donde está sentado (ej: *"P.V. 0002 - Mostrador Central"*).
  3. Presione **Guardar Puesto Local**. Esta configuración se recordará automáticamente en ese equipo para todas las facturas que emita.

---

### Tarea 10.5: Generar y Descargar una Copia de Seguridad (Backup)
- **Paso a Paso:**
  1. Ingrese a la sección de **Copias de Seguridad**.
  2. Presione el botón **Generar Backup Completo**.
  3. El sistema volcará toda la base de datos MySQL (ventas, productos, clientes, stock, cajas) y generará un archivo comprimido descargable `.zip` con fecha y hora.
  4. Guarde el archivo en un disco externo o almacenamiento seguro.
  - **Restauración:** Requiere clave de Administrador y realiza un auto-respaldo preventivo antes de sobrescribir cualquier tabla para evitar pérdida involuntaria de datos.

---

## Capítulo 11: Perfil de Usuario, Seguridad y Preferencias — [Mi Perfil]

Ajustes individuales de la cuenta del operador y ergonomía de trabajo.

---

### Tarea 11.1: Modificar Nombre, Correo y Contraseña
- **Ubicación:** Menú superior derecho > **Mi Perfil** (`/profile`).
- **Paso a Paso:**
  1. Ingrese su nuevo nombre o correo si hubo modificaciones.
  2. Para cambiar su contraseña:
     - Ingrese su **Contraseña Actual**.
     - Ingrese su **Nueva Contraseña** (mínimo 8 caracteres combinando letras y números).
     - Confirme la nueva contraseña.
  3. Presione **Guardar Cambios**.

---

### Tarea 11.2: Cambiar la Sucursal Activa (Solo Administradores)
- **Paso a Paso:**
  1. En la barra superior, localice el selector desplegable **Sucursal Activa (Admin)**.
  2. Seleccione la sucursal a la que desea conmutar (ej: pasar de *Trelew* a *Ushuaia*).
  3. La página se recargará automáticamente mostrando el stock, las cajas, los lotes de crédito y los datos fiscales de la sucursal seleccionada.

---

### Tarea 11.3: Conmutar entre Modo Oscuro y Modo Claro
- **Paso a Paso:**
  1. En la barra superior, haga clic en el botón con icono de Luna / Sol.
  2. El sistema alternará entre la paleta clara estándar y la paleta oscura de alto contraste visual (Dark Mode), reduciendo el cansancio visual en turnos prolongados.
  3. Su elección se mantiene guardada en su computadora de forma permanente.

---

*Manual de Usuario de Tiendas La Marina — Versión 4.2.*  
*Sistema de Gestión Comercial, Facturación Digital ARCA (WSFEv1), Control de Cajas y Cartera de Créditos.*
