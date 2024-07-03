<?php


class Models_DbTable_Transaccionespg extends Zend_Db_Table {

        private $searchParams = array('pk_usuario','nombre','apellido', "LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");

        protected function _setupTableName(){

        $this->_name     = 'tbl_transaccionespg';
        $this->_primary  = 'pk_transaccionpg';

    }
         public function init()
    {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function addRow($data) {

        $data = array_filter($data);
        $newRow = $this->createRow($data);
        $affected = $newRow->save();
        return $affected;  

      }


    public function getPkTransaccion($pk){
        $SQL = "SELECT pk_transaccionpg
        FROM tbl_transaccionespg
        WHERE pk_transaccionpg = {$pk}
        ";

        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }
    public function alterOrderId($pk,$OrderId){

        $SQL = "UPDATE tbl_transaccionespg SET OrderId = '{$OrderId}' WHERE pk_transaccionpg = {$pk}";
        $results = $this->_db->query($SQL);
        return $results->fetchAll();

    }

	public function getTransaccion($id){

        $SQL = "SELECT *, to_char(fechahora, 'YYYY-MM-DD HH24:MI')  as fechahora_formated
        FROM tbl_transaccionespg
        WHERE pk_transaccionpg = {$id}";
        $results = $this->_db->query($SQL);
        return $results->fetchAll();
		
	}

	public function getTransaccionByControl($numerocontrol){

        $SQL = "SELECT *, to_char(fechahora, 'YYYY-MM-DD HH24:MI')  as fechahora_formated
        FROM tbl_transaccionespg
        WHERE numerocontrol = '{$numerocontrol}' 
        ORDER BY pk_transaccionpg ASC";
        $results = $this->_db->query($SQL);
        $results =  $results->fetchAll();
        return $results[0];
		
	}

	public function getTransaccionesByControl($numerocontrol){

        $SQL = "SELECT *, to_char(fechahora, 'YYYY-MM-DD HH24:MI')  as fechahora_formated
        FROM tbl_transaccionespg
        WHERE numerocontrol = '{$numerocontrol}' 
        ORDER BY pk_transaccionpg ASC";
        $results = $this->_db->query($SQL);
        $results =  $results->fetchAll();
        return $results;
		
	}

    public function getTransacciones($usuario){
        
        $SQL = "SELECT fk_periodo,fk_atributo,
                    CASE
                      WHEN fk_atributo = '1' THEN 'Procesando'
                      WHEN fk_atributo = '2' THEN 'Rechazado'
                      WHEN fk_atributo = '3' THEN 'Aprobado'
                      WHEN fk_atributo = '4' THEN 'Pendiente'
                    END as estado
                    ,CASE WHEN fk_tipo = '1' THEN 'Pago de Cuotas'
                    END as tipo,
                    factura,
                   to_char(fechahora,'DD-MM-YYYY') fecha,
                   to_char(fechahora,'HH12:MI AM') hora,descripcion,
                   cantidad,monto,cantidad * monto as monto_total,numerocontrol
                FROM tbl_transaccionespg pg
                JOIN tbl_usuariosgrupos ug ON pk_usuariogrupo = pg.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE pk_usuario = {$usuario} 
                AND numerocontrol IS NOT NULL
                ORDER BY fechahora DESC";

        $results = $this->_db->query($SQL);
        $result = $results->fetchAll();

        return $result;

    }

    public function getTransaccionesCount($usuario){

        $SQL = "SELECT count(pk_transaccionpg)
                FROM tbl_transaccionespg pg
                JOIN tbl_usuariosgrupos ug ON pk_usuariogrupo = pg.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE pk_usuario = {$usuario}";

        return $this->_db->fetchOne($SQL);

    }

    public function isCompleted($usuario,$control,$atributo){
        $SQL = "SELECT count(pk_transaccionpg)
                FROM tbl_transaccionespg pg
                JOIN tbl_usuariosgrupos ug ON pk_usuariogrupo = pg.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                WHERE pk_usuario = {$usuario}
                AND pg.numerocontrol = '{$control}'
                AND fk_atributo <> $atributo" ;

        return (bool) $this->_db->fetchOne($SQL);
    }

    public function updateEstado($usuario,$control,$atributo){
        $SQL = "UPDATE tbl_transaccionespg
                    SET fk_atributo = {$atributo}
                    WHERE pk_transaccionpg IN (
                            SELECT pk_transaccionpg
                            FROM tbl_transaccionespg pg
                            JOIN tbl_usuariosgrupos ug ON pk_usuariogrupo = pg.fk_usuariogrupo
                            JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                            WHERE pk_usuario = {$usuario}
                            AND pg.numerocontrol = '{$control}'
                            AND fk_atributo = 1
                    );";

        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }


    public function updateLotRef($numerocontrol,$lot_ref){
        $SQL = "UPDATE tbl_transaccionespg
                    SET lot_ref = '{$lot_ref}'
                    WHERE numerocontrol = '{$numerocontrol}'";
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function updateCobNum($numerocontrol,$cob_num){
        $SQL = "UPDATE tbl_transaccionespg
                    SET cob_num = '{$cob_num}'
                    WHERE numerocontrol = '{$numerocontrol}'";
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

     public function updateCobNumByPk($pk,$cob_num){
        $SQL = "UPDATE tbl_transaccionespg
                    SET cob_num = '{$cob_num}' , reg_profit = true
                    WHERE pk_transaccionpg = {$pk}";
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function updateFacturaByPk($pk,$fact_num){
        $SQL = "UPDATE tbl_transaccionespg
                    SET fact_num = '{$fact_num}' 
                    WHERE pk_transaccionpg = {$pk}";
                    
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }
    public function updateNumeroControl($pk,$numerocontrol){
        $SQL = "UPDATE tbl_transaccionespg
                    SET numerocontrol = {$numerocontrol}
                    WHERE pk_transaccionpg = {$pk}";
        $results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
    }

    public function getFacturaByPk($pk){
         $SQL = "SELECT factura
                FROM tbl_transaccionespg pg
                WHERE pk_transaccionpg = {$pk}";
        return $this->_db->fetchOne($SQL);
    }

    public function getInfoByControl($control){
        $SQL = "SELECT fechahora, descripcion, cantidad, fk_tipo 
                FROM tbl_transaccionespg pg
                WHERE numerocontrol = '{$control}'";
        $results = $this->_db->query($SQL);
        return $results->fetchAll();
    }

    public function getTransaccionesByDate($sede, $fechaInicio, $fechaFin){
    
       $SQL = "SELECT * FROM (SELECT DISTINCT tu.pk_usuario,
                                              tu.nombre        AS nombre,
                                              tu.apellido      AS apellido,
                                              te.nombre        AS Sede,  --sede
                                              cob_num          AS Cobro,   --# de cobro
                                              lot_ref          AS lote,
                                              TO_CHAR(pg.fechahora, 'YYYY-MM-DD')  AS DIA,         --columna dia
                                              TO_CHAR(pg.fechahora, 'HH12:MI:SS AM') as HORA,
                                               pg.factura,
                                              pg.montototal,
                                              pg.cantidad,
                                              pg.monto,
                                              case when row_number() over(PARTITION by pg.numerocontrol) > 1 then '--------'
                                              else (select to_char(monto,'9G999G999G999G999G999.99') from (select fk_usuario,
                                                                                    numerocontrol,
                                                                                    sum(montototal)as monto
                                                                            from tbl_transaccionespg t
                                                                            join tbl_usuariosgrupos ug ON fk_usuariogrupo = pk_usuariogrupo
                                                                            where fk_atributo = 3
                                                                            and fk_usuario = tu.pk_usuario
                                                                            and numerocontrol = pg.numerocontrol
                                                                            group by fk_usuario,numerocontrol
                                                                            )as mont) end as dif,
                                              (case when row_number() over(PARTITION by pg.numerocontrol) > 1 then 'Deuda'
                                              else (select test.valor from (select a.valor from tbl_transaccionespg tpg JOIN tbl_usuariosgrupos ug ON fk_usuariogrupo = pk_usuariogrupo
                                                JOIN tbl_atributos a ON a.pk_atributo = tpg.fk_tipo
                                    where tpg.fk_atributo = 3
                                    and fk_usuario = tu.pk_usuario
                                    and tpg.numerocontrol = pg.numerocontrol
                                    group by valor)as test) end)as  tipo
                FROM tbl_transaccionespg    pg
                JOIN tbl_usuariosgrupos     tug    ON pg.fk_usuariogrupo    = tug.pk_usuariogrupo
                JOIN tbl_usuarios           tu     ON tu.pk_usuario         = tug.fk_usuario
                JOIN tbl_inscripciones      ti     ON ti.fk_usuariogrupo    = tug.pk_usuariogrupo  --se agrego para filtrar por sedes
                JOIN tbl_estructuras        te     ON te.pk_estructura      = ti.fk_estructura
                JOIN tbl_atributos          ta     ON ta.pk_atributo        = pg.fk_tipo
                WHERE pg.fk_atributo = 3  -- Transaccion APROBADA 
                AND ti.fk_estructura IN ({$sede})                 
		AND ti.pk_inscripcion = (SELECT pk_inscripcion
					FROM tbl_inscripciones i2
					WHERE i2.fk_usuariogrupo = tug.pk_usuariogrupo
					ORDER BY i2.fk_periodo DESC LIMIT 1 )         
                AND TO_DATE(TO_CHAR(pg.fechahora, 'YYYY-MM-DD'), 'YYYY-MM-DD') BETWEEN CAST ('{$fechaInicio}' AS DATE) AND CAST ('{$fechaFin}' AS DATE)
                ) AS SQT
                ORDER BY factura desc ;";
              // var_dump($SQL);die;
       $results = $this->_db->query($SQL);
       return $results->fetchAll();

    }

    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }
}

