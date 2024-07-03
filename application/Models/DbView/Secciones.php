<?php
class Models_DbView_Secciones extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_secciones';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, valor
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

	public function getPadres() {
		$SQL = "SELECT pk_atributo, valor
				FROM vw_secciones
				WHERE valor ILIKE '_'";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}
	
	public function getHijos($Padre) {
		if(!is_numeric($Padre)) return;
		
		$SQL = "SELECT pk_atributo, valor
				FROM vw_secciones
				WHERE valor ILIKE (SELECT valor FROM vw_secciones WHERE pk_atributo = {$Padre})::text || '%'";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}


        public function getSeccion($pk){

            $SQL = "SELECT valor
                    FROM vw_secciones
                    WHERE pk_atributo = {$pk};";

            $results = $this->_db->query($SQL);

            return (array)$results->fetchAll();

        }
}