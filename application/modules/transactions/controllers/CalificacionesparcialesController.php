<?php
/*
 * Guardar al cambiar de  pagina
 *
 */
class Transactions_CalificacionesparcialesController extends Zend_Controller_Action {

	private $Title  = 'Transacciones \ Carga de calificaciones parciales';
	private $MessageTitle = 'Consignar calificaciones parciales';
	private $MateriaEstadoCursada     = 862;
	private $MateriaEstadoPorImprimir = 1255;

	public function init() {
		/* Initialize action controller here */
		Zend_Loader::loadClass('Models_DbTable_Asignaciones');
		Zend_Loader::loadClass('Models_DbTable_Clases');
		Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbView_Calendarios');
		Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Models_DbTable_Usuarios');
		Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Une_Cde_transactions_CalificacionesParciales');
		$this->filtros         			= new Une_Filtros();
        $this->calendarios 				= new Models_DbView_Calendarios();
		$this->asignaciones    			= new Models_DbTable_Asignaciones();
		$this->periodos        			= new Models_DbTable_Periodos();
		$this->RecordAcademico 			= new Models_DbTable_Recordsacademicos();
		$this->grupo           			= new Models_DbTable_UsuariosGrupos();
		$this->usuarios        			= new Models_DbTable_Usuarios();
		$this->clases		   			= new Models_DbTable_Clases();
		$this->SwapBytes_Html           = new SwapBytes_Html();
		$this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
		$this->SwapBytes_Ajax           = new SwapBytes_Ajax();
		$this->SwapBytes_Ajax_Html      = new SwapBytes_Ajax_Html();
		$this->SwapBytes_Ajax_Action    = new SwapBytes_Ajax_Action();
		$this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
		$this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
		$this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
		$this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
		$this->SwapBytes_Uri            = new SwapBytes_Uri();
		$this->SwapBytes_Jquery         = new SwapBytes_Jquery();
		$this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
		$this->authSpace = new Zend_Session_Namespace('Zend_Auth');
		/*Clase para generar listados,validar Calificaciones Parciales*/
		$this->calificacionesParciales = new Une_Cde_Transactions_CalificacionesParciales($this->RecordAcademico,$this->asignaciones,$this->SwapBytes_Jquery);
		$this->filtros->setDisplay(true, true, true, true, true, true, false, true, false);
		$this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
		$this->filtros->setRecursive(true, true, true, true, true, true, false, true, false);
		$this->filtros->setParam('usuario', $this->authSpace->userId);
		$this->SwapBytes_Crud_Action->AddCustum('<button type="button" id="btnFinalizar" style="display: none;float:left;margin-left:10px;">Finalizar</button>');
		$this->SwapBytes_Crud_Action->AddCustum('<button type="button" id="btnValidar" style="display: none;">Guardar</button>');
		$this->SwapBytes_Crud_Action->setDisplay(true, false);
		$this->SwapBytes_Crud_Action->setEnable(true, true);
        $this->SwapBytes_Crud_Search->setDisplay(false);
	}

	/**
	 * Se inicia antes del metodo indexAction, y valida si esta autentificado,
	 * sl no ser asi, redirecciona a al modulo de login.
	 */
	function preDispatch() {

		if(!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_helper->redirector('index', 'login', 'default');
		}

		if(!$this->grupo->haveAccessToModule()) {
			$this->_helper->redirector('accesserror', 'profile', 'default');
		}

	}

	/**
	 * Crea la estructura base de la pagina principal.
 */
	public function indexAction() {
		$this->view->title            = $this->Title;
		$this->view->filters          = $this->filtros;
		$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Ajax   = new SwapBytes_Ajax();
		$this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
		$this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
	}


	public function periodoAction(){
      $this->filtros->getAction(array('usuario','regimen'=>'true'));
     }
	public function sedeAction() {
		$this->filtros->getAction(array( 'usuario','periodo'));
	}

	public function escuelaAction() {
		$this->filtros->getAction(array( 'usuario','periodo', 'sede'));
	}

	public function pensumAction() {
		$this->filtros->getAction(array( 'usuario','periodo', 'sede', 'escuela'));
	}

	public function semestreAction() {
		$this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum'));
	}

