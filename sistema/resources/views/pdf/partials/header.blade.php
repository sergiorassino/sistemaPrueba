@php
    /** @var array|null $header */
    $h = is_array($header ?? null) ? $header : [];

    $logoFile = isset($h['logo_file']) && is_string($h['logo_file']) ? trim($h['logo_file']) : '';
    $insti = isset($h['insti']) && is_string($h['insti']) ? trim($h['insti']) : '';
    $direccion = isset($h['direccion']) && is_string($h['direccion']) ? trim($h['direccion']) : '';
    $localidad = isset($h['localidad']) && is_string($h['localidad']) ? trim($h['localidad']) : '';
    $cue = isset($h['cue']) && is_string($h['cue']) ? trim($h['cue']) : '';
    $ee = isset($h['ee']) && is_string($h['ee']) ? trim($h['ee']) : '';

    $lineaDir = trim($direccion.($direccion !== '' && $localidad !== '' ? ' — ' : '').$localidad);
    $lineaIds = trim(($cue !== '' ? "CUE: {$cue}" : '').(($cue !== '' && $ee !== '') ? '   ' : '').($ee !== '' ? "EE: {$ee}" : ''));
@endphp

<style>
    /* Header sobrio, compatible con impresión monocromo */
    .pdf-header { width: 100%; border-bottom: 1px solid #111; padding-bottom: 6px; margin-bottom: 10px; }
    .pdf-header table { width: 100%; border-collapse: collapse; }
    .pdf-header td { vertical-align: middle; padding: 0; }
    .pdf-header .logo-cell { width: 64px; }
    .pdf-header .spacer-cell { width: 64px; }
    .pdf-header .text-cell { text-align: center; }
    .pdf-header .logo-img { max-width: 64px; max-height: 64px; width: auto; height: auto; }
    .pdf-header .insti { font-weight: 700; font-size: 12pt; line-height: 1.1; margin: 0; color: #111; }
    .pdf-header .line { font-size: 9pt; line-height: 1.15; margin: 2px 0 0 0; color: #111; }
    .pdf-header .mono { font-family: DejaVu Sans, sans-serif; letter-spacing: 0.02em; }
    .pdf-header .ids { font-size: 8pt; }
</style>

<div class="pdf-header">
    <table cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell">
                @if ($logoFile !== '')
                    <img class="logo-img" src="{{ $logoFile }}" alt="Logo">
                @endif
            </td>
            <td class="text-cell">
                <p class="insti">{{ $insti !== '' ? $insti : 'Institución' }}</p>
                @if ($lineaDir !== '')
                    <p class="line">{{ $lineaDir }}</p>
                @endif
                @if ($lineaIds !== '')
                    <p class="line mono ids">{{ $lineaIds }}</p>
                @endif
            </td>
            <td class="spacer-cell"></td>
        </tr>
    </table>
</div>

