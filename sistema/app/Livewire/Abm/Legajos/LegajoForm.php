<?php

namespace App\Livewire\Abm\Legajos;

use App\Models\Condicion;
use App\Models\Curso;
use App\Models\Familia;
use App\Models\Legajo;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\Terlec;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LegajoForm extends Component
{
    public ?int $id = null;
    public string $activeTab = 'alumno';

    // ─── Alumno ───────────────────────────────────────────────────────────────
    public string     $apellido    = '';
    public string     $nombre      = '';
    public string     $dni         = '';
    public string     $cuil        = '';
    public string     $fechnaci    = '';
    public string     $sexo        = '';
    public string     $nacion      = '';
    public int        $idFamilias  = 1;
    public int|string $tipoalumno  = 0;
    public string     $legajo      = '';
    public string     $libro       = '';
    public string     $folio       = '';

    // ─── Domicilio ────────────────────────────────────────────────────────────
    public string $callenum  = '';
    public string $barrio    = '';
    public string $localidad = '';
    public string $codpos    = '';
    public string $ln_ciudad   = '';
    public string $ln_depto    = '';
    public string $ln_provincia = '';
    public string $ln_pais     = '';
    public string $telefono    = '';
    public string $email       = '';

    // ─── Madre ────────────────────────────────────────────────────────────────
    public string $nombremad   = '';
    public string $dnimad      = '';
    public string $fechnacmad  = '';
    public string $nacionmad   = '';
    public string $estacivimad = '';
    public string $domimad     = '';
    public string $ocupacmad   = '';
    public string $telemad     = '';
    public string $telecelmad  = '';
    public string $emailmad    = '';
    public string $vivemad     = '';

    // ─── Padre ────────────────────────────────────────────────────────────────
    public string $nombrepad   = '';
    public string $dnipad      = '';
    public string $fechnacpad  = '';
    public string $nacionpad   = '';
    public string $estacivipad = '';
    public string $domipad     = '';
    public string $ocupacpad   = '';
    public string $telepad     = '';
    public string $telecelpad  = '';
    public string $emailpad    = '';
    public string $vivepad     = '';

    // ─── Tutor / Responsable ──────────────────────────────────────────────────
    public string $nombretut   = '';
    public string $dnitut      = '';
    public string $teletut     = '';
    public string $emailtut    = '';
    public string $respAdmiNom = '';
    public string $respAdmiDni = '';

    // ─── Escolaridad / Obs ────────────────────────────────────────────────────
    public string $escori           = '';
    public string $destino          = '';
    public string $obs              = '';
    public string $identif          = '';
    public string $vivecon          = '';
    public string $hermanos         = '';
    public string $ec_padres        = '';
    public string $parroquia        = '';
    public string $needes           = '';
    public string $needes_detalle   = '';
    public string $certDisc         = '';
    public string $emeravis         = '';
    public string $retira           = '';

    // ─── Matrículas (modales) ────────────────────────────────────────────────
    public bool   $showMatriculasModal = false;
    public bool   $showMatriculaForm   = false;
    public ?int   $matriculaEditId     = null;
    public ?int   $matriculaDeleteId   = null;
    public bool   $showMatriculaConfirm = false;
    public string $matriculaDeleteInfo  = '';

    // Matrícula form fields
    public int|string $m_idCursos       = '';
    public int|string $m_idCondiciones  = '';
    public int|string $m_idTerlec       = '';
    public int|string $m_idNivel        = '';
    public string     $m_terlec_ano     = '';
    public string     $m_nivel_nombre   = '';
    public string     $m_nroMatricula   = '';
    public string     $m_fechaMatricula = '';
    public string     $m_fechaBaja      = '';

    public function mount(?int $id = null): void
    {
        $this->id = $id;
        if ($id) {
            $this->loadLegajo($id);
        }
    }

    protected function rules(): array
    {
        $dniUnique = 'unique:legajos,dni' . ($this->id ? ",{$this->id}" : '');

        return [
            'apellido'   => ['required', 'string', 'max:50'],
            'nombre'     => ['required', 'string', 'max:50'],
            'dni'        => ['required', 'digits_between:7,11', $dniUnique],
            'idFamilias' => ['nullable', 'integer', 'min:1'],
            'fechnaci'   => ['nullable', 'date'],
            'cuil'       => ['nullable', 'string', 'max:13'],
            'email'      => ['nullable', 'email', 'max:100'],
            'emailmad'   => ['nullable', 'email', 'max:50'],
            'emailpad'   => ['nullable', 'email', 'max:50'],
            'emailtut'   => ['nullable', 'email', 'max:50'],
        ];
    }

    protected function messages(): array
    {
        return [
            'apellido.required'   => 'El apellido es obligatorio.',
            'nombre.required'     => 'El nombre es obligatorio.',
            'dni.required'        => 'El DNI es obligatorio.',
            'dni.digits_between'  => 'El DNI debe tener entre 7 y 11 dígitos.',
            'dni.unique'          => 'Ya existe un legajo con ese DNI.',
            'fechnaci.date'       => 'Fecha de nacimiento inválida.',
            'email.email'         => 'El email del alumno no es válido.',
            'emailmad.email'      => 'El email de la madre no es válido.',
            'emailpad.email'      => 'El email del padre no es válido.',
            'emailtut.email'      => 'El email del tutor no es válido.',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function save(): mixed
    {
        $this->validate();

        $data = $this->formData();

        if ($this->id) {
            Legajo::findOrFail($this->id)->update($data);
            session()->flash('success', "Legajo de {$data['apellido']}, {$data['nombre']} actualizado.");
        } else {
            $data['fechhora'] = now();
            $legajo = Legajo::create($data);
            $this->id = (int) $legajo->id;
            session()->flash('success', "Legajo de {$data['apellido']}, {$data['nombre']} creado.");
        }

        $focusId = (int) $this->id;
        $page = $this->pageForLegajo($focusId, 25);

        return redirect()->route('abm.legajos', [
            'page' => $page,
            'focus' => $focusId,
        ]);
    }

    public function cancel(): mixed
    {
        return redirect()->route('abm.legajos', ['focus' => $this->id]);
    }

    // ─── Matrículas ───────────────────────────────────────────────────────────
    public function openMatriculas(): void
    {
        if (!$this->id) {
            return;
        }

        $this->showMatriculasModal = true;
        $this->showMatriculaForm = false;
        $this->resetMatriculaForm();
    }

    public function closeMatriculas(): void
    {
        $this->showMatriculasModal = false;
        $this->showMatriculaForm = false;
        $this->resetMatriculaForm();
    }

    public function openNuevaMatricula(): void
    {
        $this->matriculaEditId = null;
        $this->resetMatriculaForm();

        $this->m_idTerlec = (int) (schoolCtx()->idTerlec ?? 0);
        $this->m_idNivel  = (int) (schoolCtx()->idNivel ?? 0);
        $this->fillMatriculaReadonlyLabels();
        $this->m_fechaMatricula = now()->format('Y-m-d');

        $this->resetValidation();
        $this->showMatriculaForm = true;
    }

    public function openEditMatricula(int $id): void
    {
        $m = Matricula::where('idLegajos', $this->id)->findOrFail($id);
        $this->matriculaEditId = $id;

        $this->m_idCursos      = (int) ($m->idCursos ?? 0);
        $this->m_idCondiciones = (int) ($m->idCondiciones ?? 0);
        $this->m_idTerlec      = (int) ($m->idTerlec ?? 0);
        $this->m_idNivel       = (int) ($m->idNivel ?? 0);
        $this->fillMatriculaReadonlyLabels();
        $this->m_nroMatricula  = (string) ($m->nroMatricula ?? '');
        $this->m_fechaMatricula = $m->fechaMatricula ? $m->fechaMatricula->format('Y-m-d') : '';
        $this->m_fechaBaja      = $m->fechaBaja ? $m->fechaBaja->format('Y-m-d') : '';

        $this->resetValidation();
        $this->showMatriculaForm = true;
    }

    public function saveMatricula(): void
    {
        $this->validate([
            'm_idCursos' => ['required', 'integer', 'min:1'],
            'm_idCondiciones' => ['required', 'integer', 'min:1'],
            'm_idTerlec' => ['required', 'integer', 'min:1'],
            'm_idNivel'  => ['required', 'integer', 'min:1'],
            'm_nroMatricula' => ['nullable', 'string', 'max:20'],
            'm_fechaMatricula' => ['nullable', 'date'],
            'm_fechaBaja' => ['nullable', 'date'],
        ], [
            'm_idCursos.required' => 'Seleccione curso y sección.',
            'm_idCondiciones.required' => 'Seleccione condición.',
        ]);

        if (!$this->id) {
            return;
        }

        $data = [
            'idLegajos'     => (int) $this->id,
            'idCursos'      => (int) $this->m_idCursos,
            'idCondiciones' => (int) $this->m_idCondiciones,
            'idTerlec'      => (int) $this->m_idTerlec,
            'idNivel'       => (int) $this->m_idNivel,
            'nroMatricula'  => trim($this->m_nroMatricula) !== '' ? trim($this->m_nroMatricula) : null,
            'fechaMatricula'=> $this->m_fechaMatricula ?: null,
            'fechaBaja'     => $this->m_fechaBaja ?: null,
        ];

        if ($this->matriculaEditId) {
            Matricula::where('idLegajos', $this->id)
                ->findOrFail($this->matriculaEditId)
                ->update($data);
            session()->flash('success', 'Matrícula actualizada.');
        } else {
            Matricula::create($data);
            session()->flash('success', 'Matrícula creada.');
        }

        $this->showMatriculaForm = false;
        $this->resetMatriculaForm();
    }

    public function confirmDeleteMatricula(int $id): void
    {
        $m = Matricula::where('idLegajos', $this->id)->with(['terlec', 'curso'])->findOrFail($id);

        $this->matriculaDeleteId = $id;
        $descAno = $m->terlec?->ano ?? '—';
        $descCurso = $m->curso?->cursec ? trim($m->curso->cursec) : '—';
        $this->matriculaDeleteInfo = "¿Confirma eliminar la matrícula {$descAno} · {$descCurso}?";
        $this->showMatriculaConfirm = true;
    }

    public function deleteMatricula(): void
    {
        if ($this->matriculaDeleteId && $this->id) {
            Matricula::where('idLegajos', $this->id)->findOrFail($this->matriculaDeleteId)->delete();
            session()->flash('success', 'Matrícula eliminada.');
        }

        $this->showMatriculaConfirm = false;
        $this->reset('matriculaDeleteId', 'matriculaDeleteInfo');
    }

    private function resetMatriculaForm(): void
    {
        $this->reset([
            'matriculaEditId',
            'm_idCursos', 'm_idCondiciones', 'm_idTerlec', 'm_idNivel',
            'm_terlec_ano', 'm_nivel_nombre',
            'm_nroMatricula', 'm_fechaMatricula', 'm_fechaBaja',
        ]);
    }

    private function fillMatriculaReadonlyLabels(): void
    {
        $terlec = null;
        $nivel  = null;

        $idTerlec = (int) ($this->m_idTerlec ?: 0);
        $idNivel  = (int) ($this->m_idNivel ?: 0);

        if ($idTerlec > 0) {
            $terlec = Terlec::query()->find($idTerlec);
        }
        if ($idNivel > 0) {
            $nivel = Nivel::query()->find($idNivel);
        }

        $this->m_terlec_ano   = $terlec?->ano !== null ? (string) $terlec->ano : '';
        $this->m_nivel_nombre = $nivel?->nivel ? (string) $nivel->nivel : '';
    }

    private function loadLegajo(int $id): void
    {
        $l = Legajo::findOrFail($id);

        $this->apellido    = $l->apellido    ?? '';
        $this->nombre      = $l->nombre      ?? '';
        $this->dni         = (string) ($l->dni ?? '');
        $this->cuil        = $l->cuil        ?? '';
        $this->fechnaci    = $l->fechnaci ? $l->fechnaci->format('Y-m-d') : '';
        $this->sexo        = $l->sexo        ?? '';
        $this->nacion      = $l->nacion      ?? '';
        $this->idFamilias  = $l->idFamilias > 0 ? (int) $l->idFamilias : 1;
        $this->tipoalumno  = $l->tipoalumno  ?? 0;
        $this->legajo      = $l->legajo      ?? '';
        $this->libro       = $l->libro       ?? '';
        $this->folio       = $l->folio       ?? '';

        $this->callenum    = $l->callenum    ?? '';
        $this->barrio      = $l->barrio      ?? '';
        $this->localidad   = $l->localidad   ?? '';
        $this->codpos      = $l->codpos      ?? '';
        $this->ln_ciudad   = $l->ln_ciudad   ?? '';
        $this->ln_depto    = $l->ln_depto    ?? '';
        $this->ln_provincia = $l->ln_provincia ?? '';
        $this->ln_pais     = $l->ln_pais     ?? '';
        $this->telefono    = $l->telefono    ?? '';
        $this->email       = $l->email       ?? '';

        $this->nombremad   = $l->nombremad   ?? '';
        $this->dnimad      = $l->dnimad      ?? '';
        $this->fechnacmad  = $l->fechnacmad ? $l->fechnacmad->format('Y-m-d') : '';
        $this->nacionmad   = $l->nacionmad   ?? '';
        $this->estacivimad = $l->estacivimad ?? '';
        $this->domimad     = $l->domimad     ?? '';
        $this->ocupacmad   = $l->ocupacmad   ?? '';
        $this->telemad     = $l->telemad     ?? '';
        $this->telecelmad  = $l->telecelmad  ?? '';
        $this->emailmad    = $l->emailmad    ?? '';
        $this->vivemad     = $l->vivemad     ?? '';

        $this->nombrepad   = $l->nombrepad   ?? '';
        $this->dnipad      = $l->dnipad      ?? '';
        $this->fechnacpad  = $l->fechnacpad ? $l->fechnacpad->format('Y-m-d') : '';
        $this->nacionpad   = $l->nacionpad   ?? '';
        $this->estacivipad = $l->estacivipad ?? '';
        $this->domipad     = $l->domipad     ?? '';
        $this->ocupacpad   = $l->ocupacpad   ?? '';
        $this->telepad     = $l->telepad     ?? '';
        $this->telecelpad  = $l->telecelpad  ?? '';
        $this->emailpad    = $l->emailpad    ?? '';
        $this->vivepad     = $l->vivepad     ?? '';

        $this->nombretut   = $l->nombretut   ?? '';
        $this->dnitut      = (string) ($l->dnitut ?? '');
        $this->teletut     = $l->teletut     ?? '';
        $this->emailtut    = $l->emailtut    ?? '';
        $this->respAdmiNom = $l->respAdmiNom ?? '';
        $this->respAdmiDni = (string) ($l->respAdmiDni ?? '');

        $this->escori         = $l->escori         ?? '';
        $this->destino        = $l->destino        ?? '';
        $this->obs            = $l->obs            ?? '';
        $this->identif        = $l->identif        ?? '';
        $this->vivecon        = $l->vivecon        ?? '';
        $this->hermanos       = $l->hermanos       ?? '';
        $this->ec_padres      = $l->ec_padres      ?? '';
        $this->parroquia      = $l->parroquia      ?? '';
        $this->needes         = $l->needes         ?? '';
        $this->needes_detalle = $l->needes_detalle ?? '';
        $this->certDisc       = $l->certDisc       ?? '';
        $this->emeravis       = $l->emeravis       ?? '';
        $this->retira         = $l->retira         ?? '';
    }

    private function formData(): array
    {
        return [
            'apellido'    => strtoupper(trim($this->apellido)),
            'nombre'      => ucwords(strtolower(trim($this->nombre))),
            'dni'         => $this->dni !== '' ? (int) $this->dni : null,
            'cuil'        => $this->cuil,
            'fechnaci'    => $this->fechnaci ?: null,
            'sexo'        => $this->sexo,
            'nacion'      => $this->nacion,
            'idFamilias'  => $this->idFamilias > 0 ? $this->idFamilias : 1,
            'tipoalumno'  => (int) $this->tipoalumno,
            'legajo'      => $this->legajo,
            'libro'       => $this->libro,
            'folio'       => $this->folio,
            'callenum'    => $this->callenum,
            'barrio'      => $this->barrio,
            'localidad'   => $this->localidad,
            'codpos'      => $this->codpos,
            'ln_ciudad'   => $this->ln_ciudad,
            'ln_depto'    => $this->ln_depto,
            'ln_provincia'=> $this->ln_provincia,
            'ln_pais'     => $this->ln_pais,
            'telefono'    => $this->telefono,
            'email'       => $this->email,
            'nombremad'   => $this->nombremad,
            'dnimad'      => $this->dnimad,
            'fechnacmad'  => $this->fechnacmad ?: null,
            'nacionmad'   => $this->nacionmad,
            'estacivimad' => $this->estacivimad,
            'domimad'     => $this->domimad,
            'ocupacmad'   => $this->ocupacmad,
            'telemad'     => $this->telemad,
            'telecelmad'  => $this->telecelmad,
            'emailmad'    => $this->emailmad,
            'vivemad'     => $this->vivemad,
            'nombrepad'   => $this->nombrepad,
            'dnipad'      => $this->dnipad,
            'fechnacpad'  => $this->fechnacpad ?: null,
            'nacionpad'   => $this->nacionpad,
            'estacivipad' => $this->estacivipad,
            'domipad'     => $this->domipad,
            'ocupacpad'   => $this->ocupacpad,
            'telepad'     => $this->telepad,
            'telecelpad'  => $this->telecelpad,
            'emailpad'    => $this->emailpad,
            'vivepad'     => $this->vivepad,
            'nombretut'   => $this->nombretut,
            'dnitut'      => $this->dnitut !== '' ? (int) $this->dnitut : null,
            'teletut'     => $this->teletut,
            'emailtut'    => $this->emailtut,
            'respAdmiNom' => $this->respAdmiNom,
            'respAdmiDni' => $this->respAdmiDni !== '' ? (int) $this->respAdmiDni : 0,
            'escori'      => $this->escori,
            'destino'     => $this->destino,
            'obs'         => $this->obs,
            'identif'     => $this->identif,
            'vivecon'     => $this->vivecon,
            'hermanos'    => $this->hermanos,
            'ec_padres'   => $this->ec_padres,
            'parroquia'   => $this->parroquia,
            'needes'      => $this->needes,
            'needes_detalle' => $this->needes_detalle,
            'certDisc'    => $this->certDisc,
            'emeravis'    => $this->emeravis,
            'retira'      => $this->retira,
        ];
    }

    private function pageForLegajo(int $id, int $perPage): int
    {
        $l = Legajo::find($id);
        if (!$l) {
            return 1;
        }

        $countBefore = Legajo::where(function ($q) use ($l) {
            $q->where('apellido', '<', $l->apellido)
                ->orWhere(function ($q2) use ($l) {
                    $q2->where('apellido', $l->apellido)
                        ->where('nombre', '<', $l->nombre);
                });
        })->count();

        return (int) floor($countBefore / $perPage) + 1;
    }

    public function render()
    {
        $idTerlec = schoolCtx()->idTerlec;
        $idNivel = schoolCtx()->idNivel;

        $familias = Familia::orderBy('id')->orderBy('apellido')->get(['id', 'apellido', 'responsable']);

        $cursos = Curso::query()
            ->when($idNivel, fn ($q) => $q->where('idNivel', $idNivel))
            ->when($idTerlec, fn ($q) => $q->where('idTerlec', $idTerlec))
            ->orderBy('Id')
            ->get(['Id', 'cursec']);

        $condiciones = Condicion::query()
            ->orderBy('id')
            ->get(['id', 'condicion']);

        $matriculasAlumno = collect();
        if ($this->id) {
            $matriculasAlumno = Matricula::where('idLegajos', $this->id)
                ->with(['terlec', 'curso', 'condicion'])
                ->orderByDesc(DB::raw('(SELECT COALESCE(ano, 0) FROM terlec WHERE terlec.id = matricula.idTerlec LIMIT 1)'))
                ->orderByDesc('matricula.id')
                ->get();
        }

        return view('livewire.abm.legajos.form', compact('familias', 'cursos', 'condiciones', 'matriculasAlumno'))
            ->layout('layouts.app', ['pageTitle' => $this->id ? 'Editar legajo' : 'Nuevo legajo']);
    }
}

