<?php

namespace App\Support\ManualSistema;

/**
 * Manual de usuario — contenido orientado a operadores del sistema (secretaría, preceptoría, docentes).
 */
final class ManualSistemaCatalog
{
    public static function meta(): array
    {
        return [
            'titulo'    => 'Manual de usuario',
            'subtitulo' => 'Guía de uso del sistema de gestión escolar',
            'version'   => '2.0',
            'generado'  => now()->format('d/m/Y'),
        ];
    }

    public static function introduccion(): array
    {
        return [
            'resumen' => 'Este documento explica cómo usar cada pantalla del sistema: qué hace, dónde encontrarla en el menú y qué pasos seguir para completar las tareas habituales de secretaría, preceptoría y docentes.',
            'antes_de_empezar' => [
                'Verifique en la parte superior del menú lateral que el nivel (inicial, primario, secundario) y el año lectivo sean los correctos. Si necesita cambiar de ciclo, use el selector de contexto junto al logo.',
                'Solo verá en el menú las opciones que su usuario tiene habilitadas. Si falta un módulo, solicite al administrador que revise sus permisos.',
                'Los datos que carga (alumnos, notas, inasistencias) quedan asociados al año lectivo activo en ese momento.',
            ],
            'portales' => [
                [
                    'titulo' => 'Portal de gestión',
                    'texto'  => 'Para personal de la escuela. Ingreso en /loginUsuario con DNI y contraseña. Debe elegir nivel y año lectivo al iniciar sesión.',
                ],
                [
                    'titulo' => 'Portal de familias y estudiantes',
                    'texto'  => 'Ingreso en /loginEstudiante con el DNI del alumno. No elige año: el sistema usa el ciclo configurado para consulta de notas. Desde ahí ve calificaciones, inasistencias y comunicados.',
                ],
            ],
            'consejos_generales' => [
                'Use el panel de inicio (Dashboard) como acceso rápido a los módulos más usados.',
                'En formularios largos, complete solapa por solapa y guarde antes de salir.',
                'Los listados en PDF suelen abrirse en una pestaña nueva del navegador: revise que no esté bloqueada la ventana emergente.',
                'Si un dato no aparece, confirme primero el año lectivo y el nivel activos en el menú lateral.',
            ],
        ];
    }

    /**
     * @param  list<string>  $pasos
     * @param  list<string>  $consejos
     * @return array{nombre: string, menu: string, objetivo: string, pasos: list<string>, consejos: list<string>, permiso?: string}
     */
    private static function mod(
        string $nombre,
        string $menu,
        string $objetivo,
        array $pasos,
        array $consejos = [],
        ?string $permiso = null,
    ): array {
        $m = [
            'nombre'   => $nombre,
            'menu'     => $menu,
            'objetivo' => $objetivo,
            'pasos'    => $pasos,
            'consejos' => $consejos,
        ];
        if ($permiso !== null) {
            $m['permiso'] = $permiso;
        }

        return $m;
    }

