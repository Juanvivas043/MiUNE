<?php

class Models_DbTable_Articulaciones extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_articulaciones';
    protected $_primary  = 'pk_articulacion';

    public function getPlanArticulacion($sede,$escuela){

        $SQL = "SELECT sqt2.codigopropietario,
                                sqt2.materia,
                                sqt2.uc,
                            sqt2.cod_1997,
                            sqt2.materia_1997,
                            sqt2.uc_1997,
                            sqt2.escuela
                          FROM
                        (SELECT sqt.codigopropietario,
                                sqt.materia,
                                sqt.uc,
                                sqt.codopsu,
                                sqt.ht,
                                sqt.hp,
                                sqt.mat2012,
                                agn.codigopropietario     AS cod_1997,
                                mat.materia             AS materia_1997,
                                agn.unidadcredito     AS uc_1997,
                                agn.codigoopsu            AS codopsu_1997,
                                agn.horasteoricas         AS ht_1997,
                                agn.horaspracticas        AS hp_1997,
                                agn.pk_asignatura         AS mat1997,
                                sqt.escuela
                           FROM
                            (SELECT distinct ags.codigopropietario,
                                    ma.materia,
                                    ags.unidadcredito  AS uc,
                                    ags.codigoopsu     AS codopsu,
                                    ags.horasteoricas  AS ht,
                                    ags.horaspracticas AS hp,
                                    ags.pk_asignatura  AS mat2012,
                                    es.escuela
                               FROM tbl_asignaturas ags
                               JOIN tbl_pensums     pe ON pe.pk_pensum      = ags.fk_pensum
                               JOIN vw_materias     ma  ON  ma.pk_atributo     = ags.fk_materia
                               JOIN vw_escuelas     es  ON  es.pk_atributo    = pe.fk_escuela
                               JOIN tbl_asignaciones asi ON ags.pk_asignatura = asi.fk_asignatura
                               JOIN vw_estructuras est ON est.pk_aula  = asi.fk_estructura
                                WHERE pe.fk_escuela = {$escuela} and est.pk_Sede = {$sede}
                               ORDER BY 1) as sqt
                          JOIN tbl_articulaciones ar ON ar.fk_asignaturanueva = sqt.mat2012
                          JOIN tbl_asignaturas   agn ON agn.pk_asignatura     = ar.fk_asignaturavieja
                          JOIN vw_materias       mat ON mat.pk_atributo       = agn.fk_materia
                          ORDER BY 1
                        ) as sqt2
                         UNION (
                        SELECT ags.codigopropietario,
                               mags.materia,
                               ags.unidadcredito,
                               '' as cod_1997,
                               '' as materia_1997,
                               0  as uc_1997,
                               es.escuela
                            
                         FROM tbl_asignaturas ags
                         JOIN vw_materias mags ON mags.pk_atributo = ags.fk_materia
                         JOIN tbl_pensums  pe  ON pe.pk_pensum  = ags.fk_pensum
                         JOIN vw_escuelas     es  ON  es.pk_atributo    = pe.fk_escuela
                         JOIN tbl_asignaciones asi ON ags.pk_asignatura = asi.fk_asignatura
                         JOIN vw_estructuras est ON est.pk_aula  = asi.fk_estructura
                          WHERE pe.fk_escuela = {$escuela} and est.pk_Sede = {$sede}
                          AND ags.pk_asignatura NOT IN (SELECT fk_asignaturanueva FROM tbl_articulaciones)
                          AND pe.nombre = '2012'
                          ORDER BY codigopropietario)
                          UNION (
                          SELECT 
                               '' as cod_2012,
                               '' as materia_2012,
                               0  as uc_2012,
                               ags.codigopropietario,
                               mags.materia,
                               ags.unidadcredito,
                               es.escuela
                         FROM tbl_asignaturas ags
                         JOIN vw_materias mags ON mags.pk_atributo = ags.fk_materia
                         JOIN tbl_pensums  pe  ON pe.pk_pensum  = ags.fk_pensum
                         JOIN vw_escuelas     es  ON  es.pk_atributo    = pe.fk_escuela
                         JOIN tbl_asignaciones asi ON ags.pk_asignatura = asi.fk_asignatura
                         JOIN vw_estructuras est ON est.pk_aula  = asi.fk_estructura
                          WHERE pe.fk_escuela = {$escuela} and est.pk_Sede = {$sede}
                         AND ags.pk_asignatura NOT IN (SELECT fk_asignaturavieja FROM tbl_articulaciones)
                         AND pe.nombre = '1997'
                         AND mags.pk_atributo NOT IN (894,   --biblioteca
                                                        907,   --preparaduria
                                                        1701)) --PIRA
                          ORDER BY 2";
//var_dump($SQL);die;
    $results = $this->_db->query($SQL);
    $return = $results->fetchAll();

    return $return;
    }

}

?>
