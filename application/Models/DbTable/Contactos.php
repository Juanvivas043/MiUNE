<?php

class Models_DbTable_Contactos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_contactos';
    protected $_primary  = 'pk_contacto';
    protected $_sequence = 'tbl_contactos_pk_contacto_seq';

       private $searchParams = array('c.pk_contacto','c.fk_institucion', 'c.fk_proyecto','c.fk_usuariogrupo','u.apellido', 'u.nombre');
    
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
         $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

public function setSearch($searchData) {
        $this->searchData = $searchData;
    }         

    public function getContactos() {
     
$whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);          
        
        $SQL = "SELECT c.pk_contacto, 
                       c.fk_institucion, 
                       c.fk_proyecto, 
                       c.fk_usuariogrupo,
                       i.nombre AS institucion,
                       p.nombre AS proyecto,
                       u.apellido || ', ' || u.nombre as nombre_tutor
                 FROM tbl_contactos c
                 JOIN tbl_instituciones   i ON i.pk_institucion   = c.fk_institucion
                 JOIN tbl_proyectos       p ON p.pk_proyecto      = c.fk_proyecto
                 JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                 JOIN tbl_usuarios        u ON u.pk_usuario       = ug.fk_usuario
                 WHERE c.pk_contacto > 0
                   AND c.fk_proyecto > 0
                  {$whereSearch}
                  ORDER BY 1 DESC;;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getTutoresEmpresariales() {
     
 $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);        
        
        $SQL = "SELECT c.pk_contacto, 
                       c.fk_institucion, 
                       c.fk_usuariogrupo,
                       ug.fk_usuario as cedula,
                       i.nombre AS institucion,
                       u.nombre || ' ' || u.apellido as nombre_tutor
                 FROM tbl_contactos c
                 JOIN tbl_instituciones   i ON  i.pk_institucion  = c.fk_institucion
                 JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                 JOIN tbl_usuarios        u ON  u.pk_usuario      = ug.fk_usuario
                 WHERE c.pk_contacto > 0
                   AND i.fk_tipopasantia = 8223
                  {$whereSearch};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getCountByContactos($pk_contacto) {
	        
              $SQL = "SELECT COUNT(pk_contacto)
		        FROM tbl_contactos c
		        WHERE c.pk_contacto = {$pk_contacto}";
		
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

        $SQL = "INSERT INTO tbl_contactos (fk_institucion, fk_proyecto, nombre_contacto, telefono_contacto, telefono2_contacto, correo_contacto)
			    SELECT fk_institucion,
                                   fk_proyecto,
                                   nombre_contacto
                                   telefono_contacto,
                                   telefono2_contacto,
                                   correo_contacto
                FROM   tbl_contactos
                WHERE pk_contacto IN ({$ids})";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}   

public function getPK() {
	$SQL = "SELECT pk_contacto
                  FROM tbl_contactos 
                WHERE {$this->Where}";

	return $this->_db->fetchOne($SQL);
  }

   public function get($proyecto) {
        $SQL = "SELECT c.pk_contacto, 
                       u.apellido || ', ' || u.nombre AS tutor
                  FROM tbl_contactos c
                  JOIN tbl_proyectos p ON p.pk_proyecto = c.fk_proyecto
                  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                  JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                  JOIN tbl_asignacionesproyectos ap ON  ap.fk_proyecto = p.pk_proyecto
                  WHERE ap.pk_asignacionproyecto = {$proyecto}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
  
   public function gettutoresempresarial($empresa, $Ignore = null) {
       $Ignore = (isset($Ignore))? "AND valor NOT IN ({$Ignore})" : null;
        $SQL = "SELECT c.pk_contacto, 
                       u.nombre || ' ' || u.apellido AS tutor
                  FROM tbl_contactos c
                  JOIN tbl_instituciones   i ON i.pk_institucion   = c.fk_institucion
                  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                  JOIN tbl_usuarios        u ON u.pk_usuario       = ug.fk_usuario
                 WHERE i.fk_tipopasantia = 8223
                   AND c.fk_institucion = {$empresa} {$Ignore}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    } 
    
      public function gettutorconasignaciones($fk_usuario, $where = null) {
        if(empty($fk_usuario)) return;

        $SQL = "SELECT COUNT({$this->_primary}) AS count
                FROM {$this->_name} c
                JOIN tbl_instituciones   i ON i.pk_institucion   = c.fk_institucion
                JOIN tbl_proyectos       p ON p.pk_proyecto      = c.fk_proyecto
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                JOIN tbl_usuarios        u ON u.pk_usuario       = ug.fk_usuario
               WHERE c.fk_proyecto > 0 
                 AND fk_usuario = {$fk_usuario} {$where};";

//        $results = $this->_db->query($SQL);
//        $results = $results->fetchAll();

        return $this->_db->fetchOne($SQL);
    }
        
     public function gettutorempresaconasignaciones($fk_usuario, $where = null) {
        if(empty($fk_usuario)) return;

        $SQL = "SELECT COUNT({$this->_primary}) AS count
                FROM {$this->_name} c
                JOIN tbl_instituciones   i ON  i.pk_institucion  = c.fk_institucion
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = c.fk_usuariogrupo
                JOIN tbl_usuarios        u ON  u.pk_usuario      = ug.fk_usuario
               WHERE c.pk_contacto > 0
                 AND i.fk_tipopasantia = 8223
                 AND fk_usuario = {$fk_usuario} {$where};";


        return $this->_db->fetchOne($SQL);
    }
    
}
