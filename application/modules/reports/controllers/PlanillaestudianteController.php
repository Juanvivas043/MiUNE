<?php
class Reports_PlanillaestudianteController extends Zend_Controller_Action{
    private $Title = "Planilla Estudiante";

    public function init() {
       $this->SwapBytes_Ajax = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       $this->grupos = new Models_DbTable_Grupos();
       
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Models_DbTable_Planilla');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->Planilla = new Models_DbTable_Planilla();
        
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
       
      }
    
      public function preDispatch() {
           if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
              $this->_helper->redirector('accesserror', 'profile', 'default');
            }
      }
      
      public function periodoAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $Ci = $this->authSpace->userId;
              
              $Periodos = $this->Planilla->getPeriodosCursados($Ci);
             
              $opt = "";
              foreach ($Periodos as $per) {
                 $opt .= "<option value='{$per['fk_periodo']}'>{$per['fk_periodo']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selPeriodo', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selPeriodo', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      
      public function sedeAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
              $Ci = $this->authSpace->userId;
              $periodo = $this->_getParam('selPeriodo');  
              
              $sede = $this->Planilla->getSedes($Ci, $periodo);
             
              $opt = "";
              foreach ($sede as $sed) {
                 $opt .= "<option value='{$sed['fk_estructura']}'>{$sed['sede']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selSede', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selSede', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      
      public function escuelaAction(){
          if ($this->_request->isXmlHttpRequest()) {
              
            $this->SwapBytes_Ajax->setHeader();
            $ci = $this->authSpace->userId;
            $sede = $this->_getParam('selSede');  
            $periodo = $this->_getParam('selPeriodo');  
              
            $cantesc = $this->Planilla->getCantidadEscuela($ci,$periodo,$sede);
            $Escuelas = $this->Planilla->getEscuelasEstudiante($ci,$periodo,$sede);

       
            $opt = "";
            $opt_pensum = "";
            if ($cantesc >= 1) {
                
                foreach ($Escuelas as $esc) {
                    $opt .= "<option value='{$esc['fk_atributo']}'>{$esc['escuela']}</option>";
                    if ($sEscuela == $esc['fk_atributo']) {
                        $swt = 1;
                       
                    }
                };
                $opt = addslashes($opt);

                if ($cantesc >= 1 && $swt != 1) {
                     
                    $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela', 'disabled', 'false');
                    $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
                    //$json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $MSG);
                    $json[] = $this->SwapBytes_Jquery->setHtml('tblLista', "");
                    
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                    $swt = 0;
                    $swt_pen = 0;
                    return;
                } else {
                    $swt = 0;
                    $swt_pen = 0;
                }
            }
            
          }
          
          
      }
      
      
      public function pensumAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
              $Ci = $this->authSpace->userId;
              $Sede = $this->_getParam('selSede');
              $Escuela = $this->_getParam('selEscuela');
              $periodo = $this->_getParam('selPeriodo');
              
              $Pensums = $this->Planilla->getEstudiantePensums($Ci, $Escuela, $periodo,$Sede);
             
              $opt = "";
              foreach ($Pensums as $pen) {
                 $opt .= "<option value='{$pen['codigopropietario']}'>{$pen['nombre']}</option>";
              };

              $opt = addslashes($opt);

              $json[] = $this->SwapBytes_Jquery->setAttr('selPensum', 'disabled', 'false');
              $json[] = $this->SwapBytes_Jquery->setHtml('selPensum', $opt);
              $json[] = $this->SwapBytes_Jquery->setAttr('btnGenerate', 'disabled', 'true');

              $this->getResponse()->setBody(Zend_Json::encode($json));
        }
      }
      
      public function validar($ci){
          $cont = 0;
          $per = "0123456789";
          for ($i = 0; $i<strlen($ci);$i++){
              for($j = 0; $j<strlen($per);$j++){
                  if($ci[$i]== $per[$j]){
                      $cont = $cont + 1;
                       
                  }
              }
              
                           
          }
          if($cont == strlen($ci) ){
             return true;  
                                   }
        
          return false;
      }

      public function busquedaAction(){
        $this->SwapBytes_Ajax->setHeader(); 
        $ci= $this->authSpace->userId;
        $json = array();
        
        if($this->validar($ci)){
            
          $entro = false;
          $usuarioes = $this->grupos->getUsuarioestudiante($ci);
          $usuariodoce = $this->grupos->getUsuariodocente($ci);
          $grupo_s = $this->grupos->getCuadro($ci);
          $grupo_n = $this->grupos->getCuadron($ci);
          
          if($usuarioes[0]['nombre'] != ""){
          $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
          $entro = true; 
          }
           if($usuariodoce[0]['nombre'] != "" && $entro==false){
          $json[] = '$("#n_usuariotxt").html("'.$usuariodoce[0]['nombre'].'")';
          $json[] = '$("#a_usuariotxt").html("'.$usuariodoce[0]['apellido'].'")';
          $json[] = '$("#e_usuariotxt").html("Docente")';
          $entro = true; 
          }
          
          if($entro == true){

              $json3 .= "</select>";
              $json[] = ' $("#marcos").show()';
              $json[] = ' $("#mensaje").hide()';
              $json[] = '$("#imprimir").attr("disabled",false)';
              $this->getResponse()->setBody(Zend_Json::encode($json));
              
          }else{
              
              $json[] = ' $("#marcos").hide()';
              $json[] = ' $("#mensaje").show()';
              $json[] = '$("#imprimir").attr("disabled",true)';
              $this->getResponse()->setBody(Zend_Json::encode($json));
              
          }  
           
       }else{
           $json[] = '$("#mensaje").show()';
           $json[] = '$("#marcos").hide()';
           $json[] = '$("#imprimir").attr("disabled",true)';
           $this->getResponse()->setBody(Zend_Json::encode($json));
       }
       
       
//            $sPensum  = $this->_getParam('selPensum');
//            $PensumNombre  = $this->_getParam('nombre');
//
//            $cantesc = $this->Planilla->getCantidadEscuela($ci);
//            $Escuelas = $this->Planilla->getEscuelasEstudiante($ci);
//
//       
//            $opt = "";
//            $opt_pensum = "";
//            if ($cantesc >= 1) {
//                
//                foreach ($Escuelas as $esc) {
//                    $opt .= "<option value='{$esc['fk_atributo']}'>{$esc['escuela']}</option>";
//                    if ($sEscuela == $esc['fk_atributo']) {
//                        $swt = 1;
//                       
//                    }
//                };
//                $opt = addslashes($opt);
//
//                if ($cantesc >= 1 && $swt != 1) {
//                     
//                    $json[] = $this->SwapBytes_Jquery->setAttr('selEscuela', 'disabled', 'false');
//                    $json[] = $this->SwapBytes_Jquery->setHtml('selEscuela', $opt);
//                    //$json[] = $this->SwapBytes_Jquery->setHtml('tblMsg', $MSG);
//                    $json[] = $this->SwapBytes_Jquery->setHtml('tblLista', "");
//                    
//                    $this->getResponse()->setBody(Zend_Json::encode($json));
//                    $swt = 0;
//                    $swt_pen = 0;
//                    return;
//                } else {
//                    $swt = 0;
//                    $swt_pen = 0;
//                }
//            }
            
          
      }
      
      public function imprimirAction()    {
            $this->SwapBytes_Ajax->setHeader();
            $ci= $this->authSpace->userId;
            $pensum= $this->_getParam('pensum');
            $escuela= $this->_getParam('escuela');
            $periodo= $this->_getParam('periodo');
            $sede= $this->_getParam('sede');
            
            $Online = $this->Planilla->getEstudianteOnline($ci, $escuela, $periodo,$sede, $pensum);
            $New = $this->Planilla->getEstudianteNew($ci, $escuela, $periodo,$sede, $pensum);
            
            
            
            if ($Online[0]['online'] == 't'){
                $tipo = 'MiUNE Online';
            }else if ($New[0]['count'] == 0){
                $tipo = 'MiUNE de Ingreso';
            }else{
                $tipo = 'MiUNE';
            }
            //
            $config = Zend_Registry::get('config');

                    $dbname = $config->database->params->dbname;
                    $dbuser = $config->database->params->username;
                    $dbpass = $config->database->params->password;
                    $dbhost = $config->database->params->host;
                    $report = APPLICATION_PATH . '/modules/reports/templates/Planilla/HomePlanillaOnline.jasper';
                    $subreport = APPLICATION_PATH . '/modules/reports/templates/Planilla/';
                    $filename    = 'planilla';
                    $filetype    = 'pdf';

                    $params      = "'ci=string:{$ci}|escuela=string:{$escuela}|pensum=string:{$pensum}|periodo=string:{$periodo}|sede=string:{$sede}|SUBREPORT_DIR=string:{$subreport}|tipo=string:{$tipo}'";                    
                    $cmd         = "java -jar -Djava.awt.headless=true " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
                    
//                    echo $cmd;

            Zend_Layout::getMvcInstance()->disableLayout();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}" );

            $outstream = exec($cmd);
            echo base64_decode($outstream);
            
            
      }
      
      
      public function indexAction() {
          
            $this->view->title = $this->Title;
            $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();

            $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
            $this->view->SwapBytes_Ajax->setView($this->view);
            $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
            $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;

      }
}

?>
