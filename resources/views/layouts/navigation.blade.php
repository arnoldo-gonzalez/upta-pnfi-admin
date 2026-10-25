<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-50">
    
    <aside 
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-upta-900 text-white transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:flex md:flex-col shadow-xl"
    >
        <!-- Titulo y logo -->
        <div class="flex items-center justify-between h-16 px-4 bg-upta-900 border-b border-upta-700">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <x-application-logo class="block h-8 w-auto fill-current text-white" />
                <span class="font-bold text-sm tracking-wider uppercase">{{ __('UPTA PNFI') }}</span>
            </a>
            <!-- Boton de cierre (solo en mobile) -->
            <button @click="sidebarOpen = false" class="md:hidden text-white hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                {{ __('Dashboard') }}
            </a>

            <!-- 2. Menu: Gestión Académica -->
            @role('admin|coordinador|docente')
            <div x-data="{ expanded: false }">
                <button @click="expanded = !expanded" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium text-upta-100 rounded-md hover:bg-upta-800 hover:text-white focus:outline-none">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        {{ __('Academic Management') }}
                    </div>
                    <!-- Arrow Icon -->
                    <svg :class="{'rotate-180': expanded, 'rotate-0': !expanded}" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <!-- Submenu -->
                <div x-show="expanded" x-transition class="mt-1 space-y-1 pl-10 pr-2">
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white">
                        {{ __('Protocols') }}
                    </a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white">
                        {{ __('Registrations') }}
                    </a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white">
                        {{ __('Tutorials') }}
                    </a>
                </div>
            </div>
            @endrole

            <!-- 3. Menu: Usuarios -->
            @role('admin|coordinador|docente')
            <div x-data="{ expanded: false }">
                <button @click="expanded = !expanded" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium text-upta-100 rounded-md hover:bg-upta-800 hover:text-white focus:outline-none">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        {{ __('Users') }}
                    </div>
                    <svg :class="{'rotate-180': expanded, 'rotate-0': !expanded}" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="expanded" x-transition class="mt-1 space-y-1 pl-10 pr-2">
                    <a href="{{ route('students.index') }}" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white {{ request()->routeIs('students.*') ? 'bg-upta-800 text-white' : '' }}">
                        {{ __('Students') }}
                    </a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white">
                        {{ __('Teachers') }}
                    </a>
                </div>
            </div>
            @endrole
        </nav>

        <!-- Perfil de usuario -->
        <div class="border-t border-upta-700 p-4 relative" x-data="{ profileOpen: false }">
            
            <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="w-full flex items-center text-left text-sm font-medium text-upta-100 hover:text-white focus:outline-none transition ease-in-out duration-150">
                <div class="h-8 w-8 rounded-full bg-upta-600 flex items-center justify-center mr-3 text-white font-bold uppercase">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="flex-1 truncate">
                    <div class="font-semibold">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-upta-300 capitalize">
                        {{ __(Auth::user()->getRoleNames()->first() ?? 'User') }}
                    </div>
                </div>
                <!-- Arrow Icon -->
                <svg :class="{'rotate-180': profileOpen}" class="fill-current h-4 w-4 text-upta-300 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="profileOpen" 
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute bottom-full left-4 mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden" 
                style="display: none;">
                
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-upta-50 hover:text-upta-900 transition-colors">
                    {{ __('My Profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); this.closest('form').submit();" 
                       class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="md:hidden flex items-center justify-between px-4 h-16 bg-white border-b border-gray-200">
            <div class="flex items-center">
                <x-application-logo class="block h-8 w-auto fill-current text-upta-600" />
                <span class="ml-2 font-bold text-upta-900">PNFI</span>
            </div>
            <button @click="sidebarOpen = true" class="text-gray-500 hover:text-upta-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <!-- Contenido de la pagina -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            {{ $slot }}
        </main>
    </div>

    <!-- Fondo negro de mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 md:hidden"></div>
</div>