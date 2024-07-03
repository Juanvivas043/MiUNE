<?php
class Models_DbTable_Profit extends Zend_Db_Table
{

    private $connect;
    private $select;
    private $close;

    // ES NECESARIO TENER INSTALADO EL DRIVER , en ubuntu se llama php5.6-sybase ÂÂIMPORTANTE!! o la version del php

    public function init()
    {
        //$this->host = ' ';
        $this->host = '1234';
        $this->username = '';
        $this->password = '';
        $this->db = 'UNE_A';
        $this->diferenciaPeriodo = 86;
    }

    public function VerificarPago($cedula, $tipo)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;

        $parametro['Tipo'] = "UNE%" . $tipo . "%";

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->VerificarPagoArt($parametro);
        xml_parse_into_struct($p, $soapResult->VerificarPagoArtResult, $vals, $index);

        if (!isset($vals[$index['NROPAGO'][0]]['value'])) {
            return null;
        }

//        return array($vals, $index, "$tipo_b"."$tipo_a"."$tipo%");
        return $vals[$index['NROPAGO'][0]]['value'];
    }

    public function VerificarPagoCompetencia($cedula, $tipo)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        $parametro['Tipo'] = $tipo;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->VerificarPagoArt($parametro);
        xml_parse_into_struct($p, $soapResult->VerificarPagoArtResult, $vals, $index);

        if (!isset($vals[$index['NROPAGO'][0]]['value'])) {
            return null;
        }

//        return array($vals, $index, "$tipo_b"."$tipo_a"."$tipo%");
        return array($vals[$index['NROPAGO'][0]]['value'], $vals[$index['MONTONETO'][0]]['value']);
    }

    public function VerificarPagoTesis($cedula, $tipo)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        $parametro['Tipo'] = $tipo;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
                $soapResult = $soapClient->VerificarPagoArt($parametro);
        xml_parse_into_struct($p, $soapResult->VerificarPagoArtResult, $vals, $index);

        if (!isset($vals[$index['NROPAGO'][0]]['value'])) {
            return null;
        }
        $uc = 0;
        //Buscando la sumatoria de UC comprados del periodo actual $uc
        foreach ($index['TOTAL_ART'] as $key => $value) {
            # code...
            foreach ($vals[$value] as $key => $value) {
                if ($key == 'value') {
                    $uc += $value;
                }
            }

        }

        // {
        # code...

//        return array($vals, $index, "$tipo_b"."$tipo_a"."$tipo%");
        return array($vals[$index['NROPAGO'][0]]['value'], $vals[$index['MONTONETO'][0]]['value'], intval($uc));
    }

    public function VerificarPagoReins($cedula, $tipo, $sede, $tipo_b = 'UNE', $tipo_a = 'LN')
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        if ($sede == 8) {
            $tipo_a = 'CENTRO';
            $tipo = $tipo - $this->diferenciaPeriodo;
        }
        $parametro['Tipo'] = "$tipo_b" . "$tipo_a" . "$tipo%";
        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->VerificarPagoArt($parametro);
        xml_parse_into_struct($p, $soapResult->VerificarPagoArtResult, $vals, $index);

        if (!isset($vals[$index['NROPAGO'][0]]['value'])) {
            return null;
        }

        return array($vals, $index, "$tipo_b" . "$tipo_a" . "$tipo%");
