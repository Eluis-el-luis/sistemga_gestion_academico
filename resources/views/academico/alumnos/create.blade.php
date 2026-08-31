<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver atrás">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Registrar Nuevo Alumno') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('academico.alumnos.store') }}" method="POST">
                @csrf
                
                <!-- Invocamos la magia del formulario -->
                @include('academico.alumnos.partials._form', ['btnText' => 'Guardar Registro'])
                
            </form>
        </div>
    </div>
</x-app-layout>