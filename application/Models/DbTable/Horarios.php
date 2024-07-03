<?php
class Models_DbTable_Horarios extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_horarios';
    protected $_primary  = 'pk_horario';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect($Turno = NULL) {
  		if($Turno == NULL) {
  			$SQL = "SELECT {$this->_primary}, TO_CHAR(horainicio, 'hh:mi') || ' / ' || TO_CHAR(horafin, 'hh:mi') AS horario
  					FROM {$this->_name}
  					WHERE pk_horario <> 11
  					ORDER BY 1";
  		} else {
  			$SQL = "SELECT {$this->_primary}, TO_CHAR(horainicio, 'hh:mi') || ' / ' || TO_CHAR(horafin, 'hh:mi') AS horario
  					FROM {$this->_name}
  					WHERE fk_atributo = {$Turno}
  					ORDER BY 2";
  		}
          $results = $this->_db->query($SQL);

          return (array)$results->fetchAll();
      }

    /**
     * Obtiene un registro en especifico
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

    public function getHorariosDetalle($Periodo, $Valores) {
        if(empty($Periodo)) return;
        if(empty($Valores)) return;
        
        $SQL     = " SELECT DISTINCT *, nombre || ', ' || apellido as profesor FROM fn_xrxx_horarios_detalle_nuevo($Periodo,
        '{{{$Valores}}}'::int[][][])
						                                 AS(pagina INT,
							                                 Grupo INT,
	                                                   Columna INT,
	                                                   SubColumna INT,
                                                      posicion INTEGER,
                                                      iddia INT2,
							                                 dia VARCHAR(45),
                                                      idhora INTEGER,
	                                                   horainicio TIME,
	                                                   horafin TIME,
	                                                   Materia VARCHAR(45),
	                                                   Seccionmadre INTEGER,
	                                                   Seccion VARCHAR(45),
	                                                   Nombre VARCHAR(45),
	                                                   Apellido VARCHAR(45),
	                                                   Edificio VARCHAR(45),
	                                                   Salon VARCHAR(45),
	                                                   Nota VARCHAR(20),
	                                                   Semestre INT2,
	                                                   SeccionID INT8,
	                                                   SemestreID INT8,
							                                 TurnoID INT8,
	                                                   id INT2,
	                                                   escuela VARCHAR,
	                                                   turnossss VARCHAR,
	                                                   codesem VARCHAR,
	                                                   sede VARCHAR,
	                                                   periodo VARCHAR)
	                                                ORDER BY pagina, grupo, columna, id, horainicio, semestre, seccionmadre, seccion, posicion;
";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        // return $SQL;
    }


    public function getCuposRestantes($periodo,$sede,$escuela,$semestre,$seccion){


        $SQL = "SELECT pk_asignatura, materia,cupos * mod - insc as disp
                FROM
                (
                SELECT pk_asignatura, materia, cupos, CASE WHEN sum(subsec) = 0 THEN 1 ELSE sum(subsec) END AS mod , SUM(inscritos) as insc
                FROM
                                (
                                SELECT distinct ag.pk_asignatura,
                                                aon.pk_asignacion,
                                                ma.materia,
                                                sec1.valor,
                                                cupos,

                                                (SELECT count(distinct pk_asignacion)
                                                 FROM(
                                                 SELECT MAX(pk_asignacion) as pk_asignacion
                                                 FROM tbl_asignaciones aon1
                                                 JOIN vw_secciones sec2 ON sec2.pk_atributo = aon1.fk_seccion
                                                 WHERE aon1.fk_asignatura = aon.fk_asignatura
                                                 AND sec2.pk_atributo <> {$seccion}
                                                 )as sqt1 ) as subsec,

                                                (select count(distinct ra.pk_recordacademico)
                                                FROM tbl_recordsacademicos ra
                                                WHERE ra.fk_asignacion = aon.pk_asignacion) as inscritos              -- pk_recordacademico
                                                FROM tbl_asignaciones aon
                                                JOIN tbl_asignaturas ag ON ag.pk_asignatura = aon.fk_asignatura
                                                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                                                JOIN tbl_pensums pe ON pe.pk_pensum = ag.fk_pensum
                                                JOIN vw_secciones sec1 ON sec1.pk_atributo = aon.fk_seccion
                                                -- JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = aon.pk_asignacion
                                                JOIN tbl_estructuras est ON est.pk_estructura = aon.fk_estructura
                                                JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura
                                                WHERE aon.fk_periodo = {$periodo}
                                                  AND aon.fk_semestre = {$semestre}
                                                  AND sec1.valor ilike (SELECT valor || '%' as seccion
                                                                        FROM vw_secciones sec
                                                                        WHERE sec.pk_atributo = {$seccion})
                                                  AND pe.fk_escuela = {$escuela}
                                                  AND pe.pk_pensum IN (20, 21, 22, 23, 24, 25, 38)
                                                  AND est1.fk_estructura = {$sede}
                                                ORDER by 3 ASC
                                )as sqt
                GROUP BY  1,2,3
                ) as sqt2
                ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getCuposMaterias($periodo,$sede,$escuela,$semestre,$seccion,$pensum){



        $SQL = "SELECT MAX(pk_asignacion), materia, valor, cupos, SUM(inscritos) as inscritos, cupos - SUM(inscritos) as restantes
                FROM
                (
                SELECT distinct ag.pk_asignatura,
                                aon.pk_asignacion,
                                ma.materia,
                                sec1.valor,
                                cupos,
                                (select count(distinct ra.pk_recordacademico)
                                FROM tbl_recordsacademicos ra
                                WHERE ra.fk_asignacion = aon.pk_asignacion) as inscritos              -- pk_recordacademico
                                FROM tbl_asignaciones aon
                                JOIN tbl_asignaturas ag ON ag.pk_asignatura = aon.fk_asignatura
                                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                                JOIN tbl_pensums pe ON pe.pk_pensum = ag.fk_pensum
                                JOIN vw_secciones sec1 ON sec1.pk_atributo = aon.fk_seccion
                                -- JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = aon.pk_asignacion
                                JOIN tbl_estructuras est ON est.pk_estructura = aon.fk_estructura
                                JOIN tbl_estructuras est1 ON est1.pk_estructura = est.fk_estructura
                                WHERE aon.fk_periodo = {$periodo}
                                  AND aon.fk_semestre = {$semestre}
                                  AND sec1.valor ilike (SELECT valor || '%' as seccion
                                                        FROM vw_secciones sec
                                                        WHERE sec.pk_atributo = {$seccion})
                                  AND pe.fk_escuela = {$escuela}
                                  AND pe.pk_pensum = {$pensum}
                                  AND est1.fk_estructura = {$sede}
                                  AND ag.fk_materia <>1701
                                ORDER by 3 ASC
                )as sqt
                GROUP BY 2,3,4
                ORDER BY 2;
                ";

           $results = $this->_db->query($SQL);
           $results = $results->fetchAll();

        return $results;


    }


    public function getNombreMateria($asignacion){

        $SQL = "SELECT ma.materia
                FROM tbl_asignaciones aon
                JOIN tbl_asignaturas ag ON ag.pk_asignatura = aon.fk_asignatura
                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                WHERE aon.pk_asignacion = {$asignacion};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function validarSecciones($periodo,$semestre,$escuela,$sede,$seccion){

        $SQL = "SELECT *
                FROM (
                SELECT CASE WHEN cantidad > 1 THEN (SELECT mat.materia
                                                    FROM tbl_asignaturas ag1
                                                    JOIN vw_materias mat ON mat.pk_atributo = ag1.fk_materia
                                                    WHERE ag1.pk_asignatura = foo.pk_asignatura)
                                              END as materia
                FROM (
                select pk_asignatura, COUNT(pk_asignatura) as cantidad
                FROM (
                select distinct ag.pk_asignatura, length(sec.valor), ma.materia
                from tbl_asignaciones aon
                JOIN vw_secciones sec ON sec.pk_atributo = aon.fk_seccion
                JOIN tbl_asignaturas ag ON ag.pk_asignatura = aon.fk_asignatura
                JOIN tbl_pensums pe ON pe.pk_pensum = ag.fk_pensum
                JOIN tbl_estructuras est ON est.pk_estructura = aon.fk_estructura
                JOIN tbl_estructuras est2 ON est2.pk_estructura = est.fk_estructura
                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                WHERE aon.fk_periodo = {$periodo}
                  AND aon.fk_semestre = {$semestre}
                  AND pe.fk_escuela = {$escuela}
                  AND est2.fk_estructura = {$sede}
                  AND pe.pk_pensum IN(20, 21, 22, 23, 24, 25, 38)
                  AND sec.valor ilike '{$seccion}'||'%'
                  AND ag.pk_asignatura <> 14127
                  order by 1 DESC
                ) as sqt
                GROUP BY pk_asignatura
                ) as foo
                )as foo2
                where materia is not null

                ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getHorarioPersona($id, $periodo, $quien, $sede){

        if($quien == 'prof') {
          if ($id == NULL) {
            $id=0;
          }
            $SQL ="SELECT distinct sqt1.pk_atributo, 
                    sqt1.horainicio,
                    sqt1.horafin, 
                    sqt1.dia, array_to_string(array(
                      SELECT fo.materia||' '||fo.lugar as materia
                          FROM
                          (select pk_atributo, ho1.horainicio,ho1.horafin,dia
                           from vw_dias
                           CROSS JOIN tbl_horarios ho1
                           where ho1.fk_atributo < 892
                           and pk_horario not in(9,10,12,13)
                           and pk_atributo < 7
                           order by pk_atributo, ho1.horainicio) as foo2 full outer join
                          (
                          select hora,
                           lugar,
                           TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                           diaint,
                           horaint

                          FROM fn_xrxx_incio_horario_profesor($periodo,$id) as (hora time, dia VARCHAR, lugar TEXT, sede integer, materia text[], diaint bigint, horaint bigint)
                          WHERE sede = $sede
                          order by 4,5
                          ) as fo ON (foo2.horainicio = fo.hora AND foo2.pk_atributo = fo.diaint)
                          where sqt1.pk_atributo = foo2.pk_atributo
                          and sqt1.horainicio = foo2.horainicio
                          and sqt1.horafin = foo2.horafin
                          and sqt1.dia = foo2.dia
                          order by foo2.pk_atributo,foo2.horainicio),' ' ) as materia
                  from 
                    (SELECT foo1.pk_atributo, 
                      foo1.horainicio,
                      foo1.horafin, 
                      foo1.dia, 
                      foo.materia||' '||foo.lugar as materia
                            FROM
                            (select pk_atributo, ho.horainicio,ho.horafin,dia
                             from vw_dias
                             CROSS JOIN tbl_horarios ho
                             where ho.fk_atributo < 892
                             and pk_horario not in(9,10,12,13)
                             and pk_atributo < 7
                             order by pk_atributo, ho.horainicio) as foo1 full outer join
                            (
                            select hora,
                             lugar,
                             TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                             diaint,
                             horaint

                            FROM fn_xrxx_incio_horario_profesor($periodo,$id) as (hora time, dia VARCHAR, lugar TEXT, sede integer, materia text[], diaint bigint, horaint bigint)
                            WHERE sede = $sede
                            order by 4,5
                            ) as foo ON (foo1.horainicio = foo.hora AND foo1.pk_atributo = foo.diaint)

                            order by foo1.pk_atributo,foo1.horainicio)as sqt1
                  order by sqt1.pk_atributo,sqt1.horainicio";
//var_dump($SQL);die;


        }elseif($quien == 'est') {

          if($sede==7){

        $SQL = "SELECT foo1.pk_atributo, foo1.horainicio, foo.dia, foo.lugar, foo.materia, foo.turnoreal, foo1.fk_atributo, foo.prof
                    FROM
                    (select pk_atributo, ho.horainicio, ho.fk_atributo
                     from vw_dias
                     CROSS JOIN tbl_horarios ho
                     where ho.fk_atributo < 892
                       and pk_atributo < 7
                       and pk_horario not in(9,10,12,13)
                     order by pk_atributo, ho.horainicio
                     ) as foo1 left outer join
                     (
                        SELECT hora,
                               dia,
                               lugar,
                               TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                               diaint,
                               horaint,
                               turnoreal,
                               prof

                        FROM fn_xrxx_inicio_horario_estudiante({$periodo}, {$id}, {$sede})
                     as (hora time, dia VARCHAR, lugar TEXT, materia text[], diaint bigint, horaint bigint, turnoreal integer, prof TEXT)
                  ) as foo ON (foo1.horainicio = foo.hora AND foo1.pk_atributo = foo.diaint)  ;";
            }else{

              $SQL = "SELECT foo1.pk_atributo, foo1.horainicio, foo.dia, foo.lugar, foo.materia, foo.turnoreal, foo1.fk_atributo, foo.prof
                    FROM
                    (select pk_atributo, ho.horainicio, ho.fk_atributo
                     from vw_dias
                     CROSS JOIN tbl_horarios ho
                     where ho.fk_atributo < 892
                       and pk_atributo < 7  
                     order by pk_atributo, ho.horainicio
                     ) as foo1 left outer join
                     (
                        SELECT hora,
                               dia,
                               lugar,
                               TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '\"' , ' ')) as materia,
                               diaint,
                               horaint,
                               turnoreal,
                               prof

                        FROM fn_xrxx_inicio_horario_estudiante({$periodo}, {$id}, {$sede})
                     as (hora time, dia VARCHAR, lugar TEXT, materia text[], diaint bigint, horaint bigint, turnoreal integer, prof TEXT)
                  ) as foo ON (foo1.horainicio = foo.hora AND foo1.pk_atributo = foo.diaint)  ;";  

            }   
        }

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
    
        return $results;

    }


    public function getHorarioEstudianteProfesor($id, $periodo, $sede) {
      
      if (!$sede) {
      
        $sede=0;
      }

      $SQL = "SELECT 
              foo1.pk_atributo, 
              foo1.horainicio,
              foo.dia, 
              foo.lugar, 
              foo.materia, 
              foo.turnoreal, 
              foo1.fk_atributo, 
              foo.prof
              FROM
                (select pk_atributo, ho.horainicio, ho.fk_atributo
                from vw_dias
                CROSS JOIN tbl_horarios ho
                where ho.fk_atributo < 892
                and pk_horario not in(9,10,12,13)
                and pk_atributo < 7
                order by pk_atributo, ho.horainicio
                ) as foo1 
                left outer join(
                  (
                  SELECT hora,dia,lugar,
                  TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '' , ' ')) as materia,
                  diaint, horaint, turnoreal, prof
                  FROM fn_xrxx_inicio_horario_estudiante({$periodo}, {$id}, {$sede})
                  as (hora time, dia VARCHAR, lugar TEXT, materia text[], diaint bigint, horaint bigint, turnoreal integer, prof TEXT)
                  
                  )
                  union
                  (select hora, dia, lugar,
                  TRIM( both ' ' FROM REPLACE(trim(both '{}' FROM materia::text), '' , ' ')) as materia,
                  diaint, horaint, null, null
                  FROM fn_xrxx_incio_horario_profesor({$periodo}, {$id}) as (hora time, dia VARCHAR, lugar TEXT, sede integer, materia text[], diaint bigint, horaint bigint)
                  order by 5,6
                  )) as foo ON (foo1.horainicio = foo.hora AND foo1.pk_atributo = foo.diaint) 
                order by 1";
          

      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results;
    }
    public function getHorarioAula($periodo,$sede,$aula,$inicio, $dia){
          
          $SQL="SELECT  atr.valor as materia, '/'||asna.nota as nota, atr2.valor as seccion, 
                 horainicio as inicio, horafin as fin , asna.fk_dia as dia, asna.fk_periodo as periodo , ve.codigo ,vs.id, 
                (SELECT count(*) as cuenta 
                FROM tbl_inscripciones ti1
                JOIN tbl_recordsacademicos tr1 ON ti1.pk_inscripcion = tr1.fk_inscripcion
                JOIN tbl_asignaciones ta1      ON tr1.fk_asignacion  = ta1.pk_asignacion
                JOIN tbl_asignaturas ts1       ON ta1.fk_asignatura  = ts1.pk_asignatura
                WHERE ti1.fk_estructura = est3.pk_estructura
                AND ts1.pk_asignatura   = asi.pk_asignatura   
                AND ts1.fk_pensum       = asi.fk_pensum
                AND ti1.fk_periodo  = asna.fk_periodo
                AND ta1.fk_semestre = asi.fk_semestre 
                /*AND tr1.fk_atributo = 864*/
                ) as inscritos,asna.cupos_max,pk_horario
