<?php
class Models_DbTable_Recordsacademicos extends Zend_Db_Table {
    protected function _setupTableName() {
        $this->_schema   = 'produccion';
        $this->_name     = 'tbl_recordsacademicos';
        $this->_primary  = 'pk_recordacademico';
        $this->_sequence = false;
    }

    private $searchParams = array('u.pk_usuario', 'u.nombre', 'u.apellido', 'ag.codigopropietario', 'm.materia', 'calificacion::text', 'me.valor');

    public function init() {
        $this->SwapBytes_Array         = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    /*
     * @todo borrar getAlumno remplazar por getCompleto
    */
    public function getAlumno($Ci, $estado=862) {
        if(empty($Ci)) return;

        $SQL = "SELECT * from fn_xrxx_recordacademico_completo({$Ci}) AS (periodo int,
            codigo1 character varying,
            codigo2 character varying,
            materia character varying,
            nota character varying,
            notatxt character varying,
            uc smallint,
            uccomputadas smallint,
            ucaprobadas smallint,
            promedio decimal,
            aprobada boolean,
            observacion character varying) ORDER BY periodo, codigo1;";

$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

public function getInfoPasantia($periodo,$ci){

    $SQL = "SELECT case when calificacion >= 10 AND fk_atributo = 862 THEN 1
    WHEN fk_atributo = 12133 THEN 2
    WHEN fk_atributo = 12134 THEN 3 ELSE 4 END  as estado
    FROM(
        select coalesce(calificacion,0) as calificacion, ra.fk_atributo
        from tbl_recordsacademicos ra
        JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
        JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
        WHERE i.fk_periodo <= {$periodo}
        and ug.fk_usuario = {$ci}
        and ag.fk_materia IN (718, 719, 913)
        ) as sqt;";

$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;

}

public function getPeriodoEscuelaPensum($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT i.fk_atributo as escuela, i.fk_periodo as periodo, pe.codigopropietario as pensum
    FROM tbl_pensums		pe
    JOIN tbl_asignaturas		asi	ON	asi.fk_pensum		=	pe.pk_pensum
    JOIN tbl_recordsacademicos	ra	ON	ra.fk_asignatura	=	asi.pk_asignatura
    JOIN tbl_inscripciONes		i	ON	i.pk_inscripciON	=	ra.fk_inscripciON
    JOIN tbl_usuariosgrupos		ug	ON	ug.pk_usuariogrupo	=	i.fk_usuariogrupo
    where ug.fk_usuario = {$Ci}
    and pe.codigopropietario not in (18)
    ORDER BY i.pk_inscripciON DESC LIMIT 1";

    $results = $this->_db->query($SQL);
    $results = (array)$results->fetchAll();

    return $results;
}

public function getIAA($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_iia({$Ci});";

    return $this->_db->fetchOne($SQL);
}

public function getIAAEscuela($Ci, $escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_iia_escuela({$Ci},{$escuela});";

    return $this->_db->fetchOne($SQL);
}

public function getIAAEscuelaPensum($Ci, $escuela, $pensum) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_iia_escuela_pensum({$Ci},{$escuela}, {$pensum});";

    return $this->_db->fetchOne($SQL);
}

public function getIAAEscuelaPensumArticulado($Ci, $escuela,$periodo, $pensum) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_iia_escuela_periodo_articulado({$Ci},{$escuela}, {$periodo}, {$pensum}) as fn_xrxx_estudiante_iia;";

    return $this->_db->fetchOne($SQL);
}

public function getUCA($Ci, $Escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_calcular_uca_te({$Ci}, {$Escuela})";

    return $this->_db->fetchOne($SQL);
}

public function getUCAC($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_calcular_ucac({$Ci})";

    return $this->_db->fetchOne($SQL);
}

public function getUCACEscuela($Ci, $escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_calcular_ucac_escuela({$Ci}, {$escuela})";

    return $this->_db->fetchOne($SQL);
}

public function getUCACEscuelaPensum($Ci, $escuela, $pensum) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_calcular_ucac_escuela_pensum({$Ci}, {$escuela}, {$pensum})";

    return $this->_db->fetchOne($SQL);
}

public function getUCACEscuelaEquiv($Ci, $escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT COALESCE(SUM(A.UnidadCredito),0)
    FROM tbl_recordsacademicos RA
    INNER JOIN tbl_asignaturas A ON RA.FK_Asignatura = A.PK_Asignatura
    INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
    INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    INNER JOIN tbl_pensums p ON p.pk_pensum = A.fk_pensum
    WHERE ug.FK_Usuario = $Ci AND
    i.fk_atributo = $escuela
    AND RA.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos);";

    return $this->_db->fetchOne($SQL);
}

public function getUCACEscuelaEquivPensum($Ci, $escuela, $pensum) {
    if(empty($Ci)) return;

    $SQL = "SELECT COALESCE(SUM(A.UnidadCredito),0)
    FROM tbl_recordsacademicos RA
    INNER JOIN tbl_asignaturas A ON RA.FK_Asignatura = A.PK_Asignatura
    INNER JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
    INNER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    INNER JOIN tbl_pensums p ON p.pk_pensum = A.fk_pensum
    WHERE ug.FK_Usuario = $Ci AND
    p.codigopropietario = $pensum AND
    i.fk_atributo = $escuela
    AND RA.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos);";

    return $this->_db->fetchOne($SQL);
}

public function UCAtotalEscuelaPensum($escuela, $pensum) {
    if(empty($escuela)) return;
    if(empty($pensum)) return;

    $SQL = "SELECT sum(sqt.unidadcredito) AS total
    FROM
    (
        SELECT DISTINCT asi.pk_asignatura,asi.unidadcredito, vma.materia
        FROM vw_materias    vma
        JOIN tbl_asignaturas    asi ON  asi.fk_materia  =   vma.pk_atributo
        JOIN tbl_pensums    pe  ON  pe.pk_pensum    =   asi.fk_pensum
        WHERE pe.fk_escuela = {$escuela}
        AND pe.codigopropietario = {$pensum}
        AND asi.pk_asignatura NOT IN (11823) --Internet Computacion (1992)
     ) AS sqt;";
return $this->_db->fetchOne($SQL);
}

public function getEscuela($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT e.escuela
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}
    GROUP BY e.escuela
    ORDER BY COUNT(i.pk_inscripcion) DESC
    ";

    return $this->_db->fetchOne($SQL);
}

public function getIDEscuela($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT i.fk_atributo
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}
    LIMIT 1";

    return $this->_db->fetchOne($SQL);
}


public function getIDEscuelaUltima($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT e.pk_atributo
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}
    GROUP BY i.fk_periodo, e.pk_atributo
    ORDER BY i.fk_periodo DESC, COUNT(i.pk_inscripcion) DESC
    LIMIT 1";

    return $this->_db->fetchOne($SQL);
}

public function getNombreEscuela($Ci, $Escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT e.escuela
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}
    AND i.fk_atributo = {$Escuela}
    GROUP BY e.escuela
    ORDER BY COUNT(i.pk_inscripcion) DESC
    LIMIT 1";

    return $this->_db->fetchOne($SQL);
}

public function getCantidadEscuela($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT COUNT(DISTINCT e.escuela)
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}
    GROUP BY ug.fk_usuario";

    return $this->_db->fetchOne($SQL);
}

