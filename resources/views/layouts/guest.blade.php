<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="shortcut icon" href="{{ asset("images/logo_upta.png") }}" type="image/x-icon">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex gap-4 border sm:justify-center items-center p-6 bg-upta-100 dark:bg-upta-900">

            <div class="flex md:flex-row flex-col justify-center">

                <div class="w-full sm:max-w-md rounded-t-md md:rounded-l-md bg-white p-[10px] flex items-center justify-center">
                    <a href="/">
                        <x-application-logo class="max-w-[183px] max-h-[183px] w-[183px] h-[183px] md:w-[350px] md:h-[350px] md:max-w-[350px] md:max-h-[350px] text-gray-500" />
                    </a>
                </div>
    
                <div class="w-full sm:max-w-md px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden rounded-b-md md:rounded-lg">
                    {{ $slot }}
                </div>

            </div>
            
        </div>
    </body>
</html>
