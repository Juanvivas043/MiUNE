<?php
class Models_DbTable_Clases extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_clases';
    protected $_primary  = 'pk_clase';
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
    public function setData($Data) {
        $Where = array(' AND  es3.pk_estructura = ' => $Data['sede'],
                       ' AND  vwe.pk_atributo   = ' => $Data['escuela'],
                       ' AND   pp.pk_pensum     = ' => $Data['pensum'],
                       ' AND    p.pk_periodo    = ' => $Data['periodo'],
                       ' AND  vws.pk_atributo   = ' => $Data['semestre'],
                       ' AND vwmt.pk_atributo   = ' => $Data['materia'],
                       ' AND  vwt.pk_atributo   = ' => $Data['turno'],
                       ' AND vwse.pk_atributo   = ' => $Data['seccion'],
                       ' AND   us.pk_usuario    = ' => $Data['usuario']);

		
		$Where = array_filter($Where);
        $Where = $this->SwapBytes_Array->implode(' ', $Where);
        $Where = ltrim($Where, ' AND ');
        $this->Where = $Where;
    }

    public function getCronogramas() {
        $SQL = "SELECT cl.pk_clase,
                       cl.numero,
                       cl.fecha,
                       cl.descripcion,
                       cl.contenido,
                       vwes.valor AS tipo_estrategia,
                       vwev.valor AS tipo_evaluacion,
                       cl.puntaje
                FROM   tbl_clases cl
                JOIN tbl_asignaciones   asg ON asg.pk_asignacion   = cl.fk_asignacion
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_periodos         p ON p.pk_periodo        = asg.fk_periodo
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_evaluaciones   vwev ON vwev.pk_atributo    = cl.fk_tipoevaluacion
                JOIN vw_estrategias    vwes ON vwes.pk_atributo    = cl.fk_tipoestrategia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_turnos          vwt ON vwt.pk_atributo     = asg.fk_turno_alterado
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                WHERE {$this->Where}
                ORDER  BY cl.numero ASC";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

	public function getCountByAsignacion($fk_asignacion) {
		$SQL = "SELECT COUNT(pk_clase)
		        FROM tbl_clases
		        WHERE fk_asignacion = {$fk_asignacion}";
		
		return $this->_db->fetchOne($SQL);
	}

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }
    
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
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

	public function copyRow($ids, $asignatura) {
		if(!is_numeric($asignatura)) return null;
		if(!is_string($ids))         return;

        $SQL = "INSERT INTO tbl_clases (numero, fecha, descripcion, contenido, fk_tipoevaluacion, fk_tipoestrategia, puntaje, fk_asignacion)
			    SELECT numero,
                       fecha,
                       descripcion,
                       contenido,
                       fk_tipoevaluacion,
                       fk_tipoestrategia,
                       puntaje,
                       {$asignatura}
                FROM   tbl_clases
                WHERE pk_clase IN ({$ids})";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}

   public function getFiltrosClase($pk_clase){
      if(!is_numeric($pk_clase)) return null;

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
                       vwse.valor as seccion,
                       cl.pk_clase,
                       cl.numero,
                       cl.fecha,
                       cl.descripcion,
                       cl.contenido,
                       vwes.valor AS tipo_estrategia,
                       vwev.valor AS tipo_evaluacion,
                       cl.puntaje
                FROM   tbl_clases cl
                JOIN tbl_asignaciones   asg ON asg.pk_asignacion   = cl.fk_asignacion
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_periodos         p ON p.pk_periodo        = asg.fk_periodo
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_evaluaciones   vwev ON vwev.pk_atributo    = cl.fk_tipoevaluacion
                JOIN vw_estrategias    vwes ON vwes.pk_atributo    = cl.fk_tipoestrategia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_turnos          vwt ON vwt.pk_atributo     = asg.fk_turno_alterado
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                WHERE cl.pk_clase = {$pk_clase}
                ORDER  BY cl.numero ASC;
";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

   }
}
