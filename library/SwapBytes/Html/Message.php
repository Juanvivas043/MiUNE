<?php
/**
 * Contiene una serie de metodos que permite construir un mensaje en HTML de forma
 * pre-definida.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Html_Message {

    /**
     * Crea una mensaje de tipo alerta para ser visualizado dentro de un objeto
     * HTML de tipo DIV.
     *
     * @param string $text Mensaje a mostrar.
     * @return string
     */
    public function alert($text) {
        if (isset($text)) {
			return "<div class=\"alert\"><div class=\"message\">{$text}</div></div>";
		}
    }
}
?>
