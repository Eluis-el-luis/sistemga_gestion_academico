<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Notas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.3; color: #333; }
        .page { width: 100%; max-width: 8.5in; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #1a3c5e; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #1a3c5e; font-weight: bold; margin-bottom: 3px; }
        .header h2 { font-size: 14px; color: #2c5f8a; font-weight: normal; margin-bottom: 3px; }
        .header .subtitle { font-size: 11px; color: #555; }
        .certificado-title { text-align: center; font-size: 16px; font-weight: bold; color: #1a3c5e; margin: 20px 0; text-decoration: underline; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; font-size: 10px; }
        .info-item { background: #f8f9fa; padding: 8px; border-radius: 4px; border: 1px solid #e0e0e0; }
        .info-label { font-weight: bold; color: #1a3c5e; font-size: 8px; text-transform: uppercase; }
        .info-value { font-size: 10px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 8px; }
        th, td { border: 1px solid #ddd; padding: 4px 2px; text-align: center; }
        th { background: #1a3c5e; color: white; font-weight: bold; font-size: 7px; }
        .area-header { background: #2c5f8a !important; color: white; font-size: 8px; }
        .area-name { text-align: left; padding-left: 6px; }
        .subject-name { text-align: left; padding-left: 10px; font-size: 7px; }
        .grade-cell { font-weight: bold; font-size: 8px; }
        .promedio-row { background: #fff3e0; font-weight: bold; }
        .promedio-row td { font-size: 9px; }
        .final-row { background: #e8f5e9; font-weight: bold; }
        .final-row td { font-size: 9px; }

        .footer { margin-top: 25px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 9px; }
        .signature-block { text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 35px; padding-top: 4px; font-weight: bold; font-size: 9px; }
        .signature-role { font-size: 8px; color: #666; }
        .promedio-general { text-align: right; font-size: 11px; font-weight: bold; color: #1a3c5e; margin-top: 15px; padding: 8px; background: #f0f4f8; border-radius: 4px; border: 1px solid #1a3c5e; }
        .official-seal { text-align: center; margin: 20px 0; padding: 15px; border: 2px solid #1a3c5e; border-radius: 8px; background: #fafafa; }
        .official-seal .seal-text { font-size: 12px; font-weight: bold; color: #1a3c5e; }
        .official-seal .seal-sub { font-size: 9px; color: #666; margin-top: 5px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(0,0,0,0.03); font-weight: bold; pointer-events: none; z-index: -1; }

        @page { margin: 15mm; }
    </style>
</head>
<body>
    <div class="watermark">CERTIFICADO</div>
    <div class="page">
        <!-- ENCABEZADO OFICIAL -->
        <div class="header">
            <h1>MINISTERIO DE EDUCACIÓN</h1>
            <h2>COLEGIO CRISTIANO NICARAGUENSE</h2>
            <div class="subtitle">Código Centro: {{ $matricula->aula->anioEscolar->nombre }} - Matrícula Oficial</div>
        </div>

        <div class="certificado-title">CERTIFICADO DE NOTAS OFICIAL</div>

        <!-- DATOS DEL ESTUDIANTE -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre Completo</div>
                <div class="info-value">{{ $alumno->nombre_completo }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">CUP (Código Único de Persona)</div>
                <div class="info-value">{{ $alumno->codigo_unico_persona }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de Nacimiento</div>
                <div class="info-value">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Sexo</div>
                <div class="info-value">{{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Grado / Sección</div>
                <div class="info-value">{{ $matricula->aula->grado->nombre }} - {{ $matricula->aula->nombre }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Modalidad</div>
                <div class="info-value">{{ $matricula->aula->modalidad->nombre }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Año Escolar</div>
                <div class="info-value">{{ $matricula->anioEscolar->nombre }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Docente Guía</div>
                <div class="info-value">{{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? 'Por asignar' }}</div>
            </div>
        </div>

        <!-- TABLA DE NOTAS POR ÁREAS -->
        @foreach ($resumenAsignaturas as $item)
            <table>
                <thead>
                    <tr>
                        <th class="area-header area-name" colspan="8">{{ $item['area'] }}</th>
                    </tr>
                    <tr>
                        <th class="area-name" style="width: 25%;">Asignatura</th>
                        @for ($i = 1; $i <= 4; $i++)
                            <th style="width: 8%;">I{{ $i }}</th>
                        @endfor
                        <th style="width: 10%;">Sem. I</th>
                        <th style="width: 10%;">Sem. II</th>
                        <th style="width: 10%;">Final</th>
                        <th style="width: 8%;">Indicador</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="subject-name">{{ $item['asignatura'] }}</td>
                        @for ($i = 1; $i <= 4; $i++)
                            <td class="grade-cell">
                                @if (isset($item['resumen']['cortes'][$i]) && $item['resumen']['cortes'][$i] !== null)
                                    {{ number_format($item['resumen']['cortes'][$i], 0) }}
                                @else
                                    —
                                @endif
                            </td>
                        @endfor
                        <td class="grade-cell">
                            @if ($item['resumen']['semestre1'] !== null)
                                {{ $item['resumen']['semestre1'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="grade-cell">
                            @if ($item['resumen']['semestre2'] !== null)
                                {{ $item['resumen']['semestre2'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="grade-cell">
                            @if ($item['resumen']['nota_final'] !== null)
                                {{ number_format($item['resumen']['nota_final'], 0) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="grade-cell">
                            @if ($item['resumen']['indicador_final'])
                                {{ $item['resumen']['indicador_final'] }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <!-- PROMEDIO GENERAL -->
        <div class="promedio-general">
            PROMEDIO GENERAL ACUMULADO: {{ number_format($promedioGeneral, 2) }} / 100
            @if ($promedioGeneral !== null)
                <br><small>Indicador de Logro: {{ \App\Services\NotaService::calcularIndicadorLogro((int) round($promedioGeneral)) }} 
                ({{ \App\Services\NotaService::estaAprobado((int) round($promedioGeneral)) ? 'APROBADO' : 'REPROBADO' }})</small>
            @endif
        </div>

        <!-- SELLO OFICIAL -->
        <div class="official-seal">
            <div class="seal-text">SELLO Y FIRMA DEL DIRECTOR</div>
            <div class="seal-sub">Válido solo con firma y sello oficial de la institución</div>
        </div>

        <!-- FIRMAS -->
        <div class="footer">
            <div class="signature-block">
                <div class="signature-line">________________________________</div>
                <div class="signature-role">Director(a) del Centro</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">________________________________</div>
                <div class="signature-role">Secretario(a) Académico</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">________________________________</div>
                <div class="signature-role">Ministerio de Educación (Visado)</div>
            </div>
        </div>

        <div style="margin-top: 25px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
            Este certificado es un documento oficial. Su autenticidad puede verificarse en la Dirección Departamental de Educación.
            <br>Emitido en Managua, Nicaragua, el {{ now()->timezone('America/Managua')->format('d \d\e F \d\e Y') }}
        </div>
    </div>
</body>
</html>