//        return $vals[$index['NROPAGO'][0]]['value'];
    }

    public function ComprobarBecado($cedula, $periodo, $sede)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;

        if ($sede == 8) {
            $periodo = $periodo - $this->diferenciaPeriodo;
            $tipo_a = "UNECENTRO" . $periodo;

        } else {
            $tipo_a = "UNELN" . $periodo;
        }

        $parametro['Tipo'] = "$tipo_a" . "BECA%";

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->VerificarBecado($parametro);
        xml_parse_into_struct($p, $soapResult->VerificarBecadoResult, $vals, $index);

        if (!isset($vals[$index['FACTURADO'][0]]['value'])) {
            return null;
        } else {

            return $vals[$index['FACTURADO'][0]]['value'];

        }

    }

    public function VerArticulosPago($cedula, $pago)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        $parametro['Pago'] = $pago;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->ArticulosPago($parametro);
        xml_parse_into_struct($p, $soapResult->ArticulosPagoResult, $vals, $index);

        $articulos = array("articulo");

        for ($i = 0; $i < (count($index['CI'])); $i++) {
            $articulos['articulo'][$i] = $vals[$index['CO_ART'][$i]]['value'];

        }

        return ($articulos);

    }

    public function VerEstacioanmientoPago($cedula, $inicio)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        $parametro['inicio'] = $inicio;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->EstacionamientoPago($parametro);
        xml_parse_into_struct($p, $soapResult->EstacionamientoPagoResult, $vals, $index);

        $articulos = array("articulo");

        for ($i = 0; $i < (count($index['CI'])); $i++) {
            $articulos['articulo'][$i] = $vals[$index['CO_ART'][$i]]['value'];

        }

        return ($articulos);

    }

    public function VerFechaArticulo($cedula, $pago)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['Cedula'] = $cedula;
        $parametro['Pago'] = $pago;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->ArticulosPago($parametro);
        xml_parse_into_struct($p, $soapResult->ArticulosPagoResult, $vals, $index);

        //$articulos = Array("articulo");

        //for($i=0;$i<(count($index['CI']));$i++){
        //$articulos['fecha'][0] = $vals[$index['FECHAEMISION'][0]]['value'];
        $articulos = $vals[$index['FECHAEMISION'][0]]['value'];

        //}

        return ($articulos);

    }

    public function verReciboDePago($cedula)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['ci'] = $cedula;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->ObtenerRecibosPagos($parametro);
        xml_parse_into_struct($p, $soapResult->ObtenerRecibosPagosResult, $vals, $index);

        $articulos = array();

        for ($i = 0; $i < (count($index['RECI_NUM'])); $i++) {
            $articulos[$i]['valor'] = $vals[$index['RECI_NUM'][$i]]['value'];
            $articulos[$i]['display'] = $vals[$index['RECI_NUM'][$i]]['value'] . ' - ' . $vals[$index['FECHA'][$i]]['value'];
        }

        return $articulos;

    }

    public function verReciboDePagoEspecifico($cedula, $recibo)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['ci'] = $cedula;
        $parametro['recibo'] = $recibo;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->ObtenerReciboEspecifico($parametro);
        xml_parse_into_struct($p, $soapResult->ObtenerReciboEspecificoResult, $vals, $index);
        //echo $soapResult->ObtenerReciboEspecificoResult;

        $ruta = APPLICATION_PATH . '/../public/tempxml/';
        $archivo = $ruta . $cedula . ".xml";
        //$archivo = $ruta . "prueba.xml";
        $xml = "<?xml version='1.0' encoding='UTF-8' ?>" . $soapResult->ObtenerReciboEspecificoResult;

        //echo $archivo;
        file_put_contents($archivo, $xml);

        $articulos = array();

        for ($i = 0; $i < (count($index['RECI_NUM'])); $i++) {
            $articulos[$i]['recibo'] = trim($vals[$index['RECI_NUM'][$i]]['value']);
            $articulos[$i]['horas'] = $vals[$index['AUXI_CHA'][$i]]['value'];
            $articulos[$i]['desde'] = $vals[$index['FEC_INIC'][$i]]['value'];
            $articulos[$i]['hasta'] = $vals[$index['FEC_EMIS'][$i]]['value'];
            $articulos[$i]['cod_concepto'] = trim($vals[$index['CO_CONCE'][$i]]['value']);
            $articulos[$i]['asignacion'] = $vals[$index['ASIGNACION'][$i]]['value'] == 0 ? ' ' : $vals[$index['ASIGNACION'][$i]]['value'];
            $articulos[$i]['deduccion'] = $vals[$index['DEDUCCION'][$i]]['value'] == 0 ? ' ' : $vals[$index['DEDUCCION'][$i]]['value'];
            $deduc_total = $deduc_total + $vals[$index['DEDUCCION'][$i]]['value'];
            $asig_total = $asig_total + $vals[$index['ASIGNACION'][$i]]['value'];
            $articulos[$i]['des_concepto'] = $vals[$index['DES_CONCE'][$i]]['value'];
            $articulos[$i]['tipo'] = $vals[$index['TIPO'][$i]]['value'];
            $articulos[$i]['des_contrato'] = $vals[$index['DES_CARGO'][$i]]['value'];

            // $articulos[$i]['display'] = $vals[$index['RECI_NUM'][$i]]['value'] . ' - ' . $vals[$index['FECHA'][$i]]['value'];
        }
        $articulos[count($index['RECI_NUM']) + 1]['total_ded'] = $deduc_total;
        $articulos[count($index['RECI_NUM']) + 1]['total_acu'] = $asig_total;
        $articulos[count($index['RECI_NUM']) + 1]['neto'] = $asig_total + $deduc_total;

        return $articulos;

    }

    public function VerSaldoEstudiante($cedula)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['cedula'] = $cedula;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->SaldoEstudante($parametro);
        xml_parse_into_struct($p, $soapResult->SaldoEstudanteResult, $vals, $index);

        return $vals[$index['SALDO'][1]]['value'];

    }

    public function InfoEstudiante($cedula)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $parametro['cedula'] = $cedula;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->InfoEstudante($parametro);
        xml_parse_into_struct($p, $soapResult->InfoEstudanteResult, $vals, $index);

        $info['nombre'] = $vals[2]['value'];
        $info['email'] = $vals[4]['value'];
        $info['direccion'] = $vals[6]['value'];

        return $info;

    }

    public function VerEstadoDeCuentaEstudiante($cedula, $npago)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();
        $parametro['Cedula'] = $cedula;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);
        //$soapResult = $soapClient->EstadoDeCuentaTest($parametro);
        xml_parse_into_struct($p, $soapResult->EstadoDeCuentaTestResult, $vals, $index);

        $estado = array("CI", "FECHAEMISION", "NROPAGO", "VENFACTCAN", "MONTOC", "MONTONETO", "TIPO", "NOMBRECLIENTE", "SEDE", "OBSERVACION");

        for ($i = 0; $i <= ($n = count($index['CI'])); $i++) {
            $estado['CI'][$i] = $vals[$index['CI'][$i]]['value'];
            $estado['FECHAEMISION'][$i] = $vals[$index['FECHAEMISION'][$i]]['value'];
            $estado['NROPAGO'][$i] = $vals[$index['NROPAGO'][$i]]['value'];
            $estado['VENFACTCAN'][$i] = $vals[$index['VENFACTCAN'][$i]]['value'];
            $estado['MONTOC'][$i] = $vals[$index['MONTOC'][$i]]['value'];
            $estado['MONTONETO'][$i] = $vals[$index['MONTONETO'][$i]]['value'];
            $estado['TIPO'][$i] = $vals[$index['TIPO'][$i]]['value'];
            $estado['NOMBRECLIENTE'][$i] = $vals[$index['NOMBRECLIENTE'][$i]]['value'];
            $estado['SEDE'][$i] = $vals[$index['SEDE'][$i]]['value'];
            $estado['TIPODOCUMENTO'][$i] = $vals[$index['TIPODOCUMENTO'][$i]]['value'];
            $estado['OBSERVACION'][$i] = $vals[$index['OBSERVACION'][$i]]['value'];
        }

        $nfactura = 0;
        for ($i = 0; $i <= ($n = count($index['CI'])); $i++) {
            list($tipo_f, $numero_f) = explode(":", $estado['VENFACTCAN'][$i]);
            if ($estado['NROPAGO'][$i] == $npago && !empty($numero_f) && $numero_f > $nfactura) {
                $nfactura = $numero_f;
            }
        }

        for ($i = 0; $i < ($n = count($index['CI'])); $i++) {
            if ($estado['TIPO'][$i] == 1 || $estado['TIPO'][$i] == 5) {
                $tipo = 'Cobro';
            }

            if ($estado['TIPO'][$i] == 2) {
                $tipo = 'Factura';
            }

            if ($estado['TIPO'][$i] == 3) {
                $tipo = 'Nota de CrÃ©dito';
            }

            if ($estado['TIPO'][$i] == 4) {
                $tipo = 'Ajuste Positivo';
            }

            list($tipo_f, $numero_f) = explode(":", $estado['VENFACTCAN'][$i]);

            if (($estado['TIPO'][$i] == 2 || $estado['TIPO'][$i] == 4 || $estado['TIPO'][$i] == 5) && $estado['NROPAGO'][$i] == $nfactura) {
                $debetotal += $estado['MONTONETO'][$i];
                $saldo = ($debetotal - $habertotal);
            }
            if (($estado['TIPO'][$i] == 1 || $estado['TIPO'][$i] == 3) && ($tipo_f == 'FACT' && $numero_f == $nfactura)) {
                $habertotal += $estado['MONTONETO'][$i];
                $saldo = ($debetotal - $habertotal);
            }
        }
        return $saldo;
    }

    public function getFechadePagoNomina()
    {

        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        $articulos = array();

        try {
            //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL");

            //$soapResult = $soapClient->ObtenerFechasNominas();
            xml_parse_into_struct($p, $soapResult->ObtenerFechasNominasResult, $vals, $index);

            $fechas = (Array) simplexml_load_string($soapResult->ObtenerFechasNominasResult);
            $encoded = json_encode($fechas["Table"]);
            $fechasFinales = json_decode($encoded, true);

            for ($i = 0; $i < (count($fechasFinales)); $i++) {
                $articulos[$i]['valor'] = $fechasFinales[$i]["fec_inic"] . " " . $fechasFinales[$i]["fec_emis"] . " " . $fechasFinales[$i]["corre"];
                $articulos[$i]['display'] = $fechasFinales[$i]["fec_inic"] . " - " . $fechasFinales[$i]["fec_emis"];
            }
        } catch (Exception $e) {
            $articulos = true;
        }
        return $articulos;
    }

    public function getDatosNomina($fecha_inic, $fecha_emis)
    {
        $ini = ini_set("soap.wsdl_cache_enabled", "0");
        $params = array();
        $level = array();
        $p = xml_parser_create();

        //Aqui pongo la fecha en formato YDM para que lo procese el Profit (Victor S)
        $inic = explode("-", $fecha_inic);
        $fecha_inic = $inic[0] . '-' . $inic[2] . '-' . $inic[1];
        $emis = explode("-", $fecha_emis);
        $fecha_emis = $emis[0] . '-' . $emis[2] . '-' . $emis[1];

        $parametro['fec_inic'] = $fecha_inic;
        $parametro['fec_emis'] = $fecha_emis;

        //$soapClient = new SoapClient("http://" . ' ' . "/ProfitConnect/index.asmx?WSDL", $parametro);

        //$soapResult = $soapClient->ObtenerNomina($parametro);
        xml_parse_into_struct($p, $soapResult->ObtenerNominaResult, $vals, $index);

        $rows = (Array) simplexml_load_string($soapResult->ObtenerNominaResult);
        $encoded = json_encode($rows["Table"]);
        $rowsFinales = json_decode($encoded, true);
        if (empty($rowsFinales[0]["cta_banc"])) {
            $rowsTemp[0] = $rowsFinales;
            $rowsFinales = $rowsTemp;
        }
        //var_dump($rowsFinales);die;
        return $rowsFinales;
    }

    /**
     * Saldo del estudiante en profit usando la nueva conexion
     * @param $cedula
     */
    public function getSaldoEstudiante($cedula)
    {

        $SQL = "SELECT LTRIM(RTRIM(COALESCE(Saldo,0))) as Saldo
                FROM fn_saldo_de_estudiante('{$cedula}');";
        $results = $this->fetchAllRows($SQL);
        return $results[0]["Saldo"];

    }
    public function getSaldoUltimaFacturaPendiente($cedula)
    {

        $SQL = "SELECT * FROM getSaldoPendienteUltimaFactura('{$cedula}');";
        $results = $this->fetchAllRows($SQL);
        return $results[0];

    }

    public function getFacturasPendientes($cedula)
    {
        $SQL = "SELECT  co_us_mo, nro_doc, monto_net,saldo,observa
				FROM docum_cc
				WHERE   co_cli = '{$cedula}' AND saldo > 0 AND tipo_doc = 'FACT'";

        $results = $this->fetchAllRows($SQL);
        return $results;

    }

    public function getArticulosByPeriodo($periodo, $sede)
    {
        if ($sede == "Los Naranjos") {
            $particula = "LN";
        } elseif ($sede == "Sede Centro") {
            $particula = "CENTRO";
            $periodo = $periodo - $this->diferenciaPeriodo;
        };

        $SQL = "SELECT co_art,art_des,prec_vta1 as monto
				FROM art
				WHERE co_art like 'UNE{$particula}{$periodo}' OR co_art like '2SEGURO'
				ORDER BY 1";

        $results = $this->fetchAllRows($SQL);
        return $results;
    }

    public function realizarCobro($cedula, $monto, $sede, $lot_ref, $periodo)
    {
        $SQL = "DECLARE @return_value int
            EXEC  @return_value = [dbo].[cobrar]
            @p1 = N'{$cedula}',
            @p2 = N'{$monto}',
            @p3 = N'{$sede}',
            @p4 = N'{$lot_ref}',
            @p5 = N'{$periodo}';
        SELECT  'Return Value' = @return_value;";
        $result = $this->fetchOneRow($SQL);
        //Retorna el cob_num que se acaba de realizar.
        return $result;
    }

    public function realizarCobroByFact($cedula, $monto, $sede, $lot_ref, $periodo, $num_fact)
    {
        $SQL = "DECLARE @return_value int
            EXEC  @return_value = [dbo].[cobrar]
            @p1 = N'{$cedula}',
            @p2 = N'{$monto}',
            @p3 = N'{$sede}',
            @p4 = N'{$lot_ref}',
            @p5 = N'{$periodo}',
            @p6 = N'{$num_fact}';
        SELECT  'Return Value' = @return_value;";

        $result = $this->fetchOneRow($SQL);
        //Retorna el cob_num que se acaba de realizar.
        return $result;
    }

    public function realizarFactura($cedula, $sede, $periodo, $desc)
    {
        if ($desc == true) {
            $descuento = 1;
        } else {
            $descuento = 0;
        }
        $SQL = "DECLARE @return_value int
            EXEC  @return_value = [dbo].[facturar]
            @p1 = N'{$cedula}',
            @p2 = N'{$sede}',
            @p3 = N'{$periodo}',
            @p4 = N'{$descuento}';
        SELECT  'Return Value' = @return_value;";

        $result = $this->fetchOneRow($SQL);
        //Retorna el num_factura que se acaba de realizar.
        return $result;
    }

    public function getCobNum($ci, $periodo)
    {
        $SQL = "SELECT cob_num
              FROM   reng_cob rc
              JOIN   docum_cc doc ON rc.doc_num = doc.nro_doc
              JOIN fact_reng fr ON fr.fact_num = doc.nro_doc
              where fr.co_cli = '{$ci}'
              and (co_art LIKE 'UNELN{$periodo}%' OR co_art LIKE 'CENTRO{$periodo}%');";

        $result = $this->fetchOneRow($SQL);
        return $result;
    }

    public function getUc($ci, $periodo, $date)
    {
        $periodoCn = $periodo - $this->diferenciaPeriodo;
        $SQL = "SELECT sum(total_art)
              FROM reng_cob rc
              JOIN docum_cc doc ON rc.doc_num = doc.nro_doc
              JOIN fact_reng fr ON fr.fact_num = doc.nro_doc
              WHERE fr.co_cli = '{$ci}'
              AND (co_art = 'UCLN{$periodo}' OR co_art = 'UCCENTRO{$periodoCn}'
                    OR (co_art = 'UC' AND fr.fec_emis BETWEEN '{$date}' AND current_timestamp)
                    OR co_art = 'UC{$periodo}');";
        $result = $this->fetchOneRow($SQL);
        return $result;

    }
    public function getEstudiantesBecados($ci, $periodo)
    {
        $periodocentro = $periodo - 86;
        $SQL = "SELECT DISTINCT  co_art
             FROM   dbo.clientes,
                    dbo.cobros  ,
                    dbo.reng_cob,
                    dbo.docum_cc
             JOIN   dbo.Fact_reng fr ON fr.fact_num = docum_cc.nro_doc
             WHERE  cobros.cob_num      = reng_cob.cob_num
             AND    reng_cob.doc_num    = docum_cc.nro_doc
             AND    reng_cob.tp_doc_cob = docum_cc.tipo_doc
             AND    (cobros.adel_num = 0 OR(
                                             cobros.adel_num  != 0 AND  docum_cc.tipo_doc = 'ISLR'
                                           )
                     )
             AND    docum_cc.co_cli = '{$ci}'
             AND    cobros.fec_cob BETWEEN '2004-01-01 00:00:00' AND  current_timestamp 
            AND    cobros.anulado = 0
             AND    (co_art LIKE '%LN{$periodo}BECA%' OR co_art LIKE '%CENTRO{$periodocentro}BECA%');";
        //var_dump($SQL);die;
        // Se cambuo la fecha de Jan 1 2004 12:00:00:000AM a la de arriba
        $results = $this->fetchAllRows($SQL);
        return $results;

    }

    public function getDatosCuentaEstudiante($ci)
    {
        $SQL = "SELECT *
            FROM fn_estado_de_cuenta_estudiante_test({$ci})
            ORDER BY fec_emis ASc, fe_us_in ASC,Orden DESC, NroPago DESC, VenFactCan ASC;";

        $results = $this->fetchAllRows($SQL);

        return $results;

    }

    public function getBecaEstudiantes($periodo)
    {

        $sede1 = "LN";
        $sede2 = "CENTRO";
        $periodo1 = $periodo;
        $periodo2 = $periodo - $this->diferenciaPeriodo;
        $SQL = "SELECT DISTINCT  clientes.co_cli,co_art
             FROM   dbo.clientes,
                    dbo.cobros  ,
                    dbo.reng_cob,
                    dbo.docum_cc
             JOIN   dbo.Fact_reng fr ON fr.fact_num = docum_cc.nro_doc
             WHERE  cobros.cob_num      = reng_cob.cob_num
             AND    reng_cob.doc_num    = docum_cc.nro_doc
             AND    reng_cob.tp_doc_cob = docum_cc.tipo_doc
             AND    (cobros.adel_num = 0 OR(
                                             cobros.adel_num  != 0 AND  docum_cc.tipo_doc = 'ISLR'
                                           )
                     )
             AND    docum_cc.co_cli = clientes.co_cli
             AND    cobros.fec_cob BETWEEN '2004-01-01 00:00:00' AND  current_timestamp
             AND    cobros.anulado = 0
             AND    (co_art LIKE '%{$sede1}{$periodo1}%BECA%' OR co_art LIKE '%{$sede2}{$periodo2}%BECA%' OR co_art LIKE '%{$sede2}{$periodo2}%FUNDA%' OR co_art LIKE '%{$sede1}{$periodo1}%FUNDA%')
             ORDER by 1
             ";
        //var_dump($SQL);die;

        $results = $this->fetchAllRows($SQL);
        return $results;

    }
    public function getCostoSeguro()
    {
        $SQL = "SELECT CAST(prec_vta1 as float)as '2SEGURO'
		FROM art
		WHERE co_art='2SEGURO';";
        $result = $this->fetchOneRow($SQL);
        return $result;

    }

    public function getNumCob($ci, $periodo)
    {

        $periodo2 = $periodo - $this->diferenciaPeriodo = 86;

        $SQL = "SELECT c.cob_num
            FROM docum_cc dc
            JOIN factura f ON f.fact_num = dc.nro_doc
            JOIN reng_fac rf ON rf.fact_num = f.fact_num
            JOIN reng_cob rc ON rc.doc_num = dc.nro_doc
            JOIN cobros c ON c.cob_num = rc.cob_num
            WHERE f.co_cli = '{$ci}'
            AND (co_art LIKE '%UNELN{$periodo}%' OR co_art LIKE '%UNECENTRO{$periodo2}%');";

        $results = $this->fetchAllRows($SQL);
        return $results;
    }

    private function fetchAllRows($query)
    {

        // Connect to DB
        //var_dump(mssql_connect($this->host,$this->username,$this->password));die;
        //$this->connect = @mssql_connect($this->host, $this->username, $this->password) or die($this->error_msg("Error with connect 2"));
        //$this->connect = @sqlsrv_connect($this->host, ["Database"=>$this->db, "UID"=>$this->username, "PWD"=>$this->password]) or die($this->error_msg("Error with connect 2"));
        //Select DB
        // $this->select = @mssql_select_db($this->db) or die($this->error_msg("Error with select database"));
        // Realiza la consulta
        //$results = @mssql_query($query) or die($this->error_msg("Error with query"));
        //$results = @sqlsrv_query($this->connect, $query) or die($this->error_msg("Error with query"));

        $rows = [];
        // Transformamos ese objeto a un array legible recorriendo cada row
        //while ($array_temp = @mssql_fetch_array($results)) {
        while ($array_temp = @sqlsrv_fetch_array($results)) {
            foreach ($array_temp as $key => $value) {
                if (is_numeric($key)) {
                    unset($array_temp[$key]);
                }

            }
            $rows[] = $array_temp;
        }

        return $rows;
    }

    private function fetchOneRow($query)
    {

        // Connect to DB
        //die(var_dump($this->host, $this->username, $this->password));
        // $this->connect = @mssql_connect($this->host, $this->username, $this->password) or die($this->error_msg("Error with connect"));
        //$this->connect = @sqlsrv_connect($this->host, ["Database"=>$this->db, "UID"=>$this->username, "PWD"=>$this->password]) or die($this->error_msg("Error with connect"));
        //Select DB
        // $this->select = @mssql_select_db($this->db) or die($this->error_msg("Error with select database"));
        // Realiza la consulta
        //$results = @mssql_query($query) or die($this->error_msg("Error with query"));
        //$results = @sqlsrv_query($this->connect, $query) or die($this->error_msg("Error with query"));

        //$row = @mssql_fetch_array($results);
        //$row = @sqlsrv_fetch_array($results);
        return $row[0];

    }

    public function sql_rows_num($query)
    {

        // $rows_num = @mssql_num_rows($query);
        // $this->close = @mssql_close() or die($this->error_msg("Error with close connect"));
        // return $rows_num;

    }

    public function error_msg($msg)
    {
        echo $msg;
        die;
    }

}