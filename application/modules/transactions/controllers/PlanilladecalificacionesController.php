<?php


class Transactions_PlanilladecalificacionesController extends Zend_Controller_Action {

private $_Title   = 'Transacciones \ Planilla de Calificaciones'; 

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
        Zend_Loader::loadClass('Forms_Tesiscalificaciones');
        
        
        $this->Usuarios         = new Models_DbTable_Usuarios();
        $this->Pasantes         = new Models_DbTable_Pasantes();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->recordacademico  = new Models_DbTable_Recordsacademicos();
        $this->tesis           = new Models_DbTable_Tesis();
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
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->AuthSpace                = new Zend_Session_Namespace('Zend_Auth');  

        $this->view->form = new Forms_Tesiscalificaciones();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();

        $this->tablas = Array(
                              'Periodo'  => Array('tbl_periodos',
                                                  null,
                                                  Array('pk_periodo',
                                                        'lpad(pk_periodo::text, 4, \'0\') || \', \' || to_char(fechainicio, \'MM-yyyy\') || \' / \' ||  to_char(fechafin, \'MM-yyyy\')'),
                                                  'DESC'),

                              'Sede'    => Array('vw_sedes',
                                                 null     ,
                                                 Array('pk_estructura',
                                                       'nombre'),
                                                 'DESC'),
                              'Escuela' => Array(Array('tbl_estructurasescuelas ee',
                                                       'vw_escuelas es'),
                                                 Array('ee.fk_atributo = es.pk_atributo',
                                                       'ee.fk_estructura = ##Sede##'),
                                                 Array('ee.fk_atributo',
                                                       'es.escuela')
                                                 )

                                                            );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 
    $this->SwapBytes_Crud_Action->addCustum('<button id="btnValidar" onclick="validar()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnValidar" role="button" aria-disabled="false">
                                                Validar
                                                </button>');



    $this->SwapBytes_Crud_Action->addCustum('<button id="btnImprimir" onclick="imprimir()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnImprimir" role="button" aria-disabled="false">
                                                Imprimir
                                                </button>');

    $this->SwapBytes_Crud_Action->addJavaScript('$("#btnImprimir").hide();');

//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(false); 


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
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->SwapBytes_Ajax_Action       = new SwapBytes_Ajax_Action();
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

    } 


    public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));

            if(!$select || !$values){
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }            
            $this->getResponse()->setBody(Zend_Json::encode($json)); 
            
    
    }




    public function listAction(){
           if ($this->_request->isXmlHttpRequest()) {
                $this->SwapBytes_Ajax->setHeader(); 

                $datos = $this->_getAllParams();
                $filtro = $this->SwapBytes_Uri->queryToArray($datos['filters']);

                
                $rows = $this->tesis->getTesisparaPlanilla($filtro['Periodo'],$filtro['Sede'],$filtro['Escuela']);

                $TG2Aprobado = $this->tesis->getTG2Aprobado($filtro['Periodo'],$filtro['Escuela']);

                if($TG2Aprobado >= 1){
                  $json[] = '$("#btnImprimir").show();';
                }else{
                  $json[] = '$("#btnImprimir").hide();';
                }
                  

                if(isset($rows) && count($rows) > 0) {

                          
                    $HTML = $this->calificaciones($filtro['Periodo'],$filtro['Sede'],$filtro['Escuela']);
                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);

                }else{

                        $HTML = $this->SwapBytes_Html_Message->alert("No hay registros");

                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }
    
    
    public function addoreditloadAction(){
      if ($this->_request->isXmlHttpRequest()) {
      
      $this->SwapBytes_Ajax->setHeader();
      $datos = $this->_getAllParams();

      $dataRow['calificaciones'] = $datos['cod'];

      $this->SwapBytes_Crud_Form->setProperties($this->view->form,$dataRow, 'Validar Calificaciones');
      $json[] = "$('#tesiscalificaciones').append('<div id= \"txtMessage\"></div>');";

      $json[] = $this->SwapBytes_Jquery->setHtml('txtMessage', '¿Está seguro que desea continuar?, a continuacíón se validarán los datos cargados, en caso de que estén correctos se guardaran, de lo contrario se le indicará donde cometió el error.');            

      $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 180);
      $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300);
      $this->SwapBytes_Crud_Form->setJson($json);
      $this->SwapBytes_Crud_Form->getAddOrEditLoad();
      }

      

    }

    public function addoreditconfirmAction(){
      if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();
        $datos = $this->_getAllParams();
        $validado =  array();

        $dataRow['calificaciones'] = str_replace('calificaciones=', '', $datos['data']);
        $Rows = $this->SwapBytes_Uri->queryToArray($dataRow['calificaciones']);
        
        foreach ($Rows as $id => $calificacion) {
          
          if(empty($calificacion)){//esta vacio
            $json[] = "$('#alert".$id."').empty()";
            $json[] = "$('#alert".$id."').append('<span style=\"margin-left: 10px; color: red;\">Está vacio</span>')";
            array_push($validado, true);
          }else{//no esta vacio
            if(!is_numeric($calificacion)){//no es numerico
              $json[] = "$('#alert".$id."').empty()";
              $json[] = "$('#alert".$id."').append('<span style=\"margin-left: 10px; color: red;\">No es numerico</span>')";
              array_push($validado, false);
            }else{// es numerico
              if($calificacion >= 0 && $calificacion <= 20){// estas entre 0-20
                $json[] = "$('#alert".$id."').empty()";
                array_push($validado, true);
              }else{// no sta entre 0-20
                $json[] = "$('#alert".$id."').empty()";
                $json[] = "$('#alert".$id."').append('<span style=\"margin-left: 10px; color: red;\">Debe estar entre 0-20</span>')";
                array_push($validado, false);
              }
            }
          }
        }
        
        
        //recorro el arreglo a ver si hay algun dato erroneo, si lo hay no paso al response
        foreach ($validado as $key => $value) {
          if($value == false){
            $entro = false;
            break;
          }else{
            $entro = true;
          }
        }

        

        //aqui procedo a ejecutar las validaciones dinamicas
        if($entro == true){
          $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow,'Horario');
          $this->SwapBytes_Crud_Form->getAddOrEditConfirm();  
        }else{
          $json[] = "$(\"#frmModal\").dialog(\"close\");";
          $this->getResponse()->setBody(Zend_Json::encode($json)); 
        }
        
      }
    }
    
    public function addoreditresponseAction(){
        if ($this->_request->isXmlHttpRequest()) {
             $this->SwapBytes_Ajax->setHeader();

             $datos = $this->_getAllParams();
             
             
             $datos['data'] = str_replace('calificaciones=', '', $datos['data']);

             $Rows = $this->SwapBytes_Uri->queryToArray($datos['data']);
             
             foreach ($Rows as $key => $value) {
                 
                 if(!empty($value)){//solo se le updateara la calificacion a la tesis que la tenga, las demas quedaran igual
                    $tesistas = $this->tesis->getTesistasParaCalificacion($key);
                  
                   foreach ($tesistas as $tesista) {
                       $this->tesis->updateTesistaCalificacion($tesista['pk_recordacademico'], $value);
                       
                   } 

                   $this->tesis->updateTesisCalificacion($key,$value);
                 }
                 
             }
             
             $this->SwapBytes_Crud_Form->setJson($json);
             $this->SwapBytes_Crud_Form->getAddOrEditEnd();   
              
        }

    }

    public function mencionAction(){
      if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
          $datos = $this->_getAllParams();

          $cod = $datos['cod'];
          $mencion = $datos['mencion'];

          $menciontesis = $this->tesis->getMencionTesis($cod);

          if($menciontesis >= 1){//significa que tiene ya una mencion UPDATE

            $this->tesis->updateMencionTesis($cod,$mencion);
          }

          $this->getResponse()->setBody(Zend_Json::encode($json)); 
      }      
    }


    public function imprimirAction(/*$periodo,$sede,$escuela*/){
        mb_internal_encoding('UTF-8');

        $periodo = $this->_getParam('periodo');
        $sede = $this->_getParam('sede');
        $escuela = $this->_getParam('escuela');

        $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/planilladecalificaciones.jasper';
        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
        
        $params      = "'logo=string:{$imagen}|periodo=string:{$periodo}|sede=string:{$sede}|escuela=string:{$escuela}'";
        $filename    = 'planilladecalificaciones';
    
        
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

//--------------------------------------------------------------------------------------------------------------------
function calificaciones($periodo,$sede,$escuela){
        
        $rows = $this->tesis->getTesisparaPlanilla($periodo,$sede,$escuela);

        
        $menciones = $this->tesis->getMenciones();
        
        
        $html .= '<table class="tableData" style="margin:0 auto;width:1000px;">';
        $html .= '<tbody>';
        $html .= '<tr>';  
        $html .= '<th>Titulo</th>';
        $html .= '<th>Autor(es)</th>'; 
        $html .= '<th>Mencion</th>'; 
        $html .= '<th>Calificacion</th>'; 
        $html .= '</tr>'; 


        foreach ($rows as $row) {

          foreach ($menciones as $mencion) {
            
            if($row['mencion'] == $mencion['pk_atributo']){
              $filtro_mencion .= "<option selected value = ".$mencion['pk_atributo']."> ".$mencion['mencion']."</option>";
            }else{
              $filtro_mencion .= "<option value = ".$mencion['pk_atributo']."> ".$mencion['mencion']."</option>";
            }
            
          }
          

          $html .= '<tr>';  
          $html .= '<td style="text-align:center;width:600px;">'.$row['titulo'].'</td>';
          $html .= '<td style="text-align:center;width:250px;">'.$row['autor'].'</td>'; 
          $html .= '<td style="text-align:center;width:50px;">';
          $html .= '<select id=\"mencion'.$row['pk_datotesis'].'\"  onclick="mencion('.$row['pk_datotesis'].')">';
          $html .= $filtro_mencion;
          $html .= '</select>';
          $html .= '</td>'; 
          $html .= '<td style="text-align:center;width:100px;"><input class="TextBoxNormal" onkeypress="return acceptNum(event)" type="text" maxlength="2" size="2" name="'.$row['pk_datotesis'].'" id="'.$row['pk_datotesis'].'"><div id="alert'.$row['pk_datotesis'].'"></td>';
          $html .= '</tr>'; 

          $filtro_mencion = "";
        }


        $html .= '</tbody>';       
        $html .= '</table>';             

        
        return $html;

    }  


}