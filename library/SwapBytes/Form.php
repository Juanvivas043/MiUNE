<?php
/**
 * Permite complementar mediante una serie de metodos las funcionalidades de la
 * clase Zend_Form.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Form {
    protected $_form;
	
	public function __construct() {
		
	}

    /**
     * Define el objeto actual del formulario.
     *
     * @param object $form Zend_Form
     */
    public function set($form) {
        $this->_form = $form;
        return true;
    }

    /**
     * Retorna el objeto form modificado.
     *
     * @return object Zend_Form
     */
    public function get() {
        return $this->_form;
    }

    /**
     * Deshabilita o Abilita todos los elementos del formulario. Ignora todos los
	 * elementos de tipo Hidden.
     *
     * @param boolean $status Estado de acceso del elemento.
     */
    public function enableElements($status = false) {
        $Elements = $this->_form->getElements();

        foreach($Elements as $Element) {
			if($Element->getType() <> 'Zend_Form_Element_Hidden') {
				$this->enableElement($Element->getName(), $status);
			}
        }
    }

    /**
     * Define la lectura unicamente todos los elementos del formulario.
     *
     * @param boolean $status Estado de acceso del elemento.
     */
    public function readOnlyElements($status = true) {
        $Elements = $this->_form->getElements();

        foreach($Elements as $Element) {
            $this->readOnlyElement($Element->getName(), $status);
        }
    }

    /**
     * Define la lectura unicamente de un elemento en espesifico del formulario.
     *
     * @param string $Field   Nombre del elemento.
     * @param boolean $status Estado de acceso del elemento.
     */
    public function readOnlyElement($Field, $status = true) {
        $this->_form->{$Field}->setAttrib('readonly', ($status == true)? true  : null);
    }

    /**
     * Deshabilita o Abilita el acceso un elemento en espesifico del formulario.
     *
     * @param string $Field   Nombre del elemento.
     * @param boolean $status Estado de acceso del elemento.
     */
    public function enableElement($Field, $status = false) {
        $this->_form->{$Field}->setAttrib('disabled', ($status == false)? false : null);
    }

    /**
     * Permite verificar y corregir si una cadena de texto que proviene de la
     * base de datos con el tipo de dato boolean (t ó f), debido a que en el MDB
     * PostgreSQL al resivir un falor 'f' es un NULL y altera el comportamiento
     * de las opciones de un Zend_Form_Element_Radio.
     *
     * @param string $Value
     * @return string
     */
    public function setValueToBoolean($Value) {
         return (isset($Value) && $Value == 't')? 't' : 'f';
    }
    
    public function setValueToCheck($Value) {
        
         return (isset($Value) && $Value == '1')? '1' : '0';
    }

    public function fillSelectBox($Field, $Rows, $Id, $Value) {
        if(isset($Rows) && is_array($Rows)) {
            foreach($Rows as $Row) {
                $this->_form->{$Field}->addMultiOption($Row[$Id], $Row[$Value]);
            }
        }
    }
}
?>
