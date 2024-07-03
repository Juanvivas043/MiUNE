<?php
class Consultas_IndicerepitenciaController extends Zend_Controller_Action {

    public function init() {
		Zend_Loader::loadClass('Une_Filtros');
		Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');


        $this->Une_Filtros     = new Une_Filtros();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();


        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
        $this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        /*Filtros*/
        $this->_params['filters'] = $this->Une_Filtros->getParams();

      	$this->Une_Filtros->setDisplay(true, true,true);
      	$this->Une_Filtros->setRecursive(true, true,true);
       	/*Botones de Acciones*/
     	$this->SwapBytes_Crud_Action->setDisplay(true,false);
      	$this->SwapBytes_Crud_Action->setEnable(true,false);
      	$this->SwapBytes_Crud_Search->setDisplay(false); 

    }

    //Acciones referidas al index
    public function indexAction() {
        $this->view->title = "Consultas \ Indice de repitencia por materia";
        $this->view->filters = $this->Une_Filtros;
        $this->view->module = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;

    }

  
	  public function periodoAction() {
	    $this->Une_Filtros->getAction();
	  }

	  public function sedeAction() {
	    $this->Une_Filtros->getAction(array('periodo'));
	  }

	  public function escuelaAction() {
	    $this->Une_Filtros->getAction(array('periodo', 'sede'));
	  }

	  public function listAction(){
	          if ($this->_request->isXmlHttpRequest()) {

	            $this->SwapBytes_Ajax->setHeader();         
	            $html = $this->getIndiceRepitenciaMateria($this->_params['filters']['periodo'], $this->_params['filters']['sede'], $this->_params['filters']['escuela']);
	  
	            $json[] = $this->SwapBytes_Jquery->setHtml('tblIndice',addslashes($html));
	            $json[] =  "$( '#tableUniversidad' ).prepend( '<thead><tr><th style=\"text-align:center;font-family: Arial,sans-serif; font-size: 12px;color:#666;font-weight: bold; text-transform: uppercase; \">Total Universidad</th></tr></thead>' );
        					$( '#tableEscuela' ).prepend( '<thead><tr><th style=\"text-align:center;font-family: Arial,sans-serif; font-size: 12px;color:#666;font-weight: bold; text-transform: uppercase; \">Total Escuela</th></tr></thead>' );";
				$this->getResponse()->setBody(Zend_Json::encode($json));
	          }
	    }

	  public function getIndiceRepitenciaMateria($periodo,$sede,$escuela){
	  	$rowsAsignaturas	=	$this->RecordAcademico->getIndiceRepitenciaporMateria($periodo,$escuela,$sede);
	  	$rowsEscuela = $this->RecordAcademico->getIndiceRepitenciaEscuela($periodo,$sede,$escuela);
	  	$rowsUniversidad = $this->RecordAcademico->getIndiceRepitenciaUniversidad($periodo,$sede);

		if (is_null($rowsAsignaturas)){
	  	 	$HTML = "<div class='alert'><center><p style='margin-left:20px;'> Usted no tiene materias con repitencia para esta escuela/periodo/sede</p></center></div>";
	 	}else{
	  		$json = array();
	  		//Definimos los valores de la tabla 

           	$configTableAsignaturas = array('class'  => 'tableData',
                           'width'  => '900px',
                           'column' => 'disponible');
            $tableAsignaturas = array(
            		 		array('name'     => '#',
                                   'width'    => '20px',
                                   'function' => 'rownum',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            				array('name'     => 'Asignatura',
                                   'column'   => 'materia',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            				array('name'     => 'Semestre de ubicación',
                                   'column'   => 'semestre',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            				array('name'     => 'Repitientes',
                                   'column'   => 'repitientes',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            				array('name'     => 'Inscritos',
                                   'column'   => 'inscritos',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            				array('name'     => 'Porcentaje',
                                   'column'   => 'porcentajerepitientes',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
            	);
            $configTableEscuela = array('class'  => 'tableData',
            			   'id' => 'tableEscuela',
                           'width'  => '370px',
                           'column' => 'disponible');

            $tableEscuela = array(
            					array('name'     => 'Escuela',
                                   'column'   => 'escuela',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            					   array('name'     => 'Inscritos',
                                   'column'   => 'insc',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                    array('name'     => 'Repitientes',
                                   'column'   => 'rep',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                    array('name'     => 'Porcentaje',
                                   'column'   => 'porcentajerepitientes',
                                   'rows'     => array('style' => 'text-align:center')
                                   )

            					 );
            $configTableUniversidad = array('class'  => 'tableData',
            				'id' => 'tableUniversidad',
            				'<thead>'=>'<tr><th> Total Universidad</tr></th></thead>',
                           'width'  => '370px',
                           'column' => 'disponible');
            $tableUniversidad = array(
            					array('name'     => 'Inscritos',
                                   'column'   => 'totalinscritos',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
            					   array('name'     => 'Repitientes',
                                   'column'   => 'repitientes',
                                   'rows'     => array('style' => 'text-align:center')
                                   ),
                                   array('name'     => 'Porcentaje',
                                   'column'   => 'porcentajerepitientes',
                                   'rows'     => array('style' => 'text-align:center')
                                   )
                                );

            $HTML  = $this->SwapBytes_Crud_List->fill($configTableEscuela, $rowsEscuela, $tableEscuela);
            $HTML  .= $this->SwapBytes_Crud_List->fill($configTableUniversidad, $rowsUniversidad, $tableUniversidad);
            $HTML   .= $this->SwapBytes_Crud_List->fill($configTableAsignaturas, $rowsAsignaturas, $tableAsignaturas);

            return $HTML;

		}
	 }

	 
  

}
    
