<?php
class Models_DbTable_Autoreslibros extends Zend_Db_Table {
        // 76 omicron , 72 local
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
                WHERE fk_atributotipo = 76 {$whereSearch};";
                

        return $this->_db->fetchOne($SQL);
    }
    
    public function get_data ($itemPerPage, $pageNumber) {
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);
        $SQL = "SELECT pk_atributo , valor as autor
                FROM tbl_atributos
                WHERE fk_atributotipo = 76 {$whereSearch}
                ORDER BY pk_atributo LIMIT {$itemPerPage} OFFSET {$pageNumber};";   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
    }
    
    public function get_Autor ($autor) {
      
        $SQL = "SELECT pk_atributo , valor as autor
                FROM tbl_atributos
                WHERE fk_atributotipo = 76 AND  valor = '$autor';";
               
                   
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
   
    public function insert($pk,$autor){
        $SQL = "INSERT INTO tbl_atributos(
                pk_atributo, fk_atributotipo, valor, id, fk_atributo)
                VALUES ({$pk}, 76, '{$autor}', NULL, NULL);";
               
                   
       $this->_db->query($SQL);
       
   }
    
    public function update($pk,$autor){
        $SQL = "UPDATE tbl_atributos
                SET valor='{$autor}'
                WHERE pk_atributo = {$pk};";
               
                   
       $this->_db->query($SQL);
       
   }
   
    public function get_dataRow($id){
        $SQL = "SELECT pk_atributo , valor as autor
                FROM tbl_atributos
                WHERE pk_atributo = {$id};";
               
                   
        $results = $this->_db->query($SQL);
        
        return (array) $results->fetchAll();
        
    }
    
    public function get_librosAsociados($id){
         
        $SQL = "SELECT COUNT (pk_autorlibro) 
                 FROM tbl_autoreslibros
                 WHERE fk_autor = {$id} 
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

