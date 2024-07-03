<?php
class Models_DbTable_Agregarmaterias extends Zend_Db_Table {
        
    
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    public function getSQLCount($libro) {
        
        $SQL = "SELECT count (pk_materiaslibro)
                FROM tbl_materiaslibros
                WHERE fk_libro = {$libro}";

        return $this->_db->fetchOne($SQL);
    }
    
    public function getMaterias($libro){
        
        $SQL = "SELECT pk_materialibro , fk_materiabiblioteca , valor as materia 
                FROM tbl_materiaslibros ml
                JOIN tbl_atributos a1 ON ml.fk_materiabiblioteca = a1.pk_atributo
                WHERE fk_libro = {$libro}
                ORDER BY 1;";
         
               
       $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();        
        
    }
    
    public function getRow($id){
         
        $SQL = "select pk_materialibro , fk_libro , valor as materia , ml.fk_materiabiblioteca 
                from tbl_materiaslibros ml
                JOIN tbl_atributos a1 ON a1.pk_atributo = ml.fk_materiabiblioteca
                where pk_materialibro = {$id};";
               
       $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();        
    }

    public function InsertMaterias($fk_libro , $fk_materiabiblioteca){

         $SQL ="INSERT INTO tbl_materiaslibros(
                fk_libro, fk_materiabiblioteca)
                VALUES ({$fk_libro},{$fk_materiabiblioteca});";        
               $this->_db->query($SQL); 
    }
   
    public function deleteRow($id){
        //eliminar la materia
        $SQL = "DELETE FROM tbl_materiaslibros
                WHERE pk_materialibro = {$id};";
        $this->_db->query($SQL);
        
    }

    public function update ($pk,$fk_materiabiblioteca){
         $SQL ="UPDATE tbl_materiaslibros
                SET fk_materiabiblioteca = {$fk_materiabiblioteca}
                WHERE pk_materialibro = {$pk};";
         $this->_db->query($SQL); 
    }

    public function getInfo($pk){
        
        $SQL ="SELECT cota, titulo,a1.valor as editorial
        FROM tbl_libros l 
        JOIN tbl_atributos a1 ON a1.pk_atributo = l.fk_editorial
        WHERE pk_libro = {$pk};";
        $results = $this->_db->query($SQL);
       return (array) $results->fetchAll();  
    }
    
}

?>