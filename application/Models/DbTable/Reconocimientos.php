<?php
class Models_DbTable_Reconocimientos extends Zend_Db_Table {
    protected function _setupTableName() {
        $this->_schema   = 'produccion';
        $this->_name     = 'tbl_reconocimientos';
        $this->_primary  = 'pk_reconocimiento';
        $this->_sequence = false;
    }

    public function init() {
        $this->SwapBytes_Array         = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function getUniversidades($cedula) {
        if(empty($cedula)) return;
        
        $SQL = "SELECT u.pk_universidad, u.nombre
                FROM tbl_reconocimientos r
                JOIN vw_universidades u ON u.pk_universidad = r.fk_universidad
				JOIN tbl_inscripciones i ON i.pk_inscripcion = r.fk_inscripcion
				JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
                WHERE ug.fk_usuario = {$cedula};";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function addRow($inscripcion, $universidad) {
        if(empty($inscripcion)) return;
        if(empty($universidad)) return;
        
        $data = array(
            'fk_inscripcion' => $inscripcion,
            'fk_universidad' => $universidad
        );

        $rows_affected = $this->insert($data);

        return $rows_affected;
    }

    public function updateRow($id, $inscripcion, $universidad) {
        if(empty($inscripcion)) return;
        if(empty($universidad)) return;
        
        $data = array(
            'fk_inscripcion' => $inscripcion,
            'fk_universidad' => $universidad
        );

        $where         = $this->_db->quoteInto('pk_reconocimiento = ?', $id);
        $rows_affected = $this->update($data, $where);

        return $rows_affected;
    }
    
    /**
     * Permite eliminar un registro dependiendo de las condiciones que son
     * enviadas como parametros.
     *
     * @param int $id Clave primaria del registro.
     * @return int
     */
    public function deleteRow($id) {
        //$rowsAffected = $this->delete( . ' = ' . (int)$id);
        $where        = $this->_db->quoteInto($this->_primary . ' = ?', $id);
        $rowsAffected = $this->delete($where);

        return $rowsAffected;
    }

    public function getPK($inscripcion, $universidad) {
        if(empty($inscripcion)) return;
        if(empty($universidad)) return;

        $SQL = "SELECT pk_reconocimiento
                FROM tbl_reconocimientos
                WHERE fk_inscripcion = {$inscripcion}
                  AND fk_universidad = {$universidad};";

        return $this->_db->fetchOne($SQL);
    }


	public function deleteAll($cedula, $tipo) {
        $SQL = "DELETE FROM tbl_reconocimientos
                WHERE fk_inscripcion IN (SELECT pk_inscripcion FROM tbl_inscripciones i 
				JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = i.fk_usuariogrupo
				WHERE fk_usuario = {$cedula});";
//                  AND fk_tipo        = $tipo

        return $this->_db->fetchOne($SQL);
    }
}
