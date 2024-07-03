<?php
        /*
         *  Estado: En desarrollo.
         *  Archivo: reinscripcion.class.php
         *  Fecha: 07/03/2006
         *  Autor: Nicola Strappazzon C.
         *  Descripcion: Clase que contiene toda las conexiones a la base de datos.
         */

    class Reinscripcion {
        private static $_instance;

        var $MyLayerSQL;
        var $EstudianteCI                  = 0;
        var $EscuelaCodigo;
        var $EscuelaNombre;
        var $Estado;
        var $SedeCodigo			   = 0;
        var $CantidadProbatorio		   = 0;
        var $SedeNombre			   = '';
        var $UltimoPeriodoInscrito         = 0;
        var $UltimoPeriodoCursado          = 0;
        var $IndiceAcademicoPeriodo        = 0;
        var $IndiceAcademicoAcumulado      = 0;
        var $aMaterias;
        var $UnidadesCreditoAdicionales    = 0;
        var $CantidadProbatorioReglamento  = 0;
        var $EstadoCambioEscuelaReglamento = 0;
        var $EstadoRetiroReglamento        = 0;
        var $CambioEscuela                 = 0;

        public function __construct($EstudianteCI)
        {
            include_once($_SERVER['DOCUMENT_ROOT'] . "/MiUNEAcademico/libs/php/connection.php");
            include_once($_SERVER['DOCUMENT_ROOT'] . "/MiUNEAcademico/libs/php/functions.php");

                        /*
                         *  Inicializamos las variables de la clase.
                         */

            $this->CantidadMateriasSeleccionadas = 0;

                        /*
                         *  Realizamos la instancia de cada una de las clases necesaras para
                         *  nuestra clase.
                         */

            $this->MyLayerSQL   = PostgreSQL::GetInstance();
            $this->EstudianteCI = $EstudianteCI;

                        /*
                         *  Llamamos en orden los metodos internos que recogen toda la informacion
                         *  relevante del estudiante que se inscribe.
                         */
            $this->Escuela();
            $this->UltimoPeriodoInscrito();
            $this->UltimoPeriodoCursado();
            $this->IndiceAcademicoAcumulado();
            $this->IndiceAcademicoPeriodo();
            $this->Estado();
            $this->CantidadProbatorio();
            $this->UnidadesCreditoAdicionales();
            $this->CantidadProbatorioReglamento();
            $this->EstadoCambioEscuelaReglamento();
            $this->EstadoRetiroReglamento();
            $this->CambioEscuela();
        }

        public static function GetInstance($EstudianteCI)
        {
            if (!isset(self::$_instance)) {
                self::$_instance = new Reinscripcion($EstudianteCI);
            }
            
            return self::$_iPnstance;
        }

        private function UnidadesCreditoAdicionales()
        {
            if(!isset($this->EstudianteCI))
            return;

            if(!isset($this->UltimoPeriodoInscrito))
            return;

            $SQLQuery = "SELECT ucadicionales
                                                 FROM tbl_inscripciones i
                                                 INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                 WHERE ug.fk_usuario = $this->EstudianteCI AND i.fk_periodo = $this->UltimoPeriodoInscrito;";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);

            if(isset($Result))
            $this->UnidadesCreditoAdicionales = $Result[0][0];
        }

        // Retorna en que escuela se encuentra el estudiante.
        private function Escuela()
        {
            $SQLQuery = "SELECT * FROM fn_xrxx_estudiante_escuela($this->EstudianteCI) AS (pk_atributo int8, codigo int2, escuela VARCHAR(45), fk_estructura int2, nombre VARCHAR(45));";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->EscuelaCodigo = $Result[0][0];
            $this->EscuelaNombre = $Result[0][2];
            $this->SedeCodigo    = $Result[0][3];
            $this->SedeNombre    = $Result[0][4];
        }
                
        // Retorna la cantidad de probatorios que tiene un estudiante determinado.
        private function CantidadProbatorio() {
            $SQLQuery = "SELECT cantidad_probatorio_voluntario
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                         JOIN tbl_estudiantesestados ee ON ee.fk_usuariogrupo = ug.pk_usuariogrupo
                         WHERE u.pk_usuario = {$this->EstudianteCI};";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->CantidadProbatorio = $Result[0][0];
        }

        private function CantidadProbatorioReglamento() {
            $SQLQuery = "SELECT cantidad_probatorio_reglamento
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                         JOIN tbl_estudiantesestados ee ON ee.fk_usuariogrupo = ug.pk_usuariogrupo
                         WHERE u.pk_usuario = {$this->EstudianteCI};";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->CantidadProbatorioReglamento = $Result[0][0];
        }

        private function EstadoCambioEscuelaReglamento() {
            $SQLQuery = "SELECT cambio_escuela_reglamento
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                         JOIN tbl_estudiantesestados ee ON ee.fk_usuariogrupo = ug.pk_usuariogrupo
                         WHERE u.pk_usuario = {$this->EstudianteCI};";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->EstadoCambioEscuelaReglamento = $Result[0][0];
        }

        private function EstadoRetiroReglamento() {
            $SQLQuery = "SELECT retiro_reglamento
                         FROM tbl_usuarios u
                         JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                         JOIN tbl_estudiantesestados ee ON ee.fk_usuariogrupo = ug.pk_usuariogrupo
                         WHERE u.pk_usuario = {$this->EstudianteCI};";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->EstadoRetiroReglamento = $Result[0][0];
        }

        // Retorna el ultimo periodo academico cursado por el estudiante.
        private function UltimoPeriodoCursado()
        {
            $SQLQuery = "SELECT fn_xrxx_reinscripcion_upc($this->EstudianteCI);";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->UltimoPeriodoCursado = $Result[0][0];
        }

        // Retorna el ultimo periodo academico inscrito por el estudiante
        private function UltimoPeriodoInscrito()
        {
            $SQLQuery = "SELECT fn_xrxx_reinscripcion_upi($this->EstudianteCI);";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->UltimoPeriodoInscrito = $Result[0][0];
        }

        // Retorna el Indice Academico Acumulado.
        private function IndiceAcademicoAcumulado()
        {
            $SQLQuery = "SELECT fn_xrxx_estudiante_iia($this->EstudianteCI);";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->IndiceAcademicoAcumulado = substr($Result[0][0], 0, 5);
        }

        // Retorna el Indice academico de un periodo determinado.
        private function IndiceAcademicoPeriodo()
        {
            if(isset($this->UltimoPeriodoCursado))
            {
                $SQLQuery = "SELECT fn_xrxx_estudiante_iap($this->EstudianteCI, $this->UltimoPeriodoCursado);";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                $this->IndiceAcademicoPeriodo = substr($Result[0][0], 0, 5);
            }
        }

                /*
                 * 	Indica cual es el estado actual del estudiante, si es:
                 * 	- Cuadro de honor.
                 * 	- Probatorio.
                 * 	- Regular.
                 */
        private function Estado()
        {
            if($this->IndiceAcademicoPeriodo >= 16) {
                $this->Estado = "<span class='TextSingleGreen'>Cuadro de honor.</span>";
            }
            elseif($this->IndiceAcademicoPeriodo > 0 && $this->IndiceAcademicoPeriodo < 11) {
                $this->Estado = "<span class='TextSingleRed'>Probatorio.</span>";
            }
            elseif($this->IndiceAcademicoPeriodo == 0) {
                $this->Estado = "Nuevo Ingreso.";
            }else {
                $this->Estado = "Regular.";
            }
        }

                /*
                 *  Analiza si el estudiante esta en Bloque o no.
                 */
        function BloqueUbicado()
        {
            $SQLQuery = "SELECT fn_xrxx_estudiante_bloque($this->EstudianteCI, $this->EscuelaCodigo);";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result[0][0];
        }

        // Retorna el limite de unidades de creditos de un semestre determinado.
        function UnidadesCreditoPorSemestre($SemestreUbicacionNuevo)
        {
            if(!is_numeric($SemestreUbicacionNuevo))
                return;

            $SQLQuery = "SELECT * FROM fn_xrxx_estudiante_calcular_ucps($this->EstudianteCI, $this->EscuelaCodigo, $SemestreUbicacionNuevo, $this->SedeCodigo) AS (semestre SMALLINT, uc INT8);";

            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function CursoSimultaneo()
        {
            $SQLQuery = "SELECT * FROM fn_xrxx_estudiante_curso_simultaneo($this->EstudianteCI);";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        // Retorna cuales son las materias que el estudiante puede inscribir.
        function FMateriasAInscribir($ci,$escuela)
        {
            if(isset($this->EscuelaCodigo))
            {
                $SQLQuery = "SELECT * FROM fn_xrxx_reinscripcion_lmcp($ci, $escuela)
                                                         AS (pk_asignatura INTEGER,
                                                         codigopropietario VARCHAR(8),
                                                         materia VARCHAR(255),
                                                         unidadcredito SMALLINT,
                                                         semestre TEXT[],
                                                                 prelacion TEXT[],
                                                                 semestrerequisito TEXT,
                                                                 uc TEXT,
                                                                 enable_uno boolean,
                                                                 enable_dos boolean,
                                                                 enable_tres boolean,
                                                                 enable_cuatro boolean,
                                                                 turno TEXT[],
                                                                 seccion TEXT[],
                                 inscripcion boolean,
                                 calificacion smallint,
                                 estado smallint,
                                 simultaneo boolean,
                                 simultaneo_candidata boolean);";

                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function MateriasAInscribirCasoEspecial() {
            if(isset($this->EscuelaCodigo)){
                $SQLQuery = "SELECT * FROM fn_xrxx_reinscripcion_lmsp($this->EstudianteCI, $this->EscuelaCodigo) AS (fk_asignatura INT2, codigopropietario VARCHAR(8), Nombre VARCHAR(45), unidadcredito INT2, semestre INT2);";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function MateriasAInscribirPasantias() {
            if(isset($this->EscuelaCodigo)) {
                $SQLQuery = "SELECT * FROM fn_xrxx_reinscripcion_lp($this->EstudianteCI, $this->EscuelaCodigo) AS(pk_asignatura INTEGER, codigopropietario VARCHAR(8), materia VARCHAR(255), unidadcredito SMALLINT, semestre SMALLINT, aprobada BOOLEAN, inscrita BOOLEAN, prelada BOOLEAN);";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function MateriasAInscritasCursoSimultaneo($iAsignatura)
        {
            if(isset($this->EstudianteCI))
            {
                $SQLQuery = "SELECT DISTINCT ag2.codigopropietario, a1.valor as nombre, ag2.unidadcredito, a2.id AS semestre, a3.valor, p.fk_asignatura
                                                        FROM tbl_asignaturas ag
                                                        INNER JOIN tbl_prelaciones p ON p.fk_asignaturaprelada = ag.pk_asignatura
                                                        INNER JOIN tbl_asignaturas ag2 ON ag2.pk_asignatura = p.fk_asignatura
                                                        INNER JOIN tbl_asignaciones ac ON ac.fk_asignatura = ag2.pk_asignatura
                                                        INNER JOIN tbl_atributos a1 ON a1.pk_atributo = ag2.fk_materia
                                                        INNER JOIN tbl_atributos a2 ON a2.pk_atributo = ag2.fk_semestre
                                                        INNER JOIN tbl_estructuras e1 ON e1.pk_estructura = ac.fk_estructura
                                                        INNER JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
                                                        INNER JOIN tbl_atributos a3 ON a3.pk_atributo = ac.fk_seccion
                                                        WHERE ag.codigopropietario = '{$iAsignatura}' AND
                                                        ac.fk_periodo = {$this->UltimoPeriodoInscrito} AND
                                                        e2.fk_estructura = {$this->SedeCodigo} AND
                                                        p.fk_asignatura IN (SELECT ra.fk_asignatura
                                                                                        FROM tbl_recordsacademicos ra
                                                                                        INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
                                                                                        INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                                                        WHERE i.fk_periodo = {$this->UltimoPeriodoInscrito} AND
                                                                                                           ug.fk_usuario = {$this->EstudianteCI} AND
                                                                                                           ra.fk_atributo = 864);";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function MateriasPreinscritas()
        {
            if(isset($this->EstudianteCI))
            {
                $SQLQuery = "SELECT codigopropietario, valor, id, unidadcredito FROM fn_xrxx_reinscripcion_lmpi({$this->EstudianteCI}) AS (codigopropietario VARCHAR(8), valor VARCHAR(255), id INT2, unidadcredito INT2);";

                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function MateriasPasantias($iSemestre)
        {
            if(isset($this->EstudianteCI) && isset($this->UltimoPeriodoInscrito) && is_numeric($iSemestre))
            {
                $SQLQuery = "SELECT ag.pk_asignatura,
                                                                ag.codigopropietario,
                                                                a1.valor,
                                                                ag.unidadcredito,
                                                                a2.id,
                                                                (SELECT a.id
                                                                FROM tbl_prelaciones p
                                                                INNER JOIN tbl_atributos a ON a.pk_atributo = p.fk_atributo
                                                                WHERE p.fk_asignatura = ag.pk_asignatura)
                                                        FROM tbl_recordsacademicos ra
                                                        INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
                                                        INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                        INNER JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
                                                        INNER JOIN tbl_atributos a1 ON a1.pk_atributo = ag.fk_materia
                                                        INNER JOIN tbl_atributos a2 ON a2.pk_atributo = ag.fk_semestre
                                                        WHERE  (ag.fk_materia = 848 OR
                                                                        ag.fk_materia = 716 OR
                                                                        ag.fk_materia = 717 OR
                                                                        ag.fk_materia = 718 OR
                                                                        ag.fk_materia = 719) AND
                                                                i.fk_periodo = {$this->UltimoPeriodoInscrito} AND
                                                                ug.fk_usuario = {$this->EstudianteCI} AND
                                                                (SELECT a.id
                                                                FROM tbl_prelaciones p
                                                                INNER JOIN tbl_atributos a ON a.pk_atributo = p.fk_atributo
                                                                WHERE p.fk_asignatura = ag.pk_asignatura) <= $iSemestre;";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result;
            }
        }

        function ConsignarReInscripcion($iMateria, $iTurno, $iSemestre, $iSeccion, $iEstado)
        {
            $SQLQuery = "SELECT fn_cxxx_reinscripcion($this->EstudianteCI, $this->UltimoPeriodoInscrito, '$iMateria', $iSemestre, $iTurno, $iSeccion, $iEstado, {$this->SedeCodigo});";

            $this->MyLayerSQL->ExecuteQuery($SQLQuery);
        }

        function ConsignarObservaciones($Observaciones)
        {
            $SQLQuery = "UPDATE tbl_inscripciones
                            SET observaciones = '{$Observaciones}'
                         WHERE fk_usuariogrupo = (SELECT pk_usuariogrupo
                                                  FROM tbl_usuariosgrupos
                                                  WHERE fk_usuario = {$this->EstudianteCI}
                                                    AND fk_periodo = {$this->UltimoPeriodoInscrito}
                                                  LIMIT 1);";
            $this->MyLayerSQL->ExecuteQuery($SQLQuery);
        }

        function ConsignarSemestre($iSemestre)
        {
            $SQLQuery = "UPDATE tbl_inscripciones
                                                 SET fk_semestre = $iSemestre
                                                 WHERE fk_usuariogrupo = (SELECT pk_usuariogrupo
                                                                          FROM tbl_usuariosgrupos
                                                                          WHERE fk_usuario = {$this->EstudianteCI}
                                                                            AND fk_periodo = {$this->UltimoPeriodoInscrito}
                                                                          LIMIT 1);";
            $this->MyLayerSQL->ExecuteQuery($SQLQuery);
        }

        function CantidadSecciones($Asignatura)
        {
            $SQLQuery = "SELECT COUNT(valor) FROM fn_xrxx_reinscripcion_secciones({$this->UltimoPeriodoInscrito}, {$this->SedeCodigo},'$Asignatura');";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result[0][0];
        }
        //
        public function CantidadDeInscritosPorMateria($iAsignatura, $iSemestre, $iSeccion, $iTurno) {
            $SQLQuery = "SELECT count(tblU.pk_usuario)
                         FROM tbl_usuarios tblU
                         JOIN tbl_usuariosgrupos tblUG ON tblU.pk_usuario = tblUG.fk_usuario
                         JOIN tbl_inscripciones tblI ON tblUG.pk_usuariogrupo = tblI.fk_usuariogrupo
                         JOIN tbl_recordsacademicos tblRA ON tblI.pk_inscripcion = tblRA.fk_inscripcion
                         JOIN tbl_asignaciones tblAS ON tblAS.pk_asignacion = tblRA.fk_asignacion
                         JOIN tbl_asignaturas tblA ON tblA.pk_asignatura = tblAS.fk_asignatura
                         JOIN tbl_pensums tblP ON tblP.pk_pensum = tblA.fk_pensum
                         JOIN vw_escuelas vwE ON vwE.pk_atributo = tblP.fk_escuela
                         WHERE tblI.fk_periodo         = {$this->UltimoPeriodoInscrito}
                            AND tblA.codigopropietario = '{$iAsignatura}'
                            AND tblAS.fk_seccion       = {$iSeccion}
                            AND tblAS.fk_semestre      = {$iSemestre}
                            AND tblAS.fk_turno         = {$iTurno}
                         GROUP BY tblA.codigopropietario
                         ORDER BY tblA.codigopropietario;";
            //var_dump($SQLQuery);die;
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result[0][0];
        
        }

        function Secciones($iTurno, $iSemestre, $sAsignatura)
        {
            $SQLQuery = "SELECT pk_atributo, valor FROM fn_xrxx_reinscripcion_secciones({$this->UltimoPeriodoInscrito}, {$this->SedeCodigo}, {$iSemestre}, {$iTurno}, '{$sAsignatura}');";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function Turnos($Semestre, $Asignatura) {
            $SQLQuery = "SELECT pk_atributo, valor FROM fn_xrxx_reinscripcion_turnos({$this->UltimoPeriodoInscrito}, {$this->SedeCodigo}, {$Semestre}, '$Asignatura');";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function Horarios($sAsignatura, $iSemestre, $iTurno, $iSeccion, $iSede)
        {
            $SQLQuery = "SELECT dia, horainicio, horafin FROM fn_xrxx_reinscripcion_horario({$this->UltimoPeriodoInscrito}, '$sAsignatura', $iSemestre, $iTurno, $iSeccion, $iSede)
                         AS (dia VARCHAR(45), horainicio TIME, horafin TIME);";

            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);

            return $Result;
        }

        function verificarCoincidenciaHorario($Materias)
        {
            $SQLQuery = "SELECT codigo, dia, horario
                                                FROM fn_xrxx_reinscripcion_validarhorarios({$this->UltimoPeriodoInscrito}, $Materias, {$this->SedeCodigo})
                                                AS (Codigo VARCHAR(8),  Dia int8, Horario int8);";

            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function eliminarReinscripcionRegular()
        {
            if(isset($this->UltimoPeriodoInscrito))
            {
                $SQLQuery = "SELECT fn_xxxd_reinscripcion($this->EstudianteCI, $this->UltimoPeriodoInscrito);";
                $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            }
        }

        function eliminarPasantia()
        {
            $SQLQuery = "SELECT fn_xxxd_pasantia($this->EstudianteCI, $this->UltimoPeriodoInscrito);";
            $this->MyLayerSQL->ExecuteQuery($SQLQuery);
        }

        function eliminarPreReinscripcion()
        {
            if(isset($this->EstudianteCI) && isset($this->UltimoPeriodoInscrito))
            {
                $SQLQuery = "DELETE FROM tbl_recordsacademicos
                                                         WHERE fk_inscripcion = (SELECT i.pk_inscripcion
                                                                                                        FROM tbl_inscripciones i
                                                                                                        INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                                                                        WHERE ug.fk_usuario = {$this->EstudianteCI} AND
                                                                                                                i.fk_periodo = {$this->UltimoPeriodoInscrito}) AND
                                                                                                                fk_atributo = 904;";
                $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            }
        }

        function estudianteNombre()
        {
            if(isset($this->EstudianteCI))
            {
                $SQLQuery = "SELECT * FROM fn_xrxx_usuario_nombreusuario({$this->EstudianteCI})";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result[0][0];
            }
        }

        function verificarPago()
        {
            if(isset($this->UltimoPeriodoInscrito))
            {
                $SQLQuery = "SELECT numeropago
                             FROM tbl_inscripciones i
                             JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                             WHERE i.fk_periodo = {$this->UltimoPeriodoInscrito} AND ug.fk_usuario = {$this->EstudianteCI};";
                $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
                return $Result[0][0];
            }
        }

        function ConsignarPasantia($iPasantia)
        {
            $SQLQuery = "SELECT fn_cxxx_pasantia({$this->EstudianteCI}, {$this->UltimoPeriodoInscrito}, '{$iPasantia}');";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function Inscrita($iMateria)
        {
            $SQLQuery = "SELECT ag.fk_semestre, ag.fk_turno, ag.fk_seccion
                                                 FROM tbl_recordsacademicos ra
                                                 INNER JOIN tbl_inscripciones   i ON i.pk_inscripcion   = ra.fk_inscripcion
                                                 INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                 INNER JOIN tbl_asignaciones   ag ON ag.pk_asignacion   = ra.fk_asignacion
                                                 INNER JOIN tbl_asignaturas    at ON at.pk_asignatura   = ag.fk_asignatura
                                                 WHERE ug.fk_usuario        = {$this->EstudianteCI}
                                                   AND ra.fk_atributo       = 864
                                                   AND at.codigopropietario = '{$iMateria}';";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        function HayMateriasInscritas($iNumeroPago)
        {
            $SQLQuery = "SELECT COUNT(ra.pk_recordacademico)
                             FROM tbl_recordsacademicos ra
                             INNER JOIN tbl_inscripciones   i ON i.pk_inscripcion   = ra.fk_inscripcion
                             INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                             INNER JOIN tbl_asignaturas    at ON at.pk_asignatura   = ra.fk_asignatura
                             WHERE ug.fk_usuario  = {$this->EstudianteCI}
                           AND i.fk_periodo   = {$this->UltimoPeriodoInscrito}
                           AND i.numeropago   = {$iNumeroPago}
                           AND ra.fk_atributo = 864;";
            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result[0][0];
        }

        function obtenerPrelaciones($iAsignatura)
        {
            $SQLQuery = "SELECT ag1.codigopropietario
                         FROM tbl_prelaciones p
                         JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = p.fk_asignatura
                         JOIN tbl_asignaturas ag2 ON ag2.pk_asignatura = p.fk_asignaturaprelada
                         WHERE ag2.codigopropietario = '{$iAsignatura}'";

            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            return $Result;
        }

        public function CambioEscuela()
        {
            $SQLQuery = "SELECT (SELECT i.fk_atributo
                        FROM tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug ON ug.fk_usuario      =  u.pk_usuario
                        JOIN tbl_inscripciones   i ON  i.fk_usuariogrupo = ug.pk_usuariogrupo
                        WHERE i.fk_periodo = {$this->UltimoPeriodoInscrito}
                          AND u.pk_usuario = {$this->EstudianteCI})
                        <>
                        (SELECT i.fk_atributo
                        FROM tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug ON ug.fk_usuario      =  u.pk_usuario
                        JOIN tbl_inscripciones   i ON  i.fk_usuariogrupo = ug.pk_usuariogrupo
                        WHERE i.fk_periodo = fn_xrxx_reinscripcion_upc({$this->EstudianteCI})
                          AND u.pk_usuario = {$this->EstudianteCI})";

            $Result = $this->MyLayerSQL->ExecuteQuery($SQLQuery);
            $this->CambioEscuela = $Result[0][0];
        }
    }
    
    
?>