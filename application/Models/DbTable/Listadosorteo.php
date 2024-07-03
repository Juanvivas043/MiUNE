<?php
class Models_DbTable_Listadosorteo extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_listadossorteos';
    protected $_primary  = 'pk_listadosorteo';
    protected $_sequence = 'tbl_listadossorteos_pk_listadosorteo_seq';

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function getBanned(){

        $SQL= "select u.nombre, u.apellido, u.pk_usuario as cedula,pk_listadosorteo
                from tbl_listadossorteos ls
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ls.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario;";
              // echo $SQL;
         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getBannedRow($pk){

        $SQL= "select u.nombre, u.apellido, u.pk_usuario as cedula,pk_listadosorteo
                from tbl_listadossorteos ls
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ls.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                and ls.pk_listadosorteo = {$pk};";
              // echo $SQL;
         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function addRow($data){
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
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

    public function getAdministrativos(){

        


    }

}

?>

