<?php

class Models_DbTable_Asignaciones extends Zend_Db_Table {

  protected $_schema = 'produccion';
  protected $_name = 'tbl_asignaciones';
  protected $_primary = 'pk_asignacion';
  protected $_sequence = false;
  private $searchParams = array('sqt2.semestre ','sqt2.materia','sqt2.valor','sqt2.pensum','sqt2.escuela',
                                'sqt2.apellido','sqt2.nombre','sqt2.dia','sqt2.horainicio',
                                'sqt2.edificio','sqt2.salones');

  public function init() {
  $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
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
    ' AND e3.pk_estructura     = ' => $Data['sede'],
    ' AND  p.fk_escuela        = ' => $Data['escuela'],
    ' AND aa.fk_pensum         = ' => $Data['pensum'],
    ' AND  a.fk_semestre_alterado       = ' => $Data['semestre'],
    ' AND aa.fk_materia        = ' => $Data['materia'],
    ' AND aa.pk_asignatura        = ' => $Data['asignatura'],
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

  public function getRows() {
  if (empty($this->Where))
    return;

    $SQL = "SELECT pk_asignacion
                   ,d.dia AS dia
               ,sc.valor AS seccion
                       ,TO_CHAR(h.horainicio, 'hh:mi') || ' / ' || TO_CHAR(h.horafin, 'hh:mi') AS horario
             ,m.materia || (CASE WHEN char_length(a.nota) > 0 THEN ' (' || a.nota || ')' ELSE '' END) AS materia
                   ,t.valor AS turno
                   ,sm.id AS semestre
                   ,u.nombre
                   ,u.apellido
                   ,u.apellido || ', ' || u.nombre AS profesor
                   ,e1.nombre AS aula
                   ,e2.nombre AS edificio
                   ,a.cupos
                   ,a.nota
                     ,a.cupos_max
        FROM tbl_asignaciones    a
        JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  a.fk_usuariogrupo
        JOIN tbl_usuarios        u ON  u.pk_usuario      = ug.fk_usuario
        JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
        JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
        JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
        JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
        JOIN tbl_horarios        h ON  h.pk_horario      = a.fk_horario
        JOIN vw_materias         m ON  m.pk_atributo     = aa.fk_materia
        JOIN vw_semestres       sm ON sm.pk_atributo     =  a.fk_semestre
        JOIN vw_escuelas        es ON es.pk_atributo     =  p.fk_escuela
        JOIN vw_turnos           t ON  t.pk_atributo     =  a.fk_turno
        JOIN vw_semestres       sa ON sa.pk_atributo     =  a.fk_semestre
        JOIN vw_secciones       sc ON sc.pk_atributo     =  a.fk_seccion
        JOIN vw_dias             d ON  d.pk_atributo     =  a.fk_dia
                WHERE {$this->Where}
        ORDER BY d.id, sc.valor, h.horainicio, m.materia ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectSedes() {
      if($this->Where=''){
      $SQL = "SELECT DISTINCT e3.pk_estructura, e3.nombre
                    FROM tbl_asignaciones    a
                    JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                    JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                    JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                    JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                    JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                    JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                    WHERE {$this->Where}
                    ORDER BY e3.pk_estructura, e3.nombre ASC";
      }else{
        $SQL = "SELECT DISTINCT e3.pk_estructura, e3.nombre
                    FROM tbl_asignaciones    a
                    JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                    JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                    JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                    JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                    JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                    JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                    ORDER BY e3.pk_estructura, e3.nombre ASC";

      }
  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }



  public function getSelectEscuelas() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT ee.pk_atributo, ee.escuela
                FROM tbl_asignaciones    a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   = a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_escuelas        ee ON ee.pk_atributo     = p.fk_escuela
                WHERE {$this->Where}
                ORDER BY ee.escuela ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectSemestres() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT ss.pk_atributo, ss.id
                FROM tbl_asignaciones    a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_semestres       ss ON ss.pk_atributo     =  a.fk_semestre
                WHERE {$this->Where}
                ORDER BY ss.id ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }


  public function getSelectPeriodos() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT pe.pk_periodo,
                (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(pe.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(pe.fechafin, 'MM-yyyy') END) as nombre

                FROM tbl_asignaciones    a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_periodos        pe ON pe.pk_periodo       = a.fk_periodo
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_semestres       ss ON ss.pk_atributo     =  a.fk_semestre
                WHERE {$this->Where}
                ORDER BY pe.pk_periodo DESC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectPeriodosRegimenes() {

  $SQL = "SELECT DISTINCT pe.pk_periodo,
                (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(pe.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(pe.fechafin, 'MM-yyyy') END) as nombre

                FROM tbl_asignaciones    a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_periodos        pe ON pe.pk_periodo       = a.fk_periodo
				JOIN tbl_asignaturas_regimenes rea ON rea.fk_asignatura = aa.pk_asignatura
				JOIN tbl_regimenes_historicos rh ON pe.pk_periodo >= rh.fk_periodo_inicio
				AND (pe.pk_periodo <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
                AND rea.fk_regimen_historico = rh.pk_regimen_historico
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_semestres       ss ON ss.pk_atributo     =  a.fk_semestre ";
                if (!empty($this->Where)) $SQL.= " WHERE {$this->Where} ";
                $SQL .= " ORDER BY pe.pk_periodo DESC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectMaterias() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT m.pk_atributo, m.materia
                FROM tbl_asignaciones    a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_materias         m ON  m.pk_atributo     = aa.fk_materia
                WHERE {$this->Where}
                ORDER BY m.materia ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectSecciones() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT s.pk_atributo, s.valor
                FROM tbl_asignaciones a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_secciones        s ON  s.pk_atributo     =  a.fk_seccion
                WHERE {$this->Where}
                ORDER BY s.valor ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

public function getSelectSeccionesCoincidenciaMateriaPensum($cedula,$periodo,$escuela,$materia,$estructura,$pensum) {
    if (empty($cedula)) return;
    if (empty($periodo)) return;
    if (empty($escuela)) return;
    if (empty($materia)) return;

    $SQL = "SELECT pk_atributo , valor ,PK_ASIGNACION
FROM (
 SELECT pk_atributo,valor,sqt.pk_asignacion,CASE WHEN pk_atributo IN (SELECT sig.fk_seccion as valor
            FROM tbl_recordsacademicos rec
                JOIN tbl_inscripciones ins ON ins.pk_inscripcion = rec.fk_inscripcion
                JOIN tbl_usuariosgrupos usu ON usu.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_asignaturas asig ON asig.pk_asignatura = rec.fk_asignatura
                JOIN tbl_asignaciones sig ON sig.pk_asignacion = rec.fk_asignacion
                WHERE usu.fk_usuario = {$cedula}
                and ins.fk_periodo = {$periodo}
                and asig.fk_materia = {$materia}) THEN 1
                ELSE 0 END as valor1
    FROM(
    SELECT DISTINCT s.pk_atributo, s.valor ||
    (SELECT CASE WHEN (
                        SELECT COUNT(asg1.pk_asignacion) > 0
                        FROM tbl_asignaciones asg1
                        JOIN tbl_recordsacademicos ra1 ON ra1.fk_asignacion = asg1.pk_asignacion
                        JOIN tbl_inscripciones ins1 ON ins1.pk_inscripcion = ra1.fk_inscripcion
                        JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = ins1.fk_usuariogrupo
                        WHERE ug1.fk_usuario = {$cedula}
                        AND ra1.fk_atributo = 864
                        AND asg1.fk_dia = a.fk_dia
                        AND asg1.fk_horario = a.fk_horario
                        AND asg1.fk_periodo = a.fk_periodo
                        AND asg1.fk_asignatura <> a.fk_asignatura
                    )
                THEN
                    ' - Coincide en horario'
                ELSE
                    ''
                END
                )as valor,a.pk_asignacion
                        FROM tbl_asignaciones a
                        JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                        JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                        JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                        JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                        JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                        JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                        JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                        JOIN vw_secciones        s ON  s.pk_atributo     =  a.fk_seccion
                        JOIN vw_materias mat ON mat.pk_atributo = aa.fk_materia
            where a.fk_periodo = {$periodo}
            and mat.pk_atributo = {$materia}
            and p.fk_escuela = {$escuela}
            and e3.pk_estructura = {$estructura}
            and p.pk_pensum = {$pensum}
                ) as sqt
        	order by 4 DESC) as sqt2";

    $results = $this->_db->query($SQL);
    return (array) $results->fetchAll();
  }

public function getSelectSeccionesCoincidenciaMateria($cedula,$periodo,$escuela,$materia,$estructura,$pensum) {
    if (empty($cedula)) return;
    if (empty($periodo)) return;
    if (empty($escuela)) return;
    if (empty($materia)) return;

    $SQL = "SELECT pk_atributo , valor ,PK_ASIGNACION
FROM (
 SELECT pk_atributo,valor,sqt.pk_asignacion,CASE WHEN pk_atributo IN (SELECT sig.fk_seccion as valor
            FROM tbl_recordsacademicos rec
                JOIN tbl_inscripciones ins ON ins.pk_inscripcion = rec.fk_inscripcion
                JOIN tbl_usuariosgrupos usu ON usu.pk_usuariogrupo = ins.fk_usuariogrupo
                JOIN tbl_asignaturas asig ON asig.pk_asignatura = rec.fk_asignatura
                JOIN tbl_asignaciones sig ON sig.pk_asignacion = rec.fk_asignacion
                WHERE usu.fk_usuario = {$cedula}
                and ins.fk_periodo = {$periodo}
                and asig.fk_materia = {$materia}) THEN 1
                ELSE 0 END as valor1
    FROM(
    SELECT DISTINCT s.pk_atributo, s.valor ||
    (SELECT CASE WHEN (
                        SELECT COUNT(asg1.pk_asignacion) > 0
                        FROM tbl_asignaciones asg1
                        JOIN tbl_recordsacademicos ra1 ON ra1.fk_asignacion = asg1.pk_asignacion
                        JOIN tbl_inscripciones ins1 ON ins1.pk_inscripcion = ra1.fk_inscripcion
                        JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = ins1.fk_usuariogrupo
                        WHERE ug1.fk_usuario = {$cedula}
                        AND ra1.fk_atributo = 864
                        AND asg1.fk_dia = a.fk_dia
                        AND asg1.fk_horario = a.fk_horario
                        AND asg1.fk_periodo = a.fk_periodo
                        AND asg1.fk_asignatura <> a.fk_asignatura
                    )
                THEN
                    ' - Coincide en horario'
                ELSE
                    ''
                END
                )as valor,a.pk_asignacion
                        FROM tbl_asignaciones a
                        JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                        JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                        JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                        JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                        JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                        JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                        JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                        JOIN vw_secciones        s ON  s.pk_atributo     =  a.fk_seccion
                        JOIN vw_materias mat ON mat.pk_atributo = aa.fk_materia
            where a.fk_periodo = {$periodo}
            and mat.pk_atributo = {$materia}
            and p.fk_escuela = {$escuela}
            and e3.pk_estructura = {$estructura}
            and p.codigopropietario = {$pensum}
                ) as sqt
            order by 4 DESC) as sqt2";

    $results = $this->_db->query($SQL);
    return (array) $results->fetchAll();
  }

  public function getSelectSeccionesCoincidencia($cedula) {
  if (empty($this->Where))
    return;

  $SQL = "
SELECT pk_atributo, MAX(valor) as valor
FROM(
 SELECT DISTINCT s.pk_atributo, s.valor ||
   (SELECT CASE WHEN (
                       SELECT COUNT(asg1.pk_asignacion) > 0
                       FROM tbl_asignaciones asg1
                       JOIN tbl_recordsacademicos ra1 ON ra1.fk_asignacion = asg1.pk_asignacion
                       JOIN tbl_inscripciones ins1 ON ins1.pk_inscripcion = ra1.fk_inscripcion
                       JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = ins1.fk_usuariogrupo
                       WHERE ug1.fk_usuario = {$cedula}
                       AND ra1.fk_atributo = 864
                       AND asg1.fk_dia = a.fk_dia
                       AND asg1.fk_horario = a.fk_horario
                       AND asg1.fk_periodo = a.fk_periodo
                       AND asg1.fk_asignatura <> a.fk_asignatura
                )
               THEN
                   ' - Coincide en horario'
               ELSE
                   ''
               END
               )as valor
                       FROM tbl_asignaciones a
                       JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                       JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                       JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                       JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                       JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                       JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                       JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                       JOIN vw_secciones        s ON  s.pk_atributo     =  a.fk_seccion
                    JOIN vw_materias mat ON mat.pk_atributo = aa.fk_materia
                WHERE {$this->Where}
            ) as sqt
                GROUP BY 1
                ORDER BY 2 ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }
  public function getSelectTurnos() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT t.pk_atributo, t.valor
                FROM tbl_asignaciones a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                JOIN vw_turnos           t ON  t.pk_atributo     =  a.fk_turno
                WHERE {$this->Where}
                ORDER BY t.valor ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getSelectPensums() {
  if (empty($this->Where))
    return;

  $SQL = "SELECT DISTINCT p.pk_pensum, p.nombre
                FROM tbl_asignaciones a
                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ag.fk_usuario
                JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
                JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
                JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
                JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
                WHERE {$this->Where}
                ORDER BY p.nombre ASC";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

  public function getmateriascustom($semestre, $periodo, $sede, $escuela){
      if (empty($semestre)) return;
    if (empty($periodo)) return;
    if (empty($escuela)) return;
    if (empty($sede)) return;
      $SQL = "SELECT distinct a.pk_asignatura, m.materia
              FROM tbl_asignaturas a
              join tbl_pensums p on p.pk_pensum = a.fk_pensum
              JOIN vw_materias     m ON m.pk_atributo = a.fk_materia
              join tbl_asignaciones asi on asi.fk_asignatura = a.pk_asignatura
              join tbl_estructuras est on est.pk_estructura = asi.fk_estructura
              join tbl_estructuras est2 on est2.pk_estructura = est.fk_estructura
              where a.fk_semestre = {$semestre}
              and asi.fk_periodo = {$periodo}
              and est2.fk_estructura = {$sede}
              and p.fk_escuela in( {$escuela} )
              ORDER BY m.materia";

  $results = $this->_db->query($SQL); 

  return (array) $results->fetchAll(); 
  }

  public function getConsignacionesEstados() {
  $SQL = "SELECT pk_asignacion,fk_materia, codigopropietario,semestre, materia, seccion, nombre, apellido, CASE WHEN MIN = 2 THEN 'EMITIDA' ELSE CASE WHEN MIN = 1 THEN 'Por imprimir' ELSE 'NO EMITIDA' end end as estado, MIN
                FROM (
                SELECT DISTINCT aa.fk_materia, aa.codigopropietario,
                               ss.id as semestre,
                               materia,
                               s.valor as seccion,
                               u.nombre,
                               u.apellido,
                               MIN(ae.id),
                               MAX(pk_asignacion) as pk_asignacion
          FROM tbl_asignaciones    a
                JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = a.pk_asignacion
          JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
          JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
          JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
          JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
          JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  a.fk_usuariogrupo
          JOIN tbl_usuarios        u ON  u.pk_usuario      = ug.fk_usuario
          JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
          JOIN vw_secciones        s ON  s.pk_atributo     =  a.fk_seccion
          JOIN vw_semestres       ss ON ss.pk_atributo     =  a.fk_semestre
          JOIN vw_materias         m ON  m.pk_atributo     = aa.fk_materia
                JOIN vw_asignacionesestados ae ON ae.pk_atributo = a.fk_estado
                WHERE ra.fk_atributo IN (862, 864, 1699)
                  AND u.pk_usuario <> 0
                  AND {$this->Where}
                GROUP BY aa.fk_materia, aa.codigopropietario,ss.id,materia,s.valor,u.nombre,u.apellido
                ORDER BY semestre, materia, seccion
                  )as sqt
                order by 10,4,6";

  $results = $this->_db->query($SQL);

  return (array) $results->fetchAll();
  }

     public function getAsignacionSimilar($asignacion) {
        $SQL = "
            SELECT asg_otras.pk_asignacion
            FROM tbl_asignaciones asg
            JOIN tbl_estructuras e1 ON e1.pk_estructura = asg.fk_estructura
            JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
            JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
            JOIN tbl_asignaciones asg_otras ON
            asg.fk_asignatura        = asg_otras.fk_asignatura
            AND   asg.fk_seccion           = asg_otras.fk_seccion
            AND   asg.fk_periodo           = asg_otras.fk_periodo
            AND   asg.fk_semestre          = asg_otras.fk_semestre
            JOIN tbl_estructuras e4 ON e4.pk_estructura = asg_otras.fk_estructura
            JOIN tbl_estructuras e5 ON e5.pk_estructura = e4.fk_estructura
            JOIN tbl_estructuras e6 ON e6.pk_estructura = e5.fk_estructura
            WHERE asg.pk_asignacion = {$asignacion}
            AND e6.pk_estructura = e3.pk_estructura
           ";

  $results = $this->_db->query($SQL);
  return (array) $results->fetchAll();
  }

  public function getPK() {
  $SQL = "SELECT pk_asignacion
        FROM tbl_asignaciones    a
        JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  a.fk_usuariogrupo
        JOIN tbl_usuarios        u ON  u.pk_usuario      = ug.fk_usuario
        JOIN tbl_estructuras    e1 ON e1.pk_estructura   =  a.fk_estructura
        JOIN tbl_estructuras    e2 ON e2.pk_estructura   = e1.fk_estructura
        JOIN tbl_estructuras    e3 ON e3.pk_estructura   = e2.fk_estructura
        JOIN tbl_pensums         p ON  p.pk_pensum       = aa.fk_pensum
        JOIN tbl_horarios        h ON  h.pk_horario      = a.fk_horario
        JOIN vw_materias         m ON  m.pk_atributo     = aa.fk_materia
        JOIN vw_semestres       sm ON sm.pk_atributo     =  a.fk_semestre
        JOIN vw_escuelas        es ON es.pk_atributo     =  p.fk_escuela
        JOIN vw_turnos           t ON  t.pk_atributo     =  a.fk_turno
        JOIN vw_semestres       sa ON sa.pk_atributo     =  a.fk_semestre
        JOIN vw_secciones       sc ON sc.pk_atributo     =  a.fk_seccion
        JOIN vw_dias             d ON  d.pk_atributo     =  a.fk_dia
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
     $SQL = "SELECT  ag.pk_asignacion,
        ag.fk_asignatura,
        ag.fk_seccion,
        ag.fk_dia,
        ag.fk_turno,
        ag.fk_semestre_alterado,
        a.fk_semestre,
        ag.fk_estructura,
        ag.preinscribible,
        ag.fk_periodo,
        ag.fk_horario,
        ag.nota,
        ag.fk_turno_alterado,
        ag.disponible,
        ag.fk_estado,
        ag.cupos,
        ag.cupos_max,
        ag.fk_usuariogrupo,
        e2.pk_estructura AS fk_edificio,
        a.fk_materia,
        a.fk_pensum,
        a.fk_semestre as semestre,
        ag.fk_asignatura as asignatura,
        e3.pk_estructura as pk_sede,
        e3.nombre AS sede,
        e.escuela,
        e.pk_atributo as fk_escuela,
        sm.id as semestre,
        m.materia AS materia,
        sc.valor AS seccion,
        e2.nombre as edif,
        e1.nombre as Aula,
        u.primer_nombre ||' ' || u.primer_apellido as prof,
        pen.nombre as pen
        FROM tbl_asignaciones ag
        JOIN tbl_asignaturas ass ON ass.pk_asignatura = ag.fk_asignatura
        JOIN tbl_pensums pen ON pen.pk_pensum = ass.fk_pensum
        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ag.fk_usuariogrupo
        JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
        JOIN tbl_estructuras e1 ON e1.pk_estructura = ag.fk_estructura
        JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
        JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
            JOIN tbl_asignaturas  a ON  a.pk_asignatura = ag.fk_asignatura
        JOIN tbl_pensums      p ON  p.pk_pensum     = a.fk_pensum
            JOIN vw_materias      m ON  m.pk_atributo   =  a.fk_materia
        JOIN vw_escuelas      e ON  e.pk_atributo   =  p.fk_escuela
        JOIN vw_semestres    sm ON sm.pk_atributo   = a.fk_semestre
        JOIN vw_secciones    sc ON sc.pk_atributo   = ag.fk_seccion
        WHERE pk_asignacion = {$id}";



  return $this->_db->fetchRow($SQL);
  }

  public function getHorarioPorRecordAcademico($id) {
  $SQL = "select sc.valor AS seccion
                ,TO_CHAR(h.horainicio, 'HH12:MI') || ' / ' || TO_CHAR(h.horafin, 'HH12:MI') AS horario
                ,m.materia AS materia
                ,u.apellido || ', ' || u.nombre AS profesor
                ,ag.nota
                ,s.valor AS seccion
                ,d.dia
                ,sm.id   AS semestre
                ,t.valor AS turno
                ,e2.nombre AS edificio
                ,e1.nombre AS aula
                    FROM tbl_asignaciones ag
                    JOIN tbl_asignaturas  ate ON ate.pk_asignatura   = ag.fk_asignatura
                    JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo = ag.fk_usuariogrupo
                    JOIN tbl_usuarios           u ON  u.pk_usuario      = ug.fk_usuario
                    JOIN tbl_horarios           h ON  h.pk_horario      = ag.fk_horario
                    JOIN tbl_estructuras       e1 ON e1.pk_estructura   = ag.fk_estructura
                    JOIN tbl_estructuras       e2 ON e2.pk_estructura   = e1.fk_estructura
                    JOIN vw_secciones           s ON  s.pk_atributo     = ag.fk_seccion
                    JOIN vw_turnos              t ON  t.pk_atributo     = ag.fk_turno
                    JOIN vw_semestres          sm ON sm.pk_atributo     = ag.fk_semestre
                    JOIN vw_secciones          sc ON sc.pk_atributo     = ag.fk_seccion
                    JOIN vw_dias                d ON  d.pk_atributo     = ag.fk_dia
                    JOIN vw_materias            m ON  m.pk_atributo     = ate.fk_materia
WHERE ARRAY[ag.fk_periodo,
      ag.fk_estructura,
      ag.fk_usuariogrupo,
      ag.fk_asignatura,
      ag.fk_seccion] =  (SELECT ARRAY[sq_ag.fk_periodo,
            sq_ag.fk_estructura,
            sq_ag.fk_usuariogrupo,
            sq_ag.fk_asignatura,
                                          sq_ag.fk_seccion]
                              FROM tbl_recordsacademicos sq_ra
            JOIN tbl_asignaciones      sq_ag ON sq_ag.pk_asignacion = sq_ra.fk_asignacion
                  WHERE sq_ra.pk_recordacademico = {$id})
        ORDER BY 1, 2";

  $results = $this->_db->query($SQL);
  $results = (array) $results->fetchAll();

  return $results;
  }

  public function addRow($data) {
  $data = array_filter($data);
  $affected = $this->insert($data);

  return $affected;
  }

  public function updateRow($id, $data) {
  $data = array_filter($data);

  if (empty($data['nota'])) {
    $data['nota'] = null;
  }

  $rows_affected = $this->update($data, $this->_primary . ' = ' . (int) $id);

  return $rows_affected;
  }

  public function updateCuposByArray($array,$cupos,$cupos_max){
    $SQL ="UPDATE tbl_asignaciones
           SET cupos = {$cupos}, cupos_max = {$cupos_max}
           WHERE pk_asignacion IN (";
    foreach ($array as $key => $value) {
      $SQL .= $value .",";
    }

    $SQL = trim($SQL,',');
    $SQL .= ");";

    $this->_db->query($SQL);
  }

  public function updateAulasByArray($array,$aula){
//updatea todas las aulas de las asignaciones fusionadas
    $SQL ="UPDATE tbl_asignaciones
           SET fk_estructura = {$aula}
           WHERE pk_asignacion IN (";
    foreach ($array as $key => $value) {
      $SQL .= $value["pk_asignacion"] .",";
    }

    $SQL = trim($SQL,',');
    $SQL .= ");";

    $this->_db->query($SQL);
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

  public function getCoincidenciaSalon($periodo, $dia, $hora, $salon, $asignacion) {
  if (empty($asignacion))
    $asignacion = 0;

  $SQL = "SELECT fn_xrxx_horarios_coincide_salon($periodo, $dia, $hora, $salon, $asignacion);";
  //var_dump($SQL);die;
  return $this->_db->fetchOne($SQL);
  }

  public function getTodasCoincidenciaSalon($periodo, $dia, $hora, $salon, $asignacion) {
    $SQL = "SELECT pk_asignacion
            FROM tbl_asignaciones
            WHERE   fk_periodo = {$periodo} AND
              fk_estructura = {$salon} AND
              fk_horario = {$hora} AND
              fk_dia = {$dia} AND
              pk_asignacion <> {$asignacion} AND
              fk_usuariogrupo <> 38971";
    $results = $this->_db->query($SQL);
    $results = (array) $results->fetchAll();
    return $results;
  }

  public function getTodasCoincidenciaHorarioCupos($asignacion){
    $SQL ="SELECT DISTINCT asgFO.pk_asignacion
            /* SELECT asg.pk_asignacion */
            /* , array_agg(DISTINCT asgO.pk_asignacion) as otras_horas */
            /* , array_agg(DISTINCT asgF.pk_asignacion) as fusiones */
            /* , array_agg(DISTINCT asgFO.pk_asignacion) as fusiones_otras_horas */
            FROM tbl_asignaciones asg
            LEFT OUTER JOIN tbl_asignaciones asgO ON
            asgO.fk_periodo = asg.fk_periodo AND
            asgO.fk_seccion = asg.fk_seccion AND
            asgO.fk_asignatura = asg.fk_asignatura AND
            asgO.fk_semestre = asg.fk_semestre
            LEFT OUTER JOIN tbl_asignaciones asgF ON
            asgF.fk_periodo = asg.fk_periodo AND
            asgF.fk_usuariogrupo = asg.fk_usuariogrupo AND
            asgF.fk_dia = asg.fk_dia AND
            asgF.fk_estructura = asg.fk_estructura AND
            asgF.fk_horario = asg.fk_horario
            LEFT OUTER JOIN tbl_asignaciones asgFO ON
            asgFO.fk_periodo = asgF.fk_periodo AND
            asgFO.fk_seccion = asgF.fk_seccion AND
            asgFO.fk_asignatura = asgF.fk_asignatura AND
            asgFO.fk_semestre = asgF.fk_semestre
            WHERE asg.pk_asignacion = $asignacion --Asignacion
            /* GROUP BY 1 */";
    $results = $this->_db->query($SQL);
    $results = (array) $results->fetchAll();
    return $results;
  }

  public function getTodasCoincidenciaHorario($periodo, $dia, $hora, $usuario, $asignacion){
    $SQL ="SELECT a.pk_asignacion
            FROM tbl_asignaciones a
            INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = a.fk_usuariogrupo
            WHERE a.fk_dia = {$dia} AND
                a.fk_horario = {$hora} AND (
                a.fk_periodo IN (SELECT DISTINCT e.pk_periodo
                    FROM tbl_periodos r, tbl_periodos e
                    WHERE r.pk_periodo <> e.pk_periodo
                      AND ((e.fechainicio >= r.fechainicio AND e.fechainicio <= r.fechafin) OR
                           (r.fechainicio >= e.fechainicio AND r.fechainicio <= e.fechafin))
                      AND r.pk_periodo = {$periodo}) OR
                a.fk_periodo = {$periodo}) AND
                ug.pk_usuariogrupo = {$usuario} AND
                a.pk_asignacion <> 0 AND
                ug.pk_usuariogrupo <> 38971;";

    $results = $this->_db->query($SQL);
    $results = (array) $results->fetchAll();
    return $results;
  }

  public function getCoincidenciaHorario($periodo, $dia, $hora, $usuario, $asignacion) {
    $SQL = "SELECT fn_xrxx_horarios_coincide_profesor($periodo, $dia, $hora, $usuario, $asignacion);";
    return $this->_db->fetchOne($SQL);
  }
   public function getSQLCount($periodo,$sede,$escuela) {


        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(DISTINCT asig.fk_asignatura)
                from tbl_asignaciones asig
                join tbl_estructuras sal on asig.fk_estructura = sal.pk_estructura
                join tbl_estructuras edf on edf.pk_estructura = sal.fk_estructura
                join tbl_estructuras sed on sed.pk_estructura = edf.fk_estructura
                join tbl_asignaturas asg ON asg.pk_asignatura = asig.fk_asignatura
                join tbl_pensums pen ON pen.pk_pensum = asg.fk_pensum
                join tbl_usuariosgrupos usug ON usug.pk_usuariogrupo=asig.fk_usuariogrupo
                where asig.fk_periodo={$periodo}
                and sed.pk_estructura={$sede}
                and pen.fk_escuela={$escuela}
                {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }
    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

 public function getCoincidenciaMateria($periodo,$sede,$escuela,$itemPerPage,$pageNumber){



    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $pageNumber  = ($pageNumber - 1) * $itemPerPage;

    $SQL="select DISTINCT sqt2.semestre as semestre, sqt2.materia as materia, sqt2.valor as seccion, sqt2.pensum as pensum, sqt2.escuela as escuelas,
    sqt2.apellido||','||sqt2.nombre as profesor, sqt2.dia as dia, sqt2.hora as horario,
    sqt2.edificio as edificio, sqt2.salones as salones, sqt2.cupos as cupos
    from(
  SELECT  DISTINCT vma.materia,sqt.fk_horario, sqt.fk_dia,
    p.fk_escuela, sqt.pk_estructura, u.nombre, asig.fk_periodo,
    sqt.salones, u.apellido,esc.escuela, sqt.dia,sqt.hora,
    sec.valor,p.nombre as pensum, sqt.edificio, sem.id as semestre, asig.cupos,
    fn_xrxx_horarios_coincide_salon(asig.fk_periodo, asig.fk_dia,
    asig.fk_horario, asig.fk_estructura, asig.pk_asignacion) as coin
    FROM (
      select DISTINCT sal.pk_estructura,asi.fk_dia, asi.fk_horario,
      sal.nombre as salones, dias.dia,
                        TO_CHAR(h.horainicio, 'hh:mi') || ' / ' || TO_CHAR(h.horafin, 'hh:mi') as hora,
      edf.nombre as edificio
      from tbl_asignaciones asi
      join tbl_estructuras sal on asi.fk_estructura = sal.pk_estructura
      join tbl_estructuras edf on edf.pk_estructura = sal.fk_estructura
      join tbl_estructuras sed on sed.pk_estructura = edf.fk_estructura
      join vw_dias dias on dias.pk_atributo = asi.fk_dia
        join tbl_horarios h on h.pk_horario = asi.fk_horario
        join (select a.pk_asignatura, vma.materia
              from tbl_asignaturas a
              join vw_materias vma on vma.pk_atributo = a.fk_materia)as subj
              on subj.pk_asignatura = asi.fk_asignatura

      where sed.pk_estructura={$sede}
      and asi.fk_periodo={$periodo}
        group by sal.pk_estructura, asi.fk_dia,asi.fk_horario, salones,
        dias.dia, h.horainicio, h.horafin,edf.nombre
        having  count(subj.materia)>1) as sqt
      JOIN tbl_asignaciones asig ON asig.fk_estructura = sqt.pk_estructura
      AND asig.fk_horario = sqt.fk_horario
      AND asig.fk_dia = sqt.fk_dia

      join tbl_usuariosgrupos usg on usg.pk_usuariogrupo = asig.fk_usuariogrupo
      join tbl_usuarios u on u.pk_usuario = usg.fk_usuario
      join tbl_asignaturas a on a.pk_asignatura = asig.fk_asignatura
      join tbl_pensums p on p.pk_pensum = a.fk_pensum
      join (SELECT asig_sub.fk_dia, asig_sub.fk_horario,usug_sub.fk_usuario, sal.pk_estructura
        from tbl_asignaciones asig_sub
        join tbl_estructuras sal on asig_sub.fk_estructura = sal.pk_estructura
        join tbl_estructuras edf on edf.pk_estructura = sal.fk_estructura
        join tbl_estructuras sed on sed.pk_estructura = edf.fk_estructura
        JOIN tbl_asignaturas asg_sub ON asg_sub.pk_asignatura = asig_sub.fk_asignatura
        JOIN tbl_pensums pen_sub ON pen_sub.pk_pensum = asg_sub.fk_pensum
        JOIN tbl_usuariosgrupos usug_sub ON usug_sub.pk_usuariogrupo=asig_sub.fk_usuariogrupo
        WHERE asig_sub.fk_periodo = {$periodo}
        AND pen_sub.fk_escuela IN({$escuela})) as co
        on co.fk_dia=asig.fk_dia
        AND co.fk_horario=asig.fk_horario
        AND co.pk_estructura=asig.fk_estructura
      join vw_materias vma on vma.pk_atributo = a.fk_materia
      join vw_escuelas esc on esc.pk_atributo = p.fk_escuela
      join vw_dias dias on dias.pk_atributo = asig.fk_dia
      join vw_secciones sec on sec.pk_atributo = asig.fk_seccion
      join vw_semestres sem on sem.pk_atributo = a.fk_semestre
      where asig.fk_periodo={$periodo}

      )as sqt2
    where sqt2.coin>=1
    and sqt2.materia NOT IN ('SERVICIO COMUNITARIO','TALLER DE SERVICIO COMUNITARIO',
                            'PASANTIAS SOCIAL I','PASANTIA SOCIAL II', 'P.I.R.A.', 'PASANTIA SOCIAL I',
                            'SERVICIO COMUNITARIO I','SERVICIO COMUNITARIO II','PASANTIA PROFESIONAL I y II',
                            'PASANTIA PROFESIONAL I','PRÁCTICA PROFESIONAL','PASANTIA SOCIAL I y II')
                             order by sqt2.dia, sqt2.hora, profesor
                             limit {$itemPerPage} offset {$pageNumber}";


            $results = $this->_db->query($SQL);
            $results = (array) $results->fetchAll();
            return $results;


  }
	public function get_clases_asignacion_feriado($data) {
		$sql = "
--la suma de las clases por dia menos la suma de feriados por dia de clase
SELECT sum(clases) FROM (
SELECT sum(clases) - sum((SELECT count(DISTINCT descripcion)
			FROM (SELECT f.descripcion,EXTRACT(dow from f.fechainicio) as diainicio,EXTRACT(dow from f.fechafin) as diafin
			-- buscamos la cantidad de dias que coinciden con periodos feriados
			from vw_feriados f
			join tbl_periodos p ON f.fechainicio >= p.fechainicio AND f.fechainicio <= p.fechafin
			where p.pk_periodo = {$data['periodo']}

		) as sql WHERE sqt.fk_dia >= sql.diainicio AND sqt.fk_dia <= sql.diafin))*(
		SELECT count(fk_horario) FROM (
		SELECT fk_horario,fk_dia
		from tbl_asignaciones a
			JOIN tbl_estructuras salon ON salon.pk_estructura = a.fk_estructura
			join tbl_estructuras edif ON edif.pk_estructura = salon.fk_estructura
			join tbl_estructuras sede on sede.pk_estructura = edif.fk_estructura
			join tbl_asignaturas asi on asi.pk_asignatura = a.fk_asignatura
			join tbl_pensums p ON p.pk_pensum = asi.fk_pensum
			join tbl_periodos pe on pe.pk_periodo = a.fk_periodo
				where a.fk_periodo = {$data['periodo']}
				and sede.pk_estructura = {$data['sede']}
				AND p.fk_escuela = {$data['escuela']}
				and p.pk_pensum = {$data['pensum']}
				AND a.fk_semestre = {$data['semestre']}
				AND asi.fk_materia = {$data['materia']}
				and a.fk_seccion = {$data['seccion']}
			 AND a.fk_dia = sqt.fk_dia
		)as sqt1 ) AS clases, sqt.fk_dia

		from (
			SELECT count(a.pk_asignacion) *
(SELECT sum(b)

			 FROM fn_xrxx_obtener_semanas(pe.inicioclases,pe.fechafin) as (a varchar,b integer) )
			--cantidad de clases por dia de semana
			as clases , a.fk_dia
			from tbl_asignaciones a
			JOIN tbl_estructuras salon ON salon.pk_estructura = a.fk_estructura
			join tbl_estructuras edif ON edif.pk_estructura = salon.fk_estructura
			join tbl_estructuras sede on sede.pk_estructura = edif.fk_estructura
			join tbl_asignaturas asi on asi.pk_asignatura = a.fk_asignatura
			join tbl_pensums p ON p.pk_pensum = asi.fk_pensum
			join tbl_periodos pe on pe.pk_periodo = a.fk_periodo
				where a.fk_periodo = {$data['periodo']}
				and sede.pk_estructura = {$data['sede']}
				AND p.fk_escuela = {$data['escuela']}
				and p.pk_pensum = {$data['pensum']}
				AND a.fk_semestre = {$data['semestre']}
				AND asi.fk_materia = {$data['materia']}
				and a.fk_seccion = {$data['seccion']}
			GROUP BY pe.pk_periodo,fk_dia,pe.inicioclases,pe.fechafin  ) as sqt
			GROUP BY fk_dia) as clasesdia
		";
//        print_r($sql);die;
        return $this->_db->fetchOne($sql);
	}

  public function getInfoInsistencias($materia, $periodo, $sede, $escuela, $pensum){

    $SQL = $this->_db->select()
            ->from(array('a' => $this->_name), array("a.fk_periodo as periodo","sede.pk_estructura as sede","p.fk_escuela as escuela","p.pk_pensum as pensum","a.fk_semestre as semestre","asi.fk_materia as materia","a.fk_seccion as seccion"))
            ->join(array('salon' => 'tbl_estructuras'), 'a.fk_estructura = salon.pk_estructura', array())
            ->join(array('edif' => 'tbl_estructuras'), 'salon.fk_estructura = edif.pk_estructura', array())
            ->join(array('sede' => 'tbl_estructuras'), 'edif.fk_estructura = sede.pk_estructura', array())
            ->join(array('asi' => 'tbl_asignaturas'), 'a.fk_asignatura = asi.pk_asignatura', array())
            ->join(array('p' => 'tbl_pensums'), 'asi.fk_pensum = p.pk_pensum', array())
            ->join(array('pe' => 'tbl_periodos'), 'a.fk_periodo = pe.pk_periodo', array())
            ->where('asi.fk_materia = ?', $materia)
            ->where('a.fk_periodo = ?', $periodo)
            ->where('sede.pk_estructura = ?', $sede)
            ->where('p.fk_escuela = ?', $escuela)
            ->where('p.pk_pensum = ?', $pensum);

      $results = $this->_db->query($SQL);
      $results = $results->fetch();
      return $results;
  }

}
