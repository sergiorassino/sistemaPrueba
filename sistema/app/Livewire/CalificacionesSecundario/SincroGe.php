<?php

namespace App\Livewire\CalificacionesSecundario;

use App\Services\SincroGe\GeCsvImporter;
use App\Services\SincroGe\GeCsvImportResult;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use RuntimeException;
use Throwable;

/**
 * sincroGe — descarga de calificaciones desde exportación GE/CIDI (CSV).
 */
class SincroGe extends Component
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $archivoCsv = null;

    public ?string $archivoNombre = null;

    public ?int $archivoTamanioKb = null;

    public bool $encabezadoValido = false;

    /** @var array<string, mixed>|null */
    public ?array $ultimoResultado = null;

    /** Ruta temporal en disco local si hubo que copiar el archivo desde Livewire. */
    private ?string $storedCsvRelativePath = null;

    public function mount(): void
    {
        abort_unless(tienePermiso(2), 403, 'Sin permiso para importar calificaciones.');
    }

    protected function rules(): array
    {
        return [
            'archivoCsv' => [
                'required',
                File::default()
                    ->extensions(['csv', 'txt'])
                    ->max(15 * 1024),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'archivoCsv.required' => 'Seleccione el archivo CSV exportado desde CIDI/GE.',
            'archivoCsv' => 'El archivo debe ser .csv o .txt y no superar 15 MB.',
        ];
    }

    public function updatedArchivoCsv(): void
    {
        $this->resetValidation('archivoCsv');
        $this->archivoNombre = null;
        $this->archivoTamanioKb = null;
        $this->encabezadoValido = false;
        $this->ultimoResultado = null;

        if ($this->archivoCsv === null) {
            return;
        }

        try {
            $this->validateOnly('archivoCsv');
        } catch (ValidationException $e) {
            $this->archivoCsv = null;

            throw $e;
        }

        if (! $this->archivoCsv instanceof TemporaryUploadedFile) {
            $this->addError('archivoCsv', 'No se pudo procesar el archivo seleccionado.');
            $this->archivoCsv = null;

            return;
        }

        $this->archivoNombre = $this->archivoCsv->getClientOriginalName();
        $bytes = (int) ($this->archivoCsv->getSize() ?? 0);
        $this->archivoTamanioKb = $bytes > 0 ? (int) ceil($bytes / 1024) : null;

        if (! $this->validarEncabezadoGe($this->archivoCsv)) {
            $this->addError(
                'archivoCsv',
                'El archivo no coincide con el formato GE/CIDI (separador «;» y columnas de calificaciones). Verifique que exportó el listado correcto.'
            );
            $this->archivoCsv = null;
            $this->archivoNombre = null;
            $this->archivoTamanioKb = null;

            return;
        }

        $this->encabezadoValido = true;
    }

    public function quitarArchivo(): void
    {
        $this->archivoCsv = null;
        $this->archivoNombre = null;
        $this->archivoTamanioKb = null;
        $this->encabezadoValido = false;
        $this->resetValidation('archivoCsv');
    }

    public function importar(GeCsvImporter $importer): void
    {
        abort_unless(tienePermiso(2), 403);

        if (! $this->archivoCsv instanceof TemporaryUploadedFile) {
            $this->addError('archivoCsv', 'Seleccione un archivo CSV antes de importar.');

            return;
        }

        $key = 'sincroGe:import:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('archivoCsv', 'Demasiados intentos seguidos. Espere un minuto e intente de nuevo.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate();
        $this->ultimoResultado = null;

        $ctx = schoolCtx();
        $idTerlec = (int) $ctx->idTerlec;
        $idNivel = (int) $ctx->idNivel;

        if ($idTerlec < 1 || $idNivel < 1) {
            $this->addError('archivoCsv', 'No hay contexto de nivel o ciclo lectivo activo en la sesión.');

            return;
        }

        $path = null;
        try {
            $path = $this->resolveCsvAbsolutePath($this->archivoCsv);
            $result = $importer->import($path, $idTerlec, $idNivel);
            $this->ultimoResultado = $this->serializeResult($result);

            if ($result->committed && $result->updatedRows > 0) {
                session()->flash('success', $result->successMessage());
            } elseif ($result->hasIssues() || $result->updatedRows === 0) {
                session()->flash('warning', $result->successMessage());
            }

            $this->quitarArchivo();
        } catch (RuntimeException $e) {
            $this->addError('archivoCsv', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->addError('archivoCsv', 'Error inesperado al importar. No se guardaron cambios. Contacte al administrador si persiste.');
        } finally {
            $this->deleteStoredCopy();
        }
    }

    public function limpiarResultado(): void
    {
        $this->ultimoResultado = null;
        $this->resetValidation();
    }

    private function validarEncabezadoGe(TemporaryUploadedFile $file): bool
    {
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            $path = $file->path();
        }
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fgetcsv($handle, 0, ';');
        fclose($handle);

        if (! is_array($header) || count($header) < 60) {
            return false;
        }

        $joined = mb_strtoupper(implode(';', array_map('trim', $header)), 'UTF-8');

        return str_contains($joined, 'NOTA FINAL')
            && str_contains($joined, 'ESPACIO CURRICULAR')
            && str_contains($joined, 'NOTA EVAL 1');
    }

    private function resolveCsvAbsolutePath(TemporaryUploadedFile $file): string
    {
        $candidates = array_filter([
            $file->getRealPath(),
            method_exists($file, 'path') ? $file->path() : null,
        ], fn ($p) => is_string($p) && $p !== '' && is_readable($p));

        if ($candidates !== []) {
            return (string) reset($candidates);
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            $ext = 'csv';
        }

        $userId = (int) (auth()->id() ?? 0);
        $relative = $file->storeAs(
            'imports/sincro-ge',
            'ge_'.$userId.'_'.uniqid('', true).'.'.$ext,
            'local'
        );

        if ($relative === false || $relative === '') {
            throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
        }

        $this->storedCsvRelativePath = $relative;
        $absolute = storage_path('app/'.$relative);

        if (! is_readable($absolute)) {
            throw new RuntimeException('No se pudo leer el archivo subido.');
        }

        return $absolute;
    }

    private function deleteStoredCopy(): void
    {
        if ($this->storedCsvRelativePath === null) {
            return;
        }

        Storage::disk('local')->delete($this->storedCsvRelativePath);
        $this->storedCsvRelativePath = null;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeResult(GeCsvImportResult $result): array
    {
        return [
            'totalDataRows' => $result->totalDataRows,
            'updatedRows' => $result->updatedRows,
            'skippedRows' => $result->skippedRows,
            'committed' => $result->committed,
            'message' => $result->successMessage(),
            'issues' => $result->issues,
            'issuesTruncated' => $result->issuesTruncated,
        ];
    }

    public function render()
    {
        return view('livewire.calificaciones-secundario.sincro-ge')
            ->layout('layouts.app', ['pageTitle' => 'Descargar calificaciones desde CIDI']);
    }
}
