<?php

namespace App\Support;

/**
 * Campos permitidos para el PDF de listado por curso (legajos + matrícula + condición).
 * Solo se aceptan claves de este catálogo en la query string; nunca input libre hacia SQL.
 */
final class ListadoCursoPdfFieldCatalog
{
    public const DEFAULT_KEYS = [
        'legajos.apellido',
        'legajos.nombre',
        'legajos.dni',
    ];

    /** @var array<string, array{label: string, group: string, table: string, column: string, needs_condiciones?: bool}> */
    private const DEFINITIONS = [
        // — Alumno —
        'legajos.apellido' => ['label' => 'Apellido', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'apellido'],
        'legajos.nombre' => ['label' => 'Nombre', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'nombre'],
        'legajos.dni' => ['label' => 'DNI', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'dni'],
        'legajos.cuil' => ['label' => 'CUIL', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'cuil'],
        'legajos.fechnaci' => ['label' => 'Fecha de nacimiento', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'fechnaci'],
        'legajos.sexo' => ['label' => 'Sexo', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'sexo'],
        'legajos.nacion' => ['label' => 'Nacionalidad', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'nacion'],
        'legajos.tipoalumno' => ['label' => 'Tipo de alumno', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'tipoalumno'],
        'legajos.legajo' => ['label' => 'Legajo', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'legajo'],
        'legajos.libro' => ['label' => 'Libro', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'libro'],
        'legajos.folio' => ['label' => 'Folio', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'folio'],
        'legajos.codigo' => ['label' => 'Código', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'codigo'],
        'legajos.identif' => ['label' => 'Identificación', 'group' => 'Alumno', 'table' => 'legajos', 'column' => 'identif'],
        // — Domicilio y contacto —
        'legajos.callenum' => ['label' => 'Calle y número', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'callenum'],
        'legajos.barrio' => ['label' => 'Barrio', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'barrio'],
        'legajos.localidad' => ['label' => 'Localidad', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'localidad'],
        'legajos.codpos' => ['label' => 'Código postal', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'codpos'],
        'legajos.ln_ciudad' => ['label' => 'Lugar nac. — ciudad', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'ln_ciudad'],
        'legajos.ln_depto' => ['label' => 'Lugar nac. — departamento', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'ln_depto'],
        'legajos.ln_provincia' => ['label' => 'Lugar nac. — provincia', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'ln_provincia'],
        'legajos.ln_pais' => ['label' => 'Lugar nac. — país', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'ln_pais'],
        'legajos.telefono' => ['label' => 'Teléfono', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'telefono'],
        'legajos.email' => ['label' => 'Email', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'email'],
        'legajos.contacto1' => ['label' => 'Contacto 1', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'contacto1'],
        'legajos.contacto2' => ['label' => 'Contacto 2', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'contacto2'],
        'legajos.contacto3' => ['label' => 'Contacto 3', 'group' => 'Domicilio y contacto', 'table' => 'legajos', 'column' => 'contacto3'],
        // — Madre —
        'legajos.nombremad' => ['label' => 'Madre — nombre', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'nombremad'],
        'legajos.dnimad' => ['label' => 'Madre — DNI', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'dnimad'],
        'legajos.vivemad' => ['label' => 'Madre — vive', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'vivemad'],
        'legajos.fechnacmad' => ['label' => 'Madre — fecha nac.', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'fechnacmad'],
        'legajos.nacionmad' => ['label' => 'Madre — nacionalidad', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'nacionmad'],
        'legajos.estacivimad' => ['label' => 'Madre — estado civil', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'estacivimad'],
        'legajos.domimad' => ['label' => 'Madre — domicilio', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'domimad'],
        'legajos.cpmad' => ['label' => 'Madre — CP', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'cpmad'],
        'legajos.ocupacmad' => ['label' => 'Madre — ocupación', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'ocupacmad'],
        'legajos.sitlabmad' => ['label' => 'Madre — situación laboral', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'sitlabmad'],
        'legajos.lugtramad' => ['label' => 'Madre — lugar de trabajo', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'lugtramad'],
        'legajos.telemad' => ['label' => 'Madre — teléfono', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'telemad'],
        'legajos.telecelmad' => ['label' => 'Madre — celular', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'telecelmad'],
        'legajos.telltm' => ['label' => 'Madre — tel. laboral', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'telltm'],
        'legajos.emailmad' => ['label' => 'Madre — email', 'group' => 'Madre', 'table' => 'legajos', 'column' => 'emailmad'],
        // — Padre —
        'legajos.nombrepad' => ['label' => 'Padre — nombre', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'nombrepad'],
        'legajos.dnipad' => ['label' => 'Padre — DNI', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'dnipad'],
        'legajos.vivepad' => ['label' => 'Padre — vive', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'vivepad'],
        'legajos.fechnacpad' => ['label' => 'Padre — fecha nac.', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'fechnacpad'],
        'legajos.nacionpad' => ['label' => 'Padre — nacionalidad', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'nacionpad'],
        'legajos.estacivipad' => ['label' => 'Padre — estado civil', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'estacivipad'],
        'legajos.domipad' => ['label' => 'Padre — domicilio', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'domipad'],
        'legajos.cppad' => ['label' => 'Padre — CP', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'cppad'],
        'legajos.ocupacpad' => ['label' => 'Padre — ocupación', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'ocupacpad'],
        'legajos.sitlabpad' => ['label' => 'Padre — situación laboral', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'sitlabpad'],
        'legajos.lugtrapad' => ['label' => 'Padre — lugar de trabajo', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'lugtrapad'],
        'legajos.telepad' => ['label' => 'Padre — teléfono', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'telepad'],
        'legajos.telecelpad' => ['label' => 'Padre — celular', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'telecelpad'],
        'legajos.telltp' => ['label' => 'Padre — tel. laboral', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'telltp'],
        'legajos.emailpad' => ['label' => 'Padre — email', 'group' => 'Padre', 'table' => 'legajos', 'column' => 'emailpad'],
        // — Tutor / responsable —
        'legajos.nombretut' => ['label' => 'Tutor — nombre', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'nombretut'],
        'legajos.dnitut' => ['label' => 'Tutor — DNI', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'dnitut'],
        'legajos.teletut' => ['label' => 'Tutor — teléfono', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'teletut'],
        'legajos.emailtut' => ['label' => 'Tutor — email', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'emailtut'],
        'legajos.ocupactut' => ['label' => 'Tutor — ocupación', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'ocupactut'],
        'legajos.lugtratut' => ['label' => 'Tutor — lugar de trabajo', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'lugtratut'],
        'legajos.telltt' => ['label' => 'Tutor — tel. laboral', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'telltt'],
        'legajos.respAdmiNom' => ['label' => 'Resp. administrativo — nombre', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'respAdmiNom'],
        'legajos.respAdmiDni' => ['label' => 'Resp. administrativo — DNI', 'group' => 'Tutor / responsable', 'table' => 'legajos', 'column' => 'respAdmiDni'],
        // — Escolaridad y otros —
        'legajos.escori' => ['label' => 'Escolaridad origen', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'escori'],
        'legajos.destino' => ['label' => 'Destino', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'destino'],
        'legajos.emeravis' => ['label' => 'Emergencia / avisar a', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'emeravis'],
        'legajos.retira' => ['label' => 'Retira', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'retira'],
        'legajos.retira1' => ['label' => 'Retira (1)', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'retira1'],
        'legajos.retira2' => ['label' => 'Retira (2)', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'retira2'],
        'legajos.obs' => ['label' => 'Observaciones', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'obs'],
        'legajos.obs_web' => ['label' => 'Observaciones web', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'obs_web'],
        'legajos.vivecon' => ['label' => 'Vive con', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'vivecon'],
        'legajos.hermanos' => ['label' => 'Hermanos', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'hermanos'],
        'legajos.ec_padres' => ['label' => 'Estado civil padres', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'ec_padres'],
        'legajos.parroquia' => ['label' => 'Parroquia', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'parroquia'],
        'legajos.needes' => ['label' => 'N.E.E.', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'needes'],
        'legajos.needes_detalle' => ['label' => 'N.E.E. — detalle', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'needes_detalle'],
        'legajos.certDisc' => ['label' => 'Certificado discapacidad', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'certDisc'],
        'legajos.motivo_detalle' => ['label' => 'Motivo — detalle', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'motivo_detalle'],
        'legajos.acopro' => ['label' => 'Acompañante', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'acopro'],
        'legajos.acopro_detalle' => ['label' => 'Acompañante — detalle', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'acopro_detalle'],
        'legajos.bloqmatr' => ['label' => 'Bloqueo matrícula', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'bloqmatr'],
        'legajos.bloqadmi' => ['label' => 'Bloqueo administración', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'bloqadmi'],
        'legajos.idnivel' => ['label' => 'ID nivel (legajo)', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'idnivel'],
        'legajos.idFamilias' => ['label' => 'ID familia', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'idFamilias'],
        'legajos.fechhora' => ['label' => 'Fecha/hora registro', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'fechhora'],
        'legajos.fechActDatos' => ['label' => 'Última actualización datos', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'fechActDatos'],
        'legajos.reglamApenom' => ['label' => 'Reglamento — apellido y nombre', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'reglamApenom'],
        'legajos.reglamDni' => ['label' => 'Reglamento — DNI', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'reglamDni'],
        'legajos.reglamEmail' => ['label' => 'Reglamento — email', 'group' => 'Escolaridad y otros', 'table' => 'legajos', 'column' => 'reglamEmail'],
        // — Matrícula (tabla matricula) —
        'matricula.nroMatricula' => ['label' => 'N° matrícula', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'nroMatricula'],
        'matricula.fechaMatricula' => ['label' => 'Fecha de matrícula', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'fechaMatricula'],
        'matricula.obsMatr' => ['label' => 'Obs. matrícula', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'obsMatr'],
        'matricula.obsAnual' => ['label' => 'Obs. anual', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'obsAnual'],
        'matricula.conducta1' => ['label' => 'Conducta 1°', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'conducta1'],
        'matricula.conducta2' => ['label' => 'Conducta 2°', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'conducta2'],
        'matricula.acept1' => ['label' => 'Aceptación 1', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'acept1'],
        'matricula.acept2' => ['label' => 'Aceptación 2', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'acept2'],
        'matricula.acept3' => ['label' => 'Aceptación 3', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'acept3'],
        'matricula.acept4' => ['label' => 'Aceptación 4', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'acept4'],
        'matricula.inscripto' => ['label' => 'Inscripto', 'group' => 'Matrícula', 'table' => 'matricula', 'column' => 'inscripto'],
        'condiciones.condicion' => ['label' => 'Condición de matrícula', 'group' => 'Matrícula', 'table' => 'condiciones', 'column' => 'condicion', 'needs_condiciones' => true],
    ];

