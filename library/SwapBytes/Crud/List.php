<?php
/**
 * Clase que permite generar una lista o tabla en HTML de una fuente de datos tipo
 * arreglo, posiblemente proveniente de una Base de Datos, permite la incorporacion
 * de controles dentro de la lista, manejo de encabezado y pie de pagina, como la
 * paginación de la misma. En esta clase se crea todo el codigo HTML y JavaScript
 * necesario. Esta orientado su uso a jQuery.
 *
 * @category SwapBytes
 * @package  SwapBytes_Crud_List
 * @version  0.4
 * @author   Nicola Strappazzon C., nicola51980@gmail.com, http://nicola51980.blogspot.com
 */

define('SQL_FUNCTION_COUNT', 1);
define('SQL_FUNCTION_SUM'  , 2);
define('SQL_FUNCTION_AVG'  , 3);
define('SQL_FUNCTION_MAX'  , 4);
define('SQL_FUNCTION_MIN'  , 5);


class SwapBytes_Crud_List {
    /**
     * Constructor de la clase.
     */
    public function __construct() {
        // Clases del Framework.
        //$this->controllerFront = Zend_Controller_Front::getInstance();
        $this->controller      = Zend_Controller_Front::getInstance();
        //$this->request         = new Zend_Controller_Request_Http();

        // Clases propias.
        $this->SwapBytes_Html         = new SwapBytes_Html();
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->SwapBytes_Array        = new SwapBytes_Array();
        $this->SwapBytes_Jquery       = new SwapBytes_Jquery();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();

    }

    /**
     * Crea una tabla HTML que contiene una lista del contenido obtenido de una
     * tabla, adicionalmente agrega la paginación y una serie de acciones que se
     * pueden realizar sobre un registro.
     *
     * @param <type> $itemPerPage
     * @param <type> $pageNumber
     * @param <type> $pageRange
     * @param <type> $rows
     * @param <type> $columns
     * @param <type> $actions
     * @param <type> $count
     * @param string $actions
     * @return string
     */
	// setPaginator
    public function fillWithPaginator($table, $rows, $columns, $itemPerPage, $pageNumber, $pageRange, $count, $actions = null) {
        $HTML = '';

        if(is_array($rows) && count($rows) > 0) {
            $HTML = $this->fill($table, $rows, $columns, $actions);

            // Asigna la lista y el paginador a la vista.
            if(isset($itemPerPage) && isset($pageNumber) && isset($pageRange) && isset($count)) {
                $paginator  = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
                $paginator->setItemCountPerPage($itemPerPage)
                          ->setCurrentPageNumber($pageNumber)
                          ->setPageRange($pageRange);

                $HTML .= $paginator;
            }

            $HTML  = str_replace("\n", "", $HTML);
            
        } else {
            // Envia un mensaje por que no consigue registros.
            $width = (isset($table['width']))? $table['width'] : '300px';
            $HTML  = '<div class="alert" style="text-align:center;width:' . $width . '">No existen registros.</div>';
        }

        return $HTML;
    }

