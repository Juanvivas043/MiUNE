<?php
class Models_DbTable_Materiasbiblioteca extends Zend_Db_Table {
        // materias omicron = 78 , local 73
    private $searchParams = array('valor');
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }
    
    
    public function getSQLCount() {
        
        $SQL = "SELECT COUNT(pk_atributo)
		FROM tbl_atributos
                WHERE fk_atributotipo = 78 {$whereSearch};";

        return $this->_db->fetchOne($SQL);
    }
    
    public function get_data ($itemPerPage, $pageNumber) {
         $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        $SQL = "SELECT pk_atributo , valor as materias
                FROM tbl_atributos
                WHERE fk_atributotipo = 78 {$whereSearch}
                ORDER BY pk_atributo DESC LIMIT $itemPerPage OFFSET {$pageNumber};";
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
    }
    
    public function get_Materia ($materia) {
      
        $SQL = "SELECT pk_atributo , valor as materias
                FROM tbl_atributos
                WHERE fk_atributotipo = 78 AND  valor = '$materia';";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
    }
   
    public function get_pkAtributo(){
       
       $SQL = "SELECT (pk_atributo +1) as pk_atributo
               FROM tbl_atributos 
               ORDER by 1 desc limit 1;";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
       
   }
   
    public function insert($pk,$materia){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, 78, '{$materia}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }
    
    public function update($pk,$materia){
        $SQL = "UPDATE tbl_atributos
                SET valor='{$materia}'
                WHERE pk_atributo = {$pk};";
               
                   
       $this->_db->query($SQL);
       
   }
   
    public function get_dataRow($id){
        $SQL = "SELECT pk_atributo , valor as materias
                FROM tbl_atributos
                WHERE pk_atributo = {$id};";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
        
    }
    
    public function get_librosAsociados($id){
         
        $SQL = "SELECT count(pk_materialibro) 
                FROM tbl_materiaslibros
                WHERE fk_materiabiblioteca = {$id}
                AND fk_libro IS NOT NULL;";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
        
    }
    
    public function deleteRow($id){
        
       $SQL = "DELETE FROM tbl_atributos
               WHERE pk_atributo = {$id};";
               
                   
       $this->_db->query($SQL);
        
    }
}

