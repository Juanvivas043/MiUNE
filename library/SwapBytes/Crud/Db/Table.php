<?php
/**
 * Clase que permite generar la sentencia SQL utilizada en la busqueda de todos
 * las columnas a mostrar en la lista. Su uso esta orientado a PostgreSQL.
 *
 * @category SwapBytes
 * @package  SwapBytes_Crud_Db_Table
 * @version  0.4
 * @author   Nicola Strappazzon C., nicola51980@gmail.com, http://nicola51980.blogspot.com
 */
class SwapBytes_Crud_Db_Table {
    /**
     * Crea parte de la clausula WHERE para realizar busquedas avanzadas en una
     * tabla en especifico. Podemos realizar las siguientes busquedas:
     *  - Valor1
     *  - Valor1 Valor2
     *  - Valor1 + Valor2 Valor3
     *  - Valor1 + 'Valor2'
     *
     * NOTA: Estas pueden ser combinadas de cualquier forma.
     *
     * @param array  $searchParams Arreglo de columnas a buscar.
     * @param strint $searchData   Cadena de texto con patrones que se desean buscar.
     * @return string
     */
    public function getSearch($searchParams, $searchData) {
        if(!isset($searchParams))    return '';
        if(!isset($searchData))      return '';
        if(!is_array($searchParams)) return '';
        if(!is_string($searchData))  return '';
        if(strlen($searchData) == 0) return '';

        $SQL    = ' AND (';
        $Search = explode('+', $searchData);

        foreach($Search as $Value) {
            $Value = trim($Value);

            if(strlen($Value) > 0) {
                if(substr_count($Value, "'") == 2) {
                    $Value = "{$Value}";
                    
                } else {
                    $Value = str_replace("'",  "", $Value);
                    $Value = str_replace(" ", "%", $Value);
                    $Value = "'%{$Value}%'";
                    
                }

                foreach($searchParams as $param) {
                    $SQL .= $param . "::text ILIKE {$Value} OR ";
                }
            }

            $SQL  = rtrim($SQL, ' OR ');
            $SQL .= ') AND (';
        }

        $SQL  = rtrim($SQL, ') AND (');
        $SQL  = rtrim($SQL, ' OR ');
        $SQL .= ')';
      
        
        return $SQL;
    }
}
?>