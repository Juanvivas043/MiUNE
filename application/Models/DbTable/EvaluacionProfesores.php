<?php
class Models_DbTable_EvaluacionProfesores extends Zend_Db_Table {

    public function getGrupoDirector($ci){
         
         $SQL = "SELECT u.pk_usuario, atr.valor, atr.pk_atributo, ug.pk_usuariogrupo
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
                join tbl_atributos atr on atr.pk_atributo = ug.fk_grupo
                where u.pk_usuario = {$ci}
                and atr.pk_atributo in (20263,20264,20265,20266,20267,20268)";
                    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }

    public function getGrupoCoordinador($ci){
         
         $SQL = "SELECT u.pk_usuario, atr.valor, atr.pk_atributo, ug.pk_usuariogrupo
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
                join tbl_atributos atr on atr.pk_atributo = ug.fk_grupo
                where u.pk_usuario = {$ci}
                and atr.pk_atributo in (20269,20270)";
                    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }

    public function getUsuariogrupoProfesorByRecord($pk_recordacademico){
         
         $SQL = "SELECT pk_usuariogrupo from tbl_recordsacademicos ra
                join tbl_asignaciones asi on asi.pk_asignacion = ra.fk_asignacion
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = asi.fk_usuariogrupo
                where ra.pk_recordacademico = {$pk_recordacademico}";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    public function getMateriasProfesor($ci,$periodo){
         
         $SQL = "SELECT distinct u.nombre, u.apellido, u.pk_usuario, m.materia, e.escuela,s.valor
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
                join tbl_asignaciones asi on asi.fk_usuariogrupo = ug.pk_usuariogrupo
                join tbl_asignaturas asig on asig.pk_asignatura = asi.fk_asignatura
                join vw_materias m on m.pk_atributo = asig.fk_materia
                join tbl_pensums p on p.pk_pensum = asig.fk_pensum
                join vw_escuelas e on e.pk_atributo = p.fk_escuela
                join vw_secciones s on s.pk_atributo = asi.fk_seccion
                where asi.fk_periodo = {$periodo}
                and u.pk_usuario = {$ci}
                order by 1
                ";
                    
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }    

    public function getProfesorPorSede($periodo,$grupo,$pk_usuariogrupo){

        switch ($grupo) {
            case 20269:
                $sede = 7;
                break;
            case 20270:
                $sede = 8;
                break;
        }

         $SQL = "SELECT sqt.pk_usuario, sqt.nombre, sqt.pk_usuariogrupo,
                        array_to_string(ARRAY_AGG(sqt.materia),'\r\n \r\n') as materia,
                        array_to_string(ARRAY_AGG(sqt.escuela),'\r\n \r\n') as escuela,
                        sqt.estado
                from (
                SELECT u.pk_usuario, u.nombre || ' '||u.apellido as nombre, ug.pk_usuariogrupo,
                                        array_to_string(ARRAY_AGG(m.materia),', ') as materia,
                        --array_to_string(ARRAY_AGG(es.escuela),', ') as escuela,
                        es.escuela,
                                    case when ae.finalizada is null or ae.finalizada is false 
                                    then 'Por Evaluar' 
                                    else 'Evaluado' 
                                    end as estado
                            from tbl_usuarios u
                            join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
                            join tbl_asignaciones asi on asi.fk_usuariogrupo = ug.pk_usuariogrupo
                            join tbl_asignaturas asig on asig.pk_asignatura = asi.fk_asignatura
                            join vw_materias m on m.pk_atributo = asig.fk_materia
                            join vw_estructuras e on e.pk_aula = asi.fk_estructura
                            join tbl_pensums p on p.pk_pensum = asig.fk_pensum
                            join vw_escuelas es on es.pk_atributo = p.fk_escuela
                            left join tbl_asignacionesencuestas ae on ae.fk_usuariogrupo = {$pk_usuariogrupo} and ug.pk_usuariogrupo::TEXT = ae.comentario
                            where  asi.fk_periodo = {$periodo}
                            and e.pk_sede = {$sede}
                            and pk_usuario != 0
                            group by u.pk_usuario, u.nombre, u.apellido,ae.finalizada, es.escuela, ug.pk_usuariogrupo
                            order by 1,4,3
                            ) AS sqt
               group by sqt.pk_usuario, sqt.nombre, sqt.estado,sqt.pk_usuariogrupo
               order by 1 DESC";     

               //var_dump($SQL);die;              
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }  
        