	public function materiaAction() {
		$this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum', 'semestre'));
	}

	public function seccionAction() {
		$this->filtros->getAction(array('usuario','periodo', 'sede', 'escuela', 'pensum', 'semestre', 'materia'));
	}

	public function listAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$json   = array();
			$Estado = $this->_getParam('estado');
			$Data   = $this->filtros->getParams();
            $inscritos = (isset($Estado))? true : $this->calificacionesParciales->isConsignado($Data);
			$rows   = $this->calificacionesParciales->listar($Data);
			$json[] = $this->SwapBytes_Jquery->setHtml('tblAutorizacion', '');
			$json[] = $this->SwapBytes_Jquery->setHide('btnFinalizar');
				if(isset($rows) && count($rows) > 0) {
                // Existen Estudiantes
					// Definimos las propiedades de la tabla.
					$table = array('class'  => 'tableData',
						'width'  => 'auto',
						'column' => 'estado',
						/*Colores de las filas dependen de el estado de el record'*/
						'rows'   => array('conditions' => array( 863 => array('equal'=> '863',
						'properties' => array('class' => 'retirado')),
						862 => array('equal'      => '862',
						'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
						864 => array('equal'      => '864',
						'properties' => array('style' => 'background-color:#FFF;color:#000;')),
						1699 => array('equal'      => '1699',
						'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
						1266 => array('equal'      => '1266',
						'properties' => array('style' => 'background-color:#99FF99;color:#666666;')),
					)));

					$columns = array(
					array('column'  => 'pk_recordacademico',
						'primary' => true,
						'hide'    => true),
					array('name'     => '#',
					'width'    => '30px',
					'function' => "rownum",
					'rows'    => array('style' => 'text-align:right')),
					array('name'    => 'C.I.',
					'width'   => '60px',
					'column'  => 'ci',
					'rows'    => array('style' => 'text-align:center')),
					array('name'    => 'Apellido',
					'width'   => '150px',
					'column'  => 'apellido'),
					array('name'    => 'Nombre',
					'width'   => '150px',
					'column'  => 'nombre'),
					array('name'    => 'Estado',
					'width'   => '50px',
					'column'  => 'estado',
					'hide'=>'true',
					'rows'    => array('style' => 'text-align:center')
					)
				);
                    /*interceptamos las evaluaciones del regimen
                        * con las calificaciones */
					$evaluaciones = $this->RecordAcademico->getEvaluacionesParciales($Data);
					$pkinasist = array();
					$colors = array (20054 => '#DFF0ED', 20055 => '#E3FAF6' );
					foreach ($evaluaciones as $evaluacion) {
						if (!$evaluacion['evaluable']) {
							array_push($pkinasist, $evaluacion['pk_atributo']);
						}
						$maximo = $evaluacion['evaluable'] ? $evaluacion['maximo']:'';
						$abrev  = $evaluacion['evaluable'] ? " ({$evaluacion['maximo']})":'';
						$columns[] = array('name'    => "{$evaluacion['abrev']}{$abrev}",
							'column'  => "{$evaluacion['pk_atributo']}_calificacion",
							'width'   => 'auto',
							'rows'    => array('style'     => 'text-align:center;'. "background:{$colors[$evaluacion['fk_lapso']]}"),
												'control' => array('class'     => 'TextBoxNormal',
												'style' 	=> !$evaluacion['evaluable'] ? 'background:#FCFCCC;':"",
												'tag'       => 'input',
												'type'      => 'text',
												'value'     => "##{$evaluacion['pk_atributo']}_calificacion##",
												'title'     => "{$evaluacion['valor']}",
                                                'name'      => $evaluacion['pk_atributo'].'_##pk_recordacademico##',
												'data-valid'=> $maximo,
												'data-mask'=> $evaluacion['evaluable'] ? '99.99':'99',
												!$inscritos? 'disabled' : '' => ''
											)
						);
					}
//					"Muestra la suma de las inasistencias"
                    if (!empty($pkinasist) ) {
                        //Evaluan inasistencias
                        $clases = $this->asignaciones->get_clases_asignacion_feriado($Data);
                        if($clases && isset($clases) && count($clases) > 0) {
                            //Existen Clases
                            $json[] = $this->SwapBytes_Jquery->setHtml('clases', $clases);
                            $json[] = $this->SwapBytes_Jquery->setHtml('inasistencias',$clases );
                            $json[] = $this->SwapBytes_Jquery->setShow('extra');

                        }

					$columns[] = array('name'    => 'T.inasist',
										'column'  => 'tinasist',
										'width'   => '60px',
										'rows'    => array('style' => 'text-align:center;border-left-width:2px;margin-left:5px;'),
										'control' => array('class'=> 'TextBoxNormal',
										'tag'       => 'input',
										'type'      => 'text',
										'value'     => '0',
										'name'      => 'tinasist_##pk_recordacademico##',
                                        'id'        => 'tinasist_##pk_recordacademico##',
										'disabled'  => 'true',
										'title'     => 'Total de la suma de inasistencias',
										





										// Cambio para no considerar inasistencias
										//'data-valid' => ceil($clases * Une_Cde_Transactions_CalificacionesParciales::$regla_insasistencias/100)-1)
										'data-valid' => $clases)
										// Cambio para no considerar inasistencias






                                        //-1 para compensar el <=
                                        //de la comparacion de
                                        //notas que no aplica
                                        //para las inasistencias
                                    );
                    }
					/*Muestra el Total de notas sin Redondear*/
					$columns[] = array('name'    => 'Acu.',
										'column'  => 'calificacion',
										'width'   => '60px',
										'rows'    => array('style'     => 'text-align:center;margin-left:5px;'),
										'control' => array('class'     => 'TextBoxNormal',
										'tag'       => 'input',
										'type'      => 'text',
										'maxlength' => '4',
										'value'     => '0',
										'name'      => 'total_##pk_recordacademico##',
										'id'      => 'total_##pk_recordacademico##',
										'disabled'  => 'true',
										'title'     => 'Total de la suma de evaluaciones',
										'data-valid' => '20')
										);
					/*Muestra el Total de notas*/
					$columns[] = array('name'    => 'Cf.',
										'column'  => 'calificacion',
										'width'   => '60px',
										'rows'    => array('style'     => 'text-align:center;'),
										'control' => array('class'     => 'TextBoxNormal',
										'tag'       => 'input',
										'type'      => 'text',
										'maxlength' => '4',
										'value'     => '0',
										'name'      => 'cf_##pk_recordacademico##',
										'id'        => 'cf_##pk_recordacademico##',
										'title'     => 'Calificación Final',
										'disabled'  => 'true',
										'data-valid' => '20')
										);

					$pk_asignacion = $this->calificacionesParciales->implodeAsignaciones($rows);
					//var_dump($pk_asignacion);die;
                    $total   = sizeof($this->RecordAcademico->getEstudiantes($Data));
                    $HTML = $this->SwapBytes_Crud_List->fill(
                        $table,
                        $rows,
                        $columns
                    );
				$HTML .= $this->SwapBytes_Html->input(array('type' => 'hidden', 'name' => 'idAsignacion', 'value' => $pk_asignacion));
				$json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
				/*validaciones en el grid para tipo de dato decimales,redondeo y validacion de cantidades*/
				$prefix = array ('total' => 'total','inasistencia' => $pkinasist,'calificacion'=>'cf','tinasistencias' => 'tinasist');
				$json[] = $this->calificacionesParciales->GridValidationJs('tblEstudiantes','keyup','[class^=TextBox][type=text]','btnValidar', $prefix);
				$json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatXLS', 'disabled', 'false');
				$json[] = $this->SwapBytes_Jquery->setAttr('rdbFormatPDF', 'disabled', 'false');
				$json[] = $this->SwapBytes_Jquery_Ui_Form->buttonDisable('btnDescargar', false);
				/*Saltamos al sguiente cuadro al darle enter para evitar que selecccione los retirados*/
				$notRetirado = 'function (index, fields) { return !$(fields.eq(index)).closest("tr").hasClass("retirado"); }';
				$json[] = '$("#tblEstudiantes").find("input[class^=TextBox][type=text]").each(function () {$(this).bind("keydown", "return", function(){$(this).focusNextInputField('. $notRetirado .');});})';
				$json[] = '$("input:text:first").focus()';
				/*Movimiento en la tabla con las flechas de teclado*/
				$json[] = '$("#tblEstudiantes").enableCellNavigation();';
				/* js Validacion de los checkbox */
                $json[] =  $this->calificacionesParciales->CheckBoxValidation(





                	// Cambio para no considerar inasistencias
					//'tblEstudiantes','keyup',$prefix, ceil($clases * Une_Cde_Transactions_CalificacionesParciales::$regla_insasistencias/100));
					'tblEstudiantes','keyup',$prefix, $clases);
					// Cambio para no considerar inasistencias

                




				/*Le damos color a las calificaciones invalidas al cargar*/
				$json[] = '$("#tblEstudiantes").find("input[class^=TextBox][type=text]").keyup()';
				$json[] = $this->calificacionesParciales->GridMaskJs('tblEstudiantes');
				$json[] = $this->SwapBytes_Jquery->setShow('divLeyenda');
				$json[] = $this->SwapBytes_Jquery->setShow('descarga');
				$ip = $this->getRequest()->getServer('REMOTE_ADDR');
				$isLocal = $this->isLocalAddress($ip);
                $isValidDate = $this->checkDateForFinalButton($Data);
				if (!$inscritos) {
					/*Ya Consignadas*/
					if ($isLocal) {
                    /*Finalizar habilitado por Control de Estudios*/
                        if(isset($Estado)) {
                            $json[] = $this->SwapBytes_Jquery->setAttr('btnFinalizar','disabled','false');
					        $json[] = $this->SwapBytes_Jquery->setShow('btnFinalizar');
                        } elseif(!$isValidDate){
                        	$HTML  = $this->SwapBytes_Html_Message->alert('El cambio extemporaneo de calificaciones se realiza por Control de Estudios.');
                        	$json[] = $this->SwapBytes_Jquery->setHtml('tblAutorizacion', $HTML);
                        }else {
                        /* Sin Clave de control */
						$HTML  = $this->SwapBytes_Html_Message->alert('Las calificaciones de la asignatura seleccionada se encuentran YA consignadas a Control de Estudios, en caso de que se requiera realizar una modificación, solicite a dicha dirección para que le autorice.');
						$HTML .= "<br>";
						$HTML .= "Contraseña de Operaciones Especiales: <input id='txtOperacionEspecial' type='password' >";
						$HTML .= "&nbsp;";
						$HTML .= "<button type='button' id='btnOperacionEspecial' style='padding:5px;' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only'> Habilitar</button>";
						$HTML  = addslashes($HTML);
						$json[] = $this->SwapBytes_Jquery->setHtml('tblAutorizacion', $HTML);
						$Functions = $this->SwapBytes_Jquery->getValInMD5('txtOperacionEspecial');
						$Functions = $this->SwapBytes_Jquery->getJSON('clave',array('page' => $pageNumber), array('value' => $Functions));
						$json[] = $this->SwapBytes_Jquery->setClick('btnOperacionEspecial', $Functions);
					    $json[] = $this->SwapBytes_Jquery->setShow('tblAutorizacion');
                        }
                    } else {
					    $json[] = $this->SwapBytes_Jquery->setHtml('btnFinalizar','');
                    }
                } else {
                /*No consignadas*/
                    if (!isset($Estado)) {
                    $json[] = $this->SwapBytes_Jquery->setAttr('btnValidar','disabled','false');
					$json[] = $this->SwapBytes_Jquery->setShow('btnValidar');
                    }
					if ($isLocal && ($isValidDate || isset($Estado))) {
                        /*No consignadas y En fecha de Carga de
                         * notas Finales*/
                            $json[] = $this->SwapBytes_Jquery->setAttr('btnFinalizar','disabled','false');
					        $json[] = $this->SwapBytes_Jquery->setShow('btnFinalizar');
                    }
                }
				} else {
                    $HTML = $this->SwapBytes_Html_Message->alert('No existen estudiantes inscritos.');
					$json[] = $this->SwapBytes_Jquery->setHtml('tblEstudiantes', $HTML);
				}
				$json[] = "$('.TextBoxNormal').focus(function(){
							    $(this).css('box-shadow','rgb(98, 180, 81) 0 0 4px 2px');
							    $(this).closest('tr').find('td').each(function(){
							    	$(this).addClass('selected');
							    });
							});
							$('.TextBoxNormal').blur(function(){
								$(this).css('box-shadow','none');
							    $(this).closest('tr').find('td').each(function(){
							    	$(this).removeClass('selected');
							    });
							});";
			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

