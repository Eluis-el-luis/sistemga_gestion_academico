<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Notas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.3; color: #000; }
        @page { size: letter portrait; margin: 15mm; }
        .page { width: 100%; max-width: 7.2in; margin: 0 auto; }

        .header { display: flex; align-items: center; gap: 10px; border-bottom: 3px solid #1a3c5e; padding-bottom: 12px; margin-bottom: 16px; }
        .header .logo { width: 55px; height: 55px; flex-shrink: 0; }
        .header .logo img { width: 100%; height: 100%; object-fit: contain; }
        .header .titles { flex: 1; text-align: center; }
        .header h1 { font-size: 16px; color: #1a3c5e; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 12px; color: #2c5f8a; font-weight: bold; margin-top: 2px; }
        .header .subtitle { font-size: 9px; color: #555; margin-top: 2px; }

        .certificado-title { text-align: center; font-size: 14px; font-weight: bold; color: #1a3c5e; margin: 14px 0; text-decoration: underline; text-transform: uppercase; }

        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 16px; font-size: 9px; }
        .info-item { background: #f8f9fa; padding: 6px 8px; border: 1px solid #e0e0e0; }
        .info-label { font-weight: bold; color: #1a3c5e; font-size: 7px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 10px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9px; }
        th, td { border: 1px solid #333; padding: 4px 3px; text-align: center; }
        .thead-main th { background: #1a3c5e; color: #ffffff; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .thead-sub th { background: #2c5f8a; color: #ffffff; font-weight: bold; font-size: 7px; text-transform: uppercase; }
        .subject-name { text-align: left; padding-left: 6px; font-weight: bold; text-transform: uppercase; }
        .promedio-row { background: #eef2f7; font-weight: bold; }
        .sin-matricula td { color: #777; font-size: 8px; }

        .legend { font-size: 7px; color: #444; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; }

        .official-seal { text-align: center; margin: 16px 0; padding: 12px; border: 2px solid #1a3c5e; background: #fafafa; }
        .official-seal .seal-text { font-size: 11px; font-weight: bold; color: #1a3c5e; }
        .official-seal .seal-sub { font-size: 8px; color: #666; margin-top: 4px; }

        .footer { display: flex; justify-content: space-between; gap: 12px; font-size: 9px; margin-top: 10px; }
        .signature-block { text-align: center; flex: 1; }
        .signature-line { border-top: 1px solid #333; margin-top: 35px; padding-top: 4px; font-weight: bold; font-size: 8px; text-transform: uppercase; }

        .foot-note { margin-top: 18px; text-align: center; font-size: 7px; color: #666; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="page">
        <!-- DATOS DEL CENTRO -->
        <div class="header">
            @if(!empty($logo))
                <div class="logo"><img src="{{ $logo }}" alt="Logo"></div>
            @endif
            <div class="titles">
                <h1>Ministerio de Educación</h1>
                <h2>Colegio Cristiano en Nicaragua</h2>
                <div class="subtitle">
                    {{ $matricula?->aula?->modalidad?->nombre ?? 'Educación Regular' }}
                    @if($matricula?->anioEscolar) &middot; Ciclo Escolar {{ $matricula->anioEscolar->nombre }}@endif
                </div>
            </div>
        </div>

        <div class="certificado-title">Certificado de Notas</div>

        <!-- DATOS DEL ESTUDIANTE -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre Completo</div>
                <div class="info-value">{{ $alumno->nombre_completo }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Código Único de Persona (CUP)</div>
                <div class="info-value">{{ $alumno->codigo_unico_persona ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de Nacimiento</div>
                <div class="info-value">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Sexo</div>
                <div class="info-value">{{ $alumno->sexo === 'M' ? 'Masculino' : ($alumno->sexo === 'F' ? 'Femenino' : '—') }}</div>
            </div>
        </div>

        <!-- TABLA COMPARATIVA POR ASIGNATURAS -->
        @if(empty($grados))
            <table>
                <tbody>
                    <tr class="sin-matricula">
                        <td style="padding: 14px;">El estudiante no tiene matrícula registrada en el año y grado seleccionados.</td>
                    </tr>
                </tbody>
            </table>
        @else
            <table>
                <thead>
                    <tr class="thead-main">
                        <th rowspan="2" style="width: 34%; text-align: left; padding-left: 6px;">Asignatura</th>
                        @foreach($grados as $g)
                            <th colspan="2">
                                {{ $g['grado']->nombre }}@if($g['anioEscolar'])<br><span style="font-weight: normal; text-transform: none;">Ciclo {{ $g['anioEscolar']->nombre }}&middot; Sec. "{{ $g['matricula']?->aula?->nombre ?? '—' }}"</span>@endif
                            </th>
                        @endforeach
                    </tr>
                    <tr class="thead-sub">
                        @foreach($grados as $g)
                            <th style="width: {{ count($grados) ? 33 / count($grados) : 33 }}%;">Cuantitativa</th>
                            <th style="width: {{ count($grados) ? 33 / count($grados) : 33 }}%;">Cualitativa</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignaturas as $fila)
                        <tr>
                            <td class="subject-name">{{ $fila['nombre'] }}</td>
                            @foreach($grados as $g)
                                @php $n = $g['notas'][$fila['nombre']] ?? null; @endphp
                                <td><strong>{{ $n && $n['cuan'] !== null ? $n['cuan'] : '—' }}</strong></td>
                                <td>{{ $n['cua'] ?? '—' }}{{ $n && $n['cua_nombre'] ? ' — ' . $n['cua_nombre'] : '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr class="sin-matricula">
                            <td colspan="{{ 1 + count($grados) * 2 }}" style="padding: 14px;">No hay asignaturas con notas registradas para los grados del certificado.</td>
                        </tr>
                    @endforelse
                    <tr class="promedio-row">
                        <td class="subject-name">Promedio del grado</td>
                        @foreach($grados as $g)
                            <td>{{ $g['promedio'] !== null ? number_format($g['promedio'], 2) : '—' }}</td>
                            <td>{{ $g['promedio_cua'] ?? '—' }}{{ $g['promedio_cua_nombre'] ? ' — ' . $g['promedio_cua_nombre'] : '' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @endif

        <!-- ESCALA CUALITATIVA MINED -->
        <p class="legend">
            Escala de logro: AA = Aprendizaje Avanzado (90-100) &middot; AS = Aprendizaje Satisfactorio (76-89) &middot; AF = Aprendizaje Fundamental (60-75) &middot; AI = Aprendizaje Inicial (0-59)
        </p>

        <!-- SELLO OFICIAL -->
        <div class="official-seal">
            <div class="seal-text">SELLO Y FIRMA DE LA DIRECCIÓN</div>
            <div class="seal-sub">Válido solo con firma y sello oficial de la institución</div>
        </div>

        <!-- FIRMAS -->
        <div class="footer">
            <div class="signature-block">
                <div class="signature-line">Director(a) del Centro</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">Secretario(a) Académico</div>
            </div>
        </div>

        <div class="foot-note">
            Este certificado es un documento oficial. Su autenticidad puede verificarse en la Dirección del Centro.
            <br>Emitido en León, Nicaragua, el {{ now()->timezone('America/Managua')->format('d/m/Y') }}
        </div>
    </div>
</body>
</html>
