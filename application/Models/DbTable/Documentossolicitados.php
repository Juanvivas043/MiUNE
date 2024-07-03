<?php

class Models_DbTable_Documentossolicitados extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_documentossolicitados';
    protected $_primary  = 'pk_documentosolicitado';
    protected $_sequence = 'tbl_documentossolicitados_pk_documentosolicitado_seq';



    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table(); 
       // $this->logger = Zend_Registry::get('logger');
    }

    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function get($pk){

        $SQL ="SELECT * FROM tbl_documentossolicitados WHERE pk_documentosolicitado = $pk;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function updateRow($id, $data) {

        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function getDocsSolicitud($pk){

        $SQL = "SELECT ds.pk_documentosolicitado, at.valor as estado, at2.valor as documento
                FROM tbl_documentossolicitados ds
                JOIN tbl_atributos at ON at.pk_atributo = ds.fk_estado
                JOIN tbl_atributos at2 ON at2.pk_atributo = ds.fk_documento
                WHERE fk_usuariogruposolicitud = {$pk};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    

    }

    public function getInfoSolicitante($pk){

        $SQL = "select DISTINCT u.pk_usuario as ced,
                       esc.pk_atributo,
                       pe.codigopropietario
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_documentossolicitados ds ON ds.fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                JOIN tbl_atributos at ON at.pk_atributo = ds.fk_documento
                JOIN tbl_pensums    pe  ON  pe.pk_pensum = i.fk_pensum
                  WHERE ds.pk_documentosolicitado = {$pk}
                  order by 3 desc limit 1;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getInfoDoc($pk){

        $SQL = "SELECT at.valor as estado,
                       at2.valor as documento
                FROM tbl_documentossolicitados ds
                JOIN tbl_atributos at ON at.pk_atributo = ds.fk_estado
                JOIN tbl_atributos at2 ON at2.pk_atributo = ds.fk_documento
                WHERE pk_documentosolicitado = {$pk};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;



    }

    public function getEstatusSolicitudes($ci){

        $SQL = "SELECT ugs.pk_usuariogruposolicitud, ds.*, doc.valor as docum, estado.valor as estado
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_documentossolicitados ds ON ds.fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud
                JOIN tbl_atributos doc ON doc.pk_atributo = ds.fk_documento
                JOIN tbl_atributos estado ON estado.pk_atributo = ds.fk_estado
                where fk_usuario = {$ci}
                  and fk_tipo = 8266
                  and ds.fk_estado <> 8271;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;





    }
    




}

?>

