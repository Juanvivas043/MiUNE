<?php
/**
 * Clase que contiene una serie de metodos que complementan las funcionalidades
 * de las fechas.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Date {
    /**
     * Convierte una fecha que puede provenir del manejador de base de datos al
     * formato local definido en el Boostrap.
     *
     * @param string $date
     * @return string
     */
    public function convertToForm($date) {
        if(isset($date)) {
            $date = ($date == '1900-01-01')? $date : strtotime($date);
            
            $Zend_Date = new Zend_Date($date);
            $Zend_Locale_Format = Zend_Locale_Format::setOptions();
            return $date = $Zend_Date->toString($Zend_Locale_Format['date_format']);
        }
    }

    /**
     * Convierte una fecha que puede provenir de un formulario, al formato que
     * entiende el manejador de base de datos, en este caso PostgreSQL.
     * 
     * @param string $date
     * @return string
     */
    public function convertToDataBase($date) {
        if(!empty($date)) {
            $date      = str_replace('/', '-', $date);
            $Zend_Date = new Zend_Date(strtotime($date));
            return $Zend_Date->toString("YYYY/MM/dd");
        }
    }
}
?>
