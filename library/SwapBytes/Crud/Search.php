<?php

class SwapBytes_Crud_Search {
    public function __construct() {
		$this->properties['display'] = true;
		$this->properties['width']   = 430;
		$this->properties['name']    = 'txtBuscar';
		$this->properties['id']      = 'txtBuscar';
    }

	/**
	 * Muestra el campo de texto para realizar la busqueda.
	 *
     * @param booleean $Enable
	 */
	public function setDisplay($Enable) {
		if(!is_bool($Enable)) return;

        $this->properties['display'] = $Enable;
	}

	public function getDisplay() {
        return $this->properties['display'];
	}

	public function setWidth($Width) {
		if(!is_numeric($Width)) return;

        $this->properties['width'] = $Width;
	}

	/**
	 * Obtiene el codigo en HTML del campo de texto necesario para el GUI.
	 * 
	 * @return string
	 */
	public function getHtml() {
		$html = "<input id='{$this->properties['id']}' name='{$this->properties['name']}' type='text' style='width:{$this->properties['width']}px'>";
		
		return $html;
	}
}
?>
