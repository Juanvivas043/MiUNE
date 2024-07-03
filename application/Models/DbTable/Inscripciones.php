<?php
class Models_DbTable_Inscripciones extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_inscripciones';
    protected $_primary  = 'pk_inscripcion';
    protected $_sequence = false;

    public function init() {
      $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
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

    public function deleteRow($id) {
        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }


    public function getUltimaEscuela($ci) {
        if(empty($ci)) return;

        $SQL = "SELECT e.escuela
                FROM tbl_inscripciones   i
	            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  i.fk_usuariogrupo
                JOIN vw_escuelas         e ON e.pk_atributo = i.fk_atributo
                WHERE ug.fk_usuario = {$ci}
                ORDER BY fk_periodo DESC
                LIMIT 1";

        return $this->_db->fetchOne($SQL);
    }

    public function getUltimaEscuelapk($ci) {
        if(empty($ci)) return;

        $SQL = "SELECT e.pk_atributo
                FROM tbl_inscripciones   i
	            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  i.fk_usuariogrupo
                JOIN vw_escuelas         e ON e.pk_atributo = i.fk_atributo
                WHERE ug.fk_usuario = {$ci}
                ORDER BY fk_periodo DESC
                LIMIT 1";

        return $this->_db->fetchOne($SQL);
    }

    public function getObservaciones($id) {
        if(empty($id)) return;

        $SQL = "SELECT observaciones
                FROM tbl_inscripciones
                WHERE pk_inscripcion = {$id}";

        return $this->_db->fetchOne($SQL);
    }

    public function getSedeInscripcion($ci,$periodo){

        $SQL = "SELECT fk_estructura, sed.nombre
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE i.fk_periodo = {$periodo}
                  AND ug.fk_usuario = {$ci};
                ";


        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }
    
        public function getUltimaSedeInscripcion($ci){

        $SQL = "SELECT fk_estructura, sed.nombre
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE ug.fk_usuario = {$ci}
                order by i.fk_periodo desc limit 1;
                ";
        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }
    
        public function getUltimoPeriodoInscripcion($ci){ 

        $SQL = "SELECT distinct i.fk_periodo
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE ug.fk_usuario = {$ci}
                order by i.fk_periodo desc limit 1;
                ";
        
       return $this->_db->fetchOne($SQL);

    }

    public function getPensumInscripcion($ci,$periodo){

        $SQL = "SELECT fk_pensum
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE i.fk_periodo = {$periodo}
                  AND ug.fk_usuario = {$ci};
                ";


        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }

    public function getPK($ci, $escuela, $periodo = 0,$pensum = null) {
        if(is_null($ci))      return;
        if(is_null($escuela)) return;
        if(is_null($periodo)) return;

        if ($pensum == null){
          $SQL = "SELECT DISTINCT i.pk_inscripcion
                FROM tbl_inscripciones      i
                JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
                WHERE u.pk_usuario  = {$ci}
                  AND i.fk_atributo = {$escuela}
                  AND i.fk_periodo  = {$periodo}";  
        }else{
          $SQL = "SELECT DISTINCT i.pk_inscripcion
                FROM tbl_inscripciones      i
                JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                JOIN tbl_usuarios           u ON  u.pk_usuario       = ug.fk_usuario
                WHERE u.pk_usuario  = {$ci}
                  AND i.fk_atributo = {$escuela}
                  AND i.fk_periodo  = {$periodo}
                  AND i.fk_pensum   = {$pensum}";  
        }
        return $this->_db->fetchOne($SQL);
    }

    public function addRow($usuariogrupo, $periodo, $numeropago, $ucadicionales, $escuela, $estructura, $semestre, $observaciones,$pensum) {
        $data = array(
            'fk_usuariogrupo' => $usuariogrupo,
            'fk_periodo'      => $periodo,
            'numeropago'      => $numeropago,
            'ucadicionales'   => $ucadicionales,
            'fk_atributo'     => $escuela,
            'fk_estructura'   => $estructura,
            'fk_semestre'     => $semestre,
            'fk_pensum'       => $pensum,
            'observaciones'   => $observaciones
        );

        $data          = array_filter($data);
        $rows_affected = $this->insert($data);

        return $rows_affected;
    }

    public function updateRow($pk, $usuariogrupo, $periodo, $numeropago, $ucadicionales, $escuela, $estructura, $semestre, $observaciones, $tipo) {
        $data = array(
            'fk_usuariogrupo' => $usuariogrupo,
            'fk_periodo'      => $periodo,
            'numeropago'      => $numeropago,
            'ucadicionales'   => $ucadicionales,
            'fk_atributo'     => $escuela,
            'fk_estructura'   => $estructura,
            'fk_semestre'     => $semestre,
            'observaciones'   => $observaciones,
            'fk_tipo'         => $tipo
        );

        $where         = $this->_db->quoteInto('pk_inscripcion = ?', $pk);
        $data          = array_filter($data);
        $rows_affected = $this->update($data, $where);

        return $rows_affected;
    }

    public function addEquivalencia($usuariogrupo, $escuela, $estructura) {
        $data = array(
            'fk_usuariogrupo' => $usuariogrupo,
            'fk_atributo'     => $escuela,
            'fk_estructura'   => $estructura,
            'fk_semestre'     => 872
        );

        $data          = array_filter($data);
        $rows_affected = $this->insert($data);

        return $rows_affected;
    }

    /**
     * Elimina aquella inscripciones que no se estan usando en el record
     * académico del estudiante. Denominando como los registros huerfanos . 
     *
     * @param int $usuario
     * @param int $escuela
     */
    public function clear($usuario, $escuela) {
        $SQL = "DELETE FROM tbl_inscripciones
                WHERE pk_inscripcion IN (SELECT DISTINCT i.pk_inscripcion
                                         FROM tbl_inscripciones  i
                                         JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
                                         WHERE ug.fk_usuario  = {$usuario}
                                           AND  i.fk_atributo = {$escuela}
                                           AND  i.pk_inscripcion NOT IN (SELECT DISTINCT fk_inscripcion
                                                                         FROM tbl_recordsacademicos ra1
                                                                         JOIN tbl_inscripciones      i1 ON i1.pk_inscripcion   = ra1.fk_inscripcion
                                                                         WHERE i1.fk_usuariogrupo = ug.pk_usuariogrupo
                                                                           AND i1.fk_atributo     = i.fk_atributo));";

        $this->_db->fetchOne($SQL);
    }

    public function getUPI($id){
        if(empty($id)) return;

        $SQL= "SELECT DISTINCT i.fk_periodo
               FROM tbl_inscripciones i
               JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
               where ug.fk_usuario = {$id}
                 and i.fk_periodo IN (select distinct ins.fk_periodo
                                      from tbl_usuariosgrupos ug1
                                      JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug1.pk_usuariogrupo
                                      JOIN tbl_recordsacademicos ra ON ins.pk_inscripcion = ra.fk_inscripcion
                                      WHERE ug1.fk_usuario = ug.fk_usuario
                                      order by 1 DESC
                                      limit 1
                                        );";

        $results = $this->_db->fetchOne($SQL);
        return $results;

    }

    public function getUltimaSede($ci) {
        if(empty($ci)) return;

        $SQL = "SELECT i.fk_estructura
                FROM tbl_inscripciones   i
	        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  i.fk_usuariogrupo
                WHERE ug.fk_usuario = {$ci}
                ORDER BY fk_periodo DESC
                LIMIT 1";

        return $this->_db->fetchOne($SQL);
    }

    public function getInscripcionpasantia($id, $periodo) {
        $SQL = "SELECT i.pk_inscripcion
                  FROM tbl_inscripciones i
                  JOIN tbl_usuariosgrupos 	ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                  JOIN tbl_recordsacademicos 	ra ON ra.fk_inscripcion  = i.pk_inscripcion
                  JOIN tbl_asignaturas		ag ON ag.pk_asignatura	 = ra.fk_asignatura
                  JOIN vw_materias 		ma ON ma.pk_atributo	 = ag.fk_materia
                  JOIN tbl_usuarios              u ON u.pk_usuario       = ug.fk_usuario
                 WHERE i.fk_periodo = {$periodo}
                   AND u.pk_usuario = {$id}
--                   AND ag.pk_asignatura IN  (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                   AND ag.fk_materia IN (716,717,848,9859);";

		return $this->_db->fetchOne($SQL);
	}

        public function getEstudiantesRecaudos($periodo, $escuela) {

        $SQL = "SELECT i.pk_inscripcion,
                       u.apellido ||', ' || u.nombre as estudiante
                  FROM tbl_inscripciones i
                  JOIN tbl_usuariosgrupos 	ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                  JOIN tbl_recordsacademicos 	ra ON ra.fk_inscripcion  = i.pk_inscripcion
                  JOIN tbl_asignaturas		ag ON ag.pk_asignatura	 = ra.fk_asignatura
                  JOIN vw_materias 		ma ON ma.pk_atributo	 = ag.fk_materia
                  JOIN tbl_usuarios              u ON u.pk_usuario       = ug.fk_usuario
                 WHERE i.fk_periodo = {$periodo}
                   AND i.fk_atributo = {$escuela}
                   AND ag.fk_materia IN (716,717,848,9859)
                   --AND ag.pk_asignatura IN  (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                   AND i.pk_inscripcion NOT IN (SELECT r.fk_inscripcion FROM tbl_recaudos r)
                        ORDER BY 2 ASC;";

    $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }

   public function getCountEstudianteInscripcionPasantias($usuario, $periodo) {
        $SQL = "SELECT COUNT(i.pk_inscripcion)
                  FROM tbl_inscripciones i
                  JOIN tbl_usuariosgrupos 	ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                  JOIN tbl_recordsacademicos 	ra ON ra.fk_inscripcion  = i.pk_inscripcion
                  JOIN tbl_asignaturas		ag ON ag.pk_asignatura	 = ra.fk_asignatura
                  JOIN vw_materias 		ma ON ma.pk_atributo	 = ag.fk_materia
                  JOIN tbl_usuarios              u ON u.pk_usuario       = ug.fk_usuario
                 WHERE i.fk_periodo = {$periodo}
                   AND u.pk_usuario = {$usuario}
--                   AND ag.pk_asignatura IN (12410,12569,12649,12418,12574,12657,12254,12328,12506)
                   AND ag.fk_materia IN (716,717,848,9859);";

		return $this->_db->fetchOne($SQL);
	}

    public function getPagoPeriodo($ci,$periodo){

        $SQL = "SELECT distinct numeropago
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE i.fk_periodo = {$periodo}
                and  ug.fk_usuario = {$ci} LIMIT 1;";

		return $this->_db->fetchOne($SQL);

    }


    public function getInscripcionPeriodo($ci,$periodo){

        $SQL = "SELECT distinct pk_inscripcion
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE i.fk_periodo = {$periodo}
                and  ug.fk_usuario = {$ci};";

		return $this->_db->fetchOne($SQL);


    }

    public function getInscripcionPeriodoPensum($ci,$periodo, $pensum){

        $SQL = "SELECT distinct pk_inscripcion
                FROM tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE i.fk_periodo = {$periodo}
                and i.fk_pensum = {$pensum}
                and  ug.fk_usuario = {$ci};";

		return $this->_db->fetchOne($SQL);


    }


    public function updateEscuelaSedePensum($data){

        $SQL = "UPDATE tbl_inscripciones
                SET fk_usuariogrupo={$data['fk_usuariogrupo']},
                    fk_atributo={$data['fk_atributo']}, fk_estructura={$data['fk_estructura']},
                    fk_pensum={$data['fk_pensum']}
                WHERE pk_inscripcion = {$data['pk_inscripcion']};";

		return $this->_db->fetchOne($SQL);
                //var_dump($SQL);

    }


    public function getPruebasDiagnostico(){


        $SQL = "SELECT q.id as valor, q.name as display
                FROM dblink('dbname=Moodle_CGV port=5432 host=192.168.1.10 user=Moodle_CGV password=c4r4c4s',
                'select distinct q.id, q.name
                from mdl_quiz q
                JOIN mdl_course c ON c.id = q.course
                where c.category = 184
                and q.id >= 124'
                ) as q(id integer,name varchar);
                ";

        $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }


    public function getCountNuevoAllIngreso($periodo,$sede,$escuela){

        $SQL = "SELECT COUNT(distinct pk_usuario)
                from tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE i.fk_usuariogrupo NOT IN (SELECT i1.fk_usuariogrupo
                                                FROM tbl_recordsacademicos ra
                                                JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra.fk_inscripcion
                                                WHERE i1.fk_periodo < {$periodo}
                                                  AND ra.fk_atributo <> 863)
                 AND  i.fk_usuariogrupo NOT IN (SELECT i1.fk_usuariogrupo
                                                FROM tbl_recordsacademicos ra
                                                JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra.fk_inscripcion
                                                WHERE i1.fk_periodo = {$periodo})
                 AND i.fk_periodo = {$periodo}
                 AND i.fk_atributo = {$escuela};";

       $results = $this->_db->query($SQL);

	return $results->fetchAll();


    }

    public function getNuevoAllNuevoIngreso($periodo,$sede,$escuela){

        $SQL = "SELECT u.primer_nombre || ' ' ||
                coalesce(u.segundo_nombre,'') as nombre, 
                u.primer_apellido || ' ' ||
                coalesce(u.segundo_apellido,'') as apellido,
                u.pk_usuario as cedula
                from tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE i.fk_usuariogrupo NOT IN (SELECT Distinct i1.fk_usuariogrupo
                                                FROM tbl_recordsacademicos ra
                                                JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra.fk_inscripcion
                                                WHERE i1.fk_periodo IN (SELECT pk_periodo FROM tbl_periodos WHERE pk_periodo <= {$periodo})
                                                  AND ra.fk_atributo <> 863)

                 AND i.fk_periodo = {$periodo}
                 AND i.fk_estructura = {$sede}
                 AND i.fk_atributo = {$escuela}
                 ORDER BY 3 ASC;";

       $results = $this->_db->query($SQL);

	return $results->fetchAll();

    }


    public function actualizarSemestre($sede,$seccion,$escuela,$periodo,$limite,$cedulas){


        $SQL = "UPDATE tbl_inscripciones SET fk_semestre = 873 where pk_inscripcion IN (
                SELECT i.pk_inscripcion
                from tbl_inscripciones i
                WHERE i.fk_usuariogrupo IN (SELECT ug.pk_usuariogrupo
                                                FROM tbl_usuariosgrupos ug
                                                WHERE ug.fk_usuario IN ({$cedulas})
                                                AND ug.fk_grupo = 855)
                  AND i.fk_periodo = {$periodo}
                  AND i.fk_atributo = {$escuela}
                     order by 1
                );";

        $results = $this->_db->query($SQL);

    }

    public function agregarMateriasPrimerSemestre($sede,$seccion,$escuela,$periodo,$limite,$cedulas,$pensum){


        $SQL = "INSERT INTO tbl_recordsacademicos
                            (fk_atributo, calificacion, fk_asignatura,
                            fk_inscripcion, fk_asignacion)
                (SELECT  864, 0, asignatura, pk_inscripcion, asignacion
                FROM fn_xrxx_asignar_materias_primer_semestre_test4({$sede}, 1, '{$seccion}' , {$escuela}, {$periodo},{$cedulas}) as m(pk_inscripcion integer, asignacion integer, asignatura integer)
                );";

        $results = $this->_db->query($SQL);
        //var_dump($SQL);
        return $results;

    }

    public function getNuevoIngresoporNota($per,$escuela,$nota,$sede,$prueba,$notaprueba,$materia){


        $SQL = "SELECT *
                FROM (
                SELECT fk_usuario, nombre, apellido, CASE WHEN nota is null THEN 0 ELSE nota END as nota,indice
                FROM
                (
                SELECT fk_usuario, nombre, apellido, MAX(nota) as nota,indice
                FROM
                (
                                select ug.fk_usuario,
                                       u.nombre,
                                       u.apellido,
                                       moodle.nota,
                                       ud.promedio as indice

                                from tbl_usuariosgrupos ug
                                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                                JOIN tbl_usuariosdatos ud ON ud.fk_usuario = u.pk_usuario
                                LEFT OUTER JOIN (
                                SELECT qp.*
                                FROM dblink('dbname=Moodle_CGV port=5432 host=192.168.1.10 user=Moodle_CGV password=c4r4c4s',
                                '
                                SELECT DISTINCT u.username, a.sumgrades
                                FROM mdl_quiz_attempts a
                                JOIN mdl_user u ON u.id = a.userid
                                WHERE quiz = {$prueba}
                                order by 2 DESC
                                '
                                ) as qp(usr varchar, nota decimal)
                                ) as moodle ON moodle.usr = ug.fk_usuario::text
                                WHERE i.fk_periodo = {$per}
                                 AND i.fk_atributo = {$escuela}
                                 AND i.fk_estructura = {$sede}
                                 AND ud.promedio < {$nota}
                                 AND ug.fk_usuario NOT IN (SELECT distinct ug2.fk_usuario
                                                           FROM tbl_usuariosgrupos ug2
                                                           JOIN tbl_inscripciones i2 ON i2.fk_usuariogrupo = ug2.pk_usuariogrupo
                                                           JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i2.pk_inscripcion
                                                           WHERE i2.fk_periodo < i.fk_periodo
                                                           limit 1 )
                                 AND ug.fk_usuario NOT IN (SELECT distinct ug2.fk_usuario
                                                           FROM tbl_usuariosgrupos ug2
                                                           JOIN tbl_inscripciones i2 ON i2.fk_usuariogrupo = ug2.pk_usuariogrupo
                                                           JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i2.pk_inscripcion
                                                           WHERE i2.fk_periodo <= i.fk_periodo
                                                             AND ra.fk_asignacion = {$materia}
                                                           )
                                ORDER BY 2 DESC
                                ) as sqt
                               group by 1,2,3,5
                            )as sqt2
                         )as sqt3
                         WHERE nota < {$notaprueba}
                         ORDER BY 5 DESC";

        $SQL1 = "SELECT u.primer_nombre || ' ' ||
                coalesce(u.segundo_nombre,'') as nombre,
                u.primer_apellido || ' ' ||
                coalesce(u.segundo_apellido,'') as apellido,
                u.pk_usuario as cedula
                from tbl_inscripciones i
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_usuariosdatos ud ON ud.fk_usuario = u.pk_usuario
                WHERE i.fk_usuariogrupo NOT IN (SELECT Distinct i1.fk_usuariogrupo
                                                FROM tbl_recordsacademicos ra
                                                JOIN tbl_inscripciones i1 ON i1.pk_inscripcion = ra.fk_inscripcion
                                                WHERE i1.fk_periodo IN (SELECT pk_periodo FROM tbl_periodos WHERE pk_periodo <= {$per})
                                                  AND ra.fk_atributo <> 863)

                 AND i.fk_periodo = {$per}
                 AND i.fk_atributo = {$escuela}
                 AND i.fk_estructura = {$sede}
                 AND ud.promedio < {$nota}

                 ORDER BY 3 ASC;";

        $results = $this->_db->query($SQL);

	return $results->fetchAll();


    }


    public function getPeriodoNuevoAInscribir($cedula){


        $SQL = "SELECT pk_periodo
		FROM tbl_periodos 
		WHERE pk_periodo NOT IN (
			SELECT fk_periodo 
			FROm tbl_usuariosgrupos ug 
			JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
			WHERE fk_usuario = {$cedula})
		AND pk_periodo >  (
			SELECT fk_periodo 
			FROm tbl_usuariosgrupos ug 
			JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
			WHERE fk_usuario = {$cedula} ORDER BY 1 limit 1)
		ORDER BY 1 DESC LIMIT 1";


         $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }
    public function inscribirMateriaMultiplesUsuarios($cedulas,$asignacion){


        $SQL = "INSERT INTO tbl_recordsacademicos
                (fk_atributo, calificacion, fk_asignatura,
                fk_inscripcion, fk_asignacion)
                (
                  SELECT 864 as fk_atributo,
                           0 as calificacion,
                         (SELECT fk_asignatura
                          FROM tbl_asignaciones
                          WHERE pk_asignacion = {$asignacion}) as asignatura,
                         pk_inscripcion,
                         {$asignacion} as asignacion
                FROM tbl_usuariosgrupos ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                WHERE ug.fk_usuario IN ({$cedulas})
                );
                ";

         $results = $this->_db->query($SQL);

	return $results->fetchAll();
    }

    public function getPkporCedula($ci){

        $SQL = "SELECT i.pk_inscripcion
                FROM tbl_inscripciones i
	        JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo =  i.fk_usuariogrupo
                WHERE ug.fk_usuario = {$ci};";

        return $this->_db->fetchOne($SQL);


    }

    public function getCuadroNuevoIngreso($periodo,$sede){

        $SQL = "SELECT es.escuela,fk_atributo,
                       COALESCE(SUM(regular),0) as regular,
                       COALESCE(SUM(prueba),0) as prueba,
                       COALESCE(SUM(total),0) as total
                FROM (
                SELECT fk_atributo,pk_usuario,
                       CASE WHEN promedio >= 14 THEN 1 END AS regular,
                       CASE WHEN promedio < 14 THEN 1 END AS prueba,
                       SUM(count) as total
                FROM(
                select DISTINCT u.pk_usuario,i.fk_atributo,
                       ud.promedio,
                       COUNT(u.pk_usuario)
                from tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_usuariosdatos ud ON ud.fk_usuario = u.pk_usuario
                WHERE i.fk_periodo = {$periodo}
                AND i.fk_estructura = {$sede}
                AND ug.pk_usuariogrupo NOT IN (SELECT Distinct i1.fk_usuariogrupo
                                               FROM tbl_inscripciones i1
                                               JOIN tbl_recordsacademicos ra1 ON ra1.fk_inscripcion = i1.pk_inscripcion
                                               WHERE i1.fk_periodo < i.fk_periodo
                                               limit 1)
                GROUP BY u.pk_usuario,promedio,i.fk_atributo
                )as sqt
                group by 1,2, promedio
                ) as foo
                JOIN vw_escuelas es ON es.pk_atributo = foo.fk_atributo
                group by 1,2
                order by 2;


                ";


        $results = $this->_db->query($SQL);

	      return $results->fetchAll();
    }

    /**
    * @return dependiendo de los filtros, retorna periodos, sedes, escuelas y pensums del estudiante que tengan regimen de evaluacion
    */
    public function getEstudianteFiltroRecord($periodo=null, $sede=null, $escuela=null, $pensum=null){
      $SQL = "SELECT distinct ";
      if(empty($periodo) && empty($sede) && empty($escuela) && empty($pensum)) $todosVacios = true;
      switch (true) {
        case isset($escuela):
          $SQL.= "i.fk_pensum ";
          $where = " AND i.fk_periodo = $periodo ";
          $where .= " AND i.fk_estructura = $sede ";
          $where .= " AND i.fk_atributo = $escuela ";
          break;
        case isset($sede):
          $SQL.= "i.fk_atributo, e.escuela ";
          $where = " AND i.fk_periodo = $periodo ";
          $where .= " AND i.fk_estructura = $sede ";
          break;
        case isset($periodo):
          $SQL.= "i.fk_estructura, s.nombre ";
          $where = " AND i.fk_periodo = $periodo ";
          break;
        case $todosVacios:
          $SQL.= "i.fk_periodo ";
          break;
      }
      $SQL.= "FROM tbl_asignaturas    ag
              JOIN tbl_asignaturas_regimenes agr ON agr.fk_asignatura = ag.pk_asignatura
              JOIN tbl_regimenes_historicos   rh ON rh.pk_regimen_historico = agr.fk_regimen_historico
              JOIN tbl_regimenes_evaluaciones re ON re.fk_regimen_historico = rh.pk_regimen_historico
              JOIN tbl_recordsacademicos ra ON ra.fk_asignatura = ag.pk_asignatura 
              JOIN tbl_inscripciones      i ON i.pk_inscripcion = ra.fk_inscripcion and i.fk_periodo = rh.fk_periodo_inicio
              JOIN tbl_usuariosgrupos    ug ON ug.pk_usuariogrupo  =  i.fk_usuariogrupo
              JOIN vw_escuelas e on i.fk_atributo = e.pk_atributo
              JOIN vw_sedes s on i.fk_estructura = s.pk_estructura
              WHERE ug.fk_usuario = {$this->AuthSpace->userId}";
      $SQL = $SQL . $where;
      $results = $this->_db->query($SQL);
      
      return $results->fetchAll();
    }

    public function getCantidadInscritos($periodo,$sede){
      $SQL = "SELECT ta.valor AS Escuela,
      /*Administrativo*/
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = false) 
      - 
      /*Nuevo Ingreso Administrativo*/
      (coalesce((SELECT count(distinct pk_usuario)
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
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo), 0)) AS Administrativo,
      /*Academico*/
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = false
      AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion))
      -
      /*Nuevo Ingreso Academico*/
      (coalesce((SELECT count(distinct pk_usuario)
      FROM tbl_usuarios u
      JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
      JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
      JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
      JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
      WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
        FROM tbl_usuariosgrupos ug
        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
        WHERE ins.fk_periodo < i.fk_periodo
        AND ug.fk_usuario = u.pk_usuario)
      AND i.fk_periodo = {$periodo}
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0)) AS Academico,
      /*Diferencia*/        
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = false
      AND ti.pk_inscripcion not IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion))
      -
      /*Nuevo Ingreso Administrativo*/
    ((coalesce((SELECT count(distinct pk_usuario)
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
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0))
      -
      /*Nuevo Ingreso Academico*/
      (coalesce((SELECT count(distinct pk_usuario)
      FROM tbl_usuarios u
      JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
      JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
      JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
      JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
      WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
        FROM tbl_usuariosgrupos ug
        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
        WHERE ins.fk_periodo < i.fk_periodo
        AND ug.fk_usuario = u.pk_usuario)
      AND i.fk_periodo = {$periodo}
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0))) AS Diferencia,
      /*Nuevo Ingreso Administrativo*/
      (coalesce((SELECT count(distinct pk_usuario)
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
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0)) AS NIAdministrativo,
      /*Nuevo Ingreso Academico*/
      (coalesce((SELECT count(distinct pk_usuario)
      FROM tbl_usuarios u
      JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
      JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
      JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
      JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
      WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
        FROM tbl_usuariosgrupos ug
        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
        WHERE ins.fk_periodo < i.fk_periodo
        AND ug.fk_usuario = u.pk_usuario)
      AND i.fk_periodo = {$periodo}
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0)) AS NIAcademico,
      /*Nuevo Ingreso Administrativo*/
      (coalesce((SELECT count(distinct pk_usuario)
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
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0))
      -
      /*Nuevo Ingreso Academico*/
      (coalesce((SELECT count(distinct pk_usuario)
      FROM tbl_usuarios u
      JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
      JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
      JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
      JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
      WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
        FROM tbl_usuariosgrupos ug
        JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
        WHERE ins.fk_periodo < i.fk_periodo
        AND ug.fk_usuario = u.pk_usuario)
      AND i.fk_periodo = {$periodo}
      AND i.fk_estructura = te.fk_estructura
      AND i.fk_atributo = te.fk_atributo
      AND i.fk_pensum in (20,21,22,23,24,25,26)
      GROUP BY a.valor, i.fk_atributo
      ORDER BY i.fk_atributo),0)) AS NIDiferencia,
      /*Administrativo Online*/
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true) AS onlineadministrativo,
      /*Academico Online*/
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true
      AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion)) as onlineacademico,
      /*Diferencia Online*/
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true)
      -
      (SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true
      AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion)) AS onlinediferencia
      FROM tbl_estructurasescuelas te
      join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
      WHERE te.fk_estructura = {$sede}
      UNION ALL
      SELECT 'Total' AS Escuela,
          /*Administrativo*/
          sum((SELECT count(*)
          FROM tbl_inscripciones ti
          WHERE ti.fk_estructura = te.fk_estructura
          AND ti.fk_atributo = te.fk_atributo
          AND ti.fk_periodo = {$periodo}
          AND ti.online = false))
          -
          /*Nuevo Ingreso Administrativo*/
          sum(coalesce((SELECT count(distinct pk_usuario)
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
          AND i.fk_estructura = te.fk_estructura
          AND i.fk_atributo = te.fk_atributo
          AND i.fk_pensum in (20,21,22,23,24,25,26)
          GROUP BY a.valor, i.fk_atributo
          ORDER BY i.fk_atributo),0)) AS Administrativo,
          /*Academico*/
          sum((SELECT count(*)
          FROM tbl_inscripciones ti
          WHERE ti.fk_estructura = te.fk_estructura
          AND ti.fk_atributo = te.fk_atributo
          AND ti.fk_periodo = {$periodo}
          AND ti.online = false
          AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
            FROM tbl_recordsacademicos tr
            WHERE tr.fk_inscripcion = ti.pk_inscripcion
            GROUP BY tr.fk_inscripcion)))
          -
          /*Nuevo Ingreso Academico*/
          sum(coalesce((SELECT count(distinct pk_usuario)
          FROM tbl_usuarios u
          JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
          JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
          JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
          JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
          WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
            FROM tbl_usuariosgrupos ug
            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
            WHERE ins.fk_periodo < i.fk_periodo
            AND ug.fk_usuario = u.pk_usuario)
          AND i.fk_periodo = {$periodo}
          AND i.fk_estructura = te.fk_estructura
          AND i.fk_atributo = te.fk_atributo
          AND i.fk_pensum in (20,21,22,23,24,25,26)
          GROUP BY a.valor, i.fk_atributo
          ORDER BY i.fk_atributo), 0))  AS Academico,
          /*Diferencia*/
          sum(((SELECT count(*)
          FROM tbl_inscripciones ti
          WHERE ti.fk_estructura = te.fk_estructura
          AND ti.fk_atributo = te.fk_atributo
          AND ti.fk_periodo = {$periodo}
          AND ti.online = false
          AND ti.pk_inscripcion not IN (SELECT tr.fk_inscripcion
            FROM tbl_recordsacademicos tr
            WHERE tr.fk_inscripcion = ti.pk_inscripcion
            GROUP BY tr.fk_inscripcion))))
          -
          /*Nuevo Ingreso Administrativo*/
          (sum(coalesce((SELECT count(distinct pk_usuario)
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
          AND i.fk_estructura = te.fk_estructura
          AND i.fk_atributo = te.fk_atributo
          AND i.fk_pensum in (20,21,22,23,24,25,26)
          GROUP BY a.valor, i.fk_atributo
          ORDER BY i.fk_atributo),0))
          -
          /*Nuevo Ingreso Academico*/
          sum(coalesce((SELECT count(distinct pk_usuario)
          FROM tbl_usuarios u
          JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
          JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
          JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
          JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
          WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
            FROM tbl_usuariosgrupos ug
            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
            WHERE ins.fk_periodo < i.fk_periodo
            AND ug.fk_usuario = u.pk_usuario)
          AND i.fk_periodo = {$periodo}
          AND i.fk_estructura = te.fk_estructura
          AND i.fk_atributo = te.fk_atributo
          AND i.fk_pensum in (20,21,22,23,24,25,26)
          GROUP BY a.valor, i.fk_atributo
          ORDER BY i.fk_atributo),0))) AS Diferencia,
        /*Nuevo Ingreso Administrativo*/
        sum(coalesce((SELECT count(distinct pk_usuario)
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
        AND i.fk_estructura = te.fk_estructura
        AND i.fk_atributo = te.fk_atributo
        AND i.fk_pensum in (20,21,22,23,24,25,26)
        GROUP BY a.valor, i.fk_atributo
        ORDER BY i.fk_atributo),0)) AS NIAdministrativo,
        /*Nuevo Ingreso Academico*/
        sum(coalesce((SELECT count(distinct pk_usuario)
        FROM tbl_usuarios u
        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
        JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
        JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
        WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
          FROM tbl_usuariosgrupos ug
          JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
          JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
          WHERE ins.fk_periodo < i.fk_periodo
          AND ug.fk_usuario = u.pk_usuario)
        AND i.fk_periodo = {$periodo}
        AND i.fk_estructura = te.fk_estructura
        AND i.fk_atributo = te.fk_atributo
        AND i.fk_pensum in (20,21,22,23,24,25,26)
        GROUP BY a.valor, i.fk_atributo
        ORDER BY i.fk_atributo),0)) AS NIAcademico,
        /*Nuevo Ingreso Administrativo*/
        sum(coalesce((SELECT count(distinct pk_usuario)
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
        AND i.fk_estructura = te.fk_estructura
        AND i.fk_atributo = te.fk_atributo
        AND i.fk_pensum in (20,21,22,23,24,25,26)
        GROUP BY a.valor, i.fk_atributo
        ORDER BY i.fk_atributo),0))
        -
        /*Nuevo Ingreso Academico*/
        sum(coalesce((SELECT count(distinct pk_usuario)
        FROM tbl_usuarios u
        JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
        JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
        JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
        JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
        WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
          FROM tbl_usuariosgrupos ug
          JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
          JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
          WHERE ins.fk_periodo < i.fk_periodo
          AND ug.fk_usuario = u.pk_usuario)
        AND i.fk_periodo = {$periodo}
        AND i.fk_estructura = te.fk_estructura
        AND i.fk_atributo = te.fk_atributo
        AND i.fk_pensum in (20,21,22,23,24,25,26)
        GROUP BY a.valor, i.fk_atributo
        ORDER BY i.fk_atributo),0)) AS NIDiferencia,
        /*Administrativo Online*/
      sum((SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true)) AS onlineadministrativo,
      /*Academico Online*/
      sum((SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true
      AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion))) AS onlineacademico,
      /*Diferencia Online*/
      sum((SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true))
      -
      sum((SELECT count(*)
      FROM tbl_inscripciones ti
      WHERE ti.fk_estructura = te.fk_estructura
      AND ti.fk_atributo = te.fk_atributo
      AND ti.fk_periodo = {$periodo}
      AND ti.online = true
      AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
          FROM tbl_recordsacademicos tr
          WHERE tr.fk_inscripcion = ti.pk_inscripcion
          GROUP BY tr.fk_inscripcion))) AS onlinediferencia
            FROM tbl_estructurasescuelas te
            join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
            WHERE te.fk_estructura = {$sede}";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    }

    public function getCantidadInscritosGenero($periodo,$sede,$sexo){
      $sex = $sexo ? 'true' : 'false' ;
      $SQL = "SELECT  
              escuela,
              administrativo - CASE WHEN niadministrativo IS NULL THEN 0 ELSE niadministrativo END AS administrativo,
              academico - CASE WHEN niacademico IS NULL THEN 0 ELSE niacademico END AS academico,
              onlineadministrativo,
              onlineacademico,
              CASE WHEN niadministrativo IS NULL THEN 0 ELSE niadministrativo END,
              CASE WHEN niacademico IS NULL THEN 0 ELSE niacademico END,
              administrativo + onlineadministrativo AS totaladministrativo,
              academico + onlineacademico AS totalacademico
            FROM (
              SELECT ta.valor AS Escuela,
                          /*Administrativo*/
                          (SELECT count(ti.pk_inscripcion)
                          FROM tbl_usuarios tu
                          JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                          WHERE ti.fk_estructura = te.fk_estructura
                          AND ti.fk_atributo = te.fk_atributo
                          AND ti.fk_periodo = {$periodo}
                          AND tu.sexo = {$sex}
                          AND ti.online = false) AS administrativo,
                          /*Academico*/
                          (SELECT count(ti.pk_inscripcion)
                          FROM tbl_usuarios tu
                          JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                          JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                          WHERE ti.fk_estructura = te.fk_estructura
                          AND ti.fk_atributo = te.fk_atributo
                          AND ti.fk_periodo = {$periodo}
                          AND ti.online = false
                          AND tu.sexo = {$sex}
                          AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                              FROM tbl_recordsacademicos tr
                              WHERE tr.fk_inscripcion = ti.pk_inscripcion
                              GROUP BY tr.fk_inscripcion)) AS academico,
                          /*Administrativo Online*/
                            (SELECT count(ti.pk_inscripcion)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            WHERE ti.fk_estructura = te.fk_estructura
                            AND tu.sexo = {$sex}
                            AND ti.fk_atributo = te.fk_atributo
                            AND ti.fk_periodo = {$periodo}
                            AND ti.online = true) AS onlineadministrativo,
                            /*Academico Online*/
                            (SELECT count(ti.pk_inscripcion)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            WHERE ti.fk_estructura = te.fk_estructura
                            AND tu.sexo = {$sex}
                            AND ti.fk_atributo = te.fk_atributo
                            AND ti.fk_periodo = {$periodo}
                            AND ti.online = true
                            AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                                FROM tbl_recordsacademicos tr
                                WHERE tr.fk_inscripcion = ti.pk_inscripcion
                                GROUP BY tr.fk_inscripcion)) AS onlineacademico,
                          /*Nuevo Ingreso Administrativo*/
                          (SELECT count(distinct pk_usuario)
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
                          AND u.sexo = {$sex}
                          AND i.fk_periodo = {$periodo}
                          AND i.fk_estructura = te.fk_estructura
                          AND i.fk_atributo = te.fk_atributo
                          AND i.fk_pensum in (20,21,22,23,24,25,26)
                          GROUP BY a.valor, i.fk_atributo
                          ORDER BY i.fk_atributo) AS NIAdministrativo,
                          /*Nuevo Ingreso Academico*/
                          (SELECT count(distinct pk_usuario)
                          FROM tbl_usuarios u
                          JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                          JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                          JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
                          JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
                          WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
                            FROM tbl_usuariosgrupos ug
                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                            WHERE ins.fk_periodo < i.fk_periodo
                            AND ug.fk_usuario = u.pk_usuario)
                          AND u.sexo = {$sex}
                          AND i.fk_periodo = {$periodo}
                          AND i.fk_estructura = te.fk_estructura
                          AND i.fk_atributo = te.fk_atributo
                          AND i.fk_pensum in (20,21,22,23,24,25,26)
                          GROUP BY a.valor, i.fk_atributo
                          ORDER BY i.fk_atributo) AS NIAcademico
                          FROM tbl_estructurasescuelas te
                          JOIN tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
                          WHERE te.fk_estructura = {$sede}
                          UNION ALL
                          SELECT 'Total' AS Escuela,
                              /*Administrativo*/
                              sum((SELECT count(ti.pk_inscripcion)
                              FROM tbl_usuarios tu
                              JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                              JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                              WHERE ti.fk_estructura = te.fk_estructura
                              AND tu.sexo = {$sex}
                              AND ti.fk_atributo = te.fk_atributo
                              AND ti.fk_periodo = {$periodo}
                              AND ti.online = false)) AS administrativo,  
                              /*Academico*/
                              sum((SELECT count(ti.pk_inscripcion)
                              FROM tbl_usuarios tu
                              JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                              JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                              WHERE ti.fk_estructura = te.fk_estructura
                              AND tu.sexo = {$sex}
                              AND ti.fk_atributo = te.fk_atributo
                              AND ti.fk_periodo = {$periodo}
                              AND ti.online = false
                              AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                                FROM tbl_recordsacademicos tr
                                WHERE tr.fk_inscripcion = ti.pk_inscripcion
                                GROUP BY tr.fk_inscripcion))) AS academico,
                             /*Administrativo Online*/
                            sum((SELECT count(ti.pk_inscripcion)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            WHERE ti.fk_estructura = te.fk_estructura
                            AND tu.sexo = {$sex}
                            AND ti.fk_atributo = te.fk_atributo
                            AND ti.fk_periodo = {$periodo}
                            AND ti.online = true)) AS onlineadministrativo,
                            /*Academico Online*/
                            sum((SELECT count(ti.pk_inscripcion)
                            FROM tbl_usuarios tu
                            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
                            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
                            WHERE ti.fk_estructura = te.fk_estructura
                            AND tu.sexo = {$sex}
                            AND ti.fk_atributo = te.fk_atributo
                            AND ti.fk_periodo = {$periodo}
                            AND ti.online = true
                            AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                                FROM tbl_recordsacademicos tr
                                WHERE tr.fk_inscripcion = ti.pk_inscripcion
                                GROUP BY tr.fk_inscripcion))) AS onlineacademico,
                            /*Nuevo Ingreso Administrativo*/
                            sum((SELECT count(distinct pk_usuario)
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
                            AND u.sexo = {$sex}
                            AND i.fk_periodo = {$periodo}
                            AND i.fk_estructura = te.fk_estructura
                            AND i.fk_atributo = te.fk_atributo
                            AND i.fk_pensum in (20,21,22,23,24,25,26)
                            GROUP BY a.valor, i.fk_atributo
                            ORDER BY i.fk_atributo)) AS NIAdministrativo,
                            /*Nuevo Ingreso Academico*/
                            sum((SELECT count(distinct pk_usuario)
                            FROM tbl_usuarios u
                            JOIN tbl_usuariosgrupos ug ON u.pk_usuario = ug.fk_usuario
                            JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                            JOIN tbl_atributos a ON i.fk_atributo = a.pk_atributo
                            JOIN tbl_recordsacademicos tr ON i.pk_inscripcion = tr.fk_inscripcion
                            WHERE u.pk_usuario NOT IN (SELECT ug.fk_usuario
                              FROM tbl_usuariosgrupos ug
                              JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                              JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                              WHERE ins.fk_periodo < i.fk_periodo
                              AND ug.fk_usuario = u.pk_usuario)
                            AND u.sexo = {$sex}
                            AND i.fk_periodo = {$periodo}
                            AND i.fk_estructura = te.fk_estructura
                            AND i.fk_atributo = te.fk_atributo
                            AND i.fk_pensum in (20,21,22,23,24,25,26)
                            GROUP BY a.valor, i.fk_atributo
                            ORDER BY i.fk_atributo)) AS NIAcademico
            FROM tbl_estructurasescuelas te
            JOIN tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
            WHERE te.fk_estructura = {$sede}) AS sqt";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    }

    public function getCantidadInscritosOnline($periodo,$sede){
      $SQL = "SELECT ta.valor AS Escuela,
                    (SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE) AS Administrativo,
                    (SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE
                    AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                            FROM tbl_recordsacademicos tr
                            WHERE tr.fk_inscripcion = ti.pk_inscripcion
                            AND ti.online = TRUE
                            GROUP BY tr.fk_inscripcion)) AS Academico,
                    (SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE
                    AND ti.pk_inscripcion not IN (SELECT tr.fk_inscripcion
                            FROM tbl_recordsacademicos tr
                            WHERE tr.fk_inscripcion = ti.pk_inscripcion
                            AND ti.online = TRUE
                            GROUP BY tr.fk_inscripcion)) AS Diferencia
                FROM tbl_estructurasescuelas te
                join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
                WHERE te.fk_estructura = {$sede}
                UNION ALL
                SELECT 'Total' AS Escuela,
                    sum((SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE)) AS Administrativo,
                    sum((SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE
                    AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                            FROM tbl_recordsacademicos tr
                            WHERE tr.fk_inscripcion = ti.pk_inscripcion
                            AND ti.online = TRUE
                            GROUP BY tr.fk_inscripcion))) AS Academico,
                    sum(((SELECT count(*)
                    FROM tbl_inscripciones ti
                    WHERE ti.fk_estructura = te.fk_estructura
                    AND ti.fk_atributo = te.fk_atributo
                    AND ti.fk_periodo = {$periodo}
                    AND ti.online = TRUE
                    AND ti.pk_inscripcion not IN (SELECT tr.fk_inscripcion
                            FROM tbl_recordsacademicos tr
                            WHERE tr.fk_inscripcion = ti.pk_inscripcion
                            AND ti.online = TRUE
                            GROUP BY tr.fk_inscripcion)))) AS Diferencia
                FROM tbl_estructurasescuelas te
                join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
                WHERE te.fk_estructura = {$sede}";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    }

    public function getCantidadInscritosPorMateria($periodo,$sede,$escuela,$pensum,$semestre,$check){
      $WhereCheck = ($check) ? " WHERE inscritos < 6 " : "";
      $SQL = "SELECT *
        FROM (
          SELECT ta.pk_asignacion,ts.pk_asignatura,ts.codigopropietario, tt.valor AS asignatura, tt1.valor as seccion, count(*) as inscritos,
              (SELECT count(*)
                FROM tbl_inscripciones ti1
                JOIN tbl_recordsacademicos tr1 ON ti1.pk_inscripcion = tr1.fk_inscripcion
                JOIN tbl_asignaciones ta1 ON tr1.fk_asignacion =ta1.pk_asignacion
                JOIN tbl_asignaturas ts1 ON ta1.fk_asignatura = ts1.pk_asignatura
                WHERE ti1.fk_estructura = {$sede}     
                  AND ti1.fk_atributo = {$escuela}
                  AND ts1.fk_pensum = {$pensum}  
                  AND ti1.fk_periodo = {$periodo}  
                  AND ta1.fk_semestre = {$semestre} 
                  AND tr1.fk_atributo = 863   
                  AND ta1.pk_asignacion = ta.pk_asignacion
              )AS Retirados,
              ta.cupos, ta.cupos_max,
              (round(((count(*) *100) / ta.cupos)::NUMERIC, 0
              ) || ' %') AS alert,
              (SELECT DISTINCT CASE WHEN COUNT (asigF.pk_asignacion) > 1 THEN 'SI' ELSE 'NO' END AS result
                FROM tbl_asignaciones asiF 
                JOIN tbl_asignaciones asigF ON asigF.fk_horario = asiF.fk_horario 
                  AND asigF.fk_periodo = asiF.fk_periodo 
                  AND asigF.fk_estructura = asiF.fk_estructura
                  AND asigF.fk_usuariogrupo = asiF.fk_usuariogrupo
                  AND asigF.fk_seccion = asiF.fk_seccion
                  AND asigF.fk_dia = asiF.fk_dia
                WHERE asiF.pk_asignacion = ta.pk_asignacion
              ) AS fusion,
              (SELECT DISTINCT  count (ug.fk_usuario )
                FROM tbl_asignaciones asi 
                JOIN tbl_asignaciones asig ON asig.fk_horario = asi.fk_horario 
                  AND asig.fk_periodo = asi.fk_periodo 
                  AND asig.fk_estructura = asi.fk_estructura
                  AND asig.fk_usuariogrupo = asi.fk_usuariogrupo
                  AND asig.fk_dia = asi.fk_dia
                JOIN tbl_recordsacademicos ra ON ra.fk_asignacion = asig.pk_asignacion
                JOIN tbl_inscripciones ins ON ins.pk_inscripcion = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                WHERE asi.pk_asignacion = ta.pk_asignacion
              ) AS total
                  FROM tbl_inscripciones ti
                  JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
                  JOIN tbl_asignaciones ta ON tr.fk_asignacion =ta.pk_asignacion
                  JOIN tbl_asignaturas ts ON ta.fk_asignatura = ts.pk_asignatura
                  JOIN tbl_atributos tt ON ts.fk_materia = tt.pk_atributo
                  JOIN tbl_atributos tt1 ON ta.fk_seccion = tt1.pk_atributo
                  WHERE ti.fk_estructura = {$sede}   
              AND ti.fk_atributo = {$escuela}  
              AND ts.fk_pensum = {$pensum}  
              AND ti.fk_periodo = {$periodo}  
              AND ta.fk_semestre = {$semestre} 
      
          GROUP BY ts.codigopropietario,ta.pk_asignacion,ts.pk_asignatura,tt.valor, tt1.valor,ta.cupos, ta.cupos_max
          ORDER BY asignatura,seccion,inscritos
        ) as sqt
               {$WhereCheck}";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    }

    public function getTotalCantidadInscritos($periodo,$sede){
      $SQL = "SELECT ta.valor AS Escuela,
              (SELECT count(*)
              FROM tbl_inscripciones ti
              WHERE ti.fk_estructura = te.fk_estructura
              AND ti.fk_atributo = te.fk_atributo
              AND ti.fk_periodo = {$periodo}
            ) AS Administrativo,
              (SELECT count(*)
              FROM tbl_inscripciones ti
              WHERE ti.fk_estructura = te.fk_estructura
              AND ti.fk_atributo = te.fk_atributo
              AND ti.fk_periodo = {$periodo}
              AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
                      FROM tbl_recordsacademicos tr
                      WHERE tr.fk_inscripcion = ti.pk_inscripcion
                      GROUP BY tr.fk_inscripcion)
            ) AS Academico,
              (SELECT count(*)
              FROM tbl_inscripciones ti
              WHERE ti.fk_estructura = te.fk_estructura
              AND ti.fk_atributo = te.fk_atributo
              AND ti.fk_periodo = {$periodo}
              AND ti.pk_inscripcion NOT IN (SELECT tr.fk_inscripcion
                      FROM tbl_recordsacademicos tr
                      WHERE tr.fk_inscripcion = ti.pk_inscripcion
                      GROUP BY tr.fk_inscripcion)
            ) AS Diferencia
          FROM tbl_estructurasescuelas te
          join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
          WHERE te.fk_estructura = {$sede}
          UNION ALL
          SELECT 'Total' AS Escuela,
            /*Administrativo*/
            sum((SELECT count(*)
            FROM tbl_inscripciones ti
            WHERE ti.fk_estructura = te.fk_estructura
            AND ti.fk_atributo = te.fk_atributo
            AND ti.fk_periodo = {$periodo})) 
            AS Administrativo,
            /*Academico*/
            sum((SELECT count(*)
            FROM tbl_inscripciones ti
            WHERE ti.fk_estructura = te.fk_estructura
            AND ti.fk_atributo = te.fk_atributo
            AND ti.fk_periodo = {$periodo}
            AND ti.pk_inscripcion IN (SELECT tr.fk_inscripcion
              FROM tbl_recordsacademicos tr
              WHERE tr.fk_inscripcion = ti.pk_inscripcion
              GROUP BY tr.fk_inscripcion))) 
            AS Academico,
            /*Diferencia*/
            sum(((SELECT count(*)
            FROM tbl_inscripciones ti
            WHERE ti.fk_estructura = te.fk_estructura
            AND ti.fk_atributo = te.fk_atributo
            AND ti.fk_periodo = {$periodo}
            AND ti.pk_inscripcion not IN (SELECT tr.fk_inscripcion
              FROM tbl_recordsacademicos tr
              WHERE tr.fk_inscripcion = ti.pk_inscripcion
              GROUP BY tr.fk_inscripcion)))) 
            AS Diferencia
              FROM tbl_estructurasescuelas te
              join tbl_atributos ta ON te.fk_atributo = ta.pk_atributo
              WHERE te.fk_estructura = {$sede}";
      $results = $this->_db->query($SQL);
      return $results->fetchAll();
    }
      public function getUiltimaInfoEstudiante($ci) {
    $SQL = "SELECT DISTINCT  fk_periodo, e.pk_estructura, a.pk_atributo, p.pk_pensum, i.numeropago, i.ucadicionales
            FROM tbl_usuarios u
            JOIN tbl_usuariosgrupos ug        on u.pk_usuario        = ug.fk_usuario
            JOIN tbl_inscripciones  i         on i.fk_usuariogrupo   = ug.pk_usuariogrupo
            JOIN tbl_recordsacademicos rec    on rec.fk_inscripcion  = i.pk_inscripcion
            JOIN tbl_pensums p                on p.pk_pensum         = i.fk_pensum
            JOIN tbl_estructuras e            on e.pk_estructura     = i.fk_estructura
            JOIN tbl_atributos a              on a.pk_atributo       = i.fk_atributo
            WHERE pk_usuario = {$ci}
            AND p.codigopropietario NOT IN (9,18,0)
            ORDER by 1 DESC
            LIMIT 1;";

    $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
  }

  public function getFkUsuariogrupo($ci){
    $SQL = "SELECT pk_usuariogrupo 
        FROM   tbl_usuarios
        JOIN   tbl_usuariosgrupos ON pk_usuario = fk_usuario
        WHERE  pk_usuario = {$ci}
        AND    fk_grupo = 855;";

     return $this->_db->fetchOne($SQL);
  }

  public function insertInscripcion($fk_usuariogrupo,$numeropago,$periodo,$escuela,$sede,$UCA,$pensum){	
      $SQL = " INSERT INTO tbl_inscripciones (fk_usuariogrupo,
                        numeropago,
                        fk_periodo,
                        fk_atributo,
                        fk_estructura,
                        ucadicionales,
                        fk_semestre,
                        observaciones,
                        fk_tipo,
                        pago_manual,
                        online,
                        fk_pensum)
           VALUES ({$fk_usuariogrupo}, {$numeropago}, {$periodo}, {$escuela}, {$sede}, {$UCA}, 872, '', 1621, true,false,{$pensum});";  
      $this->_db->query($SQL);
  }

  public function updateInscripcion($ci,$periodo,$numeropago,$UCA,$sede,$escuela,$pensum){
    $SQL = "UPDATE tbl_inscripciones SET numeropago       = {$numeropago},
                                         ucadicionales   = {$UCA},
                                         fk_estructura   = {$sede},
                                         fk_atributo     = {$escuela},
                                         fk_pensum       = {$pensum},
                                         fechahora       = now()
            WHERE fk_usuariogrupo = (SELECT pk_usuariogrupo
                         FROM tbl_usuarios 
                         JOIN tbl_usuariosgrupos ON pk_usuario = fk_usuario
                         WHERE pk_usuario = {$ci}
                         AND fk_grupo = 855)
            AND fk_periodo = {$periodo};";
    $this->_db->query($SQL);

  }

  public function deleteInscripcion($ci,$periodo){
    $SQL = "DELETE FROM tbl_inscripciones 
        WHERE fk_usuariogrupo = (SELECT pk_usuariogrupo
                     FROM tbl_usuarios 
                     JOIN tbl_usuariosgrupos ON pk_usuario = fk_usuario
                     WHERE pk_usuario = {$ci}
                     AND fk_grupo = 855)
        AND fk_periodo = {$periodo};";

    $this->_db->query($SQL);
  }

  public function getInscripcion($ci,$periodo){
    $SQL = "SELECT fk_periodo,numeropago, fk_atributo, fk_estructura,ucadicionales,fk_pensum 
            FROM tbl_inscripciones
            WHERE fk_periodo = {$periodo}
            AND fk_usuariogrupo = (SELECT pk_usuariogrupo 
                                   FROM tbl_usuariosgrupos
                                   WHERE fk_usuario = {$ci}
                                   AND fk_grupo = 855)
            ORDER BY 1 DESC;";
    $results = $this->_db->query($SQL);

    return (array)$results->fetchAll();
  }

  public function insmateriastesis($escuela, $id,$periodo){

    $SQL = "SELECT count(*) from (
        SELECT  pk_asignatura, case
        when valor like 'TESIS%' then valor || ' (1997)' 
        when valor like 'TRABAJO%' then valor || ' (2012)' 
        else valor
        end as valor
        from tbl_asignaturas asna
        join tbl_pensums pen on pen.pk_pensum = asna.fk_pensum
        join tbl_atributos atr on atr.pk_atributo = asna.fk_materia
        join tbl_recordsacademicos ra on  ra.fk_asignatura = asna.pk_asignatura
        join tbl_inscripciones ins on ins.pk_inscripcion = ra.fk_inscripcion
        join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = ins.fk_usuariogrupo
        join tbl_usuarios u on u.pk_usuario = ug.fk_usuario
        where    
        fk_escuela = {$escuela}
        and pk_pensum in (8,9,10,11,12,20,21,22,23,24,25)
        and u.pk_usuario = {$id}
        and fk_periodo = {$periodo}
        and atr.valor in( 'INVESTIGACIÓN Y DESARROLLO', 'TESIS DE GRADO I', 'TESIS DE GRADO II', 'SEMINARIO DE TRABAJO DE GRADO', 'TRABAJO DE GRADO I', 'TRABAJO DE GRADO II')
        order by valor) as mattesis
    ";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
  }
  public function getpensum($ci,$periodo) {

        $SQL = "SELECT fk_pensum
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE i.fk_periodo = {$periodo}
                  AND ug.fk_usuario = {$ci};";

        return $this->_db->fetchOne($SQL);
    }

  public function getsedeperiodo($ci,$periodo){

        $SQL = "SELECT fk_estructura, sed.nombre
                FROM tbl_usuariosgrupos  ug
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = i.fk_estructura
                WHERE i.fk_periodo = {$periodo}
                  AND ug.fk_usuario = {$ci};
                ";


        return $this->_db->fetchOne($SQL);

    }

