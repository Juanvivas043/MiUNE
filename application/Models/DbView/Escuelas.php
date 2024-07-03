<?php
class Models_DbView_Escuelas extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_escuelas';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;


    public function getEscuelaName($id){
        $SQL = "SELECT escuela
                FROM {$this->_name}
                WHERE pk_atributo = {$id};";

        return $this->_db->fetchOne($SQL);


    }

    public function getEscuelas(){

        $SQL = "SELECT pk_atributo, escuela
                FROM {$this->_name}
                WHERE escuela <> 'N/A';";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();

    }
    public function getEscuelasBySede($sede){
        
        $SQL = "SELECT DISTINCT es.pk_atributo, escuela
                FROM vw_sedes s
                JOIN tbl_inscripciones i on i.fk_estructura = s.pk_estructura
                JOIN vw_escuelas es on es.pk_atributo = i.fk_atributo
                WHERE s.pk_estructura = {$sede}
                ORDER BY 1;";

        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    public function getEscuelaInscrita($ci,$periodos) {
        $SQL = " SELECT ve.pk_atributo
                    FROM tbl_usuarios       AS tu
                    JOIN tbl_usuariosgrupos AS tug ON tu.pk_usuario = tug.fk_usuario
                    JOIN tbl_inscripciones  AS ti  ON tug.pk_usuariogrupo = ti.fk_usuariogrupo
                    JOIN tbl_periodos       AS tp  ON ti.fk_periodo = tp.pk_periodo
                    JOIN vw_escuelas        AS ve  ON ti.fk_atributo = ve.pk_atributo
                    WHERE tu.pk_usuario = {$ci} AND tp.pk_periodo = {$periodos};";
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();

    }
}