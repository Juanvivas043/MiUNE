
<?php

class Models_DbTable_Preinscripcionproyecto extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_asignacionesproyectos';
    protected $_primary  = 'pk_asignacionproyecto';
    protected $_sequence = false;
   
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
        
 public function getInfoEstudiante($ci){
     
     $SQL="select DISTINCT u.pk_usuario as cedula,pk_recordacademico, u.nombre as nombre, 
           u.apellido as apellido, ve.escuela as escuela, p.fk_escuela as fkescuela,
           m.materia as materia,
           i.fk_periodo as periodo, u.direccion as direccion, telefono, telefono_movil, correo,
           fn_xrxx_estudiante_sem_ubicacion_periodod2(u.pk_usuario, p.fk_escuela, i.fk_periodo, p.codigopropietario) as semestre
           ,i.fk_usuariogrupo as fk_usuariogrupo, 
           asi.fk_semestre as fk_semestre,
            i.fk_pensum as fk_pensum,
            i.fk_estructura as fk_estructura,
            pk_inscripcion as inscripcion,
            current_date as fecha
           from tbl_usuarios u 
           join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
           join tbl_inscripciones i on ug.pk_usuariogrupo = i.fk_usuariogrupo
           join tbl_recordsacademicos rc on rc.fk_inscripcion = i.pk_inscripcion
           join tbl_asignaturas a on a.pk_asignatura = rc.fk_asignatura
           join tbl_asignaciones asi on asi.fk_asignatura = a.pk_asignatura
           join tbl_horarios h on h.pk_horario = asi.fk_horario
           join tbl_pensums p on p.pk_pensum = a.fk_pensum
           join tbl_atributos atr on atr.pk_atributo = i.fk_atributo
           join vw_semestres sm on sm.pk_atributo = i.fk_semestre
           join vw_escuelas ve on ve.pk_atributo = p.fk_escuela
           join vw_materias m on m.pk_atributo = a.fk_materia
           join vw_escuelas ves on ves.pk_atributo = p.fk_escuela
           where a.fk_materia IN (8219,9737)     
           and i.fk_periodo  <= (SELECT pk_periodo
                   FROM tbl_periodos 
                   WHERE current_date BETWEEN  fechainicio AND fechafin)
           and rc.calificacion > 10
           and rc.fk_atributo = 862
           and u.pk_usuario = {$ci}";
           
          
               
            
            $results=$this->_db->query($SQL);
            return (array) $results->fetchAll();
     
     
     
 }

 public function getInfoEstudianteListo($ci){


     $SQL="select DISTINCT u.pk_usuario as cedula,pk_recordacademico, u.nombre as nombre, 
           u.apellido as apellido, ve.escuela as escuela, p.fk_escuela as fkescuela,
           m.materia as materia,
           i.fk_periodo as periodo, u.direccion as direccion, telefono, telefono_movil, correo,
           fn_xrxx_estudiante_sem_ubicacion_periodod2(u.pk_usuario, p.fk_escuela, i.fk_periodo, p.codigopropietario) as semestre
           ,i.fk_usuariogrupo as fk_usuariogrupo, 
           asi.fk_semestre as fk_semestre,
            i.fk_pensum as fk_pensum,
            i.fk_estructura as fk_estructura
           from tbl_usuarios u 
           join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario
           join tbl_inscripciones i on ug.pk_usuariogrupo = i.fk_usuariogrupo
           join tbl_recordsacademicos rc on rc.fk_inscripcion = i.pk_inscripcion
           join tbl_asignaturas a on a.pk_asignatura = rc.fk_asignatura
           join tbl_asignaciones asi on asi.fk_asignatura = a.pk_asignatura
           join tbl_horarios h on h.pk_horario = asi.fk_horario
           join tbl_pensums p on p.pk_pensum = a.fk_pensum
           join tbl_atributos atr on atr.pk_atributo = i.fk_atributo
           join vw_semestres sm on sm.pk_atributo = i.fk_semestre
           join vw_escuelas ve on ve.pk_atributo = p.fk_escuela
           join vw_materias m on m.pk_atributo = a.fk_materia
           join vw_escuelas ves on ves.pk_atributo = p.fk_escuela
           where a.fk_materia IN (718,913,9738)    
           and i.fk_periodo  <= (SELECT pk_periodo
                   FROM tbl_periodos 
                   WHERE current_date BETWEEN  fechainicio AND fechafin)
           and rc.calificacion > 10
           and rc.fk_atributo = 862
           and u.pk_usuario = {$ci}";

           

           $results=$this->_db->query($SQL);
            return (array) $results->fetchAll();


 }
 
 public function getInfoEstudianteListoPasantias($ci){


     $SQL="select pk_inscripcionpasantia, pk_usuario as cedula 
          from tbl_usuarios u
          join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
          join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
          join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
          join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
          join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
          where u.pk_usuario = {$ci}
          and i.fk_periodo  <= (SELECT pk_periodo
                             FROM tbl_periodos 
                             WHERE current_date BETWEEN  fechainicio AND fechafin)
          and a.fk_materia IN (718,913,9738)";

           

           $results=$this->_db->query($SQL);
            return (array) $results->fetchAll();


 }

 public function getProyectos($escuela){
     
 
         $SQL ="select pk_asignacionproyecto, proyecto,institucion,
case when tutoracademico is null then 'Tutor aun sin Asignar' else tutoracademico end
,tutorinstitucional,horario, cupos
from(
select DISTINCT (
                SELECT count (ip1.pk_inscripcionpasantia) as cuenta
                FROM tbl_inscripcionespasantias ip1
                JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                AND ap1.fk_escuela = {$escuela}
                AND ap1.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1))||' de '||ap.cupos as cupos, pk_asignacionproyecto,
                pr.nombre as proyecto, 
    ins.nombre as institucion, 
    atr.valor as horario,
    ua.nombre||','||ua.apellido as tutoracademico, 
      u.nombre||','||u.apellido as tutorinstitucional      
from tbl_asignacionesproyectos ap
join tbl_proyectos pr on pr.pk_proyecto = ap.fk_proyecto
join tbl_instituciones ins on ins.pk_institucion  = pr.fk_institucion
join tbl_pensums p on p.fk_escuela = ap.fk_escuela
join tbl_asignaturas a on a.fk_pensum = p.pk_pensum
join tbl_recordsacademicos ra on ra.fk_asignatura = a.pk_asignatura
join vw_escuelas es on es.pk_atributo = p.fk_escuela
join tbl_atributos atr on atr.pk_atributo = ap.fk_tipohorario
join vw_materias as m on m.pk_atributo = a.fk_materia
join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
join tbl_contactos c on c.fk_institucion = ins.pk_institucion
JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
JOIN tbl_usuarios u ON  u.pk_usuario      = ug.fk_usuario
LEFT OUTER JOIN tbl_inscripcionespasantias ip on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
LEFT OUTER JOIN tbl_usuariosgrupos ugA on ugA.pk_usuariogrupo = ip.fk_tutor_academico
LEFT OUTER JOIN tbl_usuarios ua on ua.pk_usuario = ugA.fk_usuario
where a.fk_materia IN (8219,9737) 
    and ap.fk_escuela = {$escuela}
    and ap.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
 ) AS subj   ";
                              
            
      
            $results=$this->_db->query($SQL);
            return (array) $results->fetchAll();
    } 
    
    
    public function getTutor($pk){
        
        $SQL="SELECT c.pk_contacto as tutor_ins, c.fk_institucion,c.fk_usuariogrupo,i.nombre AS institucion,
            u.nombre || ' ' || u.apellido as nombre_tutor, pk_asignacionproyecto
            FROM tbl_contactos c
            JOIN tbl_instituciones i ON  i.pk_institucion  = c.fk_institucion
            JOIN tbl_proyectos p on p.fk_institucion = i.pk_institucion
            JOIN tbl_asignacionesproyectos ap on ap.fk_proyecto = p.pk_proyecto
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
            JOIN tbl_usuarios u ON  u.pk_usuario      = ug.fk_usuario
            where pk_asignacionproyecto = {$pk}";
            
            $results=$this->_db->query($SQL);
            return (array) $results->fetchAll();
        
    }
 
 public function insertData ($record, $pk, $id_ins, $tutor_ins){
     
     $SQL="INSERT INTO tbl_inscripcionespasantias (fk_recordacademico, fk_asignacionproyecto, fk_institucion, fk_tutor_institucion,fk_tutor_academico)
          VALUES($record, $pk, $id_ins, $tutor_ins,38971)";
     
     $this->_db->query($SQL);
            
 }
 

 public function insertRecord($asignatura,$inscripcion){
     
      //904 Materia pre-inscrita
      $SQL="INSERT INTO tbl_recordsacademicos (fk_atributo,fk_asignatura,fk_inscripcion)
            VALUES (904, $asignatura,$inscripcion)";
      $this->_db->query($SQL);      
 } 

 public function getRecord($asignatura,$inscripcion){

       $SQL="SELECT DISTINCT pk_recordacademico
             FROM tbl_recordsacademicos
             where fk_asignatura = {$asignatura}
             AND fk_inscripcion={$inscripcion}";

       $results=$this->_db->query($SQL);
       return (array) $results->fetchAll();
 }
 public function getRow ($id){
     if(!isset($id)) return;
    
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
 }
