<?php
class Models_DbTable_Reiniciarpass extends Zend_Db_Table {

    public function init() {  
    }
    
    public function getCantidadgrupos($ci){
        
        $SQL =  "SELECT COUNT(gr.grupo)
                 FROM tbl_usuarios u 
                 JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                 JOIN vw_grupos gr ON gr.pk_atributo = ug.fk_grupo
                 WHERE pk_usuario =$ci;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }


}

?>
