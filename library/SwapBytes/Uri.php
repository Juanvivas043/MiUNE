<?php

/**
 * Permite complementar mediante una serie de metodos las funcionalidades de la
 * clase Zend_Uri.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Uri {

  /**
   * Convierte una cadena de texto recibida por URI de tipo GET a un arreglo.
   * En caso de que existan variables con el mismo nombre y diferentes valores,
   * este crea un arreglo de todos los valores en una sola variable.
   *
   * @param string $queryString
   * @return string
   */
  public function queryToArray($queryString) {
	$queryString = urldecode($queryString);
	$queryParams = array();
	$value = null;
	$key = null;

	if(isset($queryString)) {
	  $temp = explode('&', $queryString);
	  if(isset($temp)) {
		foreach($temp as $param) {
		  if(!empty($param)) {
			list($key, $value) = explode('=', $param);
			
			if(!isset($queryParams[$key])) {
			  $queryParams[$key] = $value;
			}else if(is_array($queryParams[$key])) {
			  $queryParams[$key][] = $value;
			}else{
			  $queryParams[$key] = array($value, $queryParams[$key]);
			}
		  }
		}
	  }
	}

	return $queryParams;
  }

  /**
   * Obtiene el protocolo del URI, este puede ser HTTP o HTTPS.
   *
   * @return string
   */
  public function getProtocol() {
	$protocol = 'http';
	if (isset($_SERVER['HTTPS']) || $_SERVER['HTTPS']) {
	  $protocol = 'https';
	}

	return $protocol;
  }

}

?>
