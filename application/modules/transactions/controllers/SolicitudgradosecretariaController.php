<?php

class Transactions_SolicitudgradosecretariaController extends Zend_Controller_Action {

    private $Title = "Transacciones / Solicitud de grado secretaría";
    private $Tsuperior = 19759; 
    private $aprobado = 14145;  
    private $Solicitado = 14146; 
    
    public function init() {

        Zend_Loader::loadClass('Models_DbTable_Solicitudgrado');
        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Estructuras');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Forms_Solicitudgrado');
        Zend_Loader::loadClass('Une_Filtros');
        $this->solicitudgrado   = new Models_DbTable_Solicitudgrado();
        $this->atributos        = new Models_DbTable_Atributos();
        $this->periodos         = new Models_DbTable_Periodos();
        $this->sedes            = new Models_DbTable_Estructuras();
        $this->escuelas         = new Models_DbTable_EstructurasEscuelas();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->filtros          = new Une_Filtros();
        $this->SwapBytes_Ajax               = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html          = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action        = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action        = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List          = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search        = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri                = new SwapBytes_Uri();
        $this->SwapBytes_Jquery             = new SwapBytes_Jquery();
        $this->SwapBytes_Form               = new SwapBytes_Form();
        $this->SwapBytes_Crud_Form          = new SwapBytes_Crud_Form();
        $this->SwapBytes_Jquery_Ui_Form     = new SwapBytes_Jquery_Ui_Form();
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_params['filters'] = $this->filtros->getParams();
        $this->filtros->setDisplay(true,true , true, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, true, true, true, true, true, true, true, true);
        $this->filtros->setRecursive(true, true, true, false, false, false, false, false, false);
    //		$this->filtros->setType('seccion', FILTER_TYPE_SECCION_PADRES);
        
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->view->form = new Forms_Solicitudgrado();
        $this->SwapBytes_Form->set($this->view->form);
        $this->view->form = $this->SwapBytes_Form->get();

//
        $this->SwapBytes_Crud_Action->setDisplay(true, true);
        $this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);


	    $this->logger = Zend_Registry::get('logger');
//            $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        
    }
    
        function preDispatch() {
             if (!Zend_Auth::getInstance()->hasIdentity()) {
                 $this->_helper->redirector('index', 'login', 'default');
             }

             if (!$this->grupo->haveAccessToModule()) {
                 $this->_helper->redirector('accesserror', 'profile', 'default');
             }
        }
    
    public function indexAction() {
        $this->view->title                 = $this->Title;
        $this->view->filters               = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    }
    
    public function periodoAction() {
        
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->filtros->getAction();
    }

    public function escuelaAction() {
        $this->filtros->getAction();
    }
    
    public function listAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json = array();
                $ra_data = $this->solicitudgrado->getSolicitudesGrado($this->_params['filters']['periodo'], $this->_params['filters']['sede'] ,$this->_params['filters']['escuela']);
                // Definimos las propiedades de la tabla.
        $ra_property_table = array('class' => 'tableData',
           'width' => '1100px',
           'column' => 'disponible');
        $ra_property_column = array(array('column'   => 'codigo',
          'primary'  => true,
          'hide'     => true),
        array('name'     => '#',
          'width'    => '20px',
          'function' => 'rownum',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'C.I.',
          'column'   => 'cedula',
          'width'    => '70px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'Nombre',
          'column'   => 'nombre',
          'width'    => '300px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'Apellido',
          'column'   => 'apellido',
          'width'    => '200px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'secretaría',
          'column'   => 'secretaria',
          'width'    => '250px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'Biblioteca',
          'column'   => 'biblioteca',
          'width'    => '250px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'Coordinacion',
          'column'   => 'coordinacion',
          'width'    => '250px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'TSU',
          'column'   => 'tsu',
          'width'    => '250px',
          'rows'     => array('style' => 'text-align:center')),
        array('name'     => 'Revisado',
                'column'   => 'revisado',
                'width'    => '250px',
                'rows'     => array('style' => 'text-align:center'))
        );
        $other = array  (
                    array
                        ('actionName' => 'estado',
                        'action'      => 'load(##pk##)',
                        'label'       => 'Asignar'
                        )
                );
        $HTML = $this->SwapBytes_Crud_List->fill($ra_property_table, $ra_data, $ra_property_column, 'VO', $other);   
}
$json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
$this->getResponse()->setBody(Zend_Json::encode($json));
}

