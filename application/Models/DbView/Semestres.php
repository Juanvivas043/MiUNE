<?php
class Models_DbView_Semestres extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_semestres';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, id
                FROM {$this->_name}
				WHERE id > 0";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function getName($sem) {
        $SQL = "SELECT valor
                FROM vw_semestres
                WHERE pk_atributo =$sem";

        $results = $this->_db->fetchOne($SQL);

        return $results;
        
    }
}