<?php
class Models_DbTable_Agregarautores extends Zend_Db_Table {
        
    
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    public function getSQLCount($libro) {
        
        $SQL = "SELECT count (pk_autorlibro)
                FROM tbl_autoreslibros
                WHERE fk_libro = $libro";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getAutor($libro){
        
        $SQL = "SELECT *,CASE WHEN principal = 't' then 'principal'  
	      WHEN principal = 'f' then 'otro' end as tipo
	      from(  	
                select  ati.pk_autorlibro,pk_atributo as fk_autor , valor as autor , principal
                from tbl_autoreslibros ati
                join tbl_atributos a on a.pk_atributo = fk_autor
                where fk_libro = {$libro} 
                )as sqt;";
         
               
       $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();        
        
    }
    
    public function getRow($id){
         
        $SQL = "select pk_autorlibro, fk_autor , valor as autor , principal
                from tbl_autoreslibros ati
                join tbl_atributos a on a.pk_atributo = fk_autor
                where pk_autorlibro = {$id};";
               
       $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();        
    }

    public function InsertAutores($fk_libro,$fk_autor,$principal){
          if($principal=='1'){
            $principal = 'TRUE';
        }else{
             $principal = 'FALSE';
        }
        
        $SQL ="INSERT INTO tbl_autoreslibros(
              fk_autor, fk_libro, principal)
              VALUES ({$fk_autor},{$fk_libro},{$principal});";
         $this->_db->query($SQL); 
    }
   
    public function deleteRow($id){
        //eliminar el autor
        $SQL = "DELETE FROM tbl_autoreslibros
                WHERE pk_autorlibro = {$id};";
        $this->_db->query($SQL);
        
    }
    
    public function get_info($pk){ 
        $SQL ="SELECT cota, titulo,a1.valor as editorial
        FROM tbl_libros l 
        JOIN tbl_atributos a1 ON a1.pk_atributo = l.fk_editorial
        WHERE pk_libro = {$pk};";
        $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();  
    }        
  
    public function update ($pk,$autor,$principal){
         
         if($principal=='1'){
            $principal = 'TRUE';
        }else{
             $principal = 'FALSE';
        }
         
         $SQL ="UPDATE tbl_autoreslibros
               SET fk_autor={$autor}, principal={$principal}
               WHERE pk_autorlibro = {$pk};";
         $this->_db->query($SQL); 
    }
}

?>