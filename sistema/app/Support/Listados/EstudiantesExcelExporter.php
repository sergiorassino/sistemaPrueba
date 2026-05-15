<?php

namespace App\Support\Listados;

use App\Models\CampoLegajo;
use App\Models\Curso;
use App\Models\Sexo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exporta matriculados del ciclo activo a Excel: una hoja por curso/sección,
 * columnas según solapas y campos del legajo parametrizados.
 */
final class EstudiantesExcelExporter
{
    /** @var array<string, true> */
    private array $nombresHojaUsados = [];

    /**
     * @return array{spreadsheet: Spreadsheet, filename: string}
     */
    public function build(int $idNivel, int $idTerlec, ?int $anoLectivo, EstudiantesExcelExportSpec $spec): array
    {
        $this->nombresHojaUsados = [];

        if ($spec->campoKeys === null) {
            $campos = CampoLegajo::aplicarVisibilidadListadoPdf(
                ListadoCursoPdfFieldCatalog::keysOrdenadosExportLegajoPorSolapas()
            );
        } else {
            $campos = $spec->campoKeys;
        }

        $columnasMeta = ListadoCursoPdfFieldCatalog::columnsForPdf($campos);

        $todosLosCursos = Curso::query()
            ->with('curplan')
            ->where('idNivel', $idNivel)
            ->where('idTerlec', $idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec')
            ->get();

        if ($spec->cursoIds !== null) {
            $idsPermitidos = array_flip($spec->cursoIds);
            $cursos = $todosLosCursos
                ->filter(fn (Curso $c) => isset($idsPermitidos[(int) $c->Id]))
                ->values();
        } else {
            $cursos = $todosLosCursos;
        }

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        if ($cursos->isEmpty() || $columnasMeta === []) {
            $hoja = $spreadsheet->createSheet();
            $hoja->setTitle($this->nombreHojaUnico('Sin datos'));
            $hoja->setCellValue('A1', 'No hay cursos o columnas configuradas para exportar.');

            return [
                'spreadsheet' => $spreadsheet,
                'filename' => $this->nombreArchivo($anoLectivo),
            ];
        }

        $cursoIds = $cursos->pluck('Id')->map(fn ($id) => (int) $id)->all();
        $select = array_merge(
            ['matricula.idCursos as __id_curso'],
            ListadoCursoPdfFieldCatalog::selectExpressions($campos)
        );

        $idsCondiciones = ListadoCursoCondicionFiltro::idCondicionesParaQuery($spec->filtroCondicion);

        $query = DB::table('matricula')
            ->join('legajos', 'legajos.id', '=', 'matricula.idLegajos')
            ->whereIn('matricula.idCursos', $cursoIds)
            ->whereIn('matricula.idCondiciones', $idsCondiciones)
            ->where('matricula.idTerlec', $idTerlec)
            ->where('matricula.idNivel', $idNivel)
            ->whereNull('matricula.fechaBaja')
            ->orderBy('matricula.idCursos')
            ->orderBy('legajos.apellido')
            ->orderBy('legajos.nombre');

        if (ListadoCursoPdfFieldCatalog::needsCondicionesJoin($campos)) {
            $query->leftJoin('condiciones', 'condiciones.id', '=', 'matricula.idCondiciones');
        }

        $filas = $query->select($select)->get();

        $porCurso = $filas->groupBy(fn ($r) => (int) $r->__id_curso);

        foreach ($cursos as $curso) {
            $alumnos = $porCurso->get((int) $curso->Id, collect());
            $this->poblarHojaCurso($spreadsheet, $curso, $alumnos, $columnasMeta);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return [
            'spreadsheet' => $spreadsheet,
            'filename' => $this->nombreArchivo($anoLectivo),
        ];
    }

    public function nombreArchivo(?int $anoLectivo): string
    {
        $ano = $anoLectivo ?? (int) date('Y');

        return 'Estudiantes'.$ano.'.xlsx';
    }

    /**
     * @param  Collection<int, object>  $alumnos
     * @param  list<array{key: string, label: string, alias: string}>  $columnasMeta
     */
    private function poblarHojaCurso(
        Spreadsheet $spreadsheet,
        Curso $curso,
        Collection $alumnos,
        array $columnasMeta
    ): void {
        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle($this->nombreHojaUnico($curso->nombreParaListado()));

        $col = 1;
        $hoja->setCellValue([$col, 1], 'Nº');
        $col++;
        foreach ($columnasMeta as $meta) {
            $hoja->setCellValue([$col, 1], $meta['label']);
            $col++;
        }

        $fila = 2;
        $num = 1;
        foreach ($alumnos as $alumno) {
            $col = 1;
            $hoja->setCellValue([$col, $fila], $num);
            $col++;
            foreach ($columnasMeta as $meta) {
                $valor = $this->formatearValorCelda($alumno->{$meta['alias']} ?? null, $meta['key']);
                $hoja->setCellValue([$col, $fila], $valor);
                $col++;
            }
            $fila++;
            $num++;
        }

        $this->estilizarEncabezado($hoja, count($columnasMeta) + 1);
    }

    private function estilizarEncabezado(Worksheet $hoja, int $totalColumnas): void
    {
        $ultimaCol = $this->indiceColumnaExcel($totalColumnas);
        $hoja->getStyle('A1:'.$ultimaCol.'1')->getFont()->setBold(true);
        for ($c = 1; $c <= $totalColumnas; $c++) {
            $hoja->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $hoja->freezePane('A2');
    }

    private function indiceColumnaExcel(int $numeroColumna): string
    {
        $letras = '';
        $n = $numeroColumna;
        while ($n > 0) {
            $n--;
            $letras = chr(65 + ($n % 26)).$letras;
            $n = intdiv($n, 26);
        }

        return $letras;
    }

    private function formatearValorCelda(mixed $valor, string $catalogKey): string|int|float
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        if (is_numeric($valor) && (
            str_contains($catalogKey, 'bloq')
            || str_ends_with($catalogKey, 'inscripto')
            || str_contains($catalogKey, 'acept')
        )) {
            return ((int) $valor) !== 0 ? 'Sí' : 'No';
        }

        if ($catalogKey === 'legajos.sexo') {
            return Sexo::etiquetaParaValorAlmacenado($valor);
        }

        if ($this->esCampoFecha($catalogKey) && $this->pareceFecha($valor)) {
            try {
                return Carbon::parse((string) $valor)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $valor;
            }
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        return is_scalar($valor) ? $valor : (string) $valor;
    }

    private function esCampoFecha(string $catalogKey): bool
    {
        $needle = strtolower($catalogKey);

        return str_contains($needle, 'fech')
            || str_contains($needle, 'fechnac')
            || str_contains($needle, 'fechhora')
            || str_contains($needle, 'fechactdatos');
    }

    private function pareceFecha(mixed $valor): bool
    {
        if ($valor instanceof \DateTimeInterface) {
            return true;
        }

        $s = trim((string) $valor);

        return $s !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $s) === 1;
    }

    private function nombreHojaUnico(string $base): string
    {
        $limpio = preg_replace('/[\[\]\:\*\?\/\\\\]/', ' ', $base) ?? 'Curso';
        $limpio = trim(preg_replace('/\s+/', ' ', $limpio) ?? 'Curso');
        if ($limpio === '') {
            $limpio = 'Curso';
        }
        $limpio = Str::limit($limpio, 28, '');

        $nombre = $limpio;
        $sufijo = 2;
        while (isset($this->nombresHojaUsados[$nombre])) {
            $sufijoStr = ' ('.$sufijo.')';
            $nombre = Str::limit($limpio, 31 - strlen($sufijoStr), '').$sufijoStr;
            $sufijo++;
        }
        $this->nombresHojaUsados[$nombre] = true;

        return $nombre;
    }

    public function guardarEnTemporal(Spreadsheet $spreadsheet): string
    {
        $path = tempnam(sys_get_temp_dir(), 'est_xlsx_');
        if ($path === false) {
            throw new \RuntimeException('No se pudo crear archivo temporal para Excel.');
        }
        $destino = $path.'.xlsx';
        if (! @rename($path, $destino)) {
            @unlink($path);
            throw new \RuntimeException('No se pudo preparar archivo temporal para Excel.');
        }

        (new Xlsx($spreadsheet))->save($destino);

        return $destino;
    }
}
