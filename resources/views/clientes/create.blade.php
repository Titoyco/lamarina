<x-app-layout>
    <!-- Header Compacto -->
    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-2">
        <div class="flex items-center space-x-3">
            <a href="{{  url()->previous()  }}" class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight uppercase">Alta de Nuevo Cliente</h1>
            </div>
        </div>
    </div>

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        <!-- Se inyectan los paneles form.blade.php -->
        @include('clientes.form')

        <!-- Botonera Fija -->
        <div class="bg-slate-50 p-3 rounded-md border border-slate-300 flex items-center justify-end space-x-3 shadow-sm">
            <a href="{{ route('clientes.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase px-3 py-1.5">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] py-1.5 px-6 rounded uppercase transition-all shadow-sm">
                Registrar Cliente
            </button>
        </div>
    </form>
</x-app-layout>