    /**
     * @return list<array{seccion: string, descripcion: string, modulos: list<array{nombre: string, menu: string, objetivo: string, pasos: list<string>, consejos: list<string>, permiso?: string}>}>
     */
    public static function secciones(): array
    {
        return [
            [
                'seccion'     => '1. Acceso e inicio',
                'descripcion' => 'Cómo ingresar al sistema y orientarse en el panel principal.',
                'modulos'     => [
                    self::mod(
                        'Ingreso al portal de gestión',
                        'Pantalla de login (/loginUsuario)',
                        'Permite a docentes y personal administrativo acceder al sistema con su usuario institucional.',
                        [
                            'Abra la dirección de login que le proporcionó la escuela.',
                            'Ingrese su DNI (sin puntos) en el campo usuario.',
                            'Ingrese su contraseña.',
                            'Seleccione el nivel educativo con el que va a trabajar (por ejemplo Secundario).',
                            'Seleccione el año lectivo (aparece el más reciente primero).',
                            'Pulse Ingresar. Si los datos son correctos, llegará al panel de inicio.',
                        ],
                        [
                            'Si olvidó la contraseña, solicite el blanqueo a secretaría o administración.',
                            'Si el año que necesita no aparece, debe darse de alta en Términos lectivos (ver sección Parametrización).',
                        ],
                    ),
                    self::mod(
                        'Panel de inicio (Dashboard)',
                        'Menú lateral → clic en el logo o enlace al inicio',
                        'Muestra un resumen de su sesión y accesos directos a los módulos que su perfil puede usar.',
                        [
                            'Al entrar, revise en la cabecera el nombre del colegio, su usuario, el nivel y el año lectivo activos.',
                            'Use las tarjetas de «Accesos principales» para ir directo al módulo deseado.',
                            'Si tiene permiso de comunicaciones, verá un resumen de mensajes no leídos y envíos pendientes de lectura.',
                        ],
                        [
                            'Si no ve ningún acceso, su usuario no tiene permisos asignados: contacte al administrador.',
                        ],
                    ),
                    self::mod(
                        'Cambio de nivel o año lectivo',
                        'Menú lateral → selector debajo del logo (solo escritorio)',
                        'Permite trabajar con otro nivel o ciclo lectivo sin cerrar sesión.',
                        [
                            'Localice el selector de contexto en la cabecera del menú lateral.',
                            'Elija el nivel y/o el año lectivo deseado.',
                            'Confirme el cambio. El sistema recargará el contexto y filtrará cursos y alumnos según la nueva selección.',
                        ],
                        [
                            'Todo lo que cargue después del cambio quedará asociado al nuevo año lectivo.',
                            'No mezcle datos de dos años: termine o guarde su trabajo antes de cambiar de ciclo.',
                        ],
                    ),
                    self::mod(
                        'Permisos de usuarios',
                        'Menú lateral → Configuración → Permisos de usuarios',
                        'Asigna qué funciones puede usar cada persona del personal (qué ítems del menú verá).',
                        [
                            'Entre al listado de usuarios de gestión.',
                            'Busque el usuario por apellido o DNI.',
                            'Abra su ficha de permisos.',
                            'Active o desactive cada permiso según el rol (secretaría, preceptor, docente, etc.).',
                            'Guarde los cambios. El usuario deberá volver a ingresar o refrescar para ver el menú actualizado.',
                        ],
                        [
                            'Reserve el permiso de administración solo para personal de confianza.',
                            'Si un docente solo debe ver comunicaciones, no le habilite permisos de legajos ni calificaciones.',
                        ],
                        'Permiso de administración (posición 0)',
                    ),
                ],
            ],
            [
                'seccion'     => '2. Parametrización institucional',
                'descripcion' => 'Configuración que suele realizar secretaría o dirección al inicio del año o cuando cambian normas internas.',
                'modulos'     => [
                    self::mod(
                        'Términos lectivos',
                        'Configuración → Términos Lectivos',
                        'Da de alta los años escolares (ciclos lectivos) en los que se trabajará.',
                        [
                            'Ingrese al ABM de términos lectivos.',
                            'Pulse Nuevo o Agregar según la pantalla.',
                            'Complete el año (por ejemplo 2026) y los datos que solicite el formulario.',
                            'Guarde. El ciclo quedará disponible en todos los selectores del sistema.',
                        ],
                        [
                            'Cree el ciclo nuevo antes de matricular alumnos o cargar calificaciones de ese año.',
                            'Los selectores siempre muestran el año más reciente primero.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Niveles educativos',
                        'Configuración → Niveles',
                        'Define los niveles del establecimiento (inicial, primario, secundario, etc.).',
                        [
                            'Revise los niveles existentes.',
                            'Si falta alguno, créelo con el nombre institucional acordado.',
                            'Guarde cada registro.',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Parámetros del sistema',
                        'Configuración → Parámetros del sistema',
                        'Datos generales del colegio: nombre, logo, año de consulta para familias en autogestión, etc.',
                        [
                            'Abra el formulario de parámetros.',
                            'Actualice nombre institucional, logo u opciones que muestre la pantalla.',
                            'Verifique el año lectivo visible para familias en el portal de estudiantes.',
                            'Guarde los cambios.',
                        ],
                        [
                            'El logo configurado aparece en login, dashboard e informes PDF.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Campos y solapas del legajo del estudiante',
                        'Configuración → Campos activos / Solapas del Legajo',
                        'Define qué datos se piden en el legajo del alumno y cómo se organizan en pestañas.',
                        [
                            'En Campos activos: marque qué campos están visibles y si salen en listados PDF.',
                            'En Solapas del Legajo: agrupe campos en pestañas (datos personales, familia, etc.).',
                            'Guarde cada cambio.',
                            'Pruebe abriendo un legajo de prueba para verificar que la pantalla quedó como espera.',
                        ],
                        [
                            'Haga estos cambios antes del pico de inscripciones para no interrumpir la carga.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Campos y solapas del legajo del docente',
                        'Configuración → Campos activos (Legajo del docente) / Solapas del Legajo del docente',
                        'Igual que el legajo de alumnos, pero para el personal docente.',
                        [
                            'Configure campos visibles y orden en solapas.',
                            'Guarde y verifique con un legajo docente de ejemplo.',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Canales de comunicación',
                        'Configuración → Configuración de Canales',
                        'Define por qué medios la escuela puede contactar a las familias (correo, etc.).',
                        [
                            'Revise los canales habilitados.',
                            'Active o configure los medios que usará la institución.',
                            'Guarde antes de enviar el primer comunicado masivo.',
                        ],
                        [
                            'Sin canales configurados, los envíos a familias pueden fallar o quedar incompletos.',
                        ],
                        'Permiso de canales (53)',
                    ),
                    self::mod(
                        'Notificaciones push (personal de gestión)',
                        'Configuración → Notificaciones Push',
                        'Permite recibir alertas en el navegador cuando hay novedades (por ejemplo comunicados).',
                        [
                            'Entre a la pantalla de suscripción.',
                            'Acepte el permiso cuando el navegador lo solicite.',
                            'Mantenga la pestaña o el dispositivo con notificaciones permitidas.',
                        ],
                        [
                            'Use un navegador actualizado (Chrome, Edge, Firefox).',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                ],
            ],
            [
                'seccion'     => '3. Estructura curricular',
                'descripcion' => 'Arma el «esqueleto» académico: planes, cursos del año y materias, antes de matricular y calificar.',
                'modulos'     => [
                    self::mod(
                        'Planes de estudio',
                        'Configuración → Gestión de planes → Planes de estudio',
                        'Crea los planes modelo (referencia curricular) del establecimiento.',
                        [
                            'Liste los planes existentes o cree uno nuevo.',
                            'Asigne nombre y datos del plan según el formulario.',
                            'Guarde. El plan servirá para armar cursos modelo (curplan).',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Cursos y materias del plan (curplan)',
                        'Configuración → Gestión de planes → Cursos y materias del plan',
                        'Relaciona cada plan con sus cursos y materias teóricas (modelo).',
                        [
                            'Seleccione o cree un registro de curplan.',
                            'Asocie el plan, el curso modelo y las materias que correspondan.',
                            'Guarde cada combinación.',
                        ],
                        [
                            'Esto es el «molde»; los cursos reales del año se cargan en Cursos / grados / salas.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Cursos, grados y salas (del año lectivo)',
                        'Configuración → Cursos / grados / salas',
                        'Crea las divisiones reales del año en curso (1° A, 2° B, etc.).',
                        [
                            'Verifique que el año lectivo del menú lateral sea el correcto.',
                            'Alta de curso: complete curso, división, turno y plan asociado si aplica.',
                            'Guarde. El curso aparecerá en matrícula, listados y calificaciones.',
                        ],
                        [
                            'Sin cursos del año no podrá matricular ni imprimir listados.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Asignaturas del año',
                        'Configuración → Gestión de asignaturas del año',
                        'Asigna qué materias cursa cada división en el ciclo activo.',
                        [
                            'Elija el curso del año.',
                            'Agregue o quite materias según la currícula.',
                            'Guarde. Estas materias alimentan la carga de calificaciones y horarios.',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Docentes por materia',
                        'Menú Docentes → Docentes por materia',
                        'Indica qué docente dicta cada materia en cada curso (vinculación ppc).',
                        [
                            'Filtre por curso o materia si la pantalla lo permite.',
                            'Asigne el docente responsable de cada materia-curso.',
                            'Guarde cada asignación.',
                        ],
                        [
                            'Necesario para horarios, planillas con firma docente y algunos listados.',
                        ],
                        'Permiso de parametrización (1)',
                    ),
                ],
            ],
            [
                'seccion'     => '4. Legajos y matrícula',
                'descripcion' => 'Alta y mantenimiento de estudiantes y personal.',
                'modulos'     => [
                    self::mod(
                        'Legajos de estudiantes',
                        'Estudiantes → Legajos de estudiantes',
                        'Registro maestro de cada alumno: datos personales, familia y matrícula en cursos.',
                        [
                            'Para buscar: escriba apellido, nombre o DNI en el buscador.',
                            'Filtros «Solo con matrícula» / «Solo mi nivel» acotan el listado según necesite.',
                            'Nuevo legajo: pulse Nuevo, complete las solapas (datos obligatorios marcados), guarde en cada pestaña.',
                            'Matrícula: en el legajo, indique el curso del año lectivo activo y la condición (regular, egresado, etc.).',
                            'Edición: abra el legajo desde la lista, modifique y guarde.',
                        ],
                        [
                            'No elimine un legajo si tiene matrícula, calificaciones o familiares vinculados: el sistema lo impedirá.',
                            'El DNI del legajo es el usuario del portal de familias.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Legajos de docentes',
                        'Menú Docentes → Legajos de docentes',
                        'Alta y edición del personal docente con campos parametrizables.',
                        [
                            'Busque o cree un legajo docente.',
                            'Complete las solapas configuradas para docentes.',
                            'Guarde. El DNI del legajo suele ser el usuario de login de gestión.',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                ],
            ],
            [
                'seccion'     => '5. Listados e informes de padrón',
                'descripcion' => 'Impresión y exportación de datos de alumnos para preceptoría y secretaría.',
                'modulos'     => [
                    self::mod(
                        'Listado por curso (PDF)',
                        'Estudiantes → Listado por curso',
                        'Genera un PDF con los alumnos de uno o más cursos, eligiendo qué columnas mostrar.',
                        [
                            'Seleccione en el panel izquierdo los cursos deseados y páselos a la derecha (flechas o botones de transferencia).',
                            'Elija la condición de matrícula: regulares, todos, egresados, etc.',
                            'Marque los campos/columnas que deben figurar en el PDF (DNI, domicilio, etc.).',
                            'Pulse Generar PDF o equivalente. Se abrirá o descargará el archivo.',
                        ],
                        [
                            'Las columnas disponibles dependen de lo configurado en Campos activos del legajo.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Libro de matrícula',
                        'Estudiantes → Libro de matrícula',
                        'Listado institucional amplio, con columnas configurables, en PDF multipágina.',
                        [
                            'Configure filtros y columnas en pantalla.',
                            'Revise la vista previa o selección de campos.',
                            'Genere el PDF del libro de matrícula.',
                        ],
                        [
                            'Útil para auditorías o envío a supervisión.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Exportar estudiantes a Excel',
                        'Desde listados o enlace de exportación según pantalla',
                        'Descarga una planilla Excel con datos de alumnos según los filtros aplicados.',
                        [
                            'Aplique los mismos filtros de curso y condición que usaría para un listado.',
                            'Pulse Exportar a Excel.',
                            'Abra el archivo descargado en Excel o LibreOffice.',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '6. Calificaciones (secundario)',
                'descripcion' => 'Carga, consulta e impresión de notas del nivel secundario.',
                'modulos'     => [
                    self::mod(
                        'Importar calificaciones desde CIDI (GE)',
                        'Estudiantes → menú Calificaciones → Descargar calificaciones desde CIDI',
                        'Trae notas desde la plataforma provincial/archivo GE hacia el sistema.',
                        [
                            'Seleccione curso y criterios que indique la pantalla.',
                            'Suba o confirme el archivo/exportación según el asistente.',
                            'Revise el resumen de importación antes de confirmar.',
                            'Verifique en Carga de calificaciones que los datos llegaron correctamente.',
                        ],
                        [
                            'Haga respaldo o prueba con un curso piloto antes de importar masivamente.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Carga de calificaciones',
                        'Estudiantes → Calificaciones → Carga de calificaciones',
                        'Ingreso y edición de notas por curso y materia (evaluaciones, JIS, promedio).',
                        [
                            'Elija el curso en el desplegable.',
                            'Elija la materia. Se listarán los alumnos con sus columnas de instancias (ic01 a ic28 según configuración).',
                            'Haga clic en la celda de la nota, escriba el valor permitido y salga de la celda (Tab o clic fuera): se guarda automáticamente.',
                            'El promedio anual (calif) se calcula solo al modificar módulos de evaluación, no al editar coloquios.',
                            'Marque TEA si corresponde según la columna disponible.',
                        ],
                        [
                            'Solo se aceptan notas del catálogo configurado (notaspermitidas) si la escuela lo tiene activo.',
                            'Si una nota «rebota», verifique que el valor sea válido para ese nivel.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Coloquios diciembre y febrero',
                        'Estudiantes → Calificaciones → Coloquios Dic / Feb',
                        'Carga notas de mesas de recuperación de fin de año.',
                        [
                            'Seleccione curso y materia.',
                            'Ingrese la nota de coloquio en Diciembre o Febrero según corresponda.',
                            'Al aprobar en diciembre, el sistema puede inhabilitar febrero según las reglas vigentes.',
                            'Revise la columna de calificación final resultante.',
                        ],
                        [
                            'Use los mismos valores de nota permitidos que en la carga regular.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Actas volantes de coloquio',
                        'Estudiantes → Calificaciones → Actas volantes de coloquio',
                        'Imprime acta PDF por materia con alumnos y notas de coloquio.',
                        [
                            'Elija curso, materia y período (diciembre o febrero).',
                            'Genere el PDF y revise antes de firmar o archivar.',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Planilla de calificaciones (PDF)',
                        'Estudiantes → Calificaciones → Planilla de calificaciones',
                        'PDF de una materia y un curso con todas las instancias de evaluación.',
                        [
                            'Seleccione curso y materia.',
                            'Pulse generar PDF.',
                            'Use para archivo docente, reunión de gabinete o control interno.',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Planilla resumen (PDF)',
                        'Estudiantes → Calificaciones → Planilla resumen',
                        'Un solo PDF con todas las materias de un curso.',
                        [
                            'Elija el curso.',
                            'Genere el PDF resumen.',
                            'Útil para preceptoría y cierre de año.',
                        ],
                        [
                            'El documento puede ser extenso: espere a que termine la descarga.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Consulta de calificaciones',
                        'Estudiantes → Calificaciones → Consulta de calificaciones',
                        'Busca un alumno y emite su informe individual en PDF.',
                        [
                            'Busque por apellido, nombre o DNI.',
                            'Seleccione al alumno correcto si hay homónimos.',
                            'Genere el PDF de consulta.',
                            'Entregue o archive según política de la escuela.',
                        ],
                        [
                            'Las familias pueden obtener un informe similar desde el portal de estudiantes.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '7. Boletines (secundario)',
                'descripcion' => 'Informe de progreso escolar para entrega a familias.',
                'modulos'     => [
                    self::mod(
                        'Boletines / informe de progreso',
                        'Estudiantes → Calificaciones → Boletines (secundario)',
                        'Genera el boletín oficial en PDF por alumno o en lote por curso.',
                        [
                            'Seleccione el curso.',
                            'Marque uno o varios alumnos (use «Seleccionar todos» si corresponde).',
                            'Para un alumno: genere PDF individual.',
                            'Para varios: use la opción de lote; el sistema armará un PDF con todos los seleccionados.',
                            'Revise muestras antes de imprimir masivamente.',
                        ],
                        [
                            'Asegúrese de que las calificaciones del ciclo estén cerradas o revisadas antes del envío a familias.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '8. Exámenes y materias adeudadas',
                'descripcion' => 'Previas, inscripción a mesas y registro de resultados.',
                'modulos'     => [
                    self::mod(
                        'Listado de materias adeudadas',
                        'Menú Exámenes → Listado de materias adeudadas',
                        'Consulta quién adeuda materias e imprime listados PDF.',
                        [
                            'Aplique filtros de curso, materia o estado si la pantalla los ofrece.',
                            'Revise el listado en pantalla.',
                            'Genere el PDF para archivo o convocatoria.',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Gestión de materias adeudadas',
                        'Menú Exámenes → Gestión de materias adeudadas',
                        'Operación completa por alumno: adeudos, inscripción a mesa, notas e historial.',
                        [
                            'Busque al alumno por nombre o DNI.',
                            'Carga manual: registre las materias adeudadas si no figuran.',
                            'Inscribir a examen: indique materia y mesa según el asistente.',
                            'Carga de notas: ingrese el resultado del examen.',
                            'Historial: consulte mesas anteriores del alumno.',
                        ],
                        [
                            'Recorra el flujo en orden: adeudo → inscripción → nota, para mantener trazabilidad.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Borrar inscripciones a examen',
                        'Menú Exámenes → Borrar inscripciones',
                        'Anula inscripciones cargadas por error (uso restringido).',
                        [
                            'Busque la inscripción errónea con los filtros disponibles.',
                            'Confirme el borrado solo si está seguro: la acción puede ser irreversible.',
                        ],
                        [
                            'Documente internamente por qué se eliminó una inscripción.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '9. Horarios',
                'descripcion' => 'Armado e impresión de grillas horarias.',
                'modulos'     => [
                    self::mod(
                        'Configuración de horarios',
                        'Horarios → Configuración de horarios',
                        'Define turnos, días hábiles y franjas horarias del establecimiento.',
                        [
                            'Configure turnos (mañana, tarde, etc.).',
                            'Defina la cantidad de módulos y horarios de inicio/fin.',
                            'Guarde antes de cargar horas por curso.',
                        ],
                        [],
                        'Permiso de parametrización (1)',
                    ),
                    self::mod(
                        'Carga de horarios',
                        'Horarios → Carga de horarios',
                        'Asigna materia y docente a cada celda de la grilla por curso.',
                        [
                            'Elija curso (y turno si aplica).',
                            'Haga clic en cada celda del cuadro y asigne materia/docente.',
                            'Guarde los cambios de la fila o de la pantalla según indique el botón.',
                            'Revise conflictos visibles (mismo docente en dos lugares, etc.).',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Impresión de horarios',
                        'Horarios → Impresión de horarios',
                        'PDF de la grilla por curso o por docente.',
                        [
                            'Seleccione si desea horario por curso o por docente.',
                            'Elija el curso o el docente en el listado.',
                            'Genere y descargue el PDF.',
                            'Distribuya o publique según necesidad (puerta de aula, cartelera).',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '10. Seguimiento pedagógico',
                'descripcion' => 'Disciplina, asistencia y partes diarios de preceptoría.',
                'modulos'     => [
                    self::mod(
                        'Seguimiento disciplinario',
                        'Estudiantes → Seguimiento disciplinario',
                        'Registra sanciones y genera comunicados o informes de antecedentes.',
                        [
                            'Liste sanciones existentes o cree una nueva.',
                            'Complete fecha, alumno, tipo de falta y sanción según el formulario.',
                            'Guarde. Puede imprimir comunicado PDF para la familia.',
                            'Antecedentes: desde el alumno, abra el historial disciplinario e imprima PDF si lo necesita.',
                        ],
                        [
                            'Respete la confidencialidad: solo personal autorizado debe acceder.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Inasistencias',
                        'Estudiantes → Inasistencias',
                        'Registra faltas justificadas o injustificadas por alumno.',
                        [
                            'Busque al alumno o navegue por curso según la pantalla.',
                            'Nuevo registro: indique fecha, tipo de falta y observación.',
                            'Guarde. Puede editar registros recientes si hubo error.',
                            'Informe PDF: desde el alumno, genere el informe de inasistencias para entregar o archivar.',
                        ],
                        [
                            'Las familias ven un informe similar en su portal.',
                        ],
                        'Permiso de estudiantes (2)',
                    ),
                    self::mod(
                        'Partes diarios',
                        'Estudiantes → Partes diarios',
                        'Novedades del día registradas por preceptoría; impresión PDF.',
                        [
                            'Seleccione fecha y curso/preceptor según el formulario.',
                            'Cargue las novedades del día (llegadas tarde, conducta, etc.).',
                            'Guarde y genere el PDF del parte para dirección o archivo.',
                        ],
                        [],
                        'Permiso de estudiantes (2)',
                    ),
                ],
            ],
            [
                'seccion'     => '11. Comunicaciones escuela–familia',
                'descripcion' => 'Mensajería institucional con control de lectura.',
                'modulos'     => [
                    self::mod(
                        'Bandeja de comunicados',
                        'Cuaderno de comunicados → Bandeja (o menú Comunicaciones)',
                        'Lee y responde mensajes entre la escuela y las familias.',
                        [
                            'Abra la bandeja: verá hilos recibidos y enviados del año lectivo.',
                            'Filtre por no leídos si busca pendientes.',
                            'Entre a un hilo para leer el historial y responder si corresponde.',
                            'Los mensajes no leídos se destacan en el dashboard.',
                        ],
                        [
                            'Revise periódicamente «sin lectura del destinatario» para hacer seguimiento.',
                        ],
                        'Permiso de bandeja (51)',
                    ),
                    self::mod(
                        'Nuevo comunicado',
                        'Cuaderno → Nuevo comunicado',
                        'Envía un mensaje a familias, cursos completos o docentes.',
                        [
                            'Elija destinatarios: alumnos puntuales, uno o más cursos, o todo el colegio.',
                            'Redacte asunto y cuerpo del mensaje.',
                            'Indique si la familia puede responder.',
                            'Revise destinatarios en el resumen.',
                            'Envíe. Consulte después el informe de envío para ver quién leyó.',
                        ],
                        [
                            'Evite envíos masivos de prueba en horario de clase.',
                            'Verifique ortografía: las familias reciben notificación según canal configurado.',
                        ],
                        'Permiso de envío (52)',
                    ),
                    self::mod(
                        'Revisión de comunicados',
                        'Cuaderno → Revisión de comunicados',
                        'Supervisa comunicados que requieren aprobación antes de salir.',
                        [
                            'Abra la bandeja de revisión.',
                            'Lea el borrador o envío pendiente.',
                            'Apruebe o rechace según política interna.',
                        ],
                        [],
                        'Permisos de bandeja y revisión (51 y 56)',
                    ),
                    self::mod(
                        'Informe de envío',
                        'Desde un hilo enviado → Informe de envío',
                        'Detalle de quién recibió y abrió el comunicado.',
                        [
                            'Abra el comunicado ya enviado.',
                            'Acceda al informe de envío.',
                            'Revise estado por destinatario (entregado, leído, pendiente).',
                        ],
                        [
                            'Útil para acreditar notificación a familias.',
                        ],
                        'Permiso de bandeja (51)',
                    ),
                ],
            ],
            [
                'seccion'     => '12. Portal de familias y estudiantes',
                'descripcion' => 'Uso desde el lado del alumno o la familia (autogestión).',
                'modulos'     => [
                    self::mod(
                        'Ingreso de familias',
                        'Pantalla /loginEstudiante',
                        'Acceso al portal sin elegir nivel ni año.',
                        [
                            'Ingrese DNI del alumno y contraseña entregada por la escuela.',
                            'Tras el login, verá el menú reducido del portal.',
                        ],
                        [
                            'Si no puede ingresar, verifique en secretaría que el legajo tenga DNI y contraseña activos.',
                        ],
                    ),
                    self::mod(
                        'Mis calificaciones (familia)',
                        'Portal → Calificaciones',
                        'Consulta de notas del ciclo configurado para autogestión.',
                        [
                            'Entre a Calificaciones.',
                            'Revise las materias y promedios mostrados.',
                            'Use el botón de PDF si desea descargar el informe.',
                        ],
                        [],
                    ),
                    self::mod(
                        'Informe de inasistencias (familia)',
                        'Portal → Inasistencias',
                        'Consulta de faltas registradas por la escuela.',
                        [
                            'Abra la sección de inasistencias.',
                            'Revise fechas y tipos de falta.',
                            'Descargue el PDF si necesita imprimirlo.',
                        ],
                        [],
                    ),
                    self::mod(
                        'Comunicaciones (familia)',
                        'Portal → Comunicaciones',
                        'Bandeja, respuesta a la escuela y preferencias de contacto.',
                        [
                            'Lea mensajes de la escuela en la bandeja.',
                            'Responda si el comunicado lo permite.',
                            'En Preferencias, elija cómo desea recibir avisos (correo, etc.).',
                            'Puede iniciar un nuevo mensaje hacia la institución si está habilitado.',
                        ],
                        [],
                    ),
                    self::mod(
                        'Notificaciones push (familia)',
                        'Portal → Notificaciones',
                        'Recibe alertas en el celular o navegador.',
                        [
                            'Entre a Notificaciones.',
                            'Acepte el permiso del navegador.',
                            'Mantenga activadas las notificaciones para enterarse de nuevos comunicados.',
                        ],
                        [],
                    ),
                ],
            ],
            [
                'seccion'     => '13. Manual en PDF',
                'descripcion' => 'Este documento y cómo obtenerlo de nuevo.',
                'modulos'     => [
                    self::mod(
                        'Descargar manual del sistema',
                        'Menú lateral (abajo) → Manual del sistema',
                        'Abre o descarga la versión actualizada de esta guía en PDF.',
                        [
                            'Haga clic en «Manual del sistema» al final del menú lateral.',
                            'El navegador descargará o abrirá el PDF en una pestaña nueva.',
                            'Guarde el archivo si desea consultarlo sin conexión.',
                        ],
                        [
                            'El contenido refleja los módulos habilitados en su instalación a la fecha de generación.',
                        ],
                    ),
                ],
            ],
        ];
    }
}
