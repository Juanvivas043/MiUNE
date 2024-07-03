<?php
class Validator_LessThanElement extends Zend_Validate_Abstract {
  const MSG_LESSTHEN = 'msgLessThen';


  /**
   * Definimos el mensaje de error con sus respectivas variables.
   *
   * @var array
   */
  protected $_messageTemplates = array(
//    self::MSG_LESSTHEN => "'%value%' no puede ser menor que '%token%'."
      //self::MSG_LESSTHEN => "'%value%' no puede ser menor que '%fechainicio%'."
      self::MSG_LESSTHEN => "la Fecha de culminacion debe ser mayor o igual a la de inicio"
  );

  /**
   * Definimos las variables que se van a usar para construir el mensaje de
   * error. El índice corresponde al nombre que se usara en el mensaje, y el
   * valor a la variable que se utiliza en el código.
   *
   * @var array
   */
  protected $_messageVariables = array(
    'token' => 'token'
  );

  /**
   * Variable tipo arreglo utilizado para contener todos los elementos que
   * requieran ser analizados durante la validación, estas son las opciones
   * que se pasan como tercer parámetros al método addValidator.
   *
   * @var array
   */

  protected $_options = array();



  /**
   * Constructor sobre-escrito que recibe como parámetros las opciones
   * adicionales cuando se pasan al método addValidator.
   *
   * @param array $options
   */
  public function __construct($options = null) {
    $this->_options = $options;
 
  }


  /**
   * Método sobre-escrito que determina si un elemento es valido. Este método
   * compara si un elemento es mayor que otro elemento.
   *
   * @param string $value
   * @param array $context
   * @return boolean
   */
  public function isValid($value, $context = null) {
    $this->_setValue($value);

    $this->logger = Zend_Registry::get('logger');

   
    foreach($this->_options as $option) {
      if(isset($context[$option])) {
        $this->token = $context[$option];

        
           
           $date = new Zend_Date($value,'dd-MM-YYYY');
           $date1 = new Zend_Date($this->token,'dd-MM-YYYY');
           
        
        //$this->logger->log($date,ZEND_LOG::ALERT);
        
        if($date < $date1) {
            
            
                $this->_error(self::MSG_LESSTHEN);
                return false;
            
          
          

        }
      }
    }

    return true;
    
  }
}
?>

