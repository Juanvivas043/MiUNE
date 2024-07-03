<?php
class Models_DbTable_Evaluaciontutores extends Zend_Db_Table {

  protected $_schema = 'produccion';
  protected $_name = 'tbl_usuarios';
  protected $_primary = 'pk_usuario';
  protected $_sequence = false;
  
  public function init() {
	$this->SwapBytes_Array = new SwapBytes_Array();
  }

   public function getEstudiantesTutorAcademico($ci){

    $SQL ="SELECT distinct ua.pk_usuario as cedula_tutor, ua.nombre as nombre, ua.apellido as apellido,
          u.pk_usuario as pk_usuario, u.nombre||','||u.apellido as estudiante, i.nombre as institucion,
          es.escuela,round(moodle.nota::numeric,2) as nota,(case when moodle.nota is not null then 'Evaluado' else 'Por Evaluar'end) as estado, moodle.usuario  
          from tbl_inscripcionespasantias ip
          join tbl_instituciones i  on i.pk_institucion = ip.fk_institucion
          join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
          join tbl_inscripciones ins on ins.pk_inscripcion = ra.fk_inscripcion
          join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ins.fk_usuariogrupo
          join tbl_usuarios u on u.pk_usuario  = ug.fk_usuario
          join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
          join tbl_pensums p on p.pk_pensum = a.fk_pensum
          join vw_escuelas es on es.pk_atributo = p.fk_escuela
          join tbl_usuariosgrupos uga on uga.pk_usuariogrupo = ip.fk_tutor_academico
          join tbl_usuarios ua on ua.pk_usuario = uga.fk_usuario
          LEFT OUTER JOIN( SELECT qp.*
  FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
    SELECT DISTINCT qas.responsesummary as usuario,((qat.sumgrades*q.grade)/q.sumgrades) as nota
			FROM mdl_quiz q
			JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
			JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
			JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
			JOIN mdl_user u ON u.id = qat.userid
			WHERE q.course = 215
			AND qas.responsesummary is not null
			AND qas.rightanswer =''cedula''
        '
         ) AS qp(usuario varchar , nota text) ) AS moodle ON moodle.usuario = ug.fk_usuario::varchar
                where a.fk_materia IN (716,717,848,9859)
          and ins.fk_periodo = (SELECT pk_periodo
                        FROM tbl_periodos 
                        WHERE current_date BETWEEN  fechainicio AND fechafin)
          and fk_tutor_academico = (select DISTINCT pk_usuariogrupo
                            from tbl_usuariosgrupos ug1
                            --join tbl_asignaciones ag1 on ag1.fk_usuariogrupo = ug1.pk_usuariogrupo
                            where fk_usuario = {$ci}
                            and ug1.fk_grupo = 854)
";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }



   public function getEstudiantesTutorEmpresarial($ci){

    $SQL ="SELECT distinct ue.pk_usuario as cedula_tutor, ue.nombre as nombre, ue.apellido as apellido,
          u.pk_usuario as pk_usuario, u.nombre||','||u.apellido as estudiante, i.nombre as institucion,
          es.escuela,moodle.nota,(case when moodle.nota is not null then 'Evaluado' else 'Por Evaluar'end) as estado  
          from tbl_inscripcionespasantias ip
          join tbl_instituciones i  on i.pk_institucion = ip.fk_institucion
          join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
          join tbl_inscripciones ins on ins.pk_inscripcion = ra.fk_inscripcion
          join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ins.fk_usuariogrupo
          join tbl_usuarios u on u.pk_usuario  = ug.fk_usuario
          join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
          join tbl_pensums p on p.pk_pensum = a.fk_pensum
          join vw_escuelas es on es.pk_atributo = p.fk_escuela
          join tbl_contactos c on c.pk_contacto = ip.fk_tutor_institucion
          join tbl_usuariosgrupos uge on uge.pk_usuariogrupo = c.fk_usuariogrupo
          join tbl_usuarios ue on ue.pk_usuario = uge.fk_usuario
               LEFT OUTER join ( SELECT qp.*
                    FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
		SELECT DISTINCT qas.responsesummary as usuario, ((qat.sumgrades*q.grade)/q.sumgrades) as nota
			FROM mdl_quiz q
			JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
			JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
			JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
			JOIN mdl_user u ON u.id = qat.userid
			WHERE q.course = 214
			AND qas.responsesummary is not null
			AND qas.rightanswer = ''cedula''
        ' ) AS qp( usuario bigint, nota text) ) AS moodle ON moodle.usuario = u.pk_usuario
                where a.fk_materia IN (716,717,848,9859)
          and ins.fk_periodo = (SELECT pk_periodo
                        FROM tbl_periodos 
                        WHERE current_date BETWEEN  fechainicio AND fechafin)
          and c.fk_usuariogrupo = (select DISTINCT pk_usuariogrupo
                            from tbl_usuariosgrupos ug1
                           where fk_usuario = {$ci}
                           and fk_grupo = 8237)";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();

   }

   
   public function getEstudianteInscritoPracticas($ci){

    $SQL ="SELECT DISTINCT u.pk_usuario as cedula, u.nombre as nombre , u.apellido as apellido, 
    es.escuela as escuela, me.valor, ra.fk_atributo as estado, i.fk_periodo as periodo
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i ON ra.fk_inscripcion = i.pk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN tbl_usuarios u ON  u.pk_usuario = ug.fk_usuario
    JOIN vw_escuelas es ON es.pk_atributo = i.fk_atributo
    JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
    JOIN vw_materiasestados me on me.pk_atributo = ra.fk_atributo
    WHERE ag.fk_materia IN (716,717,848,9859)
    and u.pk_usuario = {$ci} 
    order by i.fk_periodo desc
    limit 1";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

   public function getGrupoTutor($ci){

    $SQL = "select grupo
            from tbl_usuarios u
            join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
            join vw_grupos g on g.pk_atributo = ug.fk_grupo
            where pk_usuario = {$ci}";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();

   }

  public function getTutor($ci){

    $SQL = "select pk_usuario as cedula, nombre as nombre, apellido as apellido, passwordhash as pass
            from tbl_usuarios
            where pk_usuario = {$ci}";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();


  } 

  public function getEstudiante($pk){

    $SQL="select pk_usuario as cedula, nombre||','||apellido as estudiante
    from tbl_usuarios
    where pk_usuario = {$pk}";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();

  }

    
}

