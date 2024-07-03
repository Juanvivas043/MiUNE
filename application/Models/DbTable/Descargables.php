<?php
class Models_DbTable_Descargables extends Zend_Db_Table {
    
        
     public function nuevoInscrito($ci){

         
         $SQL = "   SELECT ug.fk_usuario
                    FROM tbl_usuariosgrupos ug
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE ins.fk_periodo >= (SELECT pk_periodo
                                            FROM tbl_periodos
                                            ORDER BY 1 DESC
                                            LIMIT 1)
                    AND (SELECT COUNT(ug1.fk_usuario)
                                            FROM tbl_usuariosgrupos ug1
                                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug1.pk_usuariogrupo
                                            WHERE ug1.fk_usuario = ug.fk_usuario
                                            GROUP BY ug1.fk_usuario) = 1
                    AND ug.fk_usuario = $ci
                    GROUP BY 1";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
        
    }    
    
}
?>