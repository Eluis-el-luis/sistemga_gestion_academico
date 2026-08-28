<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver a la Ficha">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Editar Expediente: ') }} <span class="text-[#e6ac27]">{{ $alumno->nombre_completo }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('academico.alumnos.update', $alumno) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Invocamos el mismo formulario -->
                @include('academico.alumnos.partials._form', ['btnText' => 'Actualizar Expediente'])
                
            </form>
        </div>
    </div>
</x-app-layout>