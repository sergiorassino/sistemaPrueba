<?php

namespace App\Livewire\Alumnos\Auth;

use App\Models\Ento;
use App\Models\Matricula;
use App\Models\Terlec;
use App\Support\StudentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $dni = '';
    public string $pwrd = '';

    public function rules(): array
    {
        return [
            'dni' => ['required', 'digits_between:7,11'],
            'pwrd' => ['required', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 11 dígitos.',
            'pwrd.required' => 'La contraseña es obligatoria.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = 'alumnos:login:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'dni' => 'Demasiados intentos de acceso. Intente nuevamente en '.RateLimiter::availableIn($throttleKey).' segundos.',
            ]);
        }

        $credentials = [
            'dni' => $this->dni,
            'pwrd' => $this->pwrd,
        ];

        if (Auth::guard('alumno')->attempt($credentials, false)) {
            /** @var \App\Models\Legajo $alumno */
            $alumno = Auth::guard('alumno')->user();

            session()->regenerate();

            $idNivel = (int) ($alumno->idnivel ?? 0);
            if ($idNivel <= 0) {
                $idNivel = (int) (Matricula::query()
                    ->where('idLegajos', (int) $alumno->id)
                    ->orderByDesc('idTerlec')
                    ->orderByDesc('id')
                    ->value('idNivel') ?? 0);
            }

            $idTerlec = (int) (Ento::query()
                ->where('idNivel', $idNivel)
                ->value('idTerlecVerNotas') ?? 0);

            if ($idNivel <= 0 || $idTerlec <= 0 || ! Terlec::query()->whereKey($idTerlec)->exists()) {
                Auth::guard('alumno')->logout();
                StudentContext::clear();
                RateLimiter::clear($throttleKey);

                throw ValidationException::withMessages([
                    'dni' => 'No se pudo determinar el ciclo lectivo para autogestión. Contacte a secretaría.',
                ]);
            }

            StudentContext::set(
                idLegajo: (int) $alumno->id,
                idNivel: $idNivel,
                idTerlec: $idTerlec,
            );

            RateLimiter::clear($throttleKey);

            return redirect()->route('alumnos.calificaciones');
        }

        RateLimiter::hit($throttleKey, 60);

        $this->addError('dni', 'DNI o contraseña incorrectos. Verifique sus datos.');
    }

    public function render()
    {
        return view('livewire.alumnos.auth.login')
            ->layout('layouts.guest', ['guestPortal' => 'alumno']);
    }
}

