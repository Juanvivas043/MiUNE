<?php

class Models_DbTable_Instituciones extends Zend_Db_Table {
  protected $_schema   = 'produccion';
  protected $_name     = 'tbl_instituciones';
  protected $_primary  = 'pk_institucion';
  protected $_sequence = false;

  private $searchParams = array('i.pk_institucion', 'i.nombre', 'i.direccion', 'i.telefono', 'i.telefono2', 'i.fk_tipopasantia');
  
  public function init() {
      $this->SwapBytes_Array = new SwapBytes_Array();
      $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
  }

  public function setSearch($searchData) {
      $this->searchData = $searchData;
  }         

  public function getListaInstituciones() {
     
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);        
        
    $SQL = "SELECT i.pk_institucion, 
                   i.nombre, 
                   i.direccion, 
                   i.telefono, 
                   i.telefono2, 
                   i.fk_tipopasantia, 
                   i.rif, 
                   i.razonsocial
            FROM tbl_instituciones i
             WHERE i.fk_tipopasantia = 8222
              {$whereSearch}
              ORDER BY 2 ASC;";
    $results = $this->_db->query($SQL);
    return (array)$results->fetchAll();
  }

  public function getListaEmpresas() {
     
    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);        
        
    $SQL = "SELECT i.pk_institucion, 
                   i.nombre, 
                   i.direccion, 
                   i.telefono, 
                   i.telefono2, 
                   i.fk_tipopasantia, 
                   i.rif, 
                   i.razonsocial
            FROM tbl_instituciones i
             WHERE i.fk_tipopasantia = 8223
              {$whereSearch}
              ORDER BY 2 ASC;";

    $results = $this->_db->query($SQL);
    return (array)$results->fetchAll();
  }

  public function getCountByInstituciones($pk_institucion) {
        
    $SQL = "SELECT COUNT(pk_institucion)
		        FROM tbl_instituciones i
		        WHERE i.pk_institucion = {$pk_institucion}";
		
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

      $SQL = "INSERT INTO tbl_instituciones (nombre, direccion, telefono, telefono2, fk_tipopasantia)
			    SELECT nombre,
                                   direccion,
                                   telefono,
                                   telefono2,
                                   fk_tipopasantia
                FROM   tbl_instituciones
                WHERE pk_institucion IN ({$ids})";

      $results = $this->_db->query($SQL);

      return (array)$results->fetchAll();
	}   

  public function getPK() {
	  $SQL = "SELECT pk_institucion
                  FROM tbl_instituciones 
                WHERE {$this->Where}";

	  return $this->_db->fetchOne($SQL);
  }

  public function get() {
    $SQL = "SELECT pk_institucion, 
                   nombre 
                  FROM tbl_instituciones";

    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }
  
  public function getinstitucionesproyectos() {
    $SQL = "SELECT pk_institucion, 
                   nombre
                  FROM tbl_instituciones
                  WHERE fk_tipopasantia = 8222
                  ORDER BY 2 ASC";

    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }
    
  public function getempresas() {
    $SQL = "SELECT pk_institucion, 
                   nombre
                  FROM tbl_instituciones
                  WHERE fk_tipopasantia = 8223
                  ORDER BY 2 ASC";

    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  //Similar al getRow Pero segun RIF
  public function getRif($rif) {
    $SQL = "SELECT pk_institucion,
                   nombre, 
                   direccion, 
                   telefono,
                   telefono2,
                   fk_tipopasantia, 
                   rif, 
                   razonsocial
                FROM tbl_instituciones
                WHERE rif = '{$rif}'";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function getEmpresaByRif($rif){
    $SQL = "SELECT pk_institucion,
                   nombre, 
                   direccion, 
                   telefono,
                   telefono2,
                   fk_tipopasantia, 
                   rif, 
                   razonsocial
            FROM tbl_instituciones
            WHERE fk_tipopasantia = 8223
            AND rif = '{$rif}'";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function getInstitucionByRif($rif){
    $SQL = "SELECT pk_institucion,
                   nombre, 
                   direccion, 
                   telefono,
                   telefono2,
                   fk_tipopasantia, 
                   rif, 
                   razonsocial
            FROM tbl_instituciones
            WHERE fk_tipopasantia = 8222
            AND rif = '{$rif}'";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function addEmpresa($empresa){
    $this->getAdapter()->beginTransaction();
    try {
      $SQL = "INSERT INTO tbl_instituciones 
              (fk_tipopasantia,rif,razonsocial,nombre)
              VALUES
              (8223,'{$empresa['rif']}','{$empresa['razonsocial']}','{$empresa['nombre']}')";
      $this->_db->query($SQL);

      $this->getAdapter()->commit();
      return true;
    }
    catch(Exception $ex) {
      $this->getAdapter()->rollback();
      throw new Exception("Error de registro de Empresa.", 1);
      return false;
    }

  }

  public function getAllEmpresas(){
    $SQL = " SELECT ti.pk_institucion, 
                    ti.nombre, 
                    ti.direccion, 
                    ti.telefono, 
                    ti.telefono2, 
                    ti.fk_tipopasantia, 
                    ti.rif, 
                    ti.razonsocial
              FROM tbl_instituciones ti
              JOIN tbl_solicitudesempleadores ts ON ti.pk_institucion = ts.fk_institucion
              GROUP BY ti.pk_institucion, ti.nombre, ti.direccion, ti.telefono, ti.telefono2, ti.fk_tipopasantia, ti.rif, ti.razonsocial
              ORDER BY ti.nombre ASC";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function getEmpresaByEmpleador($cedula){
    $SQL = "SELECT ti.pk_institucion, 
                   ti.nombre, 
                   ti.direccion, 
                   ti.telefono, 
                   ti.telefono2, 
                   ti.fk_tipopasantia, 
                   ti.rif, 
                   ti.razonsocial
            FROM tbl_usuarios tu
            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
            JOIN tbl_solicitudesempleadores ts ON tu.pk_usuario = ts.fk_usuario
            JOIN tbl_instituciones ti ON ts.fk_institucion = ti.pk_institucion
            WHERE tu.pk_usuario = {$cedula}
            AND ts.fk_estado = 19969
            GROUP BY ti.pk_institucion, ti.nombre";
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function getEmpresaNameByEmpleador($cedula){
    $SQL = "SELECT ti.pk_institucion,
                   ti.nombre
            FROM tbl_usuarios tu
            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
            JOIN tbl_solicitudesempleadores ts ON tu.pk_usuario = ts.fk_usuario
            JOIN tbl_instituciones ti ON ts.fk_institucion = ti.pk_institucion
            WHERE tu.pk_usuario = {$cedula}
            AND ts.fk_estado = 19969
            GROUP BY ti.pk_institucion, ti.nombre";

    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
  }

  public function uploadPicture($file,$id){
    //Data File
    $tmp   = $file['tmp_name'];
    $name  = $file['name'];
    $type  = $file['type'];
    $size  = $file['size'];
    $error = $file['error'];
    //Read File
    $open = fopen($tmp,'r+b');
    $data = fread($open,filesize($tmp));
    $data = pg_escape_bytea($data);
    fclose($open);
    //Query
    $config = $this->_db->getConfig();
    $conn   = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
    $SQL  = "UPDATE tbl_instituciones 
              SET foto = '{$data}'
              WHERE pk_institucion = {$id}";
    $query  = pg_query($conn,$SQL);
    pg_close($conn);
  }

  public function getPicture($institucion){
    $config = $this->_db->getConfig();
    $conn   = pg_connect("user={$config['username']} password={$config['password']} dbname={$config['dbname']} host={$config['host']}");
    $SQL    = "SELECT foto 
               FROM tbl_instituciones 
               WHERE pk_institucion = {$institucion}";
    $query  = pg_query($conn,$SQL);
    $row    = pg_fetch_row($query);
    $image  = pg_unescape_bytea($row[0]);
    pg_close($conn);
    return $image;
  }

}