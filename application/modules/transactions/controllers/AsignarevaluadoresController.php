<?php

class Transactions_AsignarevaluadoresController extends Zend_Controller_Action {

	private $_Title   = 'Transacciones \ Asignar Evaluadores';
	private $FormTitle_Info = 'Asignar Evaluadores'; 
  private $maxevaluadorperiodo = 5;
	

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
        Zend_Loader::loadClass('Forms_Observaciones');
        
        
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

        $this->view->form = new Forms_Observaciones();
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
                                                       'es.escuela'),
                                                 'ASC'),
                                'Estado'  => Array('tbl_atributos',
                                                 array('fk_atributotipo = '.$this->tesis->getAtributotipoEvaluadores()),
                                                 array('pk_atributo',
                                                       'valor')

                                                )
                                                            );
            

    $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters')); 

//      BOTONES DE ACCIONES
                
    $this->SwapBytes_Crud_Action->setDisplay(true, true, false, false, false, false);
    $this->SwapBytes_Crud_Action->setEnable(true, true, false, true, false, false);
    $this->SwapBytes_Crud_Search->setDisplay(true); 


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
                $asignado = $this->tesis->getEstadoEvaluadorAsignado();

                if($filtro['Estado'] == $asignado){
                  $estado = 'Asignado';
                  $accion = 'editar';

                  $evaluador_tecnico = 'lol';
                  $evaluador_investigacion = 'lol';
                  
                }else{
                  $estado = 'No Asignado';
                  $accion = 'asignar';


                  $evaluador_tecnico = null;
                  $evaluador_investigacion = null;

                }

                $rows = $this->tesis->getTesisEScuelaPeriodoParaDefensa($filtro['Escuela'],$filtro['Periodo'],$filtro['txtBuscar'],'Evaluadores',$estado,$filtro['Sede']);


                if(isset($rows) && count($rows) > 0) {

                        $table = array('class' => 'tableData',
                               'width' => '1270px');


                        if(empty($evaluador_tecnico) && empty($evaluador_investigacion)){  

                            $columns = array(array('column'  => 'pk_datotesis',
                                                   'primary' => true,
                                                   'hide'    => true),
                                             array('name'    => 'Cedula(s)',
                                                       'width'   => '170px',
                                                       'column'  => 'cedula',
                                                       'rows'    => array('style' => 'text-align:center')),
                                             array('name'    => 'Autor(es)',
                                                   'width'   => '200px',
                                                   'rows'    => array('style' => 'text-align:center'),
                                                   'column'  => 'autor'),
                                             array('name'    => 'Titulo',
                                                   'width'   => '700px',
                                                   'rows'    => array('style' => 'text-align:center'),
                                                   'column'  => 'titulo'),
                                             array('name'    => 'Tutor',
                                                   'width'   => '200px',
                                                   'rows'    => array('style' => 'text-align:center'), 
                                                   'column'  => 'tutor')
                                             );                          

                        }else{

                            $columns = array(array('column'  => 'pk_datotesis',
                                                   'primary' => true,
                                                   'hide'    => true),
                                             array('name'    => 'Cedula(s)',
                                                       'width'   => '70px',
                                                       'column'  => 'cedula',
                                                       'rows'    => array('style' => 'text-align:center')),
                                             array('name'    => 'Autor(es)',
                                                   'width'   => '200px',
                                                   'rows'    => array('style' => 'text-align:center'),
                                                   'column'  => 'autor'),
                                             array('name'    => 'Titulo',
                                                   'width'   => '700px',
                                                   'rows'    => array('style' => 'text-align:center'),
                                                   'column'  => 'titulo'),
                                             array('name'    => 'Tutor',
                                                   'width'   => '100px',
                                                   'rows'    => array('style' => 'text-align:center'),
                                                   'column'  => 'tutor'),
                                             array('name'    => 'Evaluador Tecnico',
                                               'width'   => '100px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'evaluador_tecnico'),
                                             array('name'    => 'Evaluador Investigacion',
                                               'width'   => '100px',
                                               'rows'    => array('style' => 'text-align:center'),
                                               'column'  => 'evaluador_investigacion')

                                             );                          

                        }


                        $other = array(
                          array('actionName' => '',
                                'action'     => 'asignar(##pk##)'  ,
                                'label'      => $accion,
                                'column' => 'acciones')
                          
                                );

                        $HTML = $this->SwapBytes_Crud_List->fill($table, $rows, $columns, 'O',$other);
                        $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                        $json[] = $this->SwapBytes_Jquery->checkOrUncheckAll('chkSelectDeselect', 'chkDatoTesis');

            }else{

                    $HTML = $this->SwapBytes_Html_Message->alert("No Existen Registros");

                    $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            }

            $this->getResponse()->setBody(Zend_Json::encode($json));        

        }

    }


    public function addoreditloadAction() {
        // Obtenemos los parametros que se esperan recibir.
     if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();

      $datos = $this->_getAllParams();

        $json = array();
        $data = array();
        $tesis = $this->tesis->getTesisByPk($datos['cod']);

        $countevaluador = $this->tesis->getCountEval($datos['cod'],$datos['Periodo']);

              //datos de la tesis
        $properties = array('width' => '750','align' => 'center');
        $styles = array(array('style' => 'text-align:right;font-size:14px;font-weight:bold;vertical-align:top'),array('style' => 'text-align:left;font-size:14px'));
        $data[] = array('', '<input id =\"cod\" hidden=\"true\" value=\"'.$tesis[0]['pk_datotesis'].'\">');
        $data[] = array('Titulo:', $tesis[0]['titulo']);
        $data[] = array('Autor(es):', $tesis[0]['autor']);
        $data[] = array('Tutor:', $tesis[0]['tutor']);


        if($countevaluador > 0){//editar
          $evaluadores = $this->tesis->getEvaluadoresDefensa($datos['cod'],$datos['Periodo']);
          $last = count($evaluadores);

            if($last > 1){//si tiene varios
              foreach ($evaluadores as $key => $evaluador) {
                    if($key == 0){//primero
                      $data[] = array('Evaluador(es) / Rol: ', $this->evaluadores($datos['Periodo'],$datos['Sede'],null,$evaluador['pk_usuariogrupo'],$datos['cod'],$evaluador['fk_rol']));
                      $jscript .= "$('#add').hide();";
                      $jscript .= "$('#del').hide();";
                    }else{
                      $data[] = array('  ', $this->evaluadores($datos['Periodo'],$datos['Sede'],$key,$evaluador['pk_usuariogrupo'],$datos['cod'],$evaluador['fk_rol']));
                      $jscript .= "$('#add".$key."').hide();";
                      if($key != ($last - 1)){//si no es el ultimo
                        $jscript .= "$('#del".$key."').hide();";  
                      }else{//ultimo
                        $jscript .= "$('#add".$key."').show();";  
                      }
                      
                    }
                  }
            }else{//si tiene varios

              $data[] = array('Evaluador(es) / Rol: ', $this->evaluadores($datos['Periodo'],$datos['Sede'],null,$evaluadores[0]['pk_usuariogrupo'],$datos['cod'],null));
              $jscript .= "$('#add').show();";
              $jscript .= "$('#del').hide();";
            }
            

          }
        else{//agregar

          $data[] = array('Evaluador(es) / Rol: ', $this->evaluadores($datos['Periodo'],$datos['Sede'],null,null,$datos['cod'], NULL));

        }
        
        $data[] = array(' ', '<div id =\"grupo\"></div>');
        $html .= $this->SwapBytes_Html->table($properties, $data, $styles);
        

        $json[] =  $this->SwapBytes_Jquery->setHtml('frmModal', $html);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 400);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal', 580);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmModal', $this->FormTitle_Info);
        $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmModal');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Proceder');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar', 'Guardar');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Proceder');
        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
        $json[] = $jscript;
        
        $this->getResponse()->setBody(Zend_Json::encode($json));

    }
  }


    
    public function addoreditconfirmAction() {
        // Obtenemos los parametros que se esperan recibir.
     if ($this->_request->isXmlHttpRequest()) {
      $this->SwapBytes_Ajax->setHeader();

      $datos = $this->_getAllParams();

      if(isset($datos['cod'])){
            
            $tipo = "19965";
            $nopuede = false;
            $update = false;
            
            if(empty($datos['cod'])){ $this->getResponse()->setBody(Zend_Json::encode("alert('Datos no estan llegando. Error: 10x1')")); }
            if(empty($datos['evaluadores'])){ $this->getResponse()->setBody(Zend_Json::encode("alert('Datos no estan llegando. Error: 11x1')")); }

            $Rows = $this->SwapBytes_Uri->queryToArray($datos['evaluadores']);

            $Rows = $this->ProcesarArrayEvaluadores($Rows);

            $validarEvaluadores = $this->ValidarArrayEvaluadores($Rows);

//             if($validarEvaluadores !== true){

//               $this->SwapBytes_Crud_Form->getDialog('Advertencia', $validarEvaluadores, swOkOnly);
//             }
//             var_dump($datos, $validarEvaluadores);
// die;

            $evaluadoresprincipales = $this->tesis->getEvaluadoresPrincipales($datos['cod'],$datos['periodo']);

            foreach ($Rows as $key => $Row) {

                foreach ($evaluadoresprincipales as $evaluadorprincipal) {
                  if($evaluadorprincipal["fk_rol"] == $Row["rol"]){
                    if($evaluadorprincipal['pk_usuariogrupo'] == $Row['evaluador']){
                      $nopuede = true;
                    }else{
                      $update = true;
                      break;
                    }
                  }
                }

                if(!$nopuede && !$update){
                  $this->tesis->addEvaluadoresTesis($datos['cod'],$datos['periodo'],$Row['evaluador'],$tipo,$Row['rol']);    
                }elseif($update){
                  $this->tesis->updateEvaluadoresDeTesis($Row['evaluador'], $evaluadorprincipal["pk_evaluadortesis"]);
                }

                $nopuede = false;
                $update = false;
            }

            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }else{
          throw new Exception("No estan llegando los datos como se debe. addoreditconfirmAction ERROR: 342", 1);
        }
     }
  }

    public function evaluadorAction(){
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
	    	$datos = $this->_getAllParams();
            $anterior = $datos['count'] - 1;

            if($anterior == 0){
                $anterior = '';
            }else{
                $anterior = $anterior;
            }

	    	$html = $this->evaluadores($datos['Periodo'],$datos['Sede'],$datos['count'],NULL,NULL,NULL);

            $json[] =  "$('#grupo').append('".$html."');";
            $json[] = "$('#add".$anterior."').hide();";
            $json[] = "$('#del".$anterior."').hide();";
	    	$this->getResponse()->setBody(Zend_Json::encode($json));
    	}
    }

    public function evaluadordeleteAction(){
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
            $datos = $this->_getAllParams();
            $divgrupo = "";

            $actual = $datos['count'] - 1;
            $anterior = $actual - 1;

            if($anterior == 0){
                $anterior = '';
                $divgrupo = "$('#grupo').html('');";

            }

            $json[] =  "$('#evaluadores".$actual."').remove();";
            $json[] =  "$('#rol_eval".$actual."').remove();";
            $json[] = "$('#add".$actual."').remove();";
            $json[] = "$('#del".$actual."').remove();";
            $json[] = "$('#add".$anterior."').show();";
            $json[] = "$('#del".$anterior."').show();";
            $json[] = $divgrupo;
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

//---------------------------------------------------funciones-----------------------------------------------
    function searchForId($id, $array,$busqueda) {

       foreach ($array as $key => $val) {

        

           if ($val[$busqueda] == $id) {
               $newkey[] = $key;
               
           }
       }


       if(!empty($newkey)){
          return count($newkey);   
       }else{
        return 0;
       }

       
    }

    public function ValidarArrayEvaluadores($array){
      

      $evaluador_roles = $this->tesis->getEvaluadorRoles();

      

      if(count($array) >= 2){

        if(count($array) == 2){//cuando son 2

   

          // con esto busco que haya dos evaluadores con roles iguales
          foreach ($evaluador_roles as $value) {

            $count = $this->searchForId($value['pk_atributo'],$array,'rol');
            $cantidad_roles[] = array('rol'=>$value['pk_atributo'], 'count'=>$count);

          }
          
          foreach ($cantidad_roles as $cantidad_rol) {
            if($cantidad_rol['count'] == 2){

              $message = "Cuando elige 2 evaluadores, debe asignarle un rol distinto a cada evaluador";
              return $message;

            }
          }

          
        }elseif(count($array)%2 != 0){//cuando elige cantidades impares de evaluadores

          $message = "Debe elegir tanto evaluador como suplente";
          return $message;

        }else{

          //me aseguro de que eliga la misma cantidad de evaluadores-roles
          foreach ($evaluador_roles as $value) {

            $count = $this->searchForId($value['pk_atributo'],$array,'rol');
            $cantidad_roles[] = array('rol'=>$value['pk_atributo'], 'count'=>$count);

          }
          
          foreach ($cantidad_roles as $cantidad_rol) {
            if($cantidad_rol['count'] > (count($array)/2)){

              $message = "La mitad de los evaluadores deben tener roles distintos";
              return $message;

            }
          }

        }

      }else{
        $message = 'Debe Seleccionar al menos 2 evaluadores';
        return $message;
      }

      return true;
    }


    public function ProcesarArrayEvaluadores($array){

      $array = $array['row'];
      

      if(is_array($array)){

        $array = array_unique($array);

        foreach ($array as $key => $value) {
          $array_temp = explode('-', $value);

          $newArray[] = array('evaluador' => $array_temp[0], 'rol'=>$array_temp[1]);
        }

      }else{

        $array_temp = explode('-', $array);
        $newArray[] = array('evaluador' => $array_temp[0], 'rol'=>$array_temp[1]);
      }
      

      return $newArray;

    }


    public function evaluadores($periodo,$sede,$cuenta,$evaluador,$tesis,$rol){

    	$evaluadores = $this->tesis->getEvaluadores($periodo,$sede);
      $evaluador_roles = $this->tesis->getEvaluadorRoles();

    	

      // para los evaluadores
    	$html .= '<select class="evaluadores" id="evaluadores'.$cuenta.'">';

    	foreach ($evaluadores as $key => $value) {
        if($value['pk_usuariogrupo'] == $evaluador){
          $html .= "<option selected value=".$value['pk_usuariogrupo'].">".$value['initcap']."</option>";
        }else{
          $html .= "<option value=".$value['pk_usuariogrupo'].">".$value['initcap']."</option>";
        }
    		

    	}
   
    	$html .= '</select>';


      // para el rol del evaluador
      $html .= '<select class ="rol_eval" id="rol_eval'.$cuenta.'">';
      foreach ($evaluador_roles as $key => $value) {

        if($value['pk_atributo'] == $rol){
          $html .= "<option selected value=".$value['pk_atributo'].">".$value['rol']."</option>";  
        }else{
          $html .= "<option value=".$value['pk_atributo'].">".$value['rol']."</option>";
        }
        
      }
      $html .= '</select>';



    	if(!empty($cuenta)){
        $html .= '<button id = "add'.$cuenta.'" onclick="add_e()"><span> &nbsp;+&nbsp; </span></button>&nbsp;';
        $html .= '<button id = "del'.$cuenta.'" onclick="del_e()"><span value> &nbsp;-&nbsp; </span></button>';
    	}else{
    		$html .= '<button id = "add" onclick="add_e()"><span> &nbsp;+&nbsp; </span></button>';
    	}


      

    	return $html;
    }

}
