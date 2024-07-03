<?php

class Models_DbTable_Usuarios extends Zend_Db_Table
{

    protected $_schema = 'produccion';
    protected $_name = 'tbl_usuarios';
    protected $_primary = 'pk_usuario';
    protected $_sequence = false;
    private $searchParams = array('pk_usuario', 'nombre', 'apellido', "LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");

    public function init()
    {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function changePassword($id, $passwordNew)
    {
        $data = array(
            'passwordhash' => md5($passwordNew)
        );

        $where = $this->getAdapter()->quoteInto('pk_usuario = ?', (int)$id);

        $rows_affected = $this->update($data, $where);

        return $rows_affected;
    }
    //reset del password para el MododiosController
    public function resetPassword(){
        $SQL =  "UPDATE tbl_usuarios


                 set passwordhash = MD5 (pk_usuario::text)";
        $this->_db->query($SQL);
    }
    public function getUsuarioSede($ci){
        $SQL =  "SELECT distinct i.fk_estructura, vws.nombre
                  from tbl_inscripciones    i
                  join vw_sedes vws on i.fk_estructura = vws.pk_estructura
                  join tbl_usuariosgrupos   ug    on  ug.pk_usuariogrupo  = i.fk_usuariogrupo
                  where ug.fk_usuario = {$ci}
                  AND i.pk_inscripcion = (
                   SELECT pk_inscripcion
                   FROM (
                     SELECT pk_inscripcion,fk_periodo
                     FROM tbl_inscripciones i2
                     JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo
                     WHERE ug2.fk_usuario = ug.fk_usuario
                     ORDER BY fk_periodo DESC limit 1
                     ) as sqt
              )";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }


    public function getCambioEscuela($periodo){

      $periodoanterior = $periodo -1;
        $SQL = "SELECT distinct sqt.pk_usuario as pk_usuario,
                        sqt.nombre as nombre,
                        sqt.apellido as apellido,
                        case  when fn_xrxx_estudiante_sem_ubicacion_periodod(sqt.pk_usuario, sqt.escuela, sqt.periodo) is not null
                          then fn_xrxx_estudiante_sem_ubicacion_periodod(sqt.pk_usuario, sqt.escuela, sqt.periodo)::varchar
                          when fn_xrxx_estudiante_sem_ubicacion_periodod(sqt.pk_usuario, sqt.escuela, sqt.periodo) is null
                          then 'N/A'
                          end as sem_ubic,
                        sqt1.valor as esc1,
                        sqt.valor as esc2
                      from (select  distinct pk_usuario,
                          u.nombre,
                          u.apellido,
                          ins.fk_atributo as escuela,
                          ins.fk_periodo as periodo,
                          a.valor
                          from tbl_usuarios     u
                          join tbl_usuariosgrupos   ug  on u.pk_usuario   = ug.fk_usuario
                          join tbl_inscripciones    ins on ug.pk_usuariogrupo   = ins.fk_usuariogrupo
                          join tbl_atributos    a   on a.pk_atributo  = ins.fk_atributo
                          where ins.fk_periodo = {$periodo}
                          and ins.numeropago <> 0) as sqt
                      join (select  distinct pk_usuario,
                          u.nombre,
                          u.apellido,
                          a.valor
                          from tbl_usuarios   u
                          join tbl_usuariosgrupos ug  on u.pk_usuario   = ug.fk_usuario
                          join tbl_inscripciones  ins   on ug.pk_usuariogrupo   = ins.fk_usuariogrupo
                          join tbl_atributos  a   on a.pk_atributo  = ins.fk_atributo
                          where ins.fk_estructura = 7
                          and ins.fk_periodo = {$periodoanterior}
                          and ins.numeropago <> 0
                          ) as sqt1 on sqt.pk_usuario = sqt1.pk_usuario
                      where sqt.valor <> sqt1.valor
                      order by 3";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function materiasreprobadas($periodo, $sede, $escuela, $semestre, $materia){//$materia
        $SQL =  "SELECT ins.fk_periodo AS periodo,
                        est2.nombre AS Sede,
                        esc.escuela,
                        pem.nombre AS pensum,
                        sem.id AS semestre,
                        ma.materia,
                        asi.codigopropietario AS Codigo,
                        us.pk_usuario,
                        us.nombre as nombre,
                        us.apellido as apellido,
                        upper(us.correo) as correo,
                        asi.fk_materia
                    FROM tbl_inscripciones ins

                      JOIN tbl_usuariosgrupos    ug   ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                      JOIN tbl_usuarios          us   ON us.pk_usuario = ug.fk_usuario
                      JOIN tbl_recordsacademicos ra   ON ra.fk_inscripcion = ins.pk_inscripcion
                      JOIN tbl_asignaciones      asg  ON asg.pk_asignacion = ra.fk_asignacion
                      JOIN tbl_asignaturas       asi  ON asi.pk_asignatura = asg.fk_asignatura
                      JOIN vw_materias           ma   ON ma.pk_atributo = asi.fk_materia
                      JOIN vw_semestres          sem  ON sem.pk_atributo = asi.fk_semestre
                      JOIN tbl_pensums           pem  ON pem.pk_pensum  = asi.fk_pensum
                      JOIN vw_escuelas           esc  ON esc.pk_atributo = pem.fk_escuela
                      JOIN tbl_estructuras       est  ON est.pk_estructura = asg.fk_estructura
                      JOIN tbl_estructuras       est1 ON est1.pk_estructura = est.fk_estructura
                      JOIN tbl_estructuras       est2 ON est2.pk_estructura = est1.fk_estructura

                    WHERE
                        ra.fk_atributo = 862            AND
                        ins.fk_periodo  =  $periodo     AND
                        est2.pk_estructura = $sede      AND
                        esc.pk_atributo in ($escuela)   AND
                        asg.fk_semestre = $semestre     AND
                        asi.pk_asignatura = $materia    AND
                        ra.calificacion < 10

                        ORDER BY ug.fk_usuario";

        try{
            $results = $this->_db->query($SQL);
            return $results->fetchAll();
        } catch (Exception $e){
            return array();
        }


    }

    public function getinscritosescuela($periodo,$sede,$escuela,$pensum){
        $SQL =  "SELECT DISTINCT u.pk_usuario,
                                 u.apellido,
                                 u.nombre,
                                 upper(u.correo) as correo
                            FROM tbl_usuarios u
                                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                JOIN tbl_pensums    pe  ON pe.pk_pensum = ins.fk_pensum
                            WHERE ins.fk_periodo = $periodo  AND
                                   ins.fk_estructura = $sede  AND
                                   ins.fk_atributo in ($escuela) AND
                                   pe.nombre = '{$pensum}'
                            ORDER BY u.apellido, u.nombre, u.pk_usuario";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getinscritosperiodos($periodo,$sede){
        $SQL =  "SELECT DISTINCT u.pk_usuario,
                                 u.apellido,
                                 u.nombre,
                                 upper(u.correo) as correo
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE ins.fk_periodo = $periodo
                    AND   ins.fk_estructura = $sede
                    ORDER BY u.apellido, u.nombre, u.pk_usuario";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getnuevosingresos($periodo, $sede, $escuela){
        $SQL =  "SELECT distinct u.pk_usuario,
                                u.apellido,
                                u.nombre,
                                upper(u.correo) as correo,
                                e.escuela,
                                i.fk_periodo,
                                es.pk_estructura
                        from tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug on ug.fk_usuario = u.pk_usuario
                        JOIN tbl_inscripciones i on i.fk_usuariogrupo = ug.pk_usuariogrupo
                        JOIN tbl_estructuras es ON i.fk_estructura = es.pk_estructura
                        JOIN vw_escuelas e on i.fk_atributo = e.pk_atributo
                        JOIN tbl_atributos atr on i.fk_semestre = atr.pk_atributo
                        where u.pk_usuario not in (select distinct ug1.fk_usuario
                                                    from tbl_recordsacademicos ra
                                                    JOIN tbl_inscripciones i1 on ra.fk_inscripcion = i1.pk_inscripcion
                                                    JOIN tbl_usuariosgrupos ug1 on ug1.pk_usuariogrupo = i1.fk_usuariogrupo
                                                    where ra.fk_atributo = 862 and i1.fk_periodo < $periodo)
                        and i.fk_periodo = $periodo and e.pk_atributo in ($escuela) and es.pk_estructura = $sede
                        ORDER BY u.apellido";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getnuevosingresosnoreinscritos($periodo, $sede, $periodoanterior){
        $SQL =  "SELECT u.pk_usuario,
                        u.apellido as apellido,
                        u.nombre as nombre,
                        u.correo as correo
                  from tbl_usuarios u
                  join tbl_usuariosgrupos ug on u.pk_usuario=ug.fk_usuario
                  join tbl_inscripciones ins on ug.pk_usuariogrupo=ins.fk_usuariogrupo
                  join tbl_atributos a on ins.fk_atributo=a.pk_atributo
                  join tbl_estructuras estr on ins.fk_estructura=estr.pk_estructura
                  where ins.fk_periodo = {$periodoanterior}
                  and  ins.fk_estructura={$sede}
                  and  u.pk_usuario not in (
                                            select u.pk_usuario
                                            from tbl_usuarios u
                                            join tbl_usuariosgrupos ug on u.pk_usuario=ug.fk_usuario
                                            join tbl_inscripciones ins on ug.pk_usuariogrupo=ins.fk_usuariogrupo
                                            where ins.fk_periodo<{$periodoanterior}
                                            group by u.pk_usuario)
                  and u.pk_usuario not in (
                                            select u.pk_usuario
                                            from tbl_usuarios u
                                            join tbl_usuariosgrupos ug on u.pk_usuario=ug.fk_usuario
                                            join tbl_inscripciones ins on ug.pk_usuariogrupo=ins.fk_usuariogrupo
                                            join tbl_atributos a on ins.fk_atributo=a.pk_atributo
                                            where ins.fk_periodo = {$periodo})
                      order by 2 asc";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getporsemestredeubicacion($periodo, $sede,$escuela, $semestre){
        $SQL =  "SELECT * ,case when ubicacion = 1 then 873
                             when ubicacion = 2 then 874
                             when ubicacion = 3 then 875
                             when ubicacion = 4 then 876
                             when ubicacion = 5 then 878
                             when ubicacion = 6 then 879
                             when ubicacion = 7 then 881
                             when ubicacion = 8 then 882
                             when ubicacion = 9 then 883
                             when ubicacion = 10 then 884
                             when ubicacion = 11 then 9696
                             when ubicacion = 12 then 9697
                             when ubicacion = 0  then 872
                             end as semestre_inscrito2
                            from(select DISTINCT pk_usuario,correo, nombre, apellido, semestre, sede,
                                    periodo, escuela, ubicacion, semestre_inscrito
                                    from (
                                            select u.pk_usuario as pk_usuario,
                                            u.nombre as nombre ,
                                            upper(u.correo) as correo,
                                            u.apellido as apellido,
                                            i.fk_semestre as semestre,
                                            est.pk_estructura as sede,
                                            i.fk_periodo  as periodo,
                                            p.fk_escuela as escuela,
                                            vs.id as semestre_inscrito,
                                            fn_xrxx_estudiante_sem_ubicacion_periodod(u.pk_usuario,i.fk_atributo,i.fk_periodo) as ubicacion
                                            from tbl_usuarios u
                                            join tbl_usuariosgrupos usg on u.pk_usuario = usg.fk_usuario
                                            join tbl_inscripciones i on usg.pk_usuariogrupo = i.fk_usuariogrupo
                                            join tbl_recordsacademicos ra on i.pk_inscripcion = ra.fk_inscripcion
                                            join tbl_asignaturas a on a.pk_asignatura = ra.fk_asignatura
                                            join tbl_estructuras est on est.pk_estructura = i.fk_estructura
                                            join tbl_pensums p on p.pk_pensum = a.fk_pensum
                                            join vw_semestres vs on vs.pk_atributo = i.fk_semestre
                                            where i.fk_periodo =$periodo
                                            and est.pk_estructura = $sede
                                            and p.fk_escuela in ($escuela)
                                            ) as sqt
                                            where ubicacion  = $semestre::smallint
                                            order by ubicacion, semestre_inscrito
                                            )as sqt2";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getrangodeUCaprobadas($periodo, $sede, $escuela, $UCA, $UCAT){
        $SQL =  "SELECT DISTINCT usu.pk_usuario,
                                 usu.apellido,
                                 usu.nombre,
                                 upper(usu.correo) as correo,
                                 COALESCE(fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer),0) as uca
                                        FROM tbl_recordsacademicos ra
                                        JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
                                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                        JOIN tbl_usuarios      usu ON usu.pk_usuario     = ug.fk_usuario
                                        JOIN tbl_asignaturas   asi ON asi.pk_asignatura  = ra.fk_asignatura
                      WHERE
                        ins.fk_estructura = $sede       AND
                        ins.fk_periodo    = $periodo     AND
                        ins.fk_atributo   in ($escuela)      AND
                        fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer) >= '$UCA'::integer AND
                        fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer) <= '$UCAT'::integer
                        GROUP BY usu.pk_usuario, usu.apellido, usu.correo, usu.nombre, fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer)";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getrepitientes($periodo, $sede, $escuela, $semestre, $materia){
        $SQL =  "SELECT     DISTINCT
                                ug.fk_usuario AS pk_usuario,
                                us.nombre,
                                us.apellido,
                                upper(us.correo) as correo
                    FROM tbl_inscripciones ins
                      JOIN tbl_usuariosgrupos    ug   ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                      JOIN tbl_usuarios          us   ON us.pk_usuario = ug.fk_usuario
                      JOIN tbl_recordsacademicos ra   ON ra.fk_inscripcion = ins.pk_inscripcion
                      JOIN tbl_asignaciones      asg  ON asg.pk_asignacion = ra.fk_asignacion
                      JOIN tbl_asignaturas       asi  ON asi.pk_asignatura = asg.fk_asignatura
                      JOIN vw_materias           ma   ON ma.pk_atributo = asi.fk_materia
                      JOIN vw_semestres        sem  ON sem.pk_atributo = asi.fk_semestre
                      JOIN tbl_pensums           pem  ON pem.pk_pensum  = asi.fk_pensum
                      JOIN vw_escuelas           esc  ON esc.pk_atributo = pem.fk_escuela
                      JOIN tbl_estructuras       est  ON est.pk_estructura = asg.fk_estructura
                      JOIN tbl_estructuras       est1 ON est1.pk_estructura = est.fk_estructura
                      JOIN tbl_estructuras       est2 ON est2.pk_estructura = est1.fk_estructura
                        WHERE   ra.fk_atributo = 862             AND
                          ins.fk_periodo < $periodo              AND
                          est2.pk_estructura = $sede             AND
                          esc.pk_atributo in ($escuela)             AND
                          asg.fk_semestre = $semestre            AND
                          asi.pk_asignatura = $materia           AND
                          ra.calificacion < 10                   AND

                      asi.pk_asignatura IN(
                    SELECT asi1.pk_asignatura
                    FROM tbl_inscripciones ins1

                            JOIN tbl_usuariosgrupos    ug1  ON ug1.pk_usuariogrupo = ins1.fk_usuariogrupo
                            JOIN tbl_recordsacademicos ra1  ON ra1.fk_inscripcion = ins1.pk_inscripcion
                            JOIN tbl_asignaciones      asg1 ON asg1.pk_asignacion = ra1.fk_asignacion
                            JOIN tbl_asignaturas       asi1 ON asi1.pk_asignatura = asg1.fk_asignatura
                            JOIN vw_materias           ma1  ON ma1.pk_atributo = asi1.fk_materia

                                   WHERE  ins1.fk_periodo  IN($periodo)      AND
                                          ra1.fk_atributo  NOT IN (863)           AND
                                            ra1.calificacion < 10                   AND
                                            ug1.fk_usuario = ug.fk_usuario)

                        ORDER BY ug.fk_usuario";
        try{
            $results = $this->_db->query($SQL);
            return $results->fetchAll();
        } catch (Exception $e){
            return array();
        }


    }

    public function getsinpasantiasocialconUCA($periodo, $sede, $escuela, $UCA){
        $SQL =  "SELECT DISTINCT usu.pk_usuario, usu.apellido, usu.nombre, upper(usu.correo) as correo, COALESCE(fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer),0) as uca
                                        FROM tbl_recordsacademicos ra
                                        JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
                                        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                        JOIN tbl_usuarios      usu ON usu.pk_usuario     = ug.fk_usuario
                                        JOIN tbl_asignaturas   asi ON asi.pk_asignatura  = ra.fk_asignatura
                      WHERE
                        ins.fk_estructura = $sede         AND
                        ins.fk_periodo    = $periodo      AND
                        ins.fk_atributo   in ($escuela)      AND
                        fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer) >= '$UCA'::integer
                        GROUP BY usu.pk_usuario, usu.apellido, usu.nombre, usu.correo, fn_xrxx_estudiante_calcular_uca(usu.pk_usuario, ins.fk_atributo::integer)";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getTipoDeListadoEstudiante(){
        $SQL =  "SELECT pk_atributo, valor
                 from tbl_atributos
                 where fk_atributotipo = 101
                 order by 1 asc";
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function changePasswordSo($id, $passwordNew)
    {
        $data = array(
            'passwordoehash' => md5($passwordNew)
        );

        $where = $this->getAdapter()->quoteInto('pk_usuario = ?', (int)$id);

        $rows_affected = $this->update($data, $where);

        return $rows_affected;
    }

    public function setSearch($searchData)
    {
        $this->searchData = $searchData;
    }

    public function setData($Data, $Keys)
    {
        $Keys = array_fill_keys($Keys, null);
        $Data = array_intersect_key($Data, $Keys);

        $Where = array(' AND  ug.fk_grupo        = ' => $Data['Perfil']);

        $Where = array_filter($Where);
        $Where = $this->SwapBytes_Array->implode(' ', $Where);
        $Where = ltrim($Where, ' AND ');

        $this->Where = $Where;
    }

    public function getEstudiantes($itemPerPage, $pageNumber)
    {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, nombre, apellido
		        FROM tbl_usuarios u
		        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		        WHERE ug.fk_grupo = 855
                          {$whereSearch}
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getUsuarios($itemPerPage, $pageNumber, $inactivo)
    {

        if (empty($this->Where)){
            return;
        }

        if(!empty($inactivo)){
            $filtro_inactivo = " AND ug.fk_usuario NOT IN (
                                    select distinct ug.fk_usuario
                                    from tbl_usuariosgrupos         ug
                                    where ug.fk_grupo = 10671
                                ) ";
        }else{
            $filtro_inactivo = " ";
        }


        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                       CASE WHEN length(segundo_nombre) > 0  THEN primer_nombre || ' ' || segundo_nombre ELSE primer_nombre END AS nombre,
                       CASE WHEN length(segundo_apellido) > 0 THEN primer_apellido || ' ' || segundo_apellido ELSE primer_apellido END AS apellido
		        FROM tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		        WHERE {$this->Where}
                ".$filtro_inactivo."
                          {$whereSearch}
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function removeAdministrativo($usuariogrupo)
    {

        $SQL = "UPDATE tbl_usuariosgrupos
              SET fk_grupo = (SELECT pk_atributo
                  FROM vw_grupos
                  WHERE grupo = 'Inactivo')
              WHERE pk_usuariogrupo = {$usuariogrupo}";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function getDocentesRetirados($iPeriodoAnterior, $iPeriodoVigente, $iSede, $EscuelaOpcion) {

        if(!isset($iPeriodoVigente))
            return;

        if(!isset($iPeriodoAnterior))
            return;

        if(!isset($iSede))
            return;


        $SQL= "SELECT DISTINCT u.pk_usuario,
                                DENSE_RANK() over(ORDER BY u.apellido, u.pk_usuario) num,
                                u.apellido,
                                u.nombre,
                                u.correo,
                                (SELECT SUM(CANTIDAD) FROM(
                                    SELECT count(DISTINCT tblH.horafin) * 2 AS Cantidad
                                    FROM tbl_asignaciones tblAS
                                    INNER JOIN tbl_asignaturas tblA ON tblA.pk_asignatura = tblAS.fk_asignatura
                                    INNER JOIN tbl_usuariosgrupos tblUG ON tblUG.pk_usuariogrupo = tblAS.fk_usuariogrupo
                                    INNER JOIN tbl_estructuras tblE1 ON tblE1.pk_estructura = tblAS.fk_estructura
                                    INNER JOIN tbl_estructuras tblE2 ON tblE2.pk_estructura = tblE1.fk_estructura
                                    INNER JOIN tbl_horarios tblH ON tblH.pk_horario = tblAS.fk_horario
                                    INNER JOIN vw_dias vwD ON vwD.pk_atributo = tblAS.fk_dia
                                    WHERE tblUG.pk_usuariogrupo   =  ag.pk_usuariogrupo
                                    AND tblAS.fk_periodo         =  {$iPeriodoAnterior}
                                    AND tblE2.fk_estructura      =  {$iSede}
                                    GROUP BY vwD.id) AS aaa) AS \"Horas\",
                                E.codigo ||''||SE.id ||''||S.valor as curso,
                                m.materia,
                                UPPER(SUBSTR(d.dia, 1, 3)) || ' - ' || TO_CHAR(H.horainicio, 'hh:mi.AM') || ' / ' || TO_CHAR(H.horafin, 'hh:mi.AM') AS \"Horario\",
                                D.pk_atributo,
                                H.pk_horario,
                                a.fk_dia
                                FROM tbl_asignaciones a
                                JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                                JOIN vw_materias         m ON m.pk_atributo      = aa.fk_materia
                                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                                JOIN tbl_usuarios        u ON u.pk_usuario       = ag.fk_usuario
                                JOIN tbl_estructuras    E1 ON E1.pk_estructura   = a.fk_estructura
                                JOIN tbl_estructuras    E2 ON E2.pk_estructura   = E1.fk_estructura
                                JOIN tbl_estructuras    E3 ON E3.pk_estructura   = E2.fk_estructura
                                JOIN tbl_horarios        H ON H.pk_horario       = a.fk_horario
                                JOIN vw_dias             D ON D.pk_atributo      = a.fk_dia
                                JOIN vw_secciones        S ON S.pk_atributo      = a.fk_seccion
                                JOIN tbl_pensums         P ON P.pk_pensum        = aa.fk_pensum
                                JOIN vw_escuelas         E ON E.pk_atributo      = P.fk_escuela
                                JOIN vw_semestres       SE ON SE.pk_atributo     = a.fk_semestre_alterado
                                WHERE a.fk_periodo = {$iPeriodoAnterior}
                                AND E3.pk_estructura = {$iSede}
                                AND E.pk_atributo IN({$EscuelaOpcion})
                                AND u.pk_usuario NOT IN (
                                        SELECT u.pk_usuario
                                        FROM tbl_asignaciones a
                                        JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                                        JOIN tbl_usuarios        u ON u.pk_usuario       = ag.fk_usuario
                                        WHERE a.fk_periodo = {$iPeriodoVigente}
                                        ORDER BY u.apellido, u.nombre ASC)
                        ORDER BY 2 ASC;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function ListadoRegularesDetalle($iPeriodoVigente, $iSede, $EscuelaOpcion) {

        if(!isset($iPeriodoVigente))
            return;

        if(!isset($iSede))
            return;



        $SQL= "SELECT DISTINCT u.pk_usuario,
                                DENSE_RANK() over(ORDER BY u.apellido, u.pk_usuario) num,
                               u.apellido,
                               u.nombre,
                               u.correo,
            (SELECT SUM(CANTIDAD) FROM(
                 SELECT count(DISTINCT tblH.horafin) * 2 AS Cantidad
                 FROM tbl_asignaciones tblAS
                 INNER JOIN tbl_asignaturas tblA ON tblA.pk_asignatura = tblAS.fk_asignatura
                 INNER JOIN tbl_usuariosgrupos tblUG ON tblUG.pk_usuariogrupo = tblAS.fk_usuariogrupo
                 INNER JOIN tbl_estructuras tblE1 ON tblE1.pk_estructura = tblAS.fk_estructura
                 INNER JOIN tbl_estructuras tblE2 ON tblE2.pk_estructura = tblE1.fk_estructura
                 INNER JOIN tbl_horarios tblH ON tblH.pk_horario = tblAS.fk_horario
                 INNER JOIN vw_dias vwD ON vwD.pk_atributo = tblAS.fk_dia
                 WHERE tblUG.pk_usuariogrupo   =  ag.pk_usuariogrupo
                 AND tblAS.fk_periodo         =  {$iPeriodoVigente}
                 AND tblE2.fk_estructura      =  {$iSede}
                 GROUP BY vwD.id) AS aaa) AS \"Horas\",
                E.codigo ||''||SE.id ||''||S.valor as curso,
                m.materia,
                UPPER(SUBSTR(D.dia, 1, 3)) || ' - ' || TO_CHAR(H.horainicio, 'hh:mi.AM') || ' / ' || TO_CHAR(H.horafin, 'hh:mi.AM') AS \"Horario\",
                D.pk_atributo,
                H.pk_horario,
                a.fk_dia
                    FROM tbl_asignaciones a
                    JOIN tbl_asignaturas    aa ON aa.pk_asignatura   =  a.fk_asignatura
                    JOIN vw_materias         m ON m.pk_atributo      = aa.fk_materia
                    JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                    JOIN tbl_usuarios        u ON u.pk_usuario       = ag.fk_usuario
                    JOIN tbl_estructuras      E1 ON E1.pk_estructura = a.fk_estructura
                    JOIN tbl_estructuras      E2 ON E2.pk_estructura = E1.fk_estructura
                    JOIN tbl_estructuras      E3 On E3.pk_estructura = E2.fk_estructura
                    JOIN tbl_horarios H ON H.pk_horario = a.fk_horario
                    JOIN vw_dias D ON D.pk_atributo = a.fk_dia
                    JOIN vw_secciones S ON S.pk_atributo = a.fk_seccion
                    JOIN tbl_pensums P ON P.pk_pensum = aa.fk_pensum
                    JOIN vw_escuelas E ON E.pk_atributo = P.fk_escuela
                    JOIN vw_semestres SE ON SE.pk_atributo = a.fk_semestre_alterado
                    WHERE a.fk_periodo = {$iPeriodoVigente}
                    AND E.pk_atributo IN($EscuelaOpcion)
                    AND u.pk_usuario <> 0
                    AND E3.pk_estructura = {$iSede}
                    GROUP BY u.pk_usuario, ag.pk_usuariogrupo, u.nombre, u.apellido,u.correo, m.materia, D.pk_atributo, H.pk_horario,
                   UPPER(SUBSTR(D.dia, 1, 3)) || ' - ' || TO_CHAR(H.horainicio, 'hh:mi.AM') || ' / ' || TO_CHAR(H.horafin, 'hh:mi.AM'),
                   E.codigo ||''||SE.id ||''||S.valor || ' ' ||  m.materia,E.codigo ||''||SE.id ||''||S.valor,m.materia,
                   a.fk_dia
                    ORDER BY u.apellido, u.nombre , a.fk_dia ASC";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function checkNuevoIngreso($user){
      $SQL = "SELECT case when ug.fk_usuario NOT IN (SELECT distinct ug.fk_usuario
                                                    FROM tbl_usuariosgrupos ug1
                                                    JOIN tbl_inscripciones i1 ON i1.fk_usuariogrupo = ug1.pk_usuariogrupo
                                                    JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
                                                    WHERE i1.fk_periodo < i.fk_periodo
                                                      and ug1.fk_usuario = $user
                                                    ) then true else false end as ni
                     FROM tbl_usuariosgrupos ug
                     JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                     where i.fk_periodo = (select pk_periodo
                                            from tbl_periodos
                                            order by 1 DESC
                                            limit 1)
                        and ug.fk_usuario = $user
                       ";
            $results = $this->_db->fetchOne($SQL);

        return $results;
    }

    public function ListadoNuevoIngresoDetalle($iPeriodoAnterior, $iPeriodo, $iSede, $EscuelaOpcion) {

        if(!isset($iPeriodoAnterior))
            return;

        if(!isset($iPeriodo))
            return;

        if(!isset($iSede))
            return;

        $SQL = "SELECT DISTINCT u.pk_usuario,
                                DENSE_RANK() over(ORDER BY u.apellido, u.nombre) num,
                                u.nombre,
                                u.apellido,
                                (SELECT SUM(CANTIDAD) FROM(
                                     SELECT count(DISTINCT tblH.horafin) * 2 AS Cantidad
                                     FROM tbl_asignaciones tblAS
                                     INNER JOIN tbl_asignaturas tblA    ON tblA.pk_asignatura    = tblAS.fk_asignatura
                                     INNER JOIN tbl_usuariosgrupos tblUG    ON tblUG.pk_usuariogrupo = tblAS.fk_usuariogrupo
                                     INNER JOIN tbl_estructuras tblE1   ON tblE1.pk_estructura   = tblAS.fk_estructura
                                     INNER JOIN tbl_estructuras tblE2   ON tblE2.pk_estructura   = tblE1.fk_estructura
                                     INNER JOIN tbl_horarios tblH       ON tblH.pk_horario   = tblAS.fk_horario
                                     INNER JOIN vw_dias vwD         ON vwD.pk_atributo   = tblAS.fk_dia
                                     WHERE tblUG.pk_usuariogrupo   =  tblAS.fk_usuariogrupo  AND
                                     tblAS.fk_periodo         =  $iPeriodo
                                     AND tblE2.fk_estructura      =  $iSede
                                     AND tblUG.fk_usuario         = u.pk_usuario
                                     GROUP BY vwD.id) AS aaa) AS \"Horas\",
                                E.codigo ||''||SE.id ||''||S.valor as curso,
                                m.materia,
                                UPPER(SUBSTR(d.dia, 1, 3)) || ' - ' || TO_CHAR(H.horainicio, 'HH12:MI.am') || ' / ' || TO_CHAR(H.horafin, 'HH12:MI.am') AS \"Horario\",
                                d.pk_atributo,
                                h.pk_horario,
                                a.fk_dia
                                FROM tbl_asignaciones a
                                JOIN tbl_asignaturas      aa ON aa.pk_asignatura    =  a.fk_asignatura
                                JOIN vw_materias           m ON m.pk_atributo       = aa.fk_materia
                                JOIN tbl_usuariosgrupos   ag ON ag.pk_usuariogrupo  =  a.fk_usuariogrupo
                                JOIN tbl_usuarios          u ON u.pk_usuario        = ag.fk_usuario
                                JOIN tbl_estructuras      E1 ON E1.pk_estructura    = a.fk_estructura
                                JOIN tbl_estructuras      E2 ON E2.pk_estructura    = E1.fk_estructura
                                JOIN tbl_estructuras      E3 On E3.pk_estructura    = E2.fk_estructura
                                JOIN tbl_horarios      h ON h.pk_horario        = a.fk_horario
                                JOIN vw_dias           d ON d.pk_atributo       = a.fk_dia
                                JOIN vw_secciones      s ON s.pk_atributo       = a.fk_seccion
                                JOIN tbl_pensums       p ON p.pk_pensum         = aa.fk_pensum
                                JOIN vw_escuelas       e ON e.pk_atributo       = P.fk_escuela
                                JOIN vw_semestres     se ON se.pk_atributo      = a.fk_semestre_alterado
                                WHERE a.fk_periodo           = $iPeriodo
                                AND E3.pk_estructura         = $iSede
                                AND e.pk_atributo IN($EscuelaOpcion)
                                AND U.pk_usuario NOT IN (SELECT u.pk_usuario
                                FROM tbl_asignaciones a
                                JOIN tbl_usuariosgrupos ag ON ag.pk_usuariogrupo =  a.fk_usuariogrupo
                                JOIN tbl_usuarios        u ON u.pk_usuario       = ag.fk_usuario
                                WHERE a.fk_periodo = $iPeriodoAnterior
                                ORDER BY u.apellido, u.nombre ASC)
                                GROUP BY u.pk_usuario,
                                     e.codigo,
                                     SE.id,
                                     S.valor,
                                     u.nombre,
                                     u.apellido,
                                     m.materia,
                                     d.pk_atributo,
                                     h.pk_horario,
                                     UPPER(SUBSTR(D.dia, 1, 3)) || ' - ' || TO_CHAR(H.horainicio, 'HH12:MI.am') || ' / ' || TO_CHAR(H.horafin, 'HH12:MI.am'),
                                     E.codigo ||''||SE.id ||''||S.valor || ' '|| m.materia,
                                     a.fk_dia
                                     ORDER BY u.apellido, u.nombre , a.fk_dia ASC";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function getEscuelasTodas(){
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


    public function getTipoNominaProfesores(){

        $SQL = "SELECT pk_atributo as value, valor as label
                from tbl_atributos a
                where a.fk_atributotipo = 100
                order by case pk_atributo when 20094 then 1 end ,pk_atributo";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function getUsuarioGrupo($cedula, $fk_grupo)
    {

        $SQL = "SELECT pk_usuariogrupo
              FROM tbl_usuariosgrupos
              WHERE fk_usuario = {$cedula}
              AND fk_grupo = {$fk_grupo};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function getPkGrupo($Grupo)
    {

        $SQL = "SELECT pk_atributo
                 FROM vw_grupos
            WHERE grupo ILIKE '{$Grupo}';";

        return $this->_db->fetchOne($SQL);

    }

    public function CheckAfinidad($id)
    {

        $SQL = "SELECT  COUNT(pk_usuarioafinidad)
                  FROM tbl_usuariosafinidades
                  WHERE fk_usuario = {$id};";


        return $this->_db->fetchOne($SQL);

    }


    public function SetAfinidad($id, $afinidad)
    {
        $SQL = "INSERT INTO tbl_usuariosafinidades (fk_usuario,fk_afinidad,fk_autorizacion)
                    VALUES ({$id},{$afinidad},1617)";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function activarAdministrativo($usuariogrupo)
    {

        $SQL = "UPDATE tbl_usuariosgrupos
              SET fk_grupo = (SELECT pk_atributo
                  FROM vw_grupos
                  WHERE grupo = 'Administrativo')
              WHERE pk_usuariogrupo = {$usuariogrupo}";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function getUsuariosNI($itemPerPage, $pageNumber, $periodo, $escuela, $sede)
    {


        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                CASE WHEN length(segundo_nombre) > 0  THEN primer_nombre || ' ' || segundo_nombre ELSE primer_nombre END AS nombre,
                CASE WHEN length(segundo_apellido) > 0 THEN primer_apellido || ' ' || segundo_apellido ELSE primer_apellido END AS apellido
                ,COALESCE((SELECT ud.promedio
                            FROM tbl_usuariosdatos ud
                            WHERE ud.fk_usuario = pk_usuario),0) as promedio
                            FROM tbl_usuarios u
                            JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                            JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                            WHERE u.pk_usuario  NOT IN (SELECT ug.fk_usuario
                            FROM tbl_usuariosgrupos ug
                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                            WHERE ins.fk_periodo < i.fk_periodo
                            AND ug.fk_usuario = u.pk_usuario)
                AND i.fk_periodo = {$periodo}
                AND i.fk_atributo = {$escuela}
                AND i.fk_estructura = {$sede}
                AND i.fk_pensum = (SELECT pk_pensum
                                    FROM tbl_pensums
                                    WHERE fk_escuela = {$escuela}
                                    AND nombre = 'Vigente')
                {$whereSearch}
                ORDER BY pk_usuario
                LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }


    public function getTutores($itemPerPage, $pageNumber, $grupo)
    {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT pk_usuario, LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                       CASE WHEN length(segundo_nombre) > 0  THEN primer_nombre || ' ' || segundo_nombre ELSE primer_nombre END AS nombre,
                       CASE WHEN length(segundo_apellido) > 0 THEN primer_apellido || ' ' || segundo_apellido ELSE primer_apellido END AS apellido
		        FROM tbl_usuarios u
                        JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
		        WHERE ug.fk_grupo = {$grupo}
                          {$whereSearch}
		        ORDER BY pk_usuario LIMIT {$itemPerPage} OFFSET {$pageNumber};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }


    /**
     * @todo que hice?
     * @return <type>
     */
    public function getSQLCount()
    {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE ug.fk_grupo = 855
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }

    public function totalusuarios()
    {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        if (empty($this->Where))
            return;

        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE {$this->Where}
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }


    public function totalusuariosNI($periodo, $escuela, $sede)
    {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);


        $SQL = "SELECT count(distinct pk_usuario)
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
		WHERE u.pk_usuario  NOT IN (SELECT ug.fk_usuario
                                        FROM tbl_usuariosgrupos ug
                                        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                                        WHERE ins.fk_periodo < i.fk_periodo
                                          AND ug.fk_usuario = u.pk_usuario)
                AND i.fk_periodo = {$periodo}
                AND i.fk_atributo = {$escuela}
                AND i.fk_estructura = {$sede}
                AND i.fk_pensum = (SELECT pk_pensum
                                   FROM tbl_pensums
                                   WHERE fk_escuela = {$escuela}
                                     AND nombre = 'Vigente')
                  {$whereSearch};";

        return $this->_db->fetchOne($SQL);
    }


    public function splitnames($ci)
    {

        $SQL = "SELECT * FROM fn_cxux_cortar_nombres({$ci});";

        return $this->_db->fetchOne($SQL);

    }

    public function totalusuariosdelsearch($grupo)
    {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT COUNT(pk_usuario)
		FROM tbl_usuarios u
		JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
		WHERE ug.fk_grupo = {$grupo}
                  {$whereSearch}";

        return $this->_db->fetchOne($SQL);
    }

    /**
     * Cuenta cuantos registros existen bajo un pk_usuario, adicionalmente si se
     * desea, permite restringir la busqueda.
     *
     * @param int $fk_usuario
     * @param string $where WHERE en SQL.
     * @return int
     */
    public function getCount($fk_usuario, $where = null)
    {
        if (empty($fk_usuario)) return;

        $SQL = "SELECT COUNT({$this->_primary}) AS count
                FROM {$this->_name}
                WHERE {$this->_primary} = {$fk_usuario} {$where};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['count'];
    }

    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id)
    {
        $id = (int)$id;

        $row = $this->fetchRow('pk_usuario' . ' = ' . $id . ' AND pk_usuario >= 10000');

        if (isset($row)) {
            return $row->toArray();
        }
    }

    public function addRow($data)
    {
        $data = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function updateRow($id, $data)
    {
        $data = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id)
    {
        $affected = $this->delete($this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function checkPasswordOperacionesEspeciales($grupo, $password)
    {
        $grupo = (int)$grupo;

        if (isset($grupo)) {
            $SQL = "SELECT count(pk_usuario) = 1
                    FROM tbl_usuarios        u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN vw_grupos          vg ON vg.pk_atributo = ug.fk_grupo
                    WHERE vg.pk_atributo   = {$grupo}
                      AND u.passwordoehash = '{$password}'";

            return (boolean)$this->_db->fetchOne($SQL);
        }
    }

    public function changePk($old, $new)
    {
        $this->_db->beginTransaction();

        // Copiamos el usuario.
        $SQL = "INSERT INTO tbl_usuarios (pk_usuario, status, nacionalidad, sexo, nombre, apellido, direccion, fechanacimiento, correo, passwordhash, deleted, telefono, foto, telefono_movil, passwordoehash)
                SELECT {$new}, status, nacionalidad, sexo, nombre, apellido, direccion, fechanacimiento, correo, MD5('{$new}'), deleted, telefono, foto, telefono_movil, passwordoehash
                FROM tbl_usuarios
                WHERE pk_usuario = {$old}";

        $return += $this->_db->query($SQL);

        // Cambiamos el usuario el la tabla de usuariogrupo, asi evitamos cambiar
        // todos los registros relacionados al viejo usuario.
        $SQL = "UPDATE tbl_usuariosgrupos
                   SET fk_usuario = {$new}
                 WHERE fk_usuario = {$old}";

        $return += $this->_db->query($SQL);

        // Cambiamos el usuario el la tabla de usuariosvehiculos, asi evitamos cambiar
        // todos los registros relacionados al viejo usuario.
        $SQL = "UPDATE tbl_usuariosvehiculos
                   SET fk_usuario = {$new}
                 WHERE fk_usuario = {$old}";

        $return += $this->_db->query($SQL);

        // Eliminamos la afinidad del usuario para el uso de las tarjetas de Banesco.
        $SQL = "DELETE FROM tbl_usuariosafinidades
				WHERE fk_usuario = {$old}";

        $return += $this->_db->query($SQL);

        // Eliminamos el usuario antiguo.
        $SQL = "DELETE FROM tbl_usuarios
                WHERE pk_usuario = {$old}";

        $return += $this->_db->query($SQL);

        $this->_db->commit();

        return $return;
    }

    public function getPhoto($id)
    {
        if (!is_numeric($id)) return;

        $config = $this->_db->getConfig();
        $conn = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
        $query = pg_query($conn, "SELECT foto FROM tbl_usuarios WHERE pk_usuario = {$id}");
        $row = pg_fetch_row($query);
        $image = pg_unescape_bytea($row[0]);

        pg_close($conn);

        /*
         * En caso de que no exista la imagen en la DB, se procede a cargar una
         * imagen generica desde el sistema de archivos:
         */
        if (empty($image)) {
            $image = file_get_contents(APPLICATION_PATH . '/../public/images/empty_profile.jpg');
        }

        return $image;
    }

    public function setPhoto($id, $image)
    {
        if (!is_numeric($id)) return;

        $config = $this->_db->getConfig();
        $conn = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
        $image = pg_escape_bytea($image);
        $query = pg_query($conn, "UPDATE tbl_usuarios SET foto = '{$image}' WHERE pk_usuario = {$id}");
        $affected = pg_affected_rows($query);

        pg_close($conn);

        return $affected;
    }

    public function getProfile($id)
    {
        if (!is_numeric($id)) return;

        $SQL = "SELECT initcap(lower(nombre)) || ' ' || initcap(lower(apellido)) as nombre,
                        correo as correo,
                        TO_CHAR(fechanacimiento,'DD-MM-YYYY') as nacimiento,
                        telefono as tlf,
                        telefono_movil as cel,
                        LOWER(direccion) as dir,
                        actualizado
                    FROM tbl_usuarios u
                    WHERE u.pk_usuario = {$id};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function getInfoGeneral($ci, $per)
    {

        $SQL = "SELECT u.nombre,
                           u.apellido,
                           sed.nombre as sed,
                           esc.escuela as escuela,
                           fn_xrxx_estudiante_turno_por_horas(i.fk_periodo, i.fk_estructura, u.pk_usuario, i.fk_atributo, i.fk_pensum) as turno,
                           (SELECT valor FROM vw_turnos WHERE pk_atributo = fn_xrxx_estudiante_turno_por_horas(i.fk_periodo, i.fk_estructura, u.pk_usuario, i.fk_atributo, i.fk_pensum)) as valor,
                           i.fk_periodo,
                           i.fk_pensum
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                    JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                    JOIN vw_turnos t ON t.pk_atributo = fn_xrxx_estudiante_turno_por_horas(i.fk_periodo, i.fk_estructura, u.pk_usuario)
                    JOIN tbl_pensums pen ON pen.pk_pensum = i.fk_pensum AND pen.codigopropietario <> 9
                    where u.pk_usuario = {$ci}
		   AND i.fk_periodo = {$per}
		    ORDER BY i.fk_pensum DESC;";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();


    }

    public function getInfoGeneralAdministrativo($ci)
    {

        $SQL = "SELECT DISTINCT u.nombre,
                           u.apellido
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    where u.pk_usuario = {$ci}
                    and ug.fk_grupo IN (854,1745);";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getInfoGeneralUsuario($ci)
    {

        $SQL = "SELECT DISTINCT u.nombre,
                           u.apellido
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    where u.pk_usuario = {$ci};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function samePassword($ci)
    {

        $SQL = "SELECT pk_usuario
                    FROM tbl_usuarios u
                    WHERE u.pk_usuario = {$ci}
                      AND u.passwordhash = MD5(pk_usuario::text);";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();


    }


    public function namestocomplete($ci)
    {

        $SQL = "UPDATE tbl_usuarios u SET nombre = name , apellido = last
                    FROM
                    (
                       SELECT primer_nombre || ' ' || coalesce(segundo_nombre,'') as name,
                              primer_apellido || ' ' || coalesce(segundo_apellido,'') as last
                         FROM tbl_usuarios usu
                       WHERE pk_usuario = {$ci}

                    ) as usu
                    WHERE pk_usuario = {$ci};";

        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }

    public function getEstudianteUG($cedula)
    {
        $SQL = "SELECT pk_usuariogrupo FROM tbl_usuariosgrupos
                    WHERE fk_usuario = {$cedula} AND fk_grupo = 855;";

        $results = $this->_db->query($SQL);
        return $results->fetchAll();
    }

        public function getEstudiantePago($cedula,$sede,$is_nuevo_ingreso){
		$is_nuevo_ingreso = ($is_nuevo_ingreso ? "true" : "false");

            $SQL = "SELECT DISTINCT u.nombre, u.apellido ,pk_usuario, ins.fk_periodo,tp.fechainicio as inicio_periodo, tp.fechafin as fin_periodo,
                                    (SELECT DISTINCT min(costo)
                                     FROM tbl_cuotasperiodos
                                     WHERE fk_periodo = ins.fk_periodo 
                                     AND fk_estructura = {$sede}
                                  	AND nuevoingreso = {$is_nuevo_ingreso} 
                                     ) as precio_cuota,
                                    (SELECT sum(costo)
                                    FROM tbl_cuotasperiodos
                                    WHERE fk_periodo = ins.fk_periodo 
                                    AND fk_estructura = {$sede}
                                  	AND nuevoingreso = {$is_nuevo_ingreso} 
                                    ) as total_pagar,
                                   (SELECT count(*)
                                    FROM tbl_calendarios
                                    WHERE fk_periodo = ins.fk_periodo
                                    AND current_date >= fechainicio
            AND fk_renglon = 1692 ) as cuotas_vencidas,
                                   (SELECT count(*)
                                    FROM tbl_calendarios
                                    WHERE fk_periodo = ins.fk_periodo
            AND fk_renglon = 1692
                                    ) as total_cuotas
                    FROM tbl_inscripciones ins
                    join tbl_periodos tp on ins.fk_periodo = tp.pk_periodo
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                    JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                    WHERE fk_usuario = {$cedula}
                    AND ins.fk_periodo = (
                                                SELECT fk_periodo
                                                FROM tbl_inscripciones i
                                                JOIN tbl_usuariosgrupos g ON g.pk_usuariogrupo = i.fk_usuariogrupo
            JOIN tbl_periodos p ON p.pk_periodo = fk_periodo
                                                WHERE g.fk_usuario = {$cedula}
			    AND now() BETWEEN fechainicio AND fechafin
                                                ORDER BY fk_periodo DESC LIMIT 1
                                        )";
		    $results = $this->_db->fetchRow($SQL); 
          
return ($results);
        }

        public function getEstudianteRandomWithPeriodo($periodo)
        {
            $SQL = "SELECT ug.fk_usuario
                    FROM tbl_inscripciones ins
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                    WHERE ins.fk_periodo = {$periodo}
                    order by random() LIMIT 1 ";
        }

    public function getPerson($cedula)
    {
        $sql = "SELECT pk_usuario, (primer_nombre || ' ' || segundo_nombre) as nombre,
                           primer_apellido,
                           segundo_apellido,
                           to_char(fechanacimiento, 'dd/mm/yyyy') as fecha_nacimiento,
                           correo,
                           telefono,
                           telefono_movil
                   FROM tbl_usuarios
                   WHERE pk_usuario = {$cedula};";

        $results = $this->_db->query($sql);
        return $results->fetchAll();

    }


       public function getUsuario($ci){

        $SQL = "SELECT pk_usuario,primer_nombre,segundo_nombre,primer_apellido,
                segundo_apellido,sexo,nacionalidad,fechanacimiento,direccion,
                correo,telefono,telefono_movil
                FROM tbl_usuarios
                WHERE pk_usuario ={$ci};" ;



      $result = $this->_db->query($SQL);
      $r = $result->fetchAll();
      return $r[0];
    }

    public function updateUsuario($ci, $usuario, $update = null){

        $data = array(

                'primer_nombre'         => utf8_encode(mb_strtoupper($usuario['primer_nombre'], 'utf-8')),
                'segundo_nombre'        => mb_strtoupper($usuario['segundo_nombre'], 'utf-8'),
                'primer_apellido'       => utf8_encode(mb_strtoupper($usuario['primer_apellido'], 'utf-8')),
                'segundo_apellido'      => mb_strtoupper($usuario['segundo_apellido'], 'utf-8'),
                'fechanacimiento'       => $usuario['fechanacimiento'],
                'sexo'                  => $usuario['sexo'],
                'nacionalidad'          => $usuario['nacionalidad'],
                'direccion'             => mb_strtoupper($usuario['direccion'], 'utf-8'),
                'telefono_movil'        => $usuario['telefono_movil'],
                'telefono'              => $usuario['telefono'],
                'correo'                => $usuario['correo'],
                'nombre'                => mb_strtoupper($usuario['primer_nombre'], 'utf-8')." ".mb_strtoupper($usuario['segundo_nombre'], 'utf-8'),
                'apellido'              => mb_strtoupper($usuario['primer_apellido'], 'utf-8')." ". mb_strtoupper($usuario['segundo_apellido'], 'utf-8')

        );

        if($update){
          $data['actualizado'] = true;
        }

            $where = $this->getAdapter()->quoteInto('pk_usuario= ?', $ci);

            $this->update($data, $where);

    }

    public function getProfesoresUltimoPeriodo(){
            //PONER PROFESORES DE LOS NARANJOS
                $sql =    "SELECT DISTINCT ug.fk_usuario as value ,UPPER(us.apellido || ' ' || us.nombre) as label
                                FROM tbl_usuariosgrupos ug
                                JOIN tbl_asignaciones asig on ug.pk_usuariogrupo = asig.fk_usuariogrupo
                                JOIN tbl_usuarios us on ug.fk_usuario = us.pk_usuario
                                JOIN tbl_asignaturas  asi on asig.fk_asignatura = asi.pk_asignatura
                                JOIN tbl_atributos atri on asi.fk_materia = atri.pk_atributo
                                JOIN tbl_estructuras e1 ON e1.pk_estructura = asig.fk_estructura
                                JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
                                JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
                                WHERE fk_periodo = (SELECT max(pk_periodo) FROM tbl_periodos)
                                AND e3.pk_estructura = 7 ORDER BY 2;";
        return $this->_db->query($sql)->fetchAll();
    }

    public function getProfesoresPeriodoSede($periodo, $sede) {
                $sql = "SELECT DISTINCT ug.fk_usuario as value ,UPPER(us.apellido || ' ' || us.nombre) as label
                                FROM tbl_usuariosgrupos ug
                                JOIN tbl_asignaciones asig on ug.pk_usuariogrupo = asig.fk_usuariogrupo
                                JOIN tbl_usuarios us on ug.fk_usuario = us.pk_usuario
                                JOIN tbl_asignaturas  asi on asig.fk_asignatura = asi.pk_asignatura
                                JOIN tbl_atributos atri on asi.fk_materia = atri.pk_atributo
                                JOIN tbl_estructuras e1 ON e1.pk_estructura = asig.fk_estructura
                                JOIN tbl_estructuras e2 ON e2.pk_estructura = e1.fk_estructura
                                JOIN tbl_estructuras e3 ON e3.pk_estructura = e2.fk_estructura
                                WHERE asig.fk_periodo = {$periodo} AND e3.pk_estructura = {$sede}
                                ORDER BY 2;";
        return $this->_db->query($sql)->fetchAll();
    }

    public function getCantidadNuevosIngresos($periodo,$sede){
        $SQL = "SELECT count(distinct pk_usuario) as nuevoIngreso, a.valor as escuela
                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
                WHERE u.pk_usuario  NOT IN (SELECT ug.fk_usuario
                                        FROM tbl_usuariosgrupos ug
                                        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                                        WHERE ins.fk_periodo < i.fk_periodo
                                          AND ug.fk_usuario = u.pk_usuario)
                AND i.fk_periodo = {$periodo}
                AND i.fk_estructura = {$sede}
                AND i.fk_pensum IN (20,21,22,23,24,25)
                GROUP BY a.valor";

      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
    }

    public function getUsuarioDataEscuela($ci) {
                $sql = "SELECT tu.apellido || ', ' ||  tu.nombre AS estudiante, tu.pk_usuario AS cedula, tu.telefono, tu.direccion, ve.escuela, vs.nombre AS sede
                        FROM tbl_usuarios tu
                        JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                        JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                        JOIN vw_escuelas ve ON ti.fk_atributo = ve.pk_atributo
                        JOIN vw_sedes vs ON ti.fk_estructura = vs.pk_estructura
                        WHERE tu.pk_usuario = {$ci}
                        GROUP BY estudiante, tu.pk_usuario, tu.telefono, tu.direccion, ve.escuela, sede";
        $results = $this->_db->fetchRow($sql);
        return ($results);
    }

  public function agregarPreEmpleador($usuario){
    if(isset($usuario)){
      $SQL = "INSERT INTO tbl_usuariosgrupos (fk_usuario,fk_grupo,fk_estado)
              VALUES ($usuario,20111,1628)";
      $this->_db->query($SQL)->fetchAll();
      return true;
    }
  }

  public function agregarEmpleador($usuario,$empresa){
    if(isset($usuario) && isset($empresa)){
        //$this->getAdapter()->beginTransaction();
        //Inicio Transaccion
        $SQL = "BEGIN";
        $this->_db->query($SQL)->fetchAll();
        try {
            //Registro Usuario
            $SQL = "INSERT INTO tbl_usuarios(pk_usuario,nacionalidad,sexo,nombre,apellido,direccion,fechanacimiento,correo,telefono,telefono_movil,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,passwordhash)
                    VALUES({$usuario['pk_usuario']},'{$usuario['nacionalidad']}','{$usuario['sexo']}','{$usuario['nombre']}','{$usuario['apellido']}','{$usuario['direccion']}',
                            '{$usuario['fechanacimiento']}','{$usuario['correo']}','{$usuario['telefono']}','{$usuario['telefono_movil']}','{$usuario['primer_nombre']}','{$usuario['segundo_nombre']}',
                            '{$usuario['primer_apellido']}','{$usuario['segundo_apellido']}',md5('{$usuario['pk_usuario']}'))";
            $this->_db->query($SQL)->fetchAll();
            //Registro Grupo para el Usuario
            $SQL = "INSERT INTO tbl_usuariosgrupos(fk_usuario,fk_grupo,fk_estado)VALUES({$usuario['pk_usuario']},20111,1628)";
            $this->_db->query($SQL)->fetchAll();
            //Inserto Institucion Relacionada con el Usuario  
            $SQL = "INSERT INTO tbl_instituciones(rif,razonsocial)VALUES('{$empresa['rif']}','{$empresa['razonsocial']}')";
            $this->_db->query($SQL)->fetchAll();
            $pk = "SELECT pk_institucion
                     FROM tbl_instituciones
                     WHERE rif ILIKE '%{$empresa['rif']}'";
            return $this->_db->query($SQL)->fetchAll();
            //Asocio al Usuario con la Institucion
            $SQL = "INSERT INTO tbl_solicitudesempleadores(fk_usuario,fk_instituciones,fk_estado)VALUES({$usuario['pk_usuario']},{$pk},19971)";
            $this->_db->query($SQL)->fetchAll();
            //$this->getAdapter()->commit();
            //Commit Transaccion
            $SQL = "COMMIT";
            $this->_db->query($SQL)->fetchAll();
            return 1;
        }catch (Exception $ex) {
          //$this->getAdapter()->rollback();
          //Rollback Transaccion
          $SQL = "ROLLBACK";
          $this->_db->query($SQL)->fetchAll();
          throw new Exception("Error de registro de usuario empresarial mensaje de la notificacion", 1);
          return 0;
        }  
      }                     
    }

  public function getEmpleadoresCount() {
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT COUNT(pk_usuario)
            FROM tbl_usuarios u
            JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
            JOIN tbl_solicitudesempleadores ts ON u.pk_usuario = ts.fk_usuario 
            WHERE ug.fk_grupo IN (20111,20120)
              {$whereSearch}";
    $results = $this->_db->fetchOne($SQL);

    return $results;
  }

  public function getEmpleadorCount($id) {
    $SQL = "SELECT CASE WHEN COUNT(pk_usuario) > 0 THEN true ELSE false END
            FROM tbl_usuarios u
            JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
            WHERE ug.fk_grupo = 20111
            AND u.pk_usuario = {$id}";
    $results = $this->_db->fetchOne($SQL);

    return $results;
  }

  public function getEmpleadores($itemPerPage, $pageNumber) {
    $pageNumber = ($pageNumber - 1) * $itemPerPage;
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
    $SQL = "SELECT u.pk_usuario,
            LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci, 
            u.nombre, 
            u.apellido, 
            tt.valor as estado, 
            ti.nombre as institucion,
            ts.pk_solicitudempleador
        FROM tbl_usuarios u
        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
        JOIN tbl_solicitudesempleadores ts ON ug.fk_usuario = ts.fk_usuario
        JOIN tbl_atributos tt ON ts.fk_estado = tt.pk_atributo
        JOIN tbl_instituciones ti ON ts.fk_institucion = ti.pk_institucion
        WHERE ug.fk_grupo IN (20111, 20120)
                      {$whereSearch}
        ORDER BY tt.pk_atributo DESC LIMIT {$itemPerPage} OFFSET {$pageNumber};";
    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function getEmpleadorState($id){
    $SQL = "SELECT ts.pk_solicitudempleador,
            ts.fk_estado, 
            tt.valor, 
            ts.fk_institucion
            FROM tbl_usuarios tu
            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
            JOIN tbl_solicitudesempleadores ts ON tg.fk_usuario = ts.fk_usuario
            JOIN tbl_atributos tt ON ts.fk_estado = tt.pk_atributo
            WHERE ts.pk_solicitudempleador = {$id}";
    $results = $this->_db->query($SQL);
    $return = $results->fetchAll();

    return $return[0];
  }


  public function getBecadosSistema($periodo,$cedulas,$sede,$escuela){
    if ($escuela==0) {
      $escuela = '11,12,13,14,15,16';
    }
    if ($sede == 0) {
      $sede = '7,8';
    }
    $SQL = "SELECT *,
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
                    END AS estado,
                     CASE  
                  -- CON LO INSCRITO EL ESTUDIANTE COMPLETA SU FASE DE BECADO 
                    WHEN uc_aprob_mas_inscritas = uc_carrera THEN 'COMPLETO LA CARRERA'
                  -- SALE () ESTABA EN PRUEBA Y VOLVIO A FALLAR
                    WHEN promedio_aprobadas_periodo_anterior < 100 AND  reprobadas_cursantes > 0 THEN 'ESTABA EN PRUEBA Y REPROBO MATERIAS'
                  -- CAE EN PRUEBA
                    WHEN promedio_aprobadas_periodo_actual >= 74 AND reprobadas_cursantes > 0  THEN 'ENTRA EN PRUEBA'
                  -- SALE SU PRIMER PERIODO
                    WHEN promedio_aprobadas_periodo_actual < 74 AND reprobadas_cursantes > 0  THEN 'TIENE MENOS DE 74% REPROBADAS'
                    ELSE 'SE MANTIENE'
                    END AS razon
                   FROM (
                  SELECT  au.pk_usuario, 
                    au.nombre, 
                    apellido,
                    fn_xrxx_estudiante_iap(au.pk_usuario,ai.fk_periodo - 1) AS indice_periodo_anterior,
                    (SELECT count(tr.pk_recordacademico)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_atributo = ai.fk_atributo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_periodo = ai.fk_periodo - 1
                    AND tr.fk_atributo IN (1699,862)
                    AND tr.calificacion < 10) AS asig_reprobadas_periodo_anterior,
                    (SELECT count(tr.pk_recordacademico)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_atributo = ai.fk_atributo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_periodo = ai.fk_periodo -1
                    AND tr.fk_atributo NOT IN (863,1699)
                    AND tr.fk_atributo IN (864,862,861)) AS asig_inscritas_menos_retiradas_periodo_anterior,
                    (SELECT count(tr.pk_recordacademico)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_atributo = ai.fk_atributo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_periodo = ai.fk_periodo - 1
                    AND tr.fk_atributo IN (863)) AS asig_retiradas_periodo_anterior,
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
                  FROM  (SELECT materia,
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
                  (SELECT count(tr.pk_recordacademico)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_atributo = ai.fk_atributo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_periodo = ai.fk_periodo
                    AND tr.fk_atributo NOT IN (863,1699)
                    AND tr.fk_atributo IN (864,862,861)) AS asig_inscritas_menos_retiradas_periodo_actual,
                  (SELECT count(tr.pk_recordacademico)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_atributo = ai.fk_atributo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_periodo = ai.fk_periodo
                    AND tr.fk_atributo IN (863)) AS asig_retiradas_periodo_actual,
                  (SELECT CASE WHEN total = 0 THEN 0 
                      ELSE 
                        round(100-((reprobadas*100)/total),2) 
                      END AS promedio
                    FROM (
                        SELECT (SELECT sum(cantidad)::DECIMAL AS reprobadas
                    FROM  (SELECT materia,
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
                    (SELECT CASE WHEN count(ra4.*) > 0 THEN 'NO' ELSE 'SI' END AS todas_cargadas
                    FROM tbl_recordsacademicos ra4
                    JOIN tbl_inscripciones i4 on i4.pk_inscripcion = ra4.fk_inscripcion
                    JOIN tbl_usuariosgrupos ug4 ON ug4.pk_usuariogrupo = i4.fk_usuariogrupo
                    JOIN tbl_asignaturas a4 ON a4.pk_asignatura = ra4.fk_asignatura
                    WHERE ug4.fk_usuario =  au.pk_usuario
                    AND i4.fk_periodo = ai.fk_periodo
                    AND ra4.fk_atributo = 864) as todas_cargadas,

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
                    AND tp.pk_pensum = ap.pk_pensum) AS uc_carrera,
                    vs.nombre AS sede,
                    ae.escuela, 
                    vsem.valor AS semestre_academico,
                    (SELECT sum(ta.unidadcredito)
                    FROM tbl_usuarios tu
                    JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                    JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                    JOIN tbl_asignaturas ta ON tr.fk_asignatura = ta.pk_asignatura
                    WHERE tu.pk_usuario = au.pk_usuario
                    AND ti.fk_periodo = ai.fk_periodo
                    AND ti.fk_pensum = ai.fk_pensum
                    AND ti.fk_atributo = ai.fk_atributo) AS uc_inscritas_periodo_actual,
                    (SELECT sum(a4.unidadcredito) -- UC semestre de ubicacion
                    from tbl_asignaturas a4
                    JOIN vw_semestres s4 ON s4.pk_atributo = a4.fk_semestre
                    JOIN tbl_pensums p4 ON a4.fk_pensum = p4.pk_pensum
                    WHERE p4.pk_pensum = ai.fk_pensum
                    AND s4.id = (SELECT id -- SEM DE UBICACION
                        FROM  (
                        SELECT  sum(a4.unidadcredito),s4.id
                        FROM tbl_recordsacademicos ra4
                        JOIN tbl_inscripciones i4 on i4.pk_inscripcion = ra4.fk_inscripcion
                        JOIN tbl_usuariosgrupos ug4 ON ug4.pk_usuariogrupo = i4.fk_usuariogrupo
                        JOIN tbl_asignaturas a4 ON a4.pk_asignatura = ra4.fk_asignatura
                        JOIN vw_semestres s4 ON s4.pk_atributo = a4.fk_semestre
                        WHERE fk_usuario = au.pk_usuario
                        AND i4.fk_periodo = ai.fk_periodo
                        GROUP BY s4.id
                        ORDER BY 1 DESC, 2 DESC
                        LIMIT 1  ) as sqt4)) AS uc_periodo_ubicacion
                  FROM tbl_usuarios au 
                  JOIN tbl_usuariosgrupos aug ON aug.fk_usuario = au.pk_usuario 
                  JOIN tbl_inscripciones ai ON ai.fk_usuariogrupo = aug.pk_usuariogrupo
                  JOIN tbl_pensums ap ON ap.pk_pensum = ai.fk_pensum
                  JOIN vw_escuelas ae ON ai.fk_atributo = ae.pk_atributo 
                  JOIN vw_sedes vs ON vs.pk_estructura = ai.fk_estructura
                  JOIN vw_semestres vsem ON ai.fk_semestre = vsem.pk_atributo
                  WHERE ai.fk_periodo = {$periodo}
                  AND  pk_usuario IN ({$cedulas})
                  AND ai.fk_estructura IN ({$sede})
                  AND ai.fk_atributo IN ({$escuela})
                  ORDER BY au.pk_usuario) as sqt_final
                  ORDER BY pk_usuario";
                  //var_dump($SQL);die;
    $results = $this->_db->query($SQL);
    return $results->fetchAll();
  }

  public function getEstudiantesBecadosEscuela($string,$periodo,$sede,$escuela,$itemPerPage, $pageNumber){
    // Set Data
    if ($escuela==0) {
      $escuela = '11,12,13,14,15,16';
    }
    if ($sede==0) {
      $sede = '7,8';
    }
      // Query
      $SQL .= "SELECT distinct  u.pk_usuario AS cedula, 
                                u.nombre, 
                                u.apellido,
                                u.correo, 
                                i.fk_atributo,
                                at2.valor AS escuela,
                                i.fk_estructura,
                                s.nombre AS sede
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                    JOIN tbl_inscripciones i ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                    JOIN tbl_atributos a ON a.pk_atributo = i.fk_estructura
                    JOIN tbl_recordsacademicos r ON i.pk_inscripcion = r.fk_inscripcion
                    JOIN tbl_asignaturas a2 ON a2.pk_asignatura = r.fk_asignatura
                    JOIN tbl_atributos at2 ON at2.pk_atributo = i.fk_atributo
                    JOIN vw_sedes AS s ON s.pk_estructura = i.fk_estructura
                    WHERE u.pk_usuario IN ({$string})
                    AND i.fk_periodo = {$periodo}
                    AND i.fk_estructura in ({$sede})
                    AND i.fk_atributo in ({$escuela})";
    if($itemPerPage!=null){
        $SQL .= "\n ORDER BY cedula \n LIMIT {$itemPerPage} OFFSET {$pageNumber}";
    }else{
      $SQL .= "ORDER BY cedula";
    }
    //var_dump($SQL);die;
    $results = $this->_db->query($SQL);
    $return = $results->fetchAll();

    return $return;


  }
  
public function tesisalumnos($periodo, $sede, $escuela, $materia){
      $SQL = "SELECT distinct dt.pk_datotesis,u.nombre, u.apellido, u.correo, dt.titulo, atr3.valor as estadotesis, 
              case 
              when u2.nombre is null then 'No tiene tutor' else (u2.nombre|| ' ' || u2.apellido ) 
              end as nombre_tutor, 
            case 
              when atr2.valor is null then 'Por aprobar' else atr2.valor
              end as tutor, dt.estado_planilla, u.pk_usuario  
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and asna.pk_asignatura = {$materia}
              order by estadotesis, dt.titulo";

      $results = $this->_db->query($SQL);
      $return = $results->fetchAll();

      return $return;
    }



   public function revisionTutor($ci){
      $SQL = "SELECT distinct u.pk_usuario,dt.titulo as titulo, atr.valor as estado_tesis,  dt.estado_planilla as planilla,
              case 
              when u2.nombre is null then 'No tiene tutor' else (u2.primer_nombre|| ' ' || u2.primer_apellido ) 
              end as nombre_tutor,
              case 
              when atr2.valor is null then 'Por aprobar' else atr2.valor
              end as tutor    
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              /* u. son datos del usuario u2. son datos del tutor */
              where  u.pk_usuario = {$ci}";


     $results = $this->_db->query($SQL);
      return (array)$results->fetchAll();
    }



public function tesisalumnosdes($periodo, $sede, $escuela, $materia){
      $SQL = "SELECT distinct u.nombre, u.apellido, u.correo, dt.titulo, atr3.valor as estadotesis, 
              case 
              when u2.nombre is null then 'No tiene tutor' else (u2.nombre|| ' ' || u2.apellido ) 
              end as nombre_tutor,
            case 
              when atr2.valor is null then 'Por aprobar' else atr2.valor
              end as tutor    
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and asna.pk_asignatura = {$materia}
              and dt.fk_estado <> 19962 
              order by dt.titulo";

           


      $results = $this->_db->query($SQL);
    return (array)$results->fetchAll();
    }

public function aprobaciontesis($periodo, $ci){
      $SQL = "SELECT distinct  u.pk_usuario,u.nombre, u.apellido,u.correo, dt.titulo, atr3.valor as estadotesis,
            case 
              when u2.nombre is null then 'No tiene tutor' else (u2.nombre|| ' ' || u2.apellido ) 
              end as nombre_tutor,
            case 
              when atr2.valor is null then 'Por aprobar' else atr2.valor
              end as tutor 
             
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
  
              where  t.fk_periodo = {$periodo}
              and u2.pk_usuario = {$ci}
              order by estadotesis , dt.titulo";

           
              

      $results = $this->_db->query($SQL);
      return (array)$results->fetchAll();

    }

 public function cuentatesisalumnos($periodo, $sede, $escuela, $materia){
      $SQL = "SELECT distinct count(dt.fk_estado)     
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and asna.pk_asignatura = {$materia}
              and dt.fk_estado = 19962
              UNION ALL
              SELECT distinct count(dt.fk_estado)
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and asna.pk_asignatura = {$materia}
              and dt.fk_estado <> 19962
              UNION ALL
              SELECT distinct count(dt.fk_estado)         
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and asna.pk_asignatura = {$materia}
              and tt.fk_estado = 19969
              UNION ALL
              SELECT distinct count(dt.fk_estado)                
              from tbl_datostesis dt
              full join tbl_autorestesis aut on aut.fk_datotesis = dt.pk_datotesis
              join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = aut.fk_usuariogrupo
              join tbl_inscripciones ins on ins.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_recordsacademicos ra on ra.fk_inscripcion = ins.pk_inscripcion 
              join tbl_asignaturas asna on asna.pk_asignatura = ra.fk_asignatura
              join tbl_tesis t on t.fk_datotesis = dt.pk_datotesis
              join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
              left join tbl_tutorestesis tt on tt.fk_datotesis = dt.pk_datotesis
              left join tbl_usuariosgrupos ug2 on ug2.pk_usuariogrupo = tt.fk_usuariogrupo
              left join tbl_usuarios u2 on u2.pk_usuario = ug2.fk_usuario
              join tbl_atributos atr on atr.pk_atributo = dt.fk_estado
              left join tbl_atributos atr2 on atr2.pk_atributo = tt.fk_estado
              join tbl_atributos atr3 on atr3.pk_atributo = dt.fk_estado
              join vw_materias m on m.pk_atributo = asna.fk_materia
              where  ins.fk_periodo = {$periodo}
              and t.fk_sede = {$sede}
              and t.fk_escuela = {$escuela}
              and  asna.pk_asignatura = {$materia}
              and ((tt.fk_estado is null) or (tt.fk_estado = 19971))";

      $results = $this->_db->query($SQL);
      $return = $results->fetchAll();

      return $return;
    }   

/*------------------------------------------------------------------------*/
public function checkplanilla($pktesis){
      $SQL = "UPDATE tbl_datostesis
              SET estado_planilla = 't'
              WHERE pk_datotesis in ({$pktesis})";
      $results = $this->_db->query($SQL);
    }
/*------------------------------------------------------------------------*/
public function ofertahoraria($periodo, $pensum, $escuela, $ci, $sede){
 /*query oferta horario solo las materias que puedes meter*/
$SQL =   "SELECT  tt1.valor as materia,
          ts.unidadcredito as UC,
          ts.fk_semestre,
          tt5.valor as turno,
          tt3.valor as seccion,
          ta1.nota,
          'Prof. ' || tu.nombre || ' ' || tu.apellido as profesor,
          th.horainicio || ' a ' || th.horafin as hora,
          te1.nombre || ' - ' || te2.nombre as ubicacion,
          tt2.valor as dia,
          tt4.valor as semestre
          FROM tbl_asignaturas ts 
          JOIN tbl_asignaciones ta1 ON ts.pk_asignatura = ta1.fk_asignatura
          JOIN tbl_pensums tp1 ON ts.fk_pensum = tp1.pk_pensum
          JOIN tbl_horarios th ON ta1.fk_horario = th.pk_horario
          JOIN tbl_usuariosgrupos tg ON ta1.fk_usuariogrupo = tg.pk_usuariogrupo
          JOIN tbl_usuarios tu ON tg.fk_usuario = tu.pk_usuario
          JOIN tbl_estructuras te1 ON ta1.fk_estructura = te1.pk_estructura
          JOIN tbl_estructuras te2 ON te1.fk_estructura = te2.pk_estructura
          JOIN tbl_atributos tt1 ON ts.fk_materia = tt1.pk_atributo
          JOIN tbl_atributos tt2 ON ta1.fk_dia = tt2.pk_atributo
          JOIN tbl_atributos tt3 ON ta1.fk_seccion = tt3.pk_atributo
          JOIN tbl_atributos tt4 ON ts.fk_semestre = tt4.pk_atributo
          JOIN tbl_atributos tt5 on ta1.fk_turno = tt5.pk_atributo
          WHERE tp1.pk_pensum = {$pensum}
          AND tp1.fk_escuela = {$escuela}
          AND ta1.fk_periodo = {$periodo}
          AND te2.fk_estructura = {$sede}
          /* Materias Aprobadas por el Estudiante */
          AND ts.pk_asignatura NOT IN (SELECT ts.pk_asignatura
          FROM tbl_usuariosgrupos tg 
          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
          WHERE tg.fk_usuario = {$ci}
          AND ts.fk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND ti.fk_estructura = {$sede}
          AND tr.calificacion > 9
          AND tr.fk_atributo IN (862))
          /* Materias Reconocidas al Estudiante */
          AND ts.pk_asignatura NOT IN (SELECT ta.pk_asignatura
          FROM tbl_recordsacademicos tr
          JOIN tbl_asignaturas ta ON tr.fK_asignatura = ta.pK_asignatura
          JOIN tbl_inscripciones ti ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_usuariosgrupos tg ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_pensums tp ON tp.pk_pensum = ta.fk_pensum
          WHERE tg.fk_usuario = {$ci}
          AND tp.pk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND tr.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
          /* Materias Inscritas por el Estudiante */
          AND ts.pk_asignatura NOT IN (SELECT ts.pk_asignatura
          FROM tbl_usuariosgrupos tg 
          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
          WHERE tg.fk_usuario = {$ci}
          AND ts.fk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND ti.fk_estructura = {$sede}
          AND tr.fk_atributo IN (864))
          /* Materias q' no puede cursar el Estudiante por Prelacion */
          AND ts.pk_asignatura NOT IN (SELECT ts.pk_asignatura
          FROM tbl_asignaturas ts 
          JOIN tbl_pensums tp ON ts.fk_pensum = tp.pk_pensum
          JOIN tbl_prelaciones tpr ON ts.pk_asignatura = tpr.fk_asignatura
          WHERE tp.pk_pensum = {$pensum}
          AND tp.fk_escuela = {$escuela}
          AND tpr.unidadescredito <= (SELECT SUM(ts.unidadcredito) /* U.C. */
          FROM tbl_usuariosgrupos tg 
          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
          WHERE tg.fk_usuario = {$ci}
          AND ts.fk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND ti.fk_estructura = {$sede}
          AND (tr.calificacion > 9 AND tr.fk_atributo IN (862)
          OR tr.fk_atributo IN (9691,9692,1266,1265,1264)))
          AND tpr.fk_asignaturaprelada NOT IN (SELECT ts.pk_asignatura /* Materia Prelada */
          FROM tbl_usuariosgrupos tg 
          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
          WHERE tg.fk_usuario = {$ci}
          AND ts.fk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND ti.fk_estructura = {$sede}
          AND tr.calificacion > 9
          AND tr.fk_atributo IN (862))) 
          AND ts.pk_asignatura NOT IN (SELECT ts.pk_asignatura
          FROM tbl_asignaturas ts 
          JOIN tbl_pensums tp ON ts.fk_pensum = tp.pk_pensum
          JOIN tbl_prelaciones tpr ON ts.pk_asignatura = tpr.fk_asignatura
          join tbl_atributos atr on atr.pk_atributo = ts.fk_materia
          WHERE tp.pk_pensum = {$pensum}
          AND tp.fk_escuela = {$escuela}
          AND tpr.unidadescredito > (SELECT SUM(ts.unidadcredito) /* UC */
          FROM tbl_usuariosgrupos tg 
          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
          JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
          JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
          WHERE tg.fk_usuario = {$ci}
          AND ts.fk_pensum = {$pensum}
          AND ti.fk_atributo = {$escuela}
          AND ti.fk_estructura = {$sede}
          AND (tr.calificacion > 9 AND tr.fk_atributo IN (862)
          OR tr.fk_atributo IN (9691,9692,1266,1265,1264))))
          ORDER BY ts.fk_semestre, ts.pk_asignatura, ta1.fk_seccion, ta1.fk_dia, th.pk_horario ASC";

      $results = $this->_db->query($SQL);
      $return = $results->fetchAll();

      return $return;
  }

  public function getMateriasPendientes($ci,$periodo,$sede,$escuela,$pensum){
    $SQL = "SELECT *
            FROM (
                  SELECT  sqt_pensum.codigo,
                          sqt_pensum.materia, sqt_pensum.fk_materia, 
                          CASE WHEN sqt_cursado.fk_atributo=864 THEN 'Inscrita'
                               WHEN ( 
          (sqt_pensum.fk_materia IN (518,1412,830,834,1384,10621,1385,9719,9723,9724)
                                        AND sqt_cursado.fk_atributo=862
                                        and sqt_cursado.fk_pensum between 20 and 25
                                        AND sqt_cursado.calificacion>=15
                                      )
                                    OR sqt_cursado.fk_atributo IN (861,9691,9692,1266,1265,1264)
                                    OR (sqt_cursado.fk_atributo=862
                                        AND sqt_cursado.calificacion>=10
                                        )
                                    )
                               THEN 'Aprobada'
                               WHEN ( (sqt_pensum.fk_materia IN (518,1412,830,834,1384,10621,1385,9719,9723,9724)
                                        AND sqt_cursado.fk_atributo=862 
                                        and sqt_cursado.fk_pensum between 20 and 25
                                        AND sqt_cursado.calificacion<15
                                      )
                                    OR sqt_cursado.fk_atributo = 1699
                                    OR (sqt_cursado.fk_atributo=862 
                                        AND sqt_cursado.calificacion<10
                                       )
                                    )
                               THEN 'Reprobada'
                               WHEN sqt_cursado.fk_atributo=863 THEN 'Retirada'
                               WHEN sqt_cursado.fk_atributo IS NULL THEN 'Pendiente'
                               END AS estado,
                          CASE WHEN sqt_cursado.fk_atributo=864 THEN 0
                               WHEN ( (sqt_pensum.fk_materia IN (518,1412,830,834,1384,10621,1385,9719,9723,9724) 
                                        AND sqt_cursado.fk_atributo=862
                                        and sqt_cursado.fk_pensum between 20 and 25
                                        AND sqt_cursado.calificacion>=15
                                      )
                                    OR sqt_cursado.fk_atributo IN (861,9691,9692,1266,1265,1264)
                                    OR (sqt_cursado.fk_atributo=862 
                                        AND sqt_cursado.calificacion>=10
                                       )
                                    )
                               THEN 4
                               WHEN ( (sqt_pensum.fk_materia IN (518,1412,830,834,1384,10621,1385,9719,9723,9724) 
                                        AND sqt_cursado.fk_atributo=862
                                        and sqt_cursado.fk_pensum between 20 and 25
                                        AND sqt_cursado.calificacion<15
                                      )
                                    OR sqt_cursado.fk_atributo = 1699
                                    OR (sqt_cursado.fk_atributo=862 
                                        AND sqt_cursado.calificacion<10
                                        )
                                    ) 
                               THEN 2
                               WHEN sqt_cursado.fk_atributo=863 THEN 1
                               WHEN sqt_cursado.fk_atributo IS NULL THEN 3
                               END AS codigoestado,
                          sqt_pensum.valor,
                          sqt_pensum.uc,
                          sqt_pensum.prelacion,
                          sqt_pensum.prelacionuc,
                          sqt_pensum.id,
                          sqt_cursado.fk_pensum
                    FROM (  /*PENSUM CON PRELACONES*/
                          SELECT a.codigopropietario AS codigo,
                                 m.materia, 
                                 a.fk_materia,
                                 'Pendiente'::VARCHAR AS estado,
                                 s.valor,
                                 a.unidadcredito AS uc,
                                 CASE WHEN (array_to_string(ARRAY_AGG(m2.materia),', ') IS NULL OR array_to_string(ARRAY_AGG(m2.materia),', ')='')
                                      THEN 'N/A'
                                      ELSE array_to_string(ARRAY_AGG(m2.materia),', ')
                                      END As prelacion,
                                 CASE WHEN p.unidadescredito IS NULL
                                      THEN 0
                                      ELSE p.unidadescredito
                                      END AS prelacionuc,
                                 s.id
                          FROM tbl_asignaturas a 
                          FULL OUTER JOIN tbl_prelaciones p        on p.fk_asignatura    = a.pk_asignatura
                          LEFT OUTER JOIN vw_materias m            on m.pk_atributo      = a.fk_materia
                          LEFT OUTER JOIN tbl_asignaturas asi      on asi.pk_asignatura  = p.fk_asignaturaprelada
                          LEFT OUTER JOIN vw_materias m2           on m2.pk_atributo     = asi.fk_materia
                          LEFT OUTER JOIN vw_semestres s           on s.pk_atributo      = a.fk_semestre
                          WHERE a.fk_pensum = {$pensum}
                          AND a.fk_materia not in (1701, 894,907)   --P.I.R.A., BIBLIOTECA, PREPARADURIA
                          --AND asi.codigopropietario NOT IN ('07000000', '06000000')
                          GROUP BY a.codigopropietario, m.materia, s.valor, a.unidadcredito, p.unidadescredito, s.id,a.fk_materia
                          ORDER BY 2, s.id
                        ) as sqt_pensum
                        FULL JOIN ( /*MATERIAS CURSADAS*/
                                  SELECT *
                                  FROM(
                                      SELECT DISTINCT a.fk_materia, m.materia, i.fk_pensum,
                                                      CASE WHEN min(i.fk_periodo)=0 THEN 0 
                                                      ELSE max(i.fk_periodo)
                                                      END AS periodo
                                      FROM tbl_asignaturas a
                                      JOIN tbl_recordsacademicos ra ON a.pk_asignatura    = ra.fk_asignatura
                                      JOIN tbl_inscripciones i      ON i.pk_inscripcion   = ra.fk_inscripcion
                                      JOIN tbl_usuariosgrupos ug    ON ug.pk_usuariogrupo = i.fk_usuariogrupo 
                                      JOIN vw_materias m on m.pk_atributo = a.fk_materia
                                      WHERE ug.fk_usuario= {$ci}
                                      AND a.fk_pensum = {$pensum}
                                      AND a.fk_materia not in (1701, 894,907)   --P.I.R.A., BIBLIOTECA, PREPARADURIA
                                      GROUP BY a.fk_materia, m.materia, i.fk_pensum
                                      ) AS sqt
                                  JOIN (
                                      SELECT distinct a.fk_materia, m.materia, i.fk_periodo, ra.calificacion, ra.fk_atributo, a.codigopropietario
                                      FROM tbl_asignaturas a
                                      JOIN tbl_recordsacademicos ra ON a.pk_asignatura    = ra.fk_asignatura
                                      JOIN tbl_inscripciones i      ON i.pk_inscripcion   = ra.fk_inscripcion
                                      JOIN tbl_usuariosgrupos ug    ON ug.pk_usuariogrupo = i.fk_usuariogrupo 
                                      JOIN vw_materias m on m.pk_atributo = a.fk_materia
                                      WHERE ug.fk_usuario= {$ci}
                                      AND a.fk_pensum = {$pensum}
                                      AND a.fk_materia not in (1701, 894,907)   --P.I.R.A., BIBLIOTECA, PREPARADURIA
                                       ) AS sqt2 ON (sqt.periodo = sqt2.fk_periodo AND sqt.fk_materia =sqt2.fk_materia)
                                  ) AS sqt_cursado ON sqt_pensum.codigo = sqt_cursado.codigopropietario
                        GROUP BY sqt_pensum.codigo,sqt_pensum.materia, sqt_pensum.valor,sqt_pensum.uc, sqt_pensum.id,sqt_cursado.fk_atributo,sqt_cursado.calificacion,sqt_pensum.prelacion,sqt_pensum.prelacionuc,sqt_pensum.fk_materia,sqt_cursado.fk_pensum
                        ORDER BY ID
                        ) AS sqt_faltantes
            WHERE sqt_faltantes.codigoestado != 4
            ORDER BY sqt_faltantes.codigoestado, sqt_faltantes.id;";
    $results = $this->_db->query($SQL);
    $return = $results->fetchAll();

    return $return;
  }

  public function uploadPicture($file,$id){
    //Data File
    $tmp   = $file['tmp_name'];
    $name  = $file['name'];
    $type  = $file['type'];
    $size  = $file['size'];
    $error = $file['error'];
    //Read File
    $open = fopen($tmp,'r+b');
    $data = fread($open,filesize($tmp));
    $data = pg_escape_bytea($data);
    fclose($open);
    //Query
    $config = $this->_db->getConfig();
    $conn   = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
    $SQL  = "UPDATE tbl_usuarios 
              SET foto = '{$data}'
              WHERE pk_usuario = {$id}";
    $query  = pg_query($conn,$SQL);
    pg_close($conn);
  }

    public function getPicture($id){
      $config = $this->_db->getConfig();
      $conn   = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
      $SQL    = "SELECT foto 
                 FROM tbl_usuarios 
                 WHERE pk_usuario = {$id}";
      $query  = pg_query($conn,$SQL);
      $row    = pg_fetch_row($query);
      $image  = pg_unescape_bytea($row[0]);
      pg_close($conn);
      return $image;
    }

	public function getEgresadosPeriodo($periodo){

	$SQL = "SELECT tu.pk_usuario, tu.apellido, tu.nombre, escuela.valor,
	fn_xrxx_estudiante_iia_escuela_periodo_articulado(tu.pk_usuario,
                    tp.fk_escuela,
                    ti.fk_periodo,
                    tp.codigopropietario) as indice,
      te.nombre as sede,
      fn_xrxx_estudiante_calcular_uc_total(tu.pk_usuario,tp.pk_pensum,tp.fk_escuela,te.pk_estructura)as uc
    from tbl_usuarios tu
    join tbl_usuariosgrupos tug on tu.pk_usuario = tug.fk_usuario
    join tbl_inscripciones ti on tug.pk_usuariogrupo = ti.fk_usuariogrupo
    join tbl_pensums tp on ti.fk_pensum = tp.pk_pensum
    join tbl_atributos escuela on tp.fk_escuela = escuela.pk_atributo
    join tbl_estructuras te on ti.fk_estructura = te.pk_estructura
    where tu.pk_usuario in (
      select tug2.fk_usuario from tbl_usuariosgrupos tug2
      join tbl_inscripciones ti2 on tug2.pk_usuariogrupo = ti2.fk_usuariogrupo
      join tbl_recordsacademicos tr2 on ti2.pk_inscripcion = tr2.fk_inscripcion
      where tr2.fk_atributo = 862 and tr2.fk_asignatura IN (	14046,
                    13801,
                    14115,
                    13880,
                    13308,
                    13155
                  )
      AND tr2.calificacion >= 15 and ti2.fk_periodo = ti.fk_periodo
    ) and ti.fk_periodo = {$periodo}
    group by tu.pk_usuario, escuela.valor, tp.fk_escuela, ti.fk_periodo, tp.codigopropietario, te.nombre, tp.pk_pensum, te.pk_estructura, tug.fk_usuario, tu.apellido, tu.nombre 
    having (SELECT  COUNT(pk_asignatura) AS materias
                                FROM tbl_asignaturas
                                WHERE fk_pensum     = tp.pk_pensum
                                AND pk_asignatura NOT IN (SELECT DISTINCT pk_asignatura
                                                          FROM tbl_recordsacademicos ra
                                                          JOIN tbl_inscripciones i   ON i.pk_inscripcion     = ra.fk_inscripcion
                                                          JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo   = i.fk_usuariogrupo
                                                          JOIN tbl_asignaturas asi   ON asi.pk_asignatura    = ra.fk_asignatura
                                                          JOIN tbl_atributos a       ON a.pk_atributo        = ra.fk_atributo
                                                          WHERE ug.fk_usuario  = tug.fk_usuario
                                                          AND ((ra.fk_atributo = 862 AND ra.calificacion>=10)
                                                          OR ra.fk_atributo = 1264 
                                                          OR ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos))
                                                          AND asi.fk_pensum   = tp.pk_pensum)
                                AND fk_materia  not in (907,894, 1701)
       ) = 0
    
    order by sede, escuela.valor, tu.pk_usuario;
    ";
  
  
      $results = $this->_db->query($SQL);
  
      return (array)$results->fetchAll();
  }

}

