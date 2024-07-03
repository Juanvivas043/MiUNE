<?php
class Models_DbView_Docentes extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_docentes';
    protected $_primary  = 'pk_usuariogrupo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, docente
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}