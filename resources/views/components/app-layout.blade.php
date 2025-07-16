<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="icon" href="{{url('images/favicon.png')}}" type="image/png">
    <title>La Marina V3</title>
    <style>
        .error {
            border: 2px solid red;
        }
        .success {
            border: 2px solid green;
        }
    </style>
</head>
<body class="min-h-screen min-w-[400px] flex flex-col items-center font-sans antialiased bg-slate-500 dark:text-white/50">
    <div class="min-h-screen max-w-[1000px] w-full mt-4 text-black bg-slate-300">
        <x-header />
        <main>
            <x-menu :botones="$botones ?? []" />
            <div class="text-black bg-white dark:bg-gray-800 dark:text-white/50 p-2 min-h-screen w-full">
                {{ $slot }}
            </div>
        </main>
        <footer class="py-16 text-center text-sm text-black">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </footer>
    </div>
</body>
</html>
    




