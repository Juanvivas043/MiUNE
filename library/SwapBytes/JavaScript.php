<?php
/**
 * Permite complementar mediante una serie de metodos las funcionalidades de la
 * clase Zend_JavaScript.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_JavaScript {

    /**
     * Crea mediante un arreglo toda la sentencia IF, THEN y con un parametro
     * adicional el ELSE.
     *
     * @param array  $ifthen
     * @param string $else
     * @return string
     */
    public function ifThenElse($ifthen, $else = null) {
        if(is_array($ifthen)) {
            $index = 0;
            $js    = array();
            
            foreach($ifthen as $condition => $action) {
                $js[$index] = (($index == 0)? 'if' : 'else if') . "({$condition}){{$action}}";
                
                $index++;
            }

            if(isset($else)) {
                $js[] = "else{{$else}}";
            }

            return implode($js);
        }
    }
}
?>
