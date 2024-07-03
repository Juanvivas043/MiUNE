<?php

class Models_DbTable_Inscripciontesis extends Zend_Db_Table {

    public function getPensum($ci) {
        $SQL = "SELECT p.nombre,p.pk_pensum
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_pensums p ON p.pk_pensum = i.fk_pensum
                WHERE u.pk_usuario = {$ci}
                ORDER BY i.fechahora DESC
                LIMIT 1;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function getSolvenciaAcademica($ci,$pensum) {

        if($pensum == '1997'){
            $SQL = "SELECT CASE WHEN (count(sqt.*)>0) THEN true ELSE false END as resultado
                    FROM
                    (SELECT a.pk_asignatura
                    FROM tbl_asignaturas a
                    JOIN tbl_pensums  p ON a.fk_pensum = p.pk_pensum AND p.nombre ilike ('1997') and p.fk_escuela = (SELECT fk_atributo
                    FROM tbl_inscripciones i
                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                    ORDER BY i.fk_periodo DESC LIMIT 1)
                    JOIN vw_materias  m ON m.pk_atributo  = a.fk_materia AND a.fk_materia NOT IN (1701,894,907,718/*,719,913*/)
                    JOIN vw_semestres s ON s.pk_atributo  = a.fk_semestre AND s.id BETWEEN 1 AND 9
                    EXCEPT
                    SELECT a.pk_asignatura
                    FROM tbl_inscripciones  i
                    JOIN tbl_usuariosgrupos ug ON ug .pk_usuariogrupo = i.fk_usuariogrupo AND fk_usuario = {$ci}
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion AND (ra.fk_atributo = 864 OR(ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861,864))))
                    JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_pensums p ON p.pk_pensum = a.fk_pensum AND p.nombre ilike('1997') AND p.fk_escuela = ( SELECT fk_atributo
                                                                                                                    FROM tbl_inscripciones i
                                                                                                                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                                                                                                                    ORDER BY i.fk_periodo DESC LIMIT 1)
                    ) as sqt";
        }else if($pensum == 'Vigente' ){
            $SQL = "SELECT CASE WHEN (count(A.PK_ASIGNATURA)>0) THEN false ELSE true END as resultado
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos g 	ON g.fk_usuario 	= u.pk_usuario
                    JOIN tbl_inscripciones i 	ON i.fk_usuariogrupo 	= g.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra 	ON ra.fk_inscripcion  	= i.pk_inscripcion
                    JOIN tbl_asignaturas a 		ON a.pk_asignatura 	= ra.fk_asignatura
                    JOIN vw_materias m 		ON m.pk_atributo 	= a.fk_materia --AND a.fk_materia not in (9738)
                    JOIN tbl_atributos atr ON atr.pk_atributo = ra.fk_atributo
                    WHERE u.pk_usuario = {$ci}
                    AND /*m.materia ilike ('%TRABAJO DE GRADO I%') AND*/ a.fk_materia in (9738,9723)
                    AND
                    (
                    (ra.calificacion >= 10 AND ra.fk_atributo  = 862)
                    OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)
                    OR ra.fk_atributo IN (861)))
                    )";
        } else if($pensum == '1992') {

            $SQL = "SELECT CASE WHEN (count(A.PK_ASIGNATURA)>0) THEN false ELSE true END as resultado
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos g 	ON g.fk_usuario 	= u.pk_usuario
                    JOIN tbl_inscripciones i 	ON i.fk_usuariogrupo 	= g.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra 	ON ra.fk_inscripcion  	= i.pk_inscripcion
                    JOIN tbl_asignaturas a 		ON a.pk_asignatura 	= ra.fk_asignatura
                    JOIN vw_materias m 		ON m.pk_atributo 	= a.fk_materia --AND a.fk_materia not in (9738)
                    JOIN tbl_atributos atr ON atr.pk_atributo = ra.fk_atributo
                    WHERE u.pk_usuario = {$ci}
                    AND /*m.materia ilike ('%TESIS DE GRADO I%')*/ a.fk_materia in (830,719)
                    AND
                    (
                    (ra.calificacion >= 10 AND ra.fk_atributo  = 862)
                    OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)
                    OR ra.fk_atributo IN (861)))
                    )";
        }

        //print_r($SQL);die;
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function verificarInscritaAprobada($ci){

        $SQL = "SELECT CASE WHEN (count(*)>0) THEN false ELSE true END as resultado
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos g 	ON g.fk_usuario 	= u.pk_usuario
                JOIN tbl_inscripciones i 	ON i.fk_usuariogrupo 	= g.pk_usuariogrupo
                JOIN tbl_recordsacademicos ra 	ON ra.fk_inscripcion  	= i.pk_inscripcion
                JOIN tbl_asignaturas a 		ON a.pk_asignatura 	= ra.fk_asignatura
                JOIN vw_materias m 		ON m.pk_atributo 	= a.fk_materia
                JOIN tbl_atributos atr ON atr.pk_atributo = ra.fk_atributo
                WHERE u.pk_usuario = {$ci}
                AND (m.materia ilike ('%TESIS DE GRADO II%') OR m.materia ilike ('%TRABAJO DE GRADO II%'))
                AND
                (
                (ra.calificacion >= 10 AND ra.fk_atributo  = 862)
                OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)
                OR ra.fk_atributo IN (861,864)))
                )
                AND a.fk_pensum = (SELECT fk_pensum
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos g ON g.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE g.fk_usuario = {$ci}
                ORDER BY fk_periodo DESC LIMIT 1)";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function faltaSoloTesis($ci,$pensum){

        $SQL="SELECT CASE WHEN (count(sqt.*)=1) THEN true ELSE false END as resultado
                    FROM
                    (SELECT a.pk_asignatura
                    FROM tbl_asignaturas a
                    JOIN tbl_pensums  p ON a.fk_pensum = p.pk_pensum AND p.nombre ilike ('{$pensum}') and p.fk_escuela = (SELECT fk_atributo
                    FROM tbl_inscripciones i
                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                    ORDER BY i.fk_periodo DESC LIMIT 1)
                    JOIN vw_materias  m ON m.pk_atributo  = a.fk_materia AND a.fk_materia NOT IN (1701,894,907,718,719,913)
                    JOIN vw_semestres s ON s.pk_atributo  = a.fk_semestre AND s.id BETWEEN 1 AND 12
                    EXCEPT
                    SELECT a.pk_asignatura
                    FROM tbl_inscripciones  i
                    JOIN tbl_usuariosgrupos ug ON ug .pk_usuariogrupo = i.fk_usuariogrupo AND fk_usuario = {$ci}
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861,864))))
                    JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_pensums p ON p.pk_pensum = a.fk_pensum AND p.nombre ilike('{$pensum}') AND p.fk_escuela = ( SELECT fk_atributo
                                                                                                                    FROM tbl_inscripciones i
                                                                                                                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                                                                                                                    ORDER BY i.fk_periodo DESC LIMIT 1))as sqt";

        return $this->_db->fetchOne($SQL);
    }

    public function getUltimaEscuela($ci){

        $SQL = "    SELECT DISTINCT pen.fk_escuela
                    FROM tbl_pensums pen
                    JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    WHERE us.pk_usuario = {$ci}
                    AND ins.fk_periodo = (  SELECT ins.fk_periodo
					    FROM tbl_usuarios us
					    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
					    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
					    WHERE us.pk_usuario = {$ci}
					    ORDER BY 1 DESC
					    LIMIT 1)";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getPkUsuarioGrupoEstudiante($ci){

        $SQL = "SELECT pk_usuariogrupo
                FROM tbl_usuariosgrupos
                WHERE fk_usuario = {$ci}
                AND fk_grupo = 855";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getPeriodoVigente($fecha){

        $SQL = "SELECT pk_periodo
                FROM tbl_periodos
                WHERE '{$fecha}'
                BETWEEN fechainicio AND fechafin";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function verificarInscripcionPeriodo($periodo,$ci){

        $SQL = "SELECT count(*)
                FROM tbl_inscripciones i
                JOIN tbl_periodos p ON i.fk_periodo = p.pk_periodo
                JOIN tbl_usuariosgrupos g ON g.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE p.pk_periodo = {$periodo}
                AND g.fk_usuario = {$ci}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getEstructuraUltimaInscripcion($ci){

        $SQL = "SELECT fk_estructura
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos g ON g.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE g.fk_usuario = {$ci}
                AND i.fk_periodo =  (SELECT ins.fk_periodo
                                     FROM tbl_usuarios us
                                     JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                     JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                     WHERE us.pk_usuario = {$ci}
                                     ORDER BY ins.fk_periodo DESC
                                     limit 1)";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getSemestreUbicacion($ci,$escuela,$periodo,$pensum){

        $SQL = "";

    }

        //Para obtener si tenia Tesis o Pasantia en su trabajo de grado I
    public function getModalidad($ci){

        $SQL = "SELECT 
                CASE    
                    WHEN ati.valor LIKE '%P%' THEN 'Pasantia'
                    ELSE 'Tesis'
                END AS Tipo
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN tbl_recordsacademicos re ON i.pk_inscripcion = re.fk_inscripcion
                JOIN tbl_asignaturas asi ON re.fk_asignatura = asi.pk_asignatura
                JOIN tbl_asignaciones a ON re.fk_asignacion = a.pk_asignacion
                JOIN tbl_atributos ati ON a.fk_seccion = ati.pk_atributo
                WHERE (asi.fk_materia = 9723 OR asi.fk_materia = 830) and u.pk_usuario = {$ci}
                ORDER BY ati.valor";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getFkInscripcion($periodo,$ci){

        $SQL = "SELECT pk_inscripcion
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos g ON g.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE g.fk_usuario = {$ci}
                AND i.fk_periodo = {$periodo}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getFkAsignatura($pensum,$pkpensum){

        if($pensum == '1997' || $pensum == '1992'){
            $materia = 834;
        }else if($pensum == '2012'){
            $materia = 9724;
        }

        $SQL = "SELECT pk_asignatura
                FROM tbl_asignaturas
                WHERE fk_pensum = {$pkpensum}
                AND fk_materia = {$materia}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function setInscripcion
    ($usuariogrupo,$numeropago,$fechahora,$periodo,$escuela,$estructura,$semestre,$pensum){
        //var_dump($usuariogrupo,$numeropago,$fechahora,$periodo,$escuela,$estructura,$semestre,$pensum);die;
        $SQL = " INSERT INTO tbl_inscripciones
                (

                fk_usuariogrupo,
                numeropago,
                fechahora,
                fk_periodo,
                fk_atributo,
                fk_estructura,
                ucadicionales,
                fk_semestre,
                pago_manual,
                online,
                fk_pensum
                )
                VALUES
                (
                --PROBAR PRIMERO SI SE AUTO INCREMENTA.
                {$usuariogrupo},{$numeropago},'{$fechahora}',{$periodo},{$escuela},{$estructura},0, {$semestre},false,false, {$pensum}
                );";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }


    public function setRecordAcademico($fk_asignatura,$fk_inscripcion){

        $SQL = "INSERT INTO tbl_recordsacademicos(
                fk_atributo,
                calificacion,
                fk_asignatura,
                pk_recordacademico,
                fk_inscripcion
                )
                VALUES
                (
                864,
                0,
                {$fk_asignatura},
                (SELECT pk_recordacademico+1 FROM tbl_recordsacademicos ORDER BY 1 DESC LIMIT 1), --VERIFICAR SI NO AUTOINCREMENTA
                {$fk_inscripcion}
                );";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

      public function getUltimoPeriodoVigente($fecha){

          $SQL = "SELECT pk_periodo
                        FROM tbl_periodos
                        WHERE '{$fecha}' BETWEEN fechainicio AND fechafin;
                        ";

       return $this->_db->fetchOne($SQL);

        }

        public function isMateriaInscrita($usuario,$materia,$periodo){

             $SQL = "SELECT ra.fk_asignatura as asignatura,e.escuela as escuela
                        FROM tbl_inscripciones 	   i
                        JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo AND fk_usuario = {$usuario} and i.fk_periodo = {$periodo}
                        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion  = i.pk_inscripcion  AND ra.fk_atributo = 864
                        JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
                        JOIN vw_materias m ON m.pk_atributo = a.fk_materia AND m.materia ilike ('{$materia}')

                        JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo;";



         $results = $this->_db->query($SQL);

         return (array)$results->fetchAll();

        }

   public function getMaterias($ci,$pensum){

    $SQL = "SELECT m.materia , s.id as semestre , a.codigopropietario
                    FROM tbl_asignaturas a
                    JOIN tbl_pensums  p ON a.fk_pensum = p.pk_pensum AND p.nombre ilike ('{$pensum}') and p.fk_escuela = (SELECT fk_atributo
                    FROM tbl_inscripciones i
                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                    ORDER BY i.fk_periodo DESC LIMIT 1)
                    JOIN vw_materias  m ON m.pk_atributo  = a.fk_materia AND a.fk_materia NOT IN (1701,894,907,718,719,913,9724,834)
                    JOIN vw_semestres s ON s.pk_atributo  = a.fk_semestre AND s.id BETWEEN 1 AND 12
                    WHERE a.pk_asignatura NOT IN(
                    SELECT a.pk_asignatura
                    FROM tbl_inscripciones  i
                    JOIN tbl_usuariosgrupos ug ON ug .pk_usuariogrupo = i.fk_usuariogrupo AND fk_usuario = {$ci}
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861))))
                    JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_pensums p ON p.pk_pensum = a.fk_pensum AND p.nombre ilike('{$pensum}') AND p.fk_escuela = ( SELECT fk_atributo
                                                                                                                    FROM tbl_inscripciones i
                                                                                                                    JOIN tbl_usuariosgrupos ug ON i.fk_usuariogrupo = ug.pk_usuariogrupo AND fk_usuario = {$ci}
                                                                                                                    ORDER BY i.fk_periodo DESC LIMIT 1)) ORDER BY 2";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
   }
}

?>
