<?php

namespace App\Livewire\Alumnos\Comunicaciones;

use App\Models\ComPreferencia;
use Livewire\Component;

class PreferenciasMedios extends Component
{
    public string $vinculoContacto = '';
    public bool $push     = true;
    public bool $email    = true;
    public bool $whatsapp = false;

    public array $vinculos = [
        'madre'      => 'Madre',
        'padre'      => 'Padre',
        'tutor'      => 'Tutor/a',
        'resp_admin' => 'Responsable Administrativo/a',
        'otro'       => 'Otro responsable',
    ];

    public function mount(): void
    {
        $idLegajo = (int) studentCtx()->idLegajo;
        $pref = ComPreferencia::paraLegajo($idLegajo);

        if ($pref->exists) {
            $this->push            = (bool) $pref->push;
            $this->email           = (bool) $pref->email;
            $this->whatsapp        = (bool) $pref->whatsapp;
            $this->vinculoContacto = (string) ($pref->vinculo_contacto ?? '');
        }
    }

    public function guardar(): void
    {
        $this->validate([
            'vinculoContacto' => 'nullable|in:madre,padre,tutor,resp_admin,otro',
            'push'            => 'boolean',
            'email'           => 'boolean',
            'whatsapp'        => 'boolean',
        ]);

        $idLegajo = (int) studentCtx()->idLegajo;

        ComPreferencia::updateOrCreate(
            ['tipo_usuario' => 'familia', 'id_legajo' => $idLegajo],
            [
                'vinculo_contacto' => $this->vinculoContacto !== '' ? $this->vinculoContacto : null,
                'push'             => $this->push,
                'email'            => $this->email,
                'whatsapp'         => $this->whatsapp,
                'updated_at'       => now(),
            ]
        );

        session()->flash('success', 'Preferencias guardadas correctamente.');
    }

    public function render()
    {
        return view('livewire.alumnos.comunicaciones.preferencias-medios')
            ->layout('layouts.alumno', ['pageTitle' => 'Preferencias de Comunicación']);
    }
}
