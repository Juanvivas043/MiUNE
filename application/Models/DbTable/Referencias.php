<?php
class Models_DbTable_Referencias extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_referencias';
    protected $_primary  = 'pk_referencia';
    protected $_sequence = false;

    public function init() {
    }

	public function getCountByAsignacion($fk_asignacion) {
		$SQL = "SELECT COUNT(pk_referencia)
		        FROM tbl_referencias
		        WHERE fk_asignacion = {$fk_asignacion}";
		
		return $this->_db->fetchOne($SQL);
	}
}
