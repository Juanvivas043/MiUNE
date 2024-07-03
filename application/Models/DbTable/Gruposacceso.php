<?php

class Models_DbTable_Gruposacceso extends Zend_Db_Table {
      public function init() {
         
    }
    
    public function getAcceso($grupo){
        
    $SQL = "SELECT DISTINCT ac.pk_acceso , nombre , include , a.fk_acceso 
            FROM tbl_accesosgrupos a
            JOIN tbl_accesos ac ON a.fk_acceso = ac.pk_acceso
            JOIN vw_grupos gr ON gr.pk_atributo = a.fk_grupo 
            WHERE a.fk_grupo = $grupo AND visibility = true
            ORDER BY nombre;-- ";
         
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    
    public function getTodo($grupo){
        
       $SQL = " SELECT DISTINCT  h.pk_acceso ,h.nombre 
            FROM tbl_accesos h
            WHERE pk_acceso  NOT IN (SELECT DISTINCT ac.pk_acceso
            FROM tbl_accesosgrupos a
            JOIN tbl_accesos ac ON a.fk_acceso = ac.pk_acceso
            JOIN vw_grupos gr ON gr.pk_atributo = a.fk_grupo 
            WHERE a.fk_grupo = {$grupo}) 
            order by h.nombre;";
            
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getAgregar($grupo , $value){
       $SQL = "INSERT INTO tbl_accesosgrupos (fk_acceso,fk_grupo,visibility)
                VALUES ({$value},{$grupo},'t');";
       
       $results = $this->_db->query($SQL);
       $results = $results->fetchAll();
       return $results;
    }
    
    public function getEliminar($grupo,$value){
        $SQL = "DELETE FROM tbl_accesosgrupos
                WHERE fk_acceso = {$value} and fk_grupo = {$grupo};";
                
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getLlenado(){
        $SQL = "SELECT pk_atributo , grupo   
                FROM vw_grupos
                ORDER BY grupo;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
}
?>