public function getNombreProyecto($pk){
    
    $SQL="select pk_asignacionproyecto, pk_proyecto, nombre as proyecto
          from tbl_asignacionesproyectos ap
          join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
          where pk_asignacionproyecto = {$pk} ";
    $results=$this->_db->query($SQL);
    return (array) $results->fetchAll();   
    
}
public function getInfoInscrito($ci){
    
    $SQL="select u.pk_usuario as cedula, p.nombre as proyecto
            from tbl_inscripcionespasantias ip 
            join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
            join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
            join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
            join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
            join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
            join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
            join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
            WHERE u.pk_usuario =  {$ci}
            and ap.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)";
       $results=$this->_db->query($SQL);
       return (array) $results->fetchAll(); 
}


public function getViewForm($pk) {
    
    $SQL="select proyecto,institucion,
          case when tutoracademico is null then 'Tutor aun sin Asignar' else tutoracademico end
          ,tutorinstitucional,horario
            from(
            SELECT p.nombre as proyecto, ins.nombre as institucion, 
            ua.nombre||','||ua.apellido as tutoracademico, 
            u.nombre||','||u.apellido as tutorinstitucional,
            atr.valor as horario
            from tbl_asignacionesproyectos ap
            join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
            join tbl_instituciones ins on ins.pk_institucion = p.fk_institucion
            join tbl_contactos c on c.fk_institucion = ins.pk_institucion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
            JOIN tbl_usuarios u ON  u.pk_usuario      = ug.fk_usuario
            join tbl_atributos atr on atr.pk_atributo = ap.fk_tipohorario
            LEFT OUTER JOIN tbl_inscripcionespasantias ip on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
            LEFT OUTER JOIN tbl_usuariosgrupos ugA on ugA.pk_usuariogrupo = ip.fk_tutor_academico
            LEFT OUTER JOIN tbl_usuarios ua on ua.pk_usuario = ugA.fk_usuario
            where pk_asignacionproyecto = {$pk}
          ) as sqt";
                
                 $results=$this->_db->query($SQL);
                 return (array) $results->fetchAll(); 
}

