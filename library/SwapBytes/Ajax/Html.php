<?php
/**
 * Clase que contiene una serie de metodos para el manejo basico de Ajax para los
 * objetos del formulario HTML.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Ajax_Html {
    /**
     * Metodo que permite crear la estructura en JSON para poder llenar un objeto
     * de tipo SELECT desde un arreglo de datos obtenido de la base de datos.
     */
    public function fillSelect($Values, $label = null) {
        $Items = array();

        if(isset($label)) {
            $Items[] = array('optionValue' => 0, 'optionDisplay' => $label, 'optionStyle' => array('text-align' => 'center', 'font-weight' => 'bolder'));
        }

        if(isset($Values) && is_array($Values)) {
            foreach($Values as $Value) {
                $Keys = array_keys($Value);
                if(count($Keys) >= 2) {
                    $Items[] = array('optionValue' => $Value[$Keys[0]], 'optionDisplay' => $Value[$Keys[1]]);
                } else if(count($Keys) == 1) {
                    $Items[] = array('optionValue' => $Value[$Keys[0]], 'optionDisplay' => $Value[$Keys[0]]);
                }
            }
        }

        return json_encode($Items);
    }
}
?>
