<?php

class Models_DbTable_Solventes extends Zend_Db_Table {

    public function getGrupos($ci){
        
        $SQL =  "SELECT g.grupo, g.pk_atributo
                FROM tbl_usuariosgrupos ug
                JOIN vw_grupos g ON g.pk_atributo = ug.fk_grupo
                WHERE ug.fk_usuario = $ci
                AND g.pk_atributo IN (855,854,1745)";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getDocenteActivo($ci, $periodo){
        
        $SQL =  "SELECT * 
                FROM tbl_usuariosgrupos ug
                JOIN tbl_asignaciones ag ON ag.fk_usuariogrupo = ug.pk_usuariogrupo
                WHERE ug.fk_usuario = $ci
                AND ag.fk_periodo = $periodo";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getUltPeriodo(){
        
        $SQL =  "SELECT pk_periodo
                FROM tbl_periodos
                WHERE CURRENT_DATE >= fechainicio AND CURRENT_DATE <= fechafin
                ORDER BY 1 DESC";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    
    
    public function getEstuInscr($ci, $periodo){
        
        $SQL =  "SELECT *
                FROM tbl_inscripciones ins
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                WHERE ug.fk_usuario = $ci
                AND ins.fk_periodo = $periodo";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }
    
    
}
