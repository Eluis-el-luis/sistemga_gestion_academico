<x-app-layout>
    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-dashboard.banner>
                Visualiza el ranking de tus alumnos, gestiona el pase de lista y administra el apoyo parental.
            </x-dashboard.banner>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-300 transition-all flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div><span class="block font-bold text-slate-800">Alumnos</span><span class="block text-xs font-medium text-slate-500">Ranking y Perfiles</span></div>
                </a>
                
                <a href="{{ route('academico.asistencia.aula.create') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div><span class="block font-bold text-slate-800">Asistencia</span><span class="block text-xs font-medium text-slate-500">Pase diario</span></div>
                </a>

                <a href="#" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 transition-all flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div><span class="block font-bold text-slate-800">Boletines</span><span class="block text-xs font-medium text-slate-500">Generación y envío</span></div>
                </a>

                <a href="#" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-purple-300 transition-all flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div><span class="block font-bold text-slate-800">Pre-matrícula</span><span class="block text-xs font-medium text-slate-500">Migración Anual</span></div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>