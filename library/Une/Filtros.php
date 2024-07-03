<?php

/**
 * Clase que permite crea y controla los filtros para ayudar a listar el contenido
 * de las tablas, permite realizar busquedas mediante los filtros seleccionados,
 * dichos filtros pueden ser recursivos, por otro lado se tiene una serie de botones
 * denominados acciones, las cuales permiten agregar, buscar, limpiar, copiar,
 * pegar y eliminar los registros de una tabla. En esta clase se crea todo el codigo
 * HTML y JavaScript necesario. Esta orientado su uso a jQuery.
 *
 * @category Une
 * @package  Une_Filtros
 * @version  0.3
 * @author   Nicola Strappazzon C., nicola51980@gmail.com, http://nicola51980.blogspot.com
 */
define('FILTER_TYPE_SECCION_TODOS', 1);
define('FILTER_TYPE_SECCION_PADRES', 2);

class Une_Filtros {

    public function __construct() {
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Pensums');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbView_Sedes');
        Zend_Loader::loadClass('Models_DbView_Semestres');
        Zend_Loader::loadClass('Models_DbView_Secciones');
        Zend_Loader::loadClass('Models_DbView_Turnos');
        Zend_Loader::loadClass('Models_DbView_Dias');

        $this->periodos = new Models_DbTable_Periodos();
        $this->pensums = new Models_DbTable_Pensums();
        $this->asignaciones = new Models_DbTable_Asignaciones();
        $this->asignaturas = new Models_DbTable_Asignaturas();
        $this->sedes = new Models_DbTable_Estructuras();
        $this->escuelas = new Models_DbTable_EstructurasEscuelas();
        $this->vw_sedes = new Models_DbView_Sedes();
        $this->vw_semestres = new Models_DbView_Semestres();
        $this->vw_secciones = new Models_DbView_Secciones();
        $this->vw_turnos = new Models_DbView_Turnos();
        $this->vw_dias = new Models_DbView_Dias();

        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Html = new SwapBytes_Html();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        /*
         * Obtenemos todos los parametos que son enviados por los filtros.
         */
        //$this->Data['periodo']    = $this->periodos->getUltimo();
        $this->Data['periodo']  = $this->Request->getParam('periodo');
        $this->Data['pensum']   = $this->Request->getParam('pensum');
        $this->Data['sede']     = $this->Request->getParam('sede');
        $this->Data['escuela']  = $this->Request->getParam('escuela');
        $this->Data['semestre'] = $this->Request->getParam('semestre');
        $this->Data['materia']  = $this->Request->getParam('materia');
        $this->Data['seccion']  = $this->Request->getParam('seccion');

        /*
         * Cada elemento del filtro debe tener un ancho especifico, al crear la
         * tabla contendra el anchos total definidos de forma automatica y se
         * evitara que se descuadre, estos valores son definidos por defecto.
         */
        $this->properties['width']['label'] = '100';
        $this->properties['width']['periodo'] = '160';
        $this->properties['width']['sede'] = '100';
        $this->properties['width']['escuela'] = '140';
        $this->properties['width']['pensum'] = '060';
        $this->properties['width']['semestre'] = '050';
        $this->properties['width']['materia'] = '140';
        $this->properties['width']['turno'] = '080';
        $this->properties['width']['seccion'] = '040';
        $this->properties['width']['dia'] = '090';

