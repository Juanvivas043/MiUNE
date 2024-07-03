<?php

/**
 * Clase creada para el manejo de la conexion hacia el boton de pago de Megasoft usando Curl
 Esta libreria requiere tener instaladas las librerias: php5-curl,
 *
 * @category Une
 * @package  Une_Payment
 * @author  Jerry Martinez
 */
class Une_Payment { 


    /**
     * funcion creada para lidiar con la restriccion del puerto 8443 en Omicron, 14200 realizara todas las peticiones
     * a traves de esta funcion
     * @param $cuotas
     * @param $host
     * @return mixed|string
     * 
     * 
     */

    private $isProduccion ;
    private $url_base;
    private $host_dev = "http://192.168.14.70/MiUNE2/transactions/pagocuotas/";

    public function __construct($isProduccion){

        $this->isProduccion = $isProduccion;
        if($isProduccion){
            $this->url_base = "https://e-payment.megasoft.com.ve/payment/action/";
	    $this->codigo_afiliacion = "30174387301";
	    $this->password = "Fedora123!";
	    $this->user = "miune1";
        }else{
	    $this->codigo_afiliacion = "2015070701";
            $this->url_base = "https://200.71.151.226:8443/payment/action/";
	    $this->password = "Fedora123!";
	    $this->user = "nuevaesparta";
        }

    }
    function conexNoSecure($query){

        $url = $host_dev.$query;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //Dirección URL a capturar
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $result = curl_exec($ch);

            if ( curl_errno($ch) ) {
                $result = 'ERROR ->> ' . curl_errno($ch) . ': ' . curl_error($ch);
            } else {

                $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                switch($returnCode){
                    case 200:
                        break;
                    default:
                        $result = 'HTTP ERROR -> ' . $returnCode;
                        break;
                }
            }

        curl_close($ch);
        return $result;
    }

    /**
     * @param $url
     * @return mixed|string
     */
    function login($url){

        $password = $this->password; 
        $user =  $this->user;
        $ch = curl_init(); 

        curl_setopt($ch, CURLOPT_URL, $url); //Dirección URL a capturar
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$password);//Nombre de usuario 
                                                                   //y contraseña
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); 
        curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_BASIC); //método de autenticación 
                                                           //HTTP

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //verificación peer 
                                                         //del certificado

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //devuelve el resultado de la 
                                                       //transferencia como string del 
                                                       //valor de curl_exec() en lugar 
                                                       //de mostrarlo directamente

        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // máximo de segundos permitido para 
                                               //ejectuar funciones cURL

        $result = curl_exec($ch);

            if ( curl_errno($ch) ) {
                $result = 'ERROR -1> ' . curl_errno($ch) . ': ' . curl_error($ch);
            } else {

                $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                switch($returnCode){
                    case 200:
                        break;
                    default:
                        $result = 'HTTP ERROR -> ' . $returnCode;
                        break;
                }
            }
        curl_close($ch);
        return $result;
    }

    /**
     * @param $monto
     * @return mixed|string
     */
    function preRegistro($monto,$factura){

        $monto = number_format($monto,2,".","");

        $url = $this->url_base . "paymentgatewayuniversal-prereg?cod_afiliacion=". $this->codigo_afiliacion ."&factura={$factura}&monto={$monto}";
        return $this->login($url);
        
    }

    /**
     * @param $numeroControl
     */
    public function redireccionar($numeroControl){

        $url= $this->url_base ."paymentgatewayuniversal-data?control=$numeroControl";

        header("Location: $url");

        die;
        
    }

    /**
     * @param $numeroControl
     * @return mixed|string
     */
    public function getStatus($numeroControl){

        $url = $this->url_base . "paymentgatewayuniversal-querystatus?control=$numeroControl";
        return $this->login($url);

    }

    public function getPreStatus($numeroControl,$host = "localhost"){

        $url = "http://{$host}/MiUNE2/transactions/pagocuotas/getstatus?numerocontrol={$numeroControl}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //Dirección URL a capturar
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        $result = curl_exec($ch);

        if ( curl_errno($ch) ) {
            $result = 'ERROR ->> ' . curl_errno($ch) . ': ' . curl_error($ch);
        } else {

            $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch($returnCode){
                case 200:
                    break;
                default:
                    $result = 'HTTP ERROR -> ' . $returnCode;
                    break;
            }
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @param $response
     * @return bool
     */
    public function checkConection($response){
        $response = split(" ",$response);
        if($response[0] == "HTTP" && $response[1] == "ERROR"  || $response[0] == "ERROR"){
            return false;
        }else{
            return true;
        }

    }
}
