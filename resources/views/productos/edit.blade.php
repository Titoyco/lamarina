<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Editar Producto</h1>

    <x-errores />

    <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Código -->
        <div class="mb-4">
            <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
            <input type="text" name="codigo" id="codigo" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" value="{{ $producto->codigo }}" required>
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
            <input type="text" name="descripcion" id="descripcion" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" value="{{ $producto->descripcion }}" required>
        </div>

        <!-- Observaciones -->
        <div class="mb-4">
            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">{{ $producto->observaciones }}</textarea>
        </div>

        <!-- Categoría -->
        <div class="mb-4">
            <label for="categoria_id" class="block text-sm font-medium text-gray-700">Categoría</label>
            <select name="categoria_id" id="categoria_id" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" required>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @if ($producto->categoria_id == $categoria->id) selected @endif>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Subcategoría -->
        <div class="mb-4">
            <label for="subcategoria_id" class="block text-sm font-medium text-gray-700">Subcategoría</label>
            <select name="subcategoria_id" id="subcategoria_id" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">
                <option value="">Seleccione una subcategoría</option>
                @foreach ($subcategorias as $subcategoria)
                    <option value="{{ $subcategoria->id }}" @if ($producto->subcategoria_id == $subcategoria->id) selected @endif>
                        {{ $subcategoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Proveedor -->
        <div class="mb-4">
            <label for="proveedor_id" class="block text-sm font-medium text-gray-700">Proveedor</label>
            <select name="proveedor_id" id="proveedor_id" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">
                <option value="">Seleccione un proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @if ($producto->proveedor_id == $proveedor->id) selected @endif>
                        {{ $proveedor->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Stock -->
        <div class="mb-4">
            <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
            <input type="number" name="stock" id="stock" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" value="{{ $producto->stock }}" min="0" required>
        </div>

        <!-- Punto de Compra -->
        <div class="mb-4">
            <label for="punto_compra" class="block text-sm font-medium text-gray-700">Punto de Compra</label>
            <input type="number" name="punto_compra" id="punto_compra" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" value="{{ $producto->punto_compra }}" min="0" required>
        </div>

        <!-- Precio de Compra -->
        <div class="mb-4">
            <label for="precio_compra" class="block text-sm font-medium text-gray-700">Precio de Compra</label>
            <input type="number" name="precio_compra" id="precio_compra" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" step="0.01" min="0" value="{{ $producto->precio_compra }}" required>
        </div>

        <!-- IVA -->
        <div class="mb-4">
            <label for="iva" class="block text-sm font-medium text-gray-700">IVA (%)</label>
            <input type="number" name="iva" id="iva" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" step="0.01" min="0" max="100" value="{{ $producto->iva }}" required>
        </div>

        <!-- Descuento -->
        <div class="mb-4">
            <label for="descuento" class="block text-sm font-medium text-gray-700">Descuento (%)</label>
            <input type="number" name="descuento" id="descuento" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" step="0.01" min="0" max="100" value="{{ $producto->descuento }}">
        </div>

        <!-- Ganancia -->
        <div class="mb-4">
            <label for="ganancia" class="block text-sm font-medium text-gray-700">Ganancia (%)</label>
            <input type="number" name="ganancia" id="ganancia" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" step="0.01" min="0" max="100" value="{{ $producto->ganancia }}" required>
        </div>

        <!-- Precio de Venta -->
        <div class="mb-4">
            <label for="precio_venta" class="block text-sm font-medium text-gray-700">Precio de Venta</label>
            <input type="number" name="precio_venta" id="precio_venta" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" step="0.01" min="0" value="{{ $producto->precio_venta }}" required>
        </div>

        <!-- Imagen -->
        <div class="mb-4">
            <label for="Imagen" class="block text-sm font-medium text-gray-700">Imagen</label>
            <input type="file" name="Imagen" id="Imagen" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">
            @if ($producto->Imagen)
                <p class="mt-2 text-sm text-gray-600">Imagen actual:</p>
                <img src="{{ asset('storage/' . $producto->Imagen) }}" alt="Imagen actual" class="w-20 h-20 object-cover">
            @endif
        </div>

        <div class="mt-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Actualizar</button>
            <a href="{{ route('productos.index') }}" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancelar</a>
        </div>
    </form>
</x-app-layout>