public function getCuposFull($escuela,$periodo_siguiente,$pk){

  $SQL="select DISTINCT(
                SELECT CASE WHEN (
                SELECT count (ip1.pk_inscripcionpasantia)
                FROM tbl_inscripcionespasantias ip1
                JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                AND ap1.fk_escuela = {$escuela}
                AND ap1.fk_periodo = {$periodo_siguiente}) >= ap.cupos
                THEN TRUE ELSE FALSE END) AS lleno       
      FROM tbl_asignacionesproyectos ap
      JOIN tbl_proyectos p  ON p.pk_proyecto = ap.fk_proyecto
      --JOIN tbl_inscripcionespasantias ip  ON ip.fk_asignacionproyecto = ap.pk_asignacionproyecto          
      WHERE ap.fk_periodo = {$periodo_siguiente}
      AND ap.fk_escuela = {$escuela}
      AND pk_asignacionproyecto = {$pk}";

    $results=$this->_db->query($SQL);
     return (array) $results->fetchAll();

}

public function getPkServicioII($escuela,$pensum){
    
    $SQL="select pk_asignatura
           from tbl_asignaturas a
           join tbl_pensums p on p.pk_pensum = a.fk_pensum
     where fk_materia in (718,913,9738)
     and fk_escuela = {$escuela}  
           and pk_pensum = {$pensum}";
           
           $results=$this->_db->query($SQL);
     return (array) $results->fetchAll(); 
}

public function getFechaPuntuales ($periodosiguiente){

  $SQL ="select fechainicio,fechafin,valor
        from tbl_calendarios c
        join tbl_atributos atr on atr.pk_atributo = c.fk_actividad
        join tbl_atributostipos ati on ati.pk_atributotipo = atr.fk_atributotipo
        where atr.pk_atributo = 10659
        and c.fk_periodo = {$periodosiguiente}";

     $results=$this->_db->query($SQL);
     return (array) $results->fetchAll();       
}

public function getFechaRezagados ($periodosiguiente){

  $SQL ="select fechainicio,fechafin,valor
        from tbl_calendarios c
        join tbl_atributos atr on atr.pk_atributo = c.fk_actividad
        join tbl_atributostipos ati on ati.pk_atributotipo = atr.fk_atributotipo
        where atr.pk_atributo =10660 
        and c.fk_periodo = {$periodosiguiente}";

  $results=$this->_db->query($SQL);
     return (array) $results->fetchAll();       
}

public function getEstudiantesProyecto($ci,$periodo,$escuela){

          $SQL ="select pk_usuario as cedula, 
        u.nombre||','||u.apellido as estudiante, 
        es.escuela as escuela, 
        pro.nombre as proyecto, 
        ins.nombre as institucion
        from tbl_usuarios u 
        join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
        join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
        join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
        join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
        join tbl_asignacionesproyectos  ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
        join tbl_instituciones ins on ins.pk_institucion = ip.fk_institucion
        join tbl_proyectos pro on pro.fk_institucion = ins.pk_institucion
        join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
        join tbl_pensums p on p.pk_pensum = a.fk_pensum
        join vw_escuelas es on es.pk_atributo = p.fk_escuela
        where fk_tutor_academico = (select DISTINCT pk_usuariogrupo
                  from tbl_usuariosgrupos ug1
                  join tbl_asignaciones ag1 on ag1.fk_usuariogrupo = ug1.pk_usuariogrupo
                  where fk_usuario = {$ci} )
        and ap.fk_periodo = {$periodo}
        and ap.fk_escuela = {$escuela}";

        $results=$this->_db->query($SQL);
     return (array) $results->fetchAll(); 
}
}



?>