    /**
     * Crea una tabla HTML que contiene una lista del contenido obtenido de una
     * tabla, adicionalmente agrega una serie de acciones que se pueden realizar
     * sobre un registro.
     *
     * Se puede remplazar un valor en especifico que se encuentra encerrado entre
     * numerales (#) dobles, Ej: ##nombre##, siempre y cuando (nombre) sea una
     * columna retornada por la consulta.
     *
     * $columns[0]['column']:  Nombre de la columna en el arreglo que contiene los datos a listar.
     * $columns[0]['name']:    Nombre de la columna a mostrar en la tabla.
     * $columns[0]['primary']: Indica si el valor contenido es primario de la tabla (true/false).
     * $columns[0]['hide']:    No se muestra la columna (true/false).
     * $columns[0]['width']:   Ancho que se le define a la columna.
     * $columns[0]['rows']:    Propiedades HTML que se le definen a cada registro que se esta listando.
     * $columns[0]['rows']['style']: Define un estilo.
     * $columns[0]['rows']['class']: Define un CSS.
     * $columns[0]['control']: Define un control de tipo HTML a la columna.
     *
     * @param <type> $table
     * @param <type> $rows
     * @param <type> $columns
     * @param <type> $actions
     * @return <type>
     */
	//getTable
    //Si se pasa la letra O por accion se deben de pasar sus contenidos en la
    //varibale $otheactions y si requiere alguna validadcion en validate
    public function fill($table, $rows, $columns, $actions = null, $otheractions = null) {
        $controllerView  = Zend_Layout::getMvcInstance()->getView();
        $controllerName  = $this->controller->getRequest()->getControllerName();
        $columnPrimary   = $this->_getColumnPrimary($columns);

        $actionName_Header = 'Acciones';
        $actionName_View   = 'Ver';
        $actionName_Update = 'Editar';
        $actionName_Delete = 'Eliminar';
        $actionName_Download = 'Descargar';
        $actionName_Info   = 'Información';

        $TableProperties = $this->SwapBytes_Html->convertToProperties('table', $table);

        $HTML  = "<table{$TableProperties}>";
        $HTML .= '<tr>';
		// Creamos el encabezado de la tabla.
        foreach($columns as $column) {
            if(empty($column['hide']) || (isset($column['hide']) && $column['hide'] == false)) {
                $ThProperties = $this->SwapBytes_Html->convertToProperties('th', $column);
                $HTML .= "<th{$ThProperties}>";
                if(is_string($column['name'])) {
                    $HTML .= $column['name'];
                } else if(isset($column['name']['control'])) {
                    $HTML .= $this->_toControl($column['name']);
                }
                $HTML .= '</th>';
            }
        }

        if(isset($actions)) {
            $HTML .= '<th>' . $actionName_Header . '</th>';
        }

        $HTML .= '</tr>';

        if(isset($rows)) {
         $onClickOther = array();
         $rowSum       = array();
			$rowOddOrEven = null;
			$rowColorTemp = $table['zebra']['colors']['odd'];

            foreach($rows as $rowNum => $row) {
				// Preparamos los eventos y las sentencias de AJAX en jQuery para editar
				// modificar, ver, eliminar y la información adicional mediante pantallas
				// modales sobre cada registro de la tabla a listar.
                $urlView    = $controllerView->url(array('controller'=>$controllerName, 'action'=>'view'         , 'id'=>$row[$columnPrimary]));
                $urlEdit    = $controllerView->url(array('controller'=>$controllerName, 'action'=>'addoreditload', 'id'=>$row[$columnPrimary]));
                $urlDelConf = $controllerView->url(array('controller'=>$controllerName, 'action'=>'deleteload'   , 'id'=>$row[$columnPrimary]));
                $urlInfo    = $controllerView->url(array('controller'=>$controllerName, 'action'=>'info'         , 'id'=>$row[$columnPrimary]));
                $urlDownload    = $controllerView->url(array('controller'=>$controllerName, 'action'=>'download'         , 'id'=>$row[$columnPrimary]));


                $onClickView    = "$.getJSON('{$urlView}', function(data){executeCmdsFromJSON(data)}); return false;";
                $onClickEdit    = "$.getJSON('{$urlEdit}', function(data){executeCmdsFromJSON(data)}); return false;";
                $onClickDelConf = "$.getJSON('{$urlDelConf}', function(data){executeCmdsFromJSON(data)}); return false;";
                $onClickInfo    = "$.getJSON('{$urlInfo}', function(data){executeCmdsFromJSON(data)}); return false;";
                $onClickDownload    = "$.getJSON('{$urlDownload}', function(data){executeCmdsFromJSON(data)}); return false;";

                $onClickView    = htmlentities($onClickView   , ENT_QUOTES);
                $onClickEdit    = htmlentities($onClickEdit   , ENT_QUOTES);
                $onClickDelConf = htmlentities($onClickDelConf, ENT_QUOTES);
                $onClickInfo    = htmlentities($onClickInfo   , ENT_QUOTES);
                $onClickDownload    = htmlentities($onClickDownload   , ENT_QUOTES);


                 $this->logger = Zend_Registry::get('logger');

                if(isset($actions)) {
                   $others = array();
                   $number_other = substr_count($actions, "O");
                   if(substr_count($actions, "O")){
                      foreach($otheractions as $otheraction){

                         $operations = array('actionName' => '',
                            'onClick'    => '',
                            'label'      => '');
                         
                        /*Array(
                         Array( 'actionName' => 'accion',
                                'label' => 'sub',
                                'column' => 'fechainicio',
                                'validate' => 'true',
                                'intrue' => '',
                                'intruelabel' => '')
                         );
                         * actionName sera el nombre de la accion asignada donde el valor de column sea null
                         * intrue es la accion para el caso contrario
                         */

                         if(isset($otheraction['validate'])){

                            if($row[$otheraction['column']]== $otheraction['intrue']){
                               $operations['actionName'] = $otheraction['actionName'];
                               $operations['label']      = $otheraction['label'];
                            }else if($row[$otheraction['column']]){
                               if(isset($otheraction['intrue'])){
                                  $operations['actionName'] = $otheraction['intrue'];
                                  $operations['label']      = $otheraction['intruelabel'];
                               }
                            }
                         }else{
                               $operations['actionName'] = $otheraction['actionName'];
                               $operations['label']      = $otheraction['label'];
                         }
                         if(isset($otheraction['action'])){
                             $otheraction['action'] = str_replace("##pk##", $row[$columnPrimary], $otheraction['action']);
                             $operations['onClick'] = $otheraction['action'];
                         }else{
                             $onClickO = "newwindow = window.open(\"{$controllerName}/{$operations['actionName']}/id/{$row[$columnPrimary]}\", '{$row[$columnPrimary]}');if (window.focus) {newwindow.focus()}";
                             $onClickO = htmlentities($onClickO , ENT_QUOTES);
                             $operations['onClick'] = $onClickO;
                         }
                        $urlOther = $controllerView->url(array('controller'=>$controllerName, 'action'=>$operations['actionName'], 'id'=>$row[$columnPrimary]));
                         //Modifcar
//                         $onClickO = "window.location.href = \"{$controllerName}/{$operations['actionName']}/id/{$row[$columnPrimary]}\";";
                         $hrefO = "#";
                         $operations['hrefO'] = $hrefO;
                         array_push($others, $operations);

                      }
                   }
               }

                $TrProperties = '';
                // Aplicamos una condicion para definir las nuevas propiedades del TR en la tabla a crear.
                if(isset($table['rows']['conditions'])) {
                    foreach($table['rows']['conditions'] as $condition) {
                        if($condition['equal'] == $row[$table['column']]) {
                            $TrProperties = $this->SwapBytes_Html->convertToProperties('tr', $condition['properties']);
                        }
                    }
                }

				// Definimos la paridad por el valor del row y no por el numero del row.
				// Se busca que si un determinado row, por ejemplo; Todos los row's de la columna Día son los que
				// definen la paridad, buscando que todos los días Lunes son de un mismo color (odd) y los Martes
				// (even) y asi por cada día de la semana.
				if(isset($table['zebra']['column'])) {
					$rowOddOrEven = " style=\'background-color: #{$rowColorTemp};\'";

                    if(isset($rows[$rowNum+1])){
                        if($rows[$rowNum][$table['zebra']['column']] <> $rows[$rowNum + 1][$table['zebra']['column']]) {
                            if($rowColorTemp == $table['zebra']['colors']['even']) {
                                $rowColorTemp = $table['zebra']['colors']['odd'];
                            } else {
                                $rowColorTemp = $table['zebra']['colors']['even'];
                            }
                        }
                    }
				// Define una configuración de colores adicionales para los registros pares e impares de la tabla.
				// Sumamos la variable $rowNum con + 1 para corregir la diferencia de que en arreglo se inicia desde
				// el valor 0, de esta forma al configurar se podra entender mas facilmente lo que se esta apreciando.
				} else {
					if(isset($table['zebra']['colors']['odd']) || isset($table['zebra']['colors']['even'])) {
						if (($rowNum + 1) % 2) {
							$rowOddOrEven = " style=\'background-color: #{$table['zebra']['colors']['odd']};\'";
						} else {
							$rowOddOrEven = " style=\'background-color: #{$table['zebra']['colors']['even']};\'";
						}
					}
				}

                // Se crea la tabla.
                $HTML .= "<tr{$TrProperties}{$rowOddOrEven}>";
                foreach($columns as $column) {
                    $TdProperties = '';
                    if(empty($column['hide']) || (isset($column['hide']) && $column['hide'] == false)) {
                        if(isset($column['rows'])) {
                            $TdProperties = $this->SwapBytes_Html->convertToProperties('td', $column['rows']);
                        }

                        // Agregamos un control de tipo HTML a la lista.
                        if(isset($column['control'])) {
                            // Aplicamos una condicion para definir las nuevas propiedades a un control.
                            if(isset($column['control']['conditions'])) {
                                foreach($column['control']['conditions'] as $condition) {
                                    if(is_array($condition['properties']) && $condition['equal'] == $row[$column['column']]) {
                                        $column['control'] = array_merge($column['control'], $condition['properties']);
                                    }
                                    if (is_object($condition['callBack']) && ($condition['callBack'] instanceof Closure) ) {
                                        if(is_array($condition['properties']) && $condition['callBack']($row[$column['column']]) )  {
                                            $column['control'] = array_merge($column['control'], $condition['properties']);
                                        }
                                    }
                                }
                            }

                            // Se busca si existe algun valor que remplzar por el que proviene de la Base de Datos.
                            // Estos valores son los que se encuentran encerrados con dobles #, Ej.: ##nombre##
                            foreach($columns as $columnTemp) {
                                $this->SwapBytes_Array->replace_recursive("##{$columnTemp['column']}##", $row[$columnTemp['column']], $column['control']);
                            }

                            // SELECT: Creamos el HTML de los option del Control Select.
                            if($column['control']['tag'] == 'select' && is_array($column['control']['options'])) {
                                $column['control']['html'] = $this->SwapBytes_Html->selectOptions($column['control']['options'], $row[$column['column']]);
                            }
                            //IMAGEN
                            if($column['control']['tag'] == 'img') {
                                $column['control']['html'] = $this->SwapBytes_Html->img($row[$column['column']]);
                            }

                            // Asignamos un control y su respectivo valor.
                            $HTML .= "<td{$TdProperties}>";
                            $HTML .= $this->_toControl($column);//"<{$TagStart}{$property}>{$TagHtml}{$TagEnd}";
                            $HTML .= "</td>";
                        // Simplemente mostramos el valor.
                        } else {
                            // Agregamos un contador a los registros si es solicitado.
                            if(isset($column['function']) && $column['function'] == 'rownum') {
                                $row[$column['column']] = $rowNum+1;
                            }

                            // Sumamos el valor de una columna si es solicitado.
                            if(isset($this->footer)){
                                if($this->footer[0]['column'] == $column['name']) {
                                    $rowSum[] = (int)$row[$column['column']];
                                }
                            }

                            // Formateamos el texto a ser mostrado devidamente en HTML.
                            $value = $row[$column['column']];
                            $value = str_replace(array("\r\n", "\n", "\r"), '<br />', $value);
                            $value = addslashes($value);//DANIEL CASTRO ESTUVO AQUI
                            //$value = $controllerView->escape($value);

                            $HTML .= "<td{$TdProperties}>" . $value . "</td>";

                        }
                    }
                }

				// Agregamos los botones de acción; Ver, Información adicional, Modificar y/o Eliminar.
                if(isset($actions)) {
                    $HTML .= '<td align="center">';
                    if(substr_count($actions, "V"))
                        $HTML .= '<a href="#" onclick="' . $onClickView    . '">' . $actionName_View   . '</a>&nbsp;';
                    if(substr_count($actions, "U"))
                        $HTML .= '<a href="#" onclick="' . $onClickEdit    . '">' . $actionName_Update . '</a>&nbsp;';
                    if(substr_count($actions, "D"))
                        $HTML .= '<a href="#" onclick="' . $onClickDelConf . '">' . $actionName_Delete . '</a>&nbsp;';
                    if(substr_count($actions, "I"))
                        $HTML .= '<a href="#" onclick="' . $onClickInfo    . '">' . $actionName_Info   . '</a>&nbsp;';
                    if(substr_count($actions, "R"))
                        $HTML .= '<a href="#" onclick="' . $onClickDownload    . '">' . $actionName_Download   . '</a>&nbsp;';

                    if(substr_count($actions, "O"))
                    {
                       $i = 0;
                      foreach ($others as $other) {
                          // code...
                          //$this->logger->log($other,ZEND_LOG::WARN);
                           $HTML .= '<a href="'. $other['hrefO']. '" onclick="' . $other['onClick'] . '">' . $other['label'] . '</a>&nbsp;';
                      }{
                        //$HTML .= '<a href="'. $other['hrefO']. '" onclick="' . $other['onClick'] . '">' . $other['label'] . '</a>&nbsp;';
                      }
                    }
                    $HTML .= '</td>';
                }

                $HTML .= '</tr>';
            }

            // Crea el footer de la tabla.
            if(isset($this->footer)) {
                $HTML .= '<tfoot><tr>';

                foreach($columns as $column) {
                    if($column['primary'] == false && $column['hide'] == false) {
                        if($this->footer[0]['column'] == $column['name']) {
                            $value = 0;
                            $name  = $this->footer[0]['name'];

							// Contamos todos los registros listados.
                            if($this->footer[0]['function'] == SQL_FUNCTION_COUNT) {
                                $value = $rowNum;
							// Sumamos todos los valores de una determinada columna.
                            } else if($this->footer[0]['function'] == SQL_FUNCTION_SUM) {
                                $value = array_sum($rowSum);
							// Calculamos el promedio todos los valores sumados de una determinada columna.
                            } else if($this->footer[0]['function'] == SQL_FUNCTION_AVG) {
                                $value = array_sum($rowSum) / $rowNum;
							// Buscamos el valor minimo de todos los valores de una determinada columna.
                            } else if($this->footer[0]['function'] == SQL_FUNCTION_MIN) {
                                $value = asort($rowSum);
                                $value = $value[0];
							// Buscamos el valor maximo de todos los valores de una determinada columna.
                            } else if($this->footer[0]['function'] == SQL_FUNCTION_MAX) {
                                $value = arsort($rowSum);
                                $value = $value[0];
                            }

                            $HTML .= "<th>{$name}: {$value}</th>";
                        } else {
                            $HTML .= "<th>&nbsp;</th>";
                        }
                    }
                }

                if(isset($actions)) {
                    $HTML .= "<th>&nbsp;</th>";
                }

                $HTML .= '</tr></tfoot>';
            }
        } else {
            return null;
        }
        $HTML .= '</table>';
        //$HTML  = htmlentities($HTML);
        //$HTML  = $controllerView->escape($HTML, ENT_QUOTES);
//        $HTML  = get_html_translation_table($HTML, ENT_QUOTES);

        return $HTML;
    }