public function addoreditloadAction() {
    $json = array();
    $dataRow['id'] = (int)$this->_params['modal']['id'];
    for($i = 0;$i < 6;$i++){
        $string[$i] = $this->solicitudgrado->getRequisitos($dataRow['id'],$i+1, NULL);
        $s = 'checkbox0' . ($i+1);
        if($string[$i][0]['estado']){
            $dataRow[$s] = true;
        }
    }
    $tecnico = $this->solicitudgrado->getValorEstadoSolicitud($dataRow['id'],$this->Tsuperior);
    $params = $this->_getAllParams();
    $tecnicoAction = 'true' === $params['tecnico'];
    if ($params['tecnico']){
        $dataRow['tecnico']  = $tecnicoAction;
    }else if ($tecnico) {
        $dataRow['tecnico'] = (bool)$tecnico;
        $tecreq = $this->solicitudgrado->getTecnicoReq($dataRow['id'],true);
    }
    //var_dump($tecreq);die;
    $json[] = $this->setCheckboxtecnico($dataRow['tecnico']);
    if ($dataRow['tecnico']){
    	if($tecnico){
            //var_dump($tecnico);die;
        foreach ($tecreq as $req) {
            $dataRow[$req['pk_atributo']] = true;
        }
    }
}

$this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Asignar requisitos');
$this->SwapBytes_Crud_Form->setJson($json);
$this->SwapBytes_Crud_Form->setWidthLeft('300px');
$this->SwapBytes_Crud_Form->getAddOrEditLoad();
}

public function addoreditconfirmAction() {
    if ($this->_request->isXmlHttpRequest()) {
        $this->SwapBytes_Ajax->setHeader();  
		$this->SwapBytes_Crud_Form->setProperties($this->view->form, $this->_params['modal']);
        $this->SwapBytes_Crud_Form->getAddOrEditConfirm();  
    }
}

public function addoreditresponseAction() {
    if ($this->_request->isXmlHttpRequest()){
        $this->SwapBytes_Ajax->setHeader();
        $dataRow = $this->_params['modal'];
        //set moroso
       /*/* $nombre = $this->solicitudgrado->getRequisitos($dataRow['id'],1)[0]['Requisito'];
        $pk = $this->solicitudgrado->getPkDocumentoPorNombre($nombre,81);
        $revisado = $this->solicitudgrado->getValorEstadoSolicitud($dataRow['id'],$pk);*/
        //var_dump($revisado);die;
        /*if (!$revisado){
           //echo('inserte');
           $this->solicitudgrado->InsertarRequisito($dataRow['id'],$pk,$this->Solicitado);
        }*/
        for($i = 0;$i < 6;$i++){
            $s = 'checkbox0' . ($i+1);
            if($dataRow[$s] == 0){
                $req = $this->solicitudgrado->getRequisitos($dataRow['id'],$i+1,true);
                if($req == true){
                    $this->solicitudgrado->updateEstadoRequisito($dataRow['id'],$i+1,'Solicitado');
                }
            }else{
                $req = $this->solicitudgrado->getRequisitos($dataRow['id'],$i+1,true);
                if($req == true){
                    $this->solicitudgrado->updateEstadoRequisito($dataRow['id'],$i+1,'Aprobado');
                }else{
                    $this->solicitudgrado->insertRequisito($dataRow['id'],$i+1);
                }
            }
        }  
        //var_dump($dataRow);die;
        if ($dataRow['tecnico']){
           $tecnico = $this->solicitudgrado->getValorEstadoSolicitud($dataRow['id'],$this->Tsuperior);
           if (!$tecnico){
               $this->solicitudgrado->InsertarRequisito($dataRow['id'],$this->Tsuperior,$this->Solicitado);
               $tecnico = $this->solicitudgrado->getValorEstadoSolicitud($dataRow['id'],$this->Tsuperior);
           }
            else{

            }

           $tecreq = $this->solicitudgrado->getTecnicoReq($dataRow['id']);
                //var_dump($tecnico);die;
           $solvente1 = true;
           foreach ($tecreq as $req) {
            $str = 'Solicitado';

            if ($dataRow[$req['pk_atributo']]!= '0'){
                $str = 'Aprobado';

            }else{
            }
            $this->solicitudgrado->updateEstadoReq($req['pk_documentorequisito'],$str);
        }
        $faltantes = $this->solicitudgrado->getRequisitosTecfaltante($dataRow['id']);
                //var_dump($faltantes);die;
        $solvente = true;
        if($faltantes){
            foreach ($faltantes as $req) {

                if($dataRow[$req["pk_atributo"]]!='0'){
                    $this->solicitudgrado->InsertarRequisito($dataRow['id'],$req["pk_atributo"],$this->aprobado);

                }else{

                    $solvente = false;
                }
            }
        }
        if ($solvente1 && $solvente){

            $this->solicitudgrado->updateEstadoReq($tecnico,'Aprobado');
        }else{

          $this->solicitudgrado->updateEstadoReq($tecnico,'Solicitado');  
      }

  }else{

    $this->solicitudgrado->DeleteTecnicoReqs($dataRow['id']);
}
            //var_dump($dataRow);
}  

$this->getResponse()->setBody(Zend_Json::encode($json));
$this->SwapBytes_Crud_Form->getAddOrEditEnd();

}

