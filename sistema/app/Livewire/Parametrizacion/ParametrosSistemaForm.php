<?php

namespace App\Livewire\Parametrizacion;

use App\Models\Ento;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ParametrosSistemaForm extends Component
{
    use WithFileUploads;

    public string $insti = '';
    public string $cue = '';
    public string $ee = '';
    public string $cuit = '';
    public string $categoria = '';
    public string $direccion = '';
    public string $localidad = '';
    public string $departamento = '';
    public string $provincia = '';
    public string $telefono = '';
    public string $mail = '';
    public string $replegal = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    public bool $removeLogo = false;

    public ?string $currentLogoUrl = null;

    public function mount(): void
    {
        $idNivel = (int) (schoolCtx()->idNivel ?? 0);

        /** @var Ento $ento */
        $ento = Ento::query()->firstOrNew(['idNivel' => $idNivel]);

        $this->insti = (string) ($ento->insti ?? '');
        $this->cue = (string) ($ento->cue ?? '');
        $this->ee = (string) ($ento->ee ?? '');
        $this->cuit = (string) ($ento->cuit ?? '');
        $this->categoria = (string) ($ento->categoria ?? '');
        $this->direccion = (string) ($ento->direccion ?? '');
        $this->localidad = (string) ($ento->localidad ?? '');
        $this->departamento = (string) ($ento->departamento ?? '');
        $this->provincia = (string) ($ento->provincia ?? '');
        $this->telefono = (string) ($ento->telefono ?? '');
        $this->mail = (string) ($ento->mail ?? '');
        $this->replegal = (string) ($ento->replegal ?? '');

        $this->currentLogoUrl = schoolLogoUrl();
    }

    protected function rules(): array
    {
        return [
            'insti' => ['nullable', 'string', 'max:120'],
            'cue' => ['nullable', 'string', 'max:30'],
            'ee' => ['nullable', 'string', 'max:30'],
            'cuit' => ['nullable', 'string', 'max:20'],
            'categoria' => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:150'],
            'localidad' => ['nullable', 'string', 'max:80'],
            'departamento' => ['nullable', 'string', 'max:80'],
            'provincia' => ['nullable', 'string', 'max:80'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'mail' => ['nullable', 'email:rfc', 'max:120'],
            'replegal' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'removeLogo' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mail.email' => 'El mail no tiene un formato válido.',
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser JPG/JPEG/PNG.',
            'logo.max' => 'El logo no puede superar los 2MB.',
        ];
    }

    public function updatedLogo(): void
    {
        if ($this->logo) {
            $this->removeLogo = false;
        }
    }

    public function save(): void
    {
        $key = 'parametros-sistema:save:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('insti', 'Demasiados intentos. Espere un momento e intente nuevamente.');
            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate();

        $idNivel = (int) (schoolCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            abort(403);
        }

        /** @var Ento $ento */
        $ento = Ento::query()->firstOrNew(['idNivel' => $idNivel]);

        $payload = [
            'insti' => ($v = trim($this->insti)) !== '' ? $v : null,
            'cue' => ($v = trim($this->cue)) !== '' ? $v : null,
            'ee' => ($v = trim($this->ee)) !== '' ? $v : null,
            'cuit' => ($v = trim($this->cuit)) !== '' ? $v : null,
            'categoria' => ($v = trim($this->categoria)) !== '' ? $v : null,
            'direccion' => ($v = trim($this->direccion)) !== '' ? $v : null,
            'localidad' => ($v = trim($this->localidad)) !== '' ? $v : null,
            'departamento' => ($v = trim($this->departamento)) !== '' ? $v : null,
            'provincia' => ($v = trim($this->provincia)) !== '' ? $v : null,
            'telefono' => ($v = trim($this->telefono)) !== '' ? $v : null,
            'mail' => ($v = trim($this->mail)) !== '' ? $v : null,
            'replegal' => ($v = trim($this->replegal)) !== '' ? $v : null,
        ];

        // Logo: remove tiene prioridad; si luego se sube nuevo, se reemplaza.
        if ($this->removeLogo) {
            $old = (string) ($ento->logo_path ?? '');
            if ($old !== '') {
                Storage::disk('public')->delete($old);
            }
            $payload['logo_path'] = null;
            $payload['logo_original_name'] = null;
        }

        if ($this->logo instanceof TemporaryUploadedFile) {
            $old = (string) ($ento->logo_path ?? '');

            $dir = 'ento/logos/nivel-' . $idNivel;
            $ext = strtolower((string) $this->logo->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $ext = 'jpg';
            }
            $filename = 'logo.' . $ext;
            $newPath = $this->logo->storeAs($dir, $filename, 'public');

            if ($old !== '' && $old !== $newPath) {
                Storage::disk('public')->delete($old);
            }

            $payload['logo_path'] = $newPath;
            $payload['logo_original_name'] = (string) $this->logo->getClientOriginalName();
        }

        $ento->fill($payload);
        $ento->save();

        $this->currentLogoUrl = schoolLogoUrl();
        $this->logo = null;

        session()->flash('success', 'Parámetros del sistema actualizados.');
    }

    public function render()
    {
        return view('livewire.parametrizacion.parametros-sistema-form', [
            'nivelNombre' => schoolCtx()->nivelNombre(),
        ])->layout('layouts.app', ['pageTitle' => 'Parámetros del sistema']);
    }
}

