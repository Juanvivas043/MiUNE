<?php
class Models_DbTable_Sorteo extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_sorteos';
    protected $_primary  = 'pk_sorteo';
    protected $_sequence = 'tbl_sorteos_pk_sorteo_seq';

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function getall($pk){

        $SQL= "SELECT *, 
                      TO_CHAR(fechainicio, 'DD/MM/YYYY') as fecha_inicio,
                      TO_CHAR(fechafin, 'DD/MM/YYYY') as fecha_fin,
                      TO_CHAR(fechatope, 'DD/MM/YYYY') as fecha_tope
                FROM tbl_sorteos s
                
                where pk_sorteo = $pk
                ;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getInscritos(){
        //recordar
    }

    public function getSorteos($data){

        $SQL= "SELECT pk_sorteo,
                       atr.valor,
                       Descripcion,
                       fechainicio,
                       fechafin,
                       fechatope,
                       fechasorteo,
                       publicado,
                       CASE WHEN publicado = true THEN 'FINALIZADO' ELSE 'ACTIVO' END AS estado,
                       COALESCE((SELECT COUNT(DISTINCT fk_usuariovehiculo)
                                 FROM tbl_usuariosvehiculossorteo uvs
                                 WHERE uvs.fk_sorteo = s.pk_sorteo),0) as inscritos
                FROM tbl_sorteos s
                JOIN tbl_atributos atr ON atr.pk_atributo = s.fk_tiposorteo
                where fk_periodo = {$data['periodo']}
                ;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getSpecificSorteo($data){

        $SQL= "SELECT s.*, at.valor
                from tbl_sorteos s
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                where fk_tiposorteo = {$data['tipo']}
                  AND fk_periodo = {$data['periodo']}
                  AND s.administrativo = false
                  AND current_date between fechainicio and fechafin ;";
         
         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getSpecificSorteoAdmin($data){

        $SQL= "SELECT s.*, at.valor
                from tbl_sorteos s
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                where fk_tiposorteo = {$data['tipo']}
                  AND fk_periodo = {$data['periodo']}
                  AND s.administrativo = true
                  AND current_date between fechainicio and fechafin ;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function checkifisAdministrativo($pk){

        $SQL= "SELECT s.*
               from tbl_sorteos s
               WHERE pk_sorteo = {$pk};";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getCurrentSorteos($data){

        $SQL= "SELECT s.*, at.valor
                from tbl_sorteos s
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                where fk_periodo = {$data['periodo']}
                  AND administrativo = false
                  AND current_date > fechainicio; ";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();

    }

    public function getActive($per){

        $SQL= "SELECT s.*, at.valor
                from tbl_sorteos s
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                where fk_periodo = {$per}
                  AND administrativo = false
                  AND current_date <= fechafin;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getActive_DeleteAction($per){

        $SQL= "SELECT s.*, at.valor
                from tbl_sorteos s
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                where fk_periodo = {$per}
                  AND current_date <= fechafin;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function addRow($data){
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function updateRow($id, $data) {

        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function publicar($id, $data){

        $SQL = "UPDATE tbl_sorteos set fechasorteo='{$data['fechasorteo']}', publicado='{$data['publicado']}'
                WHERE pk_sorteo = {$id};";

        $results = $this->_db->query($SQL);
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);
        return $affected;
    }

    public function checkSorteos($periodo,$tipos){
       
        $in = implode(',',$tipos);
        
        $SQL= "SELECT *
                FROM tbl_sorteos s
                where fk_tiposorteo IN ({$in})
                  and current_date between fechainicio  and fechafin
                  and fk_periodo = {$periodo};";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function checkSorteosAdmin($periodo,$tipos){

        $in = implode(',',$tipos);

        $SQL= "SELECT *
                FROM tbl_sorteos s
                where fk_tiposorteo IN ({$in})
                  and current_date between fechainicio  and fechafin
                  and fk_periodo = {$periodo}
                  and administrativo = true;";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }
    
}
?>
