<?php

class Models_DbTable_Materiasaretirar extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_materiasaretirar';
    protected $_primary  = 'pk_materiaretirar';
    protected $_sequence = 'tbl_materiasaretirar_pk_materiaretirar_seq';

    

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
       // $this->logger = Zend_Registry::get('logger');
    }


    public function listMateriasRetiradas($solicitud,$periodo){
        $SQL = "select DISTINCT ma.materia, sec.valor, pk_materiaretirar, pk_recordacademico,
                substring(ag.codigopropietario from 3 for 2) as cod_esc,
                substring(ag.codigopropietario from 5 for 2) as cod_sem,
                substring(ag.codigopropietario from 7 for 2) as cod_ord
                from tbl_materiasaretirar mar
                JOIN tbl_recordsacademicos ra ON ra.pk_recordacademico = mar.fk_recordacademico
                JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                LEFT JOIN tbl_asignaciones aon ON aon.pk_asignacion = ra.fk_asignacion
                LEFT JOIN vw_secciones sec ON sec.pk_atributo = aon.fk_seccion
                WHERE  fk_usuariogruposolicitud = {$solicitud}
                ;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function countRetiradas($pk){
        
        $SQL = "SELECT COUNT(DISTINCT pk_materiaretirar)
                FROM tbl_materiasaretirar mar
                WHERE mar.fk_usuariogruposolicitud = {$pk};";

        return $this->_db->fetchOne($SQL);
    }

    public function displayInfo($materia_retirar){

         $SQL = "select DISTINCT ma.materia, sec.valor, pk_materiaretirar, pk_recordacademico, ag.unidadcredito
                from tbl_materiasaretirar mar
                JOIN tbl_recordsacademicos ra ON ra.pk_recordacademico = mar.fk_recordacademico
                JOIN tbl_asignaturas ag ON ag.pk_asignatura = ra.fk_asignatura
                JOIN vw_materias ma ON ma.pk_atributo = ag.fk_materia
                LEFT JOIN tbl_asignaciones aon ON aon.pk_asignacion = ra.fk_asignacion
                LEFT JOIN vw_secciones sec ON sec.pk_atributo = aon.fk_seccion
                WHERE  pk_materiaretirar = {$materia_retirar}
                ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
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

    public function deleteByRecord($record) {
         $SQL = "delete from tbl_materiasaretirar where fk_recordacademico = {$record};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }




}

?>
