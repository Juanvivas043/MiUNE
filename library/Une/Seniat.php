<?php

/**
*
* Clase que permite manipular cierto numero para transformarlo en un Rif valido
* ademas de retornar informacion del seniat del mismo Rif
*
* @category Une
* @package Une_Seniat
* @version 0.1
* @author Alton Bell-Smythe abellsmythe@gmail.com, Alan Manuitt alansmanuittb@gmail.com
*
*/

define('URL','http://contribuyente.seniat.gob.ve/getContribuyente/getrif?rif=');

class Une_Seniat {

	public  $array_rif = array('J','G','V','C');
	public  $conn 	   = null;
	private $host 	   = "192.168.14.200";
	private $user 	   = "adminrif";
	private $pass 	   = "adminrif";
	private $bd   	   = "rif";

	/**
     * Retorna el RIF correctamente armado
     *
     * @return string
     */
	public function setRif($rif,$tipo_rif){
		$length = 9 - strlen($rif);
        $zero = "";
        for ($i = 0; $i < $length; $i++) { 
            $zero .= "0";
        }
        $rif = $tipo_rif.$zero.$rif;

        return $rif;
	}

	/**
     * Retorna informacion de cierto RIF
     *
     * @return array
     */
	public function getRifInformation($rif,$tipo_rif) {
        if(strlen($rif) == 10){
            //consulto los datos del RIF en la BD Local
            //Vieja manera de conexion del SQL Server al servidor y BD
            //$this->conn = mysql_connect($this->host,$this->user,$this->pass);
            //mysql_select_db($this->bd,$this->conn);
            $this->conn = sqlsrv_connect($this->host, ["Database"=>$this->bd, "UID"=>$this->user, "PWD"=>$this->pass]);
            if($this->conn){
                $query  = "SELECT rif,razonsocial FROM rif WHERE rif = '$rif'";
                //Vieja manera de efectuar un query en SQL Server
                //$result = mysql_query($query,$this->conn);
                $result = sqlsrv_query($this->conn, $query);
                //Vieja manera de llenar los rows con un array asociativo
                //$row    = mysql_fetch_assoc($result);
                $row    = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                //Vieja manera de liberar la memoria del resultado
                //mysql_free_result($result);
                sqlsrv_free_stmt($result);
            }
            if(!isset($row['rif']) and !isset($row['razonsocial'])){
                //Hacer consulta al Servidor del seniat
                $server = "ssh adminscripts@192.168.1.13 \"/home/adminscripts/rif/./oneRif $rif\"";
                exec($server);
                if($this->conn){
                    //Vieja manera de efectuar un query en SQL Server
                    //$result = mysql_query($query,$this->conn);
                    $result = sqlsrv_query($this->conn, $query);
                    //Vieja manera de llenar los rows con un array asociativo
                    //$row    = mysql_fetch_assoc($result);
                    $row    = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    //Vieja manera de liberar la memoria del resultado
                    //mysql_free_result($result);
                    sqlsrv_free_stmt($result);
                    return $row;
                }
            }
            else{
                return $row;
            }
        }
        else {
            //Armo correactamente el rif y vuelvo a llamar a la funcion
            $rif = $this->setRif($rif,$tipo_rif);
            return $this->getRifInformation($rif, NULL);
        }
	}

}

?>
