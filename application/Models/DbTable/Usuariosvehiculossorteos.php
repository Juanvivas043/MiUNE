<?php
class Models_DbTable_Usuariosvehiculossorteos extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_usuariosvehiculossorteo';
    protected $_primary  = 'pk_usuariovehiculosorteo';
    protected $_sequence = 'tbl_usuariosvehiculossorteo_pk_usuariovehiculosorteo_seq';

    private $searchParams = array('nombre', 'apellido', 'escuela', 'cedula','emitido','pago','seleccionado');

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
       // $this->logger = Zend_Registry::get('logger');
    }

    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

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

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function checkIfInscrito($ci,$periodo,$tipo){

         $SQL= "SELECT*
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                where uv.fk_usuario = {$ci}
                  and s.fk_periodo = {$periodo}
                  and s.fk_tiposorteo = {$tipo};";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function checkIfInscritoAny($ci,$periodo){

         $SQL= "SELECT *
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                where uv.fk_usuario = {$ci}
                  and s.fk_periodo = {$periodo};";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getAllInscripciones($ci){

        $SQL= "SELECT t.valor as turno,
                at.valor as sort_tipo,
                s.descripcion,
                COALESCE((SELECT at.valor
                FROM tbl_atributos at
                where pk_atributo = modelo.fk_atributo),modelo.valor) || ' ' ||
                CASE WHEN (SELECT at.valor
                FROM tbl_atributos at
                where pk_atributo = modelo.fk_atributo) IS NULL THEN '' ELSE  modelo.valor END AS modelo,
                v.placa,
                v.ano,
                CASE WHEN uvs.seleccionado = true THEN 'seleccionado' ELSE 'Inscrito' END AS estatus,
                uvs.pago,
                uvs.numeropago,
               s.fk_periodo,
                pk_usuariovehiculosorteo
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                JOIN vw_turnos t ON t.pk_atributo = uvs.fk_turno
                JOIN tbl_atributos at ON at.pk_atributo = s.fk_tiposorteo
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_atributos modelo ON v.fk_modelo = modelo.pk_atributo
                WHERE uv.fk_usuario = {$ci}
                               ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function participantesSorteo($pk,$turno){

       if(!isset($turno)){
        $searchParamsAdmin = array('nombre', 'apellido', 'tiposorteo', 'cedula','emitido');
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($searchParamsAdmin, $this->searchData);

            $SQL= "SELECT DISTINCT sqt.nombre, sqt.apellido, sqt.cedula, sqt.emitido, sqt.seleccionado, sqt.pk_usuariovehiculosorteo,tiposorteo FROM(
                select distinct u.nombre, u.apellido, u.pk_usuario as cedula,
                       CASE WHEN emitido = false THEN 'No Emitido' ELSE 'Emitido' end as emitido,
                       CASE WHEN seleccionado = false THEN 'Inscrito' ELSE 'Seleccionado' end  as seleccionado,
                       uvs.pk_usuariovehiculosorteo, ug.pk_usuariogrupo, gru.grupo as tiposorteo
                        ,COALESCE(wiegand::varchar, 'N/T') as wiegand
                       ,CASE WHEN cactivo = false THEN 'Inactivo' ELSE 'Activo' end  as cactivo
                , CASE WHEN (wiegand is not null AND cactivo = false AND pago = true) THEN 'Si' ELSE 'No' END as activable
		, v.placa as placa 
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
		JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_usuarios u ON u.pk_usuario = uv.fk_usuario
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN vw_grupos gru ON gru.pk_atributo = ug.fk_grupo 
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                LEFT OUTER JOIN tbl_carnets crn ON crn.fk_usuariogrupo = ug.pk_usuariogrupo AND crn.wiegand IS NOT NULL AND crn.fk_estado =1428
                where fk_sorteo = {$pk}
                 AND ug.pk_usuariogrupo NOT IN (SELECT distinct fk_usuariogrupo
                                                FROM tbl_listadossorteos)
                 AND ug.pk_usuariogrupo IN (SELECT sqtt.pk_usuariogrupo
                        FROM (SELECT *,
                            CASE    WHEN gru.grupo = 'Docente' THEN 1
                                WHEN gru.grupo = 'Administrativo' THEN 2
                                WHEN gru.grupo = 'Estudiante' THEN 3
                            END as valor
                        FROM tbl_usuariosgrupos usug
                        JOIN vw_grupos as gru ON gru.pk_atributo = usug.fk_grupo
                        WHERE usug.fk_usuario = ug.fk_usuario
                        ORDER BY valor) as sqtt 
                        WHERE  sqtt.valor > 0
                        LIMIT 1)                                                
                                                ) as sqt
                where pk_usuariovehiculosorteo is not null
                 {$whereSearch};";
        }else{

        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL= " SELECT DISTINCT * FROM(
                select distinct u.nombre, u.apellido, esc.escuela, u.pk_usuario as cedula,
                       CASE WHEN emitido = false THEN 'No Emitido' ELSE 'Emitido' end as emitido,
                       CASE WHEN pago = false THEN 'No pago' ELSE 'Si' end as pago,
                       CASE WHEN retirado = false THEN
                       CASE WHEN seleccionado = false THEN 'Inscrito' ELSE 'Seleccionado' end ELSE
                                 'Retirado' END as seleccionado,
                       uvs.pk_usuariovehiculosorteo
                        ,COALESCE(wiegand::varchar, 'N/T') as wiegand
                        ,CASE WHEN cactivo = false THEN 'Inactivo' ELSE 'Activo' end  as cactivo
                	,CASE WHEN (wiegand is not null AND cactivo = false AND pago = true) THEN 'Si' ELSE 'No' END as activable
		, v.placa as placa 
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
		JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                JOIN tbl_usuarios u ON u.pk_usuario = uv.fk_usuario
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                LEFT OUTER JOIN tbl_carnets crn ON crn.fk_usuariogrupo = ug.pk_usuariogrupo AND crn.wiegand IS NOT NULL AND crn.fk_estado =1428
                where fk_sorteo = {$pk}
                 AND i.fk_periodo = s.fk_periodo
                 AND uvs.fk_turno = {$turno}
                -- AND s.fk_tiposorteo = {$tipo}
                 AND ug.pk_usuariogrupo NOT IN (SELECT distinct fk_usuariogrupo
                                                FROM tbl_listadossorteos)) as sqt
                 where pk_usuariovehiculosorteo is not null
                 {$whereSearch}
                ORDER BY cactivo,seleccionado,pk_usuariovehiculosorteo,pago,emitido


                ";

        }


         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }


 public function participantesSorteoNew($periodo,$turno,$sorteo)
 {
    $SQL= "SELECT DISTINCT sqt.cedula,
                           sqt.nombre,
                           sqt.apellido,
                           sqt.escuela,
                           sqt.pago,
                           sqt.estado,
                           sqt.carnet,
                           sqt.activable,
                           sqt.usu_sorteo

            FROM(
                    SELECT DISTINCT tu.nombre, 
                                    tu.apellido, 
                                    vesc.escuela, 
                                    tu.pk_usuario AS cedula,
                                    CASE WHEN emitido = false THEN 'No Emitido' ELSE 'Emitido' END AS emitido,
                                    CASE WHEN pago = false THEN 'No pago' ELSE 'Si' END AS pago,
                                    CASE WHEN retirado = false THEN
                                    CASE WHEN seleccionado = false THEN 'Inscrito' ELSE 'Seleccionado' END ELSE
                                             'Retirado' END AS seleccionado,                                            
                                    COALESCE(wiegand::varchar, 'N/T') AS carnet,
                                    CASE WHEN cactivo = false THEN 'Inactivo' ELSE 'Activo' END AS estado,
                                    CASE WHEN (wiegand IS NOT NULL AND cactivo = false AND pago = true) THEN 'Si' ELSE 'No' END AS activable,
                                    tuvs.pk_usuariovehiculosorteo,tv.placa AS placa,
                                    tuvs.pk_usuariovehiculosorteo as usu_sorteo

                    FROM tbl_usuariosvehiculossorteo tuvs
                    JOIN tbl_usuariosvehiculos tuv          ON tuv.pk_usuariovehiculo = tuvs.fk_usuariovehiculo
                    JOIN tbl_vehiculos tv                   ON tv.pk_vehiculo = tuv.fk_vehiculo
                    JOIN tbl_usuarios tu                    ON tu.pk_usuario = tuv.fk_usuario
                    JOIN tbl_usuariosgrupos tug             ON tug.fk_usuario = tu.pk_usuario
                    JOIN tbl_inscripciones ti               ON ti.fk_usuariogrupo = tug.pk_usuariogrupo
                    JOIN vw_escuelas vesc                   ON vesc.pk_atributo = ti.fk_atributo
                    JOIN tbl_sorteos ts                     ON ts.pk_sorteo = tuvs.fk_sorteo
                    LEFT OUTER JOIN tbl_carnets tcrn        ON tcrn.fk_usuariogrupo = tug.pk_usuariogrupo AND tcrn.wiegand IS NOT NULL AND tcrn.fk_estado =1428
                    WHERE fk_sorteo = {$sorteo}
                    AND ti.fk_periodo = {$periodo}
                    AND tuvs.fk_turno = {$turno}
                    AND seleccionado = TRUE
                    AND tug.pk_usuariogrupo NOT IN (SELECT DISTINCT fk_usuariogrupo
                                                          FROM tbl_listadossorteos)) as sqt
            WHERE pk_usuariovehiculosorteo IS NOT NULL
            ORDER BY sqt.pago desc ,sqt.carnet asc 
         ";
    //var_dump($SQL);die;
      $results = $this->_db->query($SQL);
      return (array)$results->fetchAll();
    }

    public function updateEstadoCarnetNew($pk,$estado)
    {
      $SQL = "UPDATE tbl_usuariosvehiculossorteo
                SET cactivo = {$estado}
              WHERE pk_usuariovehiculosorteo 
                IN ({$pk})";
      $results = $this->_db->query($SQL);
      return (array)$results->fetchAll();
    }


    public function getSorteo($periodo){

        $SQL = "SELECT distinct ts.pk_sorteo,ta.valor,ts.descripcion
                FROM tbl_usuarios tu
                JOIN tbl_usuariosvehiculos tuv          ON tu.pk_usuario = tuv.fk_usuario
                JOIN tbl_usuariosvehiculossorteo tuvs   ON tuv.pk_usuariovehiculo = tuvs.fk_usuariovehiculo
                JOIN tbl_sorteos ts                     ON tuvs.fk_sorteo = ts.pk_sorteo
                JOIN tbl_atributos ta                   ON ts.fk_tiposorteo = ta.pk_atributo
                WHERE ts.fk_periodo = {$periodo}";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }



     public function cantidadPuestosSorteo($pk,$turno,$periodo){

        $SQL= "SELECT cantidad - (SELECT COUNT(DISTINCT pk_usuariovehiculosorteo)
                                   FROM tbl_usuariosvehiculossorteo
                                   where fk_sorteo = {$pk}
                                     and fk_turno = {$turno}
                                     and seleccionado = true) as restantes
                    from tbl_puestosturnos
                    where fk_tiposorteo = (SELECT fk_tiposorteo
                                          FROM tbl_sorteos
                                          where pk_sorteo = {$pk})
                     and fk_turno= {$turno}
                     and fk_periodo = {$periodo}
              ";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function isParticipantes($ci,$periodo){

        $SQL= "SELECT uvs.*, uv.fk_usuario, s.fk_periodo
                FROM tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                where uv.fk_usuario = {$ci}
                  and s.fk_periodo = {$periodo}
                ";

         $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function vehiculoIsInSorteo($periodo,$turno,$vehiculo){

        $SQL= " SELECT *
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                where fk_vehiculo = (select pk_vehiculo
                                     from  tbl_vehiculos v
                                     JOIN tbl_usuariosvehiculos uv ON uv.fk_vehiculo = v.pk_vehiculo
                                     where pk_usuariovehiculo = {$vehiculo})
                  AND uvs.fk_turno = {$turno}
                  AND s.administrativo = false
                  AND s.fk_periodo = {$periodo}
                ";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();



    }

    public function vehiculoIsInSorteoAdmin($periodo,$turno,$vehiculo){

        $SQL= " SELECT *
                from tbl_usuariosvehiculossorteo uvs
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                JOIN tbl_vehiculos v ON v.pk_vehiculo = uv.fk_vehiculo
                where fk_vehiculo = (select pk_vehiculo
                                     from  tbl_vehiculos v
                                     JOIN tbl_usuariosvehiculos uv ON uv.fk_vehiculo = v.pk_vehiculo
                                     where pk_usuariovehiculo = {$vehiculo})
                  AND s.administrativo = true
                  AND s.fk_periodo = {$periodo}
                ";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();



    }


     public function hasPago($pk){

        $SQL= "SELECT numeropago
                FROM tbl_usuariosvehiculossorteo
                WHERE pk_usuariovehiculosorteo = {$pk}";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

    public function updatePago($pk){

        $SQL= "UPDTE tbl_usuariosvehiculossorteo SET pago = true
               WHERE pk_usuariovehiculosorteo = {$pk};
                ";

        $results = $this->_db->query($SQL);

    }

    public function isSelected($pk){
        
        $SQL= "SELECT CASE WHEN seleccionado = 't' AND s.publicado = 't' THEN 'Seleccionado' ELSE 'Inscrito' END AS estado,
                       s.descripcion,
                       t.valor
                FROM tbl_usuariosvehiculossorteo uvs
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
                JOIN tbl_usuarios u ON u.pk_usuario = uv.fk_usuario
                JOIN vw_turnos t ON t.pk_atributo = uvs.fk_turno
                WHERE u.pk_usuario = {$pk}
                ORDER BY pk_usuariovehiculosorteo DESC;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

    public function getCiFromSorteo($pk){

        $SQL= "SELECT uv.fk_usuario
               from tbl_usuariosvehiculossorteo uvs
               JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
               where uvs.pk_usuariovehiculosorteo = {$pk}";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

    public function getvehiculoInSorteo($pk,$periodo){

        $SQL= "SELECT uv.fk_usuario
               from tbl_usuariosvehiculossorteo uvs
               JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
               JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
               where uvs.fk_usuariovehiculo = {$pk}
                and s.fk_periodo = {$periodo}
";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();


    }

    public function getSorteoTipo($pk){


        $SQL = "SELECT DISTINCT fk_tiposorteo
                FROM tbl_usuariosvehiculossorteo  uvs
                JOIN tbl_sorteos s ON s.pk_sorteo = uvs.fk_sorteo
                WHERE pk_usuariovehiculosorteo = {$pk};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function updateEstadoCarnet($id, $estado) {

    	if(!is_numeric($id))
    		return;

        $SQL = "UPDATE tbl_usuariosvehiculossorteo
      				    SET cactivo = {$estado}
      				    WHERE pk_usuariovehiculosorteo IN(
          				  SELECT pk_usuariovehiculosorteo
          				    FROM tbl_usuariosvehiculossorteo uvs
          				    JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
          				    JOIN tbl_usuarios u ON u.pk_usuario = uv.fk_usuario
          				    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
          				    WHERE ug.pk_usuariogrupo IN(
          				  SELECT ug.pk_usuariogrupo
          				    FROM tbl_usuariosvehiculossorteo uvs
          				    JOIN tbl_usuariosvehiculos uv ON uv.pk_usuariovehiculo = uvs.fk_usuariovehiculo
          				    JOIN tbl_usuarios u ON u.pk_usuario = uv.fk_usuario
          				    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
          				    WHERE pk_usuariovehiculosorteo ={$id}));";

                     // var_dump($SQL);die;
        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
}

?>