	//getPaginator
    /**
     * Busca la clave primaria del arreglo que contiene la configuracion de las
     * columnas a listar.
     *
     * @param array $columns
     * @return string
     */
    private function _getColumnPrimary($columns) {
        foreach($columns as $column) {
            if(isset($column['primary']) && $column['primary'] == true) {
                return $column['column'];
            }
        }
    }

    /**
     * Crea un control apartir de un arreglo.
     *
     * @param array $control
     * @return string
     */
    private function _toControl($control) {
        $TagStart = $control['control']['tag'];
        $property = $this->SwapBytes_Html->convertToProperties($TagStart, $control['control']);

        $TagEnd  = (in_array($TagStart, array('a', 'label', 'select')))? "</{$TagStart}>" : '';
        $TagHtml = (isset($control['control']['html']))? $control['control']['html'] : '';

        return "<{$TagStart}{$property}>{$TagHtml}{$TagEnd}";
    }

    /**
     * Crea un pie en la tabla para mostrar totales que son generados por funciones
     * de grupo, como por ejemplo, MAX, MIN, COUNT y AVG. Este metodo debe ser
     * definido antes de crear la tabla con los metodos $this->create o $this->action.
     *
     * @param string $columnName
     * @param string $resultName
     * @param string $functionGroup
     */
    public function addFooter($columnName, $resultName, $functionGroup) {
        $this->footer[0]['column']   = $columnName;
        $this->footer[0]['name']     = $resultName;
        $this->footer[0]['function'] = $functionGroup;

    }

