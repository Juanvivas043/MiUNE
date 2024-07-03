<?php
class Models_DbTable_Reinscripciones extends Zend_Db_Table {
    
        
     public function sedeUltimo($ci){

         
         $SQL = " SELECT DISTINCT i1.fk_estructura, estr.nombre, i1.fk_periodo
                        FROM tbl_inscripciones i1 
                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i1.fk_usuariogrupo
                        JOIN tbl_estructuras estr ON estr.pk_estructura = i1.fk_estructura
                        WHERE i1.fk_periodo = (
                        SELECT DISTINCT i.fk_periodo
                                        FROM tbl_inscripciones i
                                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                        where ug.fk_usuario = $ci
                                        order by 1 DESC
                                        limit 1
                        )
                        AND ug.fk_usuario = $ci;";
                    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }    
    
    public function buscarEst($ci){
        
        $SQL = "SELECT pk_usuario FROM tbl_usuarios WHERE pk_usuario = $ci";
        
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function infoEst($ci){
        $SQL = "SELECT ins.fk_periodo as periodo , pen.fk_escuela ,esc.escuela,pen.pk_pensum,pen.codigopropietario,pen.nombre as pensum, u.nombre, u.apellido,u.pk_usuario as ci
                FROM tbl_inscripciones ins 
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario 
                JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum 
                JOIN vw_escuelas esc ON esc.pk_atributo = pen.fk_escuela
                WHERE ug.fk_usuario = {$ci}
                AND ins.fk_periodo = (
                            SELECT fk_periodo
                            FROM tbl_inscripciones ins2
                            JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = ins2.fk_usuariogrupo
                            WHERE ug2.fk_usuario = ug.fk_usuario
                            ORDER BY 1 DESC LIMIT 1
                            )";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function acceso($include){
        
        $SQL = "SELECT pk_acceso FROM tbl_accesos WHERE include ilike '$include'";
        
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
        public function sedeUltimoPeriodo($ci){
        
                 $SQL = " SELECT DISTINCT i1.fk_estructura, estr.nombre, i1.fk_periodo
                        FROM tbl_inscripciones i1 
                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i1.fk_usuariogrupo
                        JOIN tbl_estructuras estr ON estr.pk_estructura = i1.fk_estructura
                        WHERE i1.fk_periodo = (
                        SELECT DISTINCT i.fk_periodo
                                        FROM tbl_inscripciones i
                                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion
                                        where ug.fk_usuario = $ci
                                        AND ra.fk_atributo = 862
                                          order by 1 DESC
                                          limit 1
                                                                )
                                                                AND ug.fk_usuario = $ci;";
                 $results = $this->_db->query($SQL);
                 $results = $results->fetchAll();

        return $results;
        
    }
     public function sedePrimerPeriodo($ci){
        
                 $SQL = " SELECT DISTINCT i1.fk_estructura, estr.nombre, i1.fk_periodo
                        FROM tbl_inscripciones i1 
                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i1.fk_usuariogrupo
                        JOIN tbl_estructuras estr ON estr.pk_estructura = i1.fk_estructura
                        WHERE i1.fk_periodo = (
                        SELECT DISTINCT i.fk_periodo
                                        FROM tbl_inscripciones i
                                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                        where ug.fk_usuario = $ci
                                          order by 1 DESC
                                          limit 1
                                                                )
                                                                AND ug.fk_usuario = $ci;";
                 $results = $this->_db->query($SQL);
                 $results = $results->fetchAll();

        return $results;
        
    }
    
    
    public function sedeUltimoPeriodoCursado($ci, $escuela){
        
                 $SQL = " SELECT fn_xrxx_reinscripcion_upc($ci, $escuela) as periodo;";
                 $results = $this->_db->query($SQL);
                 $results = $results->fetchAll();

        return $results;
        
    }
    
    public function ultimoperiodo(){
        
        $SQL = " SELECT * , CURRENT_DATE
                FROM tbl_periodos
                --WHERE fechainicio = CURRENT_DATE
                ORDER BY pk_periodo desc
                limit 1";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function horario(){
        $SQL = "SELECT h.fk_atributo, h.horainicio, atr.valor
                FROM tbl_horarios h
                JOIN tbl_atributos atr ON h.fk_atributo = atr.pk_atributo
                ORDER BY 1, 2;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
        public function buscarInscripcion($ci,$periodo){
        
        $SQL = "SELECT i.pk_inscripcion, i.numeropago, i.fechahora, i.fk_semestre, sem.id, ug.pk_usuariogrupo, i.observaciones
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario 
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_semestres sem ON sem.pk_atributo = i.fk_semestre
                WHERE u.pk_usuario = $ci
                AND   i.fk_periodo = $periodo";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        return $results;
        
        
    }
    
    public function quitarRespuestas($ci,$periodo){
        
        $this->_db->beginTransaction();
        
        $SQL = "delete from tbl_respuestas where pk_respuesta in (select r.pk_respuesta
						from tbl_respuestas r
						join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = fk_asignacionencuesta
						join tbl_inscripcionesencuestas ie on ie.pk_inscripcionencuesta = fk_inscripcionencuesta
						join tbl_inscripciones i on i.pk_inscripcion = ie.fk_inscripcion
						join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
						where ug.fk_usuario = {$ci}
						  and i.fk_periodo = {$periodo});";
        
        $return += $this->_db->query($SQL);
        
        
        $SQL = "delete from tbl_asignacionesencuestas where pk_asignacionencuesta in (SELECT pk_asignacionencuesta
						FROM tbl_asignacionesencuestas ae
						join tbl_inscripcionesencuestas ie on ie.pk_inscripcionencuesta = fk_inscripcionencuesta
						join tbl_inscripciones i on i.pk_inscripcion = ie.fk_inscripcion
						join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
						where ug.fk_usuario = {$ci}
						  and i.fk_periodo = {$periodo});";
        
        $return += $this->_db->query($SQL);
        
        $SQL = "delete from tbl_inscripcionesencuestas where pk_inscripcionencuesta in (select ie.pk_inscripcionencuesta
						from tbl_inscripcionesencuestas ie
						join tbl_inscripciones i on i.pk_inscripcion = ie.fk_inscripcion
						join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
						where ug.fk_usuario = {$ci}
						  and i.fk_periodo = {$periodo});";
        
        $return += $this->_db->query($SQL);
        
        $this->_db->commit();
        
        return $return;
 
        
    }
    public function verificarSCIPasadaEnUltimoPeriodo($ci,$sede,$periodo) {
        $SQL = "SELECT  tr.calificacion,    tt2.valor AS estado
                FROM tbl_usuarios tu 
                JOIN tbl_usuariosgrupos tg    ON tu.pk_usuario      = tg.fk_usuario
                JOIN tbl_inscripciones ti     ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion  = tr.fk_inscripcion 
                JOIN tbl_asignaciones ta      ON tr.fk_asignacion   = ta.pk_asignacion
                JOIN tbl_asignaturas ts       ON ta.fk_asignatura   = ts.pk_asignatura
                JOIN tbl_atributos tt         ON ts.fk_materia      = tt.pk_atributo
                JOIN tbl_atributos tt1        ON ti.fk_atributo     = tt1.pk_atributo
                JOIN tbl_atributos tt2        ON tr.fk_atributo     = tt2.pk_atributo
                WHERE tu.pk_usuario  = {$ci}
                AND ti.fk_estructura = {$sede}
                AND ti.fk_periodo    = {$periodo}
                AND tr.calificacion >= 10
                AND tr.fk_atributo IN (862)
                AND ts.fk_materia  IN (9737);";

                 $results = $this->_db->query($SQL);
                 $results = $results->fetchAll();

                return $results;
    }
    public function matasigSCI ($periodo,$pensum,$sede){
        $SQL = "SELECT tasig.fk_asignatura, tasig.pk_asignacion
                FROM tbl_asignaturas  AS ta
                JOIN tbl_asignaciones AS tasig ON ta.pk_asignatura = tasig.fk_asignatura
                JOIN vw_estructuras AS est ON tasig.fk_estructura = est.pk_aula
                WHERE  tasig.fk_periodo = {$periodo} AND ta.fk_pensum = {$pensum} AND est.pk_sede = {$sede}  AND ta.fk_materia = 9738;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    
    public function insertarRecordAcademico($fk_atributo,$fk_asignatura,$fk_inscripcion,$fk_asignacion,$ci,$periodo){

        $SQL_delete = "DELETE from tbl_recordsacademicos 
                where pk_recordacademico in (select pk_recordacademico from tbl_recordsacademicos ra
                join tbl_inscripciones ins on ins.pk_inscripcion = ra.fk_inscripcion
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ins.fk_usuariogrupo
                where fk_usuario = {$ci} and ins.fk_periodo ={$periodo});  ";
        
        $results_delete = $this->_db->query($SQL_delete);

        $count_asignatura = count($fk_asignatura);
        for ($i = 0;$i < $count_asignatura; $i++) {
            if($i == 0){
                $SQL_insert = "INSERT INTO tbl_recordsacademicos (fk_atributo, fk_asignatura, fk_inscripcion, fk_asignacion)
                        VALUES ($fk_atributo, {$fk_asignatura[$i]['fk_asignatura']}, $fk_inscripcion, {$fk_asignacion[$i]['pk_asignacion']})";
            }
            else{
                $SQL_insert .= ",($fk_atributo, {$fk_asignatura[$i]['fk_asignatura']}, $fk_inscripcion, {$fk_asignacion[$i]['pk_asignacion']})";
            }
       }
        $SQL_insert .= ";";
       
        $results_insert = $this->_db->query($SQL_insert);

        return $results;        
    }
    
    public function insertarInscripcion($fk_usuariogrupo, $numeropago, $fk_periodo, $fk_atributo, $fk_estructura, $ucadicionales, $fk_tipo,$online, $fk_pensum){
        
        $SQL = "INSERT INTO tbl_inscripciones(
                fk_usuariogrupo, numeropago, fk_periodo, 
                fk_atributo, fk_estructura, ucadicionales,   
                fk_tipo, pago_manual,online, fk_pensum)
                VALUES ($fk_usuariogrupo, $numeropago, $fk_periodo, $fk_atributo, $fk_estructura, $ucadicionales, $fk_tipo, false,$online, $fk_pensum);";
        
        
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
        
    }
    
