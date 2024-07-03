<?php
class Models_DbTable_EstructurasEscuelas extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_estructurasescuelas';
    protected $_primary  = 'pk_estructuraescuela';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect($Sede) {
        $SQL = "SELECT DISTINCT pk_atributo, escuela
                FROM vw_escuelas
                LEFT JOIN tbl_estructurasescuelas ON pk_atributo = fk_atributo
                WHERE fk_estructura = {$Sede}
                ORDER BY escuela;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    public function getName($esc) {
        $SQL = "SELECT escuela
                FROM vw_escuelas
                WHERE pk_atributo = $esc";


        $results = $this->_db->fetchOne($SQL);

        return $results;
        
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