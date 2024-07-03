<?php

class Models_DbTable_Nominareporte extends Zend_Db_Table {

    private $searchParams = array();
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');

         $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
   
  
   
 
   public function getReporte($fecha_ini,$fecha_fin,$estado,$banco,$contrato){
            $ini = ini_set("soap.wsdl_cache_enabled", "0");
            //var_dump($ini);die;
            $params = array();
               $level = array();
        $p = xml_parser_create();
        //var_dump($p);die;
            $parametro['Estado'] = $estado;
            $fecha_ini=  str_replace("-","/",$fecha_ini);
            $parametro['f_ini']=$fecha_ini;
            $fecha_fin=  str_replace("-","/",$fecha_fin);
            $parametro['f_fin']=$fecha_fin;
            $parametro['banco'] = $banco;
            $parametro['contrato'] = $contrato;
            //var_dump($parametro);exit;
            
            $soapClient = new SoapClient("http://192.168.6.8/ProfitConnect/index.asmx?WSDL", $parametro);
            //var_dump($soapClient);die;
            $soapResult = $soapClient->Obtener_Nomina2($parametro);
            //var_dump($soapResult);die;
            //el soapResult me trae una tabla en xml de los parametros de profit
            xml_parse_into_struct($p, $soapResult->Obtener_Nomina2Result, $vals, $index);
          
            $articulos = Array();
 
            for($i=0;$i<(count($index['CI']));$i++){
                //var_dump($index);die;
                $articulos['ci'][$i] = $vals[$index['CI'][$i]]['value'];
                $articulos['nombre'][$i] = $vals[$index['NOMBRES'][$i]]['value'];
                $articulos['cuenta'][$i] = $vals[$index['CTA_BANC'][$i]]['value'];
                $articulos['monto'][$i] = $vals[$index['MONTONETO'][$i]]['value'];

            }
                //var_dump($articulos);die;      
           return $articulos;

       }
       
      


    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }
   
    public function Delete_table(){
         $SQL = "DELETE FROM tbl_tmp_nominareportes";
      
        $this->_db->query($SQL);
       
    }


    public function Insert_table($cedula,$nombre,$cuenta,$monto){
        $SQL = "INSERT INTO tbl_tmp_nominareportes(
             cedula, nombre, cuenta, monto)
             VALUES ('$cedula', '$nombre','$cuenta','$monto');";
      
            $this->_db->query($SQL);
    }

  
}
?>