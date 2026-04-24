<?php

namespace App\Livewire\Auth;

use App\Models\Nivel;
use App\Models\Terlec;
use App\Support\SchoolContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $dni   = '';
    public string $pwrd  = '';
    public int|string $idNivel  = '';
    public int|string $idTerlec = '';

    public function rules(): array
    {
        return [
            'dni'      => ['required', 'digits_between:7,11'],
            'pwrd'     => ['required', 'min:1'],
            'idNivel'  => ['required', 'integer', 'min:1'],
            'idTerlec' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required'      => 'El DNI es obligatorio.',
            'dni.digits_between'=> 'El DNI debe tener entre 7 y 11 dígitos.',
            'pwrd.required'     => 'La contraseña es obligatoria.',
            'idNivel.required'  => 'Seleccione un nivel.',
            'idNivel.integer'   => 'Seleccione un nivel válido.',
            'idTerlec.required' => 'Seleccione un año lectivo.',
            'idTerlec.integer'  => 'Seleccione un año lectivo válido.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = 'login:' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'dni' => 'Demasiados intentos de acceso. Intente nuevamente en ' . RateLimiter::availableIn($throttleKey) . ' segundos.',
            ]);
        }

        $credentials = [
            'dni'   => $this->dni,
            'pwrd'  => $this->pwrd,
            'nivel' => (int) $this->idNivel,
        ];

        if (Auth::attempt($credentials, false)) {
            /** @var \App\Models\Profesor $profesor */
            $profesor = Auth::user();

            // Regenerar sesión primero (seguridad anti-session-fixation)
            session()->regenerate();

            // Guardar el contexto en la sesión ya regenerada
            SchoolContext::set(
                idProfesor: $profesor->id,
                idNivel:    (int) $this->idNivel,
                idTerlec:   (int) $this->idTerlec,
            );

            // Actualizar último nivel/año en el registro del profesor
            $profesor->update([
                'ult_idNivel'  => (int) $this->idNivel,
                'ult_idTerlec' => (int) $this->idTerlec,
            ]);

            RateLimiter::clear($throttleKey);

            // Redirección completa (no SPA wire:navigate) para garantizar
            // que las cookies de sesión se propaguen correctamente
            return redirect()->route('dashboard');
        }

        RateLimiter::hit($throttleKey, 60);

        $this->addError('dni', 'DNI o contraseña incorrectos. Verifique sus datos.');
    }

    public function render()
    {
        $niveles = Nivel::orderBy('id')->get(['id', 'nivel']);
        $terlecs = Terlec::ordenado()->get(['id', 'ano']);

        return view('livewire.auth.login', compact('niveles', 'terlecs'))
            ->layout('layouts.guest');
    }
}
