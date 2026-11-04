{{-- resources/views/academico/anios-escolares/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle del Año Escolar: ' . $anioEscolar->nombre)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $anioEscolar->nombre }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Período: {{ $anioEscolar->fecha_inicio->format('d/m/Y') }} - {{ $anioEscolar->fecha_fin->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $anioEscolar->color_estado }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $anioEscolar->icono_estado === 'heroicon-o-calendar-days' ? 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' : ($anioEscolar->icono_estado === 'heroicon-o-check-circle' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z') }}" />
                </svg>
                {{ $anioEscolar->etiqueta_estado }}
            </span>

            @can('configuracion.gestionar')
            @if(!$anioEscolar->es_cerrado)
            <div class="flex items-center gap-2">
                {{-- Botón Cambiar Estado --}}
                <button type="button"
                        onclick="abrirModalCambiarEstado()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-6 11V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H10a2 2 0 01-2-2z" />
                    </svg>
                    Cambiar Estado
                </button>

                <a href="{{ route('academico.anios-escolares.edit', $anioEscolar) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>
            </div>
            @endif
            @endcan

            <a href="{{ route('academico.anios-escolares.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Información General --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Tarjeta Información Básica --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Información General</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Período</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->nombre_periodo }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Inicio</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->fecha_inicio->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha Fin</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->fecha_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $anioEscolar->color_estado }}">
                                {{ $anioEscolar->etiqueta_estado }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">¿Vigente?</dt>
                        <dd class="mt-1 text-sm">
                            @if($anioEscolar->esta_vigente)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sí</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No</span>
                            @endif
                        </dd>
                    </div>
                    @if($anioEscolar->es_cerrado)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Fecha de Cierre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->fecha_cierre?->format('d/m/Y H:i') ?? 'No registrada' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Cerrado por</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->cerradoPor->name ?? 'No registrado' }}</dd>
                    </div>
                    @if($anioEscolar->observaciones_cierre)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Observaciones de Cierre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $anioEscolar->observaciones_cierre }}</dd>
                    </div>
                    @endif
                    @endif
                </dl>
            </div>

            {{-- Tarjeta Validaciones de Cierre --}}
            @if($anioEscolar->es_activo)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Validaciones para Cierre</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $reporteValidaciones['puede_cerrar'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $reporteValidaciones['puede_cerrar'] ? 'Listo para cerrar' : 'Validaciones pendientes' }}
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($reporteValidaciones['validaciones'] as $key => $validacion)
                    <div class="p-4 rounded-lg border {{ $validacion['cumple'] ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 {{ $validacion['cumple'] ? 'text-green-500' : 'text-yellow-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                    @if($validacion['cumple'])
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    @else
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">
                                        {{ $key === 'cortes_cerrados' ? 'Cortes Evaluativos Cerrados' : ($key === 'promedios_generados' ? 'Promedios Finales Generados' : 'Estados Finales de Estudiantes Definidos') }}
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ $validacion['mensaje'] }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $validacion['cumple'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $validacion['cumple'] ? 'Cumple' : 'Pendiente' }}
                            </span>
                        </div>

                        @if(!$validacion['cumple'] && isset($validacion['detalle']) && is_array($validacion['detalle']) && count($validacion['detalle']) > 0)
                        <div class="mt-3 ml-8 border-l-2 border-gray-200 pl-3">
                            <p class="text-xs text-gray-500 mb-1">Detalle:</p>
                            <ul class="text-xs text-gray-600 space-y-1">
                                @foreach($validacion['detalle'] as $item)
                                <li>• {{ $item['asignatura'] ?? '' }} - {{ $item['docente'] ?? '' }} ({{ $item['aula'] ?? '' }})</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tarjeta Estadísticas --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $stats = $reporteValidaciones['resumen_estadistico'] ?? $anioEscolar->getResumenEstadistico();
                    @endphp
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Aulas</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_aulas'] ?? 0 }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Matrículas Activas</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_matriculas_activas'] ?? 0 }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Docentes Asignados</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_docentes_asignados'] ?? 0 }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Cortes Evaluativos</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_cortes'] ?? 0 }}</dd>
                    </div>
                    @if($anioEscolar->es_activo)
                    <div class="md:col-span-2 p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Cortes Cerrados</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['cortes_cerrados'] ?? 0 }} / {{ $stats['total_cortes'] ?? 0 }}</dd>
                    </div>
                    <div class="md:col-span-2 p-4 bg-gray-50 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">Porcentaje de Cierre</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['porcentaje_cortes_cerrados'] ?? 0 }}%</dd>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Acciones Rápidas --}}
        <div class="lg:col-span-1 space-y-4">
            {{-- Acciones de Estado --}}
            @can('configuracion.gestionar')
            @if(!$anioEscolar->es_cerrado)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones de Estado</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Estado actual: <strong>{{ $anioEscolar->etiqueta_estado }}</strong>
                </p>
                
                <div class="space-y-2">
                    @foreach($transiciones as $transicion)
                    @php
                        $labels = [
                            'planificacion' => ['Pasar a Planificación', 'bg-gray-600 hover:bg-gray-700', 'El año vuelve a configuración'],
                            'activo' => ['Activar Año Escolar', 'bg-green-600 hover:bg-green-700', 'Se convierte en el año en operaciones'],
                            'cerrado' => ['Cerrar Año Escolar', 'bg-red-600 hover:bg-red-700', 'Proceso irreversible con validaciones estrictas'],
                        ];
                        [$label, $color, $desc] = $labels[$transicion] ?? ['Cambiar', 'bg-blue-600 hover:bg-blue-700', ''];
                    @endphp
                    <button type="button"
                            onclick="confirmarCambioEstado('{{ $transicion }}')"
                            class="w-full inline-flex items-center justify-between px-4 py-3 {{ $color }} text-white rounded-lg text-sm font-medium transition-colors">
                        <span>{{ $label }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <p class="text-xs text-gray-500 pl-1">{{ $desc }}</p>
                    @endforeach
                </div>
            </div>
            @endif
            @endcan

            {{-- Enlaces Rápidos --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Enlaces Rápidos</h3>
                <nav class="space-y-2">
                    <a href="{{ route('academico.aulas.index', ['anio_escolar' => $anioEscolar->id]) }}"
                       class="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Aulas del Año
                    </a>
                    <a href="{{ route('academico.matriculas.index', ['anio_escolar' => $anioEscolar->id]) }}"
                       class="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Matrículas
                    </a>
                    <a href="{{ route('academico.cortes.index', ['anio_escolar' => $anioEscolar->id]) }}"
                       class="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Cortes Evaluativos
                    </a>
                    <a href="{{ route('academico.asignaciones.index', ['anio_escolar' => $anioEscolar->id]) }}"
                       class="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Asignaciones Docentes
                    </a>
                    @if($anioEscolar->es_cerrado)
                    <a href="{{ route('academico.reportes.historial-estudiante') }}"
                       class="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Certificados / Historial
                    </a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cambiar Estado --}}
<div id="modalCambiarEstado" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('academico.anios-escolares.cambiar-estado', $anioEscolar) }}" id="formCambiarEstado">
                @csrf
                @method('PUT')
                <input type="hidden" name="estado" id="modalEstadoInput">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto sm:mx-0 sm:w-3/4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmar Cambio de Estado
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modalDescripcion">
                                    ¿Estás seguro de cambiar el estado?
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="modalObservacionesContainer" class="mt-4 hidden">
                        <label for="observaciones_cierre" class="block text-sm font-medium text-gray-700 mb-1">
                            Observaciones de Cierre <span class="text-red-500">*</span>
                        </label>
                        <textarea name="observaciones_cierre"
                                  id="observaciones_cierre"
                                  rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="Describe el motivo y confirmaciones del cierre..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">Obligatorio para cerrar el año escolar.</p>
                    </div>

                    @if($errors->any())
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="text-sm text-red-700">
                            @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 sm:ml-3 sm:w-auto sm:px-6">
                        Confirmar
                    </button>
                    <button type="button"
                            onclick="cerrarModalCambiarEstado()"
                            class="mt-3 w-full inline-flex justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 sm:mt-0 sm:w-auto sm:px-6">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalCambiarEstado() {
        document.getElementById('modalCambiarEstado').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalCambiarEstado() {
        document.getElementById('modalCambiarEstado').classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('formCambiarEstado').reset();
        document.getElementById('modalObservacionesContainer').classList.add('hidden');
    }

    function confirmarCambioEstado(estado) {
        const labels = {
            'planificacion': 'Pasar a Planificación',
            'activo': 'Activar Año Escolar',
            'cerrado': 'Cerrar Año Escolar (IRREVERSIBLE)'
        };
        
        const descriptions = {
            'planificacion': 'El año escolar volverá a estado Planificación. Solo es posible si actualmente está Activo y no se requiere como año en operaciones.',
            'activo': 'El año escolar se convertirá en el año Activo. Si hay otro año activo, este pasará a Planificación automáticamente.',
            'cerrado': 'ADVERTENCIA: El cierre es IRREVERSIBLE. El año pasará a Solo Lectura. Se validará que todos los cortes estén cerrados, promedios generados y estados de estudiantes definidos.'
        };

        document.getElementById('modalEstadoInput').value = estado;
        document.getElementById('modal-title').textContent = labels[estado] || 'Confirmar Cambio';
        document.getElementById('modalDescripcion').textContent = descriptions[estado] || '¿Confirmar cambio de estado?';
        
        // Mostrar/ocultar observaciones según sea cierre
        const obsContainer = document.getElementById('modalObservacionesContainer');
        if (estado === 'cerrado') {
            obsContainer.classList.remove('hidden');
            document.getElementById('observaciones_cierre').required = true;
        } else {
            obsContainer.classList.add('hidden');
            document.getElementById('observaciones_cierre').required = false;
        }

        abrirModalCambiarEstado();
    }

    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalCambiarEstado();
        }
    });
</script>
@endsection