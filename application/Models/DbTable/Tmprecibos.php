<?php

class Models_DbTable_Tmprecibos extends Zend_Db_Table {

 
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_tmprecibosdepagos';
    protected $_primary  = 'pk_tmprecibopago';
    protected $_sequence = 'tbl_tmprecibosdepagos_pk_tmprecibopago_seq';

//    public function getGrupos($ci){
//
//        $SQL =  "SELECT g.grupo, g.pk_atributo
//                FROM tbl_usuariosgrupos ug
//                JOIN vw_grupos g ON g.pk_atributo = ug.fk_grupo
//                WHERE ug.fk_usuario = $ci
//                AND g.pk_atributo IN (855,854,1745)";
//        $results = $this->_db->query($SQL);
//        $results = $results->fetchAll();
//
//        return $results;
//    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function getall($ci){

        $SQL =  "SELECT pk_tmprecibopago
                 FROM tbl_tmprecibosdepagos
                 WHERE cedula = {$ci}";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }




}
