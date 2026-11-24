# Tiendas La Marina — Sistema de Gestión Comercial, Facturación y Créditos

Sistema integral desarrollado en **Laravel**, **Blade**, **Tailwind CSS v3** y **Alpine.js** para la administración comercial, facturación en punto de venta (POS), control de stock multiesucursal, gestión de créditos personales y control estricto de cajas y cierres Z.

---

## 🏢 Sucursales y Jurisdicciones Fiscales

El sistema gestiona de forma diferenciada la facturación y los datos fiscales según la sucursal activa:

1. **Sucursal Chubut (Trelew - Centro):**
   - **Nombre de Fantasía:** La Marina
   - **Razón Social:** Tiendas la Marina SAS
   - **CUIT:** 30-71699602-2
   - **Domicilio Comercial:** Rivadavia 249, Trelew, Chubut
   - **Página Web:** [www.tiendaslamarina.com.ar](https://www.tiendaslamarina.com.ar)

2. **Sucursal Tierra del Fuego (Ushuaia):**
   - **Nombre de Fantasía:** La Marina
   - **Razón Social:** BARZINI GUILLERMO ENRIQUE
   - **CUIT:** 20-11474524-4
   - **Domicilio Comercial:** San Martín 1255, Ushuaia, Tierra del Fuego
   - **Página Web:** [www.tiendaslamarina.com.ar](https://www.tiendaslamarina.com.ar)

---

## 📖 Manual de Usuario Operativo

Para la capacitación del personal de caja, administración y mostrador, consulte el documento integral:
- 📘 [**MANUAL_DE_USUARIO.md**](MANUAL_DE_USUARIO.md): Manual operativo dividido en 11 capítulos según los módulos del menú principal, con guías paso a paso para cada tarea operativa (facturar, anular, cambiar comprobante, facturar remitos, arqueo de caja, legajos, cobro de cuotas, etiquetas de código de barras, métricas y configuración de ARCA).

---

## 🚀 Módulos Principales del Sistema

### 1. Punto de Venta (POS) & Facturación
- Emisión ágil de comprobantes (Factura B, Ticket, etc.) con selección de medios de pago:
  - Efectivo
  - Tarjeta de Débito
  - Tarjeta de Crédito
  - Crédito Personal / Cuenta Corriente
  - Transferencia Bancaria / QR
  - Otros
- **Integración con Caja:** Obliga a que la caja de la sucursal esté abierta en el día. Si no está abierta o quedó una sesión de jornada anterior pendiente, bloquea la venta preventivamente.
- **Prevención de Stock Negativo:** Bloquea ventas de artículos con stock en cero y ofrece ventana de recuento rápido físico en mostrador para regularizar el inventario sin interrumpir la operación.

### 2. Control de Cajas, Turnos y Cierres Z
- **Apertura de Jornada:** Ingreso obligatorio del fondo inicial en efectivo antes de operar.
- **Movimientos Manuales Justificados:**
  - **Retiros / Egresos:** Para pago de fletes, insumos o adelantos con motivo obligatorio y comprobante opcional.
  - **Ingresos Extras:** Para sencillo adicional o cobros directos con motivo obligatorio.
- **Arqueo y Cierre Z:**
  - Cálculo automático:
    $$\text{Efectivo Teórico} = \text{Fondo Inicial} + \text{Ventas Efectivo} + \text{Ingresos} - \text{Retiros}$$
  - Ingreso de dinero contado y determinación en tiempo real del **Fallo de Caja** (sobrante o faltante).
- **Comprobantes Imprimibles:** Emisión de Cierre Z en formato **Ticket Térmico de 80mm** y hoja **A4**.
- **Histórico por Medios de Pago:** Reporte consolidado por rango de fechas y sucursal.

### 3. Créditos Personales & Cuentas Corrientes
- Registro y evaluación crediticia de clientes (titulares y autorizados).
- Control de límites disponibles, cálculo de cuotas e intereses de financiación.
- Cobranza de cuotas con recibos oficiales, compensación de saldos y auto-cancelación.

### 4. Control de Inventario y Stock Multiesucursal
- Catálogo de productos, códigos de barra, categorías y subcategorías.
- Control de stock por sucursal con traslados inter-sucursales, remitos de proveedor y ajustes por recuento.

### 5. Copias de Seguridad (Backup y Restauración)
- Generación de respaldos íntegros de la base de datos descargables en `.zip`.
- **Restauración Protegida:** Requiere permisos de administrador o `superuser` y ejecuta automáticamente un auto-backup preventivo antes de sobrescribir cualquier tabla.

### 6. Seguridad y Despliegue
- **Login Seguro:** Acceso restringido por usuario y contraseña encriptada (Bcrypt) sin listas desplegables expuestas.
- **Superusuario Secreto:** Usuario de rescate `superuser` invisible en listados administrativos.
- **Forzado de HTTPS:** Regla en `AppServiceProvider` para entorno de producción en `https://app.tiendaslamarina.com.ar`.

---

## 🛠️ Especificaciones Técnicas

La documentación técnica detallada se encuentra en la carpeta [`specs/`](specs/):
- [`specs/models.md`](specs/models.md): Modelos Eloquent, esquemas de base de datos, relaciones y reglas de integridad.
- [`specs/routes-and-controllers.md`](specs/routes-and-controllers.md): Tabla completa de rutas, controladores, validaciones y lógica de negocio.
- [`specs/ui-guidelines.md`](specs/ui-guidelines.md): Lineamientos visuales, componentes Blade, diseño responsivo, Alpine.js y formatos de impresión.

---

## 💻 Despliegue en Servidor de Producción

### Datos de Conexión del Servidor
- **Acceso SSH / SCP:** `root@app.tiendaslamarina.com.ar` (Host VPS interno: `vps-4866941-x`)
- **Ruta de la Aplicación:** `/var/www/html/app`
- **Dominio de Producción:** [https://app.tiendaslamarina.com.ar](https://app.tiendaslamarina.com.ar)

---

### Procedimiento de Despliegue y Sincronización

1. **En la máquina de desarrollo (Local):**
   ```bash
   git add .
   git commit -m "feat: descripción de los cambios"
   git push origin main
   ```

2. **En el servidor de producción (`root@app.tiendaslamarina.com.ar`):**
   ```bash
   # Conexión SSH
   ssh root@app.tiendaslamarina.com.ar

   # Ingresar al directorio de la aplicación
   cd /var/www/html/app

   # Actualizar código y optimizaciones
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Generación, Subida y Restauración de Base de Datos:**
   ```powershell
   # Desde local (PowerShell) generar el dump en UTF-8 puro (usando --result-file para evitar UTF-16LE):
   & "C:\wamp64\bin\mysql\mysql8.3.0\bin\mysqldump.exe" -u root -h 127.0.0.1 --default-character-set=utf8mb4 --single-transaction --quick --routines --triggers --result-file="c:\wamp64\www\laravel\creditos\database\backups\lamarina_v4_prod.sql" lamarina_v4

   # Comprimir a ZIP
   Compress-Archive -Path "c:\wamp64\www\laravel\creditos\database\backups\lamarina_v4_prod.sql" -DestinationPath "c:\wamp64\www\laravel\creditos\database\backups\lamarina_v4_prod.zip" -Force

   # Subir por SCP al servidor
   scp c:\wamp64\www\laravel\creditos\database\backups\lamarina_v4_prod.zip root@app.tiendaslamarina.com.ar:/var/www/html/app
   ```
   ```bash
   # En el servidor (SSH)
   cd /var/www/html/app
   unzip lamarina_v4_prod.zip
   mysql -u root -p lamarina_v4 < lamarina_v4_prod.sql
   rm lamarina_v4_prod.zip lamarina_v4_prod.sql
   ```
