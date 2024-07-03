<?php

class Models_DbTable_Solicitudesempleadores extends Zend_Db_Table {
  protected $_schema   = 'produccion';
  protected $_name     = 'tbl_solicitudesempleadores';
  protected $_primary  = 'pk_solicitudempleador';
  protected $_sequence = false;

  private $searchParams = array('i.solicitudempleador', 'i.fk_usuario', 'i.fk_institucion', 'i.fk_estado');
  
  public function init() {
      $this->SwapBytes_Array = new SwapBytes_Array();
      $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
  }

  public function setSearch($searchData) {
      $this->searchData = $searchData;
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

  public function asociarEmpleador($pk_institucion,$cedula){
    $this->getAdapter()->beginTransaction();
    try {
      $SQL = "INSERT INTO tbl_solicitudesempleadores
              (fk_usuario,fk_institucion,fk_estado)
              VALUES 
              ({$cedula},{$pk_institucion},19971)";
      $this->_db->query($SQL);

      $this->getAdapter()->commit();
      return true;
    }
    catch(Exception $ex) {
      $this->getAdapter()->rollback();
      throw new Exception("Error de registro de Solicitud de Empleador.", 1);
      return false;
    }
  }

  public function getInstitucionEmpleador($pk_institucion,$cedula){
      $SQL = "SELECT CASE WHEN COUNT(pk_usuario) > 0 THEN true ELSE false END
              FROM tbl_usuarios tu
              JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
              JOIN tbl_solicitudesempleadores ts ON tg.fk_usuario = ts.fk_usuario
              WHERE tu.pk_usuario = {$cedula}
              AND tg.fk_grupo = 20111
              AND ts.fk_institucion = {$pk_institucion}";
      $results = $this->_db->query($SQL);
      $results = $results->fetchAll();

      return $results[0]['case'];
  }

}