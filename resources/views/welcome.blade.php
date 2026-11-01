<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('UPTA PNFI - Project Management') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 flex flex-col min-h-screen">
        
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <x-application-logo class="block h-8 w-auto fill-current text-upta-700" />
                            <span class="ml-3 font-bold text-xl text-upta-900 tracking-tight">UPTA PNFI</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-upta-700 hover:text-upta-900">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-upta-900 transition">{{ __('Log in') }}</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">{{ __('Register') }}</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-1">
            <div class="relative bg-upta-900 overflow-hidden">
                <!-- Patrón de fondo -->
                <div class="absolute inset-0">
                    <svg class="absolute left-full transform -translate-y-3/4 -translate-x-1/4 md:-translate-y-1/2 lg:-translate-x-1/2" width="404" height="784" fill="none" viewBox="0 0 404 784">
                        <defs>
                            <pattern id="5d0dd344-b041-4d26-bec4-8d33ea57ec9b" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-upta-800" fill="currentColor"></rect>
                            </pattern>
                        </defs>
                        <rect width="404" height="784" fill="url(#5d0dd344-b041-4d26-bec4-8d33ea57ec9b)"></rect>
                    </svg>
                </div>
                
                <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl">
                        <span class="block">{{ __('Administrative Management System') }}</span>
                        <span class="block text-upta-300">{{ __('for Socio-Technological Projects') }}</span>
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-lg text-upta-100">
                        {{ __('A centralized digital platform to manage protocols, registrations, tutors, and evaluations for the PNFI program at UPTA Maracay.') }}
                    </p>
                    <div class="mt-10 flex justify-center gap-4">
                        <a href="{{ route('login') }}" class="bg-white text-upta-900 px-8 py-3 rounded-md font-bold text-lg hover:bg-gray-100 transition shadow-lg">
                            {{ __('Access System') }}
                        </a>
                        <a href="{{ route('register') }}" class="bg-transparent border border-white text-white px-8 py-3 rounded-md font-bold text-lg hover:bg-white hover:text-upta-900 transition">
                            {{ __('Student Registration') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="py-16 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900">{{ __('Everything you need in one place') }}</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-upta-100 text-upta-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Protocol Management') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Submit, track, and receive feedback on your project protocols digitally and securely.') }}</p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-upta-100 text-upta-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Tutor Assignment') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Find and connect with the right academic tutor to guide your socio-technological project.') }}</p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-upta-100 text-upta-600 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Logbooks & Evaluations') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Keep track of tutoring sessions, upload minutes, and receive evaluations from expert jurors.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center md:flex md:justify-between md:text-left">
                <p>&copy; {{ date('Y') }} UPTA "Federico Brito Figueroa" - Extensión Maracay.</p>
                <p class="mt-2 md:mt-0">{{ __('Developed for the PNFI Coordination') }}</p>
            </div>
        </footer>
    </body>
</html>