public function getEscuelasEstudiante($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT DISTINCT e.escuela, i.fk_atributo
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
    WHERE ug.fk_usuario = {$Ci}";
    $results = $this->_db->query($SQL);
    $results = (array)$results->fetchAll();

    return $results;
}

    /*
     * Adolfo estuvo de aqui
     */
    public function getUltimaEscuelaEstudiante($Ci){
     if(empty($Ci)) return;

     $SQL = "SELECT DISTINCT e.escuela, i.fk_atributo
     FROM tbl_recordsacademicos ra
     JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
     JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
     JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
     WHERE ug.fk_usuario = {$Ci}
     AND i.fk_periodo  = (SELECT fk_periodo
            FROM tbl_inscripciones 
            WHERE fk_usuariogrupo = ug.pk_usuariogrupo ORDER BY fk_periodo DESC LIMIT 1) ";
         /*Colocar apenas tengas tu local con el último
          *período académico
          * IN(SELECT pk_periodo
			FROM tbl_periodos
			WHERE current_date BETWEEN  fechainicio AND fechafin)
          */
$results = $this->_db->query($SQL);
$results = (array)$results->fetchAll();

return $results;

}
    /*
     * hasta aqui
     */
    public function getListaVerde($Values) {
        if(!is_string($Values)) return;

        // Lista de parametros utilizada para traducir un valor de una determinada
        // condicion a la columna que le corresponde.
        $Params = array('periodo'  => 'i.fk_periodo',
            'sede'     => 'e3.pk_estructura',
            'escuela'  => 'p.fk_escuela',
            'semestre' => 'ac.fk_semestre',
            'materia'  => 'ag.fk_materia',
            'seccion'  => 'ac.fk_seccion',
            'usuario'  => 'ue.pk_usuario');

        $Params2 = array('periodo'  => 'i.fk_periodo',
            'sede'     => 'e3.pk_estructura',
            'escuela'  => 'p1.fk_escuela',
            'semestre' => 'ac1.fk_semestre',
            'materia'  => 'ag1.fk_materia',
            'seccion'  => 'ac1.fk_seccion',
            'usuario'  => 'ue.pk_usuario');

        // Remplazamos los valores recibidos como parametros por el arreglo definido
        // anteriormente.\
        $this->logger = Zend_Registry::get('logger');
        $Where = strtr($Values, $Params);
        $Where2 = strtr($Values, $Params2);




        $SQL1 = "SELECT DISTINCT upper(e.escuela) AS \"Escuela\",
        pe.pk_periodo AS \"Periodo\",
        upper(t.valor) AS \"Turno\",
        TO_CHAR(pe.fechainicio, 'MM/YYYY') AS fechainicio,
        TO_CHAR(pe.fechafin   , 'MM/YYYY') AS fechafin,
        sm.id AS \"Semestre\",
        ag.codigopropietario AS \"AsignaturaCodigo\",
        upper(m.materia) AS \"AsignaturaNombre\",
        s.valor AS \"Seccion\",
        foo.ci AS \"Docente C.I.\",
        foo.docente AS \"Docente\",
        ue.pk_usuario AS \"Estudiante C.I.\",
        upper(ue.apellido) AS \"Apellido\",
        upper(ue.nombre) AS \"Nombre\"
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
        JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
        JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
        JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
        JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
        JOIN tbl_usuariosgrupos   uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
        JOIN tbl_usuarios          ue ON ue.pk_usuario       = uge.fk_usuario
        JOIN tbl_usuariosgrupos   ugd ON ugd.pk_usuariogrupo = ac.fk_usuariogrupo
        JOIN tbl_usuarios          ud ON ud.pk_usuario       = ugd.fk_usuario
        JOIN tbl_pensums            p ON p.pk_pensum         = ag.fk_pensum
        JOIN tbl_periodos          pe ON pe.pk_periodo       = ac.fk_periodo
        JOIN vw_escuelas            e ON e.pk_atributo       = p.fk_escuela
        JOIN vw_secciones           s ON s.pk_atributo       = ac.fk_seccion
        JOIN vw_semestres          sm ON sm.pk_atributo      = ac.fk_semestre
        JOIN vw_turnos              t ON t.pk_atributo       = ac.fk_turno
        JOIN vw_materias            m ON m.pk_atributo       = ag.fk_materia
        JOIN (
            SELECT ud.pk_usuario as ci, apellido || ', ' || nombre as docente
            FROM tbl_asignaciones ac
            JOIN tbl_usuariosgrupos   ugd ON ugd.pk_usuariogrupo = ac.fk_usuariogrupo
            JOIN tbl_usuarios          ud ON ud.pk_usuario       = ugd.fk_usuario
            WHERE ac.pk_asignacion = (
                SELECT pk_asignacion
                FROM (
                  SELECT DISTINCT pk_asignacion, COUNT(DISTINCT pk_recordacademico)
                  FROM tbl_asignaciones ac
                  JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = ac.pk_asignacion
                  JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
                  JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
                  JOIN tbl_pensums            p ON p.pk_pensum         = ag.fk_pensum
                  JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
                  JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
                  JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
                  JOIN tbl_usuariosgrupos   uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
                  JOIN tbl_usuarios          ue ON ue.pk_usuario       = uge.fk_usuario
                  WHERE {$Where}
                  GROUP BY pk_asignacion
                  ORDER BY COUNT(DISTINCT pk_recordacademico) DESC
                  LIMIT 1) as sqt
)

) as foo ON true
WHERE {$Where}
ORDER BY \"Escuela\", \"Periodo\", \"Semestre\", \"AsignaturaCodigo\", \"Seccion\", \"Docente\", \"Apellido\", \"Nombre\"";

$SQL = "SELECT DISTINCT upper(e.escuela) AS \"Escuela\",
pe.pk_periodo AS \"Periodo\",
upper(t.valor) AS \"Turno\",
TO_CHAR(pe.fechainicio, 'MM/YYYY') AS fechainicio,
TO_CHAR(pe.fechafin , 'MM/YYYY') AS fechafin,
sm.id AS \"Semestre\",
ag.codigopropietario AS \"AsignaturaCodigo\",
upper(m.materia) AS \"AsignaturaNombre\",
s.valor AS \"Seccion\",
(SELECT pk_usuario
 FROM (SELECT ud.pk_usuario
   FROM tbl_asignaciones ac1
   JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ac1.fk_asignatura
   JOIN tbl_pensums p1 ON p1.pk_pensum = ag1.fk_pensum
   JOIN tbl_estructuras es1 ON es1.pk_estructura = ac1.fk_estructura
   JOIN tbl_estructuras es2 ON es2.pk_estructura = es1.fk_estructura
   JOIN tbl_usuariosgrupos ugd ON ugd.pk_usuariogrupo = ac1.fk_usuariogrupo
   JOIN tbl_usuarios ud ON ud.pk_usuario = ugd.fk_usuario
   JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = ac1.pk_asignacion
   JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
   JOIN tbl_estructuras e1 ON e1.pk_estructura = ac1.fk_estructura
   JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
   JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
   where {$Where2}
   AND ac1.pk_asignacion = (SELECT sqt.pk_asignacion
     FROM ( SELECT aon.pk_asignacion,
         COUNT(DISTINCT ra1.pk_recordacademico)
         FROM tbl_asignaciones aon
         JOIN tbl_recordsacademicos ra1 ON ra1.fk_asignacion = aon.pk_asignacion
         JOIN tbl_asignaturas ag2 ON ag2.pk_asignatura = aon.fk_asignatura
         JOIN tbl_pensums pe1 ON pe1.pk_pensum = ag2.fk_pensum
         JOIN tbl_estructuras es ON es.pk_estructura = aon.fk_estructura
         JOIN tbl_estructuras es1 ON es1.pk_estructura = es.fk_estructura
         JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = aon.fk_usuariogrupo
         WHERE pe1.fk_escuela = p1.fk_escuela
         AND aon.fk_periodo = i.fk_periodo
         AND aon.fk_estructura = ac1.fk_estructura
         AND ag2.pk_asignatura = ag1.pk_asignatura
         AND aon.fk_seccion = ac1.fk_seccion
         AND aon.fk_semestre = ac1.fk_semestre
         AND ug2.fk_usuario not in (0)
         GROUP by aon.pk_asignacion
         ORDER BY 2 DESC LIMIT 1) as sqt )
AND ag1.pk_asignatura = ag.pk_asignatura
AND ac1.fk_seccion = s.pk_atributo
limit 1) as foo
) AS \"Docente C.I.\",
(SELECT docente
 FROM (SELECT apellido || ', ' || ud.nombre as docente
   FROM tbl_asignaciones ac1
   JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ac1.fk_asignatura
   JOIN tbl_pensums p1 ON p1.pk_pensum = ag1.fk_pensum
   JOIN tbl_estructuras es1 ON es1.pk_estructura = ac1.fk_estructura
   JOIN tbl_estructuras es2 ON es2.pk_estructura = es1.fk_estructura
   JOIN tbl_usuariosgrupos ugd ON ugd.pk_usuariogrupo = ac1.fk_usuariogrupo
   JOIN tbl_usuarios ud ON ud.pk_usuario = ugd.fk_usuario
   JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = ac1.pk_asignacion
   JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
   JOIN tbl_estructuras e1 ON e1.pk_estructura = ac1.fk_estructura
   JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
   JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
   where {$Where2}
   AND ac1.pk_asignacion = (SELECT sqt.pk_asignacion
     FROM ( SELECT aon.pk_asignacion,
         COUNT(DISTINCT ra1.pk_recordacademico)
         FROM tbl_asignaciones aon
         JOIN tbl_recordsacademicos ra1 ON ra1.fk_asignacion = aon.pk_asignacion
         JOIN tbl_asignaturas ag2 ON ag2.pk_asignatura = aon.fk_asignatura
         JOIN tbl_pensums pe1 ON pe1.pk_pensum = ag2.fk_pensum
         JOIN tbl_estructuras es ON es.pk_estructura = aon.fk_estructura
         JOIN tbl_estructuras es1 ON es1.pk_estructura = es.fk_estructura
         JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = aon.fk_usuariogrupo
         WHERE pe1.fk_escuela = p1.fk_escuela
         AND aon.fk_periodo = i.fk_periodo
         AND aon.fk_estructura = ac1.fk_estructura
         AND ag2.pk_asignatura = ag1.pk_asignatura
         AND aon.fk_seccion = ac1.fk_seccion
         AND aon.fk_semestre = ac1.fk_semestre
         AND ug2.fk_usuario not in (0)
         GROUP by aon.pk_asignacion
         ORDER BY 2 DESC LIMIT 1) as sqt )
AND ag1.pk_asignatura = ag.pk_asignatura
AND ac1.fk_seccion = s.pk_atributo
limit 1) as foo
) as \"Docente\",
ue.pk_usuario AS \"Estudiante C.I.\",
upper(ue.apellido) AS \"Apellido\",
upper(ue.nombre) AS \"Nombre\"
FROM tbl_recordsacademicos ra
JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
JOIN tbl_asignaciones ac ON ac.pk_asignacion = ra.fk_asignacion
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
JOIN tbl_estructuras e1 ON e1.pk_estructura = ac.fk_estructura
JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
JOIN tbl_usuariosgrupos uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
JOIN tbl_usuarios ue ON ue.pk_usuario = uge.fk_usuario
JOIN tbl_usuariosgrupos ugd ON ugd.pk_usuariogrupo = ac.fk_usuariogrupo
JOIN tbl_usuarios ud ON ud.pk_usuario = ugd.fk_usuario
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
JOIN tbl_periodos pe ON pe.pk_periodo = ac.fk_periodo
JOIN vw_escuelas e ON e.pk_atributo = p.fk_escuela
JOIN vw_secciones s ON s.pk_atributo = ac.fk_seccion
JOIN vw_semestres sm ON sm.pk_atributo = ac.fk_semestre
JOIN vw_turnos t ON t.pk_atributo = ac.fk_turno
JOIN vw_materias m ON m.pk_atributo = ag.fk_materia
WHERE {$Where}
ORDER BY \"Escuela\",
\"Periodo\", \"Semestre\",
\"AsignaturaCodigo\", \"Seccion\",
\"Docente\", \"Apellido\", \"Nombre\"";
//$this->logger->log($Where,ZEND_LOG::WARN);
//$this->logger->log($Where2,ZEND_LOG::WARN);
                //echo $SQL;

$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

public function getPensumCodigoPropietario($pensum){
    $SQL = "SELECT codigopropietario FROM tbl_pensums WHERE pk_pensum = {$pensum}";

    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();

    return $results;
}

public function getEstudiantePensums($cedula, $escuela){
    if(!empty($escuela)){
       $SQLescuela = "AND ins.fk_atributo = {$escuela}";
   }else{
       $SQLescuela = "";
   }
   $SQL = "SELECT DISTINCT pk_pensum, pen.codigopropietario, pen.nombre
   FROM tbl_inscripciones ins
   JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
   WHERE ug.fk_usuario = {$cedula} {$SQLescuela}";

   $results = $this->_db->query($SQL);
   $results = $results->fetchAll();

   return $results;
}

public function getEstudiantesEvaluaciones($Data) {
    if(!is_array($Data)) return;
    $SQL = "
        SELECT DISTINCT ac.pk_asignacion,u1.pk_usuario as ci,a2.pk_atributo as estado,u1.nombre,u1.apellido,ra.pk_recordacademico,rae.*
		from tbl_asignaturas ag
		JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
		JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
		JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
		JOIN tbl_atributos a1 ON a1.pk_atributo = re.fk_tipo_evaluacion
		JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
		--LEFT SI LA ASIGNACION NO ES OBLIGATORIA
		LEFT OUTER JOIN tbl_asignaciones ac ON ac.fk_asignatura = ag.pk_asignatura
		LEFT OUTER JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
		LEFT OUTER JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
		LEFT OUTER JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
		LEFT OUTER JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  ac.fk_usuariogrupo
		LEFT OUTER JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
		--NOTAS
		JOIN tbl_recordsacademicos ra ON ra.fk_asignatura = ag.pk_asignatura AND ra.fk_asignacion = ac.pk_asignacion
		JOIN tbl_atributos a2 ON a2.pk_atributo = ra.fk_atributo
		JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion AND i.fk_periodo = ac.fk_periodo
		JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = i.fk_usuariogrupo
		JOIN tbl_usuarios u1 ON u1.pk_usuario = ug1.fk_usuario
		LEFT OUTER JOIN crosstab ('SELECT fk_recordacademico,fk_evaluacion,
			(SELECT array[pk_recordacademico_evaluacion,fk_evaluacion,calificacion])
			from tbl_recordsacademicos_evaluaciones rae ORDER BY 1
			') as rae (fk_recordacademico int,ev1 float[3],ev2 float[3],ev3 float[3],ev4 float[3],ev5 float[3],ev6 float[3],ev7 float[3],ev8 float[3])
			On rae.fk_recordacademico = ra.pk_recordacademico --AND rae.fk_evaluacion = re.pk_regimen_evaluacion
			WHERE ra.fk_atributo IN (862,864,863,1699)
			AND i.fk_periodo    = {$Data['periodo']}
			AND e3.pk_estructura = {$Data['sede']}
			AND  p.fk_escuela    = {$Data['escuela']}
			AND  p.pk_pensum     = {$Data['pensum']}
			AND  {$Data['periodo']} >= rh.fk_periodo_inicio AND ({$Data['periodo']} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
			AND ag.fk_materia = {$Data['materia']}";
	if (isset($Data['semestre'])) $SQL .="AND ac.fk_semestre   = {$Data['semestre']}";
	if (isset($Data['seccion']))  $SQL .="AND ac.fk_seccion   = {$Data['seccion']}";
	$SQL.="ORDER BY apellido,nombre,ci;";
	$results = $this->_db->query($SQL);
    $results = $results->fetchAll();
	return $results;
    }

    public function getEstudiantes($Data,$retirados = false) {
        if(!is_array($Data)) return;

    	$SQL = "SELECT DISTINCT ac.pk_asignacion, ra.pk_recordacademico, u.pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci
    		, u.apellido, u.nombre, UPPER(u.correo) as correo, u.telefono_movil as telefono, a.valor as estadoe ,(CASE WHEN ra.calificacion = 0 THEN null ELSE ra.calificacion END) AS calificacion,
    		ra.fk_atributo as estado
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
        JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
        JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
        JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
        JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
        JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
        JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
        JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
    	JOIN tbl_atributos 	a ON a.pk_atributo = ra.fk_atributo

    	WHERE (ra.fk_atributo    = 864 OR ra.fk_atributo = 862 OR ra.fk_atributo = 1699";

    	if ($retirados) {
    		$SQL  .= " OR ra.fk_atributo = 863";
    	}

    	$SQL .=")
    	AND ac.fk_periodo    = {$Data['periodo']}
    	AND e3.pk_estructura = {$Data['sede']}
    	AND  p.fk_escuela    = {$Data['escuela']}
        AND  p.pk_pensum     = {$Data['pensum']} "; // NUEVO

    	if(!empty($Data['semestre']))
    		$SQL .= "AND ac.fk_semestre   = {$Data['semestre']} ";

    	if(!empty($Data['materia']))
    		$SQL .= "AND ag.fk_materia    = {$Data['materia']} ";

    	if(!empty($Data['seccion']))
    		$SQL .= "AND ac.fk_seccion    = {$Data['seccion']} ";

    	$SQL .= "ORDER BY u.apellido, u.nombre";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getEstudiantesPorMateria($Data) {
        if(!is_array($Data)) return;

        $SQL = "SELECT DISTINCT ac.pk_asignacion, sec.valor as seccion, ra.pk_recordacademico, u.pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, u.apellido, u.nombre,u.correo, (CASE WHEN ra.calificacion = 0 THEN null ELSE ra.calificacion END) AS calificacion, ra.fk_atributo as estado, sem.id as semestre, es.escuela
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON   i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_asignaciones      ac ON  ac.pk_asignacion    = ra.fk_asignacion
        JOIN tbl_asignaturas       ag ON  ag.pk_asignatura    = ra.fk_asignatura
        JOIN tbl_estructuras       e1 ON  e1.pk_estructura    = ac.fk_estructura
        JOIN tbl_estructuras       e2 ON  e2.pk_estructura    = e1.fk_estructura
        JOIN tbl_estructuras       e3 ON  e3.pk_estructura    = e2.fk_estructura
        JOIN tbl_usuariosgrupos    ug ON  ug.pk_usuariogrupo  =  i.fk_usuariogrupo
        JOIN tbl_usuarios           u ON   u.pk_usuario       = ug.fk_usuario
        JOIN tbl_pensums            p ON   p.pk_pensum        = ag.fk_pensum
        JOIN vw_secciones         sec ON sec.pk_atributo      = ac.fk_seccion
        JOIN vw_semestres         sem ON sem.pk_atributo      = ag.fk_semestre
        JOIN vw_escuelas           es ON  es.pk_atributo      = p.fk_escuela
        WHERE --(ra.fk_atributo = 862)
        --AND
        ac.fk_periodo    = {$Data['periodo']}
        AND e3.pk_estructura = {$Data['sede']}
        AND ag.fk_materia    = {$Data['materia']}
        ORDER BY escuela ,semestre, seccion, apellido, nombre";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getEstudiantesPorMateriaCedulas($Data) {
        if(!is_array($Data)) return;

        $SQL = "SELECT DISTINCT u.pk_usuario
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON   i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_asignaciones      ac ON  ac.pk_asignacion    = ra.fk_asignacion
        JOIN tbl_asignaturas       ag ON  ag.pk_asignatura    = ra.fk_asignatura
        JOIN tbl_estructuras       e1 ON  e1.pk_estructura    = ac.fk_estructura
        JOIN tbl_estructuras       e2 ON  e2.pk_estructura    = e1.fk_estructura
        JOIN tbl_estructuras       e3 ON  e3.pk_estructura    = e2.fk_estructura
        JOIN tbl_usuariosgrupos    ug ON  ug.pk_usuariogrupo  =  i.fk_usuariogrupo
        JOIN tbl_usuarios           u ON   u.pk_usuario       = ug.fk_usuario
        JOIN tbl_pensums            p ON   p.pk_pensum        = ag.fk_pensum
        JOIN vw_secciones         sec ON sec.pk_atributo      = ac.fk_seccion
        JOIN vw_semestres         sem ON sem.pk_atributo      = ag.fk_semestre
        JOIN vw_escuelas           es ON  es.pk_atributo      = p.fk_escuela
        WHERE --(ra.fk_atributo = 862)
          --AND
          ac.fk_periodo    = {$Data['periodo']}
          AND e3.pk_estructura = {$Data['sede']}
          AND ag.fk_materia    = {$Data['materia']}
          --AND i.fk_atributo    = {$Data['escuela']}
          ";
          $results = $this->_db->query($SQL);
          $results = $results->fetchAll();

          return $results;
    }

    public function getPensum($pensum){

        $SQL = "SELECT nombre
        FROM tbl_pensums
        WHERE pk_pensum = $pensum;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getEstudiantesCalificacion($Data) {
            if(!is_array($Data)) return;

                $SQL = "SELECT DISTINCT ac.pk_asignacion, ra.pk_recordacademico, u.pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, u.apellido, u.nombre, COALESCE((ra.calificacion::VARCHAR),'N/T') AS calificacion, ra.fk_atributo as estado
                FROM tbl_recordsacademicos ra
                JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
                JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
                JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
                JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
                JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
                JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
                JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
                JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
                WHERE (ra.fk_atributo = 862)
                AND ac.fk_periodo    = {$Data['periodo']}
                AND e3.pk_estructura = {$Data['sede']}
                AND  p.fk_escuela    = {$Data['escuela']}
                AND p.pk_pensum      = {$Data['pensum']}";
                if(!empty($Data['semestre']))
                 $SQL .= "AND ac.fk_semestre   = {$Data['semestre']} ";
             if(!empty($Data['materia']))
                 $SQL .= "AND ag.fk_materia    = {$Data['materia']} ";
             if(!empty($Data['seccion']))
                 $SQL .= "AND ac.fk_seccion    = {$Data['seccion']} ";
             $SQL .= "ORDER BY u.apellido, u.nombre";

             $results = $this->_db->query($SQL);
             $results = $results->fetchAll();

             return $results;
    }

        public function getAsignaturas($pk_recordacademico) {


            $SQL = "select ra.fk_asignatura
            from tbl_recordsacademicos      ra
            where pk_recordacademico = {$pk_recordacademico}";

            return $this->_db->fetchOne($SQL);
        }


        public function getAsignaturasPreladas($asignatura) {

            $SQL = "select distinct asi.pk_asignatura as prelada
            from tbl_prelaciones        pre
            join tbl_asignaturas        asi on  asi.pk_asignatura = pre.fk_asignatura
            join tbl_asignaturas        asi_pre on  asi_pre.pk_asignatura = pre.fk_asignaturaprelada
            join vw_materias        vma on  vma.pk_atributo = asi.fk_materia
            join vw_materias        vma_pre on  vma_pre.pk_atributo = asi_pre.fk_materia
            where pre.fk_asignaturaprelada = {$asignatura};";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getInfoEstudiantePorRecord($recordacademico) {

            $SQL = "select distinct u.pk_usuario as cedula, i.fk_atributo as escuela, i.fk_periodo as periodo, pe.codigopropietario as pensum
            from tbl_recordsacademicos  ra
            join tbl_inscripciones      i   on  i.pk_inscripcion = ra.fk_inscripcion
            join tbl_usuariosgrupos     ug  on  ug.pk_usuariogrupo = i.fk_usuariogrupo
            join tbl_usuarios       u   on  u.pk_usuario = ug.fk_usuario
            join tbl_pensums        pe  on  pe.pk_pensum = i.fk_pensum
            where ra.pk_recordacademico = {$recordacademico};";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getEstadoMateriaEstudiante($cedula,$escuela, $periodo, $pensum, $asignatura) {

            $SQL = "select ra.pk_recordacademico, ra.fk_asignatura, ra.fk_atributo, vma.materia
            from tbl_recordsacademicos  ra
            join tbl_asignaturas        asi on asi.pk_asignatura = ra.fk_asignatura
            join vw_materias            vma on  vma.pk_atributo = asi.fk_materia
            join tbl_inscripciones      i   on  i.pk_inscripcion = ra.fk_inscripcion
            join tbl_usuariosgrupos     ug  on  ug.pk_usuariogrupo = i.fk_usuariogrupo
            join tbl_usuarios       u   on  u.pk_usuario = ug.fk_usuario
            join tbl_pensums        pe  on  pe.pk_pensum = i.fk_pensum
            where u.pk_usuario = {$cedula}
            and i.fk_atributo = {$escuela}
            and i.fk_periodo = {$periodo}
            and pe.codigopropietario = {$pensum}
            and ra.fk_asignatura = {$asignatura};";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getEstadoMateriaEstudianteRetirada($cedula,$escuela, $periodo, $pensum, $asignatura) {

            $SQL = "select ra.pk_recordacademico, ra.fk_asignatura, ra.fk_atributo, vma.materia
            from tbl_recordsacademicos  ra
            join tbl_asignaturas        asi on asi.pk_asignatura = ra.fk_asignatura
            join vw_materias            vma on  vma.pk_atributo = asi.fk_materia
            join tbl_inscripciones      i   on  i.pk_inscripcion = ra.fk_inscripcion
            join tbl_usuariosgrupos     ug  on  ug.pk_usuariogrupo = i.fk_usuariogrupo
            join tbl_usuarios       u   on  u.pk_usuario = ug.fk_usuario
            join tbl_pensums        pe  on  pe.pk_pensum = i.fk_pensum
            where u.pk_usuario = {$cedula}
            and i.fk_atributo = {$escuela}

            and i.fk_periodo = {$periodo}

            and pe.codigopropietario = {$pensum}
            and ra.fk_asignatura = {$asignatura}
            and ra.fk_atributo = 863;";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }


        public function getStatusConsignado($Data) {
            if(!is_array($Data)) return;

            $SQL = "SELECT DISTINCT COUNT(ra.pk_recordacademico) > 0
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
            JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
            JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            WHERE ra.fk_atributo   = 864
            AND ac.fk_periodo    = {$Data['periodo']}
            AND e3.pk_estructura = {$Data['sede']}
            AND  p.fk_escuela    = {$Data['escuela']}
            AND ac.fk_semestre   = {$Data['semestre']}
            AND ag.fk_materia    = {$Data['materia']}
            AND ac.fk_seccion    = {$Data['seccion']}";

            return $this->_db->fetchOne($SQL);
        }

        public function setSearch($searchData) {
            $this->searchData = $searchData;
        }

        public function getSQLCount($Periodo, $Sede, $Escuela,$Pensum) {
            if(!isset($Periodo))  return;
            if(!isset($Sede))     return;
            if(!isset($Escuela))  return;

            $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL = "SELECT COUNT(DISTINCT pk_recordacademico)
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
            JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
            JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
            JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
            WHERE i.fk_periodo    = {$Periodo}
            AND i.fk_estructura = {$Sede}
            AND i.fk_atributo   = {$Escuela}
            AND i.fk_pensum = {$Pensum}
            {$whereSearch}";

            return $this->_db->fetchOne($SQL);
        }

        public function getList_records($Periodo, $Sede, $Escuela,$Pensum,$itemPerPage, $pageNumber) {
            if(!isset($Periodo))  return;
            if(!isset($Sede))     return;
            if(!isset($Escuela))  return;
            $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        // echo $itemPerPage;
          // echo $pageNumber;
        //echo $pageNumber;
        //$pageNumber  = ($pageNumber == 1) ? 2 : $pageNumber;
            $pageNumber  = ($pageNumber - 1) * $itemPerPage;
            $SQL = "SELECT DISTINCT ra.pk_recordacademico
            , u.pk_usuario as ci
            , u.apellido
            , u.nombre
            ,ag.codigopropietario
            ,sm.id as semestre
            , m.materia
            , s.valor as seccion
            ,ag.unidadcredito
            ,ra.calificacion
            ,me.valor as estado
            ,ag.fk_materia
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
            JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
            JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
            JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
            LEFT JOIN tbl_asignaciones  a ON  a.pk_asignacion    = ra.fk_asignacion
	    LEFT JOIN tbl_estructuras est1 ON est1.pk_estructura = a.fk_estructura
	    LEFT JOIN tbl_estructuras est2 ON est2.pk_estructura = est1.fk_estructura AND est2.pk_estructura = {$Sede}
            LEFT JOIN vw_secciones      s ON  s.pk_atributo      =  a.fk_seccion
            LEFT JOIN vw_semestres     sm ON sm.pk_atributo      = ag.fk_semestre
            WHERE i.fk_periodo    = {$Periodo}
            AND i.fk_estructura = {$Sede}
            AND i.fk_atributo   = {$Escuela}
            AND i.fk_pensum = {$Pensum}
            {$whereSearch}
            ORDER BY ci, apellido, nombre, codigopropietario
            LIMIT {$itemPerPage} OFFSET {$pageNumber};";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getList_record_especifico($Periodo, $Sede, $Escuela, $ci, $retiro = false) {
            if(!isset($Periodo))  return;
            if(!isset($Sede))     return;
            if(!isset($Escuela))  return;

            $SQL = "SELECT DISTINCT ra.pk_recordacademico
            , u.pk_usuario as ci
            , u.apellido
            , u.nombre
            ,ag.codigopropietario
            ,sm.id as semestre
            , m.materia
            , s.valor as seccion
            ,ag.unidadcredito
            ,ra.calificacion
            ,me.valor as estado
            ,ag.fk_materia
            ,substring(ag.codigopropietario from 5 for 2) as cod_sem
            ,substring(ag.codigopropietario from 7 for 2) as cod_ord
            ,ag.unidadcredito
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
            JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
            JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
            JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
            LEFT JOIN tbl_asignaciones  a ON  a.pk_asignacion    = ra.fk_asignacion
            LEFT JOIN vw_secciones      s ON  s.pk_atributo      =  a.fk_seccion
            LEFT JOIN vw_semestres     sm ON sm.pk_atributo      = ag.fk_semestre
            WHERE i.fk_periodo    = {$Periodo}
            AND i.fk_estructura = {$Sede}
            AND i.fk_atributo   = {$Escuela}
            AND u.pk_usuario = {$ci}
            AND ra.fk_atributo = 864 ";
            if($retiro){
                // Trabajo de Grado 2
                $SQL .= " AND ag.fk_materia NOT IN (9724) ";
            }
            $SQL .= " ORDER BY ci, apellido, nombre, codigopropietario;";
            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();

            return $results;
        }

        public function getDetalleRecord($pk_recordacademico){
            if(!isset($pk_recordacademico)) return;

            $SQL = "SELECT me.valor as estadovalor, sec.valor, ag.unidadcredito,ag.codigopropietario,i.fk_atributo, ra.fk_atributo as estado,i.fk_periodo as periodo, ag.fk_materia
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
            JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
            JOIN tbl_asignaciones     aon ON aon.pk_asignacion   = ra.fk_asignacion
            JOIN vw_secciones         sec ON sec.pk_atributo     = aon.fk_seccion

            WHERE ra.pk_recordacademico = {$pk_recordacademico};";

            $results = (array)$this->_db->fetchRow($SQL);

            return $results;


        }


        public function countInscritas($ci,$periodo){

            $SQL = "select COUNT(DISTINCT pk_recordacademico) as materias
            from tbl_recordsacademicos ra
            JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
            where fk_usuario = {$ci}
            AND ra.fk_atributo = 864
            AND i.fk_periodo = {$periodo}
            ;";

            $results = (array)$this->_db->fetchRow($SQL);

            return $results;


        }

        public function getMateriaCursada($ci,$materia){

            $SQL = "select DISTINCT pk_recordacademico
            from tbl_recordsacademicos ra
            JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
            JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.FK_Asignatura
            where fk_usuario = {$ci}
            AND ((ra.fk_atributo = 862 AND ra.calificacion>=10) OR ra.fk_atributo IN (864) OR ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
            AND asi.pk_asignatura = {$materia}

            ;";

            $results = (array)$this->_db->fetchRow($SQL);

            return $results;


        }

        public function getList_record($pk_recordacademico) {
            if(!isset($pk_recordacademico)) return;

            $SQL = "SELECT u.pk_usuario, u.nombre, u.apellido, ag.codigopropietario, m.materia, calificacion, ra.fk_atributo as estado, me.valor as estadovalor, sec.valor as seccion
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_asignaciones     asi ON asi.pk_asignacion   = ra.fk_asignacion
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
            JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
            JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
            JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
            JOIN vw_secciones     sec ON sec.pk_atributo     = asi.fk_seccion

            WHERE ra.pk_recordacademico = {$pk_recordacademico};";

            $results = (array)$this->_db->fetchRow($SQL);

            return $results;
        }


        public function getList_record2($pk_recordacademico) {
            if(!isset($pk_recordacademico)) return;

            $SQL = "SELECT u.pk_usuario, u.nombre, u.apellido, ag.codigopropietario, m.materia, calificacion, ra.fk_atributo as estado, me.valor as estadovalor
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
                --JOIN tbl_asignaciones     asi ON asi.pk_asignacion   = ra.fk_asignacion
                --JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
                JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
                JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
                JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
                --JOIN vw_secciones     sec ON sec.pk_atributo     = asi.fk_seccion
                WHERE ra.pk_recordacademico = {$pk_recordacademico};";

                $results = (array)$this->_db->fetchRow($SQL);

                return $results;
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

    public function getCount($fk_usuario) {
        $SQL = "SELECT COUNT({$this->_primary}) AS count
        FROM {$this->_name} ra
        JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
        WHERE ug.fk_usuario = " . (int)$fk_usuario;

//        $results = $this->_db->query($SQL);
//        $results = $results->fetchAll();

        return $this->_db->fetchOne($SQL);
    }

    public function getCountByAsignacion($fk_asignacion) {
      $SQL = "SELECT COUNT(ra.fk_asignacion)
      FROM tbl_recordsacademicos ra
      JOIN tbl_inscripciones      i ON i.pk_inscripcion = ra.fk_inscripcion
      WHERE ra.fk_asignacion = {$fk_asignacion}";

      return $this->_db->fetchOne($SQL);
  }

  public function getListaRoja($Asignacion, $estados) {

    if(!isset($Asignacion))     return;
	$fk_atributos = implode(',', $estados);

    $SQL = "SELECT DISTINCT upper(e.escuela) AS \"Escuela\",
    pe.pk_periodo AS \"Periodo\",
    upper(t.valor) AS \"Turno\",
    t.id AS \"NTurno\",
    TO_CHAR(pe.fechainicio, 'MM/YYYY') AS fechainicio,
    TO_CHAR(pe.fechafin   , 'MM/YYYY') AS fechafin,
    sm.id AS \"Semestre\",
    ag.codigopropietario AS \"AsignaturaCodigo\",
    upper(m.materia) AS \"AsignaturaNombre\",
    s.valor AS \"Seccion\",
    ud.pk_usuario AS \"Docente C.I.\",
    upper(ud.apellido || ', ' || ud.nombre) AS \"Docente\",
    ue.pk_usuario AS \"Estudiante C.I.\",
    upper(ue.apellido) AS \"Apellido\",
    upper(ue.nombre) AS \"Nombre\",
    ra.calificacion AS \"Calificacion\",
    n.nota AS \"Notatxt\",
    UPPER(e3.nombre) AS \"Sede\"
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
    JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
    JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
    JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
    JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
    JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
    JOIN tbl_usuariosgrupos   uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN tbl_usuarios          ue ON ue.pk_usuario       = uge.fk_usuario
    JOIN tbl_usuariosgrupos   ugd ON ugd.pk_usuariogrupo = ac.fk_usuariogrupo
    JOIN tbl_usuarios          ud ON ud.pk_usuario       = ugd.fk_usuario
    JOIN tbl_pensums            p ON p.pk_pensum         = ag.fk_pensum
    JOIN tbl_periodos          pe ON pe.pk_periodo       = ac.fk_periodo
    JOIN vw_escuelas            e ON e.pk_atributo       = p.fk_escuela
    JOIN vw_secciones           s ON s.pk_atributo       = ac.fk_seccion
    JOIN vw_semestres          sm ON sm.pk_atributo      = ac.fk_semestre
    JOIN vw_turnos              t ON t.pk_atributo       = ac.fk_turno
    JOIN vw_materias            m ON m.pk_atributo       = ag.fk_materia
    JOIN vw_notas		    n ON n.pk_nota	     = ra.calificacion
    WHERE ac.pk_asignacion
    IN (SELECT pk_asignacion FROM tbl_asignaciones asi1
        JOIN vw_estructuras est ON asi1.fk_estructura = est.pk_aula
        WHERE
        fk_asignatura = (SELECT asg.fk_asignatura FROM tbl_asignaciones asg
            where asg.pk_asignacion = {$Asignacion})
AND fk_seccion = (SELECT asg.fk_seccion FROM tbl_asignaciones asg
    where asg.pk_asignacion = {$Asignacion})
AND fk_semestre = (SELECT asg.fk_semestre FROM tbl_asignaciones asg
    where asg.pk_asignacion = {$Asignacion})
AND fk_periodo = (SELECT asg.fk_periodo FROM tbl_asignaciones asg
    where asg.pk_asignacion = {$Asignacion})
AND est.pk_sede = (SELECT est1.pk_sede FROM tbl_asignaciones asg JOIN vw_estructuras est1 ON est1.pk_aula = asg.fk_estructura where asg.pk_asignacion = {$Asignacion})
)
AND ra.fk_atributo  IN ({$fk_atributos})
ORDER BY \"Escuela\", \"Periodo\", \"Semestre\", \"AsignaturaCodigo\", \"Seccion\", \"Docente\", \"Apellido\", \"Nombre\"";

$results = $this->_db->query($SQL);
$results = $results->fetchAll();
return $results;
}

public function updateRow($id, $inscripcion = null, $asignatura = null, $asignacion = null, $estado = null, $calificacion = null,$asignacion2=null) {
    $data = array(
        'fk_inscripcion' => $inscripcion,
        'fk_asignatura'  => $asignatura,
        'fk_asignacion'  => $asignacion,
        'fk_atributo'    => $estado,
        'calificacion'   => $calificacion,
        'fk_asignacion' => $asignacion2

        );

        // La funcion array_filter borra los elementos que tienen como valor 0, null, false, ''.
    $data = array_filter($data);

        // Si existe una calificación con 0, se agrega por estar eliminada por la funcion array_filter.
    if(isset($calificacion) && (int)$calificacion == 0) {
        $data['calificacion'] = 0;
    }

    $where         = $this->_db->quoteInto('pk_recordacademico = ?', (int)$id);
    $rows_affected = $this->update($data, $where);

    return $rows_affected;
}

public function addRow($inscripcion, $asignatura, $asignacion = null, $estado = null, $calificacion = null) {
    $data = array(
        'fk_inscripcion' => $inscripcion,
        'fk_asignatura'  => $asignatura,
        'fk_asignacion'  => $asignacion,
        'fk_atributo'    => $estado,
        'calificacion'   => $calificacion
        );

    $data          = array_filter($data);
    $rows_affected = $this->insert($data);

    return $rows_affected;
}

    /**
     * Permite eliminar un registro dependiendo de las condiciones que son
     * enviadas como parametros.
     *
     * @param int $id Clave primaria del registro.
     * @return int
     */
    public function deleteRow($id) {
        //$rowsAffected = $this->delete( . ' = ' . (int)$id);
        $where        = $this->_db->quoteInto($this->_primary . ' = ?', $id);
        $rowsAffected = $this->delete($where);

        return $rowsAffected;
    }

    public function getPK($inscripcion, $asignatura) {
        if(empty($inscripcion)) return;
        if(empty($asignatura))  return;

        $SQL = "SELECT pk_recordacademico
        FROM tbl_recordsacademicos
        WHERE fk_inscripcion = {$inscripcion}
        AND fk_asignatura  = {$asignatura};";

        return $this->_db->fetchOne($SQL);
    }

    public function getPKbyUserID($usuario, $asignatura) {
        if(empty($usuario))    return;
        if(empty($asignatura)) return;

        $SQL = "SELECT pk_recordacademico
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON  i.pk_inscripcion  = ra.fk_inscripcion
        JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo =  i.fk_usuariogrupo
        WHERE fk_asignatura  = {$asignatura}
        AND fk_usuario     = {$usuario}
        AND ra.calificacion >= 10;";

        return $this->_db->fetchOne($SQL);
    }

    public function getPkByEquivalencias($usuario, $escuela, $asignatura) {
        if(empty($inscripcion)) return;
        if(empty($asignatura))  return;

        $SQL = "SELECT pk_recordacademico
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
        WHERE ra.fk_asignatura = {$asignatura}
        AND ug.fk_usuario    = {$usuario}
        AND  i.fk_atributo   = {$escuela}
        AND ra.fk_atributo IN (SELECT pk_atributo FROM vw_equivalencias);";

        return $this->_db->fetchOne($SQL);
    }

    public function getList_retiros($Periodo, $Sede, $Escuela, $itemPerPage, $pageNumber) {
        if(!isset($Periodo))  return;
        if(!isset($Sede))     return;
        if(!isset($Escuela))  return;

        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        $pageNumber  = ($pageNumber - 1) * $itemPerPage;

        $SQL = "SELECT ra.pk_recordacademico, u.pk_usuario as ci, u.apellido, u.nombre, ag.codigopropietario, m.materia, calificacion, me.valor as estado
        FROM tbl_recordsacademicos ra
        JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
        JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
        JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
        JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
        JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
        JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
        JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
        WHERE i.fk_periodo    = {$Periodo}
        AND i.fk_estructura = {$Sede}
        AND i.fk_atributo   = {$Escuela}
        AND ra.fk_atributo  = 863
        {$whereSearch}
        ORDER BY ci, apellido, nombre, codigopropietario
        LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    // public function getSQLCountretiros($Periodo, $Sede, $Escuela, $Sede) {
    //     if(!isset($Periodo))  return;
    //     if(!isset($Sede))     return;
    //     if(!isset($Escuela))  return;

    //     $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

    //     $SQL = "SELECT COUNT(pk_recordacademico)
    //     FROM tbl_recordsacademicos ra
    //     JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
    //     JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
    //     JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
    //     WHERE i.fk_periodo    = {$Periodo}
    //     AND i.fk_estructura = {$Sede}
    //     AND i.fk_atributo   = {$Escuela}
    //     AND ra.fk_atributo = 863
    //     {$whereSearch}";

    //     return $this->_db->fetchOne($SQL);
    // }

    public function getCompleto($Ci) {
        if(empty($Ci)) return;

        $SQL="SELECT * from fn_xrxx_recordacademico_completo($Ci)
        AS (periodo int,
            codigo1 character varying,
            codigo2 character varying,
            materia character varying,
            nota character varying,
            notatxt character varying,
            uc smallint,
            uccomputadas smallint,
            ucaprobadas smallint,
            promedio decimal,
            aprobada boolean,
            observacion character varying,
            inicio text,
            fin text)
ORDER BY periodo, codigo1;";



$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

public function getCompletoEscuela($Ci, $escuela, $pensum = 7) {
    if(empty($Ci)) return;

    $SQL="SELECT sqt.periodo,
    CASE WHEN sqt.codigo1 = '07050000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07100000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07150000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07200000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07250000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07300000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07050001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07100001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 = '07150001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07200001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 = '07250001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07300001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 NOT IN ('07050000','07100000','07150000','07200000','07250000','07300000'
     '07050001','07100001','07150001','07200001','07250001','07300001'
     )THEN sqt.codigo1
END END END END END END END END END END END END END AS codigo1,
sqt.codigo2, sqt.materia, sqt.nota, sqt.notatxt, sqt.uc, sqt.uccomputadas, sqt.ucaprobadas, sqt.promedio, sqt.p_acumulado,
sqt.aprobada, sqt.observacion, sqt.inicio, sqt.fin
FROM (SELECT * from fn_xrxx_recordacademico_completo_escuela_articulado($Ci,$escuela, $pensum)
    AS (periodo int,
        codigo1 character varying,
        codigo2 character varying,
        materia character varying,
        nota character varying,
        notatxt character varying,
        uc smallint,
        uccomputadas smallint,
        ucaprobadas smallint,
        promedio decimal,
        p_acumulado decimal,
        aprobada boolean,
        observacion character varying,
        inicio text,
        fin text)
ORDER BY periodo, codigo1) as sqt
WHERE( sqt.codigo1 NOT IN (select asi.codigopropietario
   FROM tbl_asignaturas asi
   WHERE asi.unidadcredito = 0)
OR sqt.uccomputadas IS NOT NULL)
ORDER BY 1,2;
";



$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

    //usada en el modulo de recordacademico, certificacioncalificacion
public function getCompletoEscuela2($Ci, $escuela, $pensum = 7) {
    if(empty($Ci)) return;

    $SQL="SELECT sqt.periodo,
    CASE WHEN sqt.codigo1 = '07050000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07100000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07150000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07200000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07250000' THEN '1' ELSE CASE WHEN sqt.codigo1 = '07300000' THEN '1' ELSE
    CASE WHEN sqt.codigo1 = '07050001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07100001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 = '07150001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07200001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 = '07250001' THEN '2' ELSE CASE WHEN sqt.codigo1 = '07300001' THEN '2' ELSE
    CASE WHEN sqt.codigo1 NOT IN ('07050000','07100000','07150000','07200000','07250000','07300000'
     '07050001','07100001','07150001','07200001','07250001','07300001'
     )THEN sqt.codigo1
END END END END END END END END END END END END END AS codigo1,
sqt.codigo2, sqt.materia,(	case
  when sqt.uc = 0 then
  case
  when sqt.nota::integer >= 10 then 'Aprobado'
  when sqt.nota::integer < 10 then 'Reprobado'
  else sqt.nota
   end
else sqt.nota
 end) as nota,
(	case
  when sqt.uc = 0 then
  case
  when sqt.nota::integer >= 10 then ''
  when sqt.nota::integer < 10 then ''
  else sqt.notatxt
   end
else sqt.notatxt
 end) as notatxt,
(	case
  when sqt.uc = 0 then
  case
  when sqt.nota::integer >= 10 then null
  when sqt.nota::integer < 10 then null
  else sqt.uc
   end
else sqt.uc
 end) as uc, sqt.uccomputadas, sqt.ucaprobadas, sqt.promedio, sqt.p_acumulado,
sqt.aprobada, sqt.observacion, sqt.inicio, sqt.fin
FROM (SELECT * from fn_xrxx_recordacademico_completo_escuela_articulado($Ci,$escuela, $pensum)
    AS (periodo int,
        codigo1 character varying,
        codigo2 character varying,
        materia character varying,
        nota character varying,
        notatxt character varying,
        uc smallint,
        uccomputadas smallint,
        ucaprobadas smallint,
        promedio decimal,
        p_acumulado decimal,
        aprobada boolean,
        observacion character varying,
        inicio text,
        fin text)
ORDER BY periodo, codigo1) as sqt
ORDER BY 1,2;";



$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

public function getEquivalencias($Ci, $Estado, $pensum,$escuela = null) {
    if(empty($Ci)) return;

    $SQL="SELECT ug.fk_usuario
    , ass.codigopropietario
    , atri0.valor::varchar as materia
    , ass.unidadcredito::smallint as uc
    , INITCAP(ins.observaciones) as observacion
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones ins ON (ins.pk_inscripcion = ra.fk_inscripcion)
    JOIN tbl_usuariosgrupos ug ON (ins.fk_usuariogrupo = ug.pk_usuariogrupo)
    JOIN tbl_asignaturas ass ON (ra.fk_asignatura = ass.pk_asignatura)
    JOIN tbl_atributos atri0 ON (ass.fk_materia = atri0.pk_atributo)
    JOIN tbl_pensums pen ON (pen.pk_pensum = ass.fk_pensum)
    WHERE       ug.fk_usuario  = $Ci
    AND ra.fk_atributo = $Estado
    AND pen.codigopropietario = $pensum
    ";
    if($escuela != null)
        $SQL .= "AND pen.fk_escuela = $escuela";
    $SQL .= "ORDER BY 2;";
    //var_dump($SQL);die;

    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();

    return $results;
}

public function getUniversidadesEquivalencias($Ci) {
    if(empty($Ci)) return;

    $SQL="SELECT 		uni.nombre as universidad,
    ra.fk_atributo as tipo,
    atri1.valor
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones ins ON (ins.pk_inscripcion = ra.fk_inscripcion)
    JOIN tbl_usuariosgrupos ug ON (ins.fk_usuariogrupo = ug.pk_usuariogrupo)
    JOIN tbl_asignaturas ass ON (ra.fk_asignatura = ass.pk_asignatura)
    JOIN tbl_atributos atri0 ON (ass.fk_materia = atri0.pk_atributo)
    JOIN tbl_atributos atri1 ON (atri1.pk_atributo = ra.fk_atributo)
    JOIN tbl_reconocimientos rec ON (ins.pk_inscripcion = rec.fk_inscripcion)
    JOIN vw_universidades uni ON (uni.pk_universidad = rec.fk_universidad)
    WHERE    ug.fk_usuario  = {$Ci} AND
    ra.fk_atributo IN(1264,1265,1266)
    GROUP BY uni.nombre, ra.fk_atributo, atri1.valor;";

    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();

    return $results;
}

    //DEVUELVE EL INDICE TOTAL ACUMULADO DE UN ALUMNO
public function getIndiceAcumulado($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT fn_xrxx_estudiante_iia($Ci);";
    return $this->_db->fetchOne($SQL);
}

    // Devuelve el indice acumulado hasta un periodo
public function getIndiceAcumuladoPeriodo($Ci, $Escuela, $Periodo) {
    if(empty($Ci)) return;
    if(empty($Escuela)) return;
    if(empty($Periodo)) return;

    $SQL = "SELECT trunc(fn_xrxx_estudiante_iia_escuela_periodo($Ci, $Escuela, $Periodo)::decimal, 2);";
    return $this->_db->fetchOne($SQL);
}

public function getIndiceAcumuladoPeriodoPensum($Ci, $Escuela, $Periodo, $pensum) {
    if(empty($Ci)) return;
    if(empty($Escuela)) return;
    if(empty($Periodo)) return;

    $SQL = "SELECT trunc(fn_xrxx_estudiante_iia_escuela_periodo_articulado($Ci, $Escuela, $Periodo, $pensum)::decimal, 2);";
    return $this->_db->fetchOne($SQL);
}

    //DEVUELVE LA CANTIDAD DE U.C. APROBADAS DEL ESTUDIANTE
public function getTotalUCAprobadas($Ci, $Escuela) {
    if(empty($Ci)) return;

    $SQL = "SELECT * FROM fn_xrxx_estudiante_calcular_uca({$Ci}, {$Escuela});";
    return $this->_db->fetchOne($SQL);
}

public function getPeriodosCursados($Ci) {
    if(empty($Ci)) return;

    $SQL     = "SELECT DISTINCT I.fk_periodo,
    CASE to_char(per.fechainicio, 'TMMONTH')
    WHEN 'MAY' THEN 'MAYO '||to_char(per.fechainicio, 'YYYY')
    ELSE to_char(per.fechainicio, 'TMMONTH YYYY')
    END as \"inicio\",
    CASE to_char(per.fechafin, 'TMMONTH')
    WHEN 'MAY' THEN 'MAYO '||to_char(per.fechainicio, 'YYYY')
    ELSE to_char(per.fechafin, 'TMMONTH YYYY')
    END as \"fin\"
    FROM tbl_inscripciones I
    INNER JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion
    INNER JOIN tbl_usuariosgrupos U ON U.pk_usuariogrupo = I.fk_usuariogrupo
    INNER JOIN tbl_periodos per ON (per.pk_periodo = I.fk_periodo)
    WHERE fk_usuario = $Ci AND
    ra.fk_atributo <> 864 AND ra.fk_atributo <> 904
    ORDER BY fk_periodo;";
    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();
    return $results;
}

public function IsPensumNuevo($ci){
    $SQL = "SELECT COUNT (*)
    FROM tbl_inscripciones ins
    JOIN tbl_usuariosgrupos usug ON usug.pk_usuariogrupo = ins.fk_usuariogrupo
    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
    WHERE usug.fk_usuario = {$ci}
    AND pen.nombre = '2012'";
    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();
    if ($results[0]['count'] > 0 ){
        $result = true;
    }else{
        $result = false;
    }
    return $result;
}

public function getInfoEstudiante($Ci,$escuela) {
    if(empty($Ci)) return;

    $SQL    = "SELECT e.pk_atributo as \"pk_escuela\",
    pen.codigopropietario as \"pensum\",
    CASE pen.codigopropietario
    WHEN '7' then UPPER(sem.valor)
    else (
        case sem.pk_atributo
        when 873 then UPPER('Primer Periodo')
        when 874 then UPPER('Segundo Periodo')
        when 875 then UPPER('Tercer Periodo')
        when 876 then UPPER('Cuarto Periodo')
        when 878 then UPPER('Quinto Periodo')
        when 879 then UPPER('Sexto Periodo')
        when 881 then UPPER('Séptimo Periodo')
        when 882 then UPPER('Octavo Periodo')
        when 883 then UPPER('Noveno Periodo')
        when 884 then UPPER('Décimo Periodo')
        when 9696 then UPPER('Onceavo Periodo')
        when 9697 then UPPER('Doceavo Periodo')
        else 'N/A'
            end
        )
END as \"semestre\",
CASE to_char(per.inicioclases, 'TMMonth')
WHEN 'May' THEN to_char(per.inicioclases, '\"Mayo\" YYYY')
ELSE initcap(to_char(per.inicioclases, 'TMMonth YYYY'))
END as \"fia\",
CASE to_char(per.fechafin, 'TMMonth')
WHEN 'May' THEN to_char(per.fechafin, '\"Mayo\" YYYY')
ELSE initcap(to_char(per.fechafin, 'TMMonth YYYY'))
END as \"ffa\",
e.escuela as \"escuela\",
i.fk_estructura,
CASE usu.nacionalidad WHEN FALSE THEN 'V - '|| to_char(usu.pk_usuario, '99\".\"999\".\"999')
   ELSE 'E - ' ||  to_char(usu.pk_usuario, '99\".\"999\".\"999') END as \"Ci\"
    ,
TRIM(usu.nombre) as \"nombre\",
TRIM(usu.apellido) as \"apellido\",
atri.valor as \"facultad\",
to_char(current_date, 'DD')::INT as \"dia\",
CASE to_char(current_date, 'TMMONTH')
WHEN 'MAY' THEN 'Mayo'
ELSE initcap(to_char(current_date, 'TMMONTH'))
END as \"mes\",
to_char(current_date, 'YYYY') as \"año\",
CASE to_char(current_date, 'DD')
WHEN '01' THEN 'primer'
WHEN '02' THEN 'dos'
WHEN '03' THEN 'tres'
WHEN '04' THEN 'cuatro'
WHEN '05' THEN 'cinco'
WHEN '06' THEN 'seis'
WHEN '07' THEN 'siete'
WHEN '08' THEN 'ocho'
WHEN '09' THEN 'nueve'
WHEN '10' THEN 'diez'
WHEN '11' THEN 'once'
WHEN '12' THEN 'doce'
WHEN '13' THEN 'trece'
WHEN '14' THEN 'catorce'
WHEN '15' THEN 'quince'
WHEN '16' THEN 'dieciseis'
WHEN '17' THEN 'diecisiete'
WHEN '18' THEN 'dieciocho'
WHEN '19' THEN 'diecinueve'
WHEN '20' THEN 'veinte'
WHEN '21' THEN 'veintiun'
WHEN '22' THEN 'veintidos'
WHEN '23' THEN 'veintitres'
WHEN '24' THEN 'veinticuatro'
WHEN '25' THEN 'veinticinco'
WHEN '26' THEN 'veintiseis'
WHEN '27' THEN 'veintisiete'
WHEN '28' THEN 'veintiocho'
WHEN '29' THEN 'veintinueve'
WHEN '30' THEN 'treinta'
WHEN '31' THEN 'trentiun'
ELSE to_char(current_date, 'DD')
END AS \"diatxt\",
CASE to_char(current_date, 'YYYY')
WHEN '2012' THEN 'dos mil doce'
WHEN '2013' THEN 'dos mil trece'
WHEN '2014' THEN 'dos mil catorce'
WHEN '2015' THEN 'dos mil quince'
WHEN '2016' THEN 'dos mil dieciseis'
WHEN '2017' THEN 'dos mil diecisiete'
WHEN '2018' THEN 'dos mil dieciocho'
WHEN '2019' THEN 'dos mil diecinueve'
WHEN '2020' THEN 'dos mil veinte'
WHEN '2021' THEN 'dos mil veintiuno'
WHEN '2022' THEN 'dos mil veintidos'
WHEN '2023' THEN 'dos mil veintitres'
WHEN '2024' THEN 'dos mil veinticuatro'
WHEN '2025' THEN 'dos mil veinticinco'
WHEN '2026' THEN 'dos mil veintiseis'
WHEN '2027' THEN 'dos mil veintisiete'
WHEN '2028' THEN 'dos mil veintiocho'
WHEN '2029' THEN 'dos mil veintinueve'
WHEN '2030' THEN 'dos mil treinta'
WHEN '2031' THEN 'dos mil trentiuno'
WHEN '2032' THEN 'dos mil trentidos'
WHEN '2033' THEN 'dos mil tentitres'
WHEN '2034' THEN 'dos mil trenticuatro'
WHEN '2035' THEN 'dos mil trenticinco'
WHEN '2036' THEN 'dos mil trentiseis'
WHEN '2037' THEN 'dos mil trentisiete'
WHEN '2038' THEN 'dos mil trentiocho'
WHEN '2039' THEN 'dos mil trentinueve'
WHEN '2040' THEN 'dos mil cuarenta'
WHEN '2041' THEN 'dos mil cuarentiuno'
WHEN '2042' THEN 'dos mil cuarentidos'
WHEN '2043' THEN 'dos mil cuarentitres'
WHEN '2044' THEN 'dos mil cuarenticuatro'
WHEN '2045' THEN 'dos mil cuarenticinco'
WHEN '2046' THEN 'dos mil cuarentiseis'
WHEN '2047' THEN 'dos mil cuarentisiete'
WHEN '2048' THEN 'dos mil cuarentiocho'
WHEN '2049' THEN 'dos mil cuarentinueve'
WHEN '2050' THEN 'dos mil cincuenta'
ELSE to_char(current_date, 'YYYY')
  END AS \"añotxt\",
  usu.sexo
FROM tbl_inscripciones i
INNER JOIN tbl_usuariosgrupos  ug    ON ug.pk_usuariogrupo = i.fk_usuariogrupo
INNER JOIN tbl_usuarios        usu   ON usu.pk_usuario     = ug.fk_usuario
INNER JOIN tbl_estructuras     es    ON es.pk_estructura   = i.fk_estructura
INNER JOIN vw_escuelas         e     ON e.pk_atributo      = i.fk_atributo
INNER JOIN tbl_pensums         pen   ON pen.pk_pensum     = i.fk_pensum
INNER JOIN tbl_atributos       atri  ON atri.pk_atributo   = pen.fk_facultad
INNER JOIN vw_semestres        sem	 ON i.fk_semestre      = sem.pk_atributo
INNER JOIN tbl_periodos	   per	 ON i.fk_periodo       = per.pk_periodo
WHERE ug.fk_usuario = {$Ci}
AND i.fk_atributo = {$escuela}
ORDER BY i.fk_periodo DESC
LIMIT 1;";
$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;
}

public function getInfoEstudianteEscuela($Ci, $Escuela) {
    if(empty($Ci)) return;

    $SQL    = "SELECT e.pk_atributo as \"pk_escuela\",
                     pen.codigopropietario as \"pensum\",
                     CASE pen.codigopropietario
                            WHEN '7' then UPPER(sem.valor)
                            else (
                                    case sem.pk_atributo
                    when 873 then UPPER('Primer Periodo')
                    when 874 then UPPER('Segundo Periodo')
                    when 875 then UPPER('Tercer Periodo')
                    when 876 then UPPER('Cuarto Periodo')
                    when 878 then UPPER('Quinto Periodo')
                    when 879 then UPPER('Sexto Periodo')
                    when 881 then UPPER('Séptimo Periodo')
                    when 882 then UPPER('Octavo Periodo')
                    when 883 then UPPER('Noveno Periodo')
                    when 884 then UPPER('Décimo Periodo')
                    when 9696 then UPPER('Décimo Primer Periodo')
                    when 9697 then UPPER('Décimo Segundo Periodo')
                    else 'N/A'
                                    end
                                 )
                     END as \"semestre\",
                     CASE to_char(per.inicioclases, 'TMMonth')
                        WHEN 'May' THEN to_char(per.inicioclases, '\"Mayo\" YYYY')
                        ELSE initcap(to_char(per.inicioclases, 'TMMonth YYYY'))
                        END as \"fia\",
                     CASE to_char(per.fechafin, 'TMMonth')
                        WHEN 'May' THEN to_char(per.fechafin, '\"Mayo\" YYYY')
                        ELSE initcap(to_char(per.fechafin, 'TMMonth YYYY'))
                        END as \"ffa\",
                     e.escuela as \"escuela\",
                     i.fk_estructura,
                     to_char(usu.pk_usuario, '09\".\"999\".\"999') as \"Ci\",
                     TRIM(usu.nombre) as \"nombre\",
                     TRIM(usu.apellido) as \"apellido\",
                     atri.valor as \"facultad\",
                     to_char(current_date, 'DD')::INT as \"dia\",
                     CASE to_char(current_date, 'TMMONTH')
                        WHEN 'MAY' THEN 'Mayo'
                        ELSE initcap(to_char(current_date, 'TMMONTH'))
                        END as \"mes\",
                     to_char(current_date, 'YYYY') as \"año\",
                     CASE to_char(current_date, 'DD')
                      WHEN '01' THEN 'primer'
                      WHEN '02' THEN 'dos'
                      WHEN '03' THEN 'tres'
                      WHEN '04' THEN 'cuatro'
                      WHEN '05' THEN 'cinco'
                      WHEN '06' THEN 'seis'
                      WHEN '07' THEN 'siete'
                      WHEN '08' THEN 'ocho'
                      WHEN '09' THEN 'nueve'
                      WHEN '10' THEN 'diez'
                      WHEN '11' THEN 'once'
                      WHEN '12' THEN 'doce'
                      WHEN '13' THEN 'trece'
                      WHEN '14' THEN 'catorce'
                      WHEN '15' THEN 'quince'
                      WHEN '16' THEN 'dieciseis'
                      WHEN '17' THEN 'diecisiete'
                      WHEN '18' THEN 'dieciocho'
                      WHEN '19' THEN 'diecinueve'
                      WHEN '20' THEN 'veinte'
                      WHEN '21' THEN 'veintiun'
                      WHEN '22' THEN 'veintidos'
                      WHEN '23' THEN 'veintitres'
                      WHEN '24' THEN 'veinticuatro'
                      WHEN '25' THEN 'veinticinco'
                      WHEN '26' THEN 'veintiseis'
                      WHEN '27' THEN 'veintisiete'
                      WHEN '28' THEN 'veintiocho'
                      WHEN '29' THEN 'veintinueve'
                      WHEN '30' THEN 'treinta'
                      WHEN '31' THEN 'trentiun'
            ELSE to_char(current_date, 'DD')
             END AS \"diatxt\",
                      CASE to_char(current_date, 'YYYY')
                      WHEN '2012' THEN 'dos mil doce'
                      WHEN '2013' THEN 'dos mil trece'
                      WHEN '2014' THEN 'dos mil catorce'
                      WHEN '2015' THEN 'dos mil quince'
                      WHEN '2016' THEN 'dos mil dieciseis'
                      WHEN '2017' THEN 'dos mil diecisiete'
                      WHEN '2018' THEN 'dos mil dieciocho'
                      WHEN '2019' THEN 'dos mil diecinueve'
                      WHEN '2020' THEN 'dos mil veinte'
                      WHEN '2021' THEN 'dos mil veintiuno'
                      WHEN '2022' THEN 'dos mil veintidos'
                      WHEN '2023' THEN 'dos mil veintitres'
                      WHEN '2024' THEN 'dos mil veinticuatro'
                      WHEN '2025' THEN 'dos mil veinticinco'
                      WHEN '2026' THEN 'dos mil veintiseis'
                      WHEN '2027' THEN 'dos mil veintisiete'
                      WHEN '2028' THEN 'dos mil veintiocho'
                      WHEN '2029' THEN 'dos mil veintinueve'
                      WHEN '2030' THEN 'dos mil treinta'
                      WHEN '2031' THEN 'dos mil trentiuno'
                      WHEN '2032' THEN 'dos mil trentidos'
                      WHEN '2033' THEN 'dos mil tentitres'
                      WHEN '2034' THEN 'dos mil trenticuatro'
                      WHEN '2035' THEN 'dos mil trenticinco'
                      WHEN '2036' THEN 'dos mil trentiseis'
                      WHEN '2037' THEN 'dos mil trentisiete'
                      WHEN '2038' THEN 'dos mil trentiocho'
                      WHEN '2039' THEN 'dos mil trentinueve'
                      WHEN '2040' THEN 'dos mil cuarenta'
                      WHEN '2041' THEN 'dos mil cuarentiuno'
                      WHEN '2042' THEN 'dos mil cuarentidos'
                      WHEN '2043' THEN 'dos mil cuarentitres'
                      WHEN '2044' THEN 'dos mil cuarenticuatro'
                      WHEN '2045' THEN 'dos mil cuarenticinco'
                      WHEN '2046' THEN 'dos mil cuarentiseis'
                      WHEN '2047' THEN 'dos mil cuarentisiete'
                      WHEN '2048' THEN 'dos mil cuarentiocho'
                      WHEN '2049' THEN 'dos mil cuarentinueve'
                      WHEN '2050' THEN 'dos mil cincuenta'
                       ELSE to_char(current_date, 'YYYY')
                     END AS \"añotxt\"
                    FROM tbl_inscripciones i
                    INNER JOIN tbl_usuariosgrupos  ug    ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                    INNER JOIN tbl_usuarios        usu   ON usu.pk_usuario     = ug.fk_usuario
                    INNER JOIN tbl_estructuras     es    ON es.pk_estructura   = i.fk_estructura
                    INNER JOIN vw_escuelas         e     ON e.pk_atributo      = i.fk_atributo
                    INNER JOIN tbl_pensums         pen   ON pen.pk_pensum      = i.fk_pensum
                    INNER JOIN tbl_atributos       atri  ON atri.pk_atributo   = pen.fk_facultad
                    INNER JOIN vw_semestres        sem   ON i.fk_semestre      = sem.pk_atributo
                    INNER JOIN tbl_periodos    per   ON i.fk_periodo       = per.pk_periodo
                    WHERE ug.fk_usuario = {$Ci}
                    AND i.fk_atributo = {$Escuela}
                    ORDER BY i.fk_periodo DESC
                    LIMIT 1;";
        $results = $this->_db->query($SQL);

$results = $results->fetchAll();

return $results;
}

public function getEstudianteStatus($ci,$escuela){

    $SQL = "SELECT i.fk_periodo  as periodo,
        usu.nombre,
        usu.apellido,
    fn_xrxx_estudiante_calcular_ucac_escuela_pensum({$ci}, {$escuela}, pe.codigopropietario) as uca,
    fn_xrxx_estudiante_iia_escuela_periodo_articulado({$ci},{$escuela}, i.fk_periodo, pe.codigopropietario) as iia,
    (SELECT pk_periodo
        FROM tbl_periodos
        WHERE fechainicio <= current_date
        AND fechafin    >= current_date) as uperiodo
    FROM tbl_inscripciones  i
    JOIN tbl_usuariosgrupos ug  ON  i.fk_usuariogrupo =  ug.pk_usuariogrupo
    JOIN tbl_usuarios       usu ON ug.fk_usuario      = usu.pk_usuario
    JOIN tbl_pensums    pe  ON pe.pk_pensum = i.fk_pensum
    WHERE ug.fk_usuario = {$ci}
    AND i.fk_atributo = {$escuela}
    and i.numeropago is not null
    and (select fn_xrxx_reinscripcion_upi({$ci})) = i.fk_periodo;";
$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results;

}

public function getCursoActual($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT sem.valor, TO_CHAR(per.fechainicio, 'MM/YYYY'), TO_CHAR(per.fechafin, 'MM/YYYY')
    FROM tbl_inscripciones ins
    JOIN tbl_periodos	 per	ON (ins.fk_periodo      = per.pk_periodo)
    JOIN tbl_usuariosgrupos ug	ON (ins.fk_usuariogrupo = ug.pk_usuariogrupo)
    JOIN vw_semestres	 sem	ON (ins.fk_semestre     = sem.pk_atributo)
    WHERE ug.fk_usuario = $Ci
    LIMIT 1";
    return $this->_db->fetchOne($SQL);
}

public function getInformacionGeneral($Ci) {
    if(empty($Ci)) return;

    $SQL = "SELECT  (SELECT * FROM fn_xrxx_estudiante_sem_ubicacion_periodod({$Ci}, ins.fk_atributo::integer, ins.fk_periodo)) AS su,
    (SELECT * FROM fn_xrxx_estudiante_calcular_uca({$Ci}, ins.fk_atributo::integer)) AS uca,
    (SELECT * FROM fn_xrxx_estudiante_iia({$Ci})) AS iaa,
    (SELECT * FROM fn_xrxx_estudiante_iap({$Ci}, (SELECT fn_xrxx_reinscripcion_upc({$Ci})))) AS iap_upc,
    (SELECT CASE
        WHEN fn_xrxx_estudiante_iap>=16 THEN 'Cuadro de Honor'
        WHEN fn_xrxx_estudiante_iap>=11 AND fn_xrxx_estudiante_iap<16 THEN 'Regular'
        WHEN fn_xrxx_estudiante_iap<11 THEN 'Probatorio'
        END
        FROM fn_xrxx_estudiante_iap({$Ci}, (SELECT fn_xrxx_reinscripcion_upc({$Ci})))) AS Estado,
(SELECT fn_xrxx_reinscripcion_upi({$Ci})) AS UPI,
TO_CHAR(per.fechainicio,'TMMONTH YYYY' ) || ' / ' || TO_CHAR(per.fechaFin,'TMMONTH YYYY') AS upi_fecha,
(SELECT * FROM fn_xrxx_reinscripcion_upc({$Ci})) AS UPC,
(SELECT TO_CHAR(fechainicio,'TMMONTH YYYY' ) || ' / ' || TO_CHAR(fechaFin,'TMMONTH YYYY')
    FROM tbl_periodos
    WHERE pk_periodo =(SELECT * FROM fn_xrxx_reinscripcion_upc({$Ci}))) AS upc_fecha,
e.escuela as escuela, e.pk_atributo as esc_cod,
(SELECT array_to_string(ARRAY((SELECT DISTINCT p2.nombre
   FROM tbl_inscripciones   i2
   JOIN tbl_usuariosgrupos    ug2 ON ug2.pk_usuariogrupo =  i2.fk_usuariogrupo
   JOIN tbl_recordsacademicos ra2 on ra2.fk_inscripcion  =  i2.pk_inscripcion
   JOIN tbl_asignaturas        a2 ON  a2.pk_asignatura   = ra2.fk_asignatura
   JOIN tbl_pensums            p2 ON  p2.pk_pensum       =  a2.fk_pensum
   WHERE ug2.fk_usuario = {$Ci})), ', ')) as pensum,
sed.nombre as sede, sed.pk_estructura as sed_cod,
tur.valor
FROM tbl_inscripciones ins
JOIN tbl_usuariosgrupos  ug   ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
JOIN vw_semestres	 sem  ON sem.pk_atributo    = ins.fk_semestre
JOIN tbl_periodos	 per  ON per.pk_periodo     = ins.fk_periodo
JOIN vw_escuelas         e    ON e.pk_atributo      = ins.fk_atributo
JOIN vw_sedes           sed ON sed.pk_estructura      = ins.fk_estructura
JOIN vw_turnos          tur ON (SELECT * FROM fn_xrxx_estudiante_turno(ins.fk_periodo, ins.fk_estructura, {$Ci})) = tur.pk_atributo
WHERE ug.fk_usuario = {$Ci}
AND ins.fk_periodo = (SELECT fn_xrxx_reinscripcion_upi({$Ci}))
ORDER BY ins.fk_periodo";

$results = $this->_db->query($SQL);
$results = $results->fetchAll();

return $results[0];
}

public function getUltimoIndiceAcademicoPorPeriodo($Ci) {
    if(empty($Ci) || !is_numeric($Ci)) return;

    $SQL = "SELECT * FROM fn_xrxx_estudiante_iap({$Ci}, (SELECT fn_xrxx_reinscripcion_upc({$Ci})))";

    return $this->_db->fetchOne($SQL);
}

public function getIndiceindiceAcademico($ci){
    if(empty($ci) || !is_numeric($ci)) return;
        //var_dump($ci);
    $SQL = "SELECT fn_xrxx_estudiante_iia($ci);";
    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();

    return $results;

}

public function getUltimoPeriodocursado($ci){
    if(empty($ci) || !is_numeric($ci)) return;

    $SQL = "SELECT fn_xrxx_reinscripcion_upc($ci);";
    $results = $this->_db->query($SQL);
    $results = $results->fetchAll();

    return $results;


}

public function getBuscarMateriasRaspadas($escuela,$ci,$periodo,$materia){

   $SQL = " SELECT m.materia, ra.calificacion as calificacion, ag.codigopropietario
   FROM tbl_asignaturas ag
   JOIN tbl_pensums p ON ag.fk_pensum = p.pk_pensum
   JOIN vw_materias m ON ag.fk_materia = m.pk_atributo
   JOIN tbl_recordsacademicos ra ON ra.fk_asignatura = ag.pk_asignatura
   JOIN tbl_inscripciones ins ON ra.fk_inscripcion = ins.pk_inscripcion
   JOIN tbl_usuariosgrupos ug ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
   WHERE p.fk_escuela = $escuela
                  --AND p.nombre = '1997'
                  AND ug.fk_usuario = $ci
                  AND calificacion < 11
                  AND ra.fk_atributo = 862
                  AND ins.fk_periodo < $periodo
                  AND ag.codigopropietario = '$materia'
                  ORDER BY ins.fk_periodo desc
                  limit 1;";

                 // echo $SQL;

                  return $this->_db->fetchAll($SQL);

              }
            public function getAsignacion($codigopropietario,$seccion,$periodo,$sede = null){
        //busca nueva asignacion para cambio de seccion
                $SQL = "SELECT pk_asignacion
                FROM tbl_asignaciones asi
                JOIN tbl_asignaturas ass ON ass.pk_asignatura   = asi.fk_asignatura
        		JOIN tbl_estructuras est1 ON est1.pk_estructura = asi.fk_estructura
        		JOIN tbl_estructuras est2 ON est2.pk_estructura = est1.fk_estructura
                WHERE ass.codigopropietario = '{$codigopropietario}'
                AND fk_seccion = {$seccion}
                AND fk_periodo = {$periodo}";
            		if ($sede != null)
            			$SQL  .= "AND est2.fk_estructura = $sede";
                return $this->_db->fetchOne($SQL);

            }
            public function getMateria($recordacademico){

                $SQL = "select distinct vma.pk_atributo, vma.materia
                from vw_materias		vma
                join tbl_asignaturas		a	on	a.fk_materia = vma.pk_atributo
                join tbl_recordsacademicos	ra	on	ra.fk_asignatura = a.pk_asignatura
                where ra.pk_recordacademico = {$recordacademico}";

                return $this->_db->fetchAll($SQL);

            }

            public function getBuscarRecord($record){
                $SQL = "SELECT ag.codigopropietario
                FROM tbl_recordsacademicos ra
                JOIN tbl_asignaturas ag ON ra.fk_asignatura = ag.pk_asignatura
                WHERE ra.pk_recordacademico = $record";

                return $this->_db->fetchOne($SQL);
            }

            public function getBuscarMateria($record){
                $SQL = "SELECT ag.fk_materia
                FROM tbl_recordsacademicos ra
                JOIN tbl_asignaturas ag ON ra.fk_asignatura = ag.pk_asignatura
                WHERE ra.pk_recordacademico = $record";

                return $this->_db->fetchOne($SQL);
            }

            public function getSemestremateria($recordacademico){

                $SQL = "SELECT sem.pk_atributo , sem.id
                FROM tbl_recordsacademicos rec
                JOIN tbl_asignaciones asi ON asi.pk_asignacion = rec.fk_asignacion
                JOIN vw_semestres sem ON asi.fk_semestre = sem.pk_atributo
                where rec.pk_recordacademico = {$recordacademico}";
                return $this->_db->fetchAll($SQL);
            }

            public function getDatosasignacion($asignacion){

                $SQL = "SELECT fk_periodo as periodo,fk_dia as dia,fk_horario as horario,fk_estructura as estructura
                FROM tbl_asignaciones
                WHERE pk_asignacion = {$asignacion}";
                return $this->_db->fetchAll($SQL);
            }

            public function coincidenciaseccion($periodo,$dia,$horario,$estructura,$asignacion){
                $SQL = "SELECT*
                FROM
                fn_xrxx_horarios_coincide_salon({$periodo}, {$dia},{$horario}, {$estructura},{$asignacion})";

                if ($this->_db->fetchOne($SQL)== 1)
                 return true;
             else
                 return false;
         }


         public function getBuscarEstado($record){
            $SQL = "SELECT ra.fk_atributo
            FROM tbl_recordsacademicos ra
            JOIN tbl_asignaturas ag ON ra.fk_asignatura = ag.pk_asignatura
            WHERE ra.pk_recordacademico = {$record}";

            return $this->_db->fetchOne($SQL);
        }

        public function getBuscarDatosEstudiante($pkrecordacademico){
            $SQL = "SELECT p.fk_escuela, ug.fk_usuario, ins.fk_periodo
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones ins ON ra.fk_inscripcion = ins.pk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
            JOIN tbl_asignaturas ag ON ra.fk_asignatura = ag.pk_asignatura
            JOIN tbl_pensums p ON ag.fk_pensum = p.pk_pensum
            WHERE ra.pk_recordacademico = {$pkrecordacademico};";
            return $this->_db->fetchAll($SQL);

        }

        public function getCIFromPK($PK) {
            if(empty($PK)) return;

            $SQL = "SELECT DISTINCT ug.fk_usuario
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones   i ON  i.pk_inscripcion  = ra.fk_inscripcion
            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo  = i.fk_usuariogrupo
            WHERE ra.pk_recordacademico = {$PK}";

            return $this->_db->fetchOne($SQL);
        }

        public function deleteAll($inscripcion, $tipo) {
            $SQL = "DELETE FROM tbl_recordsacademicos
            WHERE fk_inscripcion = {$inscripcion}
            AND fk_atributo    = {$tipo}";

            return $this->_db->fetchOne($SQL);
        }

        public function retirarSemestre($ci, $periodo, $escuela, $sede, $pensum,$retirar = false) {
            $SQL = "SELECT *
                    FROM (  SELECT 
                            (SELECT COUNT(DISTINCT ra.pk_recordacademico)
                            FROM tbl_inscripciones ins
                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                            WHERE ug.fk_usuario   = {$ci}
                            AND ins.fk_periodo    = {$periodo}
                            AND ins.fk_estructura = {$sede}
                            AND ins.fk_atributo   = {$escuela}
                            AND ins.fk_pensum   = {$pensum}
                            AND ra.fk_atributo    = 864) AS cantidad_inscritas,
                            (SELECT COUNT(tr.pk_recordacademico)
                            FROM tbl_usuariosgrupos tg
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                            JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
                            JOIN tbl_atributos tt ON ts.fk_materia = tt.pk_atributo
                            WHERE tg.fk_usuario = {$ci}
                            AND ti.fk_periodo = {$periodo}
                            AND ti.fk_estructura = {$sede}
                            AND ti.fk_atributo   = {$escuela}
                            AND ti.fk_pensum   = {$pensum}
                            AND ts.fk_materia = 9724) AS trabajo_grado_2
                        ) AS sqt";
            $result = $this->_db->fetchAll($SQL);
            $result = $result[0];
            if($retirar && $result['cantidad_inscritas'] > 0 && $result['trabajo_grado_2'] < 1){
               $SQL = " UPDATE tbl_recordsacademicos
               SET fk_atributo = 863
               WHERE pk_recordacademico IN (
                  SELECT DISTINCT ra.pk_recordacademico
                  FROM tbl_inscripciones ins
                  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                  JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                  WHERE ug.fk_usuario   = {$ci}
                  AND ins.fk_periodo    = {$periodo}
                  AND ins.fk_pensum   = {$pensum}
                  AND ins.fk_estructura = {$sede}
                  AND ins.fk_atributo   = {$escuela}
                  AND ra.fk_atributo    = 864)
                RETURNING pk_recordacademico;";
                $result = $this->_db->fetchAll($SQL);
                return $result;
            }
            else{
                return false;
            }
        }

public function resumenAcademico($id,$periodo){
    if (!$periodo) {
        $periodo=0;
    }
    $SQL = "SELECT  distinct usr,
    esc as escuela,
    (ucaune+ucatras) as uca,
    indicehastahoy as indice,
    indicepant as indiceant,
    sociala as sociala,
    sociali as sociali,
    CASE WHEN (ucaune+ucatras) >= 88 AND sociali IS NULL AND sociala IS NULL THEN true END AS socialinscribible,
    proa as proa,
    proi as proi,
    CASE WHEN semubic >= 8 AND proa IS NULL AND proi IS NULL THEN true END AS proinscribible

    FROM(
        SELECT
        distinct ug.fk_usuario as usr,
        esc.escuela as esc,
        ins.fk_atributo as at,
        p.pk_periodo as per,

        (fn_xrxx_estudiante_iia_escuela_periodo_articulado(ug.fk_usuario, ins.fk_atributo, p.pk_periodo,pe.codigopropietario)) as indicehastahoy,

        fn_xrxx_estudiante_calcular_ucac_escuela_pensum( ug.fk_usuario, ins.fk_atributo::integer,pe.codigopropietario) as ucaune,

        fn_xrxx_estudiante_iap_sce(ug.fk_usuario, (p.pk_periodo-1), ins.fk_atributo) as indicepant,

        fn_xrxx_estudiante_sem_ubicacion_periodod(ug.fk_usuario, ins.fk_atributo, ins.fk_periodo) as semubic,

        (SELECT COALESCE(SUM(A1.UnidadCredito),0)
         FROM tbl_recordsacademicos RA1
         INNER JOIN tbl_asignaturas A1 ON RA1.FK_Asignatura = A1.PK_Asignatura
         INNER JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra1.fk_inscripcion
         INNER JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = i1.fk_usuariogrupo
         INNER JOIN tbl_pensums p1 ON p1.pk_pensum = A1.fk_pensum
         WHERE ug1.FK_Usuario = ug.fk_usuario AND
         i1.fk_atributo = ins.fk_atributo
                                  AND p1.pk_pensum = (SELECT pk_pensum -- CAMBIOS ACA
                                     FROM tbl_inscripciones I
                                     JOIN tbl_usuariosgrupos UG ON UG.pk_usuariogrupo = I.fk_usuariogrupo AND UG.fk_usuario = ug1.fk_usuario
                                     JOIN tbl_pensums P1 ON P1.pk_pensum = i.fk_pensum
                                     ORDER BY i.fk_periodo DESC LIMIT 1)
AND RA1.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos)) as ucatras,


(SELECT distinct true
 FROM tbl_usuariosgrupos ug1
 JOIN tbl_inscripciones i1 ON i1.fk_usuariogrupo = ug1.pk_usuariogrupo
 JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
 JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ra1.fk_asignatura
 JOIN vw_materias ma ON ma.pk_atributo = ag1.fk_materia
 JOIN tbl_periodos p1 ON p1.pk_periodo = i1.fk_periodo
 WHERE ug1.fk_usuario = ug.fk_usuario
 AND ma.materia ilike '%pasantia social%'
 AND p1.pk_periodo < p.pk_periodo
                           AND ra1.fk_atributo = 862) as \"sociala\", -- social aprobada en algun momento

(SELECT distinct true
 FROM tbl_usuariosgrupos ug1
 JOIN tbl_inscripciones i1 ON i1.fk_usuariogrupo = ug1.pk_usuariogrupo
 JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
 JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ra1.fk_asignatura
 JOIN vw_materias ma ON ma.pk_atributo = ag1.fk_materia
 JOIN tbl_periodos p1 ON p1.pk_periodo = i1.fk_periodo
 WHERE ug1.fk_usuario = ug.fk_usuario
 AND ma.materia ilike '%pasantia social%'
 AND p1.pk_periodo = p.pk_periodo
                           AND ra1.fk_atributo = 864) as \"sociali\", -- social inscrita

(SELECT distinct true
 FROM tbl_usuariosgrupos ug1
 JOIN tbl_inscripciones i1 ON i1.fk_usuariogrupo = ug1.pk_usuariogrupo
 JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
 JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ra1.fk_asignatura
 JOIN vw_materias ma ON ma.pk_atributo = ag1.fk_materia
 JOIN tbl_periodos p1 ON p1.pk_periodo = i1.fk_periodo
 WHERE ug1.fk_usuario = ug.fk_usuario
 AND ma.materia ilike '%pasantia profesional%'
 AND p1.pk_periodo <= p.pk_periodo
                           AND ra1.fk_atributo = 862) as \"proa\", -- pro aprobada en algun momento

(SELECT distinct true
 FROM tbl_usuariosgrupos ug1
 JOIN tbl_inscripciones i1 ON i1.fk_usuariogrupo = ug1.pk_usuariogrupo
 JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
 JOIN tbl_asignaturas ag1 ON ag1.pk_asignatura = ra1.fk_asignatura
 JOIN vw_materias ma ON ma.pk_atributo = ag1.fk_materia
 JOIN tbl_periodos p1 ON p1.pk_periodo = i1.fk_periodo
 WHERE ug1.fk_usuario = ug.fk_usuario
 AND ma.materia ilike '%pasantia profesional%'
 AND p1.pk_periodo = p.pk_periodo
                           AND ra1.fk_atributo IN (864,904)) as \"proi\" -- pro aprobada en algun momento



from tbl_usuariosgrupos ug
JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
join  tbl_pensums	pe on	ins.fk_pensum = pe.pk_pensum
JOIN vw_escuelas esc ON esc.pk_atributo = ins.fk_atributo
JOIN tbl_periodos p ON p.pk_periodo = ins.fk_periodo
WHERE p.pk_periodo = {$periodo}
AND ug.fk_usuario = {$id}

)as sqt
";
$results = $this->_db->query($SQL);
return (array)$results->fetchAll();

}

public function getPiraReinscritos($periodo,$sede,$escuela){

   $SQL = "SELECT DISTINCT u.pk_usuario,
   (LTRIM(TO_CHAR(pk_usuario, '99"."999"."999')::varchar, '0. '))as ci,
   u.apellido,
   u.nombre,
   es.escuela,
   se.id AS sem,
   fn_xrxx_estudiante_iap(u.pk_usuario, i.fk_periodo) AS iap,
   fn_xrxx_estudiante_iia_escuela_new2(u.pk_usuario, i.fk_atributo) AS iaa
   FROM tbl_inscripciones i
   JOIN tbl_usuariosgrupos 	ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
   JOIN tbl_usuarios 	   	u  ON u.pk_usuario 	 = ug.fk_usuario
   JOIN tbl_recordsacademicos	ra ON ra.fk_inscripcion  = i.pk_inscripcion
   JOIN tbl_asignaturas 		ag ON ag.pk_asignatura   = ra.fk_asignatura
   JOIN vw_escuelas		es ON es.pk_atributo	 = i.fk_atributo
   JOIN vw_semestres 		se ON se.pk_atributo	 = i.fk_semestre
   WHERE i.fk_periodo = {$periodo}
   AND ag.fk_materia = 1701
   AND i.fk_estructura = {$sede}
   AND ra.fk_atributo <> 863
   AND i.fk_atributo = {$escuela}
   AND u.pk_usuario IN (SELECT ug3.fk_usuario
      FROM tbl_inscripciones i3
      JOIN tbl_usuariosgrupos 	ug3 ON ug3.pk_usuariogrupo = i3.fk_usuariogrupo
      JOIN tbl_recordsacademicos	ra3 ON ra3.fk_inscripcion  = i3.pk_inscripcion
      WHERE i3.fk_periodo = {$periodo} + 1
      GROUP BY 1)
AND fn_xrxx_estudiante_iap(u.pk_usuario, i.fk_periodo) >= 0
ORDER BY 7 DESC, 8 DESC;";



return $this->_db->fetchAll($SQL);

}

public function getFueraPira($periodo,$sede,$escuela){

   $SQL = "SELECT DISTINCT u.pk_usuario,
   (LTRIM(TO_CHAR(pk_usuario, '99"."999"."999')::varchar, '0. '))as ci,
   u.apellido,
   u.nombre,
   es.escuela,
   se.id AS sem,
   fn_xrxx_estudiante_iap(u.pk_usuario, i.fk_periodo) AS iap,
   fn_xrxx_estudiante_iia_escuela_new2(u.pk_usuario, i.fk_atributo) AS iaa
   FROM tbl_inscripciones i
   JOIN tbl_usuariosgrupos 	ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
   JOIN tbl_usuarios 	   	u  ON u.pk_usuario 	 = ug.fk_usuario
   JOIN tbl_recordsacademicos	ra ON ra.fk_inscripcion  = i.pk_inscripcion
   JOIN tbl_asignaturas 		ag ON ag.pk_asignatura   = ra.fk_asignatura
   JOIN vw_escuelas		es ON es.pk_atributo	 = i.fk_atributo
   JOIN vw_semestres 		se ON se.pk_atributo	 = i.fk_semestre
   WHERE i.fk_periodo =  {$periodo}
   AND ag.fk_materia = 1701
   AND i.fk_estructura = {$sede}
   AND i.fk_atributo = {$escuela}
   AND u.pk_usuario  NOT IN (SELECT ug2.fk_usuario
       FROM tbl_inscripciones i2
       JOIN tbl_usuariosgrupos 	ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo
       WHERE i2.fk_periodo = {$periodo} + 1)
AND fn_xrxx_estudiante_iia_escuela_new2(u.pk_usuario, i.fk_atributo) < 11
AND fn_xrxx_estudiante_iap(u.pk_usuario, i.fk_periodo) < 11
ORDER BY 7 DESC,8 DESC;";



return $this->_db->fetchAll($SQL);

}

//    public function getPensum($Ci){
//         $SQL = "SELECT p.pk_pensum , p.nombre
//                 FROM tbl_pensums p
//                 JOIN tbl_inscripciones i ON i.fk_pensum = p.pk_pensum
//                 JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
//                 JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
//                 WHERE u.pk_usuario = {$Ci}
//                 GROUP BY 1,2
//                 ORDER BY 2";
//         return $this->_db->fetchAll($SQL);
//    }


public function getEstadoArticulacion($ci){
   $SQL = "
   SELECT COUNT(ins.fk_periodo), MAX(ins.fk_periodo)
   FROM tbl_inscripciones ins
   JOIN tbl_usuariosgrupos  ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
   JOIN tbl_pensums       pen ON pen.pk_pensum      = ins.fk_pensum
   WHERE ug.fk_usuario = {$ci}
   AND pen.codigopropietario = 8
   ;";



   return $this->_db->fetchAll($SQL);
}

public function removeArticulacion($ci, $escuela) {
  $this->_db->beginTransaction();

  $SQL = "
  DELETE FROM tbl_recordsacademicos
  WHERE pk_recordacademico IN(
      SELECT DISTINCT
      ra.pk_recordacademico
      FROM tbl_inscripciones ins
      JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
      JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  ins.fk_usuariogrupo
      WHERE ug.fk_usuario = {$ci}
      AND ins.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
      AND ra.fk_atributo = 864
      );
";

$return += $this->_db->query($SQL);

$SQL = "
DELETE FROM tbl_inscripciones
WHERE pk_inscripcion IN (
  SELECT DISTINCT
  ins.pk_inscripcion
  FROM tbl_inscripciones ins
  JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  ins.fk_usuariogrupo
  WHERE ug.fk_usuario = {$ci}
  AND ins.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
  AND ra.fk_atributo = 864
  );
";

$return += $this->_db->query($SQL);
      //Borramos el record de las materias articuladas
$SQL = "
DELETE FROM tbl_recordsacademicos
WHERE pk_recordacademico IN (
  SELECT ra.pk_recordacademico
  FROM tbl_recordsacademicos ra
  JOIN tbl_inscripciones    ins ON ins.pk_inscripcion = ra.fk_inscripcion
  JOIN tbl_asignaturas      asi ON asi.pk_asignatura  = ra.fk_asignatura
  JOIN vw_materias          mat ON mat.pk_atributo   = asi.fk_materia
  WHERE ins.pk_inscripcion IN(
     SELECT ins.pk_inscripcion
     FROM tbl_inscripciones ins
     JOIN tbl_usuariosgrupos  ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
     JOIN tbl_pensums       pen ON pen.pk_pensum      = ins.fk_pensum
     WHERE ug.fk_usuario = {$ci}
     AND pen.codigopropietario = 8
     )
);
";

$return += $this->_db->query($SQL);

		// Cambiamos el usuario el la tabla de usuariogrupo, asi evitamos cambiar
		// todos los registros relacionados al viejo usuario.
$SQL = "
DELETE FROM tbl_inscripciones
WHERE pk_inscripcion IN (
  SELECT ins.pk_inscripcion
  FROM tbl_inscripciones ins
  JOIN tbl_usuariosgrupos  ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
  JOIN tbl_pensums       pen ON pen.pk_pensum      = ins.fk_pensum
  WHERE ug.fk_usuario = {$ci}
  AND pen.codigopropietario = 8
  );
";

$return += $this->_db->query($SQL);

$this->_db->commit();

return $return;
}

public function makeArticulacion($ci, $escuela) {
  $this->_db->beginTransaction();

  $SQL = "
  DELETE FROM tbl_recordsacademicos
  WHERE pk_recordacademico IN(
      SELECT DISTINCT
      ra.pk_recordacademico
      FROM tbl_inscripciones ins
      JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
      JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  ins.fk_usuariogrupo
      WHERE ug.fk_usuario = {$ci}
      AND ins.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
      AND ra.fk_atributo = 864
      );
";

        //$return += $this->_db->query($SQL);

$SQL = "
DELETE FROM tbl_inscripciones
WHERE pk_inscripcion IN (
  SELECT DISTINCT
  ins.pk_inscripcion
  FROM tbl_inscripciones ins
  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  ins.fk_usuariogrupo
  WHERE ug.fk_usuario = {$ci}
  AND ins.fk_periodo = (SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1)
  );
";

        //$return += $this->_db->query($SQL);

$SQL = "
DELETE FROM tbl_recordsacademicosarticulados
WHERE pk_recordacademicoarticulado IN (
    SELECT pk_recordacademicoarticulado
    FROM tbl_recordsacademicosarticulados rarti
    JOIN tbl_inscripciones ins ON rarti.fk_inscripcion = ins.pk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
    WHERE ug.fk_usuario = {$ci}
    );
";

$return += $this->_db->query($SQL);

      // $SQL = "
      //             SELECT fn_xrxx_articular_especifico(6, 2, {$escuela}, {$ci});
$SQL = "
INSERT INTO tbl_inscripciones(
  fk_usuariogrupo, numeropago, fechahora, fk_periodo,
  fk_atributo, fk_estructura, ucadicionales, fk_semestre, observaciones,
  fk_tipo, pago_manual, online, fk_pensum)
SELECT
ins.fk_usuariogrupo,
ins.numeropago,
ins.fechahora      ,
ins.fk_periodo     ,
ins.fk_atributo    ,
ins.fk_estructura  ,
ins.ucadicionales  ,
ins.fk_semestre    ,
ins.observaciones  ,
ins.fk_tipo        ,
ins.pago_manual    ,
false,
(
    SELECT pk_pensum
    FROM tbl_pensums
    WHERE fk_escuela =
    (
        SELECT fk_escuela
        FROM tbl_pensums
        WHERE pk_pensum = ins.fk_pensum
        )
AND codigopropietario = 8
)

FROM tbl_inscripciones ins
JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
WHERE ug.fk_usuario = {$ci}
AND ins.fk_atributo = {$escuela}
AND pen.codigopropietario = 7
AND ins.fk_periodo NOT IN (
   SELECT fk_periodo
   FROM tbl_inscripciones ins
   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
   JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
   WHERE ug.fk_usuario = {$ci}
   AND pen.codigopropietario = 8
   AND ins.fk_atributo = {$escuela});

";

$return += $this->_db->query($SQL);

      //       ";
$SQL = "
SELECT fn_xrxx_articular_especifico_nofilter({$escuela}, {$ci});
";

$return += $this->_db->query($SQL);
      //Borramos el record de las materias articuladas


		// Cambiamos el usuario el la tabla de usuariogrupo, asi evitamos cambiar
		// todos los registros relacionados al viejo usuario.
$SQL = "
INSERT INTO tbl_recordsacademicos(
   fk_atributo, calificacion, fk_asignatura,
   fk_inscripcion)
SELECT * FROm (
   SELECT DISTINCT
   raart.fk_atributo                 ,
   raart.calificacion                ,
   raart.fk_asignatura               ,
   (SELECT ins_new.pk_inscripcion FROM tbl_inscripciones ins
       JOIN tbl_inscripciones ins_new ON (ins.fk_periodo = ins_new.fk_periodo
          AND ins.fk_usuariogrupo = ins_new.fk_usuariogrupo
          AND ins.fk_atributo = ins_new.fk_atributo
          )
JOIN tbl_pensums pen ON pen.pk_pensum = ins_new.fk_pensum
WHERE ins.pk_inscripcion = raart.fk_inscripcion
AND pen.codigopropietario = 8
AND pen.fk_escuela = ins.fk_atributo
) as pk_inscripcion
FROM tbl_recordsacademicosarticulados raart
JOIN tbl_inscripciones ins ON ins.pk_inscripcion = raart.fk_inscripcion
JOIN tbl_usuariosgrupos     ug  ON ug.pk_usuariogrupo      = ins.fk_usuariogrupo
WHERE ug.fk_usuario = {$ci}
AND raart.fk_asignatura NOT IN (
    SELECT fk_asignatura
    FROM tbl_recordsacademicos ra
    JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
    JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.fk_asignatura
    JOIN tbl_pensums pen ON pen.pk_pensum = asi.fk_pensum
    WHERE ug.fk_usuario = {$ci}
    AND pen.codigopropietario = 8
    )
ORDER BY 4 DESC
) as sqt
WHERE sqt.pk_inscripcion IS NOT NULL

";

$return += $this->_db->query($SQL);

$this->_db->commit();

return $return;
}

public function isNuevoIngreso($cedula){
    $SQL = "
        SELECT COUNT(Distinct fk_periodo) <= 1  as nuevo_ingreso
    FROM tbl_inscripciones ins
    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
    WHERE fk_usuario = {$cedula} and ra.fk_atributo = 862
";

return $this->_db->fetchOne($SQL);
}

public function saberCodigoPensum($pk_pensum){
    $SQL = "SELECT codigopropietario
    FROM tbl_pensums
    WHERE pk_pensum = {$pk_pensum}
    ";

    return $this->_db->fetchOne($SQL);
}

public function getEstudiantesPira($periodo,$sede,$escuela){

     $SQL = "
     select cedula, estudiante, escuela, case when iap is null then 0 else iap end as iperiodo, iia
from(
select DISTINCT pk_usuario as cedula,
u.nombre||','||u.apellido as estudiante,
escuela,fn_xrxx_estudiante_iap_escuela_pensum (pk_usuario, fk_periodo, p.fk_escuela, p.codigopropietario) as iap
,fn_xrxx_estudiante_iia_escuela_periodo_articulado
        (u.pk_usuario, i.fk_atributo, i.fk_periodo, (case
        when i.fk_periodo <= 123 then 7
        else 8 end))::decimal as iia
from tbl_usuarios u
join tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
join tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
join tbl_recordsacademicos ra on ra.fk_inscripcion = i.pk_inscripcion
join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
join tbl_pensums p on p.pk_pensum = a.fk_pensum
join vw_escuelas es on es.pk_atributo = p.fk_escuela
where i.fk_periodo = {$periodo}
and a.fk_materia = 1701
order by escuela) as sqt";

    return $this->_db->fetchAll($SQL);
}

public function getCodigopropietario($pensum) {
	// body...
	$SQL = "SELECT codigopropietario FROM tbl_pensums
		where pk_pensum = {$pensum}";
	return $this->_db->fetchOne($SQL);
}
public function getEvaluacionesParciales($data) {
	$SQL = "
		SELECT DISTINCT re.pk_regimen_evaluacion as pk_atributo,a1.valor as abrev,re.maximo,re.ordinal,
		re.evaluable,re.fk_lapso
		from tbl_asignaturas ag
		JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
		JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
		JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
		JOIN tbl_atributos a1 ON a1.pk_atributo = re.fk_tipo_evaluacion
		JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
		--LEFT SI LA ASIGNACION NO ES OBLIGATORIA
		JOIN tbl_asignaciones ac ON ac.fk_asignatura = ag.pk_asignatura
		JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
		JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
		JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
		JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  ac.fk_usuariogrupo
		JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
		WHERE ac.fk_periodo  = {$data['periodo']}
		AND e3.pk_estructura = {$data['sede']}
		AND  p.fk_escuela    = {$data['escuela']}
		AND  p.pk_pensum     = {$data['pensum']}
		AND ac.fk_periodo    >= rh.fk_periodo_inicio AND (ac.fk_periodo <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
	"; // NUEVO
	if(!empty($data['semestre']))
		$SQL .= "AND ac.fk_semestre   = {$data['semestre']} ";
	if(!empty($data['materia']))
		$SQL .= "AND ag.fk_materia    = {$data['materia']} ";
	$SQL .="ORDER BY re.ordinal";
	return $this->_db->fetchAll($SQL);
}
public function getAllEvaluacionesMax($data){
	$sql="
		SELECT * from tbl_regimenes_evaluaciones re
		JOIN tbl_regimenes_historicos rh on rh.pk_regimen_historico = re.fk_regimen_historico
		JOIN tbl_asignaturas_regimenes ar on ar.fk_regimen_historico = rh.pk_regimen_historico
		JOIn tbl_asignaturas a ON a.pk_asignatura = ar.fk_asignatura
		where
{$data['periodo']} >= rh.fk_periodo_inicio AND ({$data['periodo']} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
		AND a.fk_pensum = {$data['pensum']}
		and a.fk_materia = {$data['materia']}
		";
	return $this->_db->fetchAll($sql);
}
/*
public function transaction_calificaciones_parciales($insert, $delete, $isFinal, $records, $inasistencias, $periodo){
	$function = "SELECT fn_cxux_upsert_calificacion_parcial";
	$this->_db->beginTransaction();
		try {
			foreach ($delete as $data) {
				$sql = "DELETE FROM tbl_recordsacademicos_evaluaciones
					WHERE fk_recordacademico = {$data['fk_recordacademico']} AND fk_evaluacion = {$data['fk_evaluacion']}";
				$success = $this->_db->FetchOne($sql);
			}
			foreach($insert as $data) {
				$sql = "{$function} ({$data['fk_recordacademico']},{$data['calificacion']},{$data['fk_evaluacion']})";
				$success = $this->_db->FetchOne($sql);
				if (!$success) {
					$this->_db->rollBack();
					return false;
				}
			}
			if ($isFinal && !empty($records)) {
				foreach($records as $record) {
					$sql = "UPDATE {$this->_name}
						set calificacion = (
							SELECT CASE  when count(distinct rae.fk_evaluacion) = 0 OR
							(SELECT sum(rae.calificacion)
							FROM tbl_recordsacademicos_evaluaciones rae
							JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
							where fk_recordacademico = {$record} AND re.evaluable = false AND re.fk_tipo_evaluacion IN (20045,20049)
						) >= {$inasistencias} then ROUND(sum(calificacion::NUMERIC))::smallint

			else ROUND(sum(calificacion::NUMERIC))::smallint end as calificacion
				FROM tbl_recordsacademicos_evaluaciones rae
				JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
				where fk_recordacademico = {$record} AND re.evaluable = true
            ),fk_atributo = (SELECT CASE COUNT(fk_tipo_evaluacion) = 0 AND (SELECT count(pk_recordacademico_evaluacion) FROM tbl_recordsacademicos_evaluaciones rae
                                                WHERE fk_recordacademico = {$record}
) > 0  WHEN TRUE THEN 862 ELSE 864 END estado FROM (

SELECT DISTINCT re1.fk_tipo_evaluacion FROM tbl_recordsacademicos_evaluaciones rae
JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion AND fk_recordacademico = {$record}
CROSS JOIN tbl_regimenes_evaluaciones re1
JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = re1.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
AND {$periodo}  >= rh.fk_periodo_inicio AND ({$periodo} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
EXCEPT
SELECT DISTINCT re.fk_tipo_evaluacion FROM tbl_recordsacademicos_evaluaciones rae
JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion AND fk_recordacademico = {$record}
JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = re.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
AND {$periodo}  >= rh.fk_periodo_inicio AND ({$periodo} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)

) as sqt )
WHERE {$this->_primary} = {$record} AND fk_atributo IN (862,864,1699);";
					$success = $this->_db->FetchOne($sql);
				}
			}
		} catch (Zend_Db_Exception $e) {
			$this->_db->rollBack();
			return false;
		}
	$this->_db->commit();
	return true;
}
*/
public function transaction_calificaciones_parciales($insert, $delete, $isFinal, $records, $inasistencias, $periodo){
    $function = "SELECT fn_cxux_upsert_calificacion_parcial";
    $this->_db->beginTransaction();
        try {
            foreach ($delete as $data) {
                $sql = "DELETE FROM tbl_recordsacademicos_evaluaciones
                    WHERE fk_recordacademico = {$data['fk_recordacademico']} AND fk_evaluacion = {$data['fk_evaluacion']}";
                $success = $this->_db->FetchOne($sql);
            }
            foreach($insert as $data) {
                $sql = "{$function} ({$data['fk_recordacademico']},{$data['calificacion']},{$data['fk_evaluacion']})";
                $success = $this->_db->FetchOne($sql);
                if (!$success) {
                    $this->_db->rollBack();
                    return false;
                }
            }
            if ($isFinal && !empty($records)) {
                foreach($records as $record) {
                    $sql = "UPDATE {$this->_name}
                        set calificacion = (
                            SELECT CASE  when count(distinct rae.fk_evaluacion) = 0 OR
                            (SELECT sum(rae.calificacion)
                            FROM tbl_recordsacademicos_evaluaciones rae
                            JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
                            where fk_recordacademico = {$record} AND re.evaluable = false AND re.fk_tipo_evaluacion IN (20045,20049)
                        ) >= {$inasistencias} then 0

            else ROUND(sum(calificacion::NUMERIC))::smallint end as calificacion
                FROM tbl_recordsacademicos_evaluaciones rae
                JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
                where fk_recordacademico = {$record} AND re.evaluable = true
            ),fk_atributo = (SELECT CASE COUNT(fk_tipo_evaluacion) = 0 AND (SELECT count(pk_recordacademico_evaluacion) FROM tbl_recordsacademicos_evaluaciones rae
                                                WHERE fk_recordacademico = {$record}
) > 0  WHEN TRUE THEN 862 ELSE 864 END estado FROM (

SELECT DISTINCT re1.fk_tipo_evaluacion FROM tbl_recordsacademicos_evaluaciones rae
JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion AND fk_recordacademico = {$record}
CROSS JOIN tbl_regimenes_evaluaciones re1
JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = re1.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
AND {$periodo}  >= rh.fk_periodo_inicio AND ({$periodo} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
EXCEPT
SELECT DISTINCT re.fk_tipo_evaluacion FROM tbl_recordsacademicos_evaluaciones rae
JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion AND fk_recordacademico = {$record}
JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = re.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
AND {$periodo}  >= rh.fk_periodo_inicio AND ({$periodo} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)

) as sqt )
WHERE {$this->_primary} = {$record} AND fk_atributo IN (862,864,1699);";
//var_dump($sql);die;
                    //Error de fetchone
                    //$success = $this->_db->FetchOne($sql);
                    $success = $this->_db->FetchAll($sql);
                }
            }
        } catch (Zend_Db_Exception $e) {
            $this->_db->rollBack();
            return false;
        }
    $this->_db->commit();
    return true;
}

public function isValidUpdateForUser($user, $periodo, $updateRecords){

	$sql = "SELECT count(pk_recordacademico) = 0 from tbl_recordsacademicos where pk_recordacademico IN ({$updateRecords})
		AND pk_recordacademico NOT IN (
			SELECT pk_recordacademico FROM tbl_inscripciones i
			join tbl_usuariosgrupos uge ON uge.pk_usuariogrupo = i.fk_usuariogrupo
			join tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion
			join tbl_asignaciones ac ON ac.pk_asignacion = ra.fk_asignacion
			join tbl_usuariosgrupos ugp on ugp.pk_usuariogrupo = ac.fk_usuariogrupo
			JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
			JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
			JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
			join tbl_asignaturas ag on ag.pk_asignatura = ra.fk_asignatura
			JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
			WHERE ac.fk_periodo    = {$periodo}
			and ugp.fk_usuario = {$user})";
	return $this->_db->fetchOne($sql);
}

public function getCountRetiradasEstudiantePeriodo($ci,$periodo) {
	$sql = "SELECT count(DISTINCT pk_recordacademico) from tbl_inscripciones i
		JOIN tbl_usuariosgrupos ug on ug.pk_usuariogrupo = i.fk_usuariogrupo and ug.fk_usuario = {$ci} AND i.fk_periodo = {$periodo}
		join tbl_recordsacademicos rec on rec.fk_inscripcion = i.pk_inscripcion AND rec.fk_atributo = 863";
	return $this->_db->fetchOne($sql);

}


public function getCountEvaluacionesFaltantes($data, $estados, $rows) {
	if( !isset($estados) ) return;
	$fkatributos = implode(',', $estados);
	$request=array();
	foreach ($rows as $row) {
		$request[] = "'{$row['fk_evaluacion']}_{$row['fk_recordacademico']}'";
	}
	$evaluaciones = implode(',',$request);
	$sql= "
		SELECT count(re.pk_regimen_evaluacion || '_' || ra.pk_recordacademico) as faltantes
		from tbl_asignaturas ag
		JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
		CROSS JOIN tbl_regimenes_evaluaciones re
		JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = agr.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
		JOIN tbl_atributos a1 ON a1.pk_atributo = re.fk_tipo_evaluacion
		JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
		--LEFT SI LA ASIGNACION NO ES OBLIGATORIA
		JOIN tbl_asignaciones ac ON ac.fk_asignatura = ag.pk_asignatura
		JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
		JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
		JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
		JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  ac.fk_usuariogrupo
		JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
		--NOTAS
		JOIN tbl_recordsacademicos ra ON ra.fk_asignatura = ag.pk_asignatura AND ra.fk_asignacion = ac.pk_asignacion
		JOIN tbl_atributos a2 ON a2.pk_atributo = ra.fk_atributo
		JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion AND i.fk_periodo = ac.fk_periodo
		JOIN tbl_usuariosgrupos ug1 ON ug1.pk_usuariogrupo = i.fk_usuariogrupo
		JOIN tbl_usuarios u1 ON u1.pk_usuario = ug1.fk_usuario
		LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
		WHERE ra.fk_atributo IN ({$fkatributos})
		AND e3.pk_estructura = {$data['sede']}
		AND  p.fk_escuela    = {$data['escuela']}
		AND  p.pk_pensum     = {$data['pensum']}
		AND i.fk_periodo = {$data['periodo']}
		AND  {$data['periodo']} >= rh.fk_periodo_inicio AND ( {$data['periodo']} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)
		AND ac.fk_semestre   = {$data['semestre']}
		AND ag.fk_materia = {$data['materia']}
		AND ac.fk_seccion   = {$data['seccion']}
		AND re.pk_regimen_evaluacion || '_' || ra.pk_recordacademico NOT IN ({$evaluaciones})
";
	return $this->_db->fetchOne($sql);
}
        public function getInscritosEstado($Data) {
            if(!is_array($Data)) return;

            $SQL = "SELECT DISTINCT COUNT(ac.fk_estado) = 0
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
            JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
            JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            WHERE ra.fk_atributo   IN (864,862,1699)
            AND ac.fk_periodo    = {$Data['periodo']}
            AND e3.pk_estructura = {$Data['sede']}
            AND  p.fk_escuela    = {$Data['escuela']}
            AND ac.fk_semestre   = {$Data['semestre']}
            AND ag.fk_materia    = {$Data['materia']}
            AND ac.fk_seccion    = {$Data['seccion']}
            AND p.pk_pensum      = {$Data['pensum']}
            AND ac.fk_estado in (1255, 1256)";
            return $this->_db->fetchOne($SQL);
        }


       public function getCuadroHonor($periodo, $escuela, $sede) {

        // Cuadro honor.
            $SQL = "SELECT DISTINCT u.pk_usuario,
                                         u.apellido,
                                         u.nombre,
                                         (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) AS iap,
                                         (fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo))AS iaa
                              FROM tbl_usuarios u
                              JOIN tbl_usuariosgrupos    ug  ON u.pk_usuario       = ug.fk_usuario
                              JOIN tbl_inscripciones     i   ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                              JOIN tbl_recordsacademicos ra  ON i.pk_inscripcion   = ra.fk_inscripcion
                             WHERE i.fk_estructura  = {$escuela}
                               AND i.fk_periodo     = {$periodo}
                               AND i.fk_atributo    = {$sede} 
                               AND (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) >=16 
                            ORDER BY 5 DESC;";


                            $results = $this->_db->query($SQL);
                            return (array)$results->fetchAll();   


            }

        public function getRegulares($periodo, $sede,$escuela){

        // Regular
            $SQL = "SELECT DISTINCT u.pk_usuario,
                                         u.apellido,
                                         u.nombre,
                                        (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) AS iap,
                                        (fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo))AS iaa
                          FROM tbl_usuarios u
                          JOIN tbl_usuariosgrupos    ug  ON u.pk_usuario       = ug.fk_usuario
                          JOIN tbl_inscripciones     i   ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                          JOIN tbl_recordsacademicos ra  ON i.pk_inscripcion   = ra.fk_inscripcion
                         WHERE i.fk_estructura  = {$sede} 
                           AND i.fk_periodo     = {$periodo}
                           AND i.fk_atributo    = {$escuela}
                           AND (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) < 16
                           AND ((fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) >=11 AND
                                (fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo)) >=11)  
                        ORDER BY 5 DESC;";

                        $results = $this->_db->query($SQL);
                        return (array)$results->fetchAll();
                        
                }

        public function getPeriodoPrueba($periodo, $escuela, $sede){

        // P.I.R.A
       
            $SQL= "SELECT DISTINCT  u.pk_usuario,
                                          u.apellido,
                                          u.nombre,
                                          (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) AS iap,
                                          (fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo))AS iaa
                              FROM tbl_usuarios u
                              JOIN tbl_usuariosgrupos    ug  ON u.pk_usuario       = ug.fk_usuario
                              JOIN tbl_inscripciones     i   ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                              JOIN tbl_recordsacademicos ra  ON i.pk_inscripcion   = ra.fk_inscripcion
                             WHERE i.fk_estructura  = {$escuela}
                               AND i.fk_periodo     = {$periodo}
                               AND i.fk_atributo    = {$sede}  
                               AND (fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) >= 0
                               AND ((fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo)) <11 
                               AND (fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo)) < 11)   
                            ORDER BY 5 DESC;";
                         
                         $results = $this->_db->query($SQL);
                        return (array)$results->fetchAll();

                }

      public function getRetiroDefinitivo($periodo,$sede,$escuela){

        // Retiro definitivo.
        
            $SQL= "SELECT u.pk_usuario,
                                u.apellido,
                                u.nombre,
                                fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo) AS iap,
                                fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo) AS iaa
                          FROM tbl_inscripciones i
                          JOIN tbl_usuariosgrupos   ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                          JOIN tbl_usuarios         u  ON u.pk_usuario   = ug.fk_usuario
                          JOIN tbl_recordsacademicos    ra ON ra.fk_inscripcion  = i.pk_inscripcion
                          JOIN tbl_asignaturas      ag ON ag.pk_asignatura   = ra.fk_asignatura
                         WHERE i.fk_periodo = {$periodo}
                           AND ag.fk_materia = 1701
                           AND i.fk_estructura = {$sede}
                           AND i.fk_atributo = {$escuela}
                           AND u.pk_usuario  NOT IN (SELECT ug2.fk_usuario
                                                   FROM tbl_inscripciones i2
                                                   JOIN tbl_usuariosgrupos  ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo
                                                  WHERE i2.fk_periodo = {$periodo} +1)
                           AND fn_xrxx_estudiante_iap_sce(u.pk_usuario, i.fk_periodo, i.fk_atributo) < 11
                           AND fn_xrxx_estudiante_iia_escuela_new(u.pk_usuario, i.fk_atributo) < 11              
                        ORDER BY 5 DESC,4 DESC;";

                        $results = $this->_db->query($SQL);
                        return (array)$results->fetchAll();
    
                        
                }

        public function getPorMateriaTodos($periodo,$sede,$escuela){

            //Por Materia

           $SQL = "SELECT * FROM fn_xrxx_rendimiento_academico_por_materia2({$periodo},{$escuela}, {$sede}) AS (codigopropietario VARCHAR, 
                        semestre SMALLINT, seccion VARCHAR, pensum VARCHAR, materia VARCHAR, nombre TEXT, total INTEGER, aprobados INTEGER, 
                        aplazados INTEGER,retirados INTEGER, poraprobados NUMERIC, poraplazados NUMERIC, porretirados NUMERIC, califpro NUMERIC) 
                      ORDER BY semestre, codigopropietario;";


                 $results = $this->_db->query($SQL);
                 return (array)$results->fetchAll();
        }

        public function getPorMateriaPorcentaje($periodo,$sede,$escuela){

             // Por Materia 30%

            $SQL = "SELECT * FROM fn_xrxx_rendimiento_academico_por_materia2({$periodo},{$escuela},{$sede}) AS (codigopropietario VARCHAR, semestre SMALLINT, seccion VARCHAR,
                        pensum VARCHAR, materia VARCHAR, nombre TEXT, total INTEGER, aprobados INTEGER, aplazados INTEGER, retirados INTEGER, 
                        poraprobados NUMERIC, poraplazados NUMERIC, porretirados NUMERIC, califpro NUMERIC) 
                    WHERE poraplazados >= 30 ORDER BY semestre, codigopropietario, poraplazados DESC;";

                 $results = $this->_db->query($SQL);
                 return (array)$results->fetchAll();
        }


        public function getPorMateriaProfesorTodos($periodo,$sede,$escuela){

          // Por Materia y Profesor Todos

              $SQL = "SELECT * FROM fn_xrxx_rendimiento_academico_por_materia2({$periodo},{$escuela}, {$sede}) AS (codigopropietario VARCHAR, 
                        semestre SMALLINT, seccion VARCHAR, pensum VARCHAR, materia VARCHAR, nombre TEXT, total INTEGER, aprobados INTEGER, 
                        aplazados INTEGER,retirados INTEGER, poraprobados NUMERIC, poraplazados NUMERIC, porretirados NUMERIC, califpro NUMERIC) 
                      ORDER BY semestre, codigopropietario;";


                        $results = $this->_db->query($SQL);
                        return (array)$results->fetchAll();

        }

        public function getPorMateriaProfesorPorcentaje($periodo,$sede,$escuela){

            // Por Materia y Profesor 30%

            $SQL = "SELECT * FROM fn_xrxx_rendimiento_academico_por_materia2({$periodo},{$escuela},{$sede}) AS (codigopropietario VARCHAR, semestre SMALLINT, seccion VARCHAR,
                        pensum VARCHAR, materia VARCHAR, nombre TEXT, total INTEGER, aprobados INTEGER, aplazados INTEGER, retirados INTEGER, 
                        poraprobados NUMERIC, poraplazados NUMERIC, porretirados NUMERIC, califpro NUMERIC) 
                    WHERE poraplazados >= 30 ORDER BY semestre, codigopropietario, poraplazados DESC;";

                    $results = $this->_db->query($SQL);
                    return (array)$results->fetchAll();

        }

        public function getSemestreUbicacion($ci,$escuela,$periodo,$codpensum){
            
            $SQL="SELECT fn_xrxx_estudiante_sem_ubicacion_periodod2($ci,$escuela,$periodo,$codpensum) AS semestre;";

            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }
        public function getUnidadesDeCreditoAprobadas($ci,$pensum,$escuela,$sede){
            $SQL="  SELECT      fn_xrxx_estudiante_calcular_uc_total($ci,$pensum,$escuela,$sede)as uc;";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }

        public function materiasCursadas($ci, $pensum){
           $SQL= "SELECT         DISTINCT COUNT(pk_recordacademico) AS materias
                                 FROM tbl_recordsacademicos ra
                                 JOIN tbl_inscripciones i ON i.pk_inscripcion = ra.fk_inscripcion
                                 JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                 JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.FK_Asignatura
                                 JOIN tbl_atributos a ON a.pk_atributo = ra.fk_atributo
                                 WHERE fk_usuario = {$ci}
                                 AND ((ra.fk_atributo = 862 AND ra.calificacion>=10)
                                 OR ra.fk_atributo = 1264 
                                 OR ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
                                 AND i.fk_pensum = {$pensum};";

           $results = $this->_db->query($SQL);
           return (array)$results->fetchAll();
        }

        public function materiasPorCursar($ci, $pensum){ 
            $SQL = "SELECT  COUNT(pk_asignatura) AS materias
                            FROM tbl_asignaturas
                            WHERE fk_pensum     = {$pensum}
                            AND pk_asignatura NOT IN (SELECT DISTINCT pk_asignatura
                                                      FROM tbl_recordsacademicos ra
                                                      JOIN tbl_inscripciones i   ON i.pk_inscripcion     = ra.fk_inscripcion
                                                      JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo   = i.fk_usuariogrupo
                                                      JOIN tbl_asignaturas asi   ON asi.pk_asignatura    = ra.fk_asignatura
                                                      JOIN tbl_atributos a       ON a.pk_atributo        = ra.fk_atributo
                                                      WHERE ug.fk_usuario  = {$ci}
                                                      AND ((ra.fk_atributo = 862 AND ra.calificacion>=10)
                                                      OR ra.fk_atributo = 1264 
                                                      OR ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
                                                      AND asi.fk_pensum   = {$pensum})
                            AND fk_materia  not in (907,894, 1701);";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }
        public function getUltimoPeriodoInscrito($ci){
           $SQL = "SELECT fn_xrxx_reinscripcion_upi($ci) AS periodo;";
        
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

        }
        public function getUltimaEscuelaCursada($ci){
            $SQL = "SELECT pk_atributo, escuela FROM (SELECT DISTINCT *
                                                         FROM tbl_recordsacademicos ra
                                                         JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
                                                         JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                                                         JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
                                                         WHERE ug.fk_usuario = {$ci}
                                                         ORDER BY fk_periodo DESC LIMIT 1 ) AS SQT;";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }
        public function EstadoBeca($ci, $periodo){
            $SQL = "SELECT 
                     CASE  
                    -- CON LO INSCRITO EL ESTUDIANTE COMPLETA SU FASE DE BECADO 
                        WHEN uc_aprob_mas_inscritas = uc_carrera THEN 'COMPLETO'
                    -- SALE () ESTABA EN PRUEBA Y VOLVIO A FALLAR
                        WHEN promedio_aprobadas_periodo_anterior < 100 AND  reprobadas_cursantes > 0 THEN 'SALE'
                    -- CAE EN PRUEBA
                        WHEN promedio_aprobadas_periodo_actual >= 74 AND reprobadas_cursantes > 0  THEN 'ENTRA EN PRUEBA'
                    -- SALE SU PRIMER PERIODO
                        WHEN promedio_aprobadas_periodo_actual < 74 AND reprobadas_cursantes > 0  THEN 'SALE'
                        ELSE 'SE MANTIENE'
                        END AS estado
                     FROM (
                    SELECT      
                        (SELECT CASE WHEN total = 0 THEN 0 
                            ELSE 
                                round(100-((reprobadas*100)/total),2) 
                            END AS promedio
                        FROM (
                            SELECT (/*Reprobadas*/SELECT count(tr.pk_recordacademico)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                            WHERE tu.pk_usuario = au.pk_usuario
                            AND ti.fk_atributo = ai.fk_atributo
                            AND ti.fk_pensum = ai.fk_pensum
                            AND ti.fk_periodo = ai.fk_periodo - 1
                            AND tr.fk_atributo IN (1699,862)
                            AND tr.calificacion < 10)::DECIMAL AS reprobadas,
                            /*Total*/(SELECT count(tr.pk_recordacademico)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                            WHERE tu.pk_usuario = au.pk_usuario
                            AND ti.fk_atributo = ai.fk_atributo
                            AND ti.fk_pensum = ai.fk_pensum
                            AND tr.fk_atributo NOT IN (863)
                            AND ti.fk_periodo = ai.fk_periodo - 1)::DECIMAL AS total) as sqt4) AS promedio_aprobadas_periodo_anterior,
                        fn_xrxx_estudiante_iia_escuela_periodo_articulado(au.pk_usuario, ai.fk_atributo, ai.fk_periodo, ap.codigopropietario) as indice_acumulado,
                        (SELECT sum(cantidad) AS cantidad
                    FROM    (SELECT materia,
                            CASE WHEN totalevaluado <> 0 THEN 
                                CASE WHEN (round(sum(sqt2.calificacion))/totalevaluado) < 0.5 
                                    THEN 1 
                                    ELSE 0 
                                END 
                            END AS cantidad,
                            sum(sqt2.calificacion),
                            totalevaluado
                        FROM (SELECT ma.materia as materia, 
                                ra.pk_recordacademico,
                                a2.valor,
                                rae.calificacion as calificacion,
                                ( SELECT coalesce(sum(maximo),0) 
                                    FROM (
                                        -- Evaluaciones Completas con evaluados = estudiantes TOTAL EVALUADO
                                        SELECT fk_tipo_evaluacion, re.maximo
                                        FROM tbl_inscripciones i1
                                        JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion AND i1.fk_estructura =  i.fk_estructura AND i1.fk_periodo = i.fk_periodo
                                        JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = ma.pk_atributo
                                        JOIN tbl_pensums p1 ON p1.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
                                        JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
                                        CROSS JOIN tbl_regimenes_evaluaciones re
                                        JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
                                        JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
                                        LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
                                        WHERE  ra1.fk_atributo IN (862,864,1699) 
                                        AND re.evaluable = true 
                                        AND re.fk_tipo_evaluacion NOT IN (20045,20049) 
                                        GROUP BY fk_tipo_evaluacion, maximo
                                        HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
                                    ) AS sqt 
                                ) AS totalevaluado 
                        FROM tbl_asignaturas    ag
                        JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
                        JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
                        JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
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
                        WHERE u1.pk_usuario = au.pk_usuario
                        AND i.fk_estructura = ai.fk_estructura
                        AND i.fk_periodo = ai.fk_periodo
                        AND p.fk_escuela = ap.fk_escuela
                        AND ag.fk_pensum = ap.pk_pensum
                        AND re.fk_tipo_evaluacion NOT IN (20045,20049)
                        AND ra.fk_atributo NOT IN (863)
                        ORDER BY materia,a2.valor,ra.pk_recordacademico) AS sqt2
                        GROUP BY materia,pk_recordacademico,totalevaluado
                    ) AS sqt3 )AS reprobadas_cursantes,
                    (SELECT CASE WHEN total = 0 THEN 0 
                            ELSE 
                                round(100-((reprobadas*100)/total),2) 
                            END AS promedio
                        FROM (
                                SELECT (SELECT sum(cantidad)::DECIMAL AS reprobadas
                        FROM    (SELECT materia,
                                CASE WHEN totalevaluado <> 0 THEN 
                                    CASE WHEN (round(sum(sqt2.calificacion))/totalevaluado) < 0.5 
                                        THEN 1 
                                        ELSE 0 
                                    END 
                                END AS cantidad,
                                sum(sqt2.calificacion),
                                totalevaluado
                            FROM (SELECT ma.materia as materia, 
                                    ra.pk_recordacademico,
                                    a2.valor,
                                    rae.calificacion as calificacion,
                                    ( SELECT coalesce(sum(maximo),0) 
                                        FROM (
                                            -- Evaluaciones Completas con evaluados = estudiantes TOTAL EVALUADO
                                            SELECT fk_tipo_evaluacion, re.maximo
                                            FROM tbl_inscripciones i1
                                            JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion AND i1.fk_estructura =  i.fk_estructura AND i1.fk_periodo = i.fk_periodo
                                            JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = ma.pk_atributo
                                            JOIN tbl_pensums p1 ON p1.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
                                            JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
                                            CROSS JOIN tbl_regimenes_evaluaciones re
                                            JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
                                            JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
                                            LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
                                            WHERE  ra1.fk_atributo IN (862,864,1699) 
                                            AND re.evaluable = true 
                                            AND re.fk_tipo_evaluacion NOT IN (20045,20049) 
                                            GROUP BY fk_tipo_evaluacion, maximo
                                            HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
                                        ) AS sqt 
                                    ) AS totalevaluado 
                            FROM tbl_asignaturas    ag
                            JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
                            JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
                            JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
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
                            WHERE u1.pk_usuario = au.pk_usuario
                            AND i.fk_estructura = ai.fk_estructura
                            AND i.fk_periodo = ai.fk_periodo
                            AND p.fk_escuela = ap.fk_escuela
                            AND ag.fk_pensum = ap.pk_pensum
                            AND re.fk_tipo_evaluacion NOT IN (20045,20049)
                            AND ra.fk_atributo NOT IN (863)
                            ORDER BY materia,a2.valor,ra.pk_recordacademico) AS sqt2
                            GROUP BY materia,pk_recordacademico,totalevaluado
                        ) as sqx)::DECIMAL AS reprobadas,
                            /*Total*/(SELECT count(tr.pk_recordacademico)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                            WHERE tu.pk_usuario = au.pk_usuario
                            AND ti.fk_atributo = ai.fk_atributo
                            AND ti.fk_pensum = ai.fk_pensum
                            AND tr.fk_atributo NOT IN (863)
                            AND ti.fk_periodo = ai.fk_periodo)::DECIMAL AS total) as sqt4) AS promedio_aprobadas_periodo_actual,
                            
                        (SELECT  sum(unidadcredito)
                        FROM tbl_recordsacademicos ra4
                        JOIN tbl_inscripciones i4 on i4.pk_inscripcion = ra4.fk_inscripcion
                        JOIN tbl_usuariosgrupos ug4 ON ug4.pk_usuariogrupo = i4.fk_usuariogrupo
                        JOIN tbl_asignaturas a4 ON a4.pk_asignatura = ra4.fk_asignatura
                        WHERE fk_usuario = au.pk_usuario
                        AND (ra4.calificacion >= 10
                        AND ra4.fk_atributo NOT IN (863,1699)
                        AND a4.fk_pensum = ap.pk_pensum) -- UC APROBADAS
                        OR (ra4.pk_recordacademico IN ( -- + LAS INSCRITAS ESTE PERIODO
                            SELECT ra5.pk_recordacademico
                            FROM tbl_recordsacademicos ra5
                            JOIN tbl_inscripciones i5 on i5.pk_inscripcion = ra5.fk_inscripcion
                            WHERE i5.fk_periodo = ai.fk_periodo
                            AND i5.fk_usuariogrupo = i4.fk_usuariogrupo)
                            AND fk_usuario = au.pk_usuario AND a4.fk_pensum = ap.pk_pensum)) AS uc_aprob_mas_inscritas,
                        (SELECT sum(ta.unidadcredito)
                        FROM tbl_asignaturas ta 
                        JOIN tbl_pensums tp ON ta.fk_pensum = tp.pk_pensum
                        WHERE tp.fk_escuela = ai.fk_atributo
                        AND tp.pk_pensum = ap.pk_pensum) AS uc_carrera
                        
                        
                    FROM tbl_usuarios au 
                    JOIN tbl_usuariosgrupos aug ON aug.fk_usuario = au.pk_usuario 
                    JOIN tbl_inscripciones ai ON ai.fk_usuariogrupo = aug.pk_usuariogrupo
                    JOIN tbl_pensums ap ON ap.pk_pensum = ai.fk_pensum
                    JOIN vw_escuelas ae ON ai.fk_atributo = ae.pk_atributo 
                    JOIN vw_sedes vs ON vs.pk_estructura = ai.fk_estructura
                    JOIN vw_semestres vsem ON ai.fk_semestre = vsem.pk_atributo
                    WHERE ai.fk_periodo = {$periodo}
                    AND  pk_usuario = {$ci} ) as sqt_final;";

            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();

        }

        public function getIndiceRepitenciaporMateria($periodo,$escuela,$sede){
             $SQL = "SELECT sqt.materia,sqt.semestre,sqt.repitientes,sqt.inscritos,((sqt.repitientes*100)/sqt.inscritos)::float as porcentajeRepitientes
                        from 
                        (select internalSQT.materia, internalSQT.semestre,(select count(distinct ins.fk_usuariogrupo) 
                                    from tbl_inscripciones ins 
                                    join tbl_recordsacademicos ra on ins.pk_inscripcion = ra.fk_inscripcion 
                                    where ins.fk_periodo = internalSQT.periodo
                                    and ins.fk_atributo = internalSQT.escuela
                                    and ins.fk_estructura = internalSQT.sede
                                    and ins.fk_pensum = internalSQT.pensum
                                    and ra.fk_atributo = any ('{862,864}'::INT[])  --Valido que la materia este cursada y no fue retirada ACOMODAR
                                    and ra.fk_asignatura = internalSQT.pk_asignatura
                                    and ins.fk_usuariogrupo in (select distinct on (ins2.fk_usuariogrupo) ins2.fk_usuariogrupo--Busca el fk_usuariogrupo para saber si el usuario inscribio la materia y la reprobo en periodos anteriores
                                                    from tbl_inscripciones ins2
                                                    join tbl_recordsacademicos ra2 on ins2.pk_inscripcion = ra2.fk_inscripcion
                                                    where ins2.fk_periodo < ins.fk_periodo
                                                    and ins2.fk_atributo = ins.fk_atributo
                                                    and ins2.fk_estructura = ins.fk_estructura
                                                    and ins2.fk_pensum =  ins.fk_pensum
                                                    and ra2.fk_atributo = any ('{862,863}'::int[])
                                                    and ra2.calificacion < 10
                                                    and ra2.fk_asignatura = ra.fk_asignatura
                                                                             )
                                               ) as Repitientes,
                                               (select count (ins.fk_usuariogrupo)
                                        from tbl_recordsacademicos ra
                                        join tbl_inscripciones ins on ra.fk_inscripcion = ins.pk_inscripcion
                                        where ra.fk_asignatura = internalSQT.pk_asignatura
                                        and ins.fk_periodo = internalSQT.periodo
                                        and ins.fk_estructura = internalSQT.sede
                                       ) as Inscritos
                                    

                        from (select distinct 
                            asgt.pk_asignatura,
                            vwMat.materia as materia,
                            ins.fk_periodo as periodo,
                            ins.fk_estructura as sede,
                            ins.fk_atributo as escuela,
                            ins.fk_pensum as pensum,
                            vwSem.id as semestre
                            from tbl_inscripciones ins 
                            join tbl_recordsacademicos ra on ins.pk_inscripcion = ra.fk_inscripcion
                            join tbl_asignaciones asg on ra.fk_asignacion = asg.pk_asignacion 
                            join tbl_asignaturas asgt on asg.fk_asignatura = asgt.pk_asignatura
                            join vw_materias vwMat on asgt.fk_materia = vwMat.pk_atributo
                            join vw_semestres vwSem on asgt.fk_semestre = vwSem.pk_atributo
                            where ins.fk_periodo = {$periodo}
                            and ins.fk_estructura = {$sede}
                            and ins.fk_atributo = {$escuela}
                            and ins.fk_pensum = ANY ('{20,21,22,23,24,25}'::INT[])
                              ) as internalSQT
                        ) as sqt
                        order by sqt.semestre";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }
        public function getIndiceRepitenciaEscuela ($periodo,$sede,$escuela){
            $SQL="SELECT sqt.escuela, sqt.insc,sqt.rep,((sqt.rep*100)/sqt.insc)::FLOAT as porcentajeRepitientes
                        from 
                        (SELECT e.escuela,(SELECT count(DISTINCT i1.fk_usuariogrupo)
                            FROM tbl_recordsacademicos ra1
                            JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra1.fk_inscripcion
                            WHERE i1.fk_periodo = {$periodo}
                            AND i1.fk_atributo =  e.pk_atributo
                            AND i1.fk_estructura = {$sede}) as insc,
                            (SELECT count(DISTINCT fk_usuariogrupo) as rep
                            FROM tbl_inscripciones i2
                            JOIN tbl_recordsacademicos ra2 ON ra2.fk_inscripcion = i2.pk_inscripcion
                            JOIN tbl_asignaturas a2 ON a2.pk_asignatura = ra2.fk_asignatura
                            JOIN vw_materias mat2 ON mat2.pk_atributo = a2.fk_materia
                            JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo 
                            JOIN tbl_pensums p2 ON p2.pk_pensum = i2.fk_pensum
                            WHERE  i2.fk_periodo = {$periodo}
                            AND i2.fk_estructura = {$sede}
                            AND p2.codigopropietario = 8
                            AND i2.fk_atributo = e.pk_atributo
                            AND i2.fk_usuariogrupo IN (
                                SELECT DISTINCT i3.fk_usuariogrupo
                                FROM tbl_inscripciones i3
                                JOIN tbl_recordsacademicos ra3 ON ra3.fk_inscripcion = i3.pk_inscripcion
                                WHERE i3.fk_usuariogrupo =i2.fk_usuariogrupo
                                AND (ra3.calificacion < 10 OR ra3.fk_atributo = 1699 )
                                AND ra3.fk_atributo <> 864
                                AND i3.fk_periodo < i2.fk_periodo
                                AND ra3.fk_asignatura IN (ra2.fk_asignatura)
                                )) as rep
                        FROM vw_escuelas e
                        WHERE e.pk_atributo = {$escuela}
                        ) as sqt  ";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();

        }
        public function getIndiceRepitenciaUniversidad ($periodo,$sede){
            $SQL="SELECT finalsqt.totalInscritos,finalsqt.repitientes,round(((finalsqt.repitientes*100)/finalsqt.totalInscritos)::FLOAT) as porcentajeRepitientes
                    from 
                    (select sum(extsqt.insc) as totalInscritos,sum(extsqt.rep) as repitientes
                    from
                    (SELECT sqt.escuela, sqt.insc,sqt.rep,((sqt.rep*100)/sqt.insc)::FLOAT as porcentajeRepitientes
                                            from 
                                            (SELECT e.escuela,(SELECT count(DISTINCT i1.fk_usuariogrupo)
                                                FROM tbl_recordsacademicos ra1
                                                JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra1.fk_inscripcion
                                                WHERE i1.fk_periodo = ($periodo)
                                                AND i1.fk_atributo =  e.pk_atributo
                                                AND i1.fk_estructura ={$sede}) as insc,
                                                (SELECT count(DISTINCT fk_usuariogrupo) as rep
                                                FROM tbl_inscripciones i2
                                                JOIN tbl_recordsacademicos ra2 ON ra2.fk_inscripcion = i2.pk_inscripcion
                                                JOIN tbl_asignaturas a2 ON a2.pk_asignatura = ra2.fk_asignatura
                                                JOIN vw_materias mat2 ON mat2.pk_atributo = a2.fk_materia
                                                JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo 
                                                JOIN tbl_pensums p2 ON p2.pk_pensum = i2.fk_pensum
                                                WHERE  i2.fk_periodo = {$periodo}
                                                AND i2.fk_estructura = {$sede}
                                                AND p2.codigopropietario = 8
                                                AND i2.fk_atributo = e.pk_atributo
                                                AND i2.fk_usuariogrupo IN (
                                                    SELECT DISTINCT i3.fk_usuariogrupo
                                                    FROM tbl_inscripciones i3
                                                    JOIN tbl_recordsacademicos ra3 ON ra3.fk_inscripcion = i3.pk_inscripcion
                                                    WHERE i3.fk_usuariogrupo =i2.fk_usuariogrupo
                                                    AND (ra3.calificacion < 10 OR ra3.fk_atributo = 1699 )
                                                    AND ra3.fk_atributo <> 864
                                                    AND i3.fk_periodo < i2.fk_periodo
                                                    AND ra3.fk_asignatura IN (ra2.fk_asignatura)
                                                    )) as rep
                                            FROM vw_escuelas e
                                            WHERE e.pk_atributo in (11,12,13,14,15,16)
                                            ) as sqt 
                    ) as extsqt
                    )as finalsqt";
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }
        public function getCursoSimultaneo ($periodo,$sede,$escuela){
            if ($escuela == '0')
                $escuela = "IN (11,12,13,14,15,16)";
            else 
                $escuela = "='$escuela'";
            $SQL ="SELECT us.pk_usuario AS cedula,
                                us.apellido,
                                us.nombre,
                                ins.ucadicionales,
                                ass.codigopropietario AS codigopropietario1,
                                vwm.materia AS materia1,
                                assp.codigopropietario AS codigopropietario2,
                                vwmp.materia AS materia2,
                                assp.unidadcredito
                        FROM tbl_prelaciones pre
                        JOIN tbl_asignaturas ass        ON ass.pk_asignatura = pre.fk_asignatura
                        JOIN vw_materias vwm            ON vwm.pk_atributo = ass.fk_materia
                        JOIN tbl_pensums pen            ON pen.pk_pensum = ass.fk_pensum
                        JOIN vw_escuelas vwe            ON vwe.pk_atributo = pen.fk_escuela
                        JOIN tbl_asignaturas assp       ON assp.pk_asignatura = pre.fk_asignaturaprelada
                        JOIN vw_materias vwmp           ON vwmp.pk_atributo = assp.fk_materia
                        JOIN tbl_recordsacademicos ra   ON ass.pk_asignatura = ra.fk_asignatura
                        JOIN tbl_inscripciones ins      ON ins.pk_inscripcion = ra.fk_inscripcion
                        JOIN tbl_usuariosgrupos usg     ON usg.pk_usuariogrupo = ins.fk_usuariogrupo
                        JOIN tbl_usuarios us            ON us.pk_usuario = usg.fk_usuario
                        WHERE ins.fk_periodo = {$periodo}
                        AND ins.fk_estructura = {$sede}
                        AND vwe.pk_atributo  {$escuela}
                        AND pre.fk_asignaturaprelada IN (SELECT sq_ass.pk_asignatura
                                                           FROM tbl_asignaturas sq_ass
                                                                JOIN tbl_recordsacademicos sq_ra    ON sq_ass.pk_asignatura = sq_ra.fk_asignatura
                                                                JOIN tbl_inscripciones sq_ins       ON sq_ins.pk_inscripcion = sq_ra.fk_inscripcion
                                                                JOIN tbl_usuariosgrupos sq_usg      ON sq_usg.pk_usuariogrupo = sq_ins.fk_usuariogrupo
                                                                JOIN tbl_usuarios sq_us             ON sq_us.pk_usuario = sq_usg.fk_usuario
                                                            WHERE sq_ins.fk_periodo = {$periodo}
                                                            AND sq_us.pk_usuario = us.pk_usuario)
                      ORDER BY vwe.escuela, us.apellido, us.nombre";
                          
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
        }

        public function getPeriodoAnteriorInscrito($ci,$periodo){

            $SQL =" SELECT distinct i.fk_periodo
                    from tbl_usuariosgrupos ug
                    join tbl_inscripciones i on ug.pk_usuariogrupo= i.fk_usuariogrupo
                    where ug.fk_usuario = {$ci}
                    and i.fk_periodo < {$periodo}
                    order by 1 desc
                    limit 1";
                    
            return $this->_db->fetchOne($SQL);

        }

        public function getRendimientoporsemestre($periodo,$sede,$escuela){

          $SQL = "SELECT *FROM fn_xrxx_rendimiento_academico ({$periodo}, {$escuela}, ($sede)) AS
                         (detalle TEXT , inscritas INTEGER, aprobadas INTEGER, aplazadas INTEGER,retirados INTEGER, apro NUMERIC, apla NUMERIC, ret NUMERIC, califProm NUMERIC);";

          $results = $this->_db->query($SQL);
          return $results->fetchAll();
        }

        public function getIndicerepitencia($periodo,$sede,$escuela){
            
            $SQL = "SELECT *
                    from fn_xrxx_reportes_secretaria_repitencia({$periodo}, {$escuela}, {$sede}) as
                     (escuela varchar, materia varchar, ubic varchar, repitientes numeric, inscritos numeric, porcentaje numeric, porct smallint)
                    ORDER BY  7 asc, 6 desc;";

            $results = $this->_db->query($SQL);
            return $results->fetchAll();

        }

          public function getEstudiantesPorSeccion($periodo, $sede, $escuela, $pensum,$semestre,$materia,$turno,$seccion){

            $SQL = "SELECT DISTINCT ra.pk_recordacademico
                    , u.pk_usuario as ci
                    , u.apellido
                    , u.nombre
                    ,ag.codigopropietario
                    ,sm.id as semestre
                    , m.materia
                    , s.valor as seccion
                    ,ag.unidadcredito
                    ,ra.calificacion
                    ,me.valor as estado
                    ,ag.fk_materia
                    ,a.pk_asignacion
                    FROM tbl_recordsacademicos ra
                    JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
                    JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
                    JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
                    JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                    JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
                    JOIN vw_materias            m ON  m.pk_atributo      = ag.fk_materia
                    JOIN vw_materiasestados    me ON me.pk_atributo      = ra.fk_atributo
                    LEFT JOIN tbl_asignaciones  a ON  a.pk_asignacion    = ra.fk_asignacion
                    LEFT JOIN tbl_estructuras est1 ON est1.pk_estructura = a.fk_estructura
                    LEFT JOIN tbl_estructuras est2 ON est2.pk_estructura = est1.fk_estructura AND est2.pk_estructura = 7
                    LEFT JOIN vw_secciones      s ON  s.pk_atributo      =  a.fk_seccion
                    LEFT JOIN vw_semestres     sm ON sm.pk_atributo      = ag.fk_semestre
                    WHERE i.fk_periodo  = {$periodo}
                    AND i.fk_estructura = {$sede}
                    AND i.fk_atributo   = {$escuela}
                    AND i.fk_pensum     = {$pensum}
                    and ag.fk_semestre  = {$semestre}
                    and ag.fk_materia   = {$materia}
                    and a.fk_turno      = {$turno}
                    and a.fk_seccion    = {$seccion}
                    ORDER BY ci;";

            return $this->_db->fetchAll($SQL); 

          }

        public function getAllSecciones($periodo, $materia, $pensum, $sede, $seccion){

            if ($seccion != NULL) {
                $line = 'AND fk_seccion = '.$seccion;
            }else{
                $line = '';
            }

            $SQL =" SELECT max(asna2.pk_asignacion) as pk_asignacion, asna2.fk_seccion, valor 
                    FROM (
                         SELECT  DISTINCT  fk_seccion, s.valor, asna.pk_asignacion FROM tbl_asignaciones asna
                                    join tbl_asignaturas asi on asi.pk_asignatura = asna.fk_asignatura
                                    join vw_secciones s on s.pk_atributo = asna.fk_seccion
                                    join vw_estructuras e on e.pk_aula = asna.fk_estructura
                                    where asna.fk_periodo = {$periodo} 
                                    and asi.fk_materia = {$materia}
                                    and fk_pensum = {$pensum} 
                                    and pk_sede = {$sede}
                                    {$line}
                                    order by s.valor
                         ) as sqt 
                    join tbl_asignaciones asna2 on asna2.pk_asignacion = sqt.pk_asignacion
                    group by sqt.valor, asna2.fk_seccion
                    ORDER BY sqt.valor;";
                    //var_dump($SQL);die;
            return $this->_db->fetchAll($SQL); 
        }

        public function updateSeccion($pk_recordacademico, $fk_asignacion){

            $SQL = "UPDATE tbl_recordsacademicos 
                    SET fk_asignacion = {$fk_asignacion}
                    WHERE pk_recordacademico = {$pk_recordacademico}
                    RETURNING pk_recordacademico;";
            $result = $this->_db->fetchAll($SQL);

            return $result;
            
        }

}


