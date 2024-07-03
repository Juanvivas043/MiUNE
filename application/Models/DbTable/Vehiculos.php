<?php
class Models_DbTable_Vehiculos extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_vehiculos';
    protected $_primary  = 'pk_vehiculo';
    protected $_sequence = 'tbl_vehiculos_pk_vehiculo_seq';

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function getRowByPlaca($placa){

        $SQL= "select *,
                       modelo.valor as modelo_valor,
                       tipo.valor as tipo_valor,
                       (SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo) as marca_valor,
                       (SELECT at.pk_atributo
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo) as marca_pk
               from tbl_vehiculos v
               JOIN tbl_atributos tipo ON tipo.pk_atributo = v.fk_tipo
               JOIN tbl_atributos modelo ON modelo.pk_atributo = v.fk_modelo
               where placa ilike '{$placa}';";
              // echo $SQL;
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

}

?>