    public function getProfesoresPorEscuela($periodo,$grupo,$pk_usuariogrupo){

        switch ($grupo) {
            case 20263:
                $escuela = 11;
                break;
            case 20264:
                $escuela = 12;
                break;
            case 20265:
                $escuela = 13;
                break;
            case 20266:
                $escuela = 16;
                break;
            case 20267:
                $escuela = 14;
                break;
            case 20268:
                $escuela = 15;
                break;

        }

        
        $SQL = "SELECT sqt.pk_usuario, sqt.nombre, array_to_string(ARRAY_AGG(sqt.materia),', ') as materia, sqt.pk_usuariogrupo, sqt.estado
                from (
                 SELECT distinct u.pk_usuario, u.nombre || ' ' || u.apellido as nombre, m.materia,ug.pk_usuariogrupo,
                                        case when ae.finalizada is null or ae.finalizada is false 
                                        then 'Por Evaluar' 
                                        else 'Evaluado' 
                                        end as estado
                                from tbl_usuarios u
                                join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
                                join tbl_asignaciones asi on asi.fk_usuariogrupo = ug.pk_usuariogrupo
                                join tbl_asignaturas asig on asig.pk_asignatura = asi.fk_asignatura
                                join vw_materias m on m.pk_atributo = asig.fk_materia
                                join tbl_pensums p on p.pk_pensum = asig.fk_pensum
                                join vw_escuelas e on e.pk_atributo = p.fk_escuela
                                join vw_secciones s on s.pk_atributo = asi.fk_seccion
                                join tbl_recordsacademicos ra on ra.fk_asignacion = asi.pk_asignacion
                                left join tbl_asignacionesencuestas ae on ae.fk_usuariogrupo = {$pk_usuariogrupo} and ug.pk_usuariogrupo::TEXT = ae.comentario
                                where asi.fk_periodo = {$periodo}
                                and e.pk_atributo = {$escuela}
                                and u.pk_usuario != 0
                                group by u.pk_usuario, u.nombre,u.apellido,  ae.finalizada,m.materia, ug.pk_usuariogrupo
                                order by 1
                       ) as sqt
                  group by sqt.pk_usuario, sqt.nombre, sqt.estado,sqt.pk_usuariogrupo
                 order by 1";

        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getProfesoresPorEstudiante($ci,$periodo){
        $SQL = "SELECT u1.pk_usuario, u1.nombre || ' ' || u1.apellido as nombre, m.materia,ra.pk_recordacademico,
                        case when ae.finalizada is null or ae.finalizada is false 
                        then 'Por Evaluar' 
                        else 'Evaluado' 
                        end as estado
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
                join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
                join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
                join tbl_asignaciones asi on asi.pk_asignacion = ra.fk_asignacion
                join tbl_asignaturas asig on asig.pk_asignatura = asi.fk_asignatura
                join vw_materias m on m.pk_atributo = asig.fk_materia
                join tbl_usuariosgrupos ug1 on ug1.pk_usuariogrupo = asi.fk_usuariogrupo
                join tbl_usuarios u1 on u1.pk_usuario = ug1.fk_usuario
                left join tbl_asignacionesencuestas ae on ae.fk_recordacademico = ra.pk_recordacademico
                where u.pk_usuario = {$ci}
                and i.fk_periodo = {$periodo}
                and asi.fk_periodo = {$periodo}
                group by u1.pk_usuario, u1.nombre,u1.apellido, m.materia, ae.finalizada,ra.pk_recordacademico
                order by 1";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getPreguntasEstudiantes($record, $fk_parte = null){
        $SQL = "SELECT p.pk_pregunta, p.valor as pregunta, atr.pk_atributo, atr.valor as parte, p.ordinal, pe.fk_parte
                from tbl_inscripciones i
                join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
                join tbl_inscripcionesencuestas ie on ie.fk_inscripcion = i.pk_inscripcion
                join tbl_encuestas e on e.pk_encuesta = ie.fk_encuesta
                join tbl_preguntasencuestas pe on e.pk_encuesta = pe.fk_encuesta
                join tbl_preguntas p on p.pk_pregunta = pe.fk_pregunta
                join tbl_atributos atr on atr.pk_atributo = pe.fk_parte
                where pk_recordacademico = {$record}
                ";
                if (!is_null($fk_parte)) {
                    $SQL .= "AND pe.fk_parte = {$fk_parte}
                    ";
                }
        $SQL .= "ORDER BY pe.fk_parte, p.ordinal";

        // var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getInscripcionEncuestaEstudiante($record){
        $SQL = "SELECT ra.pk_recordacademico, i.pk_inscripcion, ie.pk_inscripcionencuesta, ie.fk_encuesta, i.fk_usuariogrupo
                from tbl_inscripcionesencuestas ie
                join tbl_inscripciones i on i.pk_inscripcion = ie.fk_inscripcion
                join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
                where ra.pk_recordacademico = {$record}";

         //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getAsignacionEncuestaProf($fk_usuariogrupo){
        $SQL = "SELECT pk_asignacionencuesta, fk_encuesta, finalizada
                from tbl_asignacionesencuestas where fk_usuariogrupo = {$fk_usuariogrupo}";

         //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getAsignacionEncuestaDir($grupodirector,$grupoprofesor ){
        $SQL = "SELECT pk_asignacionencuesta, fk_encuesta, finalizada
                from tbl_asignacionesencuestas where fk_usuariogrupo = {$grupodirector}
                and comentario = {$grupoprofesor}::TEXT ";

         //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

     public function getPkAsignacionEncuesta($record){
        $SQL = "SELECT ae.pk_asignacionencuesta
                from tbl_inscripcionesencuestas ie
                join tbl_inscripciones i on i.pk_inscripcion = ie.fk_inscripcion
                join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
                left join tbl_asignacionesencuestas ae on ae.fk_inscripcionencuesta = ie.pk_inscripcionencuesta and ra.pk_recordacademico = ae.fk_recordacademico
                where ra.pk_recordacademico = {$record}";
     
         //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function insertPreEvaluacion($fk_inscripcionencuesta,$fk_recordacademico, $fk_usuariogrupo, $fk_encuesta, $comentario = NULL){

        $this->getAdapter()->beginTransaction();
        try {
            $SQL = "INSERT into tbl_asignacionesencuestas (fk_inscripcionencuesta,fk_recordacademico, finalizada, fk_usuariogrupo, fk_encuesta, comentario)
                    values({$fk_inscripcionencuesta}, {$fk_recordacademico}, false, {$fk_usuariogrupo}, {$fk_encuesta}, {$comentario})";

                    //var_dump($SQL);die;

            $this->_db->query($SQL);

            $this->getAdapter()->commit();
            return true;
            }
        catch(Exception $ex) {
            $this->getAdapter()->rollback();
            throw new Exception("Error de Inserción de Respuestas", 1);
            return false;
        }
    }

    public function insertPreEvaluacionDir($fk_usuariogrupo, $fk_encuesta, $comentario = NULL){

        $this->getAdapter()->beginTransaction();
        try {
            $SQL = "INSERT into tbl_asignacionesencuestas (finalizada, fk_usuariogrupo, fk_encuesta, comentario)
                    values(false, {$fk_usuariogrupo}, {$fk_encuesta}, {$comentario})";

                    //var_dump($SQL);die;

            $this->_db->query($SQL);

            $this->getAdapter()->commit();
            return true;
            }
        catch(Exception $ex) {
            $this->getAdapter()->rollback();
            throw new Exception("Error de Inserción de Respuestas", 1);
            return false;
        }
    }

    public function insertRespuestas($pk_asignacionencuesta, $fk_encuesta, $array_respuestas){

        $this->getAdapter()->beginTransaction();
        try {

            $SQLi = "INSERT into tbl_respuestas(fk_asignacionencuesta, fk_preguntaopcion)
                VALUES";
        
            foreach ($array_respuestas as $key => $value) {
                $SQLi.= "({$pk_asignacionencuesta}, (SELECT po.pk_preguntaopcion
                                                            from tbl_preguntasopciones po
                                                            join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                                                            where pe.fk_encuesta = {$fk_encuesta}
                                                            and pe.fk_pregunta = {$value['pk']}
                                                            and po.peso = {$value['value']})
                                )," ;
            }

            $SQL =rtrim($SQLi,", ");    
            //var_dump($SQL);die;
            $this->_db->query($SQL);

            $this->getAdapter()->commit();
            return true;
        }
        catch(Exception $ex) {
            $this->getAdapter()->rollback();
            throw new Exception("Error de Inserción de Respuestas", 1);
            return false;
        }     
    }

