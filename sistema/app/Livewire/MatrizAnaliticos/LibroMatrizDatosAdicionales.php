<?php

namespace App\Livewire\MatrizAnaliticos;

use App\Support\MatrizAnaliticos\LibroMatrizAnalitico;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Datos adicionales del analítico (tabla analiticodatos) por legajo.
 */
class LibroMatrizDatosAdicionales extends Component
{
    public int $idLegajos = 0;

    /** @var array<string, string> */
    public array $alumno = [];

    public ?int $idAnaliticoDato = null;

    public string $analCohorte = '';

    public string $analObservaciones = '';

    public string $analParaCompletar = '';

    public string $analValidez = '';

    public string $serie = '';

    public string $analLibroFolio = '';

    public string $analFechaEmision = '';

    public string $analParaPre = '';

    public string $buscarRetorno = '';

    public function mount(): void
    {
        $idLegajos = \App\Support\Navegacion\ContextoEstudianteSesion::legajo(
            \App\Support\Navegacion\ContextoEstudianteSesion::MATRIZ_ANALITICOS,
        );
        abort_if($idLegajos === null, 404);

        abort_unless(tienePermiso(16), 403, 'Sin permiso para Libro Matriz / Analítico.');

        $ctx = schoolCtx();
        if (! str_contains(mb_strtolower($ctx->nivelNombre()), 'secundari')) {
            abort(403, 'Este módulo requiere contexto de Secundario.');
        }

        $alumno = LibroMatrizAnalitico::alumno($idLegajos);
        if ($alumno === null) {
            abort(404, 'Legajo no encontrado.');
        }

        $this->idLegajos = $idLegajos;
        $this->alumno = $alumno;
        $this->buscarRetorno = LibroMatrizAnalitico::buscarDesdeRequest();

        $datos = LibroMatrizAnalitico::datosAdicionales($idLegajos);
        $this->idAnaliticoDato = $datos['id'];
        $this->analCohorte = $datos['analCohorte'];
        $this->analObservaciones = $datos['analObservaciones'];
        $this->analParaCompletar = $datos['analParaCompletar'];
        $this->analValidez = $datos['analValidez'];
        $this->serie = $datos['serie'];
        $this->analLibroFolio = $datos['analLibroFolio'];
        $this->analFechaEmision = $datos['analFechaEmision'];
        $this->analParaPre = $datos['analParaPre'];
    }

    public function guardar(): void
    {
        abort_unless(tienePermiso(16), 403);

        $key = 'matrizAnaliticos:datos-adicionales:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->addError('guardar', 'Demasiados intentos. Espere un momento e intente nuevamente.');

            return;
        }
        RateLimiter::hit($key, 60);

        $validated = $this->validate($this->reglas());

        if (! LibroMatrizAnalitico::guardarDatosAdicionales($this->idLegajos, $validated)) {
            $this->addError('guardar', 'No se pudo guardar. Verifique los datos.');

            return;
        }

        session()->flash('success', 'Datos adicionales guardados.');

        \App\Support\Navegacion\ContextoEstudianteSesion::fijar(
            \App\Support\Navegacion\ContextoEstudianteSesion::MATRIZ_ANALITICOS,
            ['idLegajos' => $this->idLegajos],
        );

        $this->redirect(LibroMatrizAnalitico::rutaEditar(), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(): array
    {
        return [
            'analCohorte' => ['nullable', 'string', 'max:30'],
            'analObservaciones' => ['nullable', 'string', 'max:65535'],
            'analParaCompletar' => ['nullable', 'string', 'max:65535'],
            'analValidez' => ['nullable', 'string', 'max:50'],
            'serie' => ['nullable', 'string', 'max:6'],
            'analLibroFolio' => ['nullable', 'string', 'max:50'],
            'analFechaEmision' => ['nullable', 'date'],
            'analParaPre' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function render()
    {
        return view('livewire.matriz-analiticos.libro-matriz-datos-adicionales')
            ->layout(layoutMenuStaff(), ['pageTitle' => 'Datos adicionales · '.$this->alumno['apellido']]);
    }
}
