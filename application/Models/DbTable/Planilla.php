<?php
class Models_DbTable_Planilla extends Zend_Db_Table {
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
    
        public function getPeriodosCursados($Ci) {
        if(empty($Ci)) return;//
        
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
			WHERE fk_usuario = $Ci --AND
				--ra.fk_atributo <> 864 AND ra.fk_atributo <> 904
			ORDER BY fk_periodo DESC;";
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

