@props([
    'titulo' => 'Reporte Oficial del Sistema',
    'subtitulo' => 'Ciclo Escolar ' . date('Y'),
    'badge' => null,
    'detalle' => null,
    'orientacion' => 'landscape',
])

<style>
    @media print {
        @page {
            size: {{ $orientacion }};
            margin: 14mm 16mm;
        }
    }
</style>

<div class="hidden print:flex items-center justify-between bg-[#FFFDF8] border-2 border-[#e6ac27]/40 rounded-2xl px-6 py-4 mb-5">
    <div class="flex items-center gap-4">
        <img src="{{ asset('img/Logo.png') }}" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
        <div>
            <h1 class="text-lg font-bold uppercase tracking-wide text-neutral-900">Colegio Cristiano En Nicaragua</h1>
            <p class="text-[11px] font-semibold text-[#b88212] uppercase tracking-wider mt-0.5">
                {{ $titulo }} &bull; {{ $subtitulo }}
            </p>
        </div>
    </div>

    @if ($badge || $detalle)
        <div class="text-right">
            @if ($badge)
                <span class="inline-block px-3 py-1 rounded-lg bg-neutral-900 text-white font-semibold text-xs">
                    {{ $badge }}
                </span>
            @endif
            @if ($detalle)
                <p class="text-[10px] font-semibold text-neutral-600 uppercase tracking-wider mt-1.5">
                    {!! $detalle !!}
                </p>
            @endif
        </div>
    @endif
</div>