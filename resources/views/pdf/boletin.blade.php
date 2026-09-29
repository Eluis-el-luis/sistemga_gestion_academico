<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín de Calificaciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.2; color: #000; }
        @page { size: letter portrait; margin: 5mm; }
        .boletin-container { width: 100%; max-width: 8.5in; margin: 0 auto; padding: 10px; }
        
        .header-section { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .logo { width: 60px; height: 60px; flex-shrink: 0; }
        .logo img { width: 100%; height: 100%; object-fit: contain; }
        .title { flex-1; text-align: center; font-size: 14px; font-weight: 900; text-transform: uppercase; text-decoration: underline; letter-spacing: 1px; }
        
        .student-declaration { font-size: 9px; font-weight: 700; margin-bottom: 6px; text-align: justify; line-height: 1.3; }
        
        .student-info { display: grid; grid-template-columns: 150px 1fr; gap: 2px 0; font-size: 10px; font-weight: 900; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .student-info .label { text-align: right; padding-right: 6px; }
        .student-info .value { }
        
        table { width: 100%; border-collapse: collapse; font-size: 8px; font-weight: 700; }
        th, td { border: 1px solid #000; padding: 2px 1px; text-align: center; vertical-align: middle; }
        th { background: #fff; }
        .area-header td { background: #e2e8f0; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding-left: 4px; }
        .subject-name { text-align: left; padding-left: 4px; }
        .nota-final-header { background: #e2e8f0; }
        .promedio-row { font-weight: 900; }
        .otros-header { background: #e2e8f0; font-size: 7px; }
        .footer-signature { margin-top: 20px; display: flex; justify-content: center; }
        .signature-block { text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 120px; margin: 0 auto; padding-top: 2px; font-weight: 900; font-size: 9px; text-transform: uppercase; }
        
        /* Print optimizations */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="boletin-container">
        <!-- HEADER: Logo + Title -->
        <div class="header-section">
            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="Logo Colegio" onerror="this.style.display='none'">
            </div>
            <h1 class="title">CERTIFICADO DE CALIFICACIONES</h1>
        </div>

        <!-- DECLARATION TEXT -->
        <p class="student-declaration">
            La secretaria del Colegio Cristiano en Nicaragua, hace constar que las presentes calificaciones del {{ $corteActual->numero ?? 1 }}° Corte del Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '2026' }}, fueron obtenidas por el/la alumno(a):
        </p>

        <!-- STUDENT INFO GRID -->
        <div class="student-info">
            <div class="label">CÓDIGO DE PERSONA:</div>
            <div class="value">{{ $matricula->alumno->codigo_unico_persona ?? 'N/A' }}</div>
            
            <div class="label">NOMBRE Y APELLIDOS:</div>
            <div class="value">{{ $matricula->alumno->nombre_completo }}</div>
            
            <div class="label">AÑO:</div>
            <div class="value">{{ $matricula->aula->grado->nombre ?? '' }} - SECCIÓN "{{ $matricula->aula->nombre ?? '' }}"</div>
        </div>

        <!-- GRADES TABLE -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 18%;">MATERIAS</th>
                    <th colspan="2">I CORTE</th>
                    <th colspan="2">II CORTE</th>
                    <th colspan="2">III CORTE</th>
                    <th colspan="2">IV CORTE</th>
                    <th colspan="2" class="nota-final-header">NOTA FINAL</th>
                </tr>
                <tr style="font-size: 7px;">
                    <th>CUA.</th><th>CUAN</th>
                    <th>CUA.</th><th>CUAN</th>
                    <th>CUA.</th><th>CUAN</th>
                    <th>CUA.</th><th>CUAN</th>
                    <th>CUA.</th><th>CUANT</th>
                </tr>
            </thead>
            <tbody style="text-transform: uppercase;">
                @forelse ($areas ?? [] as $areaNombre => $asignaturas)
                    <tr class="area-header">
                        <td colspan="11">{{ $areaNombre }}</td>
                    </tr>
                    @foreach ($asignaturas as $asig)
                        <tr>
                            <td class="subject-name">{{ $asig['nombre'] }}</td>
                            
                            <td>{{ $asig['cortes'][1]['cua'] ?? '' }}</td>
                            <td>{{ $asig['cortes'][1]['cuan'] ?? '' }}</td>
                            
                            <td>{{ $asig['cortes'][2]['cua'] ?? '' }}</td>
                            <td>{{ $asig['cortes'][2]['cuan'] ?? '' }}</td>
                            
                            <td>{{ $asig['cortes'][3]['cua'] ?? '' }}</td>
                            <td>{{ $asig['cortes'][3]['cuan'] ?? '' }}</td>
                            
                            <td>{{ $asig['cortes'][4]['cua'] ?? '' }}</td>
                            <td>{{ $asig['cortes'][4]['cuan'] ?? '' }}</td>
                            
                            <td class="nota-final-header">{{ ($numeroCorteActual ?? 4) == 4 ? ($asig['final']['cua'] ?? '') : '' }}</td>
                            <td class="nota-final-header">{{ ($numeroCorteActual ?? 4) == 4 ? ($asig['final']['cuan'] ?? '') : '' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="11" style="text-align: center; padding: 8px;">No hay asignaturas.</td></tr>
                @endforelse

                <!-- PROMEDIO GENERAL -->
                <tr class="promedio-row">
                    <td style="text-align: center;">PROMEDIO</td>
                    <td>{{ $promedios[1]['cua'] ?? '' }}</td><td>{{ $promedios[1]['cuan'] ?? '' }}</td>
                    <td>{{ $promedios[2]['cua'] ?? '' }}</td><td>{{ $promedios[2]['cuan'] ?? '' }}</td>
                    <td>{{ $promedios[3]['cua'] ?? '' }}</td><td>{{ $promedios[3]['cuan'] ?? '' }}</td>
                    <td>{{ $promedios[4]['cua'] ?? '' }}</td><td>{{ $promedios[4]['cuan'] ?? '' }}</td>
                    <td class="nota-final-header"></td><td class="nota-final-header"></td>
                </tr>

                <!-- OTROS SECTION (kept for compatibility) -->
                <tr class="otros-header" style="text-align: center;">
                    <td>OTROS</td>
                    <td colspan="2">I P</td><td colspan="2">II P</td>
                    <td colspan="2">III P</td><td colspan="2">IV P</td>
                    <td colspan="2" class="nota-final-header">NF</td>
                </tr>
                <tr>
                    <td>AUSENCIAS INJUSTIFICADAS</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 1 ? ($asistencia[1]['injustificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 2 ? ($asistencia[2]['injustificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 3 ? ($asistencia[3]['injustificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 4 ? ($asistencia[4]['injustificadas'] ?? '0') : '' }}</td>
                    <td colspan="2" class="nota-final-header"></td>
                </tr>
                <tr>
                    <td>AUSENCIAS JUSTIFICADAS</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 1 ? ($asistencia[1]['justificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 2 ? ($asistencia[2]['justificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 3 ? ($asistencia[3]['justificadas'] ?? '0') : '' }}</td>
                    <td colspan="2">{{ ($numeroCorteActual ?? 1) >= 4 ? ($asistencia[4]['justificadas'] ?? '0') : '' }}</td>
                    <td colspan="2" class="nota-final-header"></td>
                </tr>
                <tr>
                    <td>COMPROMISO DE PADRES DE FAMILIA</td>
                    <td colspan="2">{{ $compromiso[1] ?? '' }}</td>
                    <td colspan="2">{{ $compromiso[2] ?? '' }}</td>
                    <td colspan="2">{{ $compromiso[3] ?? '' }}</td>
                    <td colspan="2">{{ $compromiso[4] ?? '' }}</td>
                    <td colspan="2" class="nota-final-header"></td>
                </tr>

                <!-- PROFESOR GUÍA -->
                <tr>
                    <td colspan="11" style="text-align: center; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; padding: 6px 2px;">
                        PROFESOR GUÍA: {{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SIGNATURE -->
        <div class="footer-signature">
            <div class="signature-block">
                <div class="signature-line">DIRECTORA</div>
            </div>
        </div>
    </div>
</body>
</html>