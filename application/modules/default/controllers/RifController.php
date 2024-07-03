<?php

class RifController extends Zend_Controller_Action {
    public function init() {
        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
    }
    /*
    function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
    }
    */
    public function indexAction() {
        $json_array = array();
        $base_url   = "http://contribuyente.seniat.gob.ve/getContribuyente/getrif?rif=";
        $this->SwapBytes_Ajax->setHeader();
        // Data
        $rif      = $_GET['rif'];
        $tipo_rif = $_GET['tipo_rif'];
        $length   = 9 - strlen($rif);
        if($length > 0){
            $zero = "";
            for ($i = 0; $i < $length; $i++) { 
                $zero .= "0";
            }
        }
        $rif = $tipo_rif.$zero.$rif;
        //Consultamos informacion del RIF
        $url = $base_url . $rif;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $result = curl_exec($ch);
        curl_close($ch);
        //Make Return Data
        if($result){
            //convierto el xml (html) en un objeto
            $dom = new DOMDocument();
            $dom->loadHTML($result);
            $res = simplexml_import_dom($dom);
            $main = $res->body->rif;
            $main = (array) $main;
            $data['rif']                = $main["@attributes"]["rif:numerorif"];
            $data['razonsocial']        = $main["nombre"];
            $data['agenteretencioniva'] = $main["agenteretencioniva"];
            $data['contribuyenteiva']   = $main['contribuyenteiva'];
            $data['tasa']               = $main["tasa"];
        }
        if(!empty($data)){
            $json_array[] = true;
            $json_array[] = $data['rif'];
            $json_array[] = $data['razonsocial'];
            echo Zend_Json::encode($json_array);
        }
        else {
            $json_array[] = false; $json_array[] = null;  $json_array[] = null;
            echo Zend_Json::encode($json_array); 
        }
    }
}

?>