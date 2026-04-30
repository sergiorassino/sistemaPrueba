<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComMensaje extends Model
{
    protected $table = 'com_mensajes';
    public $timestamps = false;

    protected $fillable = [
        'id_hilo', 'id_mensaje_padre', 'tipo_remitente',
        'id_profesor', 'id_legajo', 'rol_remitente',
        'vinculo_familiar', 'nombre_remitente_snapshot', 'dni_remitente_snapshot',
        'contenido', 'fecha', 'hora',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'created_at' => 'datetime',
    ];

    public function hilo()
    {
        return $this->belongsTo(ComHilo::class, 'id_hilo');
    }

    public function padre()
    {
        return $this->belongsTo(ComMensaje::class, 'id_mensaje_padre');
    }

    public function respuestas()
    {
        return $this->hasMany(ComMensaje::class, 'id_mensaje_padre')->orderBy('created_at');
    }

    public function destinatarios()
    {
        return $this->hasMany(ComMensajeDestinatario::class, 'id_mensaje');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'id_legajo');
    }

    /** Nombre para mostrar en la UI (snapshot) */
    public function nombreDisplay(): string
    {
        $nombre = trim((string) $this->nombre_remitente_snapshot);
        if ($nombre !== '') {
            return $nombre;
        }
        return $this->tipo_remitente === 'familia' ? 'Familia' : 'Personal escolar';
    }

    public static function etiquetasVinculo(): array
    {
        return [
            'madre'     => 'Madre',
            'padre'     => 'Padre',
            'tutor'     => 'Tutor/a',
            'resp_admin'=> 'Resp. Administrativo/a',
            'otro'      => 'Otro responsable',
        ];
    }

    public function vinculoLabel(): string
    {
        return static::etiquetasVinculo()[$this->vinculo_familiar ?? ''] ?? '';
    }
}
