<?php
class Models_DbView_Turnos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_turnos';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, valor
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}