    public static function alias(string $key): string
    {
        return str_replace('.', '_', $key);
    }

    /** @return list<string> */
    public static function allowedKeys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * @param  list<string>  $requested
     * @return list<string> orden conservado, sin duplicados, solo permitidos
     */
    public static function normalizeSelection(array $requested): array
    {
        $allowed = array_flip(self::allowedKeys());
        $out = [];
        foreach ($requested as $k) {
            $k = trim((string) $k);
            if ($k !== '' && isset($allowed[$k]) && ! in_array($k, $out, true)) {
                $out[] = $k;
            }
        }

        return $out !== [] ? $out : self::DEFAULT_KEYS;
    }

    /**
     * @param  list<string>  $keys
     * @return list<array{key: string, label: string, alias: string}>
     */
    public static function columnsForPdf(array $keys): array
    {
        $cols = [];
        foreach ($keys as $key) {
            if (! isset(self::DEFINITIONS[$key])) {
                continue;
            }
            $def = self::DEFINITIONS[$key];
            $cols[] = [
                'key' => $key,
                'label' => $def['label'],
                'alias' => self::alias($key),
            ];
        }

        return $cols;
    }

    /**
     * @param  list<string>  $keys
     * @return list<string> expresiones para select()
     */
    public static function selectExpressions(array $keys): array
    {
        $expr = [];
        foreach ($keys as $key) {
            if (! isset(self::DEFINITIONS[$key])) {
                continue;
            }
            $def = self::DEFINITIONS[$key];
            $alias = self::alias($key);
            $expr[] = $def['table'].'.'.$def['column'].' as '.$alias;
        }

        return $expr;
    }