public function viewAction() {

    $json = array();
    $dataRow['id'] = (int)$this->_params['modal']['id'];
    for($i = 0;$i < 6;$i++){
        $string[$i] = $this->solicitudgrado->getRequisitos($dataRow['id'],$i+1, NULL);
        $s = 'checkbox0' . ($i+1);
        if($string[$i][0]['estado']){
            $dataRow[$s] = true;

        }
    }
    $tecnico = $this->solicitudgrado->getValorEstadoSolicitud($dataRow['id'],$this->Tsuperior);
    if($tecnico){
            //var_dump($tecnico);die;
        $dataRow['tecnico'] = (bool)$tecnico;
        
        $tecreq = $this->solicitudgrado->getTecnicoReq($dataRow['id']);

        foreach ($tecreq as $req) {
                # code...   
            $dataRow[$req['pk_atributo']] = true;
        }
            //var_dump($dataRow);
            //die;
    }
    $this->SwapBytes_Crud_Form->setProperties($this->view->form, $dataRow, 'Ver requisitos');
    $this->SwapBytes_Crud_Form->setJson($json);
    $this->SwapBytes_Crud_Form->setWidthLeft('270px');
    $this->SwapBytes_Crud_Form->getView();
}

public function tecnicoAction(){

    if ($this->_request->isXmlHttpRequest()){
        $this->SwapBytes_Ajax->setHeader();
        $params = $this->_getAllParams();
        $tecnicoAction = 'true' === $params['tecnico'];
        $json = $this->setCheckboxtecnico($tecnicoAction);
        $this->getResponse()->setBody(Zend_Json::encode($json));
    }
}

private function setCheckboxtecnico($stado){ 

    $json = array();
    $requisitos = $this->solicitudgrado->getRequisitostotales($this->Tsuperior);
    $color = ($stado) ? 'black' : 'grey';
    $nEstado = ($stado) ? 'false' : 'true';
    foreach ($requisitos as $req) {
       if (!$stado){

        $json[] = $this->SwapBytes_Jquery->setAttr($req['pk_atributo'],'checked','false');
        
        }

     $json[] = $this->SwapBytes_Jquery->setAttr($req['pk_atributo'],'disabled',$nEstado);
     $json[] = '$("#'.$req['pk_atributo'].'-label").css("color","'.$color.'")';
     
    }

 return $json;
}

}

?> 
