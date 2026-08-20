<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800 border-b-2 border-amber-400 pb-1 inline-block">
            Visión General Institucional
        </h3>
        <span class="text-sm text-gray-500 font-medium">{{ now()->translatedFormat('d \\d\\e F, Y') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-300">
            <div class="p-4 bg-amber-100 text-amber-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Matrícula Activa</p>
                <h4 class="text-3xl font-extrabold text-gray-900">{{ \App\Models\Matricula::where('estado', 'activo')->count() }}</h4>
                <p class="text-xs text-green-600 font-semibold mt-1">↑ Alumnos registrados</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-300">
            <div class="p-4 bg-blue-100 text-blue-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Aulas en Curso</p>
                <h4 class="text-3xl font-extrabold text-gray-900">{{ \App\Models\Aula::count() }}</h4>
                <p class="text-xs text-gray-500 mt-1">Ciclo Escolar Actual</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-300">
            <div class="p-4 bg-red-100 text-red-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Alumnos Retirados</p>
                <h4 class="text-3xl font-extrabold text-red-600">{{ \App\Models\Matricula::where('estado', 'retirado')->count() }}</h4>
                <p class="text-xs text-red-500 mt-1">Requiere atención</p>
            </div>
        </div>

    </div>

    <div class="mt-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Panel de Administración</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <a href="#" class="group flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-xl hover:bg-amber-50 hover:border-amber-300 transition-colors">
                <div class="p-3 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-amber-200 group-hover:text-amber-800 mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-sm font-bold text-gray-700 group-hover:text-amber-900">Reportes Ejecutivos</span>
            </a>

            <a href="#" class="group flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-xl hover:bg-amber-50 hover:border-amber-300 transition-colors">
                <div class="p-3 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-amber-200 group-hover:text-amber-800 mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="text-sm font-bold text-gray-700 group-hover:text-amber-900">Directorio Alumnos</span>
            </a>

            <a href="#" class="group flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-xl hover:bg-amber-50 hover:border-amber-300 transition-colors">
                <div class="p-3 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-amber-200 group-hover:text-amber-800 mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-sm font-bold text-gray-700 group-hover:text-amber-900">Gestión Docente</span>
            </a>

            <a href="#" class="group flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-xl hover:bg-amber-50 hover:border-amber-300 transition-colors">
                <div class="p-3 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-amber-200 group-hover:text-amber-800 mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="text-sm font-bold text-gray-700 group-hover:text-amber-900">Malla Curricular</span>
            </a>

        </div>
    </div>
</div>