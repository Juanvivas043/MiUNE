<?php 

  class Reports_HorariodelprofesorController extends Zend_Controller_Action {

    public function init() {
        //instanciar clases
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Inscripciones');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Descargables');
        Zend_Loader::loadClass('Zend_Pdf');        

        //instanciar models
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->Record           = new Models_DbTable_Recordsacademicos();
        $this->Inscripcion      = new Models_DbTable_Inscripciones();
        $this->Horario          = new Models_DbTable_Horarios();
        $this->atributos        = new Models_DbTable_Atributos();
        $this->inscripciones    = new Models_DbTable_Inscripciones();
        $this->Periodo          = new Models_DbTable_Periodos();
        $this->Descargables     = new Models_DbTable_Descargables();
        $this->pdf              = new Zend_Pdf();
        $this->Une_Filtros      = new Une_Filtros();

        //se instancia request que es el que maneja los _params
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->Swapbytes_array          = new SwapBytes_Array();
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();

        /*Filtros*/
        $this->Une_Filtros->setDisplay(true, true);
        $this->Une_Filtros->setRecursive(true, true);
        /*Botones de Acciones*/
        $this->SwapBytes_Crud_Action->setDisplay(true,false);
        $this->SwapBytes_Crud_Action->setEnable(true,false);
        $this->SwapBytes_Crud_Action->addCustum("<button id=\"btnImprimir\" class=\"ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only\"
                                                          name=\"btnImprimir\" role=\"button\" aria-disabled=\"false\">Imprimir Horario</button>");
        $this->SwapBytes_Crud_Action->addJavaScript("$('#btnImprimir').click(function(){window.print()});");

        $this->SwapBytes_Crud_Search->setDisplay(false);
        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
        
        $customFilters = array(
          array(
           'id' => 'profesor',
           'name' => 'selProfesor',
           'label' => 'Profesor',
           'recursive' => true,
           'action' => 'alert("hola");'
          )    
        );

        $this->Une_Filtros->addCustom($customFilters);
    }   
    
  public function indexAction() {

    $this->view->title   = 'Reportes \ Horario del profesor';
    $this->view->filters = $this->Une_Filtros;
    $this->view->SwapBytes_Jquery         = $this->SwapBytes_Jquery;
    $this->view->SwapBytes_Crud_Action    = $this->SwapBytes_Crud_Action;
    $this->view->SwapBytes_Crud_Form      = $this->SwapBytes_Crud_Form;
    $this->view->SwapBytes_Crud_Search    = $this->SwapBytes_Crud_Search;
    $this->view->SwapBytes_Ajax           = new SwapBytes_Ajax();
    $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

  }

  public function periodoAction() {
    $this->Une_Filtros->getAction();
  }

  public function sedeAction() {
    $this->Une_Filtros->getAction(array('periodo'));
  }

  public function profesorAction() {
    if ($this->_request->isXmlHttpRequest()) {
      $json = array();
      $this->SwapBytes_Ajax->setHeader();
      $params  = $this->Une_Filtros->getParams();
      $profesores = $this->Usuarios->getProfesoresPeriodoSede($params['periodo'], $params['sede']);
      $this->SwapBytes_Ajax_Action->fillSelect($profesores);
   
    }
  }

  function preDispatch() {
    if (!Zend_Auth::getInstance()->hasIdentity()) {
      $this->_helper->redirector('index', 'login', 'default');
    }
    if (!$this->grupo->haveAccessToModule()) {
      $this->_helper->redirector('accesserror', 'profile', 'default');
    }
  }

  public function listAction(){
    if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();
      $params  = $this->Une_Filtros->getParams(null, array('profesor'));
       if ($params['profesor'] == null)
        $params['profesor'] = 0;
        $html = $this->horarioProfesor($params['profesor'], $params['periodo'],$params['sede']);
        $json[] = $this->SwapBytes_Jquery->setHtml('tblHorarios',addslashes($html));
        $per = $params['periodo'];
        
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
  }

  public function horarioProfesor($profesor,$periodo,$sede){
    $ci = $profesor;
    $this->result = $this->Horario->getHorarioPersona($ci,$periodo,'prof',$sede);
    $prof = $this->Usuarios->getUsuario($ci);
    $dias = array();
    $midia = 0;
    $clases = array();
    $timesStart = array();
    $timesEnd=array();
    $params = $_params['filters'];

        if ($sede == 7) {        
            $sedePrint='Naranjos';
        }
        elseif ($sede == 8) {
            $sedePrint='Centro';
        }

    $horas= $this->Horario->gethorasprofesor($ci, $periodo,$sede);
    //si no tiene horas asignadas o el query retorna nulo entonces se genera un mensaje de alerta

    //cada for revisa, si existe alguna marteria en esa hora se agrega la clase 'clase' en caso contrario se agrega la clase stripe 
    //las coincidencias estan validadas en el query, si posee mas de una materia se tendra al menos una coma en el nombre de la materia si ese es el caso entonces se agrega la clase 'coincidencias' que les da el color rojo
    //se recorre cada row de forma independiente ya que los datos referentes a cada hora por dia se encuentran cada 8 datos 0,8,16 o 1,9,17 y se insertan los td correspondientes a cada dia realizando las comprobaciones requeridas 
    
    if($horas[0]['cant_horas']!=0 and $horas[0]['cant_horas'] != NULL and $horas[0]['cant_horas']!= False){
       $HTML=" <div id='encabezados'><div class='sign'>Sede: $sedePrint </div>".
        "<div class='sign'> Periodo Académico: {$this->Une_Filtros->getParams()["periodo"]} / {$periodoPrint}</div>".
        "<div class='sign profesor'>Profesor(a): {$prof['primer_apellido']}  {$prof['primer_nombre']}</div>".
        "<div class='sign'> Función: _________________________ Dedicación:_______________ T/C___PH___Celular:__________________</div>".
        "</div>".
        "<div></div>";
        $HTML.= '<table><tr><th>Horas</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>';
        $HTML.='<tr><td class="h">7:00 - 8:30</td>';
        for ($i=0; $i < 41; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">8:40 - 10:10</td>';
        for ($i=1; $i < 42; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">10:15 - 11:45</td>';
        for ($i=2; $i < 43; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
               $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">12:00 - 1:30</td>';
        for ($i=3; $i < 44; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">1:35 - 3:05</td>';
        for ($i=4; $i < 45; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">3:10 - 4:40</td>';
        for ($i=5; $i < 46; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">5:00 - 6:30</td>';
        for ($i=6; $i < 47; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';

        $HTML.='<tr><td class="h">6:30 - 8:00</td>';
        for ($i=7; $i < 48; $i=$i+8) { 
            if($this->result[$i]['materia']==''){
                $HTML.='<td class="stripe" ><p></p></td>';
            }else{
                $mat = $this->result[$i]['materia'];
                if(strpos($mat, ",")==true){
                    $mat =  str_replace(',', '<br>', $mat);
                    $HTML.='<td class="coincidencias"><p>COINCIDENCIA DE:</p><p>'.$mat.'</p></td>';  
                }
                else{
                    $HTML.='<td class="clases"><p>'.$mat.'</p></td>';
                }
            }
        }
        $HTML.='</tr>';


        
        $HTML.='</table>';
        $HTML.='<div class="horas"><p>TOTAL DE HORAS:'. $horas[0]['cant_horas'] .'</p></div>';
        $HTML .= "<div class='clear'><br></div>";
        $HTML .= "<div class='firma2 firma'>Firma Autorizada: _____________</div><div class='firma2 fr'>Firma del Profesor: _____________</div>";
    }else{
        $HTML = '<div class="alert">Usted no tiene materias asignadas para este periodo</div>';
    }
   return $HTML;
  }   
}
