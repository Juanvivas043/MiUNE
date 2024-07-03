<?php
class Models_DbView_Sedes extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_sedes';
    protected $_primary  = 'pk_estructura';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, nombre
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getSedeName($id){
        $SQL = "SELECT nombre
                FROM {$this->_name}
                WHERE pk_estructura = {$id};";

        return $this->_db->fetchOne($SQL);


    }

    public function getSedes(){

        $SQL = "SELECT pk_estructura, nombre
                FROM vw_sedes";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

    public function getSelectSede($ci,$periodos){
        $SQL = "    SELECT ti.fk_estructura
                    FROM tbl_usuarios       AS tu
                    JOIN tbl_usuariosgrupos AS tug ON tu.pk_usuario = tug.fk_usuario
                    JOIN tbl_inscripciones  AS ti  ON tug.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_periodos       AS tp  ON ti.fk_periodo = tp.pk_periodo
                    WHERE tu.pk_usuario = $ci AND tp.pk_periodo = $periodos";
        //var_dump($SQL);die;
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

}