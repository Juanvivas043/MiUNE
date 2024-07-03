<?php
class Models_DbTable_Usuariosdatos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_usuariosdatos';
    protected $_primary  = 'pk_usuariodato';
    protected $_sequence = true;

    public function init() {
    }


    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function getUserData($ci){

        $SQL = "SELECT *
                FROM tbl_usuariosdatos
                WHERE fk_usuario = $ci";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function insertar($data){
        if($data['promedio'] == ''){
            $data['promedio'] = 0;
        }
        //var_dump($data);
        $SQL = "INSERT INTO tbl_usuariosdatos(fk_usuario, promedio, fk_tipodeingreso, fk_tipocolegio,colegio)
                VALUES ({$data['fk_usuario']}, {$data['promedio']}, {$data['fk_tipodeingreso']}, {$data['fk_tipocolegio']},'{$data['colegio']}');";

        $results = $this->_db->query($SQL);

    }

    public function updateUser($data){

        if($data['promedio'] == ''){
            $data['promedio'] = 0;
        }
        //var_dump($data);
        $SQL = "UPDATE tbl_usuariosdatos
                SET promedio={$data['promedio']}, fk_tipodeingreso={$data['fk_tipodeingreso']},
                colegio='{$data['colegio']}', fk_tipocolegio={$data['fk_tipocolegio']}
                WHERE fk_usuario = {$data['fk_usuario']};
               ";

        $results = $this->_db->query($SQL);

    }

    public function getcolegio($ci){

        $SQL = "SELECT colegio
                FROM tbl_usuariosdatos ud
                WHERE fk_usuario = $ci;";

        return $this->_db->fetchOne($SQL);

    }

    public function gettipocolegio($ci){

        $SQL = "SELECT fk_tipocolegio
                FROM tbl_usuariosdatos ud
                WHERE fk_usuario = $ci;";

        return $this->_db->fetchOne($SQL);

    }

    public function getTipoIngreso($ci){

        $SQL = "SELECT fk_tipodeingreso
                FROM tbl_usuariosdatos ud
                WHERE fk_usuario = $ci;";

        return $this->_db->fetchOne($SQL);

    }

    public function getPromedio($ci){

        $SQL = "SELECT promedio
                FROM tbl_usuariosdatos ud
                WHERE fk_usuario = $ci;";

        return $this->_db->fetchOne($SQL);

    }

    public function getPkFromUser($ci){

        $SQL = "SELECT pk_usuariodato
                FROM tbl_usuariosdatos
                WHERE fk_usuario = $ci;";

        return $this->_db->fetchOne($SQL);

    }




}
?>
