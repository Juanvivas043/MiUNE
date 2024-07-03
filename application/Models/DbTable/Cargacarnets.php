<?php
class Models_DbTable_Cargacarnets extends Zend_Db_Table {
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
    
        public function getPeriodos() {
        
        $SQL     = "SELECT pk_periodo 
                    FROM tbl_periodos p
                    ORDER BY 1 desc;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function getAfinidades() {
        
        $SQL     = "SELECT pk_afinidad, nombre
                    FROM vw_pandaid_afinidades a
                    ORDER BY 1 desc;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function getDocentes($periodo) {
        
            
        $SQL     = "SELECT  DISTINCT u.pk_usuario, u.nombre, u.apellido
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE (ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo} AND pk_usuario > 0)
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}
                                                                                 AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo) 	
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
         public function getDocentesafinidad($periodo) {
        
            
        $SQL     = "SELECT usr.pk_usuario,usr.nombre , usr.apellido , sqt.estado 
FROM(
SELECT *,CASE 
	WHEN (ca.pk_carnet IS NOT null AND ua.pk_usuarioafinidad IS null AND ca.fk_estado = 1628 ) THEN 'POR REVISAR'
	WHEN (ua.pk_usuarioafinidad IS NOT null AND ca.pk_carnet IS null) THEN  'LISTO PARA EMITIR'
	WHEN (ua.pk_usuarioafinidad IS null AND ca.pk_carnet IS NULL ) THEN 'POR CARGAR'
	 Else 'OK' END as Estado
FROM tbl_usuariosgrupos usu 
LEFT JOIN tbl_usuariosafinidades ua ON usu.fk_usuario = ua.fk_usuario
FULL OUTER JOIN tbl_carnets ca ON ca.fk_usuariogrupo = usu.pk_usuariogrupo
WHERE usu.fk_usuario IN (SELECT DISTINCT u.pk_usuario
			FROM tbl_usuarios u
			JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
			JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
			WHERE (ug.fk_grupo = 854  AND asig.fk_periodo = {$periodo} AND pk_usuario > 0)
                    
                           AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}   AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo)
                                                                                  	
                 				     
                                                    order by 1)

                                                    )as sqt
                                                    JOIN tbl_usuariosgrupos usu ON usu.pk_usuariogrupo = sqt.pk_usuariogrupo
                                                    JOIN tbl_usuarios usr ON usr.pk_usuario = usu.fk_usuario
                                                    WHERE estado!= 'OK'
                                                    AND usu.FK_GRUPO = 854
                                                    ORDER BY 4 DESC,1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function getAdministrativos($periodo) {
        
        $SQL     = "SELECT  DISTINCT u.pk_usuario, u.nombre, u.apellido
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    --JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE ug.fk_grupo = 1745  --OR 
                    --(ug.fk_grupo = 854 AND asig.fk_periodo = 123 AND pk_usuario > 0)
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    WHERE ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo})
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}
                                                                                 AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo) 	
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function getAdministrativosafinidad($periodo) {
        
        $SQL     = "SELECT usr.pk_usuario,usr.nombre , usr.apellido , sqt.estado 
                    FROM(
                    SELECT *,CASE 
                            WHEN (ca.pk_carnet IS NOT null AND ua.pk_usuarioafinidad IS null AND ca.fk_estado = 1628 ) THEN 'POR REVISAR'
                            WHEN (ua.pk_usuarioafinidad IS NOT null AND ca.pk_carnet IS null) THEN  'LISTO PARA EMITIR'
                            WHEN (ua.pk_usuarioafinidad IS null AND ca.pk_carnet IS NULL ) THEN 'POR CARGAR'
                             Else 'OK' END as Estado
                    FROM tbl_usuariosgrupos usu 
                    LEFT JOIN tbl_usuariosafinidades ua ON usu.fk_usuario = ua.fk_usuario
                    FULL OUTER JOIN tbl_carnets ca ON ca.fk_usuariogrupo = usu.pk_usuariogrupo
                    WHERE usu.fk_usuario IN (SELECT DISTINCT u.pk_usuario
                                        FROM tbl_usuarios u
                                        JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                        --JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                                        WHERE ug.fk_grupo = 1745  --OR 
                                        --(ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo} AND pk_usuario > 0)
                                                AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    WHERE ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo})
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}
                                                                                 AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo) 	
										order by 1)

                                                                )as sqt
                                                                JOIN tbl_usuariosgrupos usu ON usu.pk_usuariogrupo = sqt.pk_usuariogrupo
                                                                JOIN tbl_usuarios usr ON usr.pk_usuario = usu.fk_usuario
                                                                WHERE estado!= 'OK'
                                                                AND sqt.FK_GRUPO = 1745
                                                                ORDER BY 4 DESC,1

                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        
        public function getEstudiantes($periodo) {
        
        $SQL     = "SELECT  DISTINCT u.pk_usuario, u.nombre, u.apellido
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                   -- WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                   --                                FROM tbl_recordsacademicos ra1
                   --                                WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                    --AND
			WHERE  ins.fk_periodo = {$periodo}
                    AND estes.fk_atributo <> 920
                    --AND est.pk_estructura = 7
                    AND ins.fk_atributo = estes.fk_atributo
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
           public function getEstudiantesafinidad($periodo) {
        
                                    $SQL = "
                           SELECT usr.pk_usuario,usr.nombre , usr.apellido , sqt.estado 
                           FROM(
                           SELECT *,CASE 
                                   WHEN (ca.pk_carnet IS NOT null AND ua.pk_usuarioafinidad IS null AND ca.fk_estado = 1628 ) THEN 'POR REVISAR'
                                   WHEN (ua.pk_usuarioafinidad IS NOT null AND ca.pk_carnet IS null) THEN  'LISTO PARA EMITIR'
                                   WHEN (ua.pk_usuarioafinidad IS null AND ca.pk_carnet IS NULL ) THEN 'POR CARGAR'

                                    Else 'OK' END as Estado
                           FROM tbl_usuariosgrupos usu 
                           FULL OUTER JOIN tbl_usuariosafinidades ua ON usu.fk_usuario = ua.fk_usuario
                           FULL OUTER JOIN tbl_carnets ca ON ca.fk_usuariogrupo = usu.pk_usuariogrupo
                           WHERE usu.fk_usuario IN (
                           SELECT  DISTINCT u.pk_usuario
                                               FROM tbl_usuarios u
                                               JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                               JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                               JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                               JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                              -- WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                              --                                FROM tbl_recordsacademicos ra1
                                              --                                WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                               --AND
                                                   WHERE  ins.fk_periodo = {$periodo}
                                               AND estes.fk_atributo <> 920
                                               --AND est.pk_estructura = 7
                                               AND ins.fk_atributo = estes.fk_atributo

                                               order by 1)

                           )as sqt
                           JOIN tbl_usuariosgrupos usu ON usu.pk_usuariogrupo = sqt.pk_usuariogrupo
                           JOIN tbl_usuarios usr ON usr.pk_usuario = usu.fk_usuario
                           WHERE estado!= 'OK'
                           AND sqt.FK_GRUPO = 855
                           ORDER BY 4 DESC,1
                                                                                  ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function setDocentes($periodo,$afinidad) {
        
        $SQL     = "INSERT INTO tbl_usuariosafinidades(fk_usuario, fk_afinidad, fk_autorizacion)
                    SELECT  DISTINCT u.pk_usuario, {$afinidad}, 1617
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE (ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo} AND pk_usuario > 0)
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}
                                                                                 AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo) 	
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function setAdministrativos($periodo, $afinidad) {
        
        $SQL     = "INSERT INTO tbl_usuariosafinidades(fk_usuario, fk_afinidad, fk_autorizacion)
                    SELECT  DISTINCT u.pk_usuario, {$afinidad}, 1617
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    --JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE ug.fk_grupo = 1745  --OR 
                    --(ug.fk_grupo = 854 AND asig.fk_periodo = 123 AND pk_usuario > 0)
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_asignaciones asig ON asig.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    WHERE ug.fk_grupo = 854 AND asig.fk_periodo = {$periodo})
                            AND u.pk_usuario NOT IN (SELECT  DISTINCT u.pk_usuario
                                                    FROM tbl_usuarios u
                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                                                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                                                    WHERE ins.pk_inscripcion IN (SELECT ra1.fk_inscripcion
                                                                                 FROM tbl_recordsacademicos ra1
                                                                                 WHERE ra1.fk_inscripcion = ins.pk_inscripcion)
                                                                                 AND ins.fk_periodo = {$periodo}
                                                                                 AND estes.fk_atributo <> 920
                                                                                 --AND est.pk_estructura = 7
                                                                                 AND ins.fk_atributo = estes.fk_atributo) 	
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function setEstudiantes($periodo,$afinidad) {
        
        $SQL     = "INSERT INTO tbl_usuariosafinidades(fk_usuario, fk_afinidad, fk_autorizacion)
                    SELECT  DISTINCT u.pk_usuario, {$afinidad}, 1617
                    FROM tbl_usuarios u
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_estructuras est ON est.pk_estructura = ins.fk_estructura
                    JOIN tbl_estructurasescuelas estes ON estes.fk_estructura = est.pk_estructura
                    WHERE ins.fk_periodo = {$periodo}
                    AND estes.fk_atributo <> 920
                    --AND est.pk_estructura = 7
                    AND ins.fk_atributo = estes.fk_atributo
                    AND u.pk_usuario NOT IN (SELECT fk_usuario
                                             FROM tbl_usuariosafinidades)				     
                    order by 1
                   ";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function setEstudiantesCarnet($cedula) {
        
        $SQL     = "INSERT INTO tbl_usuariosafinidades(fk_usuario, fk_afinidad, fk_autorizacion)
                    VALUES({$cedula}, 1436, 1617);";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
        public function getEstudiantesCarnet($cedula) { 
        
        $SQL     = "select count(pk_usuarioafinidad) as cuenta from tbl_usuariosafinidades where fk_usuario = {$cedula};";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        }
        
    public function getSedes($Ci, $periodo) {
        if(empty($Ci)) return;
        
        $SQL     = "SELECT DISTINCT ins.fk_estructura, est.sede
                   FROM tbl_inscripciones ins
                   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                   JOIN vw_estructuras est ON est.pk_sede = ins.fk_estructura
                   WHERE ug.fk_usuario = $Ci AND ins.fk_periodo = $periodo;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
        public function getEstudiantePensums($cedula, $escuela, $periodo, $Sede){
        if(!empty($escuela)){
           $SQLescuela = "AND ins.fk_atributo = {$escuela}";
        }else{
           $SQLescuela = "";
        }
           $SQL = "SELECT DISTINCT pk_pensum, pen.codigopropietario, pen.nombre
                   FROM tbl_inscripciones ins
                   JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                   JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                   WHERE ug.fk_usuario = {$cedula} AND ins.fk_periodo = {$periodo} AND ins.fk_estructura = $Sede {$SQLescuela}
                   order by pk_pensum";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
        public function getEstudianteOnline($cedula, $escuela, $periodo, $Sede, $pensum){

           $SQL = "SELECT online
                    FROM tbl_usuariosgrupos ug
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_pensums p ON p.pk_pensum = ins.fk_pensum
                    WHERE fk_usuario = {$cedula}
                    AND fk_periodo = {$periodo} 
                    AND fk_atributo = {$escuela}
                    AND fk_estructura = {$Sede}
                    AND p.codigopropietario = {$pensum}";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
        public function getEstudianteNew($cedula, $escuela, $periodo, $Sede, $pensum){

           $SQL = "SELECT count(ug.fk_usuario)
                    FROM tbl_usuariosgrupos ug
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                    JOIN tbl_pensums p ON p.pk_pensum = ins.fk_pensum
                    WHERE fk_usuario = {$cedula}
                    AND fk_periodo <> {$periodo} 
                    AND ins.fk_atributo = {$escuela}
                    AND fk_estructura = {$Sede}
                    AND p.codigopropietario = {$pensum}";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
        public function getCantidadEscuela($Ci,$periodo,$sede) {
        if(empty($Ci)) return;

        $SQL = "SELECT COUNT(DISTINCT e.escuela)
                FROM tbl_recordsacademicos ra
                JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
                WHERE ug.fk_usuario = {$Ci}
                AND i.fk_periodo = {$periodo}
                AND i.fk_estructura = {$sede}
                GROUP BY ug.fk_usuario";

        return $this->_db->fetchOne($SQL);
    }
    
        public function getEscuelasEstudiante($Ci,$periodo,$sede) {
        if(empty($Ci)) return;

        $SQL = "SELECT DISTINCT e.escuela, i.fk_atributo
                FROM tbl_recordsacademicos ra
                JOIN tbl_inscripciones i on i.pk_inscripcion = ra.fk_inscripcion
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
                WHERE ug.fk_usuario = {$Ci}
                AND i.fk_periodo = {$periodo}
                AND i.fk_estructura = {$sede}";

        $results = $this->_db->query($SQL);
        $results = (array)$results->fetchAll();
        
        return $results;
    }

}

