<?php
class Models_DbView_Tiposinstituciones extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_tiposinstituciones';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function getTipos($pasantia) {
        $SQL = "SELECT pk_atributo, institucion
                FROM vw_tiposinstituciones
                WHERE pk_atributo = {$pasantia}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}