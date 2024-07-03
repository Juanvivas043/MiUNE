<?php
class Models_DbTable_Accesosip extends Zend_Db_Table {

    public function init() {
        
    }

   
    public function getAcceso1(){
        
        $SQL ="SELECT pk_acceso , nombre
               FROM tbl_accesos
               ORDER BY nombre
               LIMIT 91";
        
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    
    public function getIp($acc){
        
        $SQL = "SELECT pk_accesoip , client_ip
                FROM tbl_accesosip
                WHERE fk_acceso = {$acc}
                ORDER BY pk_accesoip";
          
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }
    public function getAgregar($acc,$ip){
        
       $SQL ="INSERT INTO tbl_accesosip (fk_acceso , client_ip)
              VALUES ({$acc},'{$ip}')"; 
       
       $results = $this->_db->query($SQL);
       $results = $results->fetchAll();

       return $results;
    }
    
    public function getEliminar($acc,$id){
        $SQL = "DELETE from tbl_accesosip
                WHERE pk_accesoip = {$id} and fk_acceso = {$acc};";
                
       $results = $this->_db->query($SQL);
       $results = $results->fetchAll();

       return $results;        
    }


    public function getValidar($acc,$ip){
       $SQL ="SELECT pk_accesoip
              FROM tbl_accesosip
              WHERE fk_acceso = {$acc} and client_ip = '{$ip}'";
        
       $results = $this->_db->query($SQL);
       $results = $results->fetchAll();

       return $results;
    }

}


?>