	public function validarAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$errorGlobal = false;
			$json        = array();
			$Data        = $this->_getParam('data');
			$rows        = $this->SwapBytes_Uri->queryToArray($Data);
			$finalizar   = $this->_getParam('finalizar');
			$filtrosData = $this->filtros->getParams();
            // Validamos si las calificaciones son correctas como dato.
            $isValid = $this->calificacionesParciales->isValid(
                $this->authSpace->userId, $rows, $filtrosData['periodo'], $filtrosData
            );
			$response = $this->calificacionesParciales->isValidAction();
			//Apilamos los js de respuesta en el response
			if (!empty($response)) {
				foreach ($response as $js) {
					$json[] = $js;
				}
			}
			if (($isValid['isValid'] && $finalizar && $isValid['isComplete']) || ($isValid['isValid'] && !$finalizar )) {
                $html  = $this->SwapBytes_Html->img(
                    $this->view->baseUrl() . '/images/icons/select-48.png',
                    array('style' => 'float:left;margin-right: 8px;')
                );
				if ($finalizar) {
					/* Mensaje para Finalizar*/
					$html .= $this->calificacionesParciales->isValidEndMessage();
				} else {
					$html .= $this->calificacionesParciales->isValidMessage();
				}
				$html .= $this->SwapBytes_Html->input(array('type'  => 'hidden',
                                                            'name'  => 'finalizar',
                                                            'value' => $finalizar));
				$html .= $this->SwapBytes_Html->input(array('type'  => 'hidden',
                                                            'name'  => 'idAsignaciones',
                                                            'value' => $rows['idAsignacion']));
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Cancelar');
			} else {
				$html  = $this->SwapBytes_Html->img(
                    $this->view->baseUrl() . '/images/icons/error-48.png',
                    array('style' => 'float:left;margin-right: 8px;')
				);
				$html .= $this->calificacionesParciales->isNotValidMessage();
				$html .= $this->SwapBytes_Html->getList(array('Calificaciones entre el número 0 y la máxima calificacion de la evaluación.',
				'Solo se permiten números enteros o decimales ej (1.2)','No se permiten letras.','No se permiten caracteres especiales.','<b>Para finalizar la carga de notas se deben llenar todas las calificaciones de todos los estudiantes</b>'),
				array('style' => 'padding-left:25px')
				);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmMessage', 'Cancelar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmMessage', 'Guardar');
			}
            $json[] = $this->SwapBytes_Jquery->setHtml('frmMessage', $html);
            $json[] = $this->SwapBytes_Jquery->setAttr('frmMessage', 'style', "'text-align:left;'");
            $json[] = $this->SwapBytes_Jquery_Ui_Form->changeTitle('frmMessage', $this->MessageTitle);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->open('frmMessage');
            $this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

