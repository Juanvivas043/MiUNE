<?php
class Models_DbView_Grupos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_grupos';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function getGrupos() {
        $SQL = "SELECT pk_atributo, grupo
                  FROM vw_grupos;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    
        public function getGrupoespecifico($grupo) {
        $SQL = "SELECT pk_atributo, grupo
                  FROM vw_grupos
                  WHERE pk_atributo = {$grupo};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}