public function getMatriculaSemestre($iPeriodo,$iSede){
      $SQL = "SELECT *

      FROM (
        (SELECT escuela,
          a1 as \"1\",
          b2 as \"2\",
          c3 as \"3\",
          d4 as \"4\",
          e5 as \"5\",
          f6 as \"6\",
          g7 as \"7\",
          h8 as \"8\",
          i9 as \"9\",
          j10 as \"10\",
              k11 as \"11\",
              l12 as \"12\",
          total as \"TOTAL\"
      
      
        FROM
        (
        SELECT  escuela,
           a AS \"a1\",
           b AS \"b2\",
           c AS \"c3\",
           d AS \"d4\",
           e AS \"e5\",
           f AS \"f6\",
           g AS \"g7\",
           h AS \"h8\",
           i AS \"i9\",
           j AS \"j10\",
              k AS \"k11\",
              l AS \"l12\",
           (sum(a)+sum(b)+sum(c)+sum(d)+sum(e)+sum(f)+sum(g)+sum(h)+sum(i)+sum(j)+sum(k)+sum(l)) AS total
      
         FROM(SELECT  escuela        AS \"escuela\",
                COUNT(uno)     AS \"a\",
                COUNT(dos)     AS \"b\",
                COUNT(tres)    AS \"c\",
                COUNT(cuatro)  AS \"d\",
                COUNT(cinco)   AS \"e\",
                COUNT(seis)    AS \"f\",
                COUNT(siete)   AS \"g\",
                COUNT(ocho)    AS \"h\",
                COUNT(nueve)   AS \"i\",
                COUNT(diez)    AS \"j\",
                  COUNT(once)    AS \"k\",
                  COUNT(doce)    AS \"l\"
      
      
          FROM (SELECT  escuela AS \"escuela\",
            CASE ubic when 1  THEN usr end AS \"uno\",
            CASE ubic when 2  THEN usr end AS \"dos\",
            CASE ubic when 3  THEN usr end AS \"tres\",
            CASE ubic when 4  THEN usr end AS \"cuatro\",
            CASE ubic when 5  THEN usr end AS \"cinco\",
            CASE ubic when 6  THEN usr end AS \"seis\",
            CASE ubic when 7  THEN usr end AS \"siete\",
            CASE ubic when 8  THEN usr end AS \"ocho\",
            CASE ubic when 9  THEN usr end AS \"nueve\",
            CASE ubic when 10 THEN usr end AS \"diez\",
                  CASE ubic when 11 THEN usr end AS \"once\",
                  CASE ubic when 12 THEN usr end AS \"doce\"
      
                 FROM (SELECT DISTINCT ug.fk_usuario  AS \"usr\",
                     es.escuela     AS \"escuela\",
                  COALESCE(fn_xrxx_estudiante_sem_ubicacion_periodod2(ug.fk_usuario,i.fk_atributo,i.fk_periodo,i.fk_pensum)) AS \"ubic\"
      
              FROM tbl_inscripciones i
               JOIN tbl_usuariosgrupos      ug ON   ug.pk_usuariogrupo = i.fk_usuariogrupo
               JOIN tbl_recordsacademicos   ra ON    i.pk_inscripcion  = ra.fk_inscripcion
               JOIN tbl_asignaturas       asig ON asig.pk_asignatura   = ra.fk_asignatura
               JOIN vw_escuelas             es ON    i.fk_atributo     = es.pk_atributo
                                       join tbl_pensums p               ON    p.pk_pensum       = i.fk_pensum
              WHERE    i.fk_estructura = {$iSede}
                AND    i.fk_periodo = {$iPeriodo}
      
                 GROUP BY es.escuela,\"ubic\",ug.fk_usuario) AS foo
      
            GROUP BY escuela,foo.ubic, foo.usr
            ORDER BY 1 asc) AS foo2
      
          GROUP BY escuela) AS foo3
      
        GROUP BY escuela,foo3.a,foo3.b,foo3.c,foo3.d,foo3.e,foo3.f,foo3.g,foo3.h,foo3.i,foo3.j,foo3.k,foo3.l
        ORDER BY 1 asc
        ) AS foo4
        GROUP BY escuela,foo4.a1,foo4.b2,foo4.c3,foo4.d4,foo4.e5,foo4.f6,foo4.g7,foo4.h8,foo4.i9,foo4.j10,foo4.k11,foo4.l12,foo4.TOTAL
        ORDER BY 1 asc
      
        )UNION (
      SELECT 'Totales' 	AS \"TOTALES\",
             SUM(uno) 	AS \"1\",
             SUM(dos) 	AS \"2\",
             SUM(tres)	AS \"3\",
             SUM(cuatro) 	AS \"4\",
             SUM(cinco) 	AS \"5\",
             SUM(seis) 	AS \"6\",
             SUM(siete) 	AS \"7\",
             SUM(ocho) 	AS \"8\",
             SUM(nueve) 	AS \"9\",
             SUM(diez) 	AS \"10\",
             SUM(once) 	AS \"11\",
             SUM(doce) 	AS \"12\",
             SUM(total) 	AS \"TOTAL\"
      
      
      FROM (
      (SELECT
          a1 as \"uno\",
          b2 as \"dos\",
          c3 as \"tres\",
          d4 as \"cuatro\",
          e5 as \"cinco\",
          f6 as \"seis\",
          g7 as \"siete\",
          h8 as \"ocho\",
          i9 as \"nueve\",
          j10 as \"diez\",
          k11 as \"once\",
          l12 as \"doce\",
          total as \"total\"
      
      
        FROM
        (
        SELECT  escuela,
           a AS \"a1\",
           b AS \"b2\",
           c AS \"c3\",
           d AS \"d4\",
           e AS \"e5\",
           f AS \"f6\",
           g AS \"g7\",
           h AS \"h8\",
           i AS \"i9\",
           j AS \"j10\",
           k AS \"k11\",
           l AS \"l12\",
           (sum(a)+sum(b)+sum(c)+sum(d)+sum(e)+sum(f)+sum(g)+sum(h)+sum(i)+sum(j)+sum(k)+sum(l)) AS total
      
         FROM(SELECT  escuela        AS \"escuela\",
                COUNT(uno)     AS \"a\",
                COUNT(dos)     AS \"b\",
                COUNT(tres)    AS \"c\",
                COUNT(cuatro)  AS \"d\",
                COUNT(cinco)   AS \"e\",
                COUNT(seis)    AS \"f\",
                COUNT(siete)   AS \"g\",
                COUNT(ocho)    AS \"h\",
                COUNT(nueve)   AS \"i\",
                COUNT(diez)    AS \"j\",
                COUNT(once)    AS \"k\",
                COUNT(doce)    AS \"l\"
      
      
          FROM (SELECT  escuela AS \"escuela\",
            CASE ubic when 1  THEN usr end AS \"uno\",
            CASE ubic when 2  THEN usr end AS \"dos\",
            CASE ubic when 3  THEN usr end AS \"tres\",
            CASE ubic when 4  THEN usr end AS \"cuatro\",
            CASE ubic when 5  THEN usr end AS \"cinco\",
            CASE ubic when 6  THEN usr end AS \"seis\",
            CASE ubic when 7  THEN usr end AS \"siete\",
            CASE ubic when 8  THEN usr end AS \"ocho\",
            CASE ubic when 9  THEN usr end AS \"nueve\",
            CASE ubic when 10 THEN usr end AS \"diez\",
                  CASE ubic when 11 THEN usr end AS \"once\",
                  CASE ubic when 12 THEN usr end AS \"doce\"
      
                 FROM (SELECT DISTINCT ug.fk_usuario  AS \"usr\",
                     es.escuela     AS \"escuela\",
                  COALESCE(fn_xrxx_estudiante_sem_ubicacion_periodod2(ug.fk_usuario,i.fk_atributo,i.fk_periodo,i.fk_pensum)) AS \"ubic\"
      
              FROM tbl_inscripciones i
               JOIN tbl_usuariosgrupos      ug ON   ug.pk_usuariogrupo = i.fk_usuariogrupo
               JOIN tbl_recordsacademicos   ra ON    i.pk_inscripcion  = ra.fk_inscripcion
               JOIN tbl_asignaturas       asig ON asig.pk_asignatura   = ra.fk_asignatura
               JOIN vw_escuelas             es ON    i.fk_atributo     = es.pk_atributo
                                       join tbl_pensums p               ON    p.pk_pensum       = i.fk_pensum
              WHERE    i.fk_estructura = {$iSede}
                AND    i.fk_periodo = {$iPeriodo}

                 GROUP BY es.escuela,\"ubic\",ug.fk_usuario) AS foo
      
            GROUP BY escuela,foo.ubic, foo.usr
            ORDER BY 1 asc) AS foo2
      
          GROUP BY escuela) AS foo3
      
        GROUP BY escuela,foo3.a,foo3.b,foo3.c,foo3.d,foo3.e,foo3.f,foo3.g,foo3.h,foo3.i,foo3.j,foo3.k,foo3.l
        ORDER BY 1 asc
        ) AS foo4
        GROUP BY escuela,foo4.a1,foo4.b2,foo4.c3,foo4.d4,foo4.e5,foo4.f6,foo4.g7,foo4.h8,foo4.i9,foo4.j10,foo4.k11,foo4.l12,foo4.TOTAL
        ORDER BY 1 asc)) AS sqt5
        )) AS final
      ORDER BY 1";

      $results = $this->_db->query($SQL);
      return $results->fetchAll();
      }


}
