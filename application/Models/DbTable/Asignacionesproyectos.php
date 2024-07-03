<?php

class Models_DbTable_Asignacionesproyectos extends Zend_Db_Table {

  protected $_schema = 'produccion';
  protected $_name = 'tbl_asignacionesproyectos';
  protected $_primary = 'pk_asignacionproyecto';
  protected $_sequence = false;

  public function init() {
  $this->SwapBytes_Array = new SwapBytes_Array();
  }

  /**
   * Permite crear una clausula WHERE dependiendo de los parametros que se envien.
   *
   * @param <type> $Data
   * @param <type> $Keys
   */
  public function setData($Data, $Keys) {
  $Keys = array_fill_keys($Keys, null);
  $Data = array_intersect_key($Data, $Keys);

  $Where = array(' AND  a.fk_periodo        = ' => $Data['periodo'],
           ' AND  a.fk_escuela        = ' => $Data['escuela'],
           'AND a.fk_estructura = ' => $Data['sede']);

  $Where = array_filter($Where);
  $Where = $this->SwapBytes_Array->implode(' ', $Where);
  $Where = ltrim($Where, ' AND ');

  $this->Where = $Where;
  }

  public function getRows($periodo,$escuela,$sede) {

    $SQL = "SELECT p.nombre as proyecto,pk_asignacionproyecto, a.valor as horario,ap.cupos, pk_proyecto, (    
                            SELECT count (ip1.pk_inscripcionpasantia) as cuenta
                            FROM tbl_inscripcionespasantias ip1
                            JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                            WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                            AND ap1.fk_estructura = {$sede}
                            AND ap1.fk_periodo = {$periodo}
                            AND ap1.fk_escuela = {$escuela}) as inscritos
            FROM tbl_proyectos p
            JOIN tbl_asignacionesproyectos ap ON ap.fk_proyecto = pk_proyecto
            JOIN tbl_atributos a ON a.pk_atributo = ap.fk_tipohorario 
            WHERE ap.fk_estructura = {$sede}
            AND ap.fk_periodo = {$periodo}
            AND ap.fk_escuela = {$escuela}";
/*
    $SQL = "select pk_asignacionproyecto, proyecto,horario,cupos, inscritos, pk_proyecto
from(
select DISTINCT (
                SELECT count (ip1.pk_inscripcionpasantia) as cuenta
                FROM tbl_inscripcionespasantias ip1
                JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                WHERE ip1.fk_asignacionproyecto = a.pk_asignacionproyecto
                AND ap1.fk_escuela in (11,12,13,14,15,16)
                AND ap1.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1))as inscritos, pk_asignacionproyecto,
                pr.nombre as proyecto,
    atr.valor as horario, a.cupos, pk_proyecto     
from tbl_asignacionesproyectos a
join tbl_proyectos pr on pr.pk_proyecto = a.fk_proyecto
join tbl_instituciones ins on ins.pk_institucion  = pr.fk_institucion
join tbl_pensums p on p.fk_escuela = a.fk_escuela
join tbl_asignaturas ag on ag.fk_pensum = p.pk_pensum
join tbl_recordsacademicos ra on ra.fk_asignatura = ag.pk_asignatura
join vw_escuelas es on es.pk_atributo = p.fk_escuela
join tbl_atributos atr on atr.pk_atributo = a.fk_tipohorario

join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
join tbl_contactos c on c.fk_institucion = ins.pk_institucion
JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
JOIN tbl_usuarios u ON  u.pk_usuario      = ug.fk_usuario
LEFT OUTER JOIN tbl_inscripcionespasantias ip on a.pk_asignacionproyecto = ip.fk_asignacionproyecto
LEFT OUTER JOIN tbl_usuariosgrupos ugA on ugA.pk_usuariogrupo = ip.fk_tutor_academico
LEFT OUTER JOIN tbl_usuarios ua on ua.pk_usuario = ugA.fk_usuario
WHERE {$this->Where}
    and ag.fk_materia IN (8219,9737) 
    
 ) AS subj";
 */
  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getRowsPk(){

    if (empty($this->Where))
    return;

    $SQL = "select pk_asignacionproyecto, pk_proyecto
          from(
          select DISTINCT (
                          SELECT count (ip1.pk_inscripcionpasantia) as cuenta
                          FROM tbl_inscripcionespasantias ip1
                          JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                          WHERE ip1.fk_asignacionproyecto = a.pk_asignacionproyecto
                          AND ap1.fk_escuela in (11,12,13,14,15,16)
                          AND ap1.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1))as inscritos, pk_asignacionproyecto,
                          pr.nombre as proyecto,
              atr.valor as horario, a.cupos, pk_proyecto     
          from tbl_asignacionesproyectos a
          join tbl_proyectos pr on pr.pk_proyecto = a.fk_proyecto
          join tbl_instituciones ins on ins.pk_institucion  = pr.fk_institucion
          join tbl_pensums p on p.fk_escuela = a.fk_escuela
          join tbl_asignaturas ag on ag.fk_pensum = p.pk_pensum
          join tbl_recordsacademicos ra on ra.fk_asignatura = ag.pk_asignatura
          join vw_escuelas es on es.pk_atributo = p.fk_escuela
          join tbl_atributos atr on atr.pk_atributo = a.fk_tipohorario

          join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
          join tbl_contactos c on c.fk_institucion = ins.pk_institucion
          JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
          JOIN tbl_usuarios u ON  u.pk_usuario      = ug.fk_usuario
          LEFT OUTER JOIN tbl_inscripcionespasantias ip on a.pk_asignacionproyecto = ip.fk_asignacionproyecto
          LEFT OUTER JOIN tbl_usuariosgrupos ugA on ugA.pk_usuariogrupo = ip.fk_tutor_academico
          LEFT OUTER JOIN tbl_usuarios ua on ua.pk_usuario = ugA.fk_usuario
          WHERE {$this->Where}
              and ag.fk_materia IN (8219,9737) 
              
           ) AS subj";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();

  }


  public function getPK() {
  $SQL = "SELECT a.pk_asignacionproyecto,
                         a.fk_proyecto,
                         a.fk_escuela,
                         a.fk_periodo,
                         a.fk_tipohorario,
                         a.cupos,
                         p.nombre as proyecto,
                         atr2.valor as escuela,
                         atr.valor as tipohorario,
                         pk_proyecto
  FROM tbl_asignacionesproyectos    a
  JOIN tbl_atributos atr  ON atr.pk_atributo  = a.fk_tipohorario
  JOIN tbl_atributos atr2   ON atr2.pk_atributo = a.fk_escuela
  JOIN tbl_proyectos p    ON p.pk_proyecto    = a.fk_proyecto
                WHERE {$this->Where}";

  return $this->_db->fetchOne($SQL);
  }

  /**
   * Obtiene un registro en especifico.
   *
   * @param int $id Clave primaria del registro.
   * @return array
   */
  public function getRow($id) {
     $SQL = "SELECT a.pk_asignacionproyecto,
                         a.fk_proyecto,
                         a.fk_escuela,
                         a.fk_periodo,
                         a.fk_tipohorario,
                         a.cupos,
                         p.nombre as proyecto,
                         atr2.valor as escuela,
                         atr.valor as tipohorario
  FROM tbl_asignacionesproyectos    a
  JOIN tbl_atributos atr  ON atr.pk_atributo  = a.fk_tipohorario
  JOIN tbl_atributos atr2   ON atr2.pk_atributo = a.fk_escuela
  JOIN tbl_proyectos p    ON p.pk_proyecto    = a.fk_proyecto
WHERE pk_asignacionproyecto = {$id}";



  return $this->_db->fetchRow($SQL);
  }

  public function addRow($data) {
  $data = array_filter($data);
  $affected = $this->insert($data);

  return $affected;
  }

  public function updateRow($id, $data) {
  $data = array_filter($data);
  
  $rows_affected = $this->update($data, $this->_primary . ' = ' . (int) $id);

  return $rows_affected;
  }

  public function deleteRow($id) {
  if (!is_numeric($id))
    return null;

  $affected = $this->delete($this->_primary . ' = ' . (int) $id);

  return $affected;
  }

  public function deleteRows($ids) {
  if (is_array($ids)) {
    $ids = implode(',', $ids);
  } else if (!is_numeric($ids)) {
    return null;
  }

  $affected = $this->delete($this->_primary . " IN ({$ids})");

  return $affected;
  }

     public function getasignacionproyecto($periodo, $escuela) {
        $SQL = "SELECT ap.pk_asignacionproyecto,
                        p.nombre || ' '||(SELECT CASE WHEN (SELECT count (ip1.pk_inscripcionpasantia)
               FROM tbl_inscripcionespasantias ip1
               JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
              WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                AND ap1.fk_escuela = ap.fk_escuela
                AND ap1.fk_periodo = ap.fk_periodo) >= ap.cupos
          THEN '(EL PROYECTO NO TIENE CUPOS)'
          ELSE '(Cupos:' || 
                (SELECT count (ip1.pk_inscripcionpasantia)
               FROM tbl_inscripcionespasantias ip1
               JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
              WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                AND ap1.fk_escuela = ap.fk_escuela
                AND ap1.fk_periodo = ap.fk_periodo)||'/'|| ap.cupos ||')'
          END) AS nombre      
                  FROM tbl_asignacionesproyectos ap
                  JOIN tbl_proyectos p  ON p.pk_proyecto = ap.fk_proyecto
                  WHERE ap.fk_periodo = {$periodo}
                    AND ap.fk_escuela = {$escuela};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

       public function getCuposProyecto($periodo, $escuela, $fk_asignacionproyecto) {
    $SQL = "SELECT cupos
                          FROM tbl_asignacionesproyectos ap
                          JOIN tbl_inscripcionespasantias ip ON ip.fk_asignacionproyecto = ap.pk_asignacionproyecto
                          WHERE ip.fk_asignacionproyecto = {$fk_asignacionproyecto} 
                            AND ap.fk_escuela = {$escuela}
                            AND ap.fk_periodo = {$periodo};";
    
    return $this->_db->fetchOne($SQL);
  }
        
        public function getInscritosProyecto ($periodo,$sede,$escuela){
            
             $SQL="select DISTINCT  u.pk_usuario as cedula, u.nombre||','|| u.apellido as alumno,
                    pro.nombre as proyecto, ins.nombre as institucion
                    from tbl_usuarios u
                    join tbl_usuariosgrupos ug on u.pk_usuario = ug.fk_usuario 
                    join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
                    join tbl_recordsacademicos rc on rc.fk_inscripcion = i.pk_inscripcion
                    join tbl_asignaturas a on a.pk_asignatura = rc.fk_asignatura
                    join tbl_asignaciones asi on asi.fk_asignatura = a.pk_asignatura
                    join tbl_pensums pen on pen.pk_pensum =  a.fk_pensum
                    join tbl_inscritosproyectosservicio ips on i.pk_inscripcion = ips.fk_inscripcion
                    join tbl_proyectos pro on pro.pk_proyecto = ips.fk_proyecto
                    join tbl_instituciones ins on ins.pk_institucion = ips.fk_institucion
                    join tbl_estructuras est on est.pk_estructura = i.fk_estructura
                    where i.fk_periodo = {$periodo}
                    and i.fk_estructura = {$sede}
                    and pen.fk_escuela = {$escuela}";
                 
                    $results = $this->_db->query($SQL);
                    $results = (array) $results->fetchAll();
                    return $results;      
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
            i.fk_estructura as sede,
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

 public function getProyectos($escuela, $sede){
     
 
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
    and ap.fk_estructura = {$sede}
    and ap.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
 ) AS subj   
ORDER BY proyecto";
                              
            
      
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
 
public function getNombreProyecto($pk){
    
    $SQL="select pk_asignacionproyecto, pk_proyecto, nombre as proyecto
          from tbl_asignacionesproyectos ap
          join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
          where pk_asignacionproyecto = {$pk} ";
    $results=$this->_db->query($SQL);
    return (array) $results->fetchAll();   
    
}
public function getInfoInscrito($ci){
    
    $SQL="select distinct u.pk_usuario as cedula, ra.fk_atributo, materia, ra.calificacion,i.fk_periodo, pk_inscripcionpasantia, p.nombre as proyecto
          from tbl_inscripciones i 
          join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
          join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
          join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
          join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
          join vw_materias m on m.pk_atributo = a.fk_materia
          join tbl_inscripcionespasantias ip on ra.pk_recordacademico = ip.fk_recordacademico
          join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
          join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
          WHERE u.pk_usuario =  {$ci}
          and fk_materia in (9738,718,719,913)";
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
     where fk_materia in (718,719,913,9738)
     and fk_escuela = {$escuela}  
           and pk_pensum = {$pensum} ";
           
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

public function getEstudianteProyecto($ci,$periodo,$escuela){

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

public function getFoto($ci){

  $SQL="SELECT foto FROM tbl_usuarios WHERE pk_usuario = {$ci}::integer";
  $results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
}

public function getPreinscritos($periodo,$sede,$escuela,$pensum){

  $SQL="SELECT u.pk_usuario,u.pk_usuario, u.nombre||','||u.apellido as estudiante, m.materia, me.valor 
        from tbl_recordsacademicos ra
        join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
        join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
        join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
        join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
        join tbl_pensums p on p.pk_pensum = a.fk_pensum 
        join vw_escuelas es on es.pk_atributo = p.fk_escuela
        join vw_materiasestados me on me.pk_atributo = ra.fk_atributo
        join vw_materias m on m.pk_atributo = a.fk_materia
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where a.fk_materia in (
        718,
        719,
        913,
        9738)
        and ra.fk_atributo = 904
        and ap.fk_periodo = {$periodo}+1
        and i.fk_estructura = {$sede}
        and p.fk_escuela = {$escuela}
        and p.pk_pensum = {$pensum} 
        order by 2 asc";
  $results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
}

public function getInscritos($sede,$escuela,$pensum,$periodo){

  $SQL="SELECT u.pk_usuario, u.nombre||','||u.apellido as estudiante, m.materia, me.valor||' en el período '||fk_periodo::text as valor 
        from tbl_recordsacademicos ra
        join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
        join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
        join tbl_pensums p on p.pk_pensum = a.fk_pensum 
        join vw_escuelas es on es.pk_atributo = p.fk_escuela
        join vw_materiasestados me on me.pk_atributo = ra.fk_atributo
        join vw_materias m on m.pk_atributo = a.fk_materia
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where a.fk_materia in (
        718,
        719,
        913,
        9738)
        and ra.fk_atributo = 864
        and i.fk_periodo = {$periodo}+1
        and i.fk_estructura = {$sede}
        and p.fk_escuela = {$escuela}
        and p.pk_pensum = {$pensum} 
        order by 2 asc";
  $results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
}
public function getPkAsignacion($servicio,$escuela,$pensum,$sede){

  $SQL="SELECT pk_asignacion 
from tbl_asignaciones ag
join tbl_estructuras sal on ag.fk_estructura = sal.pk_estructura
join tbl_estructuras edf on edf.pk_estructura = sal.fk_estructura
join tbl_estructuras sed on sed.pk_estructura = edf.fk_estructura
join tbl_asignaturas a on a.pk_asignatura = ag.fk_asignatura
join tbl_pensums p on p.pk_pensum = a.fk_pensum
where fk_asignatura = {$servicio}
and p.fk_escuela = {$escuela}
and a.fk_pensum = {$pensum}
and sed.pk_estructura = {$sede}
and ag.fk_periodo = (SELECT pk_periodo FROM tbl_periodos WHERE current_date BETWEEN  fechainicio AND fechafin)";

  $results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
}

public function getLastInscripcion($periodo,$sede,$escuela){

  $SQL="SELECT DISTINCT pk_inscripcion
from tbl_usuarios u
join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
where fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
and u.pk_usuario in (SELECT u.pk_usuario 
        from tbl_recordsacademicos ra
        join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
        join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
        join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
        join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
        join tbl_pensums p on p.pk_pensum = a.fk_pensum 
        join vw_escuelas es on es.pk_atributo = p.fk_escuela
        join vw_materiasestados me on me.pk_atributo = ra.fk_atributo
        join vw_materias m on m.pk_atributo = a.fk_materia
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where a.fk_materia in (
        718,
        719,
        913,
        9738)
        and ra.fk_atributo = 904
        and ap.fk_periodo = {$periodo}+1
        and i.fk_estructura = {$sede}
        and p.fk_escuela = {$escuela})";

$results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
}

public function inscribirRecord($asignatura,$array,$asignacion){
     
      //864 Materia inscrita no cursada y sin nota
      $SQL="INSERT INTO tbl_recordsacademicos (fk_atributo,fk_asignatura,fk_inscripcion,fk_asignacion)
            VALUES";
      foreach ($array as $key => $value) {

        $SQL .= "(864,$asignatura,".
              $value['pk_inscripcion'].
              ",$asignacion)". ",";
      }
        
         $SQL = trim($SQL,',');   

         //var_dump($SQL);die; 
      $this->_db->query($SQL);      
 }

 public function getNoInscripcion($periodo,$sede,$escuela,$pensum){

  $SQL ="SELECT distinct ug.pk_usuariogrupo
        from tbl_recordsacademicos ra
        join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
        join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
        join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
        join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
        join tbl_pensums p on p.pk_pensum = a.fk_pensum 
        join vw_escuelas es on es.pk_atributo = p.fk_escuela
        join vw_materiasestados me on me.pk_atributo = ra.fk_atributo
        join vw_materias m on m.pk_atributo = a.fk_materia
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where a.fk_materia in (
        718,
        719,
        913,
        9738)
        and ra.fk_atributo = 904
        and ap.fk_periodo = {$periodo} +1
        and i.fk_estructura = {$sede}
        and p.fk_escuela = {$escuela}
        and p.pk_pensum = {$pensum}
        except 
        select DISTINCT ug.pk_usuariogrupo
        from tbl_inscripciones i
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)";
  $results=$this->_db->query($SQL);
  return (array) $results->fetchAll();
 }

 public function inscribirInscripcion($array,$periodo,$sede,$escuela,$pensum){

  $SQL="INSERT INTO tbl_inscripciones (fk_usuariogrupo,fk_periodo,fk_atributo,fk_estructura,
        fk_semestre,fk_pensum)
        VALUES";
      foreach ($array as $key => $value) {

        $SQL .= "(".
              $value['pk_usuariogrupo'].
              ",".$periodo.
              ",$escuela,$sede,881,$pensum)". ",";
      }
        
         $SQL = trim($SQL,',');   

         //var_dump($SQL);die; 
      $this->_db->query($SQL); 
 } 
 
}
