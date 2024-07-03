<?php

class Models_DbTable_Cuotas extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_cuotas';
    protected $_primary  = 'pk_cuota';
    protected $_sequence = 'tbl_cuotasperiodos_pk_cuotaperiodo_seq';


    public function getCostoPer($periodo,$sede,$ni){

    	$ni = ($ni ? "true" : "false");
        $SQL = "SELECT SUM(cp.costo) as monto
                from tbl_cuotasperiodos cp
                JOIN tbl_atributos atr ON atr.pk_atributo = cp.fk_cuota
                JOIN tbl_calendarios cal ON cal.fk_actividad = fk_cuota
                where cal.fk_periodo = {$periodo}
                  AND cp.fk_periodo = {$periodo}
                  AND cp.nuevoingreso = {$ni}
                  AND cp.fk_estructura = {$sede}
                  ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['monto'];

    }

    public function getMontoNoVencido($periodo,$sede,$ni){

    	$ni = ($ni ? "true" : "false");
        $SQL = "SELECT CASE WHEN monto is null THEN 0 ELSE monto END as monto
                FROM (
                select TRUNC(SUM(cp.costo),2) as monto
                from tbl_cuotasperiodos cp
                JOIN tbl_atributos atr ON atr.pk_atributo = cp.fk_cuota
                JOIN tbl_calendarios cal ON cal.fk_actividad = fk_cuota
                where cal.fk_periodo = $periodo
                  AND cp.fk_periodo = $periodo
                  AND cp.nuevoingreso = {$ni}
                  AND cp.fk_estructura = {$sede}
                  AND cal.fechainicio >= current_date) as sqt ";
       
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['monto'];
    }

    public function getMontoPrimeraCuota($periodo,$sede,$ni){

    	$ni = ($ni ? "true" : "false");
        $SQL = "SELECT cp.costo
                FROM tbl_cuotasperiodos cp 
                JOIN tbl_atributos a ON pk_atributo = fk_cuota
                WHERE fk_periodo = {$periodo}
                AND cp.fk_estructura = {$sede}
                AND fk_cuota = 17
                AND cp.nuevoingreso = {$ni}
                  ;";
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['costo'];
    }

    public function getNombreCuotaCorto($id){
        $SQL = "SELECT substring(valor for (position (' ' in valor)-1)) as shortname
                FROM tbl_atributos
                WHERE fk_atributo = 1692
                AND id = {$id}
                  ;";
                

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['shortname'];
    }

    public function getNombreCuota($id){
        $SQL = "SELECT substring(valor for (position (' ' in valor)-1)) as shortname
                FROM tbl_atributos
                WHERE fk_atributo = 1692
                AND id = {$id}
                  ;";
                

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results[0]['shortname'];
    }


   public function getNuevoIngreso()
   {

        $SQL = "SELECT DISTINCT CASE WHEN fk_estructura = 7 THEN 0 
                                     WHEN nuevoingreso = TRUE THEN 1
                                     WHEN nuevoingreso = FALSE THEN 2        
                                END AS pk_nuevoingreso,
                                CASE WHEN fk_estructura = 7 THEN 'Todos'
                                     WHEN nuevoingreso = TRUE THEN 'True'
                                     WHEN nuevoingreso = FALSE THEN 'False'
                                END AS nombre
                FROM tbl_cuotasperiodos tcp
                ORDER BY pk_nuevoingreso;";
                
        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getPeriodosCuotas($sede,$periodo,$nuevoingreso,$fk_cuota)
    {
       
        $SQL = "SELECT  nuevoingreso,costo
                FROM tbl_cuotasperiodos 
                WHERE fk_estructura = {$sede}
                AND fk_periodo = {$periodo}
                AND fk_cuota in ({$fk_cuota})
                AND nuevoingreso in ({$nuevoingreso}) 
                group by costo,nuevoingreso
                order by costo,nuevoingreso;";
        //var_dump($SQL);die;
            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();
              return $results;
    }

    public function getFkCuota()
    {
        $SQL = "SELECT fk_cuota
                FROM tbl_cuotasperiodos
                group by fk_cuota 
                order by fk_cuota;";

            $results = $this->_db->query($SQL);
            $results = $results->fetchAll();
              return $results;
    }

    public function getCostoCuotas($sede,$periodo,$nuevoingreso,$fk_cuota)
    {
        $SQL = "SELECT  costo
                FROM tbl_cuotasperiodos tcp 
                WHERE fk_estructura = {$sede}
                AND fk_periodo = {$periodo}
                AND fk_cuota in ({$fk_cuota})
                AND nuevoingreso in ({$nuevoingreso});";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();
          return $results;
    }

   public function insertPeriodosCuotasAll($periodo,$montocuota,$montocuotaNew,$montoinscri,$montoinscriNew,$sede)
    {
        $SQL = " INSERT INTO tbl_cuotasperiodos (
                                                fk_periodo,
                                                fk_cuota,
                                                costo,
                                                nuevoingreso,
                                                fk_estructura)

                 VALUES ({$periodo},17,{$montoinscriNew},true,{$sede}),
                        ({$periodo},1695,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1696,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1697,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1698,{$montocuotaNew},true,{$sede}),
                        ({$periodo},17,{$montoinscri},false,{$sede}),
                        ({$periodo},1695,{$montocuota},false,{$sede}),
                        ({$periodo},1696,{$montocuota},false,{$sede}),
                        ({$periodo},1697,{$montocuota},false,{$sede}),
                        ({$periodo},1698,{$montocuota},false,{$sede}); ";
        $this->_db->query($SQL);

    }

    public function insertPeriodosCuotasFalse($periodo,$montocuota,$montoinscri,$sede)
    {

        $SQL = " INSERT INTO tbl_cuotasperiodos (
                                                fk_periodo,
                                                fk_cuota,
                                                costo,
                                                nuevoingreso,
                                                fk_estructura)

                 VALUES ({$periodo},17,{$montoinscri},false,{$sede}),
                        ({$periodo},1695,{$montocuota},false,{$sede}),
                        ({$periodo},1696,{$montocuota},false,{$sede}),
                        ({$periodo},1697,{$montocuota},false,{$sede}),
                        ({$periodo},1698,{$montocuota},false,{$sede}); ";
        $this->_db->query($SQL);
    }

    public function insertPeriodosCuotasTrue($periodo,$montocuotaNew,$montoinscriNew,$sede)
    {

        $SQL = " INSERT INTO tbl_cuotasperiodos (
                                                fk_periodo,
                                                fk_cuota,
                                                costo,
                                                nuevoingreso,
                                                fk_estructura)

                 VALUES ({$periodo},17,{$montoinscriNew},true,{$sede}),
                        ({$periodo},1695,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1696,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1697,{$montocuotaNew},true,{$sede}),
                        ({$periodo},1698,{$montocuotaNew},true,{$sede}); ";

        $this->_db->query($SQL);
    }


    public function getPkCuotaPeriodo()
    {    
       $SQL = " SELECT MAX(pk_cuotaperiodo)
        FROM tbl_cuotasperiodos; ";

       return $this->_db->fetchOne($SQL);
    }    

    public function updatePeriodosCuotas($periodo,$montocuota,$sede, $montoinscri)
    {    
            $SQL = "    UPDATE  tbl_cuotasperiodos 
                        SET     costo = {$montocuota}
                        WHERE   fk_periodo in ({$periodo})
                        AND     fk_cuota in (1695,1695,1696,1697,1698)
                        AND     nuevoingreso = false
                        AND     fk_estructura in ({$sede});";
            $this->_db->query($SQL);

            $SQL2 = "   UPDATE  tbl_cuotasperiodos 
                        SET     costo = {$montoinscri}
                        WHERE   fk_periodo in ({$periodo})
                        AND     fk_cuota in (17)
                        AND     nuevoingreso = false
                        AND     fk_estructura in ({$sede});
                        ";
        $this->_db->query($SQL2);
    }    

    public function updatePeriodosCuotasNI($periodo,$montocuotaNew,$sede, $montoinscriNew)
    {    
            $SQL = "   UPDATE  tbl_cuotasperiodos 
                        SET     costo = {$montocuotaNew}
                        WHERE   fk_periodo in ({$periodo})
                        AND     fk_cuota in (1695,1695,1696,1697,1698)
                        AND     nuevoingreso = true
                        AND     fk_estructura in ({$sede});";
            $this->_db->query($SQL);

            $SQL2 =    "UPDATE  tbl_cuotasperiodos 
                        SET     costo = {$montoinscriNew}
                        WHERE   fk_periodo in ({$periodo})
                        AND     fk_cuota in (17)
                        AND     nuevoingreso = true
                        AND     fk_estructura in ({$sede})";
        $this->_db->query($SQL2);
    }  

}

?>
