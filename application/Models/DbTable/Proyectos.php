<?php

class Models_DbTable_Proyectos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_proyectos';
    protected $_primary  = 'pk_proyecto';
    protected $_sequence = false;

       private $searchParams = array('p.pk_proyecto', 'p.nombre', 'p.descripcion', 'p.fk_institucion', 'i.nombre');
    
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
         $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

public function setSearch($searchData) {
        $this->searchData = $searchData;
    }         

    public function getProyectos() {
     
 $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);        
        
        $SQL = "SELECT p.pk_proyecto,
                       p.nombre, 
                       p.descripcion, 
                       p.fk_institucion ,
                       i.nombre AS institucion
		  FROM tbl_proyectos p
                  JOIN tbl_instituciones i ON i.pk_institucion = p.fk_institucion 
                 WHERE p.pk_proyecto > 0
                  {$whereSearch}
                  ORDER BY 2 ASC;;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }



    public function getCountByProyectos($pk_proyecto) {	
        
              $SQL = "SELECT COUNT(pk_proyecto)
		        FROM tbl_proyectos p
		        WHERE p.pk_proyecto = {$pk_proyecto}";
		
		return $this->_db->fetchOne($SQL);
	}
    

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function getRow($id) {
		if(!isset($id)) return;
		
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }
    
 public function copyRow($ids) {
     
		if(!is_string($ids))         return;

        $SQL = "INSERT INTO tbl_proyectos (nombre, descripcion, fk_institucion)
			    SELECT nombre,
                                   descripcion,
                                   fk_institucion
                FROM   tbl_proyectos
                WHERE pk_proyecto IN ({$ids})";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}   

public function getPK() {
	$SQL = "SELECT pk_proyecto
                  FROM tbl_proyectos 
                WHERE {$this->Where}";

	return $this->_db->fetchOne($SQL);
  }

   public function getpro($institucion, $proyecto) {
        $SQL = "SELECT DISTINCT p.pk_proyecto, p.nombre AS proyectos
                  FROM tbl_proyectos p
                  WHERE p.fk_institucion = {$institucion}";
           if(!empty($proyecto))
               $SQL .= "AND p.pk_proyecto   = {$proyecto} ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
  
        
}