    public function updateAsignacionEncuesta($pk_asignacionencuesta){
        $this->getAdapter()->beginTransaction();
        try {

            $SQL = "UPDATE tbl_asignacionesencuestas
                    set finalizada = true,
                        fecha = now()
                    where pk_asignacionencuesta = {$pk_asignacionencuesta}
                    ";
            //var_dump($SQL);die;
            $this->_db->query($SQL);

            $this->getAdapter()->commit();
            return true;
        }
        catch(Exception $ex) {
            $this->getAdapter()->rollback();
            throw new Exception("Error de Update de Asignacion Encuesta", 1);
            return false;
        }

    }

    public function errorEncuesta($pk_asignacionencuesta){

        $this->getAdapter()->beginTransaction();
        try {

            $SQL = "UPDATE tbl_asignacionesencuestas
                    set finalizada = false
                    where pk_asignacionencuesta = {$pk_asignacionencuesta}";
        
            $SQL2 = "DELETE FROM tbl_respuestas where fk_asignacionencuesta = {$pk_asignacionencuesta} " ;
//var_dump($SQL);die;
            $this->_db->query($SQL);
            $this->_db->query($SQL2);

            $this->getAdapter()->commit();
            return true;
        }
        catch(Exception $ex) {
            $this->getAdapter()->rollback();
            throw new Exception("Error de Inserción de Respuestas", 1);
            return false;
        }     
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

    public function getResultadosAll($periodo,$sede,$escuela){

        if ($sede == 0 || is_null($sede)) {
            $sede = '7,8';
        }
        if ($escuela == 0 || is_null($escuela)) {
            $escuela = '11,12,13,14,15,16';
        }

        $SQL = "SELECT u.pk_usuario, 
                (u.nombre ||' '||u.apellido) as nombre, 
                estudiantes.peso1_1, 
                estudiantes.peso1_2, 
                estudiantes.peso1_3, 
                profesores.peso2_1,
                profesores.peso2_2,
                profesores.peso2_3,
                profesores.peso2_4,
                directores.peso3_1,
                directores.peso3_2,
                directores.peso3_3,
                directores.peso3_4,
                coordinadores.peso4_1,
                coordinadores.peso4_2,
                round(((COALESCE(estudiantes.peso1_1+estudiantes.peso1_2+estudiantes.peso1_3,0))/3)+
                ((COALESCE(profesores.peso2_1+profesores.peso2_2+profesores.peso2_3+profesores.peso2_4,0))/4)+
                ((COALESCE(directores.peso3_1+directores.peso3_2+directores.peso3_3+directores.peso3_4,0))/4)+
                ((COALESCE(coordinadores.peso4_1+coordinadores.peso4_2,0))/2),2) as total
            from tbl_usuarios u
            join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
            join tbl_asignaciones asi on asi.fk_usuariogrupo = ug.pk_usuariogrupo
            JOIN vw_estructuras e on e.pk_aula = asi.fk_estructura
            join tbl_asignaturas asig on asig.pk_asignatura = asi.fk_asignatura
            join tbl_pensums p on p.pk_pensum = asig.fk_pensum
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso1_1, parte2.peso1_2, parte3.peso1_3
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 33
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 33
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                  join (
                select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_3, pe.fk_parte 
                from tbl_respuestas r
                join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                where ae.fk_encuesta = 33
                and pe.fk_parte = 20261
                group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
             ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
            ) estudiantes on u.pk_usuario = estudiantes.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso2_1, parte2.peso2_2, parte3.peso2_3, parte4.peso2_4
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_3, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20261
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
                join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_4, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20262
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte4 on parte4.pk_usuario = parte2.pk_usuario
            ) profesores on profesores.pk_usuario = u.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso3_1, parte2.peso3_2, parte3.peso3_3, parte4.peso3_4
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                  join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_3, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20261
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
                  join (
                select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_4, pe.fk_parte 
                from tbl_respuestas r
                join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                where ae.fk_encuesta = 35
                and pe.fk_parte = 20262
                group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
             ) as parte4 on parte4.pk_usuario = parte2.pk_usuario
            ) directores on directores.pk_usuario = u.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso4_1, parte2.peso4_2
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso4_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 36
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso4_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 36
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
            ) coordinadores on coordinadores.pk_usuario = u.pk_usuario
            where asi.fk_periodo = {$periodo}
            and e.pk_sede in ({$sede})
            and p.fk_escuela in ({$escuela})
            and u.pk_usuario != 0
            group by u.pk_usuario,u.nombre,u.apellido,estudiantes.peso1_1,estudiantes.peso1_2,estudiantes.peso1_3,profesores.peso2_1,profesores.peso2_2,profesores.peso2_3,profesores.peso2_4,
            directores.peso3_1,directores.peso3_2,directores.peso3_3,directores.peso3_4,coordinadores.peso4_1,coordinadores.peso4_2
            order by pk_usuario";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getResultadosProfesor($periodo,$ci){

        $SQL = "SELECT u.pk_usuario, 
                (u.nombre ||' '||u.apellido) as nombre, 
                round((SELECT AVG(c) FROM (VALUES(estudiantes.peso1_1),(profesores.peso2_1),(directores.peso3_1),(coordinadores.peso4_1)) T (c)),2) as ri,
                round((SELECT AVG(c) FROM (VALUES(estudiantes.peso1_2),(profesores.peso2_2),(directores.peso3_2),(coordinadores.peso4_2)) T (c)),2) as v,
                round((SELECT AVG(c) FROM (VALUES(estudiantes.peso1_3),(profesores.peso2_3),(directores.peso3_3)) T (c)),2) as DP,
                round((SELECT AVG(c) FROM (VALUES(profesores.peso2_4),(directores.peso3_4)) T (c)),2) as c,
                estudiantes.peso1_1, 
                estudiantes.peso1_2, 
                estudiantes.peso1_3, 
                profesores.peso2_1,
                profesores.peso2_2,
                profesores.peso2_3,
                profesores.peso2_4,
                directores.peso3_1,
                directores.peso3_2,
                directores.peso3_3,
                directores.peso3_4,
                coordinadores.peso4_1,
                coordinadores.peso4_2,
                round(((COALESCE(estudiantes.peso1_1+estudiantes.peso1_2+estudiantes.peso1_3,0))/3)+
                ((COALESCE(profesores.peso2_1+profesores.peso2_2+profesores.peso2_3+profesores.peso2_4,0))/4)+
                ((COALESCE(directores.peso3_1+directores.peso3_2+directores.peso3_3+directores.peso3_4,0))/4)+
                ((COALESCE(coordinadores.peso4_1+coordinadores.peso4_2,0))/2),2)::text ||'/16' as total
            from tbl_usuarios u
            join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
            join tbl_asignaciones asi on asi.fk_usuariogrupo = ug.pk_usuariogrupo
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso1_1, parte2.peso1_2, parte3.peso1_3
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 33
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 33
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                  join (
                select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso1_3, pe.fk_parte 
                from tbl_respuestas r
                join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                where ae.fk_encuesta = 33
                and pe.fk_parte = 20261
                group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
             ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
            ) estudiantes on u.pk_usuario = estudiantes.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso2_1, parte2.peso2_2, parte3.peso2_3, parte4.peso2_4
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_3, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20261
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
                join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso2_4, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 34
                    and pe.fk_parte = 20262
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte4 on parte4.pk_usuario = parte2.pk_usuario
            ) profesores on profesores.pk_usuario = u.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso3_1, parte2.peso3_2, parte3.peso3_3, parte4.peso3_4
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
                  join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_3, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 35
                    and pe.fk_parte = 20261
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte3 on parte3.pk_usuario = parte2.pk_usuario
                  join (
                select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso3_4, pe.fk_parte 
                from tbl_respuestas r
                join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                where ae.fk_encuesta = 35
                and pe.fk_parte = 20262
                group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
             ) as parte4 on parte4.pk_usuario = parte2.pk_usuario
            ) directores on directores.pk_usuario = u.pk_usuario
            left join (
                select parte1.pk_usuario, parte1.nombre,parte1.apellido, parte1.peso4_1, parte2.peso4_2
                from (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso4_1, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 36
                    and pe.fk_parte = 20259
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte1
                 join (
                    select u.pk_usuario, u.nombre,u.apellido, round(avg(po.peso),2) as peso4_2, pe.fk_parte 
                    from tbl_respuestas r
                    join tbl_preguntasopciones po on po.pk_preguntaopcion = r.fk_preguntaopcion
                    join tbl_preguntasencuestas pe on pe.pk_preguntaencuesta = po.fk_preguntaencuesta
                    join tbl_asignacionesencuestas ae on ae.pk_asignacionencuesta = r.fk_asignacionencuesta
                    join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ae.comentario::int
                    join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                    where ae.fk_encuesta = 36
                    and pe.fk_parte = 20260
                    group by u.pk_usuario, u.nombre,u.apellido, pe.fk_parte
                 ) as parte2 on parte1.pk_usuario = parte2.pk_usuario
            ) coordinadores on coordinadores.pk_usuario = u.pk_usuario
            where asi.fk_periodo = {$periodo}
            and u.pk_usuario = {$ci}
            group by u.pk_usuario,u.nombre,u.apellido,estudiantes.peso1_1,estudiantes.peso1_2,estudiantes.peso1_3,profesores.peso2_1,profesores.peso2_2,profesores.peso2_3,profesores.peso2_4,
            directores.peso3_1,directores.peso3_2,directores.peso3_3,directores.peso3_4,coordinadores.peso4_1,coordinadores.peso4_2
            order by pk_usuario";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
 }
?>
