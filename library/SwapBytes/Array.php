<?php
/**
 * Clase que contiene una serie de metodos que complementan las funcionalidades
 * de los arreglos.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Array {
/**
 * Permite convertir un arreglo compuesto de sus indices y valores a una
 * cadena de texto, la funcion original de php no permite agregar los indices
 * del arreglo.
 *
 * @param string $glue
 * @param array $array
 * @return string
 */
    public function implode($glue, $array) {
        $string = '';

        foreach ($array as $index => $value) {
            $string .= $index.$value.$glue;
        }

        return $string;
    }

    public function replace_recursive($find, $replace, &$data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->replace_recursive($find, $replace, $data[$key]);
                } else if (!(is_object($value) && ($value instanceof Closure))) {
                    $data[$key] = str_replace($find, $replace, $value);
                }
            }
        } else {
            $data = str_replace($find, $replace, $data);
        }
    }

    /**
     * Divide la clave de un arreglo en dos claves y conserva el valor del elemento.
     *
     * Arreglo original:
     * array([chk12256] => on
     *       [sel12256] => 1269
     *       [txt12256] => 12
     *       [chk12260] => on
     *       [sel12260] => 1264
     *       [chk12261] => on
     *       [sel12261] => 1264)
     *
     * Arreglo modificado:
     * Array([12256] => Array([txt] => 12
     *                        [sel] => 1269
     *                        [chk] => on)
     *       [12260] => Array([sel] => 1264
     *                        [chk] => on)
     *       [12261] => Array([sel] => 1264
     *                        [chk] => on))
     *
     * @param array $array
     * @param array $keyPrefix
     * @return array
     */
    public function split_key($array, $keyPrefix) {
        if(!is_array($array))     return;
        if(!is_array($keyPrefix)) return;

        $arrayOut = array();

        foreach($keyPrefix as $prefix) {
            foreach($array as $RowIndex => $Rows) {
                if(substr($RowIndex, 0, strlen($prefix)) == $prefix) {
                    $arrayOut[substr($RowIndex, strlen($prefix), strlen($RowIndex) - strlen($prefix))][$prefix] = $Rows;
                }
            }
        }

        return $arrayOut;
    }

     public function in_array_recursivo($needle, $haystack) {
    foreach ($haystack as $item) {
        if ($item === $needle || (is_array($item) && $this->in_array_recursivo($needle, $item))) {
            return true;
        }
    }

    return false;
    }
}
?>