    public function actualizarSemestreUbic($semUbic, $pk_inscripcion){
        
        $SQL = "UPDATE tbl_inscripciones
                SET fk_semestre = $semUbic
                WHERE pk_inscripcion = $pk_inscripcion;";
        
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
        
        
    }

    public function pkusuariogrupo($ci){
        $SQL = " SELECT *
                 FROM tbl_usuariosgrupos 
                 WHERE fk_usuario = $ci
                 AND fk_grupo = 855";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function actualizarInscripcion($pk_inscripcion, $numeropago, $fk_tipo, $uca){
        
        $SQL = "UPDATE tbl_inscripciones
                SET numeropago = $numeropago, fk_tipo = $fk_tipo, ucadicionales = $uca
                WHERE pk_inscripcion = $pk_inscripcion;"; 
        //var_dump($SQL);die;
                    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
        
    } 
    
    public function borrarInscripcionAnterior($fk_inscripcion, $fk_asignatura){
        
        $SQL = "DELETE FROM tbl_recordsacademicos 
                WHERE fk_inscripcion = {$fk_inscripcion}
                AND fk_asignatura = {$fk_asignatura};";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function borrarMateriasRetiradas($fk_recordacademico){
        
        $SQL = "DELETE FROM tbl_materiasaretirar WHERE fk_recordacademico = {$fk_recordacademico};";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function verificarMateriasRetiradas($fk_inscripcion){
        
        $SQL = " SELECT fk_recordacademico
                 from tbl_materiasaretirar mr
                 JOIN tbl_recordsacademicos ra ON ra.pk_recordacademico = mr.fk_recordacademico
                 WHERE ra.fk_inscripcion =  {$fk_inscripcion};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
   
    public function verificarMateriasInscritas($fk_inscripcion){
        
        $SQL = " SELECT ra.fk_asignatura
                    FROM tbl_inscripciones ins
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                    WHERE ins.pk_inscripcion = {$fk_inscripcion};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function nombreMateria($codpro,$escuela,$codpensum){
        
        $SQL = "SELECT ma.materia, asi.codigopropietario ,substring(asi.codigopropietario from 5 for 4)
                FROM tbl_asignaturas asi 
                JOIN vw_materias ma ON ma.pk_atributo = asi.fk_materia
                JOIN tbl_pensums pen ON pen.pk_pensum = asi.fk_pensum
                JOIN vw_escuelas es ON es.pk_atributo = pen.fk_escuela
                WHERE
                es.pk_atributo = $escuela AND
                pen.codigopropietario = $codpensum AND
                 substring(asi.codigopropietario from 5 for 4) = '$codpro'
                limit 1";
                 
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function buscarDocente($pk_asignacion){
        
        $SQL = "SELECT doc.docente
                FROM tbl_asignaciones asg
                JOIN vw_docentes doc ON doc.pk_usuariogrupo = asg.fk_usuariogrupo
                WHERE asg.pk_asignacion = $pk_asignacion
                limit 1;";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function buscarMateriasIncritas($fk_inscripcion,$fk_periodo){
         $SQL = "SELECT distinct ag.codigopropietario, m.materia, sem.id, ag.unidadcredito, s.valor, a.pk_asignacion, tur.valor as turno
                    FROM tbl_recordsacademicos ra
                    JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND a.pk_asignacion = ra.fk_asignacion
                    JOIN vw_materias m ON ag.fk_materia = m.pk_atributo
                    JOIN vw_secciones s ON a.fk_seccion = s.pk_atributo
                    JOIN vw_semestres sem ON sem.pk_atributo = a.fk_semestre
                    JOIN vw_turnos tur ON tur.pk_atributo = a.fk_turno
                    WHERE fk_inscripcion = $fk_inscripcion
                    and a.fk_periodo = $fk_periodo";
         
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function buscarAsignatura($periodo,$pkasignacion){
        
        $SQL = " SELECT fk_asignatura 
        FROM tbl_asignaciones
        WHERE fk_periodo = $periodo
        AND pk_asignacion = $pkasignacion";
       
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function turnos(){
        
       $SQL = " SELECT pk_atributo, id, valor
                FROM vw_turnos;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

        
    }
    
    public function horariosNuevos($periodo, $sede, $ci, $escuela){
        
        $SQL = "    SELECT sqt.pk_atributo, sqt.dia, sqt.horainicio, sqt.horafin, foo.materia, foo.valor as seccion, foo.pksec, foo.fk_materia,foo.sem as semestre, foo.pksem, foo.turnos, foo.pkturno, foo.codigopropietario, foo.unidadcredito, foo.pk_asignacion, foo.calificacion, foo.enable_uno, foo.prelacion, foo.curso_simultaneo, foo.estado
                    FROM
                    (select ho.horainicio, ho.horafin, d.dia, d.pk_atributo 
                                         from vw_dias d
                                         CROSS JOIN tbl_horarios ho
                                         where ho.fk_atributo < 892
                                           --and d.id != 6
                                           --and pk_atributo < 7
                                           and pk_atributo < 8
                                           and ho.pk_horario <> 9
                                         order by pk_atributo, ho.horainicio) as sqt
                    LEFT OUTER JOIN
                    (		
				SELECT di.dia, h.horainicio, h.horafin, sec.valor, sec.pk_atributo as pksec, asig.fk_materia ,tur.valor as turnos, tur.pk_atributo as pkturno, asg.pk_asignacion,tbl_rscp.*,di.id, sem.id as sem, sem.pk_atributo as pksem	
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT *,

				prelacion && 
				(SELECT ARRAY(

						SELECT substring(codigopropietario from 5 for 8) 
						--SELECT *
						FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
						)
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
					 simultaneo_candidata boolean)
					 WHERE calificacion < 10 AND estado = 862
					 )) as curso_simultaneo


				FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura AND asg.fk_periodo = $periodo
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 LEFT OUTER JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
				WHERE (tbl_rscp.enable_uno = true OR curso_simultaneo = true)
				AND 
				tbl_rscp.enable_dos = true
				AND tbl_rscp.enable_tres = true
				AND tbl_rscp.enable_cuatro = true
                                AND asg.disponible = true
				--AND p.fk_escuela = $escuela 
                                ) as foo ON (sqt.horainicio = foo.horainicio AND sqt.pk_atributo = foo.id)
                                WHERE foo.materia IS NOT NULL --Quita horas vacias
                                --AND materia NOT IN ('P.I.R.A.')
                                --AND foo.sem = 4
				ORDER BY 1,3,2,sqt.pk_atributo,semestre,seccion; ";    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function horariosNuevosCasosEspeciales($escuela, $sede, $ci){
        
        $SQL = "    SELECT sqt.pk_atributo, sqt.dia, sqt.horainicio, sqt.horafin, foo.materia, foo.valor as seccion, foo.pksec, foo.fk_materia,foo.sem as semestre, foo.pksem, foo.turnos, foo.pkturno, foo.codigopropietario, foo.unidadcredito, foo.pk_asignacion, foo.calificacion, foo.enable_uno, foo.prelacion, foo.curso_simultaneo, foo.estado
                    FROM
                    (select ho.horainicio, ho.horafin, d.dia, d.pk_atributo 
                                         from vw_dias d
                                         CROSS JOIN tbl_horarios ho
                                         where ho.fk_atributo < 892
                                           --and d.id != 6
                                           --and pk_atributo < 7
                                           and pk_atributo < 8
                                           and ho.pk_horario <> 9
                                         order by pk_atributo, ho.horainicio) as sqt
                    LEFT OUTER JOIN
                    (		
				SELECT di.dia, h.horainicio, h.horafin, sec.valor, sec.pk_atributo as pksec, asig.fk_materia ,tur.valor as turnos, tur.pk_atributo as pkturno, asg.pk_asignacion,tbl_rscp.*,di.id, sem.id as sem, sem.pk_atributo as pksem	
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT *,

				prelacion && 
				(SELECT ARRAY(

						SELECT substring(codigopropietario from 5 for 8) 
						--SELECT *
						FROM fn_xrxx_reinscripcion_lmcp($ci, $escuela
						)
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
					 simultaneo_candidata boolean)
					 WHERE calificacion < 10 AND estado = 862
					 )) as curso_simultaneo
				FROM fn_xrxx_reinscripcion_lmcp($ci, $escuela
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura 
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 LEFT OUTER JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
                                ) as foo ON (sqt.horainicio = foo.horainicio AND sqt.pk_atributo = foo.id)
                                WHERE foo.materia IS NOT NULL --Quita horas vacias
                                --AND materia NOT IN ('P.I.R.A.')
				ORDER BY 1,3,2,sqt.pk_atributo,semestre,seccion; ";    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    
    public function fechaPeriodo($periodo){
        
        $SQL = "SELECT to_char(fechainicio, 'TMMonth YYYY') as fechainicio, to_char(fechafin, 'TMMonth YYYY') as fechafin
                FROM tbl_periodos 
                WHERE pk_periodo = $periodo";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function semestreUbic($periodo, $sede, $ci, $escuela, $asignacion,$codpensum){
       $SQL =  " SELECT SUM(foo.unidadcredito),foo.id, foo.pk_atributo FROM(
                 SELECT DISTINCT tbl_rscp.pk_asignatura, tbl_rscp.unidadcredito,sem.id, sem.pk_atributo
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT * FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura AND asg.fk_periodo = $periodo
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 LEFT OUTER JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela AND p.codigopropietario = $codpensum
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
				WHERE 
				asg.pk_asignacion IN ($asignacion) group by 1,2,3,4) as foo
				GROUP BY foo.id, foo.pk_atributo
				ORDER BY 1 DESC,2 DESC
                LIMIT 1";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function retiroDef($ci){
        
        $SQL = "SELECT ug.fk_usuario, COUNT(ra.pk_recordacademico)
                   FROM tbl_inscripciones ins
                   JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                   JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.fk_asignatura
                   WHERE ins.fk_periodo >= 121
                   AND ug.fk_usuario = $ci
                   AND ra.fk_atributo = 862
                   AND asi.fk_materia IN (
                      SELECT pk_atributo FROM vw_materias WHERE materia ilike '%P.I.R.A.%')
                   GROUP BY 1";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    public function checkBiblioteca($ci){
        
        $SQL = "
        SELECT *, CASE 
          WHEN estado = 'Vacio' then 1
          WHEN estado = 'Mora' then 2  
          WHEN estado = 'Transito'then 3 
          WHEN estado = 'Solvente' then 4 end as orden 
          
          FROM (
                SELECT solicitud, pk_usuario, nombre, apellido, perfil,correo, estado,fecha_prestamo,numeroart 
                FROM (SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                CASE WHEN mora > 0 THEN 'Mora'
                WHEN mora = 0 AND prestamo > 0 THEN 'Transito'
                WHEN mora = 0 AND prestamo = 0 AND devuelto > 0 THEN 'Solvente'
                ELSE 'Vacio' END as estado
                FROM(
                    SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                        SUM(mora) as mora,
                        SUM(prestamo) as prestamo,
                        SUM(devuelto) as devuelto
                        FROM(
                        SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                        CASE WHEN fk_asignacion = 8244 THEN 1 ELSE 0 END as mora,
                        CASE WHEN fk_asignacion = 8242 THEN 1 ELSE 0 END as prestamo,
                        CASE WHEN fk_asignacion = 8243 THEN 1 ELSE 0 END as devuelto

                            FROM(
                                 SELECT p.pk_prestamo as solicitud , u.pk_usuario , u.nombre , u.apellido ,u.correo,gr.grupo as perfil, p.fecha_prestamo , preart.fk_asignacion , count(preart.pk_prestamoarticulo)as numeroart
                                 FROM tbl_usuarios u 
                                 JOIN tbl_usuariosgrupos gp ON gp.fk_usuario = u.pk_usuario
                                 JOIN tbl_prestamos p ON p.fk_usuariogrupo = gp.pk_usuariogrupo
                                 left outer join tbl_prestamosarticulos preart ON preart.fk_prestamo = p.pk_prestamo
                                 JOIN vw_grupos gr ON gr.pk_atributo = gp.fk_grupo
                                 GROUP BY 1,2,3,4,5,6,7,8
                                 ) as sqt) as sqt2
        GROUP BY 1,2,3,4,5,6,7,8) as sqt3) as sqt4) as sqt5
        WHERE pk_usuario = {$ci} and ( estado ilike '%Mora%' or estado ilike '%Transito%')
        ORDER BY 10  ASC  , 8 DESC 
         ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function uca($ci,$periodo,$escuela){
        $SQL =  "SELECT ucadicionales
                    FROM tbl_usuariosgrupos ug
                    JOIN tbl_inscripciones   i ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                    WHERE ug.fk_usuario  = $ci
                      AND  i.fk_periodo  = $periodo
                      AND  i.fk_atributo = $escuela";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function existeMateria($periodo, $sede, $ci, $escuela, $asignacion){
        
        	$SQL =  "	SELECT di.dia, h.horainicio, h.horafin, sec.valor, tur.valor as turnos, asg.pk_asignacion,tbl_rscp.*,di.id
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT *,

                                prelacion && 
                                (SELECT ARRAY(

                                SELECT substring(codigopropietario from 5 for 8) 
                                --SELECT *
                                FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)
					 WHERE calificacion < 10 AND estado = 862
					 )) as curso_simultaneo


                                FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura AND asg.fk_periodo = $periodo
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
				WHERE  (tbl_rscp.enable_uno = true OR curso_simultaneo = true)
				AND tbl_rscp.enable_dos = true
				AND tbl_rscp.enable_tres = true
				AND tbl_rscp.enable_cuatro = true
                                AND asg.disponible = true
				AND p.fk_escuela = $escuela
				AND asg.pk_asignacion = $asignacion";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function ucSum($periodo, $sede, $ci, $escuela, $asignacion, $codpensum){
        $SQL =  "SELECT SUM(uc.unidadcredito) FROM(
                 SELECT DISTINCT tbl_rscp.pk_asignatura, tbl_rscp.unidadcredito
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT * FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura AND asg.fk_periodo = $periodo
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 LEFT OUTER JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela AND p.codigopropietario = $codpensum
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
				WHERE --tbl_rscp.enable_uno = true
				--AND tbl_rscp.enable_dos = true
				--AND tbl_rscp.enable_tres = true
				--AND tbl_rscp.enable_cuatro = true
                            codigopropietar    --AND asg.disponible = true
				--AND 
                                --p.fk_escuela = $escuela
				asg.pk_asignacion IN ($asignacion)) AS uc";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
				
				
        
    }
    
    public function buscarSeleccionarMaterias($periodo, $sede, $ci, $escuela, $seccion, $codigopropietario){
        
        $SQL =  " SELECT asg.pk_asignacion, sec.valor, tbl_rscp.codigopropietario
				--SELECT asg.pk_asignacion, tbl_rscp.*
				FROM(
				SELECT * FROM fn_xrxx_reinscripcion_lmcp_per($ci, $escuela, $periodo
				)
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
					 simultaneo_candidata boolean)order by 3) as tbl_rscp
					 JOIN tbl_asignaciones asg ON asg.fk_asignatura = tbl_rscp.pk_asignatura AND asg.fk_periodo = $periodo
					 JOIN vw_secciones sec ON asg.fk_seccion = sec.pk_atributo
                                         JOIN vw_semestres sem ON asg.fk_semestre = sem.pk_atributo
                                         JOIN vw_turnos tur ON asg.fk_turno = tur.pk_atributo
					 JOIN tbl_asignaturas asig ON asg.fk_asignatura = asig.pk_asignatura
					 JOIN tbl_pensums p ON asig.fk_pensum = p.pk_pensum AND p.fk_escuela = $escuela
					 JOIN tbl_estructuras est ON est.pk_estructura = asg.fk_estructura
					 JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura AND est1.fk_estructura = $sede
					 JOIN tbl_horarios h ON asg.fk_horario = h.pk_horario
					 JOIN vw_dias di ON di.pk_atributo = asg.fk_dia
				WHERE tbl_rscp.enable_uno = true
				AND tbl_rscp.enable_dos = true
				AND tbl_rscp.enable_tres = true
				AND tbl_rscp.enable_cuatro = true
                                AND asg.disponible = true
				AND p.fk_escuela = $escuela
				AND sec.valor = '$seccion'
				AND tbl_rscp.codigopropietario = '$codigopropietario'";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function datosEstudiante($ci){
        $SQL =  "SELECT u.nombre, u.apellido
                      FROM tbl_usuariosgrupos as ug
                      JOIN tbl_usuarios as u ON ug.fk_usuario = u.pk_usuario
                      WHERE u.pk_usuario = $ci  
                      GROUP BY 1,2";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function ultimoPensumEscuela($escuela){
	    $SQL = "SELECT *
                        FROM tbl_pensums
                        WHERE fk_escuela = $escuela
                        ORDER BY 4 DESC
                        LIMIT 1;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getPensum($cedula, $escuela){

        $SQL = "SELECT pen.codigopropietario as codigopensum
             ,pen.nombre
             ,pen.pk_pensum
                      FROM tbl_inscripciones ins
                      JOIN tbl_pensums       pen ON pen.pk_pensum = ins.fk_pensum
                      JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                      WHERE ug.fk_usuario = {$cedula}
                                     AND pen.fk_escuela = {$escuela}
                      ORDER BY 1 DESC
                      LIMIT 1;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }
    
    public function getPensumNew($cedula, $periodo){

        $SQL = "SELECT numeropago, ucadicionales, S.Nombre, I.fk_atributo, S.pk_estructura, I.fk_pensum, pen.nombre as name_pensum, pen.codigopropietario as codigopensum
					FROM tbl_inscripciones I
					INNER JOIN tbl_usuariosgrupos U ON U.pk_usuariogrupo = I.fk_usuariogrupo
                    INNER JOIN tbl_pensums pen ON pen.pk_pensum = I.fk_pensum
					INNER JOIN vw_sedes S ON S.pk_estructura = I.fk_estructura
					WHERE U.fk_usuario = $cedula AND I.fk_periodo = $periodo";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        return $results;

    }
    
    
    
    
    public function escuelaEstudiante($ci){
        $SQL = "SELECT atr.valor, atr.pk_atributo, MAX(fechahora)
                      FROM tbl_usuariosgrupos as ug
                      JOIN tbl_inscripciones as ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                      JOIN tbl_atributos as atr ON ins.fk_atributo = atr.pk_atributo
                      WHERE ug.fk_usuario = $ci  
                      GROUP BY 1,2
                      ORDER BY 3 desc;";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }
    
    public function checkNuevoPensum($ultperi, $sede, $ci){

       
        $SQL = "  SELECT pen.codigopropietario
                  FROM tbl_inscripciones ins
                  JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum 
                  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                          WHERE ins.fk_periodo = {$ultperi}
                                            AND ins.fk_estructura = {$sede}
                        AND ug.fk_usuario = {$ci}
                       ORDER BY 1 ASC
                       LIMIT 1;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function IndiceAcademicoAcumuladoEscuela($ci, $escuela, $ultperi, $check) {
        $SQL = "SELECT fn_xrxx_estudiante_iia_escuela_periodo_articulado($ci, $escuela, $ultperi, $check);";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function indiceAcademico($ci,$escuela){
//        $SQL = "SELECT fn_xrxx_estudiante_iia($ci);";
        $SQL = "SELECT fn_xrxx_estudiante_iia_escuela_new($ci,$escuela);";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function indicePeriodo($ci,$periodo,$escuela){
//        $SQL = "SELECT fn_xrxx_estudiante_iap($ci,$periodo);";
        $SQL = "SELECT COALESCE(fn_xrxx_estudiante_iap_sce($ci , $periodo, $escuela),0) as iiap;";//hola
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        return $results;
        
    }
    
    public function unidadesCreditoApro($ci,$escuela, $pensum){
        $SQL = "SELECT COALESCE(SUM(A.UnidadCredito),0) as uca
                                   FROM tbl_recordsacademicos RA
                                   INNER JOIN tbl_asignaturas A ON RA.FK_Asignatura = A.PK_Asignatura
                                   INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
                                   INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
				   INNER JOIN tbl_pensums p ON p.pk_pensum = A.fk_pensum
                                   WHERE ug.FK_Usuario = {$ci} AND
                                   i.fk_atributo = {$escuela}
                                   AND (RA.fk_atributo = 862 AND RA.calificacion >= 10)
                                   AND p.codigopropietario = {$pensum}
                                   ;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function calificacionMaterias($ci,$periodo){
       $SQL = " SELECT substring(codigopropietario from 5 for 8), ra. calificacion
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones ins ON ra.fk_inscripcion = ins.pk_inscripcion 
            JOIN tbl_usuariosgrupos ug ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
            JOIN tbl_asignaturas asi ON ra.fk_asignatura = asi.pk_asignatura
            WHERE ug.fk_usuario = $ci
              AND ra.fk_atributo = 862 
              AND ins.fk_periodo = $periodo";
       
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function UnnidadesCreditoAdicionales($ci, $periodo){
        $SQL = "SELECT ins.ucadicionales
                FROM tbl_usuariosgrupos ug
                JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                WHERE ug.fk_usuario = $ci
                AND ins.fk_periodo = $periodo";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function ucEscuela($ci, $escuela ,$SemestreUbicacionNuevo, $Sede, $ultperiinsc, $pensum){
        
//        $SQL = "SELECT sqt.fk_periodo ,sqt.pk_pensum, sqt.nombre as pensum, sqt.sempk as pksemestre ,sqt.id as semestre, sqt.pkesc as pkescuela, sqt.escuela, sum(sqt.unidadcredito) as uc 
//                FROM
//                (SELECT DISTINCT MAX(asig.pk_asignacion), asig.fk_periodo, p.pk_pensum, p.nombre, es.pk_atributo as pkesc ,es.escuela, se.pk_atributo as sempk, se.id, ma.materia,asi.unidadcredito
//                FROM tbl_asignaturas asi
//                JOIN tbl_asignaciones asig ON asig.fk_asignatura = asi.pk_asignatura
//                JOIN tbl_pensums p ON asi.fk_pensum = p.pk_pensum
//                JOIN vw_escuelas es ON p.fk_escuela = es.pk_atributo
//                JOIN vw_semestres se ON asi.fk_semestre = se.pk_atributo
//                JOIN vw_materias ma ON asi.fk_materia = ma.pk_atributo
//                WHERE asig.fk_periodo = $periodo
//                AND se.id = $semestre
//                AND es.pk_atributo  = $escuela
//                AND asi.fk_materia NOT IN (716,717,718,719,848,913)
//                GROUP BY 2,3,4,5,6,7,8,9,10) as sqt
//                WHERE sqt.fk_periodo = $periodo
//                  AND sqt.id = $semestre
//                  AND sqt.pkesc = $escuela
//                GROUP BY 1,2,3,4,5,6,7
//                ORDER BY 3 DESC,6,5";
        

        $SQL = "SELECT * FROM fn_xrxx_estudiante_calcular_ucps($ci, $escuela, $SemestreUbicacionNuevo, $Sede , $ultperiinsc, $pensum) AS (semestre SMALLINT, uc INT8);";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    //Busca cupos maximos de un aula
    public function getCuposMax($ultPeriodoIns, $sede, $asignatura, $semestre, $seccion, $turno) {
        $SQL = "SELECT fn_xrxx_inscripciones_copo_max({$ultPeriodoIns}, {$sede}, '{$asignatura}', {$semestre}, {$turno}, {$seccion}) as max;";
       // var_dump($SQL);die;

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    //Busca  cuantos estudiantes se incribieron o se estan inscribiendo en una seccion
    public function CantidadDeInscritosPorMateria($ultPeriodoIns, $sede, $asignatura, $semestre, $seccion, $turno, $estudiante) {
        $SQL = "SELECT fn_xrxx_inscripciones_cupo_cant({$ultPeriodoIns}, {$sede}, {$estudiante}, '{$asignatura}', {$semestre}, {$turno}, {$seccion}) as inscritos;";

        //var_dump($SQL);die;

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    //Busca si la materia seleccionada tiene alguna prelacion
    public function buscarPrelacion($pk_asignacion){
        
        $SQL = "SELECT pre.fk_atributo, pre.unidadescredito
                FROM tbl_asignaciones asg 
                JOIN tbl_asignaturas asi ON asi.pk_asignatura = asg.fk_asignatura
                JOIN tbl_prelaciones pre ON pre.fk_asignatura = asi.pk_asignatura
                WHERE asg.pk_asignacion = $pk_asignacion
                limit 1";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function buscarFkmateria($pk_asignacion){
        
        $SQL = "SELECT asg.fk_materia
        FROM tbl_asignaciones asi
        JOIN tbl_asignaturas asg ON asg.pk_asignatura = asi.fk_asignatura
        WHERE asi.pk_asignacion = $pk_asignacion";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    

    public function RetiroDefinitivo($ci, $ultmperiodo){
            
//        $SQL = "select * from fn_xrxx_estudiante_is_retiro_definitivo({$ci}, {$ultmperiodo}) as
//                     (usuario bigint, cant_pr bigint, pr_actual integer );";
        
          $SQL = " SELECT ug.fk_usuario, COUNT(ra.pk_recordacademico)
                   FROM tbl_inscripciones ins
                   JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                   JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.fk_asignatura
                   WHERE ins.fk_periodo >= 121
                   AND ug.fk_usuario = {$ci}
                   AND ra.fk_atributo = 862
                   AND ins.fk_pensum = (SELECT pk_pensum
                                        FROM tbl_pensums pe
                                        WHERE pe.codigopropietario = 7
                                          and pe.fk_escuela = ins.fk_atributo)
                   AND asi.fk_materia IN (
                      SELECT pk_atributo FROM vw_materias WHERE materia ilike '%P.I.R.A.%')
                   GROUP BY 1;";
    
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        
        
        if(!empty($results)){

            if($results[0][1] >= 2){
                $this->retirodef = true;
            }else{
                $this->retirodef = false;
            }

            $this->cantidad_recuperacion = $results[0][1] - $results[0][2];
        }else{
            $this->cantidad_recuperacion = 0;
            $this->retirodef = false;
            
        }
       
        return array($this->retirodef, $this->cantidad_recuperacion);
            
    }
    
    public function totalUCCAprobadasEscuelaEquiv($ci,$escuela,$pensum) { //Con traslados y equivalencias
        $SQL = "SELECT COALESCE(SUM(A.UnidadCredito),0)
                                   FROM tbl_recordsacademicos RA
                                   INNER JOIN tbl_asignaturas A ON RA.FK_Asignatura = A.PK_Asignatura
                                   INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
                                   INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
				    INNER JOIN tbl_pensums p ON p.pk_pensum = A.fk_pensum
                                   WHERE ug.FK_Usuario = $ci AND
                                   i.fk_atributo = $escuela
                                   AND RA.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)
                                   AND p.codigopropietario = $pensum;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function SinPeriodosAnteriores($ci, $ultPeriodo){
        
       $SQL = "SELECT *
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
            WHERE ug.fk_usuario = $ci
            AND fk_periodo < $ultPeriodo";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function listarPreinscriptos($per , $sede, $esc,$filtro=null){
        if ($filtro == "ins"){
            $SQL = "SELECT row_number() OVER () as num,*
		    FROM (
		    SELECT DISTINCT u.pk_usuario as cedula ,u.nombre as nombre,u.apellido as apellido,pen.nombre  as pensum,esc.escuela,u.correo,sem.id as sem
                    FROM tbl_recordsacademicos_preinscripcion rap 
                    JOIN tbl_inscripciones ins ON ins.pk_inscripcion = rap.fk_inscripcion
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                    JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                    JOIN vw_escuelas esc ON esc.pk_atributo = pen.fk_escuela
                    JOIN vw_semestres sem ON sem.pk_atributo = ins.fk_semestre 
                    WHERE ins.fk_periodo = {$per}
                    AND ins.fk_estructura = {$sede}
                    AND esc.pk_atributo = {$esc}
                    ";


        }else{
            $SQL = "SELECT row_number() OVER () as num,*
	            FROM (
			SELECT DISTINCT u.pk_usuario as cedula ,u.nombre as nombre,u.apellido as apellido,pen.nombre  as pensum,esc.escuela,u.correo,sem.id as sem,	
					CASE WHEN (SELECT rapC.pk_recordacademico
					FROM tbl_inscripciones insC 
					FULL OUTER JOIN tbl_recordsacademicos_preinscripcion rapC ON rapC.fk_inscripcion = insC.pk_inscripcion 
					WHERE insC.pk_inscripcion = ins.pk_inscripcion
					LIMIT 1 ) is null THEN 'No Preinscrito' ELSE 'Preinscrito' END as estado
                     FROM tbl_inscripciones ins 
                     JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                     JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo 
                     JOIN vw_escuelas esc ON esc.pk_atributo = pen.fk_escuela
                     JOIN vw_semestres sem ON sem.pk_atributo = ins.fk_semestre 
                     JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                     WHERE ins.fk_periodo = {$per}
                     AND ins.fk_estructura = {$sede}
                     AND esc.pk_atributo = {$esc}
                     ";

            if ($filtro == "fal"){
                $SQL .= "AND pk_inscripcion NOT IN (
                              SELECT fk_inscripcion
                              FROM tbl_recordsacademicos_preinscripcion
                              )";

            }
        }
        $SQL .= "ORDER BY pen.nombre , sem.id, u.pk_usuario DESC)  as sqt
		ORDER BY num";                
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;        
    }

    public function listarPreinscripcionMaterias($per,$sede,$esc){
        $SQL = "SELECT pen.nombre as pensum,mat.materia,count(a.pk_asignatura) as inscritos,a.codigopropietario,sem.id as semestre
                FROM tbl_recordsacademicos_preinscripcion rap
                JOIN tbl_inscripciones ins ON ins.pk_inscripcion = rap.fk_inscripcion
                JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                JOIN tbl_asignaturas a ON a.pk_asignatura = rap.fk_asignatura 
                JOIN vw_materias mat ON mat.pk_atributo = a.fk_materia
                JOIN vw_escuelas esc oN esc.pk_atributo = pen.fk_escuela
                JOIN vw_semestres sem ON sem.pk_atributo = a.fk_semestre
                WHERE ins.fk_periodo = {$per}
                AND ins.fk_estructura = {$sede}
                AND pen.fk_escuela = {$esc}
                GROUP BY a.pk_asignatura,esc.escuela,mat.materia,pen.nombre,a.codigopropietario,sem.id
                ORDER BY pen.nombre,sem.id,count(a.pk_asignatura) ";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;    
    }
    public function listarMateriaPreinscripcion($ci, $escuela, $ultPeriodo, $materias = ''){
        
       $SQL = "SELECT *,
        CASE WHEN estado = 864 THEN estado ELSE null END as new_estado
        FROM fn_xrxx_reinscripcion_lmcp_per_preinscripcion({$ci}, {$escuela}, {$ultPeriodo}, '{{$materias}}'::integer[])
                AS (pk_asignatura INTEGER,
                  codigopropietario VARCHAR(8),
                  materia VARCHAR(255),
                  unidadcredito SMALLINT,
                  semestre VARCHAR(16),
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
                  simultaneo_candidata boolean,
                  pensum integer,
                  estructura integer) 
                LEFT OUTER JOIN vw_materiasestados mest ON mest.pk_atributo = estado
                WHERE enable_uno = true AND enable_dos = true AND enable_tres = true AND enable_cuatro = true
                -- ORDER BY semestre::integer;
                ORDER BY new_estado DESC, semestre::integer, codigopropietario;
                ";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function revisarMateriaPreinscripcion($ci, $escuela, $ultPeriodo, $materias){
        $SQL = "SELECT *,
                      (SELECT uc FROM fn_xrxx_estudiante_calcular_ucps_pensum({$escuela}, sem.pk_atributo::integer, codigopropietario) AS (semestre SMALLINT, uc INT8)) as uc_limit
                    FROM(
                    SELECT count(pk_asignatura) as cant_materia, SUM(unidadcredito), semestre, pen.codigopropietario, estructura
                      FROM fn_xrxx_reinscripcion_lmcp_per_preinscripcion({$ci}, {$escuela}, {$ultPeriodo}, '{{$materias}}'::integer[])
                    AS (pk_asignatura INTEGER,
                      codigopropietario VARCHAR(8),
                      materia VARCHAR(255),
                      unidadcredito SMALLINT,
                      semestre VARCHAR(16),
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
                      simultaneo_candidata boolean,
                      pensum integer,
                      estructura integer)
                    LEFT OUTER JOIN vw_materiasestados mest ON mest.pk_atributo = estado
                    JOIN tbl_pensums pen ON pen.pk_pensum = pensum
                    WHERE enable_uno = true AND enable_dos = true AND enable_tres = true AND enable_cuatro = true";
        if(isset($materias))
            $SQL .= " AND pk_asignatura IN ({$materias})";

        $SQL .= "   
                    GROUP BY semestre, pen.codigopropietario, estructura
                    ) as sqt
                    JOIN vw_semestres sem ON sem.id = semestre::integer
                    ORDER BY sum DESC, semestre DESC
            ";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    function insertarPreinscripcion($ci, $escuela, $ultPeriodo, $materias){
        $SQL = "DELETE FROM tbl_recordsacademicos_preinscripcion WHERE pk_recordacademico IN(
                    SELECT pk_recordacademico
                    FROM tbl_recordsacademicos_preinscripcion ra_pre
                    JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra_pre.fk_inscripcion
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                WHERE fk_asignatura NOT IN({$materias})
                    AND ug.fk_usuario = {$ci}
                    AND ins.fk_atributo = {$escuela}
                    AND ins.fk_periodo = {$ultPeriodo}
            )
                ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        $SQL = "
        INSERT INTO tbl_recordsacademicos_preinscripcion (fk_atributo, calificacion, fk_asignatura, fk_inscripcion)
        SELECT 904 as fk_atributo, 
                0 as calificacion,
                pk_asignatura as fk_asignatura,
                pk_inscripcion as fk_inscripcion
          FROM fn_xrxx_reinscripcion_lmcp_per_preinscripcion({$ci},
               {$escuela}, {$ultPeriodo}, '{{$materias}}'::integer[]
             )
               AS (
                 pk_asignatura INTEGER,
                 codigopropietario VARCHAR(8), materia VARCHAR(255), unidadcredito SMALLINT,
                 semestre VARCHAR(16), prelacion TEXT[],
                 semestrerequisito TEXT, uc TEXT, enable_uno boolean, enable_dos boolean,
                 enable_tres boolean, enable_cuatro boolean, turno TEXT[], seccion TEXT[],
                 inscripcion boolean, calificacion smallint, estado smallint,
                 simultaneo boolean, simultaneo_candidata boolean, pensum integer,
                 estructura integer
               )
          LEFT OUTER JOIN vw_materiasestados mest
            ON mest.pk_atributo = estado
          JOIN tbl_pensums pen
            ON pen.pk_pensum = pensum
          JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = {$ci}
          JOIN tbl_inscripciones ins ON ins.fk_periodo = {$ultPeriodo} AND ins.fk_pensum = pen.pk_pensum AND ins.fk_usuariogrupo = ug.pk_usuariogrupo AND ins.fk_atributo = {$escuela}
          LEFT OUTER JOIN tbl_recordsacademicos_preinscripcion ra_pre ON ra_pre.fk_inscripcion = ins.pk_inscripcion AND pk_asignatura = ra_pre.fk_asignatura
         WHERE enable_uno    = true
           AND enable_dos    = true
           AND enable_tres   = true
           AND enable_cuatro = true
           AND pk_asignatura IN ({$materias})
           AND ra_pre.pk_recordacademico IS NULL;
        ";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    function revisarPreinscrito($ci, $escuela, $ultPeriodo){

        $SQL = "SELECT COUNT(DISTINCT pk_recordacademico) > 0  as check
                FROM tbl_recordsacademicos_preinscripcion ra_pre
                  JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra_pre.fk_inscripcion
                  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                WHERE ins.fk_periodo = {$ultPeriodo}
                  AND ug.fk_usuario = {$ci}
                  AND ins.fk_atributo = {$escuela};
                ";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    function obtenerCasos($per){
        $SQL ="SELECT DISTINCT fk_usuario, RANDOM()
                FROM tbl_inscripciones ins
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                WHERE ins.fk_periodo = {$per}
                ORDER BY 2
                LIMIT 1";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['fk_usuario'];
    }

    function periodoEnCurso(){
        $SQL ="SELECT *
                    FROM tbl_periodos
                    WHERE now() >= fechainicio AND now() <= fechafin;
                ";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    function getUCACEscuelaPensum($ci,$escuela,$pensum){

        $SQL = " SELECT fn_xrxx_estudiante_calcular_ucac_escuela_pensum({$ci}, {$escuela}, {$pensum})";

           return $this->_db->fetchOne($SQL);


        return $results;

    }

        // Retorna cuales son las materias que el estudiante puede inscribir.
    function MateriasInscripcion($ci,$escuela,$ultimoperiodo) {
        if(isset($escuela)) {
            $SQL = "SELECT * FROM (SELECT * FROM fn_xrxx_reinscripcion_lmcp_per($ci,$escuela,$ultimoperiodo)
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
                                 simultaneo_candidata boolean)) AS materiasins
                                 JOIN tbl_asignaturas asi ON asi.pk_asignatura = materiasins.pk_asignatura
                                 WHERE asi.fk_materia not IN (9724,1701)
                                 ORDER BY fk_Semestre ASC;";
//var_dump($SQL);die;

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();


        return $results;
        }
    } 


    //Retorna las unidades aprobadas por escuela

    public function totalAsignaturasAprobadas($ci,$escuela){
      $SQL = "SELECT COUNT(rec.*) as total
                      FROM tbl_recordsacademicos rec 
                      JOIN tbl_inscripciones ins ON ins.pk_inscripcion = rec.fk_inscripcion
                      JOIN tbl_asignaturas ag ON ag.pk_asignatura = rec.fk_asignatura
                      JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                      JOIN tbl_pensums pen ON pen.pk_pensum = ag.fk_pensum
                      WHERE ug.fk_usuario = $ci
                      AND pen.pk_pensum = ( SELECT ins.fk_pensum
                            FROM tbl_inscripciones ins 
                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                            WHERE ug.fk_usuario = $ci
                            ORDER BY ins.fk_periodo DESC 
                            LIMIT 1
                            ) 
                      AND pen.fk_escuela = $escuela
                AND     ( (( rec.fk_atributo = 862) AND (rec.calificacion >= 10))
        OR rec.fk_atributo = 861 OR rec.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos ))";
    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();


        return $results;
    }

    //Asignaturas de a carrera
     public function totalAsignaturas($ci){
              $SQL = "  SELECT COUNT (pk_asignatura) as total
                FROM tbl_asignaturas ag
                WHERE ag.fk_pensum = (SELECT ins.fk_pensum
                                                FROM tbl_inscripciones ins 
                                                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                                WHERE ug.fk_usuario = $ci
                                                ORDER BY ins.fk_periodo DESC 
                                                LIMIT 1)
                AND ag.fk_materia NOT IN (894,907,1701)";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }
       //Coincidencia de horarios 
     function verificarCoincidenciaHorario($Materias) {
        $SQL = "SELECT codigo, dia, horario
                                                FROM fn_xrxx_reinscripcion_validarhorarios({$this->UltimoPeriodoInscrito}, $Materias, {$this->SedeCodigo})
                                                AS (Codigo VARCHAR,  Dia int8, Horario int8);";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
      //Obtener prelaciones
    function obtenerPrelaciones($iAsignatura) {
        $SQL = "SELECT ag1.codigopropietario
                         FROM tbl_prelaciones p
                         JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = p.fk_asignatura
                         JOIN tbl_asignaturas ag2 ON ag2.pk_asignatura = p.fk_asignaturaprelada
                         WHERE ag2.codigopropietario = '{$iAsignatura}'";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
        //Horario por materia. 
       public function Horarios($sAsignatura, $iSemestre, $iTurno, $iSeccion, $iSede) {
        $SQL = "SELECT dia, horainicio, horafin FROM fn_xrxx_reinscripcion_horario({$this->UltimoPeriodoInscrito}, '$sAsignatura', $iSemestre, $iTurno, $iSeccion, $iSede)
                         AS (dia VARCHAR(255), horainicio TIME, horafin TIME);";
//
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

        public function getAsignatura($semestre,$turno,$seccion,$codigopropietario,$periodo){
            $SQL = "SELECT DISTINCT pk_asignatura FROM tbl_asignaturas a
                    JOIN tbl_asignaciones asi ON asi.fk_asignatura = a.pk_asignatura
                    WHERE asi.fk_semestre = {$semestre}
                    AND fk_turno = {$turno}
                    AND fk_seccion = {$seccion}
                    AND codigopropietario = '{$codigopropietario}'
                    AND asi.fk_periodo = {$periodo}; ";

            return $this->_db->fetchOne($SQL);        
        }

        public function unidadcreditoporsemestre($ci,$escuela,$semestre,$sede,$periodo,$codpensum){
            $SQL = "SELECT * FROM fn_xrxx_estudiante_calcular_ucps($ci, $escuela, $semestre, $sede , $periodo, $codpensum) AS (semestre SMALLINT, uc INT8)";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();
            return $results;

        }

        public function getAsignacion($data){
            $count = count($data) - 1;
            foreach ($data as $key => $value) {
                $asignatura = $value['asignatura'];
                $seccion    = $value['seccion'];
                $turno      = $value['turno'];
                $semestre   = $value['semestre'];
                $periodo    = $value['periodo'];
                $SQL .= "SELECT * 
                        FROM(SELECT pk_asignacion
                            FROM tbl_asignaciones
                            WHERE fk_asignatura IN({$asignatura})
                            AND fk_semestre IN ({$semestre})
                            AND fk_seccion IN ({$seccion})
                            AND fk_turno IN ({$turno})
                            AND fk_periodo = {$periodo}
                            ORDER BY pk_asignacion ASC
                            LIMIT 1) AS sqt";

                if($key < $count){
                    $SQL .= "\n UNION ALL \n";
                }
                else{
                    $SQL .= "\n ORDER BY 1 \n";
                }
            }


            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getUbicacion($pensum,$periodo, $sede, $asignaturas){
            $SQL = "  SELECT  distinct ts.pk_asignatura,ta1.fk_turno,ta1.fk_seccion, ts.fk_semestre
                      FROM tbl_asignaturas ts 
                      JOIN tbl_asignaciones ta1 ON ts.pk_asignatura = ta1.fk_asignatura
                      JOIN tbl_pensums tp1 ON ts.fk_pensum = tp1.pk_pensum
                      JOIN tbl_usuariosgrupos tg ON ta1.fk_usuariogrupo = tg.pk_usuariogrupo
                      JOIN tbl_usuarios tu ON tg.fk_usuario = tu.pk_usuario
                      JOIN tbl_estructuras te1 ON ta1.fk_estructura = te1.pk_estructura
                      JOIN tbl_estructuras te2 ON te1.fk_estructura = te2.pk_estructura
                      WHERE tp1.pk_pensum = {$pensum}
                      AND ta1.fk_periodo = {$periodo}
                      AND te2.fk_estructura = {$sede}
                      /* Materias Aprobadas por el Estudiante */
                      AND ts.pk_asignatura IN ({$asignaturas}) ;";
                   
            return $this->_db->fetchAll($SQL);        
        }

        public function getSecciones($pensum,$periodo,$sede,$semestre,$turno,$materia){
            $SQL= " SELECT distinct ta1.fk_seccion as valor,atr.valor as atributo
          FROM tbl_asignaturas ts 
          JOIN tbl_asignaciones ta1  ON ts.pk_asignatura    = ta1.fk_asignatura
          JOIN tbl_pensums tp1       ON ts.fk_pensum        = tp1.pk_pensum
          JOIN tbl_usuariosgrupos tg ON ta1.fk_usuariogrupo = tg.pk_usuariogrupo
          JOIN tbl_usuarios tu       ON tg.fk_usuario       = tu.pk_usuario
          JOIN tbl_estructuras te1   ON ta1.fk_estructura   = te1.pk_estructura
          JOIN tbl_estructuras te2   ON te1.fk_estructura   = te2.pk_estructura
          JOIN vw_semestres vsem     ON vsem.pk_atributo    = ts.fk_semestre 
          JOIN tbl_atributos atr     ON atr.pk_atributo     = ta1.fk_seccion
          WHERE tp1.pk_pensum   = {$pensum}
          AND ta1.fk_periodo    = {$periodo}
          AND te2.fk_estructura = {$sede}
          AND ta1.fk_semestre   = {$semestre}
          AND ta1.fk_turno      = {$turno}
          AND ts.codigopropietario   IN ('{$materia}')";
          //var_dump($SQL);die;
        return $this->_db->fetchAll($SQL);  
        }

         public function SemUb($materia){
            if($materia == false){
                $materia = "'0'";
            }
            $SQL= "SELECT vsem.pk_atributo as valor, atr2.valor as nombresem, vsem.id as numsem FROM (SELECT  asi.fk_semestre, SUM (asi.unidadcredito)
                    FROM tbl_asignaturas asi
                    JOIN tbl_atributos atr ON atr.pk_atributo = asi.fk_materia
                    JOIN tbl_atributos atr2 ON atr2.pk_atributo = asi.fk_semestre
                    JOIN vw_Semestres vsem on vsem.pk_atributo= asi.fk_semestre
                    Where asi.codigopropietario IN ({$materia})
                     GROUP BY asi.fk_semestre
                    ORDER BY sum desc, asi.fk_semestre desc LIMIT 1) as semub
                    JOIN tbl_atributos atr2 ON atr2.pk_atributo = semub.fk_semestre
                    JOIN vw_Semestres vsem on vsem.pk_atributo= semub.fk_semestre";
            return $this->_db->fetchAll($SQL);  
        }

        public function getpk($id){
        $SQL="SELECT pk_asignatura FROM tbl_asignaturas asi where asi.codigopropietario = '{$id}';";
         return $this->_db->fetchOne($SQL);  
        }

        public function matRetirada($id,$cod,$periodo){
        $SQL="SELECT asi.codigopropietario AS estado FROM tbl_recordsacademicos ra
              JOIN tbl_asignaturas asi   ON asi.pk_asignatura  = ra.fk_asignatura
              JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
              JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
              WHERE ug.fk_usuario = {$id} AND asi.codigopropietario = '{$cod}'
              AND ins.fk_periodo <> {$periodo}
              AND ra.fk_atributo <>863
              AND asi.codigopropietario NOT IN (SELECT asi.codigopropietario FROM tbl_recordsacademicos ra
              JOIN tbl_asignaturas asi   ON asi.pk_asignatura = ra.fk_asignatura
              JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
              JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
              WHERE ug.fk_usuario = {$id} AND asi.codigopropietario ='{$cod}' AND ra.fk_atributo = 862 AND ra.calificacion >=10 AND ra.fk_atributo <>863)
              ORDER BY fk_periodo DESC
              LIMIT 1;";
              
         return $this->_db->fetchOne($SQL);  
        }

        public function Prelmateria($materia){
        $SQL= "SELECT asi2.codigopropietario AS codigopropietario FROM tbl_asignaturas asi
              JOIN tbl_prelaciones pre  ON asi.pk_asignatura  = pre.fk_asignatura
              JOIN tbl_asignaturas asi2 ON asi2.pk_asignatura = pre.fk_asignaturaprelada
              WHERE asi.pk_asignatura = {$materia};";

         return $this->_db->fetchAll($SQL);  
        }

        public function Simultaneos($ci, $pensum, $ret){
        if($ret ==NULL){
            $ret  = "'99999999'";
        }
        $SQL= "SELECT distinct asi.codigopropietario, asi.unidadcredito, asi2.codigopropietario as habilitante from tbl_asignaturas asi
                join tbl_pensums pen on pen.pk_pensum  = asi.fk_pensum
                join tbl_prelaciones pre on pre.fk_asignatura = asi.pk_asignatura
                join tbl_asignaturas asi2 on asi2.pk_asignatura = pre.fk_asignaturaprelada
                join tbl_atributos atr on atr.pk_atributo = asi.fk_materia
                where pen.pk_pensum = {$pensum} and asi.codigopropietario not in (
                select asi.codigopropietario from tbl_asignaturas asi 
                join tbl_prelaciones pre on pre.fk_asignatura = asi.pk_asignatura
                join tbl_asignaturas asi2 on asi2.pk_asignatura = pre.fk_asignaturaprelada
                join tbl_pensums pen on pen.pk_pensum = asi.fk_pensum
                join tbl_atributos atr on atr.pk_atributo = asi.fk_materia
                join tbl_atributos atr2 on atr2.pk_atributo = asi2.fk_materia
                where pen.pk_pensum = {$pensum} and (asi2.codigopropietario not in ({$ret})
                ));";

        return $this->_db->fetchAll($SQL);  
        }

        public function aptitudsimultaneos ($pensum, $noapr){
            if($noapr== NULL){
                $noapr = "'99999999'";
            }
            $SQL="SELECT distinct asi.codigopropietario, asi2.codigopropietario as apta from tbl_asignaturas asi 
                  join tbl_prelaciones pre on pre.fk_asignatura = asi.pk_asignatura
                  join tbl_asignaturas asi2 on asi2.pk_asignatura = pre.fk_asignaturaprelada
                  join tbl_pensums pen on pen.pk_pensum = asi.fk_pensum
                  join tbl_atributos atr on atr.pk_atributo = asi.fk_materia
                  join tbl_atributos atr2 on atr2.pk_atributo = asi2.fk_materia
                  where pen.pk_pensum = {$pensum} and asi2.codigopropietario in ({$noapr});";

            return $this->_db->fetchAll($SQL); 
        }

        public function materiasaplazadas($ci,$escuela,$periodo,$sede){
            if ($escuela ==NULL && $periodo == NULL && $sede == NULL){
                $escuela =99999;
                $periodo = 99999;
                $sede = 99999;
            }
            $SQL = "SELECT  ((aprobadas * 100) / total) AS porc_aprobadas,
                            ((reprobadas * 100) / total) AS porc_reprobadas
                    FROM  (SELECT (SELECT COUNT(tr.pk_recordacademico)
                        FROM tbl_recordsacademicos tr 
                        WHERE tr.fk_asignatura NOT IN (14198,12569,12649,12410,11763,11840,11930,12016,12090,12168,14203,12657,12176,12418,12574,11771,11848,11938,12026,12095,14197,12568,12323,12249,12089,12650,12411,11764,11841,11931,12169,14204,12255,11772,11939,12419,12575,12658,12329,11849,12017,12027,12096,12177,12487,12981,12982,12983,12984,12986,12987,12985,12993,12994,12995,12996,12989,12990,12991,12992,12988,14128,14126,14129,14124,14125,14127,13065,13066,13067,13068,13064,13063,13073,13070,13071,13069,13072,13074,13838,14081,13760,14004,13269,13114,14087,13275,14012,13845,13769,13124,14036,14102,13792,13866,13291,13145)
                        AND tr.fk_atributo IN (862, 861)
                        AND tr.calificacion > 9
                        AND tr.fk_inscripcion = ti.pk_inscripcion
                        ) AS aprobadas,
                        (SELECT COUNT(tr.pk_recordacademico)
                        FROM tbl_recordsacademicos tr 
                        WHERE tr.fk_asignatura NOT IN (14198,12569,12649,12410,11763,11840,11930,12016,12090,12168,14203,12657,12176,12418,12574,11771,11848,11938,12026,12095,14197,12568,12323,12249,12089,12650,12411,11764,11841,11931,12169,14204,12255,11772,11939,12419,12575,12658,12329,11849,12017,12027,12096,12177,12487,12981,12982,12983,12984,12986,12987,12985,12993,12994,12995,12996,12989,12990,12991,12992,12988,14128,14126,14129,14124,14125,14127,13065,13066,13067,13068,13064,13063,13073,13070,13071,13069,13072,13074,13838,14081,13760,14004,13269,13114,14087,13275,14012,13845,13769,13124,14036,14102,13792,13866,13291,13145)
                        AND tr.fk_atributo IN (1699, 862)
                        AND tr.calificacion < 10
                        AND tr.fk_inscripcion = ti.pk_inscripcion
                        ) AS reprobadas,
                        (SELECT COUNT(tr.pk_recordacademico)
                        FROM tbl_recordsacademicos tr 
                        WHERE tr.fk_asignatura NOT IN (14198,12569,12649,12410,11763,11840,11930,12016,12090,12168,14203,12657,12176,12418,12574,11771,11848,11938,12026,12095,14197,12568,12323,12249,12089,12650,12411,11764,11841,11931,12169,14204,12255,11772,11939,12419,12575,12658,12329,11849,12017,12027,12096,12177,12487,12981,12982,12983,12984,12986,12987,12985,12993,12994,12995,12996,12989,12990,12991,12992,12988,14128,14126,14129,14124,14125,14127,13065,13066,13067,13068,13064,13063,13073,13070,13071,13069,13072,13074,13838,14081,13760,14004,13269,13114,14087,13275,14012,13845,13769,13124,14036,14102,13792,13866,13291,13145)
                        AND tr.fk_atributo NOt IN (863, 904)
                        AND tr.fk_inscripcion = ti.pk_inscripcion
                        ) AS total
                      FROM tbl_usuariosgrupos tg
                      JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                      WHERE tg.fk_usuario = {$ci}
                      AND ti.fk_atributo = {$escuela}
                      AND ti.fk_periodo = {$periodo} 
                      AND ti.fk_estructura = {$sede}
                    ) AS sqt;";
            return $this->_db->fetchAll($SQL);

        }

    public function countins($ci,$escuela,$periodo,$sede){
            $SQL = "SELECT count(pk_inscripcion)
                    FROM tbl_inscripciones ti
                    JOIN tbl_usuariosgrupos tg ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    WHERE tg.fk_usuario  = {$ci}
                    AND ti.fk_atributo   = {$escuela}
                    AND ti.fk_periodo    = {$periodo}
                    AND ti.fk_estructura = {$sede}";
            return $this->_db->fetchAll($SQL);
        }

    public function getAsignacionMateria($asignaciones){
        $SQL = "SELECT fk_asignatura, pk_asignacion 
                from tbl_asignaciones asna
                join tbl_asignaturas asi on asi.pk_asignatura = asna.fk_asignatura
                where asna.pk_asignacion in ({$asignaciones});
                ";
        return $this->_db->fetchAll($SQL);
    }
    public function getGrupos($ci){
            
            $SQL = "SELECT ug.fk_grupo, valor as grupo
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_atributos atr on atr.pk_Atributo = ug.fk_grupo
                    WHERE us.pk_usuario = {$ci}
                    ORDER BY 1;";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }

    public function getCodProp($pensum, $cod){
            
            $SQL = "SELECT ug.fk_grupo, valor as grupo
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_atributos atr on atr.pk_Atributo = ug.fk_grupo
                    WHERE us.pk_usuario = {$ci}
                    ORDER BY 1;";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }

    public function anterioresSimultaneos($pensum, $noapr){

        $SQL = "SELECT distinct asi.codigopropietario, asi.unidadcredito, asi2.codigopropietario as habilitante
                from tbl_asignaturas asi
                join tbl_pensums pen on pen.pk_pensum  = asi.fk_pensum
                join tbl_prelaciones pre on pre.fk_asignaturaprelada = asi.pk_asignatura
                join tbl_asignaturas asi2 on asi2.pk_asignatura = pre.fk_asignatura
                join tbl_atributos atr on atr.pk_atributo = asi.fk_materia
                where pen.pk_pensum = {$pensum} and asi.codigopropietario not in (
                select asi.codigopropietario from tbl_asignaturas asi 
                join tbl_prelaciones pre on pre.fk_asignaturaprelada = asi.pk_asignatura
                join tbl_asignaturas asi2 on asi2.pk_asignatura = pre.fk_asignatura
                join tbl_pensums pen on pen.pk_pensum = asi.fk_pensum
                join tbl_atributos atr on atr.pk_atributo = asi.fk_materia
                join tbl_atributos atr2 on atr2.pk_atributo = asi2.fk_materia
                where pen.pk_pensum = {$pensum} and (asi2.codigopropietario not in ('{$noapr}')
                ));";
                
        return $this->_db->fetchAll($SQL);
    }

    public function agregarObservacion($text, $pk_inscripcion){
        $SQL = "  UPDATE tbl_inscripciones 
                  SET observaciones = '{$text}'
                  WHERE pk_inscripcion = {$pk_inscripcion}
            
        ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

    }
 }
?>
