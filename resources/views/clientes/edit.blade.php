<x-app-layout>
    <!-- Header Compacto -->
    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-2">
        <div class="flex items-center space-x-3">
            @if(request()->boolean('embed'))
                <a href="{{ route('clientes.show', ['cliente' => $cliente->id, 'embed' => 1]) }}" class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm" title="Volver a la ficha">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
            @else
                <a href="{{ url()->previous() }}" class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
            @endif
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight uppercase">
                    Modificar Legajo: <span class="text-blue-700">{{ $cliente->nombre }}</span>
                </h1>
            </div>
        </div>
    </div>

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')
        @if(request()->boolean('embed'))
            <input type="hidden" name="embed" value="1">
        @endif

        <!-- Se inyectan los paneles form.blade.php -->
        @include('clientes.form', ['cliente' => $cliente])

        <!-- Botonera Fija -->
        <div class="bg-slate-50 p-3 rounded-md border border-slate-300 flex items-center justify-end space-x-3 shadow-sm">
            <a href="{{ request()->boolean('embed') ? route('clientes.show', ['cliente' => $cliente->id, 'embed' => 1]) : route('clientes.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase px-3 py-1.5">
                Cancelar
            </a>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] py-1.5 px-6 rounded uppercase transition-all shadow-sm">
                Guardar Cambios
            </button>
        </div>
    </form>
</x-app-layout>