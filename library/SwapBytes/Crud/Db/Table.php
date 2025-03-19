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

    public function getSearchFixed($searchParams, $searchData, $hasWhere = false) {
        if (!isset($searchParams) || !is_array($searchParams) || empty($searchParams)) {
            return ''; // Si no hay parámetros de búsqueda, no se genera SQL.
        }
    
        if (!isset($searchData) || !is_string($searchData) || strlen(trim($searchData)) === 0) {
            return ''; // Si no hay datos de búsqueda válidos, no se genera SQL.
        }
    
        $conditions = []; // Arreglo para acumular condiciones.
        $Search = explode('+', $searchData); // Divide los términos por el carácter '+'.
    
        foreach ($Search as $Value) {
            $Value = trim($Value);
    
            if (strlen($Value) > 0) { // Sólo procesar términos válidos.
                $Value = str_replace("'", "", $Value); // Elimina apóstrofes.
                $Value = str_replace(" ", "%", $Value); // Reemplaza espacios por '%'.
                $Value = "'%{$Value}%'"; // Envuelve el término con comodines.
    
                foreach ($searchParams as $param) {
                    // Genera una condición para cada parámetro.
                    $conditions[] = "{$param}::text ILIKE {$Value}";
                }
            }
        }
    
        if (!empty($conditions)) {
            // Si hay condiciones, únelas con OR.
            $SQL = implode(" OR ", $conditions);
    
            // Si la consulta original no tiene WHERE, prepende WHERE, si no, prepende AND.
            $SQL = ($hasWhere ? " AND " : " WHERE ") . $SQL . "";
        } else {
            // Si no hay condiciones válidas, no se genera SQL.
            $SQL = '';
        }
    
        return $SQL;
    }
    
}
?>