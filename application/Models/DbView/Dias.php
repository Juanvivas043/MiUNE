<?php
class Models_DbView_Dias extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_dias';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, dia
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}