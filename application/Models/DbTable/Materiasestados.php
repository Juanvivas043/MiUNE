<?php
class Models_DbTable_Materiasestados extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_materiasestados';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect($Ignore = null) {
        $Ignore = (isset($Ignore))? "WHERE valor NOT IN ({$Ignore})" : null;

        $SQL = "SELECT {$this->_primary} as id, valor
                FROM {$this->_name}
                {$Ignore}
		ORDER BY id ASC;";

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