    public function fillMultiTable($table, $rows, $columns, $actions = null, $otheractions = null) {
        $controllerView  = Zend_Layout::getMvcInstance()->getView();
        $controllerName  = $this->controller->getRequest()->getControllerName();
        $columnPrimary   = $this->_getColumnPrimary($columns);
        $actionName_Header = 'Acciones';
        $actionName_View   = 'Ver';
        $actionName_Update = 'Editar';
        $actionName_Delete = 'Eliminar';
        $actionName_Info   = 'Información';
        $actionName_Download   = 'Descargar';

        $TableProperties = $this->SwapBytes_Html->convertToProperties('table', $table);

        $HTML  = "<table{$TableProperties}>";
        $HTML .= '<tr>';
        // Creamos el encabezado de la tabla.
        foreach($columns as $column) {
         
            if(empty($column['hide']) || (isset($column['hide']) && $column['hide'] == false)) {
                $ThProperties = $this->SwapBytes_Html->convertToProperties('th', $column);
                $HTML .= "<th{$ThProperties}>";
                if(is_string($column['name'])) {
                    $HTML .= $column['name'];
                } else if(isset($column['name']['control'])) {
                    $HTML .= $this->_toControl($column['name']);
                }
                $HTML .= '</th>';
            }
        }

        if(isset($actions)) {
            $HTML .= '<th>' . $actionName_Header . '</th>';
        }
        
        $HTML .= '</tr>';

        if(isset($rows)) {
         $onClickOther = array();
         $rowSum       = array();
            $rowOddOrEven = null;
            $rowColorTemp = $table['zebra']['colors']['odd'];

            foreach($rows as $rowNum => $row) {

                 $this->logger = Zend_Registry::get('logger');

                if(isset($actions)) {
                   $others = array();
                   $number_other = substr_count($actions, "O");
                   if(substr_count($actions, "O")){
                      foreach($otheractions as $otheraction){
                          
                         $operations = array('actionName' => '',
                            'onClick'    => '',
                            'label'      => '');
                         
                         
                        /*
                         * actionName sera el nombre de la accion asignada donde el valor de column sea null
                         * intrue es la accion para el caso contrario
                         */

                         if(isset($otheraction['validate'])){
                               
                            if($row[$otheraction['column']]== $otheraction['intrue']){
                               $operations['actionName'] = $otheraction['actionName'];
                               $operations['label']      = $otheraction['label'];
                            }else if($row[$otheraction['column']]){
                               if(isset($otheraction['intrue'])){
                                  $operations['actionName'] = $otheraction['intrue'];
                                  $operations['label']      = $otheraction['intruelabel'];
                               }
                            }
                         }else{
                               $operations['actionName'] = $otheraction['actionName'];
                               $operations['label']      = $otheraction['label'];
                         }
                         if(isset($otheraction['action'])){
                             $otheraction['action'] = str_replace("##pk##", $row[$columnPrimary], $otheraction['action']);
                             $operations['onClick'] = $otheraction['action'];
                         }else{
                             $onClickO = "newwindow = window.open(\"{$controllerName}/{$operations['actionName']}/id/{$row[$columnPrimary]}\", '{$row[$columnPrimary]}');if (window.focus) {newwindow.focus()}";
                             $onClickO = htmlentities($onClickO , ENT_QUOTES);
                             $operations['onClick'] = $onClickO;
                         }
                        $urlOther = $controllerView->url(array('controller'=>$controllerName, 'action'=>$operations['actionName'], 'id'=>$row[$columnPrimary]));
                         //Modifcar
//                         $onClickO = "window.location.href = \"{$controllerName}/{$operations['actionName']}/id/{$row[$columnPrimary]}\";";
                         $hrefO = "#";
                         $operations['hrefO'] = $hrefO;
                         array_push($others, $operations);
                         
                      }
                   }
               }

                $TrProperties = '';
                // Aplicamos una condicion para definir las nuevas propiedades del TR en la tabla a crear.
                if(isset($table['rows']['conditions'])) {
                    foreach($table['rows']['conditions'] as $condition) {
                        if($condition['equal'] == $row[$table['column']]) {
                            $TrProperties = $this->SwapBytes_Html->convertToProperties('tr', $condition['properties']);
                        }
                    }
                }
                
                // Se crea la tabla.
                $HTML .= "<tr{$TrProperties}{$rowOddOrEven}>";
                foreach($columns as $column) {
                    $TdProperties = '';
                    if(empty($column['hide']) || (isset($column['hide']) && $column['hide'] == false)) {
                        if(isset($column['rows'])) {
                            $TdProperties = $this->SwapBytes_Html->convertToProperties('td', $column['rows']);
                        }
                        
                        // Agregamos un control de tipo HTML a la lista.
                        if(isset($column['control'])) {
                            
                            // Aplicamos una condicion para definir las nuevas propiedades a un control.
                            if(isset($column['control']['conditions'])) {
                                foreach($column['control']['conditions'] as $condition) {                                    
                                    if(is_array($condition['properties']) && $condition['equal'] == $row[$column['column']]) {
                                        $column['control'] = array_merge($column['control'], $condition['properties']);
                                    }
                                    if (is_object($condition['callBack']) && ($condition['callBack'] instanceof Closure) ) {
                                        if(is_array($condition['properties']) && $condition['callBack']($row[$column['column']]) )  {
                                            $column['control'] = array_merge($column['control'], $condition['properties']);
                                        }
                                    }
                                }
                            }
                             
                            // Se busca si existe algun valor que remplzar por el que proviene de la Base de Datos.
                            // Estos valores son los que se encuentran encerrados con dobles #, Ej.: ##nombre##
                            foreach($columns as $columnTemp) {
                                
                                $this->SwapBytes_Array->replace_recursive("##{$columnTemp['column']}##", $row[$columnTemp['column']], $column['control']);
                            }

                            // SELECT: Creamos el HTML de los option del Control Select.
                            if($column['control']['tag'] == 'select' && is_array($column['control']['options'])) {
                                $column['control']['html'] = $this->SwapBytes_Html->selectOptions($column['control']['options'], $row[$column['column']]);
                            }
                            //IMAGEN
                            if($column['control']['tag'] == 'img') {
                                $column['control']['html'] = $this->SwapBytes_Html->img($row[$column['column']]);
                            }

                            // Asignamos un control y su respectivo valor.
                            // echo "segundo";var_dump($column);
                            $HTML .= "<td{$TdProperties}>";
                            $HTML .= $this->_toControl($column);
                            $HTML .= "</td>";
                        // Simplemente mostramos el valor.
                        } else {
                            // Agregamos un contador a los registros si es solicitado.
                            if(isset($column['function']) && $column['function'] == 'rownum') {
                                $row[$column['column']] = $rowNum+1;
                            }

                            // Sumamos el valor de una columna si es solicitado.
                            if(isset($this->footer)){
                                if($this->footer[0]['column'] == $column['name']) {
                                    $rowSum[] = (int)$row[$column['column']];
                                }
                            }
                            // Formateamos el texto a ser mostrado devidamente en HTML.
                            $value = $row[$column['column']];

                            if(is_array($value)){
                                if($value['condition']['isInasis']){
                                    $inasistencias = str_replace(array("\r\n", "\n", "\r"), '<br />', $value['nota']);
                                    $inasistencias = (Float) $inasistencias;
                                    $limite = $value['condition']['limite'];
                                    if($inasistencias >= $limite){
                                        $HTML .= "<td{$TdProperties} data=\"reprobado\">" . $inasistencias . "</td>";
                                    }else{
                                        $HTML .= "<td{$TdProperties}>" . $inasistencias . "</td>";
                                    }
                                }else{
                                    $value = str_replace(array("\r\n", "\n", "\r"), '<br />', $value['nota']);
                                    $HTML .= "<td{$TdProperties}>" . $value . " </td>";
                                }
                                
                            }else{
                                if($column['name'] == 'estado'){
                                    $value = str_replace(array("\r\n", "\n", "\r"), '<br />', $value);
                                    $HTML .= "<td{$TdProperties} data=\"".$value."\">" . $value . " </td>";
                                }else{
                                    $value = str_replace(array("\r\n", "\n", "\r"), '<br />', $value);
                                    $HTML .= "<td{$TdProperties}>" . $value . " </td>";
                                }
                                
                            }

                        }
                    }
                }

                $HTML .= '</tr>';
            }

        } else {
            return null;
        }
        $HTML .= '</table>';

        return $HTML;
    }

    //fill para llenar tabla de reinsciopciones
    public function fillReinscripcion(){

        $controllerView  = Zend_Layout::getMvcInstance()->getView();
        $controllerName  = $this->controller->getRequest()->getControllerName();
        $columnPrimary   = $this->_getColumnPrimary($columns);



    }

}
?>
