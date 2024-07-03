<?php

class Transactions_DefensatesisController extends Zend_Controller_Action {

	private $_Title   = 'Transacciones \ Defensa Tesis';
    
    
	

	public function init() {

        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('CmcBytes_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Usuarios'); 
        Zend_Loader::loadClass('Models_DbTable_Pasantes'); 
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Materiasestados');
        Zend_Loader::loadClass('Models_DbView_Grupos');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Forms_HorarioTesis');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->Horario = new Models_DbTable_Horarios();
        $this->materiasestados = new Models_DbTable_Materiasestados();
        $this->atributos       = new Models_DbTable_Atributos();
        $this->filtros          = new Une_Filtros();
        $this->CmcBytes_Filtros = new CmcBytes_Filtros();
        $this->vw_grupos        = new Models_DbView_Grupos();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  
        $this->CmcBytes_Redirector          = new CmcBytes_Redirect();
        
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');  

        $this->_params['redirect'] = $this->redirect_session->params;

        
        $this->view->form = new Forms_HorarioTesis();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();



//      BOTONES DE ACCIONES
                
        $this->SwapBytes_Crud_Action->setDisplay(false, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(false, false, false, false, false, false);
        $this->SwapBytes_Crud_Search->setDisplay(false);

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnRegresar" onclick="regresar()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnRegresar" role="button" aria-disabled="false">
                                                Regresar
                                                </button>');



        $this->SwapBytes_Crud_Action->addCustum('<button id="btnActa" onclick="imprimir()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnActa" role="button" aria-disabled="false">
                                            Actas
                                            </button>');

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnReHorario" onclick="reprogramar()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnReHorario" role="button" aria-disabled="false">
                                            Reprogramar Defensa
                                            </button>');
        

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnHorario" onclick="horario()"  class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnHorario" role="button" aria-disabled="false">
                                                Disponibilidad Horario
                                                </button>');        

	}

function preDispatch() {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
       if (!$this->grupo->haveAccessToModule()) {
           $this->_helper->redirector('accesserror', 'profile', 'default');
         }    
    }


    public function indexAction() {

        $this->view->title                 = $this->_Title;
        $this->view->filters               = $this->filtros;
        // $this->view->info                  = $this->masterInfo();
        $this->view->info                  = '<div id ="info"></div>';
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
     


    } 

    private function masterInfo(){

            $cod = $this->_params['redirect']['cod'];
            $periodo = $this->_params['redirect']['periodo']; 
            $sede = $this->_params['redirect']['sede'];
            $escuela = $this->_params['redirect']['escuela'];

            $info = $this->tesis->getTesisConDefensa($escuela,$periodo,null,null,$cod,$sede);
            $defensainfo = $this->tesis->getDatosDefensa($cod,$periodo);

            $horario = explode('-', $defensainfo[0]['horainicio']);

            $temp_horario = array();

            foreach ($horario as $h) {
                $h = date('h:i:s a', strtotime($h));

                $temp_horario[] = $h;
            }

            $horario = $temp_horario;



            //infro de los tesistas
            $html .= "<table class=\"tableData\" style=\"width:600px;\">";
            $html .= "<tbody>";

            $html .= "<tr>";
            $html .= "<p style=\"text-align:center;font-size:16px\"><b>INFORMACION TESIS</b></p>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<b>Periodo: </b>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $periodo;
            $html .= "</td>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<b>Titulo: </b>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $info[0]['titulo'];
            $html .= "</td>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<b>Cedula(s): </b>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $info[0]['cedula'];
            $html .= "</td>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<b>Autor(es): </b>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $info[0]['autor'];
            $html .= "</td>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<b>Tutor: </b>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $info[0]['tutor'];
            $html .= "</td>";
            $html .= "</tr>";

            $html .= "</tbody>";
            $html .= "</table>";
            $html .= "<br>";

            if(!empty($defensainfo)){

                //datos de la defensa
                $fecha = str_replace('/', '-', $defensainfo[0]['fecha']);
                $fecha = date_create($fecha);
                $fecha =  date_format($fecha, 'd-m-Y');



                $html .= "<table class=\"tableData\" style=\"width:600px;\">";
                $html .= "<tbody>";

                $html .= "<tr>";
                $html .= "<p style=\"text-align:center;font-size:16px\"><b>DATOS DEFENSA</b></p>";
                $html .= "</tr>";

                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<b>Fecha: </b>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= $fecha;
                $html .= "</td>";
                $html .= "</tr>";

                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<b>Hora: </b>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= $horario[0] . ' - '. $horario[1];
                $html .= "</td>";
                $html .= "</tr>";

                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<b>Edificio: </b>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= $defensainfo[0]['edif'];
                $html .= "</td>";
                $html .= "</tr>";

                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<b>Aula: </b>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= $defensainfo[0]['aula'];
                $html .= "</td>";
                $html .= "</tr>";

                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<b>Evaluadores: </b>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= $defensainfo[0]['evaluador'];
                $html .= "</td>";
                $html .= "</tr>";

                $html .= "</tbody>";
                $html .= "</table>";
                $html .= "<br>";

            }
            

            return $html;
    }

    public function listAction(){
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $cod = $this->_params['redirect']['cod'];
            $periodo = $this->_params['redirect']['periodo'];
            $sede = $this->_params['redirect']['sede'];
            $escuela = $this->_params['redirect']['escuela'];

            $rows = $this->tesis->getEvaluadoresDefensa($cod, $periodo);
            $defensainfo = $this->tesis->getDatosDefensa($cod,$periodo);
            $masterinfo = $this->masterinfo();
            
            if(isset($rows) && count($rows) > 0) {

                $HTML = $this->evaluadores($cod,$periodo);
                
            }else{

                $HTML .= $this->SwapBytes_Html_Message->alert("No hay evaluadores asignados");  
            } 

            
            $json[] = "$('#tableData').html('".$HTML."');";
            $json[] = "$('#info').html('".$masterinfo."');";

            if(!empty($defensainfo)){//cuando editas una defensa
                $json[] = "$('#btnReHorario').show()";
                $json[] = "$('#btnHorario').hide()";
                $json[] = "$('#btnActa').show()";
                $json[] = "$('.check').attr('disabled', true);";

            }else{//cuando agregar una defensa nueva
                $json[] = "$('#btnReHorario').hide()";
                $json[] = "$('#btnHorario').show()";
                $json[] = "$('#btnActa').hide()";
                $json[] = "$('.check').attr('disabled', false);";
            }            

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));  

            

    }

    }



  public function regresoAction() {

      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();

      $data = array( 'module'=>'transactions',
                     'controller'=>'asignardefensa',
                      'params'=>array('action' => 'listar', 
                                      'periodo' => $this->_params['redirect']['periodo'],
                                      'sede' => $this->_params['redirect']['sede'],
                                      'escuela' => $this->_params['redirect']['escuela'],
                                      'estado' => '19964',
                                      'set' => 'true'
                                      )
              );

        $json[] = $this->CmcBytes_Redirector->getRedirect($data);
        unset($_SESSION['filterBack']);
        $_SESSION['counter'] = 1;

        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

  }


  public function addoreditloadAction(){
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        
        $datos = $this->_getAllParams();

        // $evaluadores = $this->SwapBytes_Uri->queryToArray($datos['evaluadores']);
        // $evaluadores = $evaluadores['row'];

        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $sede = $this->_params['redirect']['sede'];

        $defensa = $this->tesis->getDefensa($cod,$periodo);

        if(!empty($defensa)){//estamos editando

            //transformo la fecha para que se cargue en el formulario
            $defensa[0]['fecha'] = str_replace('-', '/', $defensa[0]['fecha']);
            $dataRow['fecha'] = date_create($defensa[0]['fecha']);
            $dataRow['fecha'] =  date_format($dataRow['fecha'], 'd/m/Y');

            $edificio = $this->tesis->getEstructura($defensa[0]['fk_estructura']);//para conseguir el pk del edificio
            
            $dataRow['estructura'] = $defensa[0]['fk_estructura'];

            //debo transformar la fecha nuevamente para la funcion de los horarios
            $defensa[0]['fecha'] = str_replace('/', '-', $defensa[0]['fecha']);
            $defensa[0]['fecha'] = date_create($defensa[0]['fecha']);
            $defensa[0]['fecha'] =  date_format($defensa[0]['fecha'], 'd-m-Y');
            
            $html = $this->horario($datos['evaluadores'],$defensa[0]['fecha'],$edificio[0]['pk_estructura_1'],$defensa[0]['fk_estructura'],1);

        }else{//estamos agregando
            $dataRow['fecha'] = date('d/m/o');

            $aulas = $this->tesis->getAulas(9);
            $edificios = $this->tesis->getEdificios($sede);

            $html = $this->horario($datos['evaluadores'],date('d-m-o'),null,null,1);
        }


        $dataRow['evaluadores'] = $datos['evaluadores'];

        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Horario');

        
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
        
        $json[] = '$(\'#horariotesis\').append(\'<div id="tableHorario"></div>\')';
        $json[] = '$(\'#tableHorario\').append(\''.$html.'\')';

        $json[] = '$(\'#fecha-label\').hide()';
        $json[] = '$(\'#fecha-element\').hide()';

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getAddOrEditLoad(); 

        
        
    }
  } 

  public function addoreditconfirmAction(){

    if($this->_request->isXmlHttpRequest()){
        $this->SwapBytes_Ajax->setHeader();

        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $sede = $this->_params['redirect']['sede'];

        $datos = $this->_getAllParams();
        $this->_params['modal'] = $datos;


        $horario = $this->tesis->horas_academicas($this->_params['modal']['pk_horario']);

        $temp_horario = array();

        foreach ($horario as $h) {
            $h['hora_12h'] = date('h:i:s a', strtotime($h['hora']));
            $h['horafin_12h'] = date('h:i:s a', strtotime($h['horafin']));

            $temp_horario[] = $h;
        }

        $horario = $temp_horario;


        $evaluadores = $this->tesis->getEvaluadorDatosbyPk($this->_params['modal']['evaluadores']);
        $fecha = $this->fechasByDia($this->_params['modal']['fecha']);
        

        if(!empty($this->_params['modal']['estructura'])){
            $cod_edificio = $this->tesis->getEstructura($this->_params['modal']['estructura']);//para conseguir el pk del edificio    
        }else{
            $cod_edificio[0]['pk_estructura_1'] = null;
        }
        
        

        $fecha_defensa = date_create($this->_params['modal']['fecha']);
        $fechaformatoBD =  date_format($fecha_defensa, 'Y-m-d');

        // hay que llenarlo cuando se esta editando una tesis
        $edificiosHTML = $this->getEdificiosHTML($cod_edificio[0]['pk_estructura_1']);
        $aulasHTML = $this->getAulasHTML($cod_edificio[0]['pk_estructura_1'],$periodo,$this->_params['modal']['pk_horario'],$this->_params['modal']['fecha'],$this->_params['modal']['estructura']);
        


        $message .= '<input id = "pk_horario_confirm" type = "hidden" value = "'.$this->_params['modal']['pk_horario'].'">';


        $message .= '<table class="tableData" style="align: left;width:auto;">'; 
        $message .= '<tr>';
        $message .= '<td><b>Fecha: </b></td>';
        $message .= '<td>'.$fecha.'</td>';
        $message .= '</tr>';

        $message .= '<tr>';
        $message .= '<td><b>Edificio: </b></td>';
        $message .= '<td>'.$edificiosHTML.'</td>';
        $message .= '</tr>';

        $message .= '<tr>';
        $message .= '<td><b>Aula: </b></td>';
        $message .= '<td>'.$aulasHTML.'</td>';
        $message .= '</tr>';

        $message .= '<tr>';
        $message .= '<td><b>Horario: </b></td>';
        $message .= '<td>'.$horario[0]['hora_12h'] . ' - ' . $horario[0]['horafin_12h'].'</td>';
        $message .= '</tr>';

        foreach ($evaluadores as $key => $value) {
            $message .= '<tr>';
            $message .= '<td><b>Evaluador'.$key.': </b></td>';
            $message .= '<td>'.$value['apellido'].', '.$value['nombre'].'</td>';
            $message .= '</tr>';
        }

        $message .= '</table>';


        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal'],'Horario');
        $this->SwapBytes_Crud_Form->getAddOrEditConfirm();
        $this->SwapBytes_Crud_Form->getDialog('Seguro que deseas continuar?', $message, swYesNo);
      
    }
  }



  public function addoreditresponseAction(){
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $defensa = $this->tesis->getDefensa($cod,$periodo);

        $datos = $this->_getAllParams();
        
        
        $queryArray  = $this->SwapBytes_Uri->queryToArray($datos['data']);

        
        $fecha = str_replace('/', '-', $queryArray['fecha']);

        $fecha = date_create($fecha);
        $fechaformatoBD =  date_format($fecha, 'Y-m-d');

        

        if(empty($queryArray['estructura'])){//valida que no venga un aula ocupada
        // $this->SwapBytes_Crud_Form->getDialog('Advertencia', 'Aula Ocupada', swOkOnly);
            die;
        }


        if(!empty($queryArray['evaluadores'])){

            if(empty($defensa)){//para agregar

                $this->tesis->updateEvaluadortipo($queryArray['evaluadores'],$this->tesis->getAtributoEvaluadorPrincipal());
                $this->tesis->addDefensaTesis($periodo, $queryArray['estructura'], $queryArray['pk_horario'], $cod, $fechaformatoBD);      
            }else{//para editar

                $this->tesis->updateEvaluadoresPeriodo($cod,$periodo,$this->tesis->getAtributoEvaluadorSecundario());
                $this->tesis->updateEvaluadortipo($queryArray['evaluadores'],$this->tesis->getAtributoEvaluadorPrincipal());

                $this->tesis->updateDefensaTesis($cod,$periodo, $queryArray['estructura'], $queryArray['pk_horario'], $fechaformatoBD);      
            }
           
        }
        // $json[] = 'location.reload();';
        $this->SwapBytes_Crud_Form->setJson($json);
        $this->SwapBytes_Crud_Form->getAddOrEditEnd();
    }
    
       
    
  }


  public function newhorarioAction(){
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $datos = $this->_getAllParams();

        // $evaluadores = $this->SwapBytes_Uri->queryToArray($datos['evaluadores']);
        $evaluadores = $datos['evaluadores'];
        $evaluadores = $evaluadores['row'];
        
        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];

        $fecha = $this->_getParam('fecha');
        $edificio = $this->_getParam('edificio');
        $aula = $this->_getParam('aula');

        
        $fechaformatoBD = date_create($fecha);
        $fechaformatoBD =  date_format($fechaformatoBD, 'Y-m-d'); 


        
        // $fechavalida = $this->tesis->fechaValida($fechaformatoBD,$periodo);


        
        $html = $this->horario($evaluadores,$fecha,$edificio,$aula,1);

        // if(empty($fechavalida)){//valido que escoja una fecha que este dentro del semestre escogido en el modulo asignardefensa
        //     $html = '<div class="alert">La Fecha escogida no esta dentro del periodo '. $periodo .'</div>';            
        // }

        $json[] = $this->SwapBytes_Jquery->setHtml('tableHorario', $html);

        $this->getResponse()->setBody(Zend_Json::encode($json));
    }

  }


  public function aulaAction(){
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $edificio = $this->_getParam('edificio');
        $fecha = $this->_getParam('fecha');
        $horario = $this->_getParam('horario');
        $periodo = $this->_params['redirect']['periodo'];
        $cod = $this->_params['redirect']['cod'];


        $dia = $this->calcular_dia_de_la_semana($fecha);

        $aulas = $this->tesis->getAulas($edificio);

        $fechaBD = $fecha;
        $fechaBD = date_create($fechaBD);
        $fechaBD = date_format($fechaBD, 'Y-m-d');
        
        foreach ($aulas as $aula) {

            $asignacion = $this->tesis->getAsignaciones($periodo,$horario,$dia,$aula['pk_estructura']);
            $otra_defensa = $this->tesis->getDefensaDetallada($periodo,$horario,$fechaBD,$aula['pk_estructura'],$cod);

            $alias = $asignacion[0]['alias'];

            if(!empty($otra_defensa[0]['alias'])){
                $alias = $otra_defensa[0]['alias'];    
            }

            if(!empty($asignacion) || !empty($otra_defensa)){
                $ocupado = ' (ocupado) ' .$alias;
                $value = ' value = \"\">  ';
                
            }else{
                $ocupado = '';
                $value = ' value = \"'.$aula['pk_estructura'].'\">  ';
            }

            $html .= '<option '.$value.$aula['aula'].$ocupado.'</option>';
        }


        $json[] = "$('#aula').html('".$html."');";

        $this->getResponse()->setBody(Zend_Json::encode($json));

    }

   } 

   public function cambiartipoAction(){
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

        $datos = $this->_getAllParams();
        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $sede = $this->_params['redirect']['sede'];
        $escuela = $this->_params['redirect']['escuela'];

        if(empty($datos['tipo'])){//viene de la accion check de la vista
            
            if($datos['principal'] == '0'){
                $secundario = $this->tesis->getAtributoEvaluadorSecundario();
                $tipo = $secundario;
            }else{
                $principal = $this->tesis->getAtributoEvaluadorPrincipal();
                $tipo = $principal;
            }

        }else{

            $tipo = $datos['tipo'];
            
        }

        
        $this->tesis->updateEvaluadortipo($datos['evaluador'],$tipo);

        // $json[] = 'location.reload();';
        $masterinfo = $this->masterinfo();
        $HTML = $this->evaluadores($cod,$periodo);
        
        $json[] = "$('#info').html('".$masterinfo."');";
        $json[] = "$('#tableData').html('".$HTML."');";

        $this->SwapBytes_Crud_Form->setJson($json);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }  

   }

   public function reprogramarAction(){
       if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();

            $cod = $this->_params['redirect']['cod'];
            $periodo = $this->_params['redirect']['periodo'];
            $evaluadorsecundario = $this->tesis->getAtributoEvaluadorSecundario();


            $this->tesis->deleteDefensastesis($cod);//elimino la defensa
            $this->tesis->updateEvaluadoresPeriodo($cod,$periodo,$evaluadorsecundario);//cambio los evaluadores a secundarios




            $masterinfo = $this->masterinfo();
            $HTML = $this->evaluadores($cod,$periodo);
            
            $json[] = "$('#info').html('".$masterinfo."');";
            $json[] = "$('#tableData').html('".$HTML."');";
            
            $json[] = "$.getJSON(MyurlAjax + '/list', function(data){executeCmdsFromJSON(data)});";

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
   }


   public function imprimirAction(){ //Antes tenia estos 3 parametros $periodo,$sede,$escuela (quitados para actualizacion a php 7.2)
        mb_internal_encoding('UTF-8');

        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];

        $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/actadefensaTG.jasper';
        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
        
        $params      = "'logo=string:{$imagen}|periodo=string:{$periodo}|cod=string:{$cod}'";
        $filename    = 'actadefensaTG';
    
        
        $config = Zend_Registry::get('config');

        $dbname = $config->database->params->dbname;
        $dbuser = $config->database->params->username;
        $dbpass = $config->database->params->password;
        $dbhost = $config->database->params->host;


        
        $filetype    = 'PDF';//strtolower($Params['rdbFormat']);
        

        $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype }");
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );
        
        
        
        $outstream = exec($cmd);
        
        echo base64_decode($outstream); 
        
    }

  //-----------------------------------------funciones--------------------------------------
    function getEdificiosHTML($edificio){

        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $sede = $this->_params['redirect']['sede'];

        $edificios = $this->tesis->getEdificios($sede);


        $html .= '<select id = \"edificio\" onclick=\"changeedificio()\">';
        foreach ($edificios as $edif) {
            if($edif['pk_estructura'] == $edificio){
                $html .= '<option selected value = \"'.$edif['pk_estructura'].'\">'.$edif['edificio'].'</option>';      
            }else{
                $html .= '<option  value = \"'.$edif['pk_estructura'].'\">'.$edif['edificio'].'</option>';
            }

            
            
        }
        $html .= '</select>';

        return $html;

        
    }


        function getAulasHTML($edificio,$periodo,$horario,$fecha,$aula){

        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];
        $sede = $this->_params['redirect']['sede'];

        $aulas = $this->tesis->getAulas($edificio);
        
        $dia = $this->calcular_dia_de_la_semana($fecha);

        $fechaBD = $fecha;
        $fechaBD = date_create($fechaBD);
        $fechaBD = date_format($fechaBD, 'Y-m-d');

        $html .= '<select id = \"aula\" onclick=\"cambia_aula()\">';
        if(is_array($aulas)){
            foreach ($aulas as $a) {
                
                $asignacion = $this->tesis->getAsignaciones($periodo,$horario,$dia,$a['pk_estructura']);
                $otra_defensa = $this->tesis->getDefensaDetallada($periodo,$horario,$fechaBD,$a['pk_estructura'],$cod);
                
                $alias = $asignacion[0]['alias'];
                
                if(!empty($otra_defensa[0]['alias'])){
                    $alias = $otra_defensa[0]['alias'];    
                }
                
                

                if(!empty($asignacion) || !empty($otra_defensa)){
                    $ocupado = ' (ocupado) ' .$alias;
                    $value = ' value = \"\">  ';
                    
                }else{
                    $ocupado = '';
                    $value = ' value = \"'.$a['pk_estructura'].'\">  ';
                }


                if($a['pk_estructura'] == $aula){
                    $html .= '<option selected '.$value.$a['aula'].$ocupado.' </option>';
                }else{
                    $html .= '<option '.$value.$a['aula'].$ocupado.' </option>';
                }
                
                $alias = '';
            }
            
        }else{
            $html .= '<option>-------------</option>';
        }
        
        $html .= '</select>';

        return $html;

        
    }



     function evaluadores($cod,$periodo){
        
        $rows = $this->tesis->getEvaluadoresDefensa($cod, $periodo);
        
        $tipoevaluadores = $this->tesis->TipoEvaluadores();

        $evaluadorprincipal = $this->tesis->getAtributoEvaluadorPrincipal();
        

        $html .= '<table class="tableData" style="margin:0 auto;width:600px;">';
        $html .= '<tbody>';
        $html .= '<tr>'; 
        $html .= '<th></th>'; 
        $html .= '<th>CEDULA</th>';
        $html .= '<th>EVALUADOR</th>'; 
        $html .= '<th>ROL</th>';
        $html .= '<th>TIPO</th>'; 
        $html .= '</tr>'; 


        foreach ($rows as $key => $value) {

          //con esto se verifica si es evaluador principal o no
          foreach ($tipoevaluadores as $tipoevaluador) {

                if($value['pk_atributo_tipo'] == $evaluadorprincipal){
                        $checked = 'checked';    
                    }else{
                        $checked = '';
                    }
              
          }


          $html .= '<tr>'; 
          $html .= '<td style="text-align:center"><input type="checkbox" class="check" onclick = "set_tipo('.$value['pk_evaluadortesis'].', '.$key.')" name="chkEvaluadorTesis'.$key.'" id="chkEvaluadorTesis'.$key.'" value="'.$value['pk_evaluadortesis'].'" '.$checked.'></td>'; 
          $html .= '<td style="text-align:center">'.$value['cedula'].'</td>';
          $html .= '<td style="text-align:center">'.$value['evaluador'].'</td>'; 
          $html .= '<td style="text-align:center">'.$value['rol'].'</td>'; 
          $html .= '<td style="text-align:center">'.$value['tipo'].'</td>'; 
          $html .= '</tr>'; 


          $filtro_tipo = '';
        }


        $html .= '</tbody>';       
        $html .= '</table>';             

        
        return $html;

    }



    function fechasByDia($fecha){//esto es para obtener las fechas posibles a partir de una fecha pasada por parametro

        $fecha_inicial = $fecha;
        $periodo = $this->_params['redirect']['periodo'];

        $datosperiodo = $this->tesis->getPeriodo($periodo);

        $fechainicioperiodo = strtotime ($datosperiodo[0]['fechainicio']);
        $fechainicioperiodo = date('d-m-o', $fechainicioperiodo);

        $fechafinperiodo = strtotime ($datosperiodo[0]['fechafin']);
        $fechafinperiodo = date('d-m-o', $fechafinperiodo);


        $dia_de_semana_fecha    = $this->calcular_dia_de_la_semana($fecha);


        $fechahoy = date('d-m-o');
        $semana_completa_inicio = $this->calcular_semana_completa($fechahoy);



        foreach ($semana_completa_inicio as $value) {
            if($value['pk_atributo'] == $dia_de_semana_fecha){//verifico que sea el mismo  dia de la semana, para partir el calculo en base a esto
                $fecha = $value['fechadeldia'];
            }
        }

        
        $html .= '<select id = \"posiblesfechas\" onclick=\"cambia_fecha();changeedificio();\">';
        
        

        //32 = equivalente a dos periodos
        for ($i=1; $i < 16 ; $i++) { 

            if(strtotime ($fechaoption) > strtotime ($fechafinperiodo)){break;}

            if($i == 1){
                $fechaoption = $fecha;
                $html .= '<option  value = \"'.$fechaoption.'\">'.$fechaoption.'</option>';    
            }else{
                $nuevafecha = strtotime ('+7 day' , strtotime ($fechaoption) ) ;
                $nuevafecha = date ( 'd-m-o' , $nuevafecha );
                $fechaoption = $nuevafecha;
                
                if($fechaoption == $fecha_inicial){
                    $html .= '<option selected onclick=\"cambia_fecha('.$fechaoption.')\"  value = \"'.$fechaoption.'\">'.$fechaoption.'</option>';
                }else{
                    $html .= '<option  onclick=\"cambia_fecha('.$fechaoption.')\"  value = \"'.$fechaoption.'\">'.$fechaoption.'</option>';
                }
                 
            }
            
            
            
        }
        
        $html .= '</select>';
        

        return $html;
        
    }


    function calcular_dia_de_la_semana($fecha){
        
        $fecha = explode('-', $fecha);

        $mes = (int)$fecha[1];
        $dia = (int)$fecha[0];
        $ano = (int)$fecha[2];


        
        //mes->dia->año
        $dia_de_semana = date("w",mktime(0, 0, 0, $mes, $dia, $ano));

        switch ($dia_de_semana) {//para sacar los pk_atributo correspondiente a cada dia_de_semana de la semana
            case '0': $dia_de_semana = 7; break; //domingo
            case '1': $dia_de_semana = 1; break; //lunes
            case '2': $dia_de_semana = 2; break; //martes
            case '3': $dia_de_semana = 3; break; //miercoles
            case '4': $dia_de_semana = 4; break; //jueves
            case '5': $dia_de_semana = 5; break; //viernes
            case '6': $dia_de_semana = 6; break; //sabado
        } 


        return $dia_de_semana;
    }

    function calcular_semana_completa($fecha){

        $dia_de_semana = $this->calcular_dia_de_la_semana($fecha);
        $actividad = $this->tesis->dias_de_semana(NULL);
        $newactividad = array();


        //para calcular las fechas del dia
        foreach ($actividad as $value) {

            if($value['pk_atributo'] != 0){
                
                $dias_para_calcular = $value['pk_atributo'] - $dia_de_semana;
                if($dias_para_calcular < 0){//se resta
                    $nuevafecha = strtotime ( $dias_para_calcular.' day' , strtotime ($fecha) ) ;
                    $nuevafecha = date ( 'd-m-o' , $nuevafecha );
                }elseif($dias_para_calcular == 0){//se deja igual
                    $nuevafecha = $fecha;
                }else{//se suma
                    $nuevafecha = strtotime ( '+'.$dias_para_calcular.' day' , strtotime ($fecha) ) ;
                    $nuevafecha = date ( 'd-m-o' , $nuevafecha );
                }

                $value['fechadeldia'] = $nuevafecha;
            }
            
            $newactividad[] = $value;
            
        }

        return $newactividad;

    }


    function horario($evaluadores, $fecha,$edificio, $aula,$mod){//el mod es para saber si habilitar el confirm o no 1 para si, 0 para no
        
        $cod = $this->_params['redirect']['cod'];
        $periodo = $this->_params['redirect']['periodo'];

        //la fecha se necesita en formato (d-m-a), de lo contrario no hara la cosa bien
        $fecha_defensa = $fecha;
        $fecha_calculo = $fecha;

        //se hace todo esto para calcular el dia de la semana que se usara como filtro
        
        $dia_de_semana = $this->calcular_dia_de_la_semana($fecha);
        
        
        //-------------------------------------------------------------------------------------

        
        // $actividad = array('0'=> array('pk_atributo' => 1, 'dia'=> 'Actividad'));
        $actividad = $this->tesis->dias_de_semana(NULL);

        $horas_academicas = $this->tesis->horas_academicas(null);

        
        $tutor = $this->tesis->getTutorNombre($cod);

        $usuariogrupo_tutor = $this->tesis->getUsuariogrupo($tutor[0]['pk_usuario'],854);

        if(!empty($usuariogrupo_tutor)){
            $horario_tutor = $this->tesis->horario_tutores($periodo,$tutor[0]['pk_usuario']);

        }
        
        
        
        $temp_horas_academicas = array();

        foreach ($horas_academicas as $hora_academica) {
            $hora_academica['hora_12h'] = date('h:i:s a', strtotime($hora_academica['hora']));
            $hora_academica['horafin_12h'] = date('h:i:s a', strtotime($hora_academica['horafin']));

            $temp_horas_academicas[] = $hora_academica;
        }

        $horas_academicas = $temp_horas_academicas;



        $horario_tesistas       = $this->tesis->horario_tesistas($periodo,$cod,$dia_de_semana);
        $horario_evaluadores    = $this->tesis->horario_evaluadores($periodo,$evaluadores,$dia_de_semana);


        // aqui se cargaran los selectbox de edificio y aula
        $actividad0 = array('pk_atributo' => 0, 'dia' => 'HORA'); 

        $actividad = $this->calcular_semana_completa($fecha_calculo);
        array_unshift($actividad, $actividad0);

        
        $fecha_inicial = $actividad[1]['fechadeldia'];
        $fecha_inicial = date_create($fecha_inicial);
        $fecha_inicial =  date_format($fecha_inicial, 'Y-m-d');


        $fecha_final = $actividad[5]['fechadeldia'];
        $fecha_final = date_create($fecha_final);
        $fecha_final =  date_format($fecha_final, 'Y-m-d');


        

            
            $fecha_defensa = date_create($fecha_defensa);
            $fechaformatoBD =  date_format($fecha_defensa, 'Y-m-d');
            
            
            $horario_defensas       = $this->tesis->horario_defensas($periodo,$aula,$fecha_inicial,$fecha_final,NULL,NULL);

            $newhorario_defensas = array();

            //debemos anexarle el dia para completar el id de cada defensa, calculandola con su fecha de defensa
            foreach ($horario_defensas as $horario_defensa) {
                $horario_defensa['fecha'] = strtotime ($horario_defensa['fecha']);
                $horario_defensa['fecha'] = date('d-m-o', $horario_defensa['fecha']);
                
                $pk_dia = $this->calcular_dia_de_la_semana($horario_defensa['fecha']);
                $dia = $this->tesis->dias_de_semana($pk_dia);

                $horario_defensa['id'] = $horario_defensa['id'] . '/' . $dia[0]['dia'];


                $newhorario_defensas[] = $horario_defensa;

            }

            // $horario_defensas = $newhorario_defensas;
            
            // $horario_clases =   $this->tesis->horario_clases($periodo, $edificio,$aula,$dia_de_semana);    
        

        
        // encabezado
        
        $html .= '<table class=\"tableData\" align= \"center\">';
        $html .= '<tr>';
        foreach ($actividad as $value) {//con esto pinto los dias de la semana
            $html .= '<th>'.$value['dia'].'</th><input id =\"fechadeldia'.$value['pk_atributo'].'\" type =\"hidden\" value=\"'.$value['fechadeldia'].'\">';
            
        }
        $html .= '</tr>';

        foreach ($horas_academicas as $hora) {//horario academico 

            $html .= '<tr>';

            foreach ($actividad as $act) {//pinto las casillas de horario dentro de cada act de la semana

                if ($act['pk_atributo'] == 0) {// si estas en la columna de HORA/act, pintas el horario
                    $html .= '<td style=\"color:#666;font-weight: bold;font-size: 12px;\">'.$hora['hora_12h'].'</td>';
                }else{//sino, pintas un cuadro vacio
                    $id = $hora['hora'].'-'.$hora['horafin'] .'/'.$act['dia'];//identificador unico de cada casilla

                    //para los tesistas
                    foreach ($horario_tesistas as $horario_tesista) {//busco dentro de los horarios de los tesistas
                        if($horario_tesista['id'] === $id){//si hay coincidencia entre los codigos, se dibuja la casilla del horario
                            $ho_tes.= '<div id=\"'.$id.'\"';
                            $ho_tes .= '<i><span style =\"font-size:11px\">'.$horario_tesista['tipo'].'</span></i>';
                            $ho_tes.= '<br>';
                            $ho_tes .= '<b><span style =\"font-size:11px\">'.$horario_tesista['nombre_estudiante'].'</span></b>';
                            $ho_tes.= '<br>';
                            $ho_tes .= '<span style =\"font-size:11px\">'.$horario_tesista['datos_materia'].'</span>';
                            $ho_tes.= '</div>';
                            $ho_tes.= '<br>';
                            
                        }
                    }

                    //para los tutores
                    foreach ($horario_tutor as $horario_tut) {//busco dentro de los horarios de los tutores
                        if($horario_tut['id'] === $id){//si hay coincidencia entre los codigos, se dibuja la casilla del horario
                            $ho_tu.= '<div id=\"'.$id.'\"';
                            $ho_tu .= '<i><span style =\"font-size:11px\">'.$horario_tut['tipo'].'</span></i>';
                            $ho_tu.= '<br>';
                            $ho_tu .= '<b><span style =\"font-size:11px\">'.$horario_tut['nombre_profesor'].'</span></b>';
                            $ho_tu.= '<br>';
                            $ho_tu .= '<span style =\"font-size:11px\">'.$horario_tut['datos_materia'].'</span>';
                            $ho_tu.= '</div>';
                            $ho_tu.= '<br>';
                            
                        }
                    }


                    //para los evaluadores
                    foreach ($horario_evaluadores as $horario_evaluador) {
                        if($horario_evaluador['id'] === $id){//si hay coincidencia entre los codigos, se dibuja la casilla del horario
                            $ho_eval.= '<div id=\"'.$id.'\">';
                            $ho_eval .= '<i><span style =\"font-size:11px\">'.$horario_evaluador['tipo'].'</span></i>';
                            $ho_eval.= '<br>';
                            $ho_eval .= '<b><span style =\"font-size:11px\">'.$horario_evaluador['nombre_profesor'].'</span></b>';
                            $ho_eval.= '<br>';
                            $ho_eval .= '<span style =\"font-size:11px\">'.$horario_evaluador['datos_materia'].'</span>';
                            $ho_eval.= '</div>';
                            $ho_eval.= '<br>';
                            
                        }
                    }


                    
                    
                    // para las clases
                    foreach ($horario_clases as $horario_clase) {
                        if($horario_clase['id'] === $id){//si hay coincidencia entre los codigos, se dibuja la casilla del horario
                            $ho_cla.= '<div id=\"'.$id.'\">';
                            $ho_cla .= '<i><span style =\"font-size:11px\">'.$horario_clase['tipo'].'</span></i>';
                            $ho_cla.= '<br>';
                            $ho_cla .= '<b><span style =\"font-size:11px\">'.$horario_clase['nombre_profesor'].'</span></b>';
                            $ho_cla.= '<br>';
                            $ho_cla .= '<span style =\"font-size:11px\">'.$horario_clase['datos_materia'].'</span>';
                            $ho_cla.= '</div>';
                            $ho_cla.= '<br>';
                            
                        }
                    }

                    //para las defensas
                    foreach ($horario_defensas as $horario_defensa) {
                        
                        if($horario_defensa['pk_datotesis'] == $cod){
                            $confirmar = " onclick=\"horario_confirm(".$horario_defensa['pk_horario'].",".$act['pk_atributo'].");\" ";
                        }else{
                            $confirmar = "  ";
                        }

                        if($horario_defensa['id'] === $id){//si hay coincidencia entre los codigos, se dibuja la casilla del horario
                            $ho_def.= '<div id=\"'.$id.'\" '.$confirmar.'>';
                            $ho_def .= '<i><span style =\"font-size:11px\">'.$horario_defensa['tipo'].'</span></i>';
                            $ho_def.= '<br>';
                            $ho_def .= '<span style =\"font-size:11px\">Tesisas: <b>'.$horario_defensa['tesistas'].'</b></span>';
                            $ho_def.= '<br>';
                            $ho_def .= '<span style =\"font-size:11px\">Evaluadores: <b>'.$horario_defensa['evaluadores'].'</b></span>';
                            $ho_def.= '<br>';
                            $ho_def .= '<span style =\"font-size:11px\">'.$horario_defensa['datos_defensa'].'</span>';
                            $ho_def.= '<br>';
                            
                        }

                        $confirmar = "";
                    }

                    

                    if(!empty($ho_tes) || !empty($ho_tu) || !empty($ho_eval)  || !empty($ho_cla) || !empty($ho_def)){//para los tesistas
                        $html .= '<td>'.$ho_tes.$ho_tu.$ho_eval.$ho_cla.$ho_def.'</td>';
                        $ho_tes = "";
                        $ho_tu = "";
                        $ho_eval = "";
                        $ho_cla = "";
                        $ho_def = "";
                    }else{

                        if($mod == 1){
                            $html .= '<td onclick="horario_confirm('.$hora['pk_horario'].','.$act['pk_atributo'].');"></td>'; 
                           
                       }else{
                            $html .= '<td></td>';
                       }
                        
                    }
                                        

                    
                }
                
            }


            $html .= '</tr>';
        }


        $html .= '</table>';
        
        
        return $html;  
    }



}