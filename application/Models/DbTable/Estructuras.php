<?php
class Models_DbTable_Estructuras extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_estructuras';
    protected $_primary  = 'pk_estructura';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect() {
        $SQL = "SELECT pk_estructura, nombre FROM vw_sedes;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }
}