<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-50">
    
    <aside 
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-upta-900 text-white transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:flex md:flex-col shadow-xl"
    >
        <!-- Titulo y logo -->
        <div class="flex items-center justify-between h-16 px-4 bg-upta-900 border-b border-upta-700">
            <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-2">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'block h-8 w-auto fill-current text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block h-8 w-auto fill-current text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                <span class="font-bold text-sm tracking-wider uppercase"><?php echo e(__('UPTA PNFI')); ?></span>
            </a>
            <!-- Boton de cierre (solo en mobile) -->
            <button @click="sidebarOpen = false" class="md:hidden text-white hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            
            <!-- 1. Dashboard -->
            <a href="<?php echo e(route('dashboard')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('dashboard') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <?php echo e(__('Dashboard')); ?>

            </a>

            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'estudiante')): ?>
            <a href="<?php echo e(route('projects.index')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('projects.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <?php echo e(__('My Project')); ?>

            </a>
            <a href="<?php echo e(route('tutors.student.index')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('tutors.student.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <?php echo e(__('My Tutor')); ?>

            </a>
            <?php endif; ?>

            <!-- 2. Menu: Gestión Académica -->
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|coordinador')): ?>
            <div x-data="{ expanded: false }">
                <button @click="expanded = !expanded" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium text-upta-100 rounded-md hover:bg-upta-800 hover:text-white focus:outline-none">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <?php echo e(__('Academic Management')); ?>

                    </div>
                    <!-- Arrow Icon -->
                    <svg :class="{'rotate-180': expanded, 'rotate-0': !expanded}" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <!-- Submenu -->
                <div x-show="expanded" x-transition class="mt-1 space-y-1 pl-10 pr-2">
                    <a href="<?php echo e(route('coordinator.protocols')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('coordinator.protocols') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Protocols')); ?>

                    </a>

                    <a href="<?php echo e(route('calendar.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('calendar.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Academic Calendar')); ?>

                    </a>

                    <a href="<?php echo e(route('late-requests.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('late-requests.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Late Requests')); ?>

                    </a>
                    
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-upta-200 rounded-md hover:bg-upta-800 hover:text-white opacity-50 cursor-not-allowed">
                        <?php echo e(__('Registrations')); ?>

                    </a>
                    <a href="<?php echo e(route('coordinator.tutorships')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('coordinator.tutorships') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Tutorships')); ?>

                    </a>
                    <a href="<?php echo e(route('evaluations.coordinator.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('evaluations.coordinator.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Evaluations')); ?>

                    </a>

                    <a href="<?php echo e(route('events.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('events.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Academic Events')); ?>

                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'docente')): ?>
            <div x-data="{ expanded: <?php echo e(request()->routeIs('evaluations.teacher.*') ? 'true' : 'false'); ?> }">
                <button @click="expanded = !expanded" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('evaluations.teacher.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?> focus:outline-none">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <?php echo e(__('Evaluations')); ?>

                    </div>
                    <svg :class="{'rotate-180': expanded, 'rotate-0': !expanded}" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="expanded" x-transition class="mt-1 space-y-1 pl-10 pr-2">
                    <a href="<?php echo e(route('evaluations.teacher.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('evaluations.teacher.index') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Pending')); ?>

                    </a>
                    <a href="<?php echo e(route('evaluations.teacher.history')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('evaluations.teacher.history*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('History')); ?>

                    </a>
                </div>
            </div>
            <a href="<?php echo e(route('tutors.index')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('tutors.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <?php echo e(__('Tutoring Requests')); ?>

            </a>
            <a href="<?php echo e(route('tutor-logs.teacher')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('tutor-logs.teacher') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <?php echo e(__('Review Logbooks')); ?>

            </a>

            <a href="<?php echo e(route('evaluations.coordinator.index')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('evaluations.coordinator.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <?php echo e(__('Assign Evaluators')); ?>

            </a>
            <?php endif; ?>

            <!-- 3. Menu: Usuarios -->
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|coordinador|docente')): ?>
            <div x-data="{ expanded: <?php echo e(( request()->routeIs('students.*') || request()->routeIs('teachers.*') ) ? 'true' : 'false'); ?> }">
                <button @click="expanded = !expanded" class="w-full group flex items-center justify-between px-3 py-2 text-sm font-medium text-upta-100 rounded-md hover:bg-upta-800 hover:text-white focus:outline-none">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <?php echo e(__('Users')); ?>

                    </div>
                    <svg :class="{'rotate-180': expanded, 'rotate-0': !expanded}" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <!-- Submenú -->
                <div x-show="expanded" x-transition class="mt-1 space-y-1 pl-10 pr-2">
                    <a href="<?php echo e(route('students.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md transition-colors <?php echo e(request()->routeIs('students.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Students')); ?>

                    </a>

                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|coordinador')): ?>
                    <a href="<?php echo e(route('teachers.index')); ?>" class="block px-3 py-2 text-sm font-medium rounded-md transition-colors <?php echo e(request()->routeIs('teachers.*') ? 'bg-upta-600 text-white shadow-sm' : 'text-upta-200 hover:bg-upta-800 hover:text-white'); ?>">
                        <?php echo e(__('Teachers')); ?>

                    </a>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            <!-- 4. Menu: Secciones -->
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|coordinador')): ?>
            <a href="<?php echo e(route('sections.index')); ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('sections.*') ? 'bg-upta-700 text-white' : 'text-upta-100 hover:bg-upta-800 hover:text-white'); ?>">
                
                <svg class="mr-3 h-5 w-5 text-upta-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                
                <?php echo e(__('Sections')); ?>

            </a>
            <?php endif; ?>
        </nav>

        <!-- Campana de Notificaciones -->
        <?php
            $unreadNotifications = auth()->user()->unreadNotifications;
        ?>
        
        <div class="relative" x-data="{ openNotifications: false }">
            <!-- Botón de la campana -->
            <button @click="openNotifications = !openNotifications" class="relative p-2 text-upta-200 hover:text-white focus:outline-none transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>

                <?php if($unreadNotifications->count() > 0): ?>
                    <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-upta-900"></span>
                <?php endif; ?>
            </button>
        
            <!-- Menú Desplegable -->
            <div x-show="openNotifications" @click.away="openNotifications = false" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="absolute left-0 bottom-full mb-2 w-72 bg-white rounded-lg shadow-xl z-50 border border-gray-100 overflow-hidden" 
                 style="display: none;">

                <!-- Cabecera del menú -->
                <div class="p-3 bg-gray-50 border-b flex justify-between items-center">
                    <span class="font-bold text-xs text-gray-600 uppercase tracking-wider"><?php echo e(__('Notifications')); ?></span>

                    <!-- Botón para marcar todas como leídas -->
                    <?php if($unreadNotifications->count() > 0): ?>
                        <form action="<?php echo e(route('notifications.markAllRead')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs text-upta-600 hover:text-upta-800 hover:underline font-medium">
                                <?php echo e(__('Mark all as read')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            
                <!-- Lista de notificaciones -->
                <div class="max-h-64 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $unreadNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('notifications.read', $notification->id)); ?>" class="block p-4 border-b hover:bg-gray-50 transition-colors">
                            <p class="text-sm font-bold text-gray-800"><?php echo e($notification->data['title']); ?></p>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2"><?php echo e($notification->data['message']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="p-4 text-sm text-gray-500 text-center"><?php echo e(__('No new notifications')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Perfil de usuario -->
        <div class="border-t border-upta-700 p-4 relative" x-data="{ profileOpen: false }">
            
            <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="w-full flex items-center text-left text-sm font-medium text-upta-100 hover:text-white focus:outline-none transition ease-in-out duration-150">
                <div class="h-8 w-8 rounded-full bg-upta-600 flex items-center justify-center mr-3 text-white font-bold uppercase">
                    <?php echo e(substr(Auth::user()->name, 0, 1)); ?>

                </div>
                <div class="flex-1 truncate">
                    <div class="font-semibold"><?php echo e(Auth::user()->name); ?></div>
                    <div class="text-xs text-upta-300 capitalize">
                        <?php echo e(__(Auth::user()->getRoleNames()->first() ?? 'User')); ?>

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
                
                <a href="<?php echo e(route('profile.edit')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-upta-50 hover:text-upta-900 transition-colors">
                    <?php echo e(__('My Profile')); ?>

                </a>

                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <a href="<?php echo e(route('logout')); ?>" 
                       onclick="event.preventDefault(); this.closest('form').submit();" 
                       class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <?php echo e(__('Log Out')); ?>

                    </a>
                </form>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="md:hidden flex items-center justify-between px-4 h-16 bg-white border-b border-gray-200">
            <div class="flex items-center">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'block h-8 w-auto fill-current text-upta-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block h-8 w-auto fill-current text-upta-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                <span class="ml-2 font-bold text-upta-900">PNFI</span>
            </div>
            <button @click="sidebarOpen = true" class="text-gray-500 hover:text-upta-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <!-- Contenido de la pagina -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <?php echo e($slot); ?>

        </main>
    </div>

    <!-- Fondo negro de mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 md:hidden"></div>
</div><?php /**PATH C:\Users\Sebas\Documents\University things\Proyecto 2do año\Proyecto_Código\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>