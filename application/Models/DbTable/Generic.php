<?php
class Models_DbTable_Generic extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_periodos';
    protected $_primary  = 'pk_periodo';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect($SQL) {

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }


}
