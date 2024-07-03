
<?php
class Models_DbTable_CalificacionesParciales extends Zend_Db_Table {

    private $lapsos = 98;
    private $inasistencias = array (20045,20049);
    private $cursando = array(862,864,1699);
    private $retirado = 863;

    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function getLapsosAcademicos () {

       $SQL = "select distinct a.pk_atributo as value, a.valor as label
              from tbl_atributostipos     at
              join tbl_atributos        a       on    a.fk_atributotipo = at.pk_atributotipo
              where pk_atributotipo = {$this->lapsos} ORDER BY 2;";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }

    public function getDinamicList ($data, $evaluaciones, $maxInasistencias) {
        if (!isset($maxInasistencias)) $maxInasistencias = 100;
        $evalu = "";
        $cross = "";
        foreach ($evaluaciones as $evaluacion ) {
           $evalu .= "\"{$evaluacion['abrev']}\",";
           $cross .= ",\"{$evaluacion['abrev']}\" FLOAT";
        }
        $cuenta = count($evaluaciones);
        $sql = "
            SELECT u.pk_usuario as ci,UPPER(u.apellido) as apellido,UPPER(u.nombre) as nombre,ra.fk_atributo as estado,{$evalu}
            (SELECT COALESCE(ROUND(SUM(rae.calificacion)::DECIMAL,0),0)
        FROM tbl_recordsacademicos ra1
        JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND ra1.pk_recordacademico = ra.pk_recordacademico
        JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion ANd re.evaluable = false) as tinasist,(SELECT COALESCE(ROUND(SUM(rae.calificacion)::decimal,2),0)
            FROM tbl_recordsacademicos ra1
            JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND ra1.pk_recordacademico = ra.pk_recordacademico
            JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion ANd re.evaluable = TRUE
        )as  total,

        CASE (ra.fk_atributo NOT IN (863)) WHEN (SELECT COALESCE(ROUND(SUM(rae.calificacion)::DECIMAL,0),0)
            FROM tbl_recordsacademicos ra1
                JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND ra1.pk_recordacademico = ra.pk_recordacademico
                JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion ANd re.evaluable = false) >= {$maxInasistencias} then 0 ELSE

                (SELECT COALESCE(ROUND(SUM(rae.calificacion)::DECIMAL,0),0)
                FROM tbl_recordsacademicos ra1
                JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND ra1.pk_recordacademico = ra.pk_recordacademico
                JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion ANd re.evaluable = TRUE
            ) END as  final
    FROM tbl_inscripciones i
    JOIN tbl_recordsacademicos ra ON i.pk_inscripcion = ra.fk_inscripcion and i.fk_periodo = {$data['periodo']}
    JOIN  CROSSTAB ('SELECT pk_recordacademico, re.ordinal, ROUND(rae.calificacion::decimal,2)
    FROM tbl_inscripciones i
    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion
    JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
    JOIN tbl_pensums p ON p.pk_pensum = a.fk_pensum
    JOIN tbl_asignaciones ac ON ra.fk_asignacion = ac.pk_asignacion
    JOIN tbl_asignaturas_regimenes ar ON ar.fk_asignatura = a.pk_asignatura
    CROSS JOIN tbl_regimenes_evaluaciones re
    JOIN tbl_regimenes_historicos rh ON rh.pk_regimen_historico = ar.fk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico
    LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
    WHERE p.fk_escuela = {$data['escuela']}
    AND p.pk_pensum = {$data['pensum']}
    AND i.fk_periodo = {$data['periodo']}
    AND a.fk_materia = {$data['materia']}
    AND i.fk_estructura = {$data['sede']}
    AND ac.fk_seccion = {$data['seccion']}
    ORDER BY 1,re.ordinal', 'SELECT DISTINCT ordinal FROM tbl_regimenes_evaluaciones re ORDER BY 1 LIMIT {$cuenta}')
    as rae (pk_recordacademico int{$cross})
    ON ra.pk_recordacademico = rae.pk_recordacademico
    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
    JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
    JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
    JOIN vw_materias m ON m.pk_atributo = a.fk_materia
    GROUP BY u.pk_usuario,u.apellido,u.nombre,ra.pk_recordacademico,ra.fk_atributo,{$evalu} m.materia
    ORDER BY u.apellido,u.nombre
        ";
        //print_r($sql);die;
        $results = $this->_db->query($sql);
        return  $results->fetchAll();
    }

    public function getDesempenoAcademico ($periodo, $sede, $escuela ,$lapso = NULL, $semestre, $pensum, $estado =NULL) {
        if (empty($periodo)) return;
        if (empty($sede)) return;
        if (empty($escuela)) return;
        $estados = implode(',', $this->cursando);
        $inasistencias = implode(',', $this->inasistencias);
        $filtroLapso = isset($lapso) ?"AND re.fk_lapso = {$lapso}":'';

        $sql = "
SELECT  sqt.id,
sqt.unidadc,
sqt.profesor,
sqt.seccion,
sqt.aplazadoinasist,
sqt.totalevaluado,
sqt.inscritos,
sqt.retirados,
(sqt.inscritos - sqt.retirados) as inscritosneto,
(sqt.inscritos -sqt.retirados - sqt.aplazadoinasist) as asisten,
sqt.cantidadaprobados,
COALESCE(CASE WHEN (sqt.inscritos- sqt.retirados - sqt.aplazadoinasist) > 0 THEN ROUND(100*sqt.cantidadaprobados/(sqt.inscritos - sqt.retirados - sqt.aplazadoinasist)::NUMERIC,2) END,0.00)::NUMERIC as porcentajeaprobado,
sqt.cantidadreprobados,
COALESCE(CASE WHEN (sqt.inscritos- sqt.retirados - sqt.aplazadoinasist) > 0 THEN ROUND(100*sqt.cantidadreprobados/(sqt.inscritos - sqt.retirados -sqt.aplazadoinasist)::NUMERIC,2) END,0.00)::NUMERIC as porcentajereprobado,
sqt.promedio,
sqt.promedio::varchar || '/' || sqt.totalevaluado as calipromedio,
sqt.estadototal
FROM (
SELECT DISTINCT
s.id,
m.materia as unidadc,
sec.valor as seccion,
tu.apellido || ' ' || tu.nombre as profesor,
----------------------------------------------------------------------------------------------------------
(SELECT count(DISTINCT ra1.pk_recordacademico) FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
WHERE i.fk_periodo = i1.fk_periodo AND ra1.fk_atributo IN ({$estados},{$this->retirado}) AND i.fk_estructura = i1.fk_estructura AND ag.fk_pensum = {$pensum}
) as inscritos ,
---------------------------------------------------------------------------------------------------------------
(SELECT count(DISTINCT ra1.pk_recordacademico) FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre AND ag.fk_pensum = {$pensum}
WHERE i.fk_periodo = i1.fk_periodo AND ra1.fk_atributo IN ({$this->retirado}) AND i.fk_estructura = i1.fk_estructura ) as retirados,
-----------------------------------------------------------------------------------------------------
COALESCE ( (SELECT COUNT(cantidad) FROM (
-- MAS DE 25% Inasistencias
SELECT count(ra1.pk_recordacademico) as cantidad FROM tbl_recordsacademicos ra1
JOIN tbl_asignaciones a ON a.pk_asignacion = ra1.fk_asignacion
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura
JOIN tbl_estructuras es ON es.pk_estructura = a.fk_estructura
JOIN tbl_estructuras es1 ON es1.pk_estructura = es.fk_estructura
JOIN tbl_estructuras es2 ON es2.pk_estructura = es1.fk_estructura
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae On rae.fk_recordacademico = ra1.pk_recordacademico
LEFT OUTER JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
where a.fk_periodo = i1.fk_periodo AND es2.pk_estructura = i1.fk_estructura
AND p.fk_escuela = p1.fk_escuela
AND a.fk_semestre = ac.fk_semestre
AND a.fk_seccion = ac.fk_seccion
AND ag.fk_materia = m.pk_atributo
-- FILTRAR POR LAPSO
    {$filtroLapso}
AND ra1.fk_atributo IN ({$estados})
AND re.fk_tipo_evaluacion IN ({$inasistencias}) and re.evaluable = false
GROUP BY ra1.pk_recordacademico
-- Solo los que tengan Mas de 25% inasistencias
HAVING sum(rae.calificacion) > 0.25 * (
    --la suma de las clases por dia menos la suma de feriados por dia de clase
    SELECT sum(clases) FROM (
        SELECT sum(clases) - sum((SELECT count(DISTINCT descripcion)
        FROM (SELECT f.descripcion,EXTRACT(dow from f.fechainicio) as diainicio,EXTRACT(dow from f.fechafin) as diafin
        -- buscamos la cantidad de dias que coinciden con periodos feriados
        from vw_feriados f
        join tbl_periodos p ON f.fechainicio >= p.fechainicio AND f.fechainicio <= p.fechafin
        where p.pk_periodo = i1.fk_periodo

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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura =  i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura = i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
            GROUP BY pe.pk_periodo,fk_dia,pe.inicioclases,pe.fechafin  ) as sqt
            GROUP BY fk_dia) as clasesdia
        ) )as sql
    ), 0) as aplazadoinasist ,
-------------------------------------------------------------------------------------------------
( SELECT coalesce(sum(maximo),0) FROM (
-- Evaluaciones Completas con evaluados = estudiantes TOTAL EVALUADO
SELECT fk_tipo_evaluacion, re.maximo
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE  ra1.fk_atributo IN ({$estados}) AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
    {$filtroLapso}
GROUP bY fk_tipo_evaluacion, maximo
HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
) as sqt ) as totalevaluado ,
---------------------------------------------------------------------------------------------------
(SELECT coalesce(count( DISTINCT sqt.fk_recordacademico),0) FROM (
SELECT DISTINCT fk_recordacademico
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE ra1.fk_atributo IN ({$estados}) AND rae.fk_recordacademico IS NOT NULL AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
-- FILTRAR PRIMER LAPSO
    {$filtroLapso}
AND re.evaluable = true
----------------- EVALUACIONES DONDE EL DOCENTE COMPLETO TODAS LAS CALIFICACIONES
and re.fk_tipo_evaluacion IN (
SELECT fk_tipo_evaluacion
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE ra1.fk_atributo IN ({$estados}) AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
    {$filtroLapso}
GROUP bY fk_tipo_evaluacion, maximo
HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
)
GROUP bY fk_recordacademico
HAVING sum(rae.calificacion) >= (sum(maximo)/ 2)
) as sqt) as cantidadaprobados ,
----------------------------------------------------------------------------------------------------
(SELECT coalesce(count( DISTINCT sqt.fk_recordacademico),0) FROM (
SELECT DISTINCT fk_recordacademico
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
jOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE ra1.fk_atributo IN ({$estados}) AND rae.fk_recordacademico IS NOT NULL AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
-- FILTRAR PRIMER LAPSO
    {$filtroLapso}
----------------- EVALUACIONES DONDE EL DOCENTE COMPLETO TODAS LAS CALIFICACIONES
and re.fk_tipo_evaluacion IN (
SELECT fk_tipo_evaluacion
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE  ra1.fk_atributo IN ({$estados}) AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
    {$filtroLapso}
GROUP bY fk_tipo_evaluacion, maximo
HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
)
and ra1.pk_recordacademico NOT IN (

SELECT ra1.pk_recordacademico as cantidad FROM tbl_recordsacademicos ra1
JOIN tbl_asignaciones a ON a.pk_asignacion = ra1.fk_asignacion
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura
JOIN tbl_estructuras es ON es.pk_estructura = a.fk_estructura
JOIN tbl_estructuras es1 ON es1.pk_estructura = es.fk_estructura
JOIN tbl_estructuras es2 ON es2.pk_estructura = es1.fk_estructura
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae On rae.fk_recordacademico = ra1.pk_recordacademico
LEFT OUTER JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
where a.fk_periodo = i1.fk_periodo AND es2.pk_estructura = i1.fk_estructura
AND p.fk_escuela = p1.fk_escuela
AND a.fk_semestre = ac.fk_semestre
AND a.fk_seccion = ac.fk_seccion
AND ag.fk_materia = m.pk_atributo
-- FILTRAR POR LAPSO
AND ra1.fk_atributo IN ({$estados})
AND re.fk_tipo_evaluacion IN ({$inasistencias}) and re.evaluable = false
GROUP BY ra1.pk_recordacademico
-- Solo los que tengan Mas de 25% inasistencias
HAVING sum(rae.calificacion) > 0.25 * (
    --la suma de las clases por dia menos la suma de feriados por dia de clase
    SELECT sum(clases) FROM (
        SELECT sum(clases) - sum((SELECT count(DISTINCT descripcion)
        FROM (SELECT f.descripcion,EXTRACT(dow from f.fechainicio) as diainicio,EXTRACT(dow from f.fechafin) as diafin
        -- buscamos la cantidad de dias que coinciden con periodos feriados
        from vw_feriados f
        join tbl_periodos p ON f.fechainicio >= p.fechainicio AND f.fechainicio <= p.fechafin
        where p.pk_periodo = i1.fk_periodo

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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura =  i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura = i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
            GROUP BY pe.pk_periodo,fk_dia,pe.inicioclases,pe.fechafin  ) as sqt
            GROUP BY fk_dia) as clasesdia
        )
)
GROUP bY fk_recordacademico
HAVING sum(rae.calificacion) < (sum(maximo)/ 2)
) as sqt) as cantidadreprobados ,
                        ---------------------------------------------------------------------------------------------------
(SELECT ROUND(coalesce(avg(sqt.nota),0)::NUMERIC,2) FROM (
-- PROMEDIO
SELECT DISTINCT fk_recordacademico , sum(rae.calificacion) as nota
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura = i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE ra1.fk_atributo IN ({$estados}) AND rae.fk_recordacademico IS NOT NULL AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
-- FILTRAR PRIMER LAPSO
    {$filtroLapso}
and re.fk_tipo_evaluacion IN (  SELECT fk_tipo_evaluacion
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_estructura =  i1.fk_estructura AND i.fk_periodo = i1.fk_periodo
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE ra1.fk_atributo IN ({$estados}) AND re.evaluable = true AND re.fk_tipo_evaluacion NOT IN ({$inasistencias})
    {$filtrolapso}
GROUP bY fk_tipo_evaluacion, maximo
HAVING count(DISTINCT rae.fk_recordacademico) = count(DISTINCT ra1.pk_recordacademico)
)
and ra1.pk_recordacademico NOT IN (

SELECT ra1.pk_recordacademico as cantidad FROM tbl_recordsacademicos ra1
JOIN tbl_asignaciones a ON a.pk_asignacion = ra1.fk_asignacion
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura
JOIN tbl_estructuras es ON es.pk_estructura = a.fk_estructura
JOIN tbl_estructuras es1 ON es1.pk_estructura = es.fk_estructura
JOIN tbl_estructuras es2 ON es2.pk_estructura = es1.fk_estructura
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae On rae.fk_recordacademico = ra1.pk_recordacademico
LEFT OUTER JOIN tbl_regimenes_evaluaciones re ON re.pk_regimen_evaluacion = rae.fk_evaluacion
where a.fk_periodo = i1.fk_periodo AND es2.pk_estructura = i1.fk_estructura
AND p.fk_escuela = p1.fk_escuela
AND a.fk_semestre = ac.fk_semestre
AND a.fk_seccion = ac.fk_seccion
AND ag.fk_materia = m.pk_atributo
-- FILTRAR POR LAPSO
AND ra1.fk_atributo IN ({$estados})
AND re.fk_tipo_evaluacion IN ({$inasistencias}) and re.evaluable = false
GROUP BY ra1.pk_recordacademico
-- Solo los que tengan Mas de 25% inasistencias
HAVING sum(rae.calificacion) > 0.25 * (
    --la suma de las clases por dia menos la suma de feriados por dia de clase
    SELECT sum(clases) FROM (
        SELECT sum(clases) - sum((SELECT count(DISTINCT descripcion)
        FROM (SELECT f.descripcion,EXTRACT(dow from f.fechainicio) as diainicio,EXTRACT(dow from f.fechafin) as diafin
        -- buscamos la cantidad de dias que coinciden con periodos feriados
        from vw_feriados f
        join tbl_periodos p ON f.fechainicio >= p.fechainicio AND f.fechainicio <= p.fechafin
        where p.pk_periodo = i1.fk_periodo

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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura =  i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
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
            where a.fk_periodo = i1.fk_periodo
            and sede.pk_estructura = i1.fk_estructura
            AND a.fk_semestre = ac.fk_semestre
            AND asi.fk_materia = m.pk_atributo
            and a.fk_seccion = ac.fk_seccion
            GROUP BY pe.pk_periodo,fk_dia,pe.inicioclases,pe.fechafin  ) as sqt
            GROUP BY fk_dia) as clasesdia
        )
)
GROUP bY fk_recordacademico
) as sqt
) as promedio ,
------------------------------------------------------------------------------------------------------------
(SELECT CASE COALESCE(count(rae.calificacion),0) WHEN 0 THEn 'VACIO'
WHEN count(DISTINCT re.fk_tipo_evaluacion)*COUNT(DISTINCT ra1.pk_recordacademico) THEN 'COMPLETA'
ELSE 'INCOMPLETA' END
---- ESTADO
FROM tbl_inscripciones i
JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i.pk_inscripcion AND i.fk_periodo = i1.fk_periodo AND i.fk_estructura =  i1.fk_estructura
JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra1.fk_asignatura AND ag.fk_materia = m.pk_atributo
JOIN tbl_pensums p ON p.pk_pensum = ag.fk_pensum AND p.fk_escuela = p1.fk_escuela
JOIN tbl_asignaciones a ON a.fk_periodo = i1.fk_periodo AND a.fk_asignatura = ag.pk_asignatura AND ra1.fk_asignacion = a.pk_asignacion AND a.fk_seccion = ac.fk_seccion AND a.fk_semestre = ac.fk_semestre
CROSS JOIN tbl_regimenes_evaluaciones re
JOIN tbl_regimenes_historicos rh ON  re.fk_regimen_historico = rh.pk_regimen_historico
JOIN tbl_asignaturas_regimenes agr ON agr.fk_regimen_historico = rh.pk_regimen_historico AND re.fk_regimen_historico = rh.pk_regimen_historico AND ra1.fk_asignatura = agr.fk_asignatura
LEFT OUTER JOIN tbl_recordsacademicos_evaluaciones rae ON rae.fk_recordacademico = ra1.pk_recordacademico AND rae.fk_evaluacion = re.pk_regimen_evaluacion
WHERE  ra1.fk_atributo IN ({$estados}) AND ag.fk_pensum = {$pensum}
    {$filtroLapso}
) as estadototal
-----------------------------------------------------------------------------------------------------
-- QUERY PPAL DE MATERIAS CON SECCIONES ASIGNADAS
FROM tbl_asignaciones ac
JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = ac.pk_asignacion
JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra.fk_inscripcion AND i1.fk_periodo = ac.fk_periodo
JOIN tbl_asignaturas a ON ra.fk_asignatura = a.pk_asignatura
JOIN tbl_pensums p1 ON p1.pk_pensum = a.fk_pensum
JOIN vw_materias m ON m.pk_atributo = a.fk_materia
JOIN vw_semestres s ON s.pk_atributo = ac.fk_semestre
JOIN vw_secciones sec ON sec.pk_atributo = ac.fk_seccion
JOIN tbl_usuariosgrupos tg ON ac.fk_usuariogrupo = tg.pk_usuariogrupo
JOIN tbl_usuarios tu ON tg.fk_usuario = tu.pk_usuario
WHERE i1.fk_atributo = {$escuela} AND i1.fk_estructura = {$sede} AND ac.fk_periodo = {$periodo} AND ra.fk_atributo IN ({$estados},{$this->retirado})
";
if (isset($semestre) && $semestre) {
    $sql .= "AND a.fk_semestre = {$semestre} ";
}
$sql .= "AND a.fk_pensum = {$pensum}
GROUP BY ac.pk_asignacion, p1.fk_escuela, m.materia, i1.fk_estructura, m.pk_atributo, s.id, sec.valor, i1.fk_periodo, ac.fk_semestre, a.fk_materia, ac.fk_seccion,tu.pk_usuario,tu.apellido,tu.nombre
) AS sqt";
if (isset($estado) && $estado != 'Todos') $sql .= " WHERE estadototal ilike '{$estado}' ";

$sql .= " ORDER BY id,unidadc,seccion ";
        $results = $this->_db->query($sql);
        return (array) $results->fetchAll();
    }

    public function getAsignaturaRegimen($Data) {
        $sql= "SELECT DISTINCT ar.cargacontinua
            FROM tbl_recordsacademicos ra
            JOIN tbl_inscripciones      i ON  i.pk_inscripcion   = ra.fk_inscripcion
            JOIN tbl_asignaciones      ac ON ac.pk_asignacion    = ra.fk_asignacion
            JOIN tbl_asignaturas       ag ON ag.pk_asignatura    = ra.fk_asignatura
            JOIN tbl_asignaturas_regimenes ar ON ar.fk_asignatura = ag.pk_asignatura
            JOIN tbl_regimenes_historicos rh ON rh .pk_regimen_historico = ar.fk_regimen_historico
            JOIN tbl_estructuras       e1 ON e1.pk_estructura    = ac.fk_estructura
            JOIN tbl_estructuras       e2 ON e2.pk_estructura    = e1.fk_estructura
            JOIN tbl_estructuras       e3 ON e3.pk_estructura    = e2.fk_estructura
            JOIN tbl_pensums            p ON  p.pk_pensum        = ag.fk_pensum
            WHERE ra.fk_atributo   IN (864,862, 1699)
            AND ac.fk_periodo    = {$Data['periodo']}
            AND e3.pk_estructura = {$Data['sede']}
            AND  p.fk_escuela    = {$Data['escuela']}
            AND ac.fk_semestre   = {$Data['semestre']}
            AND ag.fk_materia    = {$Data['materia']}
            AND ac.fk_seccion    = {$Data['seccion']}
            AND  {$Data['periodo']} >= rh.fk_periodo_inicio AND ( {$Data['periodo']} <= rh.fk_periodo_fin OR rh.fk_periodo_fin IS NULL)";
        $results = $this->_db->query($sql);
        return (array) $results->fetchAll();
    }
}