        $this->properties['type']['seccion'] = FILTER_TYPE_SECCION_TODOS;
    }

    /**
     * Retorna el ancho total de la tabla en HTML segun los filtros habilitados.
     *
     * @return string
     */
    public function getWidth() {
        $sum = 0;
        $sum += $this->properties['width']['label'];
//      $sum += $this->properties['width']['buttons'];

        foreach ($this->properties['display'] as $displayIndex => $displayValue) {
            if ($displayValue == true) {
                $sum += $this->properties['width'][$displayIndex] + 4;
            }
        }

        if ($sum == $this->properties['width']['label'] && $this->properties['button']['search']) {
            $sum += $this->properties['width']['search'];
        }

        return $sum . 'px';
    }

    public function getColspan() {
        $count = 0;

        foreach ($this->properties['display'] as $displayIndex => $displayValue) {
            if ($displayValue == true) {
                $count++;
            }
        }

        if ($count == 0 && $this->properties['button']['search']) {
            $count++;
        }

        return $count;
    }

    /**
     * Define un parametro de busqueda por defecto.
     *
     * @param string $Name
     * @param string $Value
     * @return string
     */
    public function setParam($Name, $Value) {
        return $this->Data[$Name] = $Value;
    }

    /**
     * Retorna todos los parametros de busqueda utilizados en el filtro.
     *
     * @return array
     */
    public function getParams($Name=null, $custom=null) {
        $Params = $this->Request->getParam($Name? $Name: 'filters');
        $Params = $this->SwapBytes_Uri->queryToArray($Params);

        $Data['periodo'] = (!empty($Params['selPeriodo'])) ? $Params['selPeriodo'] : $this->Request->getParam('periodo');
        $Data['sede'] = (!empty($Params['selSede'])) ? $Params['selSede'] : $this->Request->getParam('sede');
        $Data['escuela'] = (!empty($Params['selEscuela'])) ? $Params['selEscuela'] : $this->Request->getParam('escuela');
        $Data['pensum'] = (!empty($Params['selPensum'])) ? $Params['selPensum'] : $this->Request->getParam('pensum');
        $Data['semestre'] = (!empty($Params['selSemestre'])) ? $Params['selSemestre'] : $this->Request->getParam('semestre');
        $Data['materia'] = (!empty($Params['selMateria'])) ? $Params['selMateria'] : $this->Request->getParam('materia');
        $Data['turno'] = (!empty($Params['selTurno'])) ? $Params['selTurno'] : $this->Request->getParam('turno');
        $Data['seccion'] = (!empty($Params['selSeccion'])) ? $Params['selSeccion'] : $this->Request->getParam('seccion');
        $Data['dia'] = (!empty($Params['selDia'])) ? $Params['selDia'] : $this->Request->getParam('dia');

        // agrego los datos de los filtros custom si necesita
        if (isset($custom) && $custom){
            foreach ($custom as $val) {
                $Data[$val] = (!empty($Params['sel'.ucfirst($val)])) ? $Params['sel'. ucfirst($val)] : $this->Request->getParam($val);
            }
        }
        return $Data;
    }

    /**
     * Define la visibilidad de cada filtro.
     *
     * @param boolean $periodo
     * @param boolean $sede
     * @param boolean $escuela
     * @param boolean $pensum
     * @param boolean $semestre
     * @param boolean $materia
     * @param boolean $turno
     * @param boolean $seccion
     * @param boolean $dia
     */
    public function setDisplay($periodo = false, $sede = false, $escuela = false, $pensum = false, $semestre = false, $materia = false, $turno = false, $seccion = false, $dia = false) {
        $this->properties['display']['periodo'] = $periodo;
        $this->properties['display']['sede'] = $sede;
        $this->properties['display']['escuela'] = $escuela;
        $this->properties['display']['pensum'] = $pensum;
        $this->properties['display']['semestre'] = $semestre;
        $this->properties['display']['materia'] = $materia;
        $this->properties['display']['turno'] = $turno;
        $this->properties['display']['seccion'] = $seccion;
        $this->properties['display']['dia'] = $dia;
    }

    /**
     * Define la propiedad inabilitada (disabled) de cada filtro.
     *
     * @param boolean $periodo
     * @param boolean $sede
     * @param boolean $escuela
     * @param boolean $pensum
     * @param boolean $semestre
     * @param boolean $materia
     * @param boolean $turno
     * @param boolean $seccion
     * @param boolean $dia
     */
    public function setDisabled($periodo = false, $sede = false, $escuela = false, $pensum = false, $semestre = false, $materia = false, $turno = false, $seccion = false, $dia = false) {
        $this->properties['disabled']['periodo'] = $periodo;
        $this->properties['disabled']['sede'] = $sede;
        $this->properties['disabled']['escuela'] = $escuela;
        $this->properties['disabled']['pensum'] = $pensum;
        $this->properties['disabled']['semestre'] = $semestre;
        $this->properties['disabled']['materia'] = $materia;
        $this->properties['disabled']['turno'] = $turno;
        $this->properties['disabled']['seccion'] = $seccion;
        $this->properties['disabled']['dia'] = $dia;
    }

    /**
     * Define cual de cada filtro sera recursivo con el siguiente.
     *
     * @param boolean $periodo
     * @param boolean $sede
     * @param boolean $escuela
     * @param boolean $pensum
     * @param boolean $semestre
     * @param boolean $materia
     * @param boolean $turno
     * @param boolean $seccion
     * @param boolean $dia
     */
    public function setRecursive($periodo = false, $sede = false, $escuela = false, $pensum = false, $semestre = false, $materia = false, $turno = false, $seccion = false, $dia = false) {
        $this->properties['recirsive']['periodo'] = $periodo;
        $this->properties['recirsive']['sede'] = $sede;
        $this->properties['recirsive']['escuela'] = $escuela;
        $this->properties['recirsive']['pensum'] = $pensum;
        $this->properties['recirsive']['semestre'] = $semestre;
        $this->properties['recirsive']['materia'] = $materia;
        $this->properties['recirsive']['turno'] = $turno;
        $this->properties['recirsive']['seccion'] = $seccion;
        $this->properties['recirsive']['dia'] = $dia;
    }

    /**
     * Retorna todas las propiedades de los filtros.
     *
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * Indica a un filtro en especifico, que los datos a llenar en la lista son
     * todos los valores, o los padres, o los hijos, entiendase que hablamos de
     * cascada.
     *
     * @param string $FilterName
     * @param int    $Type
     */
    public function setType($FilterName, $Type) {
        $this->properties['type'][$FilterName] = $Type;
    }

    /**
     * Solo para ser usado mediante una llamada de tipo AJAX, permite llenar las
     * opciones de cada filtro que es solicitado.
     *
     * @param string $Params
     */
    public function getAction($Params = [], $callBack=null) {
        $ActionName = $this->Request->getActionName();
        $this->asignaciones->setData($this->Data, $Params);

        switch ($ActionName) {
            case 'periodo':
                if ($this->properties['display']['periodo']) {
                    if(!empty($Params)){
						if (isset($Params['regimen']) && $Params['regimen']) {
						// Regimen de Evaluaciones Calificaciones Parciales
							$dataRows = $this->asignaciones->getSelectPeriodosRegimenes();
						}else {
                        	$dataRows = $this->asignaciones->getSelectPeriodos();
						}
                    }
                    else{
                        $dataRows = $this->periodos->getSelect(10);
                    }
                }
                break;
            case 'sede':
                if ($this->properties['display']['sede']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectSedes();
                    } else {
                        $dataRows = $this->vw_sedes->get();
                    }
                }
                break;
            case 'escuela':
                if ($this->properties['display']['escuela']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectEscuelas();
                    } else {
                        $dataRows = $this->escuelas->getSelect($this->Data['sede']);
                    }
                }
                break;
            case 'pensum':
                if ($this->properties['display']['pensum']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectPensums();
                    } else {
                        $dataRows = $this->pensums->getSelect($this->Data['escuela']);
                    }
                }
                break;
            case 'semestre':
                if ($this->properties['display']['semestre']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectSemestres();
                    } else {
                        $dataRows = $this->vw_semestres->get();
                    }
                } break;
            case 'materia':
                if ($this->properties['display']['materia']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectMaterias();
                    } else {
                        $dataRows = $this->asignaturas->getSelect($this->Data['pensum'], $this->Data['semestre']);
                    }
                }
                break;
            case 'seccion':
                if ($this->properties['display']['seccion']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectSecciones();
                    } else {
                        $dataRows = $this->vw_secciones->get();
                    }
                }
                break;
            case 'turno':
                if ($this->properties['display']['turno']) {
                    if (!empty($Params)) {
                        $dataRows = $this->asignaciones->getSelectTurnos();
                    } else {
                        $dataRows = $this->vw_turnos->get();
                    }
                }
                break;
            case 'dia':
                if ($this->properties['display']['dia']) {
                    if (!empty($Params)) {

                    } else {
                        $dataRows = $this->vw_dias->get();
                    }
                }
                break;
        }
        if (isset($callBack) && (is_object($callBack) && ($callBack instanceof Closure))) {
            $dataRows = $callBack($dataRows);
        }
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    /**
     * Genera el codigo JavaScript para habilitar la funcionalidad de cascada en
     * los filtros y los botones de acción sobre los registros.
     *
     * @param string $RenderDiv
     * @param string $Functions
     * @param string $Path
     * @return string
     */
    public function getJavaScript($RenderDiv, $Functions = null) {
        $js = '';

        if (isset($this->properties['display'])) {
            foreach ($this->properties['display'] as $index => $value) {
                if ($this->properties['recirsive'][$index] == true) {
                    $display[] = $index;
                }
            }

            if( isset($this->properties['custom'])) {
              foreach ($this->properties['custom']  as $id => $filter) {
                if ($filter['recursive'] == true) {
                  $display[] = strtolower($id);
                  $customRecursive = true;
                }
              }
            }
            /*
             * Crea los filtros dinamicos.
             */
            if ($this->properties['recirsive']['periodo'] ||
                    $this->properties['recirsive']['sede'] ||
                    $this->properties['recirsive']['escuela'] ||
                    $this->properties['recirsive']['pensum'] ||
                    $this->properties['recirsive']['semestre'] ||
                    $this->properties['recirsive']['materia'] ||
                    $this->properties['recirsive']['turno'] ||
                    $this->properties['recirsive']['seccion'] ||
                    $this->properties['recirsive']['dia'] ||
                    $customRecursive) {
                
                $arFilters = "'" . implode($display, "','") . "'";
                $js .= "arraySelects = new Array({$arFilters});";
                $js .= "eval(fillSelectRecursive(urlAjax, arraySelects, 0));";

                foreach ($display as $index => $value) {
                    if ($index < (count($display) - 1)) {
                        $js .= '$("#sel' . ucfirst($value) . '").change(function(){eval(fillSelectRecursive(urlAjax, arraySelects, ' . ($index + 1) . '));$("#' . $RenderDiv . '").html(""); ' . $Functions . '});';
                    } else {
                        $js .= '$("#sel' . ucfirst($value) . '").change(function(){$("#' . $RenderDiv . '").html(""); ' . $Functions . '});';
                    }
                }
            }

            /*
             * Asigna el evento de desabilitar los actions a los filtros.
             */
            foreach ($this->properties['display'] as $index => $value) {
                if ($this->properties['recirsive'][$index] == false) {
                  $js .= '$("#sel' . ucfirst($index) . '").change(function(){$("#' .
                    $RenderDiv . '").html(""); ' . $buttonAction . $Functions . '});';
                }
            }
        }
        // js acciones para los filtros custom
        // con el parametro action
        if (isset($this->properties['custom'])) {
          foreach($this->properties['custom'] as $id => $filter) {
            if(isset($filter['action']))
              $js .= "$('#{$id}').change(function(e){ {$recursive} {$filter['action']}});";
          }
        }
        return $js;
    }

    public function getJson() {
        $json = array();

        if ($this->properties['button']['add'])
            $json[] = "$('#btnAgregar').button({ disabled: false })";
        if ($this->properties['button']['delete'])
            $json[] = "$('#btnEliminar').button({ disabled: false })";
        if ($this->properties['button']['copy'])
            $json[] = "$('#btnCopiar').button({ disabled: false })";
        if ($this->properties['button']['paste'])
            $json[] = "$('#btnPegar').button({ disabled: false })";

        return $json;
    }

    /**
     * Genera el codigo HTML para ser impreso en la vista, no todos los filtros
     * se pueden generar, debigo a que algunos requieren de valores de sus padres.
     *
     * @param string $Name
     */
    public function getHtml($Name) {
        if (isset($this->properties['recirsive'][$Name])) {
            if ($this->properties['recirsive'][$Name] == false) {
                switch ($Name) {
                    case 'periodo':
                        $dataRows = $this->periodos->getSelect(10);
                        break;
                    case 'sede':
                        $dataRows = $this->vw_sedes->get();
                        break;
                    case 'escuela':
                        // requiere de paso de parametros.
                        break;
                    case 'pensum':
                        // requiere de paso de parametros.
                        break;
                    case 'semestre':
                        $dataRows = $this->vw_semestres->get();
                        break;
                    case 'materia':
                        // requiere de paso de parametros.
                        break;
                    case 'seccion':
                        switch ($this->properties['type']['seccion']) {
                            case FILTER_TYPE_SECCION_TODOS:
                                $dataRows = $this->vw_secciones->get();
                                break;
                            case FILTER_TYPE_SECCION_PADRES:
                                $dataRows = $this->vw_secciones->getPadres();
                                break;
                        }
                        break;
                    case 'turno':
                        $dataRows = $this->vw_turnos->get();
                        break;
                    case 'dia':
                        $dataRows = $this->vw_dias->get();
                        break;
                }

                if (isset($dataRows)) {
                    echo $this->SwapBytes_Html->selectOptions($dataRows);
                }
            }
        }
    }

    public function addCustom($custom) {
      if (!is_array($custom)) return;
      foreach($custom as $filter) {
        if (is_array($filter)) {
          foreach($filter as $key => $value) {
            if($key == 'id') continue;
            $this->properties['custom'][$filter['id']][$key] = $value;
          }
        }
      }
    }
}
