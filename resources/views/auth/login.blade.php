<x-app-layout>
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 w-full">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">

      <img class="mx-auto h-30 w-auto" src="{{asset('images/logo.png')}}" alt="La Marina">


      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">INGRESAR</h2>
    </div>
  
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6"  method="POST" action="{{ route('login') }}">
        @csrf
        <div>
          <label for="username" class="block text-sm/6 font-medium text-gray-900">Usuario</label>
          <div class="mt-2">
            <input type="text" name="username" id="username" autocomplete="username" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
          </div>
        </div>
  
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Clave</label>

          </div>
          <div class="mt-2">
            <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
          </div>
        </div>
  
        <div>
          <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Ingresar</button>
        </div>
      </form>
    </div>
  </div>
   
</x-app-layout>
