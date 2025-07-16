<button type="submit" {{$onclick ?? ''}} {{$attributes->merge(['class'=>'bg-blue-500 text-white border border-transparent hover:bg-blue-700 hover:border hover:border-gray-300 transition duration-200 px-2 py-1 rounded-md'])}}>
    {{ $slot }}
</button>

