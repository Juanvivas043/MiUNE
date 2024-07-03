<?php

class Models_DbTable_Inscripcionespasantias extends Zend_Db_Table {

  protected $_schema = 'produccion';
  protected $_name = 'tbl_inscripcionespasantias';
  protected $_primary = 'pk_inscripcionpasantia';
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

	$Where = array(' AND  ap.fk_periodo        = ' => $Data['Periodo'],
		       ' AND  ap.fk_escuela        = ' => $Data['Escuela'],
                       ' AND  i.fk_estructura     = ' => $Data['sede'],
                       'AND  ap.pk_asignacionproyecto       = ' => $Data['Proyecto']);

	$Where = array_filter($Where);
	$Where = $this->SwapBytes_Array->implode(' ', $Where);
	$Where = ltrim($Where, ' AND ');

	$this->Where = $Where;
  }
  
  
  public function setDataempresas($Data, $Keys) {
	$Keys = array_fill_keys($Keys, null);
	$Data = array_intersect_key($Data, $Keys);

	$Where = array(' AND  i.fk_periodo        = ' => $Data['periodo'],
		       ' AND  p.fk_escuela       = ' => $Data['escuela'],
                       ' AND  i.fk_estructura     = ' => $Data['sede']);

	$Where = array_filter($Where);
	$Where = $this->SwapBytes_Array->implode(' ', $Where);
	$Where = ltrim($Where, ' AND ');

	$this->Where = $Where;
  }
  
  public function getInscripcionesproyectos($periodo,$sede,$escuela,$proyecto) {
              
        $SQL = "SELECT 	ip.pk_inscripcionpasantia, 
                        ip.fk_recordacademico, 	
                        ip.fk_asignacionproyecto, 
                        ip.fk_institucion, 
                        ip.fk_tutor_institucion, 
                        ip.fk_tutor_academico, 
                        ip.status_inscripcion,
                        p.nombre AS proyecto,
                        u3.apellido || ',' || u3.nombre AS tutorinstitucion,
                        u.apellido || ',' || u.nombre AS tutoracademico,
                        u2.pk_usuario AS ci,
                        u2.apellido || ',' || u2.nombre AS estudiante
                 FROM tbl_inscripcionespasantias ip
                  JOIN tbl_asignacionesproyectos	ap  ON ap.pk_asignacionproyecto 	= ip.fk_asignacionproyecto
                  JOIN tbl_proyectos	       		p   ON  p.pk_proyecto 		= ap.fk_proyecto 
                  JOIN tbl_contactos			c   ON c.pk_contacto		= ip.fk_tutor_institucion
                  JOIN tbl_usuariosgrupos		ug  ON ug.pk_usuariogrupo	= ip.fk_tutor_academico
                  JOIN tbl_usuarios			u   ON  u.pk_usuario		= ug.fk_usuario
                  JOIN tbl_recordsacademicos 		ra  ON ra.pk_recordacademico	= ip.fk_recordacademico
                  JOIN tbl_inscripciones		i   ON i.pk_inscripcion 	= ra.fk_inscripcion
                  JOIN tbl_usuariosgrupos		ug2 ON ug2.pk_usuariogrupo	= i.fk_usuariogrupo
                  JOIN tbl_usuarios			u2  ON u2.pk_usuario		= ug2.fk_usuario
                  JOIN tbl_usuariosgrupos 		ug3 ON ug3.pk_usuariogrupo	= c.fk_usuariogrupo
                  JOIN tbl_usuarios			u3  ON u3.pk_usuario		= ug3.fk_usuario
                  WHERE ap.fk_periodo={$periodo} and i.fk_estructura={$sede} and i.fk_atributo={$escuela} and ap.pk_asignacionproyecto={$proyecto}
                  ORDER BY p.nombre, u2.apellido;";
                  //var_dump($SQL);die;

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    } 
  
  
    public function getInscripcionesempresas() {
     
      if (empty($this->Where))
	  return;
      
        $SQL = "SELECT  ip.pk_inscripcionpasantia, 
                        ip.fk_recordacademico,  
                        ip.fk_asignacionproyecto, 
                        ip.fk_institucion, 
                        ip.fk_tutor_institucion, 
                        ip.fk_tutor_academico, 
                        ip.status_inscripcion,
                        u3.apellido || ',' || u3.nombre AS tutorempresa,
                        u.apellido || ',' || u.nombre AS tutoracademico,
                        u2.pk_usuario AS ci,
                        u2.apellido || ',' || u2.nombre AS estudiante,
                        ins.nombre AS empresa
                 FROM tbl_inscripcionespasantias ip
      join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
      join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
      join tbl_asignaturas ag on ag.pk_asignatura = ra.fk_asignatura
      join tbl_pensums p on p.pk_pensum =ag.fk_pensum   
                  JOIN tbl_instituciones                ins ON ins.pk_institucion               = ip.fk_institucion 
                  JOIN tbl_contactos      c   ON c.pk_contacto                    = ip.fk_tutor_institucion
                  JOIN tbl_usuariosgrupos   ug  ON ug.pk_usuariogrupo               = ip.fk_tutor_academico
                  JOIN tbl_usuarios     u   ON  u.pk_usuario                    = ug.fk_usuario
      JOIN tbl_usuariosgrupos   ug2 ON ug2.pk_usuariogrupo              = i.fk_usuariogrupo
                  JOIN tbl_usuarios     u2  ON u2.pk_usuario                    = ug2.fk_usuario
                  JOIN tbl_usuariosgrupos   ug3 ON ug3.pk_usuariogrupo              = c.fk_usuariogrupo
                  JOIN tbl_usuarios     u3  ON u3.pk_usuario                    = ug3.fk_usuario
                  WHERE  {$this->Where}
                  AND ag.fk_materia IN (716,717,848,9859)";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    } 
    
  public function getEstudiantesInscritospasantias($periodo, $escuela) {
     
        $SQL = "SELECT DISTINCT ra.pk_recordacademico,
		u.apellido || ',' || u.nombre as estudiante
  FROM  tbl_recordsacademicos 	ra
  JOIN 	tbl_inscripciones 	i	ON ra.fk_inscripcion 	= i.pk_inscripcion
  JOIN	tbl_usuariosgrupos	ug	ON ug.pk_usuariogrupo 	= i.fk_usuariogrupo
  JOIN  tbl_usuarios		u	ON  u.pk_usuario	= ug.fk_usuario
  JOIN 	vw_escuelas		es	ON es.pk_atributo	= i.fk_atributo
  JOIN  tbl_asignaturas         ag      ON ag.pk_asignatura 	= ra.fk_asignatura
  WHERE i.fk_periodo  = {$periodo}
    AND i.fk_atributo = {$escuela}
    --AND ra.fk_asignatura IN  (12249,12323,12411,12568,12650,12487) 
    AND ag.fk_materia IN (8219,9737)
    and ra.calificacion > 10
    and ra.fk_atributo = 862
    GROUP BY ra.pk_recordacademico, u.pk_usuario, u.apellido, u.nombre
    ORDER BY 2 ASC;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    } 

  public function getEstudiantesInscritosServicio($id){

    $SQL = "select pk_recordacademico, u.nombre||','||u.apellido as estudiante
            from tbl_inscripcionespasantias ip
            join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
            join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
            join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
            join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
            where pk_inscripcionpasantia = {$id}";

    $results = $this->_db->query($SQL);

  return $results->fetchAll();
  }  
  
  public function getListadoPasantes($ci,$sede,$periodo){

    $SQL = "select distinct ua.pk_usuario as cedula_tutor, ua.nombre as nombre, ua.apellido as apellido,
            u.pk_usuario as pk_usuario, u.nombre||','||u.apellido as estudiante, i.nombre as institucion,
            es.escuela
            from tbl_inscripcionespasantias ip
            join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
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
            and ap.fk_periodo = {$periodo}
            and ins.fk_estructura = {$sede}
            and fk_tutor_academico = (select DISTINCT pk_usuariogrupo
                                        from tbl_usuariosgrupos ug1
                                        join tbl_asignaciones ag1 on ag1.fk_usuariogrupo = ug1.pk_usuariogrupo
                                        where fk_usuario = {$ci})";
  $results = $this->_db->query($SQL);

  return $results->fetchAll();
  }  
    
    public function getEstudiantesInscritospasantiaslaborales($periodo, $escuela, $sede = null) {
     
      $SQL = "SELECT DISTINCT ra.pk_recordacademico,
            		u.apellido || ',' || u.nombre as estudiante
              FROM  tbl_recordsacademicos 	ra
              JOIN 	tbl_inscripciones 	i	ON ra.fk_inscripcion 	= i.pk_inscripcion
              JOIN	tbl_usuariosgrupos	ug	ON ug.pk_usuariogrupo 	= i.fk_usuariogrupo
              JOIN  tbl_usuarios		u	ON  u.pk_usuario	= ug.fk_usuario
              JOIN 	vw_escuelas		es	ON es.pk_atributo	= i.fk_atributo
              JOIN  tbl_asignaturas		ag	ON ag.pk_asignatura     = ra.fk_asignatura
              WHERE i.fk_periodo  = {$periodo}
                AND i.fk_atributo = {$escuela} ";
      if(isset($sede) && is_numeric($sede)){
        $SQL .= "AND i.fk_estructura = {$sede}";
      }
      $SQL .= " --AND ra.fk_asignatura IN  (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                AND ag.fk_materia IN (716,717,848,9859)
                AND ra.pk_recordacademico = (SELECT DISTINCT MAX(ra1.pk_recordacademico)	
            				   FROM tbl_recordsacademicos 	ra1
            				   JOIN tbl_inscripciones 	i1	ON ra1.fk_inscripcion 	= i1.pk_inscripcion
            				   JOIN	tbl_usuariosgrupos	ug1	ON ug1.pk_usuariogrupo 	= i1.fk_usuariogrupo
            				   JOIN tbl_usuarios		u1	ON  u1.pk_usuario	= ug1.fk_usuario
            				   JOIN vw_escuelas		es1	ON es1.pk_atributo	= i1.fk_atributo
            				   JOIN  tbl_asignaturas	ag1	ON ag1.pk_asignatura     = ra1.fk_asignatura
            				  WHERE i1.fk_periodo  = i.fk_periodo
            				    AND i1.fk_atributo = i.fk_atributo
            				   -- AND ra1.fk_asignatura IN (12410,12569,12649,12418,12574,12657,12254,12328,12506)
            				    AND ag1.fk_materia IN (716,717,848,9859)
            				    AND u1.pk_usuario = u.pk_usuario)
                GROUP BY ra.pk_recordacademico, u.pk_usuario, u.apellido, u.nombre
                ORDER BY 2 ASC;";

      $results = $this->_db->query($SQL);
    	return $results->fetchAll();
    } 
    
    
    public function getEstudiantesInscritosProyecto($periodo,$sede,$escuela) {
     
        $SQL = "SELECT distinct ra1.pk_recordacademico, u1.nombre||', '||u1.apellido as estudiante
                from tbl_usuarios u1
                join tbl_usuariosgrupos ug1 on ug1.fk_usuario = u1.pk_usuario
                join tbl_inscripciones i1 on i1.fk_usuariogrupo = ug1.pk_usuariogrupo
                join tbl_recordsacademicos ra1 on ra1.fk_inscripcion = i1.pk_inscripcion
                join tbl_asignaturas a1 on a1.pk_asignatura = ra1.fk_asignatura
                where u1.pk_usuario in (

                select *
                from (select distinct u.pk_usuario 
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on ug.fk_usuario=u.pk_usuario
                join tbl_inscripciones ins on ins.fk_usuariogrupo=ug.pk_usuariogrupo
                join tbl_recordsacademicos ra on ra.fk_inscripcion=ins.pk_inscripcion
                join tbl_asignaturas asig on ra.fk_asignatura=asig.pk_asignatura
                join tbl_atributos atr on ins.fk_atributo=atr.pk_atributo
                join tbl_pensums pen on asig.fk_pensum=pen.pk_pensum
                where asig.fk_materia in(8219,9737) and ra.fk_atributo = 862 and ra.calificacion>=10 and atr.pk_atributo={$escuela} and ins.fk_estructura={$sede} and pen.codigopropietario not in (6,5,9,18)
                except
                select distinct u.pk_usuario
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on ug.fk_usuario=u.pk_usuario
                join tbl_inscripciones ins on ins.fk_usuariogrupo=ug.pk_usuariogrupo
                join tbl_recordsacademicos ra on ra.fk_inscripcion=ins.pk_inscripcion
                join tbl_asignaturas asig on ra.fk_asignatura=asig.pk_asignatura
                join tbl_atributos atr on ins.fk_atributo=atr.pk_atributo
                join tbl_pensums pen on asig.fk_pensum=pen.pk_pensum
                where asig.fk_materia in(718,719,913,9738) and ra.calificacion>=10 and ra.fk_atributo = 862 and atr.pk_atributo={$escuela} and ins.fk_estructura={$sede} and pen.codigopropietario not in (6,5,9,18)) as SQT
                except
                  /*Excluye estudiantes con:
          
                  1.-PASANTIA SOCIAL I
                  2.-PASANTIA SOCIAL II
                  3.-PASANTIA SOCIAL I y II
                  4.-SERVICIO COMUNITARIO II 
                  
                  pre-inscritas*/
                select distinct u.pk_usuario
                from tbl_usuarios u
                join tbl_usuariosgrupos ug on ug.fk_usuario=u.pk_usuario
                join tbl_inscripciones ins on ins.fk_usuariogrupo=ug.pk_usuariogrupo
                join tbl_recordsacademicos ra on ra.fk_inscripcion=ins.pk_inscripcion
                join tbl_asignaturas asig on ra.fk_asignatura=asig.pk_asignatura
                join tbl_atributos atr on ins.fk_atributo=atr.pk_atributo
                join tbl_pensums pen on asig.fk_pensum=pen.pk_pensum
                where asig.fk_materia in(718,719,913,9738) and ra.fk_atributo=904 and atr.pk_atributo={$escuela} and ins.fk_estructura={$sede} and ins.fk_periodo = {$periodo}
                ) and  fk_materia in(8219,9737) and ra1.fk_atributo = 862 and ra1.calificacion >9 and i1.fk_atributo = {$escuela}
                order by 2 asc";     

    $results = $this->_db->query($SQL);
    
    /* Función para eliminar el duplicado de estudiantes que tengan pensum 1997 y 2012 */
    function ordenar($registros) {
      foreach ($registros as $index => $registro) {
        $estudiante = $registro["estudiante"];
        
        if ($estudiante == $registros[$index-1]["estudiante"]) {
          unset($registros[$index-1]);
        }
      }
      
      return $registros;
    }

    /* Función para ver en consola el resultado */
    // function dd($param) {
    //   echo '<pre>';
    //   var_dump($param);
    //   echo '</pre>';
    //   die();
    // }
    //dd(ordenar($results->fetchAll()));

    $registros = ordenar($results->fetchAll());

  return $registros;
    } 
    
public function getPensumByRecord($recordacademico){

  $SQL ="SELECT a1.fk_pensum, pk_inscripcion 
FROM tbl_recordsacademicos r1
INNER JOIN tbl_asignaturas a1 ON a1.pk_asignatura = r1.fk_asignatura
INNER JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = r1.fk_inscripcion
INNER JOIN tbl_pensums pe1 ON pe1.pk_pensum = a1.fk_pensum 
INNER JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = i1.fk_usuariogrupo 
WHERE ug1.fk_usuario = (select distinct pk_usuario
      from tbl_usuarios u
      join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
      join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
      join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
      where pk_recordacademico = {$recordacademico})
ORDER BY i1.fk_periodo desc limit 1";

  $results = $this->_db->query($SQL);

  return $results->fetchAll();
}
 public function getEstudiantesInscritosLaboral($periodo, $escuela, $sede) {
     
        $SQL = "SELECT DISTINCT ra.pk_recordacademico,
		u.apellido || ',' || u.nombre as estudiante
  FROM  tbl_recordsacademicos 	ra
  JOIN 	tbl_inscripciones 	i	ON ra.fk_inscripcion 	= i.pk_inscripcion
  JOIN	tbl_usuariosgrupos	ug	ON ug.pk_usuariogrupo 	= i.fk_usuariogrupo
  JOIN  tbl_usuarios		u	ON  u.pk_usuario	= ug.fk_usuario
  JOIN 	vw_escuelas		es	ON es.pk_atributo	= i.fk_atributo
  JOIN  tbl_asignaturas		ag	ON ag.pk_asignatura     = ra.fk_asignatura
  WHERE i.fk_periodo  = {$periodo}
    AND i.fk_atributo = {$escuela}
    AND i.fk_estructura = {$sede}
    --AND ra.fk_asignatura IN  (12410,12569,12649,12418,12574,12657,12254,12328,12506) 
    AND ag.fk_materia IN (716,717,848,9859)
    AND ra.pk_recordacademico NOT IN (SELECT ip.fk_recordacademico 
					FROM tbl_inscripcionespasantias ip 
					JOIN tbl_recordsacademicos ra1 ON ra1.pk_recordacademico = ip.fk_recordacademico
					JOIN tbl_inscripciones 	i1     ON ra1.fk_inscripcion 	 = i1.pk_inscripcion
				       WHERE i1.fk_periodo  = i.fk_periodo
					 AND i1.fk_atributo = i.fk_atributo) 
    AND ra.pk_recordacademico = (SELECT DISTINCT MAX(ra1.pk_recordacademico)	
				   FROM tbl_recordsacademicos 	ra1
				   JOIN tbl_inscripciones 	i1	ON ra1.fk_inscripcion 	= i1.pk_inscripcion
				   JOIN	tbl_usuariosgrupos	ug1	ON ug1.pk_usuariogrupo 	= i1.fk_usuariogrupo
				   JOIN tbl_usuarios		u1	ON  u1.pk_usuario	= ug1.fk_usuario
				   JOIN vw_escuelas		es1	ON es1.pk_atributo	= i1.fk_atributo
				   JOIN  tbl_asignaturas	ag1	ON ag1.pk_asignatura     = ra1.fk_asignatura
				  WHERE i1.fk_periodo  = i.fk_periodo
				    AND i1.fk_atributo = i.fk_atributo
				    --AND ra1.fk_asignatura IN (12410,12569,12649,12418,12574,12657,12254,12328,12506)
				    AND ag1.fk_materia IN (716,717,848,9859)
				    AND u1.pk_usuario = u.pk_usuario)
    GROUP BY ra.pk_recordacademico, u.pk_usuario, u.apellido, u.nombre
    ORDER BY 2 ASC;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }    
  
    
public function getCountByInscripcionPasantias($fk_estudianteinscripcionpasantia) {
$SQL = "SELECT COUNT(u.pk_usuario)     
          FROM  tbl_recordsacademicos 	ra
          JOIN 	tbl_inscripciones 	i	ON ra.fk_inscripcion 	= i.pk_inscripcion
          JOIN	tbl_usuariosgrupos	ug	ON ug.pk_usuariogrupo 	= i.fk_usuariogrupo
          JOIN  tbl_usuarios		u	ON  u.pk_usuario	= ug.fk_usuario
          WHERE u.pk_usuario  = {$fk_estudianteinscripcionpasantia}
            AND i.fk_periodo  = (SELECT pk_periodo FROM tbl_periodos ORDER BY  pk_periodo DESC LIMIT 1)
            AND fk_asignatura IN  (12249,12323,12411,12568,12650)";
		
		return $this->_db->fetchOne($SQL);
	}    
        //19993402

    //{$id}
    //  {$periodo}  
  public function getRows() {
	if (empty($this->Where))
	  return;

	  $SQL = "SELECT ip.pk_inscripcionpasantia,
                    ip.fk_recordacademico,
                    ip.fk_asignacionproyecto,
                    ip.fk_institucion,
                    ip.fk_tutor_institucion,
                    ip.fk_tutor_academico,
                    ip.status_inscripcion
             FROM tbl_inscripcionespasantias ip
                WHERE {$this->Where}
	     ORDER BY 3";

	$results = $this->_db->query($SQL);

	return (array) $results->fetchAll();
  }


  public function getPK() {
	$SQL = "SELECT ip.pk_inscripcionpasantia,
                  FROM tbl_inscripcionespasantias ip
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
     $SQL = "SELECT ip.pk_inscripcionpasantia,
                    ip.fk_recordacademico,
                    ip.fk_asignacionproyecto,
                    ip.fk_institucion,
                    ip.fk_tutor_institucion,
                    ip.fk_tutor_academico,
                    ip.status_inscripcion,
                    i.fk_periodo,
                    i.fk_atributo AS escuela
             FROM tbl_inscripcionespasantias ip
             JOIN tbl_recordsacademicos ra ON ra.pk_recordacademico = ip.fk_recordacademico
             JOIN tbl_inscripciones 	i  ON i.pk_inscripcion	    = ra.fk_inscripcion
             WHERE pk_inscripcionpasantia = {$id}";

	return $this->_db->fetchRow($SQL);
  }

  public function getCountByAsignacionPasantias($fk_asignacionpasantia) {
		$SQL = "SELECT COUNT(ip.pk_inscripcionpasantia)
				FROM tbl_inscripcionespasantias ip
				WHERE ip.fk_asignacionproyecto = {$fk_asignacionpasantia}";
		
		return $this->_db->fetchOne($SQL);
	}
        
     public function getCountInscritosPasantias($periodo, $escuela, $fk_asignacionproyecto) {
		$SQL = "SELECT count (ip.pk_inscripcionpasantia)
                          FROM tbl_inscripcionespasantias ip
                           JOIN tbl_asignacionesproyectos ap ON ap.pk_asignacionproyecto = ip.fk_asignacionproyecto 
                          WHERE ip.fk_asignacionproyecto = {$fk_asignacionproyecto}
                            AND ap.fk_escuela = {$escuela}
                            AND ap.fk_periodo = {$periodo};";
		
		return $this->_db->fetchOne($SQL);
	} 
        
       public function getCuposfull($periodo, $escuela, $pk_asignacionproyecto,$id) {
		$SQL = "SELECT (SELECT CASE WHEN (SELECT count (ip1.pk_inscripcionpasantia)
                                                   FROM tbl_inscripcionespasantias ip1
                                                   JOIN tbl_asignacionesproyectos ap1 ON ap1.pk_asignacionproyecto = ip1.fk_asignacionproyecto 
                                                  WHERE ip1.fk_asignacionproyecto = ap.pk_asignacionproyecto
                                                    AND ap1.fk_escuela = {$escuela}
                                                    AND ap1.fk_periodo = {$periodo}) >= ap.cupos
                                              AND ip.pk_inscripcionpasantia <> {$id}
                                           THEN TRUE ELSE FALSE END) AS lleno 		  
                                          FROM tbl_asignacionesproyectos ap
                                          JOIN tbl_proyectos p 	ON p.pk_proyecto = ap.fk_proyecto
                                          JOIN tbl_inscripcionespasantias ip	ON ip.fk_asignacionproyecto = ap.pk_asignacionproyecto          
                                          WHERE ap.fk_periodo = {$periodo}
                                            AND ap.fk_escuela = {$escuela}
                                            AND pk_asignacionproyecto = {$pk_asignacionproyecto};";
		
		return $this->_db->fetchOne($SQL);
	}   

  public function getEstudianteInscripcionPasantias($usuario, $periodo) {
        $SQL = "SELECT ip.pk_inscripcionpasantia
                  FROM tbl_inscripcionespasantias ip
                   JOIN tbl_recordsacademicos ra ON ra.pk_recordacademico = ip.fk_recordacademico
                   JOIN tbl_inscripciones     i  ON  i.pk_inscripcion	  = ra.fk_inscripcion
                   JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo 	  = i.fk_usuariogrupo
                  WHERE ug.fk_usuario = {$usuario}
                    AND i.fk_periodo =  {$periodo}";
		
		return $this->_db->fetchOne($SQL);
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

  
   public function getListadoinscripcionesempresas($periodo, $sede, $escuela) {
     
        $SQL = "SELECT 	u2.pk_usuario as usuario,
                        ip.pk_inscripcionpasantia, 
                        u3.apellido || ',' || u3.nombre AS tutorempresa,
                        u.apellido || ',' || u.nombre AS tutoracademico,
                        u2.pk_usuario AS ci,
                        u2.apellido || ',' || u2.nombre AS estudiante,
                        ins.nombre AS empresa,
                        ins.direccion, 
                        ins.telefono, 
                        ins.telefono2
                 FROM tbl_inscripcionespasantias ip
      join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
      join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
      join tbl_asignaturas ag on ag.pk_asignatura = ra.fk_asignatura
      join tbl_pensums p on p.pk_pensum =ag.fk_pensum   
                  JOIN tbl_instituciones                ins ON ins.pk_institucion               = ip.fk_institucion 
                  JOIN tbl_contactos      c   ON c.pk_contacto                    = ip.fk_tutor_institucion
                  JOIN tbl_usuariosgrupos   ug  ON ug.pk_usuariogrupo               = ip.fk_tutor_academico
                  JOIN tbl_usuarios     u   ON  u.pk_usuario                    = ug.fk_usuario
      JOIN tbl_usuariosgrupos   ug2 ON ug2.pk_usuariogrupo              = i.fk_usuariogrupo
                  JOIN tbl_usuarios     u2  ON u2.pk_usuario                    = ug2.fk_usuario
                  JOIN tbl_usuariosgrupos   ug3 ON ug3.pk_usuariogrupo              = c.fk_usuariogrupo
                  JOIN tbl_usuarios     u3  ON u3.pk_usuario                    = ug3.fk_usuario
                  WHERE  i.fk_periodo        = {$periodo}
            AND  p.fk_escuela        = {$escuela}
                       AND  i.fk_estructura     = {$sede}
                  AND ag.fk_materia IN (716,717,848,9859)
                  
               ORDER BY u2.apellido;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    } 
  
    public function getListadopasantesasignadosatutores($periodo, $sede, $escuela) {
     
        $SQL = "SELECT 	u2.pk_usuario as usuario,
        --ip.pk_inscripcionpasantia, 
        u.apellido || ',' || u.nombre AS tutoracademico,
        u2.pk_usuario AS ci,
        u2.apellido || ',' || u2.nombre AS estudiante,
        ins.nombre AS empresa,
        u.pk_usuario
        
                 FROM tbl_inscripcionespasantias ip
                  JOIN tbl_instituciones                ins ON ins.pk_institucion               = ip.fk_institucion 
                  JOIN tbl_contactos			c   ON c.pk_contacto                    = ip.fk_tutor_institucion
                  JOIN tbl_usuariosgrupos		ug  ON ug.pk_usuariogrupo               = ip.fk_tutor_academico
                  JOIN tbl_usuarios			u   ON  u.pk_usuario                    = ug.fk_usuario
                  JOIN tbl_recordsacademicos 		ra  ON ra.pk_recordacademico            = ip.fk_recordacademico
                  JOIN tbl_inscripciones		i   ON i.pk_inscripcion                 = ra.fk_inscripcion
                  JOIN tbl_usuariosgrupos		ug2 ON ug2.pk_usuariogrupo              = i.fk_usuariogrupo
                  JOIN tbl_usuarios			u2  ON u2.pk_usuario                    = ug2.fk_usuario
                  JOIN tbl_usuariosgrupos		ug3 ON ug3.pk_usuariogrupo              = c.fk_usuariogrupo
                  JOIN tbl_usuarios			u3  ON u3.pk_usuario                    = ug3.fk_usuario
                  WHERE i.fk_periodo        = {$periodo}
		    AND i.fk_atributo       = {$escuela}
                    AND i.fk_estructura     = {$sede} 
               ORDER BY u2.apellido;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }
    
      public function getCredenciales($ci) {
     $SQL = "SELECT 	ug.fk_usuario, 
                        pr.nombre AS proyecto,
                        it.nombre AS institucion,
                        es.escuela,
                        us.nombre,
                        us.apellido,
                        to_char(fechainicio, 'MM-yyyy') || ' / ' ||  to_char(fechafin, 'MM-yyyy')  as periodo
               FROM tbl_inscripcionespasantias ip
                JOIN tbl_recordsacademicos 		ra ON ra.pk_recordacademico 	= ip.fk_recordacademico
                JOIN tbl_inscripciones                   i ON  i.pk_inscripcion 	= ra.fk_inscripcion
                JOIN tbl_usuariosgrupos 		ug ON ug.pk_usuariogrupo 	=  i.fk_usuariogrupo
                JOIN tbl_asignacionesproyectos          ap ON ap.pk_asignacionproyecto 	= ip.fk_asignacionproyecto
                JOIN tbl_proyectos 			pr ON pr.pk_proyecto 		= ap.fk_proyecto
                JOIN tbl_instituciones                  it ON it.pk_institucion		= pr.fk_institucion
                JOIN tbl_periodos 			pe ON pe.pk_periodo 		=  i.fk_periodo
                JOIN vw_escuelas			es ON es.pk_atributo		=  i.fk_atributo
                JOIN tbl_usuarios			us ON us.pk_usuario		= ug.fk_usuario
              WHERE ug.fk_usuario = {$ci}
              ORDER BY ip.pk_inscripcionpasantia DESC
              LIMIT 1";

	return $this->_db->fetchRow($SQL);
  }

    public function getboolinscritopasantia($ci) {
	$SQL = "SELECT CASE WHEN COUNT(ip.pk_inscripcionpasantia) > 0 THEN TRUE ELSE FALSE END AS pasantia
                  FROM tbl_inscripcionespasantias ip
                   JOIN tbl_recordsacademicos 	  ra  ON ra.pk_recordacademico = ip.fk_recordacademico
                   JOIN tbl_inscripciones	  ins ON ins.pk_inscripcion    = ra.fk_inscripcion
                   JOIN tbl_usuariosgrupos	  ug  ON ug.pk_usuariogrupo    = ins.fk_usuariogrupo
                 WHERE ug.fk_usuario = {$ci}";

	return $this->_db->fetchOne($SQL);
  }
  
  
   public function getListadocredencialservicio($periodo, $sede, $escuela) {
     
        $SQL = "SELECT 	ug.fk_usuario as usuario,
        ug.fk_usuario as ci, 
	us.nombre ||', '|| us.apellido as estudiante,
        pr.nombre AS proyecto,
        it.nombre AS institucion,
        es.escuela,
        to_char(fechainicio, 'MM-yyyy') || ' / ' ||  to_char(fechafin, 'MM-yyyy')  as periodo
               FROM tbl_inscripcionespasantias ip
                JOIN tbl_recordsacademicos 		ra ON ra.pk_recordacademico 	= ip.fk_recordacademico
                JOIN tbl_inscripciones                   i ON  i.pk_inscripcion 	= ra.fk_inscripcion
                JOIN tbl_usuariosgrupos 		ug ON ug.pk_usuariogrupo 	=  i.fk_usuariogrupo
                JOIN tbl_asignacionesproyectos          ap ON ap.pk_asignacionproyecto 	= ip.fk_asignacionproyecto
                JOIN tbl_proyectos 			pr ON pr.pk_proyecto 		= ap.fk_proyecto
                JOIN tbl_instituciones                  it ON it.pk_institucion		= pr.fk_institucion
                JOIN tbl_periodos 			pe ON pe.pk_periodo 		=  i.fk_periodo
                JOIN vw_escuelas			es ON es.pk_atributo		=  i.fk_atributo
                JOIN tbl_usuarios			us ON us.pk_usuario		= ug.fk_usuario
              WHERE i.fk_periodo = {$periodo}
                AND i.fk_atributo = {$escuela}
                AND i.fk_estructura = {$sede}
              ORDER BY 3;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    } 

   public function getEvaluacion($periodo,$escuela,$sede){

$SQL="SELECT ug.fk_usuario as cedula,
u.nombre ||' '|| u.apellido as estudiante, 
ins.nombre as empresa, 
ins.direccion, 
u2.pk_usuario as cedulate,
u2.nombre ||' '|| u2.apellido as tutor_ins, 
u3.pk_usuario as cedulata,
u3.nombre ||' '|| u3.apellido as tutor_a,
--coalesce (qy.nota::float, null)as nota,
--coalesce (qp.nota::float, null)as evacademica,
--coalesce (qx.nota::float, null)as evempresarial,
case when qy.nota::float is null then 0::float when qy.nota::float is not null then qy.nota::float end as nota,
CASE WHEN qp.nota::float > 10 THEN 10 WHEN qp.nota::float is null THEN 0::float ELSE qp.nota::float END as evempresarial, 
case when qx.nota is null then 0::float when qx.nota::float is not null then qx.nota::float end as evacademica,
--case when qp.nota::float > 10 THEn coalesce(qy.nota::float, 0::float)+10+coalesce (qx.nota::float, 0::float) when qp.nota::float <= 10 then coalesce(qy.nota::float, 0::float)+coalesce(qp.nota::float,0::float)+coalesce (qx.nota::float, 0::float) end as total
round((coalesce(qy.nota::float,0)+coalesce (qp.nota::float,0) + coalesce(qx.nota::float,0))::int,0) as total
from tbl_inscripcionespasantias ip
join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
FULL OUTER JOIN (SELECT qp.* FROM 
-- Emrpesarial
 dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
select  DISTINCT qas.responsesummary as usuario, round(((qat.sumgrades*q.grade)/q.sumgrades),2) as nota
FROM mdl_quiz q
JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
JOIN mdl_user u ON u.id = qat.userid
WHERE q.course = 214 
AND qas.responsesummary notnull
AND q.intro ilike ''%$escuela%''
' ) AS qp ( usuario VARCHAR, nota text) ) as qp on qp.usuario = ug.fk_usuario::varchar
-- Academico
FULL OUTER JOIN (SELECT qx.* from dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
select  DISTINCT qas.responsesummary as usuario, round(((qat.sumgrades*q.grade)/q.sumgrades),2) as nota
FROM mdl_quiz q
JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
JOIN mdl_user u ON u.id = qat.userid
WHERE q.course = 215 
AND qas.responsesummary notnull
and q.id = 95
' ) AS qx ( usuario VARCHAR, nota text) ) as qx on qx.usuario = ug.fk_usuario::varchar
-- Autoevaluacion
FULL OUTER JOIN (SELECT * from dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
SELECT  DISTINCT  u.username as usuario,round(((qat.sumgrades*q.grade)/q.sumgrades),2) as nota
FROM mdl_quiz q
JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
JOIN mdl_user u ON u.id = qat.userid
WHERE q.course = 194  
AND q.intro ilike ''%$escuela%''

' ) AS qy ( usuario VARCHAR, nota text) ) as qy on qy.usuario = ug.fk_usuario::varchar

JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
join tbl_usuarios u on ug.fk_usuario = u.pk_usuario
join tbl_instituciones ins on ins.pk_institucion = ip.fk_institucion
join tbl_contactos con on con.pk_contacto = ip.fk_tutor_institucion
join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = con.fk_usuariogrupo
join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
join tbl_usuariosgrupos ug3 on ug3.pk_usuariogrupo = ip.fk_tutor_academico
join tbl_usuarios u3 on u3.pk_usuario = ug3.fk_usuario
JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
where ag.fk_materia IN (716,717,848,9859) 
and i.fk_periodo = {$periodo}
and i.fk_estructura = {$sede}
and esc.pk_atributo = {$escuela}";


    $results = $this->_db->query($SQL);

    return $results->fetchAll();


   } 


   public function getEvaluacionEmpresarial($ci){
    $SQL="SELECT moodle.nota as nota
from tbl_usuarios u 
LEFT OUTER join ( SELECT qp.*
                    FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '

                              select  DISTINCT qas.responsesummary as usuario, round(((qat.sumgrades*q.grade)/q.sumgrades),2) as nota
			FROM mdl_quiz q
			JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
			JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
			JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
			JOIN mdl_user u ON u.id = qat.userid
			WHERE q.course = 214
			AND qas.responsesummary ilike ''%{$ci}%''
        ' ) AS qp( usuario bigint, nota text) ) AS moodle ON moodle.usuario = u.pk_usuario
        where pk_usuario = {$ci}";
    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

   public function getEvaluacionAcademica($ci){

    $SQL="SELECT moodle.nota as nota
from tbl_usuarios u 
LEFT OUTER JOIN( SELECT qp.*
                    FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
		SELECT  DISTINCT qas.responsesummary as usuario, round(((qat.sumgrades*q.grade)/q.sumgrades),2) as nota
			FROM mdl_quiz q
			JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
			JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
			JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
			JOIN mdl_user u ON u.id = qat.userid
				WHERE q.course = 215 
			AND qas.responsesummary ilike ''%{$ci}%''
		        '
         )  AS qp( usuario bigint, nota text) ) AS moodle ON moodle.usuario = u.pk_usuario
       

        where pk_usuario = {$ci}";
    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

   public function getEstudianteInscritoPracticas($ci,$escuela){

    //estoy hay que cambiarlo OJO 
    //poner todo con lo de práctica


    $SQL ="SELECT DISTINCT u.pk_usuario as cedula, u.nombre as nombre , u.apellido as apellido, 
    es.escuela as escuela, me.valor, ra.fk_atributo as estado, i.fk_periodo as periodo
    ,round(moodle.nota,2) as nota
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i ON ra.fk_inscripcion = i.pk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN tbl_usuarios u ON  u.pk_usuario = ug.fk_usuario
    JOIN vw_escuelas es ON es.pk_atributo = i.fk_atributo
    JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
    JOIN vw_materiasestados me on me.pk_atributo = ra.fk_atributo
    FULL OUTER JOIN
                   ( SELECT qp.*
                    FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
                                SELECT DISTINCT u.username, qat.sumgrades/4 as nota, q.name, q.grade::integer
		FROM mdl_quiz q
		JOIN mdl_quiz_attempts qat ON qat.quiz = q.id 
		JOIN mdl_question_usages qu ON qu.id = qat.uniqueid 
		JOIN mdl_question_attempts qas ON qas.questionusageid = qu.id
		JOIN mdl_user u ON u.id = qat.userid
	        where q.course = 194
        	and username = ''{$ci}''
	        ' ) AS qp(usr varchar, nota decimal, nombre varchar, sobre integer) ) AS moodle ON moodle.usr = ug.fk_usuario::text
    WHERE 
    ag.fk_materia IN (716,717,848,9859)
    and u.pk_usuario = {$ci}
    and i.fk_periodo = (SELECT pk_periodo
                        FROM tbl_periodos 
                        WHERE current_date BETWEEN  fechainicio AND fechafin)
    ";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

  public function getEstudiantePracticasListo($ci){

    $SQL = "SELECT DISTINCT u.pk_usuario as cedula, u.nombre as nombre, u.apellido as apellido, 
    es.escuela as escuela
            FROM  tbl_recordsacademicos   ra
            JOIN  tbl_inscripciones       i       ON ra.fk_inscripcion    = i.pk_inscripcion
            JOIN  tbl_usuariosgrupos      ug      ON ug.pk_usuariogrupo   = i.fk_usuariogrupo
            JOIN  tbl_usuarios            u       ON  u.pk_usuario        = ug.fk_usuario
            JOIN  vw_escuelas             es      ON es.pk_atributo       = i.fk_atributo
            JOIN  tbl_asignaturas         ag      ON ag.pk_asignatura     = ra.fk_asignatura
            WHERE  ag.fk_materia IN (716,717,848,9859)
              AND ra.pk_recordacademico = (SELECT DISTINCT MAX(ra1.pk_recordacademico)    
                                             FROM tbl_recordsacademicos   ra1
                                             JOIN tbl_inscripciones       i1      ON ra1.fk_inscripcion   = i1.pk_inscripcion
                                             JOIN tbl_usuariosgrupos      ug1     ON ug1.pk_usuariogrupo  = i1.fk_usuariogrupo
                                             JOIN tbl_usuarios            u1      ON  u1.pk_usuario       = ug1.fk_usuario
                                             JOIN vw_escuelas             es1     ON es1.pk_atributo      = i1.fk_atributo
                                             JOIN  tbl_asignaturas        ag1     ON ag1.pk_asignatura     = ra1.fk_asignatura
                                            WHERE i1.fk_periodo  = i.fk_periodo
                                              AND i1.fk_atributo = i.fk_atributo
                                             -- AND ra1.fk_asignatura IN (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                                              AND ag1.fk_materia IN (716,717,848,9859)
                                              AND u1.pk_usuario = u.pk_usuario)
              and ra.fk_atributo = 862
              and ra.calificacion >= 10   
              and u.pk_usuario = {$ci}
                    ";
    $results = $this->_db->query($SQL);

    return $results->fetchAll();
  } 

  

   public function getIdQuizAcademico(){

    $SQL="SELECT id
          FROM ( SELECT qp.*
                    FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
		select cm.id, shortname as curso
                        from mdl_quiz q
			join mdl_course c on c.id = q.course
                        join mdl_course_modules cm on cm.instance = q.id
                        where  c.id = 215 and cm.id=5931
                        order by 1 
                              ' ) 
        AS qp(id bigint, curso text) ) AS moodle
        where moodle.id is not null";
          $results = $this->_db->query($SQL);

        return $results->fetchAll();
  }

  public function getIdQuizEmpresarial($escuela){

    $SQL="SELECT moodle.periodo, moodle.id
           from(
           SELECT pk_periodo||'-TEPLE' as periodo
           FROM tbl_periodos 
           WHERE current_date BETWEEN  fechainicio AND fechafin) as periodo
           RIGHT OUTER JOIN
                             ( SELECT qp.*
                                FROM dblink('dbname=moodle port=5432 host=75.119.155.86 user=moodle password=M1UN3@OWNER:pochonga', '
                                select cm.id, shortname as periodo
          from mdl_quiz q
          join mdl_course c on c.id = q.course
          join mdl_course_modules cm on cm.instance = q.id
          where  c.id =214 
          and q.intro ilike ''%{$escuela}%''
          limit 1' ) 
          AS qp(id bigint,periodo text) ) AS moodle ON moodle.periodo = periodo.periodo
          where moodle.id is not null";
          $results = $this->_db->query($SQL);

        return $results->fetchAll();
  }



  public function getPasanteEscuela($ci){

    $SQL = "select e.pk_atributo as escuela
            from tbl_inscripciones i
            INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
            INNER JOIN tbl_estructuras es ON es.pk_estructura = i.fk_estructura
            INNER JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
            where ug.fk_usuario = {$ci}
            ORDER BY i.fk_periodo DESC LIMIT 1";
    $results = $this->_db->query($SQL);

    return $results->fetchAll();        

   }
  public function getEstudiantesTutorAcademico($ci){

    $SQL ="select distinct ua.pk_usuario as cedula_tutor, ua.nombre as nombre, ua.apellido as apellido,
          u.pk_usuario as cedula_est, u.nombre||','||u.apellido as estudiante, i.nombre as institucion,
          es.escuela 
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
          where a.fk_materia IN (716,717,848,9859)
          and ins.fk_periodo = 124
          and fk_tutor_academico = (select DISTINCT pk_usuariogrupo
                            from tbl_usuariosgrupos ug1
                            join tbl_asignaciones ag1 on ag1.fk_usuariogrupo = ug1.pk_usuariogrupo
                            where fk_usuario = {$ci})";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

   public function getTutor($ci){

    $SQL="select pk_usuario, nombre,apellido
          from tbl_usuarios
          where pk_usuario ={$ci}";

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
   }

    public function insertRecord($asignatura,$inscripcion){
     
      //904 Materia pre-inscrita
      $SQL="INSERT INTO tbl_recordsacademicos (fk_atributo,fk_asignatura,fk_inscripcion)
            VALUES (864,$asignatura,$inscripcion)";
      $this->_db->query($SQL);      
 } 

    public function insertRecordAsignacion($asignatura,$inscripcion,$asignacion){
     
      //904 Materia pre-inscrita
      $SQL="INSERT INTO tbl_recordsacademicos (fk_atributo,fk_asignatura,fk_inscripcion,fk_asignacion)
            VALUES (864,$asignatura,$inscripcion,$asignacion)";
      $this->_db->query($SQL);      
 } 

 public function getPreinscritosReporte($periodo,$sede,$escuela){

    $SQL="SELECT u.pk_usuario,u.nombre||','||u.apellido as estudiante, u.correo, u.telefono,
                u.telefono_movil as celular, 
                pr.nombre as proyecto, ins.nombre as institucion, 
                ins.telefono as telefono_institucion, ui.nombre||','||ui.apellido as tutor_i, ui.telefono as tel_tutor_i, 
                ua.nombre||','||ua.apellido as tutor_a, ua.telefono as tel_tutor_a
                from tbl_recordsacademicos ra
                join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
                join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
                join tbl_pensums p on p.pk_pensum = a.fk_pensum 
                join vw_escuelas es on es.pk_atributo = p.fk_escuela
                join vw_materiasestados me on me.pk_atributo = ra.fk_atributo
                join vw_materias m on m.pk_atributo = a.fk_materia
                join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
                join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
                join tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
                join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
                join tbl_proyectos pr on pr.pk_proyecto = ap.fk_proyecto
                join tbl_instituciones ins on ins.pk_institucion = ip.fk_institucion
                join tbl_contactos con on con.pk_contacto = ip.fk_tutor_institucion
                join tbl_usuariosgrupos ugi on ugi.pk_usuariogrupo = con.fk_usuariogrupo
                join tbl_usuarios ui on ui.pk_usuario = ugi.fk_usuario
                join tbl_usuariosgrupos uga on uga.pk_usuariogrupo = ip.fk_tutor_academico
                join tbl_usuarios ua on ua.pk_usuario = uga.fk_usuario  
                where a.fk_materia in (
                718,
                719,
                913,
                9738)
                and ra.fk_atributo = 904
                and ap.fk_periodo = {$periodo} +1
                and i.fk_estructura = {$sede}
                and p.fk_escuela = {$escuela}
                order by 1 asc
                ";
                $results = $this->_db->query($SQL);

    return $results->fetchAll();

 }

 public function getUsuarioByRecord($recordacademico){

   $SQL="SELECT distinct u.pk_usuario,ug.pk_usuariogrupo
        from tbl_usuarios u
        join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
        join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
        join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
        where pk_recordacademico = {$recordacademico}";
                
    $results = $this->_db->query($SQL);
    return $results->fetchAll(); 
 }

 public function getLastInscripcionUsuario($periodo,$sede,$escuela,$estudiante){

    $SQL="SELECT distinct pk_inscripcion
          from tbl_usuarios u
          join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
          join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
          where i.fk_periodo = {$periodo}
          and i.fk_atributo = {$escuela}
          and i.fk_estructura = {$sede}
          and u.pk_usuario = {$estudiante}";
    $results = $this->_db->query($SQL);
    return $results->fetchAll();       
 }

  public function insertInscripcionUsuario($usuariogrupo,$periodo,$sede,$escuela,$pensum){
    $SQL="INSERT INTO tbl_inscripciones (fk_usuariogrupo,fk_periodo,fk_atributo,fk_estructura,
          fk_semestre,fk_pensum)
          VALUES($usuariogrupo,$periodo,$escuela,$sede,881,$pensum)";
      //var_dump($SQL);die;      
     $this->_db->query($SQL);      
  }
  public function getPlanilla($ciEstudiante){
    $SQL="SELECT DISTINCT ip.pk_inscripcionpasantia, u.pk_usuario as cedula, u.apellido||','|| u.nombre as estudiante, u.direccion as direccionusuario, u.telefono as telefonousuario,
          u.telefono_movil as celular, u.correo as mail, es.escuela as escuela, p.nombre as proyecto,  ins.nombre as institucion,

          (case when ins.direccion is null then 'Dirección no cargada' else ins.direccion end) as direccionins

          , ins.telefono as tin, subj.periodo

          from tbl_inscripcionespasantias ip
          join tbl_recordsacademicos ra on ra.pk_recordacademico = ip.fk_recordacademico
          join tbl_asignacionesproyectos ap on ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
          join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
          join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
          join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
          join tbl_proyectos p on p.pk_proyecto = ap.fk_proyecto
          join tbl_instituciones ins on ins.pk_institucion = p.fk_institucion
          join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
          join tbl_pensums pen on pen.pk_pensum = a.fk_pensum
          join vw_escuelas es on es.pk_atributo = pen.fk_escuela
          join tbl_periodos per on per.pk_periodo = ap.fk_periodo
          join tbl_usuariosgrupos uga on uga.pk_usuariogrupo = ip.fk_tutor_academico
          join tbl_usuarios ua on ua.pk_usuario = uga.fk_usuario
          join (select DISTINCT u.pk_usuario, i.fk_periodo as periodo
              from vw_materias m
              join tbl_asignaturas a on a.fk_materia = m.pk_atributo
              join tbl_recordsacademicos ra on ra.fk_asignatura = a.pk_asignatura
              join tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
              join tbl_periodos p on p.pk_periodo = i.fk_periodo
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              where a.fk_materia in (8219,9737)
              and u.pk_usuario ={$ciEstudiante}
              and ra.fk_atributo = 862
              and ra.calificacion > 10
          )as subj
          on subj.pk_usuario = u.pk_usuario

          where u.pk_usuario ={$ciEstudiante}
          order by 1 desc limit 1";
    $results = $this ->_db->query($SQL);
    return $results->fetchAll();
  }

 public function getInscripcionesproyectosEscuela($periodo,$sede,$escuela) {
              
    $SQL = "SELECT  ip.pk_inscripcionpasantia, 
                    ip.fk_recordacademico,  
                    ip.fk_asignacionproyecto, 
                    ip.fk_institucion, 
                    ip.fk_tutor_institucion, 
                    ip.fk_tutor_academico, 
                    ip.status_inscripcion,
                    p.nombre AS proyecto,
                    u3.apellido || ', ' || u3.nombre AS tutorinstitucion,
                    u.apellido || ', ' || u.nombre AS tutoracademico,
                    u2.pk_usuario AS ci,
                    u2.apellido || ', ' || u2.nombre AS estudiante
             FROM tbl_inscripcionespasantias ip
              JOIN tbl_asignacionesproyectos  ap  ON ap.pk_asignacionproyecto   = ip.fk_asignacionproyecto
              JOIN tbl_proyectos            p   ON  p.pk_proyecto     = ap.fk_proyecto 
              JOIN tbl_contactos      c   ON c.pk_contacto    = ip.fk_tutor_institucion
              JOIN tbl_usuariosgrupos   ug  ON ug.pk_usuariogrupo = ip.fk_tutor_academico
              JOIN tbl_usuarios     u   ON  u.pk_usuario    = ug.fk_usuario
              JOIN tbl_recordsacademicos    ra  ON ra.pk_recordacademico  = ip.fk_recordacademico
              JOIN tbl_inscripciones    i   ON i.pk_inscripcion   = ra.fk_inscripcion
              JOIN tbl_usuariosgrupos   ug2 ON ug2.pk_usuariogrupo  = i.fk_usuariogrupo
              JOIN tbl_usuarios     u2  ON u2.pk_usuario    = ug2.fk_usuario
              JOIN tbl_usuariosgrupos     ug3 ON ug3.pk_usuariogrupo  = c.fk_usuariogrupo
              JOIN tbl_usuarios     u3  ON u3.pk_usuario    = ug3.fk_usuario
              WHERE ap.fk_periodo={$periodo} and i.fk_estructura={$sede} and i.fk_atributo={$escuela}
              ORDER BY u3.pk_usuario;";
    $results = $this->_db->query($SQL);
    return $results->fetchAll();
    } 

  public function getEstudiantesProyecto($periodo,$sede,$proyecto){
    /*Tutor Institucion -> tu3 , Estudiante -> tu2 , Tutor Academicos -> tu*/
    $SQL ="SELECT tu2.pk_usuario AS cedula, tu2.apellido || ', ' || tu2.nombre AS estudiante, lower(tu2.correo) AS correo, tu2.telefono, tu2.telefono_movil as celular, tt.valor AS escuela, tp.nombre AS proyecto, tii.nombre AS institucion
            FROM tbl_inscripcionespasantias tip
            JOIN tbl_asignacionesproyectos  tap   ON tap.pk_asignacionproyecto  = tip.fk_asignacionproyecto
            JOIN tbl_atributos              tt    ON tap.fk_escuela             = tt.pk_atributo
            JOIN tbl_proyectos              tp    ON tp.pk_proyecto             = tap.fk_proyecto 
            JOIN tbl_contactos              tc    ON tc.pk_contacto             = tip.fk_tutor_institucion
            JOIN tbl_instituciones          tii   ON tc.fk_institucion          = tii.pk_institucion
            JOIN tbl_usuariosgrupos         tg    ON tg.pk_usuariogrupo         = tip.fk_tutor_academico
            JOIN tbl_usuarios               tu    ON tg.fk_usuario              = tu.pk_usuario
            JOIN tbl_recordsacademicos      tr    ON tr.pk_recordacademico      = tip.fk_recordacademico
            JOIN tbl_inscripciones          ti    ON tr.fk_inscripcion          = ti.pk_inscripcion
            JOIN tbl_usuariosgrupos         tg2   ON ti.fk_usuariogrupo         = tg2.pk_usuariogrupo 
            JOIN tbl_usuarios               tu2   ON tg2.fk_usuario             = tu2.pk_usuario
            JOIN tbl_usuariosgrupos         tg3   ON tg3.pk_usuariogrupo        = tc.fk_usuariogrupo 
            JOIN tbl_usuarios               tu3   ON tg3.fk_usuario             = tu3.pk_usuario
            WHERE tap.fk_periodo = {$periodo}
            AND ti.fk_estructura = {$sede}
            AND tp.pk_proyecto = {$proyecto}
            ORDER BY tt.valor, tu2.pk_usuario;";
    $results=$this->_db->query($SQL);
    return (array) $results->fetchAll();
 }

 public function getEstudiantesProyectosEscuelas($periodo,$sede,$escuela){
    /*Tutor Institucion -> tu3 , Estudiante -> tu2 , Tutor Academicos -> tu*/
    $SQL = "SELECT tu2.pk_usuario AS cedula, tu2.apellido || ', ' || tu2.nombre AS estudiante, lower(tu2.correo) AS correo, tu2.telefono, tu2.telefono_movil as celular, tt.valor AS escuela, tp.nombre AS proyecto, tii.nombre AS institucion
             FROM tbl_inscripcionespasantias  tip
              JOIN tbl_asignacionesproyectos  tap   ON tap.pk_asignacionproyecto  = tip.fk_asignacionproyecto
              JOIN tbl_atributos              tt    ON tap.fk_escuela             = tt.pk_atributo
              JOIN tbl_proyectos              tp    ON tp.pk_proyecto             = tap.fk_proyecto 
              JOIN tbl_contactos              tc    ON tc.pk_contacto             = tip.fk_tutor_institucion
              JOIN tbl_instituciones          tii   ON tc.fk_institucion          = tii.pk_institucion
              JOIN tbl_usuariosgrupos         tg    ON tg.pk_usuariogrupo         = tip.fk_tutor_academico
              JOIN tbl_usuarios               tu    ON tg.fk_usuario              = tu.pk_usuario
              JOIN tbl_recordsacademicos      tr    ON tr.pk_recordacademico      = tip.fk_recordacademico
              JOIN tbl_inscripciones          ti    ON tr.fk_inscripcion          = ti.pk_inscripcion
              JOIN tbl_usuariosgrupos         tg2   ON ti.fk_usuariogrupo         = tg2.pk_usuariogrupo 
              JOIN tbl_usuarios               tu2   ON tg2.fk_usuario             = tu2.pk_usuario
              JOIN tbl_usuariosgrupos         tg3   ON tg3.pk_usuariogrupo        = tc.fk_usuariogrupo 
              JOIN tbl_usuarios               tu3   ON tg3.fk_usuario             = tu3.pk_usuario
              WHERE tap.fk_periodo = {$periodo} 
              AND ti.fk_estructura = {$sede} 
              AND ti.fk_atributo = {$escuela}
              ORDER BY tp.nombre, tt.valor, tu2.pk_usuario;";
    $results=$this->_db->query($SQL);
    return (array) $results->fetchAll();
 }

  public function getEstudiantesContinuidad($sede, $periodo, $escuela) {
              
    $SQL = "SELECT ti.fk_periodo AS periodo, tu.pk_usuario AS cedula , tu.apellido || ', ' || tu.nombre AS estudiante, lower(tu.correo) AS correo, tu.telefono_movil AS celular, tu.telefono, tt.valor AS escuela, tp.nombre AS proyecto
            FROM tbl_usuarios tu
            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
            JOIN tbl_inscripcionespasantias tip ON tr.pk_recordacademico = tip.fk_recordacademico
            JOIN tbl_asignacionesproyectos tap ON tip.fk_asignacionproyecto = tap.pk_asignacionproyecto
            JOIN tbl_proyectos tp ON tap.fk_proyecto = tp.pk_proyecto
            JOIN tbl_atributos tt on ti.fk_atributo = tt.pk_atributo
            /*Estudiante con Materia Reprobada*/
            WHERE tu.pk_usuario IN (
                  SELECT tu.pk_usuario
                  FROM tbl_usuarios  tu
                  JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                  JOIN tbl_inscripciones ti1 ON tg.pk_usuariogrupo = ti1.fk_usuariogrupo
                  JOIN tbl_recordsacademicos tr ON ti1.pk_inscripcion = tr.fk_inscripcion
                  JOIN tbl_inscripcionespasantias tip ON tr.pk_recordacademico = tip.fk_recordacademico
                  JOIN tbl_asignacionesproyectos tap ON tip.fk_asignacionproyecto = tap.pk_asignacionproyecto
                  JOIN tbl_proyectos tp ON tap.fk_proyecto = tp.pk_proyecto
                  WHERE ti1.fk_estructura = ti.fk_estructura
                  AND ti1.fk_atributo = ti.fk_atributo
                  AND tr.calificacion < 10)
            /*Estudiante con Materia Aprobada*/
            AND tu.pk_usuario NOT IN (
                  SELECT tu.pk_usuario
                  FROM tbl_usuarios  tu
                  JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                  JOIN tbl_inscripciones ti1 ON tg.pk_usuariogrupo = ti1.fk_usuariogrupo
                  JOIN tbl_recordsacademicos tr ON ti1.pk_inscripcion = tr.fk_inscripcion
                  JOIN tbl_inscripcionespasantias tip ON tr.pk_recordacademico = tip.fk_recordacademico
                  JOIN tbl_asignacionesproyectos tap ON tip.fk_asignacionproyecto = tap.pk_asignacionproyecto
                  JOIN tbl_proyectos tp ON tap.fk_proyecto = tp.pk_proyecto
                  WHERE ti1.fk_estructura = ti.fk_estructura
                  AND ti1.fk_atributo = ti.fk_atributo
                  AND tr.calificacion > 10)
            AND ti.fk_estructura = {$sede}
            AND ti.fk_periodo = {$periodo}
            AND ti.fk_atributo = {$escuela}
            ORDER BY ti.fk_atributo, ti.fk_periodo, tu.pk_usuario";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    } 


 public function getProyecto($proyecto){

  $SQL = " SELECT tp.nombre AS proyecto
            FROM tbl_proyectos tp
            WHERE tp.pk_proyecto = {$proyecto}";
  $results=$this->_db->query($SQL);
  return $results->fetchAll();
 }

  public function getEstudianteTutoresProfesional($periodo,$ci){

    $SQL = "SELECT u.pk_usuario,(u.nombre||' '||u.apellido) AS estudiante,
                   case when u2.pk_usuario is null then 'No Posee Tutor Institucional'
                        else (UPPER(u2.nombre||' '||u2.apellido))
                        end as institucional,
                   case when u3.pk_usuario is null then 'No Posee Tutor Académico'
                        else (UPPER(u3.nombre||' '||u3.apellido) )
                        end AS academico 
            FROM tbl_usuarios               u 
            JOIN tbl_usuariosgrupos         ug  ON ug.fk_usuario         = u.pk_usuario 
            JOIN tbl_inscripciones          i   ON i.fk_usuariogrupo     = ug.pk_usuariogrupo 
            JOIN tbl_recordsacademicos      ra  ON ra.fk_inscripcion     = i.pk_inscripcion 
            JOIN tbl_asignaturas            asi ON asi.pk_asignatura     = ra.fk_asignatura 
            LEFT OUTER JOIN tbl_inscripcionespasantias ip  ON ip.fk_recordacademico = ra.pk_recordacademico 
            LEFT OUTER JOIN tbl_contactos              c   ON c.pk_contacto         = ip.fk_tutor_institucion 
            LEFT OUTER JOIN tbl_usuariosgrupos         ug2 ON ug2.pk_usuariogrupo   = c.fk_usuariogrupo 
            LEFT OUTER JOIN tbl_usuarios               u2  ON u2.pk_usuario         = ug2.fk_usuario 
            LEFT OUTER JOIN tbl_usuariosgrupos         ug3 ON ug3.pk_usuariogrupo   = ip.fk_tutor_academico 
            LEFT OUTER JOIN tbl_usuarios               u3  ON u3.pk_usuario         = ug3.fk_usuario 

            WHERE i.fk_periodo = {$periodo}  
            AND u.pk_usuario   = {$ci}
            AND asi.fk_materia IN (716,717,848,9896,9716,9859)";

          $results = $this->_db->query($SQL);

          return (array)$results->fetchAll();

  }

  public function getEstudianteTutorComunitario($ci, $periodo){

    $SQL ="SELECT u2.pk_usuario AS ci,
                  u2.apellido || ',' || u2.nombre AS estudiante,
                  p.nombre AS proyecto,
                  case when u3.apellido is null AND u3.nombre is null then 'No Posee Tutor Institucional'
                    else (UPPER(u3.apellido || ',' || u3.nombre))
                    end as tutorinstitucion,
                  case when u.apellido is null AND u.nombre is null then 'No Posee Tutor Académico'
                    else (UPPER(u.apellido || ',' || u.nombre))
                    end AS tutoracademico 
                FROM tbl_inscripcionespasantias ip
                JOIN tbl_asignacionesproyectos  ap  ON ap.pk_asignacionproyecto = ip.fk_asignacionproyecto
                JOIN tbl_proyectos          p   ON  p.pk_proyecto     = ap.fk_proyecto 
                JOIN tbl_contactos    c   ON c.pk_contacto    = ip.fk_tutor_institucion
                JOIN tbl_usuariosgrupos   ug  ON ug.pk_usuariogrupo = ip.fk_tutor_academico
                JOIN tbl_usuarios   u   ON  u.pk_usuario    = ug.fk_usuario
                JOIN tbl_recordsacademicos  ra  ON ra.pk_recordacademico  = ip.fk_recordacademico
                JOIN tbl_inscripciones    i   ON i.pk_inscripcion   = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos   ug2 ON ug2.pk_usuariogrupo  = i.fk_usuariogrupo
                JOIN tbl_usuarios   u2  ON u2.pk_usuario    = ug2.fk_usuario
                JOIN tbl_usuariosgrupos   ug3 ON ug3.pk_usuariogrupo  = c.fk_usuariogrupo
                JOIN tbl_usuarios   u3  ON u3.pk_usuario    = ug3.fk_usuario
                WHERE ap.fk_periodo = {$periodo}
                AND u2.pk_usuario   = {$ci}
                ORDER BY p.nombre, u2.apellido;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

  }
}
