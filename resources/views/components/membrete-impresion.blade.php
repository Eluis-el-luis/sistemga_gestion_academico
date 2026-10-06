@props([
    'titulo' => 'Reporte Oficial del Sistema',
    'subtitulo' => 'Ciclo Escolar ' . date('Y'),
    'badge' => null,
    'detalle' => null,
    'orientacion' => 'landscape' // Puedes pasar 'portrait' (vertical) o 'landscape' (horizontal)
])

<!-- Configuración dinámica de orientación de página al imprimir -->
<style>
    @media print {
        @page {
            size: {{ $orientacion }};
            margin: 14mm 16mm;
        }
    }
</style>

<!-- MEMBRETE OFICIAL A COLOR (Oculto en pantalla, visible solo en Impresión / PDF) -->
<div class="hidden print:flex items-center justify-between bg-[#FFFDF5] border-2 border-[#e6ac27]/40 rounded-2xl px-6 py-4 mb-5">
    <div class="flex items-center gap-4">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
        <div>
            <h1 class="text-xl font-black uppercase tracking-wide text-[#3d2c1d]">Colegio Cristiano En Nicaragua</h1>
            <p class="text-[11px] font-black text-[#e6ac27] uppercase tracking-widest mt-0.5">
                {{ $titulo }} • {{ $subtitulo }}
            </p>
        </div>
    </div>

    @if($badge || $detalle)
        <div class="text-right">
            @if($badge)
                <span class="inline-block px-3 py-1 rounded-lg bg-[#3d2c1d] text-white font-black text-sm">
                    {{ $badge }}
                </span>
            @endif
            @if($detalle)
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-wider mt-1.5">
                    {!! $detalle !!}
                </p>
            @endif
        </div>
    @endif
</div>