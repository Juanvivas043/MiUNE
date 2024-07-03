<?php

/**
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Soap {
    private $URL = null;
    
    public function __construct() {
        ini_set("soap.wsdl_cache_enabled", "0");
        ini_set('display_errors', false);
        
        $this->SwapBytes_String = new SwapBytes_String();
    }

    /**
     * Define el URL del WebServices que se estara ejecutando.
     *
     * @param string $URL
     */
    public function setUrl($URL) {
        $this->URL = $URL;
    }

    /**
     * Obtiene un archivo desde un WebServices para ser descargado, utilizando
     * el protocolo de SOAP, y estara coficado en hexadecimal durante su
     * transportación.
     *
     * @param string $Name
     * @param array  $Params
     * @return string
     */
    public function getFile($Name, $Params) {
        $Type = strtolower($Params['FileType']);
        
        try {
            $soapClient   = new SoapClient($this->URL, array('trace' => true));
            $soapResponse = $soapClient->generate($Params);

            header("Content-type:application/{$Type}");
            header("Content-Disposition:filename={$Name}.{$Type}");

            $f = $soapResponse->return;
            $f = $this->SwapBytes_String->hexToStr($f);
            echo $f;
            exit(0);
        } catch (Exception $ex) {
            return "<br>En estos momentos el servicio web de reportes no se encuentra disponible, intente mas tarde.";
        }
    }
}
?>