	public function guardarAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();
			$params  = $this->_getAllParams();
			$Data        = $this->_getParam('data');
			$rows        = $this->SwapBytes_Uri->queryToArray($Data);
			$filtrosData = $this->filtros->getParams();
            $results = $this->calificacionesParciales->isValid(
                $this->authSpace->userId, $rows, $filtrosData['periodo'], $filtrosData
            );
			$finalizar = $this->_getParam('finalizar');
			$isCommit = isset($finalizar) && $finalizar == 'true';
			if ($results['isValid']) {
					$ip = $this->getRequest()->getServer('REMOTE_ADDR');
					$isLocal = $this->isLocalAddress($ip);
                    $isValidDate = $this->checkDateForFinalButton($filtrosData);
				if (($isCommit && $isLocal && $results['isComplete'])) {
					//Finalizar
                    $isSuccess = $this->calificacionesParciales->transaction_cxud(
                        $rows, $isCommit, $filtrosData
                    );
					if ($isSuccess) {
                        /* Oculto los botones y listo de Nuevo al
                         * Finalizar Correctamente*/
                        $this->calificacionesParciales->setEstadoMateria(
                            $rows['idAsignacion'], $this->MateriaEstadoPorImprimir
                        );
                        $json[] = $this->SwapBytes_Jquery->setAttr('btnValidar','disabled','true');
                        $json[] = $this->SwapBytes_Jquery->setHide('btnValidar');
                        $json[] = $this->SwapBytes_Jquery->setAttr('btnFinalizar','disabled','true');
			            $json[] = $this->SwapBytes_Jquery->setHide('btnFinalizar');
                        $json[] = $this->SwapBytes_Jquery->getJSON(
                            'list', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros'))
                        );
					}
                }
                //Actualizar solo las notas parciales
                elseif ($results['isValid'] && !$isCommit) {
                    /*Verifico si la asignatura es de carga
                     * Continua*/
                    $isContinuo = $this->calificacionesParciales->isAsignaturaRegimenContinuo($filtrosData);
                    //Guardar
                    $isSuccess  = $this->calificacionesParciales->transaction_cxud(
                        $rows, $isContinuo, $filtrosData
                    );
					if ($isContinuo && $isSuccess) {

                        $json[] = $this->SwapBytes_Jquery->getJSON(
                            'list', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros'))
                        );
                    }
                }
            /* Al finalizar de Guardar Evitamos que salga el modal
                * de Confirmación */
            $json[] = "\$j(window).unbind('beforeunload');window.isChange = false;";
			$json[] = $this->SwapBytes_Jquery_Ui_Form->close('frmMessage');
			}
        $this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}


    public function claveAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $json       = array();
            $Contraseña = $this->_getParam('value');
            $pageNumber = $this->_getParam('page', 1);
			$ip = $this->getRequest()->getServer('REMOTE_ADDR');
            $isLocal = $this->isLocalAddress($ip);
            if ($this->usuarios->checkPasswordOperacionesEspeciales(1253, $Contraseña) && $isLocal ) {
				$json[] = $this->SwapBytes_Jquery->setHtml("tblEstudiantes","");
                $json[]    = $this->SwapBytes_Jquery->getJSON('list',array('page' =>$pageNumber), array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros'),
                                                                                  'estado' => $this->MateriaEstadoCursada));
            } else {
                $json[] = $this->SwapBytes_Jquery->setVal('txtOperacionEspecial');
                $json[] = $this->SwapBytes_Jquery->setFocus('txtOperacionEspecial');
                $message = 'La clave que escribio es Invalida.';
                $this->SwapBytes_Crud_Form->getDialog('Advertencia', $message);
            }
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
    }

    public function descargarAction() {
            $this->SwapBytes_Ajax->setHeader();
            $encodedData = $this->_getParam('data');
            $data= $this->SwapBytes_Uri->queryToArray($encodedData);
            $params = array ('sede' => $data['selSede'],
                'escuela' => $data['selEscuela'],
                'periodo' => $data['selPeriodo'],
                'semestre' => $data['selSemestre'],
                'materia' => $data['selMateria'],
                'seccion' => $data['selSeccion'],
                'pensum' => $data['selPensum']
            );
            $outstream = $this->calificacionesParciales->generarReporte($params);
            $this->getResponse()->setBody(base64_decode($outstream));
    }

    private function isLocalAddress($ip) {
	$ipAddress = explode(".", $ip);

	// Comprueba si la sesión es desde una red local - Comentar si no se desea verificar si la sesión es local
        // Segmentos de red Local
	// if ($ipAddress[0] == 192 && $ipAddress[1] == 168 ||
        // 	$ipAddress[0] == 172 && $ipAddress[1] == 16 ||
        // 	$ipAddress[0] == 10 || $ipAddress[0] == 127   )
	//	return true;
	// else
	//	return false;

	// Descomentar la siguiente linea para no verificar si la sesión es local
	return true;
    }

    private function checkDateForFinalButton($data) {
        $periodo = $data['periodo'];
        //Revisamos la Fecha de Carga de Callificaciones
        $periodoActividad = $this->calendarios->getPeriodoActividad($periodo, $data);
        return $periodoActividad[0]['isvalid'];
    }
}

