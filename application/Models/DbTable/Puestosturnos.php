<?php
class Models_DbTable_Puestosturnos extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_puestosturnos';
    protected $_primary  = 'pk_puestoturno';
    protected $_sequence = 'tbl_puestosturnos_pk_puestoturno_seq';

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function getPuestosTurnos($periodo){

        $SQL= "SELECT tiposorteo,
                       turno,
                       cantidad,
                       inscritos,
                       seleccionados,
                       cantidad - seleccionados as restantes,
                       pago,
                       seleccionados - pago as porpagar,
                       CASE WHEN tiposorteo = 'Carros' THEN 1 ELSE 2 END as orden,
                       pk_atributo,
                       pk_puestoturno
                FROM (

                SELECT at.valor as tiposorteo,
                       t.valor as turno,
                       t.pk_atributo,
                       pt.cantidad as cantidad,
                       pk_puestoturno,

                (select count(distinct pk_usuariovehiculosorteo)
                 from tbl_usuariosvehiculossorteo uvs
                 JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                 where uvs.fk_turno = pt.fk_turno
                   and s.fk_tiposorteo = pt.fk_tiposorteo
                   and s.fk_periodo = {$periodo}) as inscritos,

                (select count(distinct pk_usuariovehiculosorteo)
                 from tbl_usuariosvehiculossorteo uvs
                 JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                 where uvs.fk_turno = pt.fk_turno
                   and s.fk_tiposorteo = pt.fk_tiposorteo
                   and uvs.seleccionado = true
                   and s.fk_periodo = {$periodo}) as seleccionados,

                (select count(distinct pk_usuariovehiculosorteo)
                 from tbl_usuariosvehiculossorteo uvs
                 JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                 where uvs.fk_turno = pt.fk_turno
                   and s.fk_tiposorteo = pt.fk_tiposorteo
                   and uvs.pago = true
                   and s.fk_periodo = {$periodo}) as pago
                from tbl_puestosturnos pt
                JOIN tbl_atributos at ON at.pk_atributo = pt.fk_tiposorteo
                JOIN vw_turnos t ON t.pk_atributo = pt.fk_turno
                where pt.fk_periodo = {$periodo}
                ) as sqt
                order by orden , 10 ASC

               ;";

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

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function existTurnoPeriodo($turno,$periodo,$tipo){

        $SQL= "select *
                from tbl_puestosturnos
                where fk_periodo = {$periodo}
                and fk_turno = {$turno}
                and fk_tiposorteo = {$tipo} ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

}

?>