/*si se quita el atributo 864 apareceran todos los que en algun'momento inscribieron la materia en el periodo, es decir, conta
ra incluso las personas que hayan retirado o que tengan equivalencias de la materia*/
                from tbl_horarios h 
                join tbl_asignaciones asna on asna.fk_horario    = h.pk_horario
                join tbl_asignaturas asi   on asi.pk_asignatura  = asna.fk_asignatura
                join tbl_pensums pen       on pen.pk_pensum      = asi.fk_pensum
                join tbl_estructuras est1  on est1.pk_estructura = asna.fk_estructura
                join tbl_estructuras est2  on est2.pk_estructura = est1.fk_estructura
                join tbl_estructuras est3  on est3.pk_estructura = est2.fk_estructura
                join vw_escuelas ve        on ve.pk_atributo     = pen.fk_escuela
                join vw_semestres vs       on vs.pk_Atributo     = asi.fk_semestre
                join tbl_atributos atr     on atr.pk_atributo    = asi.fk_materia 
                join tbl_atributos atr2    on atr2.pk_atributo   = asna.fk_seccion
                where h.pk_horario <> 11   and asna.fk_periodo   = {$periodo} 
                and est1.pk_estructura = {$aula}
                and est3.pk_estructura = {$sede} 
                and horainicio = '{$inicio}'
                and asna.fk_dia = {$dia}
                order by dia, inicio";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
        return $results;
            
    
    }

    public function getAllHoras(){
      $SQL="SELECT * from tbl_horarios 
            Where pk_horario <9;";
      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results;
    }
    public function getAllDias(){
      $SQL="SELECT * from vw_dias  
            Where id <>7;";
      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results;
    }

    public function getSedeAula($sede,$aula){
      $SQL="select sede,edificio,aula from vw_estructuras
      where pk_sede={$sede}
      and pk_aula={$aula}";
      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results;
    }    
    public function getOcupacionAulas ($periodo, $estructura, $dia, $horario){

      $SQL = "SELECT  asignatura, 
        profesor, 
        curso,
        aula,
        dia,
        horainicio,
        CASE WHEN aula ILIKE '%Edif. 1 - 1%' THEN 1
        WHEN aula ILIKE '%Edif. 1 - 2%' THEN 2
        WHEN aula ILIKE '%Edif. 2 - LA%' THEN 3
        WHEN aula ILIKE '%Edif. 2 - 1' THEN 4
        WHEN aula ILIKE '%Edif. 2 - 2' THEN 5
        WHEN aula ILIKE '%Edif. 2 - 3' THEN 6
        WHEN aula ILIKE '%Edif. 2 - 4' THEN 7
        WHEN aula ILIKE '%Edif. 2 - L%' THEN 8
        WHEN aula ILIKE '%Edif. 2 - 1%' THEN 9
        WHEN aula ILIKE '%Edif. 2 - 2%' THEN 10
        WHEN aula ILIKE '%Edif. 2 - %' THEN 11
        WHEN aula ILIKE 'Edif. Admin. - Salon Las Trinitarias' THEN 12
        WHEN aula ILIKE 'Esp. Interactivo - Piso 3' THEN 13
        WHEN aula ILIKE 'Canchas' THEN 14
        WHEN aula ILIKE 'CDT PC' THEN 15
        WHEN aula ILIKE 'CDT PC 2' THEN 16
        WHEN aula ILIKE 'CDT MAC' THEN 17
        WHEN aula ILIKE 'CPT' THEN 18
        WHEN aula ILIKE 'CRC - CRC' THEN 19
        WHEN aula ILIKE 'CRC - AULA' THEN 20
        WHEN aula ILIKE 'CRC - AULA 2' THEN 21
        WHEN aula ILIKE 'CRC - CRC LAB.' THEN 22
        WHEN aula ILIKE 'Lab. Ingles' THEN 23
        END AS ordinal,
        pk_estructura
      FROM(SELECT DISTINCT (array_to_string(sqt2.asignatura, ' / ')) AS asignatura,
                            array_to_string(sqt2.profesor, ' / ') AS profesor,
                            array_to_string(sqt2.curso, ' / ') AS curso,
                            sqt2.dia AS dia, sqt2.horainicio AS horainicio,
                            CASE WHEN sqt2.aula='Edif. 1 - CDT MAC' THEN 'CDT MAC'
                            WHEN sqt2.aula='Edif. 1 - CDT PC' THEN 'CDT PC'
                            WHEN sqt2.aula='Edif. 1 - CDT PC 2' THEN 'CDT PC 2'
                            WHEN sqt2.aula='Edif. 1 - CPT' THEN 'CPT'
                            WHEN sqt2.aula='Edif. 1 - Lab. Ingles' THEN 'Lab. Ingles'
                            WHEN sqt2.aula='Areas Comunes - Canchas' THEN 'Canchas'else sqt2.aula END AS aula, pk_estructura
            FROM (SELECT sqt.dia AS dia, 
          sqt.horainicio AS horainicio , 
          sqt.aula AS aula,
                      array_agg(DISTINCT profesor) AS profesor, 
                      array_agg(DISTINCT asignatura) AS asignatura, 
                      array_agg(distinct curso)AS curso, sqt.pk_estructura
                  FROM (SELECT DISTINCT m.materia AS asignatura,
              d.dia AS dia , 
              h.horainicio AS horainicio,
              edf.nombre || ' - ' || sal.nombre AS aula, 
              u.apellido || ',' || u.nombre AS profesor,
              a2.codigopropietario||sec.valor AS curso, sal.pk_estructura
                         FROM vw_dias d
                         CROSS JOIN tbl_horarios h
                         CROSS JOIN tbl_estructuras sal
                         JOIN tbl_estructuras edf ON edf.pk_estructura = sal.fk_estructura
                         JOIN tbl_estructuras sed ON sed.pk_estructura = edf.fk_estructura
                         LEFT OUTER JOIN tbl_asignaciones a1 ON a1.fk_estructura = sal.pk_estructura
                                 and  a1.fk_horario = h.pk_horario
                                 and  a1.fk_dia = d.pk_atributo
                                 and a1.fk_periodo = {$periodo}
                         LEFT OUTER JOIN tbl_asignaturas a2 ON a1.fk_asignatura = a2.pk_asignatura
                         LEFT OUTER JOIN vw_materias m ON m.pk_atributo = a2.fk_materia
                         LEFT OUTER JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = a1.fk_usuariogrupo
                         LEFT OUTER JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                         LEFT OUTER JOIN vw_secciones sec ON sec.pk_atributo = a1.fk_seccion
                         WHERE sed.pk_estructura = {$estructura}
             AND sal.pk_estructura NOT IN (105,65,55,100,108,44,50,69,51)
             AND d.id = {$dia}
             AND h.pk_horario = {$horario}
             ) AS sqt
          GROUP BY sqt.dia, sqt.aula, sqt.horainicio, sqt.pk_estructura
          ) AS sqt2
      )AS orden
      ORDER BY ordinal, aula ASC ";

      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();

      return $results;
    }

    public function getHorarioCoincidencia($search,$periodo,$sede,$escuela){
      if(isset($search)){

            $WHERE = "WHERE ";
            $first_time = true;
            $search = explode("+", $search);

            foreach ($search as $key => $value) {
               if(!$first_time)
                 $WHERE .= " OR ";
                $WHERE .= " (ci::varchar ilike '%{$value}%' OR nombre ilike '%{$value}%' OR apellido ilike '%{$value}%' OR materia_1 ilike '%{$value}%' OR materia_2 ilike '%{$value}%') ";
                $first_time = false;

          }
        }
      

             $SQL = "SELECT DISTINCT * FROM fn_xrxx_horarios_coincidencias({$periodo}, {$sede}, {$escuela}) "
                      .$WHERE.
                     " ORDER BY ci;";
              
              $results = $this->_db->query($SQL);
              $results = $results->fetchAll();


        return $results;
    }

