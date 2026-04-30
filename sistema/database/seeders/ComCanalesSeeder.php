<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComCanalesSeeder extends Seeder
{
    /**
     * Configuración por defecto de canales de comunicación.
     *
     * medios_permitidos es la intersección de lo que el canal permite con
     * lo que la familia/profesor haya elegido en sus preferencias.
     */
    public function run(): void
    {
        $canales = [
            // Familia → Preceptor: puede iniciar y responder (canales completos)
            [
                'rol_emisor'      => 'familia',
                'rol_receptor'    => 'preceptor',
                'puede_iniciar'   => true,
                'puede_responder' => true,
                'medios_permitidos' => json_encode(['push', 'email', 'whatsapp']),
                'activo'          => true,
            ],
            // Familia → Profesor: solo puede responder (no inicia)
            [
                'rol_emisor'      => 'familia',
                'rol_receptor'    => 'profesor',
                'puede_iniciar'   => false,
                'puede_responder' => true,
                'medios_permitidos' => json_encode(['push', 'email']),
                'activo'          => true,
            ],
            // Familia → Directivo: puede iniciar y responder
            [
                'rol_emisor'      => 'familia',
                'rol_receptor'    => 'directivo',
                'puede_iniciar'   => true,
                'puede_responder' => true,
                'medios_permitidos' => json_encode(['push', 'email']),
                'activo'          => true,
            ],
            // Profesor → Familia: inicia pero no recibe respuestas
            [
                'rol_emisor'      => 'profesor',
                'rol_receptor'    => 'familia',
                'puede_iniciar'   => true,
                'puede_responder' => false,
                'medios_permitidos' => json_encode(['push', 'email']),
                'activo'          => true,
            ],
            // Preceptor → Familia: canales completos
            [
                'rol_emisor'      => 'preceptor',
                'rol_receptor'    => 'familia',
                'puede_iniciar'   => true,
                'puede_responder' => true,
                'medios_permitidos' => json_encode(['push', 'email', 'whatsapp']),
                'activo'          => true,
            ],
            // Directivo → Familia: canales completos
            [
                'rol_emisor'      => 'directivo',
                'rol_receptor'    => 'familia',
                'puede_iniciar'   => true,
                'puede_responder' => true,
                'medios_permitidos' => json_encode(['push', 'email', 'whatsapp']),
                'activo'          => true,
            ],
            // Preceptor → Profesor: avisos internos (solo inicio)
            [
                'rol_emisor'      => 'preceptor',
                'rol_receptor'    => 'profesor',
                'puede_iniciar'   => true,
                'puede_responder' => false,
                'medios_permitidos' => json_encode(['push', 'email']),
                'activo'          => true,
            ],
        ];

        foreach ($canales as $canal) {
            DB::table('com_canales')->updateOrInsert(
                ['rol_emisor' => $canal['rol_emisor'], 'rol_receptor' => $canal['rol_receptor']],
                array_merge($canal, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
