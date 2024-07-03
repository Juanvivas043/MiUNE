<?php

class Models_DbTable_Recaudos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_recaudos';
    protected $_primary  = 'pk_recaudo';
    protected $_sequence = false;
   
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
         $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

public function setSearch($searchData) {
        $this->searchData = $searchData;
    }         

    public function getRecaudos($ci, $periodo){
	   if(!is_numeric($ci)) return null;
                   
        $SQL = "SELECT r.pk_recaudo,
                       r.fk_inscripcion, 
                       r.fk_nombre_recaudo,
                       r.dir_archivo,
                       a.valor,
                       LTRIM(TO_CHAR(u.pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                       u.apellido || ', ' || u.nombre as estudiante
                  FROM tbl_recaudos r
                  JOIN tbl_inscripciones           i ON i.pk_inscripcion      	  =  r.fk_inscripcion
                  JOIN tbl_usuariosgrupos	  ug ON ug.pk_usuariogrupo    	  =  i.fk_usuariogrupo 		  
                  JOIN tbl_atributos 		   a ON a.pk_atributo		  =  r.fk_nombre_recaudo
                  JOIN tbl_usuarios                u ON u.pk_usuario              = ug.fk_usuario           
                 WHERE ug.fk_usuario = {$ci}
                   AND i.fk_periodo = {$periodo}
                ORDER BY 3,1;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    
        public function getRecaudosmaster($escuela , $periodo, $sede){
            
          $SQL="SELECT sqt2.pk_recaudo,  sqt2.ci as ci, sqt2.estudiante, case when tutores is null then 'Tutores Sin Asignar' else tutores end, sqt2.valor, estado,
                sqt2.dir_archivo,sqt2.impreso, 
                case when sqt2.impreso = 'Revisado' then 1
         when sqt2.impreso = 'Nuevo' then 2
         when sqt2.impreso is null then 3 end as ordinal
            from ( 
                SELECT sqt.pk_recaudo,  ci, estudiante, tutora||' / '||tutori as tutores, sqt.valor, 
                (case when sqt.valor  is null then 'Recaudo Sin Cargar' else 'Recaudo Cargado' end) as estado, sqt.dir_archivo,
                case when sqt.impreso = 'false' then 'Nuevo'
         when sqt.impreso = 'true' then 'Revisado'
         else sqt.impreso end
                from (
                        SELECT DISTINCT pk_recaudo, LTRIM(TO_CHAR(u.pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                        u.apellido || ',' || u.nombre as estudiante, uI.nombre||','||uI.apellido as tutori,
                        uA.nombre||','||uA.apellido as tutora, a.valor, r.dir_archivo, r.estado::text as impreso
                        FROM tbl_recordsacademicos ra
                        JOIN tbl_inscripciones i ON ra.fk_inscripcion = i.pk_inscripcion
                        JOIN tbl_usuariosgrupos ug  ON ug.pk_usuariogrupo   = i.fk_usuariogrupo
                        JOIN tbl_usuarios u ON  u.pk_usuario  = ug.fk_usuario
                        JOIN vw_escuelas es ON es.pk_atributo = i.fk_atributo
                        JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
                        LEFT OUTER JOIN tbl_inscripcionespasantias ip on ip.fk_recordacademico = ra.pk_recordacademico
                        LEFT OUTER JOIN tbl_contactos c on c.pk_contacto = ip.fk_tutor_institucion
                        LEFT OUTER JOIN tbl_usuariosgrupos ugI on ugI.pk_usuariogrupo = c.fk_usuariogrupo
                        LEFT OUTER JOIN tbl_usuarios uI on uI.pk_usuario = ugI.fk_usuario
                        LEFT OUTER JOIN tbl_usuariosgrupos ugA on ugA.pk_usuariogrupo = ip.fk_tutor_academico
                        LEFT OUTER JOIN tbl_usuarios uA on uA.pk_usuario = ugA.fk_usuario
                        LEFT OUTER JOIN tbl_recaudos r on r.fk_inscripcion = i.pk_inscripcion
                        LEFT OUTER JOIN tbl_atributos a ON a.pk_atributo =  r.fk_nombre_recaudo
                        WHERE i.fk_periodo  = {$periodo}
                        AND i.fk_atributo = {$escuela}
                        AND i.fk_estructura = {$sede}
                        --AND ra.fk_asignatura IN  (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                        AND ag.fk_materia IN (716,717,848,9859)
                       
                )as sqt) as sqt2
    order by ordinal";  
                  
//        $SQL = "SELECT r.pk_recaudo,
//                       r.fk_inscripcion, 
//                       r.fk_nombre_recaudo,
//                       r.dir_archivo,
//                       a.valor,
//                       LTRIM(TO_CHAR(u.pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
//                       u.apellido || ', ' || u.nombre as estudiante
//                  FROM tbl_recaudos r
//                  JOIN tbl_inscripciones           i ON i.pk_inscripcion      	  = r.fk_inscripcion
//                  JOIN tbl_usuariosgrupos	  ug ON ug.pk_usuariogrupo    	  =  i.fk_usuariogrupo 		  
//                  JOIN tbl_atributos 		   a ON a.pk_atributo		  =  r.fk_nombre_recaudo
//                  JOIN tbl_usuarios                u ON u.pk_usuario              = ug.fk_usuario
//                  WHERE i.fk_atributo = {$escuela} 
//                  and i.fk_periodo = {$periodo}
//                ORDER BY 3,1;";
//                  
                  

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    
   public function getFiltrosRecaudos($id){
      if(!is_numeric($id)) return null;

      $SQL = "SELECT 
                       p.pk_periodo,
                       (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(p.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(p.fechafin, 'MM-yyyy') END) as periodo,
                       es3.pk_estructura as pk_sede,
                       es3.nombre as sede,
                       vwe.pk_atributo as pk_escuela,
                       vwe.escuela as escuela,
                       pp.pk_pensum,
                       pp.nombre as pensum,
                       vws.pk_atributo as pk_semestre,
                       vws.id as semestre,
                       vwmt.pk_atributo as pk_materia,
                       vwmt.materia as materia,
                       vwt.pk_atributo as pk_turno,
                       vwt.valor as turno,
                       vwse.pk_atributo as pk_seccion,
                       vwse.valor as seccion
                       
                FROM tbl_asignaciones   asg 
                JOIN tbl_usuariosgrupos        usg ON usg.pk_usuariogrupo   = asg.fk_usuariogrupo
                JOIN tbl_usuarios               us ON us.pk_usuario         = usg.fk_usuario
                JOIN tbl_asignaturas           ast ON ast.pk_asignatura     = asg.fk_asignatura
                JOIN tbl_periodos                p ON p.pk_periodo          = asg.fk_periodo
                JOIN tbl_estructuras           es1 ON es1.pk_estructura     = asg.fk_estructura
                JOIN tbl_estructuras           es2 ON es2.pk_estructura     = es1.fk_estructura
                JOIN tbl_estructuras           es3 ON es3.pk_estructura     = es2.fk_estructura
                JOIN tbl_pensums                pp ON pp.pk_pensum          = ast.fk_pensum
                JOIN vw_materias              vwmt ON vwmt.pk_atributo      = ast.fk_materia
                JOIN vw_escuelas              vwe  ON vwe.pk_atributo       = pp.fk_escuela
                JOIN vw_turnos                 vwt ON vwt.pk_atributo       = asg.fk_turno_alterado
                JOIN vw_semestres              vws ON vws.pk_atributo       = asg.fk_semestre
                JOIN vw_secciones      	      vwse ON vwse.pk_atributo      = asg.fk_seccion
                JOIN tbl_recordsacademicos 	ra ON ra.fk_asignacion      = asg.pk_asignacion
                -- JOIN tbl_inscripcionespasantias ip ON ip.fk_recordacademico = ra.pk_recordacademico 
                JOIN tbl_inscripciones		 i ON i.pk_inscripcion      = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos		ug ON ug.pk_usuariogrupo    =  i.fk_usuariogrupo
                
                WHERE ug.fk_usuario = {$id}
                AND ra.fk_asignatura IN (11930,12016,12090,12168,11840,11763,12254,12328,12410,12506,12569,12649);";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }

    public function getCountByRecaudos($pk_recaudo) {
        
              $SQL = "SELECT COUNT(pk_recaudo)
		        FROM tbl_recaudos r
		        WHERE r.pk_recaudo = {$pk_recaudo}";
		
		return $this->_db->fetchOne($SQL);
	}
    

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function getRow($id) {
		if(!isset($id)) return;
		
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

 
    
    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }
    

public function getPK() {
	$SQL = "SELECT pk_recaudo
                  FROM tbl_recaudos 
                WHERE {$this->Where}";

	return $this->_db->fetchOne($SQL);
  }
  
public function getrecaudoscargados($usuario, $periodo) {
	$SQL = "SELECT count(r.pk_recaudo)
                  FROM tbl_recaudos r
                  JOIN tbl_inscripciones      i ON  i.pk_inscripcion     = r.fk_inscripcion
                  JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo    = i.fk_usuariogrupo
                 WHERE i.fk_periodo = {$periodo}
                   AND ug.fk_usuario = {$usuario};";

	return $this->_db->fetchOne($SQL);
  }  


  
   public function gettipoderecaudo() {
        $SQL = "SELECT pk_atributo, valor
                  FROM tbl_atributos
                  WHERE fk_atributotipo = 38";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

   public function getDirRecaudo($recaudo){

    $SQL = "SELECT dir_archivo,estado
                  FROM tbl_recaudos
                  WHERE pk_recaudo={$recaudo}";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();

   }

  public function updateEstado($recaudo){

    $SQL = "UPDATE tbl_recaudos
            set estado = true 
            WHERE pk_recaudo={$recaudo}";

       $this->_db->query($SQL);

   } 
        
}
