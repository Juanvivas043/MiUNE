<?php
class Models_DbTable_Pensums extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_pensums';
    protected $_primary  = 'pk_pensum';
    protected $_sequence = true;

    public function init() {
    }

    /**
     *
     * @param int $Escuela
     * @return array
     */
    public function getSelect($Escuela) {
        $SQL = "SELECT {$this->_primary}, nombre
                FROM {$this->_name}
                WHERE fk_escuela = {$Escuela}
		ORDER BY nombre DESC;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
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
    
    
    
    public function getTurnos(){
        
        $SQL = "SELECT valor
                FROM vw_turnos";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        
        
    }


    public function getPensumDeEscuela($escuela){

         $SQL = "select pk_pensum
                 from tbl_pensums
                 where nombre = 'Vigente'
                 and fk_escuela = $escuela
                 ;";
               //var_dump($SQL) ;
        return $this->_db->fetchOne($SQL);

    }

    public function getAllPensums($escuela){
        $SQL = "SELECT DISTINCT nombre,nombre
                FROM tbl_pensums 
                WHERE nombre not in ('PIU 2012','EXTENSION','N/A')
                AND codigopropietario not in (9,18,0)";
        if ($escuela <> 0){
            $SQL .= "AND fk_escuela = {$escuela}"; 
        }
        $SQL .= "ORDER BY 1 DESC";
        
        $results = $this->_db->query($SQL);
        return $results->fetchAll();
    }
    public function getPensums($escuela){
        $SQL = "SELECT DISTINCT pk_pensum,nombre
                FROM tbl_pensums 
                WHERE codigopropietario not in (9,18,0)
                AND fk_escuela = {$escuela}
                ORDER BY 1 DESC;";
        $results = $this->_db->query($SQL);
        return $results->fetchAll();
    }
    public function getPensumInscrito($ci,$periodos,$escuelas) {
        $SQL = "SELECT ti.fk_pensum
                FROM tbl_usuarios       AS tu
                JOIN tbl_usuariosgrupos AS tug ON tu.pk_usuario = tug.fk_usuario
                JOIN tbl_inscripciones  AS ti  ON tug.pk_usuariogrupo = ti.fk_usuariogrupo
                JOIN tbl_periodos       AS tp  ON ti.fk_periodo = tp.pk_periodo
                JOIN vw_escuelas        AS ve  ON ti.fk_atributo = ve.pk_atributo
                WHERE tu.pk_usuario = {$ci}
                AND tp.pk_periodo = {$periodos} 
                AND ti.fk_atributo = {$escuelas};";
        //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }





}