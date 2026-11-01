<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Help Center - UPTA PNFI') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 flex flex-col min-h-screen">
        
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <x-application-logo class="block h-8 w-auto fill-current text-upta-700" />
                            <span class="ml-3 font-bold text-xl text-upta-900 tracking-tight">{{ __('UPTA PNFI') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">{{ __('Go to Dashboard') }}</a>
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

        <!-- Hero / Header -->
        <header class="bg-upta-900 py-16 text-center">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-extrabold text-white sm:text-4xl">
                    {{ __('Help Center') }}
                </h1>
                <p class="mt-4 text-lg text-upta-100">
                    {{ __('Find guides, workflows, and answers to frequently asked questions about the Socio-Technological Project Management System.') }}
                </p>
            </div>
        </header>

        <!-- Guías por Rol -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">{{ __('Workflows by Role') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Guía del Estudiante -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('For Students') }}</h3>
                        </div>
                        <ol class="space-y-4 text-sm text-gray-600 list-decimal list-inside">
                            <li><strong class="text-gray-900">{{ __('Create your account') }}:</strong> {{ __('Register with your credentials and select your section.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Form your team') }}:</strong> {{ __('Create a project and invite your classmates.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Submit Protocol') }}:</strong> {{ __('Upload your PDF protocol for evaluation.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Choose a Tutor') }}:</strong> {{ __('Once approved, select an academic tutor.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Log your meetings') }}:</strong> {{ __('Upload minutes and files to your tutoring logbook.') }}</li>
                        </ol>
                    </div>

                    <!-- Guía del Docente -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('For Teachers') }}</h3>
                        </div>
                        <ol class="space-y-4 text-sm text-gray-600 list-decimal list-inside">
                            <li><strong class="text-gray-900">{{ __('Evaluate Protocols') }}:</strong> {{ __('Review assigned protocols and approve or reject them with observations.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Manage Tutoring') }}:</strong> {{ __('Accept or reject tutoring requests from student teams.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Review Logbooks') }}:</strong> {{ __('Verify and approve the minutes uploaded by your tutorados.') }}</li>
                        </ol>
                    </div>

                    <!-- Guía del Coordinador -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-upta-100 text-upta-600 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('For Coordinators') }}</h3>
                        </div>
                        <ol class="space-y-4 text-sm text-gray-600 list-decimal list-inside">
                            <li><strong class="text-gray-900">{{ __('Setup Sections') }}:</strong> {{ __('Create academic periods and close them at the end of the year.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Assign Evaluators') }}:</strong> {{ __('Assign teachers to review student protocols.') }}</li>
                            <li><strong class="text-gray-900">{{ __('Publish Events') }}:</strong> {{ __('Create events like defenses or deadlines visible on the calendar.') }}</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Preguntas Frecuentes (FAQ) -->
            <div class="max-w-3xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2">{{ __('Frequently Asked Questions') }}</h2>
                
                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <details class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group">
                        <summary class="px-6 py-4 font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center hover:bg-gray-50 transition">
                            {{ __('What happens if my protocol is rejected?') }}
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-600 bg-gray-50">
                            {{ __('You will receive a notification and a red alert on your project page with the observations made by the evaluators. You can correct the document and upload a new version (Submission 2) to be evaluated again by the same teachers.') }}
                        </div>
                    </details>

                    <!-- FAQ 2 -->
                    <details class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group">
                        <summary class="px-6 py-4 font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center hover:bg-gray-50 transition">
                            {{ __('Can I choose any teacher as my tutor?') }}
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-600 bg-gray-50">
                            {{ __('You can choose any active teacher from the system, with one exception: the teacher who is the current advisor of your section (the one who teaches the subject) cannot be your project tutor.') }}
                        </div>
                    </details>

                    <!-- FAQ 3 -->
                    <details class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group">
                        <summary class="px-6 py-4 font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center hover:bg-gray-50 transition">
                            {{ __('Why can I not invite more members to my team?') }}
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-600 bg-gray-50">
                            {{ __('Each section has a maximum team size set by the coordinator. Also, if you already sent invitations that are pending, they count towards the limit to prevent overbooking the team.') }}
                        </div>
                    </details>

                    <!-- FAQ 4 -->
                    <details class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group">
                        <summary class="px-6 py-4 font-semibold text-gray-900 cursor-pointer list-none flex justify-between items-center hover:bg-gray-50 transition">
                            {{ __('What happens when the academic period closes?') }}
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-600 bg-gray-50">
                            {{ __('Once the coordinator closes the period, students can no longer create new projects, send invitations, or request tutors. You can still consult your project details and download documents.') }}
                        </div>
                    </details>
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