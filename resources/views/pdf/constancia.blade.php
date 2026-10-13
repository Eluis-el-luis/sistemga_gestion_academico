<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Notas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #000; }
        @page { size: letter landscape; margin: 15mm; }
        .container { width: 100%; max-width: 9.8in; margin: 0 auto; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 25px; }
        .header .institution { font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .header .doc-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; }
        .header .doc-subtitle { font-size: 11px; color: #444; }
        
        .student-data { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px 20px; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 12px; }
        .student-data p { margin: 0; }
        .student-data .label { font-weight: 900; color: #444; display: inline-block; margin-right: 6px; }
        .student-data .value { font-weight: 900; color: #1a3c5e; display: inline; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 6px 4px; text-align: center; }
        th { background: #e2e8f0; font-weight: 900; font-size: 10px; text-transform: uppercase; }
        .subject-cell { text-align: left; font-weight: 900; padding-left: 8px; }
        .grade-cell { font-weight: 700; }
        
        .promedio-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .promedio-box { text-align: right; }
        .promedio-label { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #444; margin-bottom: 4px; }
        .promedio-value { font-size: 28px; font-weight: 900; }
        .promedio-value.approved { color: #166534; }
        .promedio-value.failed { color: #991b1b; }
        
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-block { text-align: center; width: 160px; }
        .signature-line { border-top: 2px solid #000; width: 100%; margin-top: 40px; padding-top: 6px; font-weight: 900; font-size: 12px; text-transform: uppercase; }
        
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <p class="institution">Colegio Cristiano en Nicaragua</p>
            <p class="doc-title">Constancia de Calificaciones — Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '—' }}</p>
            <p class="doc-subtitle">Documento para trámites académicos (universidad, traslado, entre otros)</p>
        </div>

        <!-- STUDENT DATA -->
        <div class="student-data">
            <p><span class="label">Estudiante:</span> <span class="value">{{ $matricula->alumno->nombre_completo }}</span></p>
            <p><span class="label">Código de Persona:</span> <span class="value">{{ $matricula->alumno->codigo_unico_persona ?? 'N/A' }}</span></p>
            <p><span class="label">Grado:</span> <span class="value">{{ $matricula->aula->grado->nombre ?? '—' }}</span></p>
            <p><span class="label">Sección:</span> <span class="value">{{ $matricula->aula->nombre ?? '—' }}</span></p>
            <p><span class="label">Modalidad:</span> <span class="value">{{ $matricula->aula->modalidad->nombre ?? '—' }}</span></p>
            <p><span class="label">Docente Guía:</span> <span class="value">{{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? '—' }}</span></p>
        </div>

        <!-- GRADES TABLE -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Asignatura</th>
                    @foreach ($cortes as $corte)
                        <th>{{ $corte->numero }}° Corte</th>
                    @endforeach
                    <th>Nota Final</th>
                    <th>Indicador</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($filas as $fila)
                    <tr>
                        <td class="subject-cell">{{ $fila['asignatura'] }}</td>
                        @foreach ($cortes as $corte)
                            <td class="grade-cell">{{ $fila['cortes'][$corte->numero] ?? '—' }}</td>
                        @endforeach
                        <td class="grade-cell">{{ $fila['nota_final'] ?? '—' }}</td>
                        <td class="grade-cell">{{ $fila['indicador'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($cortes) + 3 }}" style="text-align: center; padding: 20px;">No hay notas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- PROMEDIO GENERAL -->
        <div class="promedio-section">
            <div class="promedio-box">
                <p class="promedio-label">Promedio General</p>
                <p class="promedio-value {{ $promedioGeneral !== null && $promedioGeneral >= 60 ? 'approved' : 'failed' }}">
                    {{ $promedioGeneral !== null ? number_format($promedioGeneral, 2) : '—' }}
                </p>
            </div>
        </div>

        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">Docente Guía</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">Dirección</div>
            </div>
        </div>
    </div>
</body>
</html>