public function horarioPorMateria($periodo,$sede,$materia){

                     $SQL = "SELECT DISTINCT tt3.valor as seccion, ve.codigo as codigo, vs.id as periodo, tp.fk_escuela, te2.nombre as edificio, te1.nombre as salon, te3.pk_estructura as sede, ta.fk_periodo, tu.nombre as primer_nombre, tu.apellido as primer_apellido, tt.valor as materia, ta.fk_asignatura, ta.pk_asignacion, th.horainicio as inicio, ta.fk_horario as horario, tt2.valor as dia, ta.fk_dia, ts.fk_materia
                                      FROM tbl_usuarios tu
                                      JOIN tbl_usuariosgrupos tg on tu.pk_usuario = tg.fk_usuario
                                      JOIN tbl_asignaciones ta on tg.pk_usuariogrupo = ta.fk_usuariogrupo
                                      JOIN tbl_asignaturas ts on ta.fk_asignatura = ts.pk_asignatura
                                      JOIN tbl_pensums tp on ts.fk_pensum = tp.pk_pensum
                                      JOIN tbl_horarios th on ta.fk_horario = th.pk_horario
                                      JOIN tbl_estructuras te1 on ta.fk_estructura = te1.pk_estructura
                                      JOIN tbl_estructuras te2 on te1.fk_estructura = te2.pk_estructura
                                      JOIN tbl_estructuras te3 on te2.fk_estructura = te3.pk_estructura
                                      JOIN tbl_atributos tt on ts.fk_materia = tt.pk_atributo
                                      JOIN tbl_atributos tt1 on tp.fk_escuela = tt1.pk_atributo
                                      JOIN tbl_atributos tt2 on ta.fk_dia = tt2.pk_atributo
                                      JOIN tbl_atributos tt3 on ta.fk_seccion = tt3.pk_atributo
                                      JOIN vw_escuelas ve on tp.fk_escuela = ve.pk_atributo
                                      JOIN vw_semestres vs on ta.fk_semestre = vs.pk_atributo
                                        WHERE te3.pk_estructura = {$sede}
                                        AND tg.fk_grupo = 854
                                        AND ta.fk_periodo = {$periodo}
                                        AND ts.fk_materia = {$materia}
                                        ORDER BY ta.fk_dia,ta.fk_horario,tt.valor,ta.fk_asignatura";

                            $results = $this->_db->query($SQL);
                            $results = $results->fetchAll();

                            return $results;


              }
    public function gethorasprofesor($ci,$periodo,$sede){
              $SQL = "SELECT (count(sqt.fk_estructura)*2) AS cant_horas
                      FROM (
                            SELECT DISTINCT est1.fk_estructura, fk_dia, horainicio,horafin
                                  FROM tbl_usuarios u
                                  JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                                  JOIN tbl_asignaciones asg  ON ug.pk_usuariogrupo = asg.fk_usuariogrupo
                                  JOIN tbl_horarios h        ON asg.fk_horario = h.pk_horario
                                  JOIN tbl_estructuras est1  ON est1.pk_estructura = asg.fk_estructura
                                  JOIN tbl_estructuras est2  ON est2.pk_estructura = est1.fk_estructura
                                  WHERE u.pk_usuario     = {$ci}
                                  AND asg.fk_periodo     = {$periodo}
                                  AND est2.fk_estructura = {$sede}
                                  ) as sqt";

              $results = $this->_db->query($SQL);
              $results = $results->fetchAll();

              return $results; 
              }  

    public function horarioReinscripcion($sede,$periodo,$codigo,$seccion,$turno,$semestre){
        if($seccion == '--'){
          return 0;
        }
        /*$seccion  = intval($seccion);
        $turno    = intval($turno);
        $semestre = intval($semestre);*/

         $SQL= " SELECT DISTINCT ('/ ' || ta.nota) as nota, te1.nombre as aula, te2.nombre as edif, tu.nombre as primer_nombre, tu.apellido as primer_apellido, tt.valor as materia, ta.fk_asignatura, ta.pk_asignacion, th.horainicio as inicio, ta.fk_horario as horario, tt2.valor as dia, ta.fk_dia, ts.fk_materia, th.horafin
              FROM tbl_usuarios tu
              JOIN tbl_usuariosgrupos tg on tu.pk_usuario = tg.fk_usuario
              JOIN tbl_asignaciones ta on tg.pk_usuariogrupo = ta.fk_usuariogrupo
              JOIN tbl_asignaturas ts on ta.fk_asignatura = ts.pk_asignatura
              JOIN tbl_pensums tp on ts.fk_pensum = tp.pk_pensum
              JOIN tbl_horarios th on ta.fk_horario = th.pk_horario
              JOIN tbl_estructuras te1 on ta.fk_estructura = te1.pk_estructura
              JOIN tbl_estructuras te2 on te1.fk_estructura = te2.pk_estructura
              JOIN tbl_estructuras te3 on te2.fk_estructura = te3.pk_estructura
              JOIN tbl_atributos tt on ts.fk_materia = tt.pk_atributo
              JOIN tbl_atributos tt1 on tp.fk_escuela = tt1.pk_atributo
              JOIN tbl_atributos tt2 on ta.fk_dia = tt2.pk_atributo
              JOIN tbl_atributos tt3 on ta.fk_seccion = tt3.pk_atributo
              JOIN vw_escuelas ve on tp.fk_escuela = ve.pk_atributo
              JOIN vw_semestres vs on ta.fk_semestre = vs.pk_atributo
              WHERE te3.pk_estructura in ({$sede})
              AND tg.fk_grupo = 854
              AND ta.fk_periodo in ({$periodo})
              AND ts.codigopropietario in ('{$codigo}')
              AND ta.fk_seccion in ({$seccion})
              AND ta.fk_turno in ({$turno})
              AND ta.fk_semestre in ({$semestre})
              ORDER BY ta.fk_dia,ta.fk_horario,tt.valor,ta.fk_asignatura";
              $results = $this->_db->query($SQL);
              $results = $results->fetchAll();
              return $results; 
        }
           
    public function horarioestudiante($periodo,$cedula,$sede){
      $SQL = "SELECT * from fn_xrxx_inicio_horario_estudiante($periodo, $cedula,$sede)  as (hora time, dia VARCHAR, lugar TEXT, materia text[], diaint bigint, horaint bigint, turnoreal integer, prof TEXT);";

      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results; 
    }

    public function horarioseccion($seccion,$periodo,$materia,$pensum){
      $SQL = "  SELECT h.*, asna.fk_dia from tbl_asignaturas asi
                join tbl_asignaciones asna on asna.fk_asignatura = asi.pk_asignatura
                join tbl_horarios h on h.pk_horario = asna.fk_horario
                where fk_seccion = {$seccion} and
                asna.fk_periodo = {$periodo}  and 
                asi.fk_materia =  {$materia}  and 
                fk_pensum = {$pensum}";

      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();
      return $results; 
    }

        
}

