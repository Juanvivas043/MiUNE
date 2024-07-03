<?php
class Models_DbTable_Periodos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_periodos';
    protected $_primary  = 'pk_periodo';
    protected $_sequence = true;

    public function init() {
    }

    public function getSelect($Limit = null) {
        $Limit = (isset($Limit))? "LIMIT {$Limit}" : null;

        $SQL = "SELECT pk_periodo, (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad({$this->_primary}::text, 4, '0') || ', ' || to_char(fechainicio, 'MM-yyyy') || ' / ' ||  to_char(fechafin, 'MM-yyyy') END) as nombre
                FROM {$this->_name}
		ORDER BY pk_periodo DESC limit 94;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getUltimo() {
        $SQL = "SELECT pk_periodo FROM tbl_periodos WHERE current_date BETWEEN  fechainicio AND fechafin;";

        return $this->_db->fetchOne($SQL);
    }

    public function getMasNuevo() {
        $SQL = "SELECT pk_periodo FROM tbl_periodos ORDER BY 1 DESC LIMIT 1;";

        return $this->_db->fetchOne($SQL);
    }

    public function getInicio($per){
        $SQL = "SELECT fechainicio FROM tbl_periodos WHERE pk_periodo = {$per};";

        return $this->_db->fetchOne($SQL);
    }

    public function getDuracion($Id) {
        $SQL = "SELECT fechainicio, fechafin FROM tbl_periodos WHERE pk_periodo = {$Id};";

        $results = $this->_db->query($SQL);
        $results = (array)$results->fetchAll();

        return $results[0];
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

    public function checkDateInPeriodo($fecha,$up){

         $SQL = "SELECT CASE WHEN '{$fecha}' between fechainicio and fechafin THEN true ELSE false END AS valid
                 FROM tbl_periodos
                 WHERE pk_periodo = {$up}";

        $results = $this->_db->query($SQL);
        $results = (array)$results->fetchAll();

        return $results[0];


    }

    public function checkDateInPeriodoCast($fecha,$up){

        $SQL = "SELECT CASE WHEN TO_CHAR({$fecha},'YYYY-MM-DD') between TO_CHAR(fechainicio,'YYYY-MM-DD') and TO_CHAR(fechafin,'YYYY-MM-DD') THEN true ELSE false END AS valid
                 FROM tbl_periodos
                 WHERE pk_periodo = {$up}";
                 echo $SQL;
        $results = $this->_db->query($SQL);
        $results = (array)$results->fetchAll();

        return $results[0];


    }
    //función para traer los períodos ordenados y limitados dinamicamente
      
 public function getPeriodos ($limit){
        $SQL="SELECT pk_periodo, fechainicio, fechafin, inicioclases, fechacorte
              from tbl_periodos
              order by 1 desc
              limit {$limit}";
        $results=$this->_db->query($SQL);
        return (array)$results->fetchAll();

    }
 public function getPeriodoActual(){
         
        $SQL ="select distinct pk_periodo
                from tbl_periodos
                where current_date between fechainicio and fechafin";

        return $this->_db->fetchOne($SQL);
      
  }

    //función para agregar período
    public function addPeriodo($pk_periodo,$fechainicio,$fechafin,$inicioclases,$fechacorte){
        $SQL="INSERT INTO tbl_periodos (pk_periodo,fechainicio,fechafin,inicioclases,fechacorte)
              VALUES ({$pk_periodo},'{$fechainicio}','{$fechafin}','{$inicioclases}','{$fechacorte}')";
        $results=$this->_db->query($SQL);
        return (array)$results->fetchAll();

    }
    //función para modificar período
    public function modPeriodo($pk_periodo,$fechainicio,$fechafin,$inicioclases,$fechacorte){
        $SQL="UPDATE tbl_periodos 
              SET fechainicio  = '{$fechainicio}',
                  fechafin     = '{$fechafin}',
                  inicioclases = '{$inicioclases}',
                  fechacorte   = '{$fechacorte}' 
              WHERE pk_periodo={$pk_periodo}";
        $results=$this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function monthPeriodo($id){
        $month = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $periodo = $this->getDuracion($id);
        $str     = $month[date("n", strtotime($periodo['fechainicio'])) - 1]." - ".$month[date("n", strtotime($periodo['fechafin'])) - 1]." ".date("Y", strtotime($periodo['fechafin']));
        return $str;
    }

        public function periodosBecados(){
        $SQL="SELECT DISTINCT pe.pk_periodo,
                (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(pe.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(pe.fechafin, 'MM-yyyy') END) as nombre
                FROM tbl_periodos as pe
                WHERE pk_periodo>129
                ORDER BY pk_periodo DESC";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
}
    public function listadoPeriodos(){
        $SQL = " SELECT pk_periodo||' - '||TO_CHAR(fechainicio,'MONTH YYYY')||' - '||TO_CHAR(fechafin,'MONTH YYYY') AS periodo,fechainicio, inicioclases,fechafin FROM tbl_periodos
                 ORDER BY pk_periodo DESC";
        $results=$this->_db->query($SQL);
        return (array)$results->fetchAll();

    }
}
