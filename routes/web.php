<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\StockController;
use App\Models\Usuario;
use App\Models\Clientes;


Route::middleware(['auth', 'check.route.permission'])->group(function () {

    // Rutas para la gestión de usuarios
    Route::get('/testuser', [HomeController::class, 'testAuth'])->name('test.auth'); // Ruta de prueba para autenticación
    // Usuarios - Admin
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index'); // Listar usuarios
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create'); // Formulario para agregar usuario
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store'); // Guardar usuario


    // Cambio de clave
    Route::get('/usuarios/{user}/edit-password', [UserController::class, 'editPassword'])->name('usuarios.edit-password'); // Formulario para cambiar clave
    Route::put('/usuarios/{user}/update-password', [UserController::class, 'updatePassword'])->name('usuarios.update-password'); // Actualizar clave

    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy'); // Borrar usuario

    Route::get('/', [HomeController::class, 'index'])->name('home'); // Página de inicio


    // Rutas para clientes
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/',[ClienteController::class, 'index'])->name('index');
        Route::post('/',[ClienteController::class, 'Buscar'])->name('buscar');
        Route::get('/create',[ClienteController::class, 'create'])->name('create');
        Route::get('/search',[ClienteController::class, 'search'])->name('search');
        Route::get('/searchdni',[ClienteController::class, 'searchdni'])->name('searchdni');
        Route::post('/store',[ClienteController::class, 'store'])->name('store');
        Route::get('/{cliente}',[ClienteController::class, 'show'])->name('show');
        Route::get('/{cliente}/edit',[ClienteController::class, 'edit'])->name('edit');
        Route::put('/{cliente}',[ClienteController::class, 'update'])->name('update');
        Route::get('/{cliente}/movimientos', [ClienteController::class, 'movimientos'])->name('movimientos');
        Route::get('/{cliente}/creditos', [ClienteController::class, 'creditos'])->name('creditos');
        Route::get('/{cliente}/movimientos/imprimir', [ClienteController::class, 'imprimirMovimientos'])->name('imprimirMovimientos');
        Route::get('/{cliente}/exportarFicha', [ClienteController::class, 'exportarFicha'])->name('exportarFicha');
        Route::get('/{cliente}/imprimirFicha', [ClienteController::class, 'imprimirFicha'])->name('imprimirFicha');
        Route::get('/{cliente}/nuevoCredito', [ClienteController::class, 'nuevoCredito'])->name('nuevoCredito');
        Route::get('/{cliente}/exportar-movimientos', [ClienteController::class, 'exportarMovimientos'])->name('exportarMovimientos');
    });

    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::get('/proveedores/{id}', [ProveedorController::class, 'show'])->name('proveedores.show');
    Route::get('/proveedores/create', [ProveedorController::class, 'create'])->name('proveedores.create');
    Route::post('/proveedores/store', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/{id}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::get('/proveedores/{id}/imprimirFicha', [ProveedorController::class, 'imprimirFicha'])->name('proveedores.imprimirFicha');
    Route::get('/proveedores/{id}/exportarFicha', [ProveedorController::class, 'exportarFicha'])->name('proveedores.exportarFicha');
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');



    // Rutas para ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas/store', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{id}/edit', [VentaController::class, 'edit'])->name('ventas.edit');
    Route::get('/ventas/{id}', [VentaController::class, 'show'])->name('ventas.show');
    Route::put('/ventas/{id}', [VentaController::class, 'update'])->name('ventas.update');

    //Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->name('ventas.destroy');




    // Rutas para el CRUD de sucursales (sin destroy)
    Route::resource('sucursales', App\Http\Controllers\SucursalController::class)
    ->parameters(['sucursales' => 'sucursal'])
    ->except(['destroy']);

    Route::resource('creditos/tipo_credito', App\Http\Controllers\Tipo_creditoController::class)
    ->parameters(['tipo_credito' => 'tipo_credito'])
    ->except(['destroy']);


    // Rutas para productos
    // En routes/web.php

    Route::prefix('productos')->name('productos.')->group(function () {
                // Rutas para categorías
        Route::resource('/categorias', CategoriaController::class)
            ->parameters(['categorias' => 'categoria']);

        // Rutas para subcategorías
        Route::resource('/subcategorias', SubcategoriaController::class)
            ->parameters(['subcategorias' => 'subcategoria']);

        
        // CRUD principales
        Route::resource('/', ProductoController::class)
            ->parameters(['' => 'producto']);

        // Utilidades
        Route::get('/barcode/{id}', [ProductoController::class, 'generateBarcode'])->name('barcode');
        Route::get('/search', [ProductoController::class, 'search'])->name('search');
        Route::get('/searchcod', [ProductoController::class, 'searchcod'])->name('searchcod');

        // Stock
        Route::post('/{id}/stock/ingresar', [StockController::class, 'ingresarStock'])->name('stock.ingresar');
        Route::get('/{id}/stock/modificar', [StockController::class, 'modificarStockForm'])->name('stock.modificar.form');
        Route::put('/{id}/stock/modificar', [StockController::class, 'modificarStock'])->name('stock.modificar');
        Route::post('/{id}/stock/transferir', [StockController::class, 'transferirStock'])->name('stock.transferir');
        Route::get('/stock/bajo/lista', [StockController::class, 'productosStockBajo'])->name('stock.bajo');


    });

    // Rutas API para obtener información de stock
    Route::prefix('api/productos')->name('api.productos.')->group(function () {
        Route::get('/{id}/stock-sucursales', [ProductoController::class, 'getStockPorSucursales'])->name('stock-sucursales');
    });




    Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/pagare/{id}', [CreditoController::class, 'imprimirPagare'])->name('creditos.imprimirPagare');
    Route::get('/creditos/create', [CreditoController::class, 'create'])->name('creditos.create');
    Route::get('/creditos/{id}', [CreditoController::class, 'show'])->name('creditos.show');
    //Route::post('/creditos/store',[CreditoController::class, 'store'])->name('creditos.store');
    Route::get('/terminales', [TerminalController::class, 'obtenerTerminal'])->name('terminales');;



    // Rutas para el sistema
    Route::get('/sistema', [SistemaController::class, 'index'])->name('sistema.index');

    // Rutas para la gestión de permisos
    Route::get('/permisos', [PermisosController::class, 'index'])->name('permisos.index');
    Route::get('/permisos/{id}/edit', [PermisosController::class, 'edit'])->name('permisos.edit');
    Route::put('/permisos/{id}', [PermisosController::class, 'update'])->name('permisos.update');

    Route::post('/cambiar-sucursal', [SucursalController::class, 'cambiarSucursal'])->name('cambiar.sucursal');

});

    Route::get('/consultadeuda',[ClienteController::class, 'consultaDeuda'])->name('clientes.consultaDeuda');
    Route::get('/consultadeuda/{id}',[ClienteController::class, 'deuda'])->name('clientes.deuda');

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');



    Route::get('/bancos/{cbu}', [BancoController::class, 'obtenerBanco'])->name('cbu');;
    Route::get('/localidades/{provinciaId}', [LocalidadController::class, 'getLocalidades'])->name('localidades');


