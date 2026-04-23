<?php

namespace App\Livewire\Abm\Legajos;

use App\Models\Familia;
use App\Models\Legajo;
use App\Models\Nivel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LegajosIndex extends Component
{
    use WithPagination;

    // List state
    public string     $search        = '';
    public bool       $soloMatricula = false;
    public int|string $filtroNivel   = '';   // '' = todos los niveles

    // Modal state
    public bool   $showModal    = false;
    public bool   $showConfirm  = false;
    public string $activeTab    = 'alumno';
    public ?int   $editId       = null;
    public ?int   $deleteId     = null;
    public string $deleteInfo   = '';

    // ─── Alumno ───────────────────────────────────────────────────────────────
    public string     $apellido    = '';
    public string     $nombre      = '';
    public string     $dni         = '';
    public string     $cuil        = '';
    public string     $fechnaci    = '';
    public string     $sexo        = '';
    public string     $nacion      = '';
    public int|string $idnivel     = '';
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

    public function mount(): void
    {
        $this->filtroNivel = '';
    }

    public function updatedFiltroNivel(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        $dniUnique = 'unique:legajos,dni' . ($this->editId ? ",{$this->editId}" : '');

        return [
            'apellido'   => ['required', 'string', 'max:50'],
            'nombre'     => ['required', 'string', 'max:50'],
            'dni'        => ['required', 'digits_between:7,11', $dniUnique],
            'idnivel'    => ['required', 'integer', 'min:1'],
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
            'idnivel.required'    => 'Seleccione un nivel.',
            'fechnaci.date'       => 'Fecha de nacimiento inválida.',
            'email.email'         => 'El email del alumno no es válido.',
            'emailmad.email'      => 'El email de la madre no es válido.',
            'emailpad.email'      => 'El email del padre no es válido.',
            'emailtut.email'      => 'El email del tutor no es válido.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->idnivel  = schoolCtx()->idNivel ?? '';
        $this->activeTab = 'alumno';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $l = Legajo::findOrFail($id);
        $this->editId = $id;

        $this->apellido    = $l->apellido    ?? '';
        $this->nombre      = $l->nombre      ?? '';
        $this->dni         = (string) ($l->dni ?? '');
        $this->cuil        = $l->cuil        ?? '';
        $this->fechnaci    = $l->fechnaci ? $l->fechnaci->format('Y-m-d') : '';
        $this->sexo        = $l->sexo        ?? '';
        $this->nacion      = $l->nacion      ?? '';
        $this->idnivel     = $l->idnivel     ?? '';
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

        $this->resetValidation();
        $this->activeTab = 'alumno';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->formData();

        if ($this->editId) {
            Legajo::findOrFail($this->editId)->update($data);
            session()->flash('success', "Legajo de {$data['apellido']}, {$data['nombre']} actualizado.");
        } else {
            $data['fechhora'] = now();
            Legajo::create($data);
            session()->flash('success', "Legajo de {$data['apellido']}, {$data['nombre']} creado.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $l = Legajo::findOrFail($id);

        $countMatricula      = DB::table('matricula')->where('idLegajos', $id)->count();
        $countCalificaciones = DB::table('calificaciones')->where('idLegajos', $id)->count();
        $countIef            = DB::table('ief')->where('idLegajos', $id)->count();
        $countApf            = DB::table('apf')->where('idLegajos', $id)->count();
        $countVarios         = DB::table('variosalumnos')->where('idLegajos', $id)->count();

        $total = $countMatricula + $countCalificaciones + $countIef + $countApf + $countVarios;

        if ($total > 0) {
            $detail = collect([
                $countMatricula      ? "{$countMatricula} matrículas"          : null,
                $countCalificaciones ? "{$countCalificaciones} calificaciones"  : null,
                $countIef            ? "{$countIef} registros IEF"             : null,
                $countApf            ? "{$countApf} vínculos familiares"        : null,
                $countVarios         ? "{$countVarios} datos varios"            : null,
            ])->filter()->implode(', ');

            $this->deleteInfo = "No se puede eliminar el legajo de {$l->apellido}, {$l->nombre} porque tiene: {$detail}.";
            $this->deleteId   = null;
        } else {
            $this->deleteId   = $id;
            $this->deleteInfo = "¿Confirma eliminar el legajo de {$l->apellido}, {$l->nombre}?";
        }

        $this->showConfirm = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $l = Legajo::findOrFail($this->deleteId);
            $nombre = "{$l->apellido}, {$l->nombre}";
            $l->delete();
            session()->flash('success', "Legajo de {$nombre} eliminado.");
        }

        $this->showConfirm = false;
        $this->reset('deleteId', 'deleteInfo');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'activeTab',
            'apellido', 'nombre', 'dni', 'cuil', 'fechnaci', 'sexo', 'nacion',
            'idnivel', 'idFamilias', 'tipoalumno', 'legajo', 'libro', 'folio',
            'callenum', 'barrio', 'localidad', 'codpos', 'ln_ciudad', 'ln_depto',
            'ln_provincia', 'ln_pais', 'telefono', 'email',
            'nombremad', 'dnimad', 'fechnacmad', 'nacionmad', 'estacivimad', 'domimad',
            'ocupacmad', 'telemad', 'telecelmad', 'emailmad', 'vivemad',
            'nombrepad', 'dnipad', 'fechnacpad', 'nacionpad', 'estacivipad', 'domipad',
            'ocupacpad', 'telepad', 'telecelpad', 'emailpad', 'vivepad',
            'nombretut', 'dnitut', 'teletut', 'emailtut', 'respAdmiNom', 'respAdmiDni',
            'escori', 'destino', 'obs', 'identif', 'vivecon', 'hermanos', 'ec_padres',
            'parroquia', 'needes', 'needes_detalle', 'certDisc', 'emeravis', 'retira',
        ]);
        $this->activeTab = 'alumno';
        $this->resetValidation();
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
            'idnivel'     => (int) $this->idnivel,
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

    public function render()
    {
        $idNivel = schoolCtx()->idNivel;
        $idTerlec = schoolCtx()->idTerlec;

        $query = Legajo::with('familia');

        if ($this->filtroNivel !== '' && $this->filtroNivel !== 0) {
            $query->where('idnivel', $this->filtroNivel);
        }

        if ($this->search !== '') {
            $query->buscar($this->search);
        }

        if ($this->soloMatricula) {
            $query->whereHas('matriculas', fn ($q) => $q->where('idTerlec', $idTerlec));
        }

        $legajos  = $query->orderBy('apellido')->orderBy('nombre')->paginate(25);
        $niveles  = Nivel::orderBy('id')->get(['id', 'nivel']);
        $familias = Familia::orderBy('id')->orderBy('apellido')->get(['id', 'apellido', 'responsable']);

        return view('livewire.abm.legajos.index', compact('legajos', 'niveles', 'familias'))
            ->layout('layouts.app', ['pageTitle' => 'Legajos de Estudiantes']);
    }
}
