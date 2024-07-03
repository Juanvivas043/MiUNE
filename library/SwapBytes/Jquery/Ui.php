<?php
/**
 * Clase que contiene una serie de metodos que permiten la integracion con el
 * framework de javascript llamado jQuery.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Jquery_Ui {
	/**
     * Asigna un Widgets de tipo datepicker al Objeto HTML deseado.
     * @param string $id
     */
    public function setDatepicker($id) {
        return "$('#{$id}').datepicker({dateFormat:'dd/mm/yy', dayNamesMin:['D', 'L', 'M', 'M', 'J', 'V', 'S'], monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] })";
    }

        public function phone($id) {
        return "$('#{$id}').mask('(9999)999.99.99')";
    }
}
?>
