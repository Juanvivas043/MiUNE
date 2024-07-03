<?php
class Models_DbTable_Asignaturas extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_asignaturas';
    protected $_primary  = 'pk_asignatura';
    protected $_sequence = true;

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->AuthSpace      = new Zend_Session_Namespace('Zend_Auth');
    }

    public function setData($Data, $Keys) {
	$Keys = array_fill_keys($Keys, null);
	$Data = array_intersect_key($Data, $Keys);

	$Where = array(' AND  a.fk_periodo        = ' => $Data['periodo'],
		' AND e3.pk_estructura     = ' => $Data['sede'],
		' AND  p.fk_escuela        = ' => $Data['escuela'],
		' AND aa.fk_pensum         = ' => $Data['pensum'],
		' AND aa.fk_semestre       = ' => $Data['semestre'],
		' AND aa.fk_materia        = ' => $Data['materia'],
		' AND  a.fk_turno          = ' => $Data['turno'],
		' AND  a.fk_dia            = ' => $Data['dia'],
		' AND  u.pk_usuario        = ' => $Data['usuario']);

	$Where = array_filter($Where);
	$Where = $this->SwapBytes_Array->implode(' ', $Where);

	// En caso de que se requiera la sección, debemos realizar una busqueda
	// un poco mas compleja, a razon de que se maneja las secciones padres e
	// hijas con una misma vista y condicion.
	if (isset($Data['seccion'])) {
	  $Where .= " AND  sc.valor ILIKE (SELECT valor FROM vw_secciones WHERE pk_atributo = {$Data['seccion']})::text || '%'";
	}

	$Where = ltrim($Where, ' AND ');

	$this->Where = $Where;
    }

    public function getEquivalenciasExterna($Ci, $Pensum, $Escuela, $Estado) {
        if(empty($Ci))      return;
        if(empty($Pensum))  return;
        if(empty($Escuela)) return;
        if(empty($Estado))  return;

        $SQL = "SELECT ag.pk_asignatura,
	               ag.fk_pensum,
	                s.id as semestre,
                       ag.codigopropietario,
	                m.valor as materia,
	               ag.unidadcredito,
	                       (SELECT DISTINCT ra1.fk_atributo
		                FROM tbl_recordsacademicos ra1
		                JOIN tbl_inscripciones      i1 ON  i1.pk_inscripcion  = ra1.fk_inscripcion
		                JOIN tbl_usuariosgrupos    ug1 ON ug1.pk_usuariogrupo =  i1.fk_usuariogrupo
		                WHERE fk_asignatura   = ag.pk_asignatura
		                   AND i1.fk_atributo =  p.fk_escuela
                                   AND ((ra1.fk_atributo = 862 AND ra1.calificacion >= 10)
                                    OR  (ra1.fk_atributo = 864 AND ra1.calificacion  = 0)
                                    OR  (ra1.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)))
	                           AND ug1.fk_usuario = {$Ci}) as disponible,
	                       (SELECT DISTINCT true
	                         FROM tbl_recordsacademicos ra1
	                         JOIN tbl_inscripciones      i1 ON  i1.pk_inscripcion  = ra1.fk_inscripcion
	                         JOIN tbl_usuariosgrupos    ug1 ON ug1.pk_usuariogrupo =  i1.fk_usuariogrupo
	                         WHERE ra1.fk_asignatura = ag.pk_asignatura
			           AND  i1.fk_atributo   = p.fk_escuela
		                   AND ra1.fk_atributo   = {$Estado}
		                   AND  i1.fk_periodo    = 0
			           AND ug1.fk_usuario    = {$Ci}) as seleccionada
	                FROM tbl_asignaturas ag
	                JOIN tbl_atributos    m ON m.pk_atributo = ag.fk_materia
	                JOIN tbl_atributos    s ON s.pk_atributo = ag.fk_semestre
	                JOIN tbl_pensums      p ON p.pk_pensum   = ag.fk_pensum
	                WHERE  p.fk_escuela = {$Escuela}
	                  AND  p.pk_pensum  = {$Pensum}
	                  AND ag.codigopropietario NOT IN ('07000000', '06000000')
	                ORDER BY s.id, ag.codigopropietario";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getEquivalenciasInterna($Ci, $Pensum, $Escuela) {
        if(empty($Ci))      return;
        if(empty($Pensum))  return;
        if(empty($Escuela)) return;

        $SQL = "SELECT ag.pk_asignatura,
                       ag.fk_pensum,
                        s.id as semestre,
                       ag.codigopropietario,
                       m.valor as materia,
                       ag.unidadcredito,
                       (SELECT DISTINCT MAX(i1.fk_periodo)
                         FROM tbl_recordsacademicos ra1
                         JOIN tbl_inscripciones      i1 ON  i1.pk_inscripcion  = ra1.fk_inscripcion
                         JOIN tbl_usuariosgrupos    ug1 ON ug1.pk_usuariogrupo =  i1.fk_usuariogrupo
                         WHERE fk_asignatura  = ag.pk_asignatura
                           AND i1.fk_atributo =  p.fk_escuela
	                   AND ((ra1.fk_atributo = 862 AND ra1.calificacion >= 10)
                            OR  (ra1.fk_atributo = 864 AND ra1.calificacion  = 0)
                            OR  (ra1.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)))
                           AND ug1.fk_usuario = {$Ci}) as fk_periodo,
                       (SELECT calificacion
                        FROM (SELECT DISTINCT i1.fk_periodo, ra1.calificacion
                              FROM tbl_recordsacademicos ra1
                              JOIN tbl_inscripciones      i1 ON  i1.pk_inscripcion  = ra1.fk_inscripcion
                              JOIN tbl_usuariosgrupos    ug1 ON ug1.pk_usuariogrupo =  i1.fk_usuariogrupo
                              WHERE fk_asignatura  = ag.pk_asignatura
                                AND i1.fk_atributo =  p.fk_escuela
	                        AND ((ra1.fk_atributo = 862 AND ra1.calificacion >= 10)
                                 OR  (ra1.fk_atributo = 864 AND ra1.calificacion  = 0)
                                 OR   ra1.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
                                AND ug1.fk_usuario = {$Ci}
                              ORDER BY i1.fk_periodo DESC
                              LIMIT 1) AS sq) as calificacion,
                       (SELECT fk_atributo
                        FROM (SELECT DISTINCT i1.fk_periodo, ra1.fk_atributo
                              FROM tbl_recordsacademicos ra1
                              JOIN tbl_inscripciones      i1 ON  i1.pk_inscripcion  = ra1.fk_inscripcion
                              JOIN tbl_usuariosgrupos    ug1 ON ug1.pk_usuariogrupo =  i1.fk_usuariogrupo
                              WHERE fk_asignatura  = ag.pk_asignatura
                                AND i1.fk_atributo =  p.fk_escuela
	                        AND ((ra1.fk_atributo = 862 AND ra1.calificacion >= 10)
                                 OR  (ra1.fk_atributo = 864 AND ra1.calificacion  = 0)
                                 OR  (ra1.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)))
                                AND ug1.fk_usuario = {$Ci}
                              ORDER BY i1.fk_periodo DESC
                              LIMIT 1) AS sq) as disponible
                FROM tbl_asignaturas ag
                JOIN tbl_atributos    m ON m.pk_atributo = ag.fk_materia
                JOIN tbl_atributos    s ON s.pk_atributo = ag.fk_semestre
                JOIN tbl_pensums      p ON p.pk_pensum   = ag.fk_pensum
                WHERE  p.fk_escuela = {$Escuela}
                  AND  p.pk_pensum  = {$Pensum}
                  AND ag.codigopropietario NOT IN ('07000000', '06000000')
                ORDER BY s.id, ag.codigopropietario";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

	public function getSelect($Pensum, $Semestre) {
        $SQL = "SELECT a.pk_asignatura, m.materia
				FROM tbl_asignaturas a
				JOIN vw_materias     m ON m.pk_atributo = a.fk_materia
				WHERE a.fk_pensum   = {$Pensum}
				  AND a.fk_semestre = {$Semestre}
				ORDER BY m.materia";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}
  public function getSelectmaterias($Periodo, $Sede, $Escuela, $Semestre) {
        $SQL = "SELECT a.pk_asignatura, m.materia
        FROM tbl_asignaturas a
        JOIN vw_materias     m ON m.pk_atributo = a.fk_materia
        WHERE a.fk_pensum   = {$Pensum}
          AND a.fk_semestre = {$Semestre}
        ORDER BY m.materia";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
  }
        public function getSelectPensums() {
            if (empty($this->Where))
              return;

            $SQL = "SELECT DISTINCT p.pk_pensum, p.nombre
                    FROM tbl_asignaturas    aa
                    JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                    WHERE {$this->Where}
                    ORDER BY p.nombre ASC";

            $results = $this->_db->query($SQL);

            return (array) $results->fetchAll();
        }
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function updateRow($id, $data) {
        $data          = array_filter($data);
        $rows_affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $rows_affected;
    }

  public function getRows() {
	if (empty($this->Where))
	  return;

	  $SQL = "SELECT pk_asignatura
                    ,m.materia
				FROM tbl_asignaturas    aa
				JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
				JOIN vw_materias         m ON  m.pk_atributo     = aa.fk_materia
				JOIN vw_semestres       sm ON sm.pk_atributo     = aa.fk_semestre
				JOIN vw_escuelas        es ON es.pk_atributo     =  p.fk_escuela
                WHERE {$this->Where}
				ORDER BY 1 DESC";

	$results = $this->_db->query($SQL);

	return (array) $results->fetchAll();
  }

  public function getRecordAcademicoParcialEstudiante($periodo, $regimen_historico, $sede, $pensum, $escuela, $idUser){

    if(isset($idUser)){
      $cedula = $idUser;
    }else{
      $cedula = $this->AuthSpace->userId;
    }

    $SQL = "SELECT DISTINCT ma.pk_atributo, ma.materia as materia, a2.valor as estado, ra.pk_recordacademico,a1.valor as evaluacion,rh.pk_regimen_historico as regimen_historico, rae.calificacion as calificacion, re.ordinal, re.evaluable, re.fk_lapso
            from tbl_asignaturas    ag
            JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
            JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
            JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
            JOIN tbl_atributos              a1 ON a1.pk_atributo = re.fk_tipo_evaluacion
            JOIN vw_materias                ma on ma.pk_atributo = ag.fk_materia
            JOIN tbl_pensums                 p ON p.pk_pensum = ag.fk_pensum
            --LEFT SI LA ASIGNACION NO ES OBLIGATORIA
            JOIN tbl_asignaciones           ac ON ac.fk_asignatura = ag.pk_asignatura
            JOIN tbl_estructuras            e1 ON e1.pk_estructura    = ac.fk_estructura
            JOIN tbl_estructuras            e2 ON e2.pk_estructura    = e1.fk_estructura
            JOIN tbl_estructuras            e3 ON e3.pk_estructura    = e2.fk_estructura
            JOIN tbl_usuariosgrupos         ug ON ug.pk_usuariogrupo  =  ac.fk_usuariogrupo
            JOIN tbl_usuarios                u ON  u.pk_usuario       = ug.fk_usuario
            --NOTAS
            JOIN tbl_recordsacademicos      ra ON ra.fk_asignatura = ag.pk_asignatura AND ra.fk_asignacion = ac.pk_asignacion
            JOIN tbl_atributos              a2 ON a2.pk_atributo = ra.fk_atributo
            JOIN tbl_inscripciones           i ON i.pk_inscripcion = ra.fk_inscripcion AND i.fk_periodo = ac.fk_periodo
            JOIN tbl_usuariosgrupos        ug1 ON ug1.pk_usuariogrupo = i.fk_usuariogrupo
            JOIN tbl_usuarios               u1 ON u1.pk_usuario = ug1.fk_usuario
            LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra.pk_recordacademico AND fk_evaluacion = pk_regimen_evaluacion
            WHERE u1.pk_usuario = {$cedula}
            AND rh.pk_regimen_historico = {$regimen_historico}
            and i.fk_estructura = {$sede} 
            AND i.fk_periodo = {$periodo}
            AND p.fk_escuela = {$escuela}
            AND ag.fk_pensum = {$pensum}
            ORDER BY 2,8,7";

            $results = $this->_db->query($SQL);
            return (array) $results->fetchAll();
  }

  public function getRegimenHistoricoEstudiante($periodo, $escuela, $pensum, $idUser=null){
    if(isset($idUser)){
      $cedula = $idUser;
    }else{
      $cedula = $this->AuthSpace->userId;
    }

    $SQL = "SELECT DISTINCT rh.pk_regimen_historico
            FROM tbl_asignaturas    ag
            JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
            JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
            JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
            JOIN tbl_recordsacademicos      ra ON ra.fk_asignatura = ag.pk_asignatura 
            JOIN tbl_inscripciones           i ON i.pk_inscripcion = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos        ug1 ON ug1.pk_usuariogrupo = i.fk_usuariogrupo
            JOIN tbl_usuarios               u1 ON u1.pk_usuario = ug1.fk_usuario
            WHERE u1.pk_usuario = {$cedula}
            AND i.fk_periodo = {$periodo}
            AND i.fk_atributo = {$escuela}
            AND ag.fk_pensum = {$pensum}
            order by 1"; 

    $results = $this->_db->query($SQL);
    return $results->fetchAll();

  }

  public function getDatosRecordEstudiante($Params = array()){
    if(empty($Params)) { return; }

    $SQL = "";
    $escuela = "";
    $Cedula = $Params['usuario'];

    if(isset($Params['escuela'])) $escuela = $Params['escuela'];

    switch ($Params['action'] ) {
      case 'periodo':
          $SQL .= "select distinct i.fk_periodo, (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(pe.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(pe.fechafin, 'MM-yyyy') END) as nombre";
        break;
      case 'sede':
          $SQL .= "select distinct s.pk_estructura, s.nombre";
        break;
      case 'escuela':
          $SQL .= "select distinct e.pk_atributo, e.escuela";
        break;
      case 'pensum':
          $SQL .= "select distinct ag.fk_pensum, p.nombre";
        break;
    }

    $SQL .= " FROM tbl_asignaturas    ag
            JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
            JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
            JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
            JOIN tbl_recordsacademicos ra ON ra.fk_asignatura = ag.pk_asignatura 
            JOIN tbl_inscripciones      i ON i.pk_inscripcion = ra.fk_inscripcion and i.fk_periodo >= rh.fk_periodo_inicio
            JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
            JOIN vw_escuelas e on i.fk_atributo = e.pk_atributo
            JOIN vw_sedes s on i.fk_estructura = s.pk_estructura
            join tbl_pensums p on p.pk_pensum = ag.fk_pensum
            JOIN tbl_periodos        pe ON pe.pk_periodo       = i.fk_periodo
            WHERE ug.fk_usuario = {$Cedula} ";

    switch ($Params['action'] ) {
      case 'periodo':
          $SQL .= "ORDER BY i.fk_periodo desc";          
        break;
      case 'sede':
          $SQL .= "";
        break;
      case 'escuela':
          $SQL .= "AND i.fk_periodo = " . $Params['periodo'];
          $SQL .= "";
        break;
      case 'pensum':
          $SQL .= "AND i.fk_atributo = " . $Params['escuela'];
          $SQL .= '';
        break;
    }

    $results = $this->_db->query($SQL);

    return $results->fetchAll();
  }


  public function validacionPasantia($ci,$pk_asignaturas){

    $SQL = "SELECT DISTINCT MAX(i.fk_periodo) as periodo
            from tbl_recordsacademicos ra
            JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
            JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.fk_asignatura
            where fk_usuario = {$ci}
            AND ((ra.fk_atributo = 862 AND ra.calificacion>=10) OR ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
            AND asi.pk_asignatura in ({$pk_asignaturas})";

    $results = (array)$this->_db->fetchRow($SQL);

    return $results;

  }

}
