<?php

class Models_DbTable_Tmpcertificacionpasantia extends Zend_Db_Table {

 

    

    public function insertar_cert($pk,$responsable,$departamento,$empresa) { 
        
        $SQL = "INSERT INTO produccion.tmp_certificacionpasantia(
                        pk_certificacion,responsable, departamento, empresa)
                VALUES ({$pk}, '{$responsable}', '{$departamento}', '{$empresa}')";
                
        $results = $this->_db->query($SQL);

        return $results->fetchAll();
    }

    public function getlastpk(){

        $SQL =  "SELECT DISTINCT pk_certificacion AS codigo
                 FROM produccion.tmp_certificacionpasantia
                 ORDER BY 1 DESC LIMIT 1";
        
        $results = $this->_db->query($SQL);

        return $results->fetchAll();

    }
    

    public function delete_cert($id) { 
		if(!is_numeric($id)) return null;
                
        $SQL = "DELETE FROM  produccion.tmp_certificacionpasantia WHERE pk_certificacion = {$id}";
                
        $results = $this->_db->query($SQL);

        return $results->fetchAll();
    }




}
