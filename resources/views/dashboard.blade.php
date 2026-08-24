<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-amber-900 leading-tight">
            {{ __('Panel Principal') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-extrabold text-amber-900">
                            ¡Hola, {{ Auth::user()->primer_nombre ?? Auth::user()->name ?? Auth::user()->username ?? 'Usuario' }}!
                        </h3>
                        <p class="text-gray-600 mt-1 font-medium">
                            Bienvenido al Sistema de Gestión Académica — Colegio Cristiano en Nicaragua.
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
                            Año Lectivo 2026
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
                    <div class="p-3.5 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Matrícula Activa</p>
                        <p class="text-2xl font-bold text-gray-800">-- Estudiantes</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
                    <div class="p-3.5 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Asistencia Promedio</p>
                        <p class="text-2xl font-bold text-gray-800">-- %</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center space-x-4">
                    <div class="p-3.5 rounded-xl bg-amber-50 text-amber-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Corte Evaluativo</p>
                        <p class="text-2xl font-bold text-gray-800">1er Parcial</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-amber-900 border-b pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 58a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V58zM11 11a1 1 0 100-2 1 1 0 000 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Avisos e Informes Recientes
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 rounded-lg bg-amber-50/50 border border-amber-100">
                            <span class="text-xs font-bold text-amber-700 uppercase">Aviso de Dirección</span>
                            <p class="text-sm font-semibold text-gray-800 mt-1">Cierre de ingreso de notas del primer parcial</p>
                            <p class="text-xs text-gray-500 mt-1">Recuerden que el sistema congelará las actas de calificaciones al finalizar la fecha límite programada por subdirección.</p>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="text-xs font-bold text-gray-500 uppercase">Recordatorio Técnico</span>
                            <p class="text-sm font-semibold text-gray-800 mt-1">Pase de lista diario desde dispositivos móviles</p>
                            <p class="text-xs text-gray-500 mt-1">Los docentes de asignatura pueden tomar asistencia directamente desde la sección "Asistencia" en sus teléfonos.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-amber-900 border-b pb-3 mb-4">Estado del Sistema</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex justify-between items-center text-gray-600">
                            <span>Base de Datos:</span>
                            <span class="font-bold text-emerald-600">PostgreSQL (Conectado)</span>
                        </li>
                        <li class="flex justify-between items-center text-gray-600">
                            <span>Servidor Local:</span>
                            <span class="font-bold text-emerald-600">Laragon / Activo</span>
                        </li>
                        <li class="flex justify-between items-center text-gray-600">
                            <span>Modalidades:</span>
                            <span class="font-bold text-gray-800">Preescolar, Primaria, Sec.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>

        <button 
            x-show="showTopBtn" 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Volver arriba">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>
    </div>
</x-app-layout>