    public static function needsCondicionesJoin(array $keys): bool
    {
        foreach ($keys as $key) {
            if (! isset(self::DEFINITIONS[$key])) {
                continue;
            }
            if ((self::DEFINITIONS[$key]['needs_condiciones'] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grupos de campos para la UI del listado PDF.
     *
     * @param  list<string>|null  $soloColumnasLegajosVisibles nombres de columna física en `legajos` (p. ej. `apellido`);
     *                             null = no filtrar por visibilidad (tabla de parametrización vacía o inexistente).
     * @return array<string, list<array{key: string, label: string}>>
     */
    public static function groupedForUi(?array $soloColumnasLegajosVisibles = null): array
    {
        $visibles = null;
        if ($soloColumnasLegajosVisibles !== null) {
            $visibles = array_flip($soloColumnasLegajosVisibles);
        }

        $groups = [];
        foreach (self::DEFINITIONS as $key => $def) {
            if ($def['table'] === 'legajos' && $visibles !== null) {
                $col = $def['column'];
                if (! isset($visibles[$col])) {
                    continue;
                }
            }
            $g = $def['group'];
            if (! isset($groups[$g])) {
                $groups[$g] = [];
            }
            $groups[$g][] = ['key' => $key, 'label' => $def['label']];
        }

        return $groups;
    }
}
