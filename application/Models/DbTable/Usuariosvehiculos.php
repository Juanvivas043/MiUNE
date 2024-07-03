<?php
class Models_DbTable_Usuariosvehiculos extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_usuariosvehiculos';
    protected $_primary  = 'pk_usuariovehiculo';
    protected $_sequence = 'tbl_usuariosvehiculos_pk_usuariovehiculo_seq';

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
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

    public function updateDeleted($id,$status){

        $SQL= "UPDATE tbl_usuariosvehiculos SET eliminado={$status} where pk_usuariovehiculo = {$id};";

         $results = $this->_db->query($SQL);

    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }
    

    public function checkIfExists($ci,$carro = null){

        $carro == null ? null : $carro = 'AND fk_vehiculo = ' . $carro;

        $SQL= "select  COALESCE((SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo),modelo.valor) as marca,
                       CASE WHEN (SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo) IS NULL THEN '' ELSE  modelo.valor END AS modelo,
                       v.placa,
                       v.ano,
                       uv.pk_usuariovehiculo
                from tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos modelo ON modelo.pk_atributo = v.fk_modelo
                WHERE uv.fk_usuario = {$ci}
                  AND uv.eliminado = false
                 {$carro};";

        $SQL= "SELECT DISTINCT COALESCE((SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo),modelo.valor) as marca,
                       CASE WHEN (SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo) IS NULL THEN '' ELSE  modelo.valor END AS modelo,
                       v.placa,
                       v.ano,
                       uv.pk_usuariovehiculo,
                       COALESCE(sor.pk_sorteo > 0, false) as inscrito,
                       CASE WHEN uvs.emitido THEN 'Emitido' 
                            WHEN sor.pk_sorteo > 0 THEN 'Solicitado' 
                            ELSE 'No Solicitado' END as nombre_sorteo                 FROM tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos modelo ON modelo.pk_atributo = v.fk_modelo
        LEFT OUTER JOIN tbl_usuariosvehiculossorteo uvs ON uvs.fk_usuariovehiculo = uv.pk_usuariovehiculo
        LEFT OUTER JOIN tbl_sorteos sor ON sor.pk_sorteo = uvs.fk_sorteo AND sor.fk_periodo = (SELECT pk_periodo FROM tbl_periodos WHERE fechainicio <= CURRENT_DATE AND fechafin >= CURRENT_DATE)
                WHERE uv.fk_usuario = {$ci}
                  AND uv.eliminado = false
          AND (sor.pk_sorteo IS NOT null OR ( sor.pk_sorteo IS null AND uv.pk_usuariovehiculo NOT IN(
                  SELECT pk_usuariovehiculo
                  FROM tbl_usuariosvehiculos uv
                  JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                  JOIN tbl_usuariosvehiculossorteo uvs ON uvs.fk_usuariovehiculo = uv.pk_usuariovehiculo
                  JOIN tbl_sorteos sor ON sor.pk_sorteo = uvs.fk_sorteo AND sor.fk_periodo = (SELECT pk_periodo FROM tbl_periodos WHERE fechainicio <= CURRENT_DATE AND fechafin >= CURRENT_DATE)
                  WHERE uv.fk_usuario = {$ci}
                  AND uv.eliminado = false)))
         {$carro};";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function checkIfEliminado($ci,$carro = null){

        $carro == null ? null : $carro = 'AND fk_vehiculo = ' . $carro;

        $SQL= "select  COALESCE((SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo),modelo.valor) as marca,
                       CASE WHEN (SELECT at.valor
                       FROM tbl_atributos at
                       where pk_atributo = modelo.fk_atributo) IS NULL THEN '' ELSE  modelo.valor END AS modelo,
                       v.placa,
                       v.ano,
                       uv.eliminado,
                       uv.pk_usuariovehiculo
                from tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos modelo ON modelo.pk_atributo = v.fk_modelo
                WHERE uv.fk_usuario = {$ci}
                  --AND uv.eliminado = false
                 {$carro};";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getAllData($fk){

        $SQL= "SELECT *,
                CASE WHEN tipoveh = 'Moto' THEN (SELECT pk_atributo FROm tbl_atributos WHERe valor = 'Motos') ELSE (SELECT pk_atributo FROm tbl_atributos WHERe valor = 'Carros') END as tipo
                FROM (
                SELECT COALESCE((SELECT at.valor
                                       FROM tbl_atributos at
                                       where pk_atributo = modelo.fk_atributo),modelo.valor) as marca,
                       COALESCE((SELECT at.pk_atributo
                                       FROM tbl_atributos at
                                       where pk_atributo = modelo.fk_atributo),modelo.pk_atributo) as marca_pk,
                                       CASE WHEN (SELECT at.valor
                                       FROM tbl_atributos at
                                       where pk_atributo = modelo.fk_atributo) IS NULL THEN '' ELSE  modelo.valor END AS modelo,
                                       CASE WHEN (SELECT at.pk_atributo
                                       FROM tbl_atributos at
                                       where pk_atributo = modelo.fk_atributo) IS NULL THEN null ELSE  modelo.pk_atributo END AS modelo_pk,
                                       v.placa,
                                       v.ano,
                                       uv.pk_usuariovehiculo,
                                       tipo.valor as tipoveh
                from tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos modelo ON modelo.pk_atributo = v.fk_modelo
                JOIN tbl_atributos tipo ON tipo.pk_atributo = v.fk_tipo
                where pk_usuariovehiculo = {$fk}
                ) as sqt;
";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getUserVehicules($ci,$tipo){

         if($tipo == 8977){
             $tipo = 'and at2.pk_atributo = 8977';
         }elseif($tipo == 8976){
             $tipo = 'and at2.fk_atributo = 8976';
         }

         $SQL= "select uv.pk_usuariovehiculo, at2.valor || ', ' ||  at.valor || ', Placa: ' || v.placa as valor
                FROM tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos at ON at.pk_atributo = v.fk_modelo
                JOIN tbl_atributos at2 ON at2.pk_atributo = at.fk_atributo
                where fk_usuario = {$ci}
                  {$tipo}
                  --and at2.pk_atributo = 12373
                  --and at2.fk_atributo = 12372
                  and uv.eliminado = false
                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getaVehiculo($pk_uvs){

        $SQL= "select at2.fk_atributo,at2.pk_atributo,at2.valor || ', ' ||  at.valor || ', Placa: ' || v.placa as valor
               FROM tbl_usuariosvehiculos uv
               JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
               JOIN tbl_usuariosvehiculossorteo uvs ON uvs.fk_usuariovehiculo = uv.pk_usuariovehiculo
               JOIN tbl_atributos at ON at.pk_atributo = v.fk_modelo
               JOIN tbl_atributos at2 ON at2.pk_atributo = at.fk_atributo
               and uvs.pk_usuariovehiculosorteo = {$pk_uvs}
                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();



    }

    public function getUserVehiculoTipes($ci){

        $SQL= "SELECT DISTINCT CASE WHEN at2.fk_atributo is null THEN 8977 ELSE 8976 END as tipo
                from tbl_usuariosvehiculos uv
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos at ON at.pk_atributo = v.fk_modelo
                JOIN tbl_atributos at2 ON at2.pk_atributo = at.fk_atributo
                where fk_usuario = {$ci}
                  and uv.eliminado = false
                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }


    public function getcantVehiculos($ci){

        $SQL= "select COUNT(distinct pk_usuariovehiculo)
               FROM tbl_usuariosvehiculos uv
               WHERE uv.fk_usuario = {$ci}
               and eliminado = false;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }
    

}

?>

