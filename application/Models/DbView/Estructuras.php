<?php
class Models_DbView_Estructuras extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_estructuras';
    protected $_sequence = false;

    public function getSedes() {
        $SQL = "SELECT DISTINCT pk_sede, sede
                FROM {$this->_name}
				ORDER BY 2";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

	public function getEdificios($Sede) {
        $SQL = "SELECT DISTINCT pk_edificio, edificio
                FROM {$this->_name}
				WHERE pk_sede = {$Sede}
				ORDER BY 2";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}

	public function getAulas($Edificio) {
        $SQL = "SELECT DISTINCT pk_aula, aula
                FROM {$this->_name}
				WHERE pk_edificio = {$Edificio}
				ORDER BY 2";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}

	public function getAulasOcupadas($Periodo, $Hora, $Dia, $Edificio) {
		$SQL = "SELECT pk_estructura AS pk_aula
		             , nombre
		             || (SELECT CASE WHEN (SELECT COUNT(pk_estructura)
				         FROM   tbl_asignaciones ag
						 INNER JOIN tbl_estructuras e ON e.pk_estructura = ag.fk_estructura
						 WHERE ag.fk_periodo    = {$Periodo}
						   AND ag.fk_horario    = {$Hora}
						   AND ag.fk_dia        = {$Dia}
						   AND e.fk_estructura  = {$Edificio}
						   AND e.pk_estructura  = es.pk_estructura) > 0
		                 THEN ' (Ocupado)'
		                 ELSE ''
	                     END) AS aula
				FROM   tbl_estructuras es
				WHERE (es.fk_atributo  = 887 OR es.fk_atributo = 889)
				  AND es.fk_estructura = {$Edificio}
				ORDER BY nombre ASC;";

		$results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}
}