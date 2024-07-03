<?php

class Transactions_DatostesisestudianteController extends Zend_Controller_Action {

    private $Title = 'Transacciones \ Datos Tesis (Estudiante)';
    private $FormTitle_AgregarTesis = 'Agregar Tesis'; 
    private $FormTitle_AgregarTesista = 'Agregar otro Tesista';
    private $FormTitle_AgregarTutor = 'Agregar  Tutor';
    private $FormTitle_DescargarPlanilla = 'Descargar Planilla';
    private $FormTitle_EditarTutor = 'Editar Tutor';
    private $FormTitle_Renunciatesista = 'Renuncia Tema';
    private $FormTitle_Renunciatutor = 'Renuncia Tutor';
    private $FormTitle_NuevotuTor = 'Nuevo Tutor';

    // para validar que pueda guardar los cambios en otro tesista o tutor
    private $si = '_-si-_';
    private $no = '_-no-_';   

    private $maxtutorperiodo = 3;
  
    public function init() {
        /* Initialize action controller here */
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Clases');
        Zend_Loader::loadClass('Models_DbView_Estrategias');
        Zend_Loader::loadClass('Models_DbView_Evaluaciones');
        Zend_Loader::loadClass('Models_DbTable_Tesis');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');
        Zend_Loader::loadClass('Forms_Tesis');
        Zend_Loader::loadClass('Forms_OtroTesista');
        Zend_Loader::loadClass('Forms_Tutortesis');
        Zend_Loader::loadClass('Forms_Planilla');
        Zend_Loader::loadClass('Forms_Renuncia');
        Zend_Loader::loadClass('Forms_Nuevotutortesis');

        $this->Asignaciones    = new Models_DbTable_Asignaciones();
        $this->grupo           = new Models_DbTable_UsuariosGrupos();
        $this->Clases          = new Models_DbTable_Clases();
        $this->vw_estrategias  = new Models_DbView_Estrategias();
        $this->vw_evaluaciones = new Models_DbView_Evaluaciones();
        $this->tesis           = new Models_DbTable_Tesis();
        $this->filtros         = new Une_Filtros();
        $this->periodos        = new Models_DbTable_Periodos();
        $this->Usuarios         = new Models_DbTable_Usuarios(); 
        $this->RecordAcademico      = new Models_DbTable_Recordsacademicos();

        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Form2          = new SwapBytes_Form();
        $this->SwapBytes_Form3          = new SwapBytes_Form();
        $this->SwapBytes_Form4          = new SwapBytes_Form();
        $this->SwapBytes_Form5          = new SwapBytes_Form();
        $this->SwapBytes_Form6          = new SwapBytes_Form();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui      = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();

        
//        formularios
        $this->view->form_tesis = new Forms_Tesis();
        $this->view->form_usuario = new Forms_OtroTesista();
        $this->view->form_tutor = new Forms_Tutortesis();
        $this->view->form_planilla = new Forms_Planilla();

        $this->view->form_renuncia = new Forms_Renuncia();
        $this->view->form_nuevotutortesis = new Forms_Nuevotutortesis();
        
        $this->logger = Zend_Registry::get('logger');
        
        $this->SwapBytes_Form->set($this->view->form_tesis);
        $this->SwapBytes_Form2->set($this->view->form_usuario);
        $this->SwapBytes_Form3->set($this->view->form_tutor);
        $this->SwapBytes_Form4->set($this->view->form_planilla);
        $this->SwapBytes_Form5->set($this->view->form_renuncia);
        $this->SwapBytes_Form6->set($this->view->form_nuevotutortesis);
       

        $this->view->form_tesis = $this->SwapBytes_Form->get();
        $this->view->form_usuario = $this->SwapBytes_Form2->get();
        $this->view->form_tutor = $this->SwapBytes_Form3->get();
        $this->view->form_planilla = $this->SwapBytes_Form4->get();
        $this->view->form_renuncia = $this->SwapBytes_Form5->get();
        $this->view->form_nuevotutortesis = $this->SwapBytes_Form6->get();

      
       
        /*
         * Configuramos los filtros.
         */
        
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        
        $this->filtros->setParam('usuario', $this->authSpace->userId);

        /*
         * Configuramos los botones.
         */
        $this->SwapBytes_Crud_Action->setDisplay(true, false, false, false, false, false);
        $this->SwapBytes_Crud_Action->setEnable(true, false, false, false, false, false);
                
               
        $this->SwapBytes_Crud_Search->setDisplay(false);

        

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnDescargar" onclick="planilla()" class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnDescargar" role="button" aria-disabled="false">
                                                 Planillas
                                                </button>');

     

        $this->SwapBytes_Crud_Action->addCustum('<button id="btnRenunciar" onclick="renunciar()"  class="ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only" name="btnRenunciar" role="button" aria-disabled="false">
                                                Renuncia Tesista
                                                </button>');
        
        $this->SwapBytes_Crud_Action->addJavaScript('$("#btnRenunciar").hide();$("#btnDescargar").hide();');
        

        /*
         * Obtiene los parametros de los filtros y del modal.
         */
        $this->_params['modal']   = $this->SwapBytes_Crud_Form->getParams();
        $this->_params['filters'] = $this->filtros->getParams();


    }

   function preDispatch() {
      if (!Zend_Auth::getInstance()->hasIdentity()) {
          $this->_helper->redirector('index', 'login', 'default');
      }

      if (!$this->grupo->haveAccessToModule()) {
          $this->_helper->redirector('accesserror', 'profile', 'default');
      }
  }

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        
        $this->view->filters = $this->filtros;
        $this->view->title = $this->Title;

        $this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Jquery_Ui_Form = $this->SwapBytes_Jquery_Ui_Form;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);        
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
        
        $this->SwapBytes_Ajax->setHeader();
        
        $cedula = $this->authSpace->userId;
        $periodo = $this->periodos->getUltimo();
        
        $pensum = $this->tesis->getUltimoPensum($cedula);
        $pensum = $this->tesis->getPensumCodigopropietario($pensum[0]['fk_pensum']);
        $entra = true;


        $inscrito = $this->tesis->getTesisInscrito($cedula,$pensum);
        
        if($inscrito >= 1){
            $HTML .= $this->datos();
            
            $nombretesis = $this->tesis->getTesisNombre($cedula,null);


            if(!empty($nombretesis)){

                $tesistas = $this->tesis->getTesistasByTesis($nombretesis[0]['pk_datotesis'],NULL);
                
                if(!empty($tesistas)){//valido si debo mostrar un mensaje

                    $tesis_escuela = $this->tesis->getLineaTemaTesis($nombretesis[0]['pk_datotesis'],NULL); 
                    $tesis_escuela = $tesis_escuela[0]['fk_escuela'];

                    foreach ($tesistas as $tesista) {
                        if($tesis_escuela == $tesista['fk_atributo']){
                            $entra = false;
                            break;
                        }
                    }

                    if($entra == true){

                        $message = '<div id="advertencia" style="width:400px;height:auto;">El sistema ha detectado que la Linea de Investigacion que actualmente posee el tema no corresponde a la carrera del (de los) tesista(s), por lo que debe cambiarla y comunicarse con su dirección de escuela para su re-aprobación. Así como descargar de nuevo las planillas correspondientes para llevarlas a Coordinacion de Trabajo de Grado. <br><br>';
                        $message .= '<button type="button" onclick="editar_tesis('.$nombretesis[0]['pk_datotesis'].')" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only " role="button" aria-disabled="false" style="display: inline-block;"><span class="ui-button-text">Cambiar Linea</span></button>';
                        $message .= '</div>';
                        $this->SwapBytes_Crud_Form->setProperties(null,null, 'Advertencia');
                        $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $message);
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');   
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');
                        $this->SwapBytes_Crud_Form->setJson($json);
                        $this->SwapBytes_Crud_Form->getAddOrEditLoad(); 
                    }else{

                        if($nombretesis[0]['valor'] == 'Aprobado'){
                        $json[] .= $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('btnDescargar','btnRenunciar'));    
                        }else{
                            $json[] .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('btnDescargar','btnRenunciar')); 
                        }

                        $json[] .= $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                        $this->getResponse()->setBody(Zend_Json::encode($json));
                    }
                }else{

                    //solo puede descargar planillas o renunciar con el tema aprobado
                    if($nombretesis[0]['valor'] == 'Aprobado'){
                        $json[] .= $this->SwapBytes_Jquery_Ui_Form->showThisInputs(array('btnDescargar','btnRenunciar'));    
                    }else{
                        $json[] .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('btnDescargar','btnRenunciar')); 
                    }

                    $json[] .= $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                }

                
                
            }else{
                $json[] .= $this->SwapBytes_Jquery_Ui_Form->hideThisInputs(array('btnDescargar','btnRenunciar'));
                $json[] .= $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
                $this->getResponse()->setBody(Zend_Json::encode($json));
            }
            
        }else{
            $HTML = $this->SwapBytes_Html_Message->alert("Usted <b>No Tiene</b> inscrita ninguna materia relacionada al proceso de trabajo de grado ");
            $json[] .= $this->SwapBytes_Jquery->setHtml('tableData', $HTML);
            $this->getResponse()->setBody(Zend_Json::encode($json));
            
        }
        
    }
    
    
     public function addoreditloadtesisAction() {
        // Obtenemos los parametros que se esperan recibir.
       if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $id = $this->_getParam('id');
            $cod = $this->_getParam('cod');
            $cedula = $this->authSpace->userId;
            
            if($id == 2){//editar
                $estado = $this->tesis->getTesisEstado($cod);
                
                if($estado[0]['estado']=='Por Aprobar' || $estado[0]['estado']=='No Aprobado'){//por revisar
                    $dataRow = $this->tesis->getLineaTemaTesis($cod,$cedula);

                    $dataRow = $dataRow[0];
                    
                    $escuela = $this->tesis->getTesistaEscuela($cedula);

                    $lineainvestigacion = $this->tesis->getTesisLinea($escuela[0]['fk_atributo']);
                    $tema = $this->tesis->getTesisTema($dataRow['fk_lineainvestigacion']);

                    $this->SwapBytes_Crud_Form->setProperties($this->view->form_tesis,$dataRow, 'Editar Tesis');
                    $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion', $lineainvestigacion, 'pk_atributo', 'lineainvestigacion'); 
                    $this->SwapBytes_Crud_Form->fillSelectBox('fk_tema', $tema, 'pk_atributo', 'tema'); 
                    $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_lineainvestigacion',$dataRow['fk_lineainvestigacion']);
                    $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tema',$dataRow['fk_tema']);
                    
                    /*oculto los botones usados por el Modal*/
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

                    /*llamo a los botones del formulario tesis*/
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('tesis', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('tesis', 'Cancelar');

                    $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\"#guardar_tesis\").click(function(data){confirm_tesis(2)});');
                    $json[] = "$( '#cancelar_tesis' ).clone().appendTo( '#guardar_tesis-element' );$('#cancelar_tesis-element').remove();";
                    $this->SwapBytes_Crud_Form->setJson($json);
                }else{//revisado
                    $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', 'Comuniquese con su Director de Escuela para habilitar la edicion de su Trabajo de Grado, puesto que está <b>Aprobado</b>');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $this->SwapBytes_Crud_Form->setJson($json);
                }
                
            }else{//agregar
                $cedula = $this->authSpace->userId;
                $escuela = $this->tesis->getTesistaEscuela($cedula);

                $lineainvestigacion = $this->tesis->getTesisLinea($escuela[0]['fk_atributo']);
                $tema = $this->tesis->getTesisTema($lineainvestigacion[0]['pk_atributo']);
                $this->SwapBytes_Crud_Form->setProperties($this->view->form_tesis,null, $this->FormTitle_AgregarTesis,null);
                $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion', $lineainvestigacion, 'pk_atributo', 'lineainvestigacion'); 
                $this->SwapBytes_Crud_Form->fillSelectBox('fk_tema', $tema, 'pk_atributo', 'tema'); 
                $this->SwapBytes_Crud_Form->enableElements(true);

                /*oculto los botones usados por el Modal*/
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

                /*llamo a los botones del formulario tesis*/
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('tesis', 'Guardar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('tesis', 'Cancelar');

                $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\"#guardar_tesis\").click(function(data){confirm_tesis(1)});');
                $json[] = "$( '#cancelar_tesis' ).clone().appendTo( '#guardar_tesis-element' );$('#cancelar_tesis-element').remove();";
                $this->SwapBytes_Crud_Form->setJson($json); 
            }

            $this->SwapBytes_Crud_Form->getAddOrEditLoad();  
       }
    }
    
    public function addoreditloadotrotesistaAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $this->SwapBytes_Crud_Form->setProperties($this->view->form_usuario,null, $this->FormTitle_AgregarTesista,null);
            
            $json[] = "$('#fechanacimiento-label').hide();";
            $json[] = "$('#fechanacimiento-element').hide();";
            $json[] = "$('#sexo-label').hide();";
            $json[] = "$('#sexo-element').hide();";
            $json[] = "$('#nacionalidad-label').hide();";
            $json[] = "$('#nacionalidad-element').hide();";
            $json[] = "$('#direccion-label').hide();";
            $json[] = "$('#direccion-element').hide();";
            $json[] = "$('#telefono-label').hide();";
            $json[] = "$('#telefono-element').hide();";
            $json[] = "$('#telefono_movil-label').hide();";
            $json[] = "$('#telefono_movil-element').hide();";
            $json[] = "$('#correo-label').hide();";
            $json[] = "$('#correo-element').hide();";

            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\"#guardar_otrotesista\").click(function(data){agregar_otrotesista()});');  

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        } 
    } 
    
    
    public function addoreditloadtutorAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $id = $this->_getParam('id');
            $cod = $this->_getParam('cod');
            $pk_datotesis = $this->_getParam('pk');
                
            $hide = false;
            $hide_titulo = false;
            
            if($id == 1){//agregar
                
                $hide = true;
                $hide_titulo = true;

                $tipostutores = $this->tesis->getTutoresTipos(NULL);
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor,null, $this->FormTitle_AgregarTutor,null);
                $this->SwapBytes_Crud_Form->fillSelectBox('fk_tipo', $tipostutores, 'pk_atributo', 'valor');

                       
            }elseif($id == 2){//editar

                $tutorexterno = $this->tesis->getTutorTipoExterno();
                $tipotutor = $this->tesis->getTutorTipo($cod);
                $tipostutores = $this->tesis->getTutoresTipos($tipotutor[0]['tipo']);

                if($tipotutor[0]['tipo'] == $tutorexterno){
                    $tutor =  $this->Usuarios->getRow($cod);
                    $titulo_academico = $this->tesis->getTutorTituloAcademico($cod,$pk_datotesis);
                    $tutor['titulo_academico'] = $titulo_academico[0]['titulo_academico'];

                    $hide = false;
                    $hide_titulo = false;
                }else{
                    $tutor    = $this->tesis->getUsuarioTutorDatos($cod);
                    $tutor = $tutor[0];

                    $hide = true;
                    $hide_titulo = true;
                }


                $tutor['id'] = $tutor['pk_usuario'];
                $tutor['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($tutor['fechanacimiento']);
                
                $grupo = $this->tesis->getUsuariogrupo($cod,"(select pk_atributo 
                                                            from vw_grupos
                                                            where pk_atributo in (855,854,1745))");
                
//                me aseguro de que No pueda editar los datos del personal Docente/Administrativo de la Universidad
                // if(empty($grupo)){
                   
                    $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor,$tutor, $this->FormTitle_EditarTutor,null);
                    $this->SwapBytes_Crud_Form->fillSelectBox('fk_tipo', $tipostutores, 'pk_atributo', 'valor');
                   
                    $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                    $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                    $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$cod}'");

                    $tipotutor = $this->tesis->getTutorTipo($cod);
                    
                    $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$tipotutor[0]['tipo']);

                // }else{
                //     $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor,null, 'Advertencia');
                //     $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', 'No puedes editar los datos del personal Docente/Administrativo de la Universidad');
                //     $json[] = "$('#frmModal').parent().find(\"button:contains('Imprimir')\").children().html('Guardar');"; 
                //     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    
                // }
                
              
                // $this->SwapBytes_Form3->readOnlyElement('pk_usuario');
            }

            if($hide == false){
               
                $json[] = "$('#fechanacimiento-label').show();";
                $json[] = "$('#fechanacimiento-element').show();";
                $json[] = "$('#sexo-label').show();";
                $json[] = "$('#sexo-element').show();";
                $json[] = "$('#nacionalidad-label').show();";
                $json[] = "$('#nacionalidad-element').show();";
                $json[] = "$('#direccion-label').show();";
                $json[] = "$('#direccion-element').show();";
                $json[] = "$('#telefono-label').show();";
                $json[] = "$('#telefono-element').show();";
                $json[] = "$('#telefono_movil-label').show();";
                $json[] = "$('#telefono_movil-element').show();";
                $json[] = "$('#correo-label').show();";
                $json[] = "$('#correo-element').show();";
            }else{
                
                $json[] = "$('#fechanacimiento-label').hide();";
                $json[] = "$('#fechanacimiento-element').hide();";
                $json[] = "$('#sexo-label').hide();";
                $json[] = "$('#sexo-element').hide();";
                $json[] = "$('#nacionalidad-label').hide();";
                $json[] = "$('#nacionalidad-element').hide();";
                $json[] = "$('#direccion-label').hide();";
                $json[] = "$('#direccion-element').hide();";
                $json[] = "$('#telefono-label').hide();";
                $json[] = "$('#telefono-element').hide();";
                $json[] = "$('#telefono_movil-label').hide();";
                $json[] = "$('#telefono_movil-element').hide();";
                $json[] = "$('#correo-label').hide();";
                $json[] = "$('#correo-element').hide();";
            }


            if($hide_titulo == false){
                $json[] = "$('#titulo_academico-label').show();";
                $json[] = "$('#titulo_academico-element').show();";
            }else{
                $json[] = "$('#titulo_academico-label').hide();";
                $json[] = "$('#titulo_academico-element').hide();";
            }
            

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();     
            
        }
    }

    public function addoreditloadnuevotutorAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();

            $dataRow['pk_tutortesis'] = $datos['cod'];

            
            $this->SwapBytes_Crud_Form->setProperties($this->view->form_nuevotutortesis,$dataRow, $this->FormTitle_NuevotuTor,null);
            
            $json[] = "$('#nuevotutor').prepend('<div id= \"txtNuevotutor\"></div>');";

            $json[] = $this->SwapBytes_Jquery->setHtml('txtNuevotutor', '¿Está seguro que desea continuar?, a continuacion se procederá a eliminar el tutor actual, para que pueda agregar uno nuevo');            

            /*oculto los botones usados por el Modal*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

            /*llamo a los botones del formulario nuevotutor*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('nuevotutor', 'Aceptar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('nuevotutor', 'Cancelar');

            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 180);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300);

            $json[] = $this->SwapBytes_Jquery_Ui_Form->addJscript('$(\"#guardar_nuevotutor\").click(function(data){delete_tutor()})');  

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }

    
   /*confirm para las tesis*/ 
    public function addoreditconfirmtesisAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $datos = $this->_getAllParams();

            $datos['titulo'] = str_replace('[!*!]', '%', $datos['titulo']); 


            $longitud = strlen($datos['titulo']); 
            $mod = $datos['mod'];
            $cedula = $this->authSpace->userId;
            
            if($datos['id']=='undefined'){die;}
            
            // if($longitud <= 255){
                
                if($mod==1){//viene de agregar, por lo que se comprueba si existe el tema en la bd

                    $existe = $this->tesis->getTesisExiste($datos['titulo']);
                    $periodo = $this->tesis->getPeriodoActual();

                    if(empty($periodo)){
                        $periodo = $this->tesis->getPeriodoAnterior();
                    }

                    if($existe==0){//validamos si no existe una tesis igual

                        if(!empty($periodo)){//validamos que solo pueda crearla dentro del periodo 

                            //tesis
                            $pk_linea = $this->tesis->getLineaPk($datos['linea'], $datos['tema']);

                            $this->tesis->addTesis($pk_linea,$datos['titulo']);

                            //tesista
                            $pk_datotesis = $this->tesis->getTesisByTitulo($datos['titulo']);
                            $pk_usuariogrupo = $this->tesis->getUsuariogrupo($cedula,855);
                            $this->tesis->addTesista($pk_datotesis,$pk_usuariogrupo,$periodo);

                            //mencion default -> ninguna     
                            $mencion_ninguna = $this->tesis->getAtributoMencionNinguna();                       
                            $this->tesis->addMencionTesis($pk_datotesis,$mencion_ninguna);

                            //tbl_tesis (registro vacio)
                            $this->tesis->addTesisBiblioteca($pk_datotesis,$periodo);

                            //actualizo la cota de la tesis
                            $pk_tesis = $this->tesis->getTesisByFK($pk_datotesis);

                            $ultimacota = $this->tesis->getUltimaCota();

                            $ultimacota = str_replace('TG', '', $ultimacota);

                            $ultimacota = $ultimacota + 1;
                            
                            $cota = '';
                            // ya esto se actualizo, las cotas no se asignan así... la cota de ahora
                           //  en adelante seran vacias
                            $sede = $this->tesis->getSedeEstudiante($cedula);
                            
                            $escuela = $this->tesis->getTesistaEscuela($cedula);
                            $escuela = $escuela[0]['fk_atributo'];

                            $this->tesis->updateTesisBiblioteca($pk_tesis,$cota,$sede,$escuela);
                        }
                    
                    }
                    
                }else{//viene de editar, por lo que se omite la comprobacion
                    $pk_datotesis = $datos['id'];

                    $tesisexiste = $this->tesis->getLineaTemaTesis($pk_datotesis,$cedula);

                    if(!empty($tesisexiste)){//valido que solo puedas editar tu tesis
                        $pk_linea = $this->tesis->getLineaPk($datos['linea'], $datos['tema']);
                        $this->tesis->updateTesis($pk_datotesis,$datos['titulo'], $pk_linea);    
                    }
                    
                }
                
                $this->SwapBytes_Crud_Form->getAddOrEditEnd();
                
            // }
        }
    }   

    /*confirm para el otro tesista y el tutor (al fin y al cabo son usuarios)*/
    public function addoreditconfirmAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $queryString = $this->_getAllParams();
            
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString['data']);
            
            $tipo = null;
            $cedula = $this->authSpace->userId;
            
            

            if(count($queryArray) == 0){
                die;
            }else{//lo relacionado a los formularios
                

                if(empty($queryArray['fk_tipo'])){//formulario tesista
                    $tipo = 1;
                }else{//formulario tutor
                    $tipo = 2;
                }
                
                if($tipo == 1){//estudiante
                    
                    if($queryArray['permiso_tesista'] == md5($this->si)){

                        $tesis_inscrito = $this->tesis->getTesisInscrito($queryArray['pk_usuario'],NULL);

                        $tesistacount = $this->tesis->getTesistasCount($datosestudiante[0]['usuario']);
                        

                        // valido que no se agrege el mismo, que tenga alguna materia de tesis inscrita y que no tenga otra tesis asociada respectivamente
                        if(($cedula == $queryArray['pk_usuario']) ||($tesis_inscrito == 0) || ($tesistacount >= 1)){
                            die;
                        }else{//significa que esta calificado para ser compañero de tesis

                            $usuariogrupo = $this->tesis->getUsuariogrupo($queryArray['pk_usuario'],855);
                            $periodo = $this->tesis->getPeriodoActual();

                            if(empty($periodo)){
                                $periodo = $this->tesis->getPeriodoAnterior();
                            }

                            $tesis = $this->tesis->getTesisNombre($cedula);
                            $this->tesis->addTesista($tesis[0]['pk_datotesis'],$usuariogrupo,$periodo);
                        }

                    }else{
                        die;
                    }
                    
                }elseif($tipo == 2){//tutor

                    // if($queryArray['permiso_tutor']  ==  md5($this->si)){//cumple los requisitos establecidos en el exist, las validaciones aqui abajo son por doble seguridad

                        $tesis = $this->tesis->getTesisNombre($cedula);
                        $aprobado = $this->tesis->getTemaAprobado($tesis[0]['pk_datotesis']);
                        $tipotutor = $queryArray['fk_tipo'];
                        $nombretesis = $this->tesis->getTesisNombre($cedula,null);

                        $periodo = $this->tesis->getPeriodoActual();

                        if(empty($periodo)){
                            $periodo = $this->tesis->getPeriodoAnterior();
                        }

                        $grupotutor = $this->tesis->getGrupoTutor();

                        $tutorexterno = $this->tesis->getTutorTipoExterno();

                        if($tipotutor == $tutorexterno){
                            $titulo_academico = $this->tesis->getTutorTituloAcademico($queryArray['pk_usuario'],null);
                            
                            if(!empty($titulo_academico)){
                                $titulo_academico = $titulo_academico[0]['titulo_academico'];
                            }else{
                                $titulo_academico = '';
                            }
                        }
                        
                        if($aprobado[0]['valor']=='Aprobado'){
                            
                            if(empty($queryArray['id'])){//agregando

                                    $usuariogrupo = $this->tesis->getUsuariogrupo($queryArray['pk_usuario'],"(select pk_atributo 
                                                                                     from vw_grupos
                                                                                     where pk_atributo in (854,855,1745,{$grupotutor}))");

                                    if(!empty($usuariogrupo)){// esta en la tabla usuarios, ya que tiene un usuariogrupo(854,855,1745)

                                        $usuariogrupo = $this->tesis->getUsuariogrupo($queryArray['pk_usuario'],$grupotutor);
                                        
                                        if(!empty($usuariogrupo)){//verifico que tenga el grupo tutor
                                            
                                            if(empty($nombretesis[0]['pk_usuariogrupo'])){
                                                $this->tesis->addTutor($tesis[0]['pk_datotesis'],$usuariogrupo,$titulo_academico,$periodo,$tipotutor);                              
                                            }else{
                                                if($nombretesis[0]['pk_usuariogrupo'] != $usuariogrupo){
                                                    $this->tesis->updateTutor($tesis[0]['pk_datotesis'],$periodo,$usuariogrupo,$titulo_academico);
                                                }
                                            }
                                                                                   
                                        }else{//no tiene grupo tutor
                                            $this->grupo->addRow($queryArray['pk_usuario'],$grupotutor);
                                            $usuariogrupo = $this->tesis->getUsuariogrupo($queryArray['pk_usuario'],$grupotutor);

                                            if(empty($nombretesis[0]['pk_usuariogrupo'])){
                                                $this->tesis->addTutor($tesis[0]['pk_datotesis'],$usuariogrupo,$titulo_academico,$periodo,$tipotutor);                              
                                            }else{
                                                if($nombretesis[0]['pk_usuariogrupo'] != $usuariogrupo){
                                                    $this->tesis->updateTutor($tesis[0]['pk_datotesis'],$periodo,$usuariogrupo,$titulo_academico);
                                                }
                                            }
                                        }

                                    }else{//no esta en la tabla usuarios, hay que agregar sus datos

                                        $dataRow['pk_usuario']      = $queryArray['pk_usuario'];
                                        $dataRow['nombre']          = $queryArray['primer_nombre'] . ' '. $queryArray['segundo_nombre'];
                                        $dataRow['apellido']        = $queryArray['primer_apellido'] . ' '. $queryArray['segundo_apellido'];
                                        $dataRow['telefono']        = str_replace('.','',str_replace(')','',str_replace('(','',$queryArray['telefono'])));
                                        $dataRow['telefono_movil']  = str_replace('.','',str_replace(')','',str_replace('(','',$queryArray['telefono_movil'])));
                                        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToDataBase($queryArray['fechanacimiento']);
                                        $dataRow['passwordhash']    = md5($queryArray['pk_usuario']);
                                        $dataRow['direccion']       = $queryArray['direccion'];
                                        $dataRow['correo']          = $queryArray['correo'];
                                        $dataRow['primer_nombre']   = $queryArray['primer_nombre'];
                                        $dataRow['segundo_nombre']  = $queryArray['segundo_nombre'];
                                        $dataRow['primer_apellido'] = $queryArray['primer_apellido'];
                                        $dataRow['segundo_apellido']= $queryArray['segundo_apellido'];
                                        $dataRow['titulo_academico']= $queryArray['titulo_academico'];

                                        $this->tesis->addUsuarioTutor($dataRow);
                                        $this->grupo->addRow($dataRow['pk_usuario'],$grupotutor);

                                        $usuariogrupo = $this->tesis->getUsuariogrupo($dataRow['pk_usuario'],$grupotutor);

                                        $tipotutor = $this->tesis->getTutorTipoExterno();

                                        if(empty($nombretesis[0]['pk_usuariogrupo'])){
                                                $this->tesis->addTutor($tesis[0]['pk_datotesis'],$usuariogrupo,$dataRow['titulo_academico'],$periodo,$tipotutor);                              
                                        }else{
                                            if($nombretesis[0]['pk_usuariogrupo'] != $usuariogrupo){
                                                $this->tesis->updateTutor($tesis[0]['pk_datotesis'],$periodo,$usuariogrupo,$dataRow['titulo_academico']);
                                            }
                                        }

                                }                        
                            }else{//editando

                                $dataRow['pk_usuario']      = $queryArray['id'];
                                $dataRow['nombre']          = $queryArray['primer_nombre'] . ' '. $queryArray['segundo_nombre'];
                                $dataRow['apellido']        = $queryArray['primer_apellido'] . ' '. $queryArray['segundo_apellido'];
                                $dataRow['telefono']        = str_replace('.','',str_replace(')','',str_replace('(','',$queryArray['telefono'])));
                                $dataRow['telefono_movil']  = str_replace('.','',str_replace(')','',str_replace('(','',$queryArray['telefono_movil'])));
                                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToDataBase($queryArray['fechanacimiento']);
                                $dataRow['direccion']       = $queryArray['direccion'];
                                $dataRow['correo']          = $queryArray['correo'];
                                $dataRow['primer_nombre']   = $queryArray['primer_nombre'];
                                $dataRow['segundo_nombre']  = $queryArray['segundo_nombre'];
                                $dataRow['primer_apellido'] = $queryArray['primer_apellido'];
                                $dataRow['segundo_apellido']= $queryArray['segundo_apellido'];
                                $dataRow['titulo_academico']= $queryArray['titulo_academico'];

                                $this->tesis->updateUsuarioTutor($dataRow);
                                $this->tesis->updateTituloTutor($tesis[0]['pk_datotesis'],$dataRow['titulo_academico']);
                            }

                        }

                    // }else{
                    //     die;
                    // }

                    
                }else{//no deberia hacer nada
                    die;
                }
                
            }            
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
        
    }
    
    function deletetutorAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $cedula = $this->authSpace->userId;
            $datos = $this->_getAllParams();

            // esto lo hago para asegurarme de que una persona  solo pueda borrar su tutor
            $verificar = $this->tesis->verificarTutor($datos['cod'],$cedula);

            if(!empty($verificar)){
                $this->tesis->deleteTutor($datos['cod']);
            }
            
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
    }

    function renunciatetistaAction(){
        if ($this->_request->isXmlHttpRequest()) { 
            $this->SwapBytes_Ajax->setHeader();
            
            $cedula = $this->authSpace->userId;

            $tesis = $this->tesis->getTesisNombre($cedula, null); 

            $this->SwapBytes_Crud_Form->setProperties($this->view->form_renuncia,null, $this->FormTitle_Renunciatesista,null);

            $json[] = "$('#renuncia').prepend('<div id= \"txtRenuncia\"></div>');";

            $json[] = $this->SwapBytes_Jquery->setHtml('txtRenuncia', '¿Está seguro que desea continuar?, al descargar la planilla de <b>Renuncia del Tema</b>, está confirmando su renuncia. Deberá llevar dicha planilla a Coordinacion de Trabajo de Grado para anexarse a su expediente, solo puede descargarse una vez');            

            /*oculto los botones usados por el Modal*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

            /*llamo a los botones del formulario tesis*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Cancelar');

            $json[] = "$('#guardar_renuncia').click(function(data){renuncia_confirm(4,".$tesis[0]['pk_datotesis'].")});";
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 200);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }

    function renunciatutorAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $tutor = $this->_getParam('cod');
            $pk_datotesis = $this->_getParam('id');

            $cedula = $this->authSpace->userId;
            $tesis = $this->tesis->getTesisNombre($cedula, null);

            $this->SwapBytes_Crud_Form->setProperties($this->view->form_renuncia,null, $this->FormTitle_Renunciatutor,null);
            
            $json[] = "$('#renuncia').prepend('<div id= \"txtRenuncia\"></div>');";

            $json[] = $this->SwapBytes_Jquery->setHtml('txtRenuncia', '¿Está seguro que desea continuar?, al descargar la planilla de <b>Renuncia del Tutor</b>, está confirmando su renuncia. Deberá llevar dicha planilla a Coordinacion de Trabajo de Grado para anexarse a su expediente, solo puede descargarse una vez');            

            /*oculto los botones usados por el Modal*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

            /*llamo a los botones del formulario tesis*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Cancelar');

            $json[] = "$('#guardar_renuncia').click(function(data){renuncia_confirm(5,".$pk_datotesis.", ".$tutor.")});";
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 200);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }

    public function renunciaresponseAction(){

        if ($this->_request->isXmlHttpRequest()) {

            $tutor = $this->_getParam('tutor');
            $pk_datotesis = $this->_getParam('id');
            $mod = $this->_getParam('mod');

            $cedula = $this->authSpace->userId;
            $tesis = $this->tesis->getTesisNombre($cedula, null);

            if($mod == 4){//para renuncia de tesista
                //con esto borro al tesista
                $usuariogrupo = $this->tesis->getUsuariogrupo($cedula, 855);
                $pk_autortesis = $this->tesis->getAutortesis($usuariogrupo,$tesis[0]['fk_periodo'],$tesis[0]['pk_datotesis']);

                $ultimotesista = $this->tesis->ultimoTesista($tesis[0]['pk_datotesis']);

                //valido si borro con todo y tesis y solo autor
                if($ultimotesista == 1){
                    
                    $this->tesis->deleteRealTutorbyTesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteRealAutorestesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteTesisBiblioteca($tesis[0]['pk_datotesis']);
                    $this->tesis->deletePasostesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteDefensastesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteEvaluadorestesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteMenciontesis($tesis[0]['pk_datotesis']);
                    $this->tesis->deleteDatostesis($tesis[0]['pk_datotesis']);

                }else{
                    $escuela_otro_tesista = $this->tesis->getTesistasByTesis($tesis[0]['pk_datotesis'],$cedula);
                    $escuela_otro_tesista = $escuela_otro_tesista[0]['fk_atributo'];
                    $this->tesis->updateTesisEscuela($tesis[0]['pk_datotesis'],$escuela_otro_tesista);


                    $this->tesis->deleteTesista($pk_autortesis);    
                }

            }elseif($mod == 5){//para renuncia de tutor

                $tutortesis = $this->tesis->getPerfilTutorTesis();
                $usuariogrupo = $this->tesis->getUsuariogrupo($tutor, $tutortesis);
                $pk_tutortesis = $this->tesis->getTutortesis($usuariogrupo,$tesis[0]['periodotutor'],$pk_datotesis);
                $this->tesis->deleteTutor($pk_tutortesis);

            }else{
                die;
            }

            $json[] = 'window.location.href = escape(MyurlAjax + "/imprimir/mod/" +'.$mod.'+"/id/"+'.$tesis[0]['pk_datotesis'].'+"/tutor/"+'.$tutor.')';

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();
        }
    }

    function renunciatemaAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            
            $datos = $this->_getAllParams();

            $dataRow['id'] = $datos['cod'];

            $this->SwapBytes_Crud_Form->setProperties($this->view->form_renuncia,$dataRow, $this->FormTitle_Renunciatesista,null);
            
            $json[] = "$('#renuncia').prepend('<div id= \"txtRenuncia\"></div>');";

            $json[] = $this->SwapBytes_Jquery->setHtml('txtRenuncia', 'A continuación Renunciará a su tema actual, lo que significa que no podrá trabajarlo nuevamente');            

            /*oculto los botones usados por el Modal*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

            /*llamo a los botones del formulario tesis*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('renuncia', 'Cancelar');

            $json[] = "$('#guardar_renuncia').click(function(data){renunciatema_confirm()});";
            $json[] = "$('#frmModal').parent().find(\"button:contains('Descargar')\").children().html('Guardar');"; 
            
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setHeight('frmModal', 200);
            $json[] = $this->SwapBytes_Jquery_Ui_Form->setWidth('frmModal' , 300);
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }

    public function renunciatemaresponseAction(){//en este caso como la renuncia es de un tema que aun no ha sido aprobado, se borra fisicamente
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $datos = $this->_getAllParams();
            $cedula = $this->authSpace->userId;
        
            $usuariogrupo = $this->tesis->getUsuariogrupo($cedula, 855);
            
            $tesisexiste = $this->tesis->getLineaTemaTesis($datos['cod'],$cedula);

            $tesis = $this->tesis->getTesisNombre($cedula, null);

            if(!empty($tesisexiste)){//estas en esta tesis

                $pk_autortesis = $this->tesis->getAutortesis($usuariogrupo,null,$datos['cod']);
                $ultimotesista = $this->tesis->ultimoTesista($datos['cod']);

                //valido si borro con todo y tesis y solo autor
                if($ultimotesista == 1){
                    
                    $this->tesis->deleteRealTutorbyTesis($datos['cod']);
                    $this->tesis->deleteRealAutorestesis($datos['cod']);
                    $this->tesis->deleteTesisBiblioteca($datos['cod']);
                    $this->tesis->deletePasostesis($datos['cod']);
                    $this->tesis->deleteDefensastesis($datos['cod']);
                    $this->tesis->deleteEvaluadorestesis($datos['cod']);
                    $this->tesis->deleteMenciontesis($datos['cod']);
                    $this->tesis->deleteDatostesis($datos['cod']);

                }else{

                    $escuela_otro_tesista = $this->tesis->getTesistasByTesis($datos['cod'],$cedula);
                    $escuela_otro_tesista = $escuela_otro_tesista[0]['fk_atributo'];
                    $this->tesis->updateTesisEscuela($datos['cod'],$escuela_otro_tesista);

                    $this->tesis->deleteRealTesista($pk_autortesis);    
                } 

            }else{//no estas en esta tesis
                die;
            }
            
            $this->SwapBytes_Crud_Form->getAddOrEditEnd();           

        }
    }    


    function planillaAction(){
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $cedula = $this->authSpace->userId;
            $periodoactual = $this->tesis->getPeriodoActual();
        
            $tesis = $this->tesis->getTesisNombre($cedula, $periodoactual);
            $dataRow['pk_datotesis'] = $tesis[0]['pk_datotesis'];

            $fasetrabajodegrado = $this->tesis->getFaseTrabajodeGrado($cedula);
            // $TG2 = $this->tesis->getTG2Inscrito($cedula); 

            $this->SwapBytes_Crud_Form->setProperties($this->view->form_planilla,$dataRow, $this->FormTitle_DescargarPlanilla,null);

            if(!empty($dataRow['pk_datotesis'])){

                $pk_tutortesis = $this->tesis->getPkTutor($dataRow['pk_datotesis']);
                $tutortesis = $this->tesis->getTutorDatos($pk_tutortesis);

                if(!empty($tutortesis) && $tutortesis[0]['valortutor'] == 'Aprobado'){
                    $fase0 = array('fase' => 'Aceptacion de Tutoria',  'mod' => 6);
                    array_unshift($fasetrabajodegrado, $fase0);

                    $fase1 = array('fase' => 'Autorizacion para Defensa',  'mod' => 7);
                    array_unshift($fasetrabajodegrado, $fase1);

                    
                    $this->SwapBytes_Crud_Form->fillSelectBox('fase',$fasetrabajodegrado, 'mod', 'fase');
                    
                    /*oculto los botones usados por el Modal*/
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Cancelar');

                    /*llamo a los botones del formulario tesis*/
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('planilla', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('planilla', 'Cancelar');

                    $json[] = "$('#guardar_planilla').click(function(data){imprimir_planilla()});";
                }else{

                    $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', 'Debes tener tu tutor cargado y aprobado para descargar las planillas');

                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('planilla', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('planilla', 'Cancelar');

                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }
   
            }

            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getAddOrEditLoad();
        }
    }    

    function imprimirAction(){
        mb_internal_encoding('UTF-8');
    
        $mod = $this->_getParam('mod');
        $pk_datotesis = $this->_getParam('id');
        $tutor = $this->_getParam('tutor');

        $cedula = $this->authSpace->userId;
        
        $periodoactual = $this->tesis->getPeriodoActual();
        
        $tesis = $this->tesis->getTesisNombre($cedula, null);

        $imagen = APPLICATION_PATH . '/../public/images/logo_UNE_color.jpg';
        
        //reportes
        if($mod == 1 || $mod == 2 || $mod == 3){//anteproyecto de TG
            $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/inscripcionTG.jasper'; 
            $params      = "'logo=string:{$imagen}|id=string:{$pk_datotesis}|mod=string:{$mod}'";
            $filename    = 'inscripcionTG';
        }elseif($mod == 4){//para tesista

            $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/renunciatesista.jasper';
            $params      = "'logo=string:{$imagen}|id=string:{$pk_datotesis}|cedula=string:{$cedula}'";
            $filename    = 'RetiroTema';
        }elseif($mod == 5){//para tutor

            // con esto borro tutor
            $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/renunciatutor.jasper';
            $params      = "'logo=string:{$imagen}|id=string:{$pk_datotesis}|cedula=string:{$tutor}'";
            $filename    = 'RetiroTutor';
        }elseif($mod == 6){//aceptacion de tutoria
            $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/aceptaciontutoria.jasper';
            $params      = "'logo=string:{$imagen}|id=string:{$tesis[0]['pk_datotesis']}|cedula=string:{$tesis[0]['fk_usuario']}'";
            $filename    = 'AceptacionTutoria';
        }elseif($mod == 7){
            $report = APPLICATION_PATH . '/modules/transactions/templates/trabajodegrado/autorizaciondefensa.jasper';
            $params      = "'logo=string:{$imagen}|id=string:{$tesis[0]['pk_datotesis']}|cedula=string:{$tesis[0]['fk_usuario']}'";
            $filename    = 'AutorizacionDefensa';
        }else{
            die;
        }
        
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
    
    public function viewAction() {
        $this->SwapBytes_Ajax->setHeader();
        $id = $this->_getParam('id');
        $cod = $this->_getParam('cod');
        $pk = $this->_getParam('pk');
        $cedula = $this->authSpace->userId;

        if($id ==1){// tesis
            $dataRow = $this->tesis->getLineaTemaTesis($cod,$cedula);
            $dataRow = $dataRow[0];
            $linea[0] = array(
                'fk_lineainvestigacion' => $dataRow['fk_lineainvestigacion'],
                'linea'=> $dataRow['linea']
            );
            
            $tema[0] = array(
                'fk_tema' => $dataRow['fk_tema'],
                'tema' => $dataRow['tema']
            );
           
            $this->SwapBytes_Crud_Form->setProperties($this->view->form_tesis, $dataRow, 'Ver Tesis');
            $this->SwapBytes_Crud_Form->fillSelectBox('fk_lineainvestigacion',$linea, 'fk_lineainvestigacion', 'linea');
            $this->SwapBytes_Crud_Form->fillSelectBox('fk_tema',$tema, 'fk_tema', 'tema');

            /*oculto los botones usados por el Modal*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');

            /*llamo a los botones del formulario tesis*/
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('tesis', 'Guardar');
            $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('tesis', 'Cancelar');
            
            $this->SwapBytes_Crud_Form->setJson($json);
            $this->SwapBytes_Crud_Form->getView();
        }elseif($id == 2){//otro tesista
            
                $dataRow['pk_usuario']  = $cod;
                $dataRow = $this->Usuarios->getRow($cod);
                $dataRow['sexo']            = $this->SwapBytes_Form2->setValueToBoolean($dataRow['sexo']);
                $dataRow['nacionalidad']    = $this->SwapBytes_Form2->setValueToBoolean($dataRow['nacionalidad']);
                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);

                 $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                 $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                 $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                 $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
                $json[] = "$('#frmModal').parent().find(\"button:contains('Imprimir')\").children().html('Guardar');"; 
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                $this->SwapBytes_Crud_Form->setJson($json); 
                $this->SwapBytes_Crud_Form->setProperties($this->view->form_usuario, $dataRow, 'Ver Otro Tesista');
                $this->SwapBytes_Crud_Form->getView();
            
        }elseif($id ==3){//tutor
            
                $dataRow['pk_usuario']  = $cod;
                
                $dataRow = $this->Usuarios->getRow($cod);
                
                //id pk de la tesis
                //cod pk_usuario
                
                $tipotutor = $this->tesis->getTutorTipo($cod);
                $tipotutores = $this->tesis->getTutoresTipos($tipotutor[0]['tipo']);
                $tutoraprobado = $this->tesis->getEstadoTutorAprobado();
                $estadotutor = $this->tesis->getTutorEstado($cod,$pk);
                $tutorinterno = $this->tesis->getTutorInterno();

                if($tipotutor[0]['tipo'] == $tutorinterno){
                    
                    if($estadotutor == $tutoraprobado){//para validar que no puedan ver los datos personales del personal de la une hasta tanto no sean aprobados

                        $dataRow['sexo']            = $this->SwapBytes_Form3->setValueToBoolean($dataRow['sexo']);
                        $dataRow['nacionalidad']    = $this->SwapBytes_Form3->setValueToBoolean($dataRow['nacionalidad']);
                        $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                        
                         $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$tipotutor[0]['tipo']);
                         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                         $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                         $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                         $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
                         $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$tipotutor[0]['tipo']);
                         $json[] = "$('#frmModal').parent().find(\"button:contains('Imprimir')\").children().html('Guardar');";
                         $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                        $this->SwapBytes_Crud_Form->setJson($json); 
                        $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor, $dataRow, 'Ver Tutor');
                        $this->SwapBytes_Crud_Form->fillSelectBox('fk_tipo', $tipotutores, 'pk_atributo', 'valor');
                        $this->view->form_tutor->removeElementDatosTesis();
                        $this->SwapBytes_Crud_Form->getView();                    
                    }else{
                        $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor, 'Ver Tutor', 'Advertencia');
                        $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', 'No puedes ver los datos del personal Docente/Administrativo de la Universidad hasta tanto este aprobado por Coordinacion de Trabajo de Grado');
                        $json[] = "$('#frmModal').parent().find(\"button:contains('Imprimir')\").children().html('Guardar');"; 
                        $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                        $this->view->form_tutor->removeElementDatosTesis();
                        $this->SwapBytes_Crud_Form->setJson($json);
                        $this->SwapBytes_Crud_Form->getAddOrEditLoad(); 

                    }
                }else{

                    $dataRow['sexo']            = $this->SwapBytes_Form3->setValueToBoolean($dataRow['sexo']);
                    $dataRow['nacionalidad']    = $this->SwapBytes_Form3->setValueToBoolean($dataRow['nacionalidad']);
                    $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);

                    $titulo_academico = $this->tesis->getTutorTituloAcademico($cod,$pk);

                    $dataRow['titulo_academico'] = $titulo_academico[0]['titulo_academico'];
                    
                     $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$tipotutor[0]['tipo']);
                     $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                     $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                     $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                     $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$dataRow['pk_usuario']}'");
                     $json[] = $this->SwapBytes_Jquery->setValSelectOption('fk_tipo',$tipotutor[0]['tipo']);
                     $json[] = "$('#frmModal').parent().find(\"button:contains('Imprimir')\").children().html('Guardar');";
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                    $this->SwapBytes_Crud_Form->setJson($json); 
                    $this->SwapBytes_Crud_Form->setProperties($this->view->form_tutor, $dataRow, 'Ver Tutor');
                    $this->SwapBytes_Crud_Form->fillSelectBox('fk_tipo', $tipotutores, 'pk_atributo', 'valor');
                    $this->view->form_tutor->removeElementDatosTesis();
                    $this->SwapBytes_Crud_Form->getView();

                }

        }

    }
    
    public function temasAction(){
            $this->SwapBytes_Ajax->setHeader();
            $data = $this->tesis->getTesisTema($this->_getParam('fk_lineainvestigacion'));
            
            foreach ($data as $key => $value) {
                $temas .= "<option value=".$value['pk_atributo'].">".$value['tema']."</option>";
            }
            
            $json[] .= "$('#fk_tema').empty();";
            $json[] .= "$('#fk_tema').append('".$temas."');";
            $this->getResponse()->setBody(Zend_Json::encode($json));

    }
    
    public function existsAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $pk_usuario  = $queryArray['pk_usuario'];
            $cedula = $this->authSpace->userId;
        
            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                
                $usuario    = $this->tesis->getUsuarioTesistaCount($pk_usuario);
                $dataRow    = $this->tesis->getUsuarioTesistaDatos($pk_usuario,855);
                $hide = false;
                $dataRow = $dataRow[0];

               
                // valido si cumple con los requisitos necesarios para optar a ser compañero de tesis
                if(empty($dataRow)){
                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No cumple los requisitos</span>');";
                    $hide = true;
                }else{

                    $pensum = $this->tesis->getUltimoPensum($dataRow['pk_usuario']);
                    $pensum = $this->tesis->getPensumCodigopropietario($pensum[0]['fk_pensum']);


                    $tesis_inscrito = $this->tesis->getTesisInscrito($dataRow['pk_usuario'],$pensum);
                    $datosestudiante = $this->tesis->getDatosEstudiante($dataRow['pk_usuario']);
                    $UCA = $this->tesis->getUCA($datosestudiante[0]['usuario'],$datosestudiante[0]['escuela'],$datosestudiante[0]['pensum'],$datosestudiante[0]['periodo']);
                    $EquiUCA = $this->RecordAcademico->getUCACEscuelaEquivPensum($datosestudiante[0]['usuario'], $datosestudiante[0]['escuela'],$datosestudiante[0]['pensum']);
                    $tesistacount = $this->tesis->getTesistasCount($datosestudiante[0]['usuario']);

                    //buscamos el pensum de cada estudiante
                    $pensum_estudiante =$this->tesis->getUltimoPensum($cedula);
                    $pensum_estudiante = $pensum_estudiante[0]['fk_pensum'];

                    $pensum_otro_estudiante =$this->tesis->getUltimoPensum($pk_usuario);
                    $nombre_pensum_otro_estudiante =  $pensum_otro_estudiante[0]['nombre'];
                    $escuela_otro_estudiante =  $pensum_otro_estudiante[0]['escuela'];
                    $pensum_otro_estudiante = $pensum_otro_estudiante[0]['fk_pensum'];
                    

                    if($pensum_estudiante  == $pensum_otro_estudiante){//si tiene el mismo pensum, verifico que las materias que tienen inscrita sean la misma
                        $materia_actual_estudiante = $this->tesis->getMateriaActual($cedula);
                        $materia_actual_otrotesista = $this->tesis->getMateriaActual($pk_usuario);    

                        $materia_actual_estudiante = $materia_actual_estudiante[0]['pk_atributo'];
                        $materia_actual_otrotesista = $materia_actual_otrotesista[0]['pk_atributo'];

                    }else{//si tienen diferentes pensums, verifica que esta al mismo nivel en las materias de trabajo de grado

                        $fase_estudiante = $this->tesis->getFaseTrabajodeGrado($cedula);
                        $fase_otro_estudiante = $this->tesis->getFaseTrabajodeGrado($pk_usuario);

                        $materia_actual_estudiante = $fase_estudiante[0]['fase'];
                        $materia_actual_otrotesista = $fase_otro_estudiante[0]['fase'];
                    }

                    $totalUCA = $UCA + $EquiUCA;
                    
                    switch ($nombre_pensum_otro_estudiante) {
                        case '2012':
                            switch ($escuela_otro_estudiante) {
                                case 'Ingeniería Civil':
                                    if($totalUCA == 197)
                                        $graduado = true;
                                    break;
                                    case 'Administración':
                                    if($totalUCA == 180)
                                        $graduado = true;
                                    break;
                                    case '"Computación"':
                                    if($totalUCA == 185)
                                        $graduado = true;
                                    break;
                                    case "Administración de Empresas de Diseño":
                                    if($totalUCA == 189)
                                        $graduado = true;
                                    break;
                                    case "Ingeniería Electrónica":
                                    if($totalUCA == 199)
                                        $graduado = true;
                                    break;
                                     case "Administración de Empresas Turísticas":
                                    if($totalUCA == 193)
                                        $graduado = true;
                                    break;
                            }
                            break;
                    }

                   if($datosestudiante[0]['pensum'] == 7){// es del pensum 1997

                        switch ((int)$totalUCA) {                      
                            case 175:$graduado = true; break;//pensum 1997
                            default: $graduado = false;break;//indica que no esta graduado aun
                        }

                    }elseif($datosestudiante[0]['pensum'] == 6){//es del pensum 1992

                        switch ((int)$totalUCA) {
                            case 176:$graduado = true; break;//pensum 1992 administracion - diseño - civil - electronica - turismo
                            case 178:$graduado = true; break;//pensum 1992 computacion
                            default: $graduado = false;break;//indica que no esta graduado aun
                        }

                    }else{//esto quiere decir que no se ha podido calcular bien sus UCA
                        $graduado = false;
                    }                    
                    
                    if($cedula == $pk_usuario){//eres tu mismo
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Usted es el Usuario Autenticado</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->no)."');";
                        $hide = true;
                        
                    }elseif($graduado == true){// ya graduado
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Esta graduado</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->no)."');"; 
                        $hide = true;
                                               
                    }elseif($tesis_inscrito == 0){//no tiene alguna materia de tesis inscrita
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No tiene inscrita ninguna materia de Trabajo de Grado</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->no)."');";
                        $hide = true;
                        
                    }elseif($tesistacount >= 1){//esta en otra tesis
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Tiene otra tesis asociada</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->no)."');"; 
                        $hide = true;
                                               
                    }elseif($materia_actual_estudiante != $materia_actual_otrotesista){//esta viendo la misma materia que su compañero
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No está cursando actualmente tu materia</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->no)."');"; 
                        $hide = true;
                                               
                    }else{//si puede
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Cumple los requisitos</span>');";
                        $cumple .= "$('#permiso_tesista').val('".md5($this->si)."');";                 
                                              
                    }
 
                }

                $dataRow['nacionalidad']    = $this->SwapBytes_Form2->setValueToBoolean($dataRow['nacionalidad']);
                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                // Verificamos si el Usuario existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                
                if($usuario == 1) {
                    $Status = true;
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuario == 0) {
                    $Status = false;
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuario == 1) {
                    $Status = true;
                    $dataRow['id'] = $pk_usuario;

                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }

                 // Creamos el frmModal con los datos necesarios.
                //  $dataRow['pk_usuario'] = $pk_usuario;

                $this->SwapBytes_Form2->set($this->view->form_usuario);

                if(isset($dataRow)) {
                    $dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 't' : 'f';
                    $dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';
                    
                    $this->view->form_usuario->populate($dataRow);
                }

                    // Definimos el acceso a los controles del frmModal.
                    $this->SwapBytes_Form2->readOnlyElement('nacionalidad'    , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('sexo'            , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('primer_nombre'   , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('segundo_nombre'  , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('primer_apellido' , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('segundo_apellido', $Status);
                    $this->SwapBytes_Form2->readOnlyElement('direccion'       , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('correo'          , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('fechanacimiento' , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('telefono'        , $Status);
                    $this->SwapBytes_Form2->readOnlyElement('telefono_movil'  , $Status);   

                $this->view->form_usuario = $this->SwapBytes_Form2->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form_usuario);

                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");

                $json[] = $cumple;


                if($hide == true){
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                }else{
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                }

                $json[] = "$('#fechanacimiento-label').hide();";
                $json[] = "$('#fechanacimiento-element').hide();";
                $json[] = "$('#sexo-label').hide();";
                $json[] = "$('#sexo-element').hide();";
                $json[] = "$('#nacionalidad-label').hide();";
                $json[] = "$('#nacionalidad-element').hide();";
                $json[] = "$('#direccion-label').hide();";
                $json[] = "$('#direccion-element').hide();";
                $json[] = "$('#telefono-label').hide();";
                $json[] = "$('#telefono-element').hide();";
                $json[] = "$('#telefono_movil-label').hide();";
                $json[] = "$('#telefono_movil-element').hide();";
                $json[] = "$('#correo-label').hide();";
                $json[] = "$('#correo-element').hide();";
                

                $this->getResponse()->setBody(Zend_Json::encode($json));
            } else {

                $html  .= $this->SwapBytes_Ajax->render($this->view->form_usuario);
                
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                
                $json[] = "$('#fechanacimiento-label').hide();";
                $json[] = "$('#fechanacimiento-element').hide();";
                $json[] = "$('#sexo-label').hide();";
                $json[] = "$('#sexo-element').hide();";
                $json[] = "$('#nacionalidad-label').hide();";
                $json[] = "$('#nacionalidad-element').hide();";
                $json[] = "$('#direccion-label').hide();";
                $json[] = "$('#direccion-element').hide();";
                $json[] = "$('#telefono-label').hide();";
                $json[] = "$('#telefono-element').hide();";
                $json[] = "$('#telefono_movil-label').hide();";
                $json[] = "$('#telefono_movil-element').hide();";
                $json[] = "$('#correo-label').hide();";
                $json[] = "$('#correo-element').hide();";

                $this->getResponse()->setBody(Zend_Json::encode($json));
            
            } 
        }
    } 
    
    
    // IMPORTANTE: si se cambia algo de la logica para esgoger al tutor aqui debe hacerse en el moduo aprobartutor
    public function existstutorAction() {
    
        if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();

            $json        = array();
            $Status      = true;
            $html        = '';
            $queryString = $this->_getParam('data');
            
            $queryArray  = $this->SwapBytes_Uri->queryToArray($queryString);
            $pk_usuario  = $queryArray['pk_usuario'];
            $periodo = $this->periodos->getUltimo();
            $hide = true;
            $hide_titulo = true;
            $nopermitir = false;
            $cedula = $this->authSpace->userId;
            
            if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
                
                $usuario    = $this->tesis->getUsuarioTutorCount($pk_usuario);
                $dataRow    = $this->tesis->getUsuarioTutorDatos($pk_usuario);

                $titulo_academico = $this->tesis->getTutorTituloAcademico($pk_usuario,null);


                $dataRow    = $dataRow[0];

                // $usuario    = $this->Usuarios->getCount($pk_usuario);
                // $dataRow    = $this->Usuarios->getRow($pk_usuario);

                $TutorCount = $this->tesis->getTutorNumeroTesis($pk_usuario,$periodo);
                $grupoestudiante = $this->tesis->getUsuariogrupo($pk_usuario,855);
                $autoridad = $this->tesis->getGrupoAutoridad();

                $grupoautoridad = $this->tesis->getUsuariogrupo($pk_usuario,$autoridad);
                
                if(!empty($grupoautoridad)){//si es autoridad
                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Es autoridad</span>');";//no cumple
                    $cumple .= "$('#permiso_tutor').val('".md5($this->no)."');";
                    $nopermitir = true; 
                }else{//no es autoridad

                    if($TutorCount >= $this->maxtutorperiodo){//tiene mas de 3 tesis por periodo
                        $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>Tiene 3 o mas tesis asociadas</span>');";//no cumple
                        $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";
                    }else{

                        if(!empty($grupoestudiante)){//eres estudiante
                            $datosestudiante = $this->tesis->getDatosEstudiante($pk_usuario);

                            

                            $UCA = $this->tesis->getUCA($datosestudiante[0]['usuario'],$datosestudiante[0]['escuela'],$datosestudiante[0]['pensum'],$datosestudiante[0]['periodo']);
                            $EquiUCA = $this->RecordAcademico->getUCACEscuelaEquivPensum($datosestudiante[0]['usuario'], $datosestudiante[0]['escuela'],$datosestudiante[0]['pensum']);

                            $totalUCA = $UCA + $EquiUCA;

                            if($datosestudiante[0]['pensum'] == 8){//es del pensum 2012
                                switch ((int)$totalUCA) {
                                    case 180:$graduado = true; break;//administracion 
                                    case 185:$graduado = true; break;//computacion 
                                    case 189:$graduado = true; break;//diseño 
                                    case 197:$graduado = true; break;//civil 
                                    case 199:$graduado = true; break;//electronica 
                                    case 193:$graduado = true; break;//turismo 
                                    default: $graduado = false;break;//indica que no esta graduado aun
                                }

                            }elseif($datosestudiante[0]['pensum'] == 7){// es del pensum 1997

                                switch ((int)$totalUCA) {                      
                                    case 175:$graduado = true; break;//pensum 1997
                                    default: $graduado = false;break;//indica que no esta graduado aun
                                }

                            }elseif($datosestudiante[0]['pensum'] == 6){//es del pensum 1992

                                switch ((int)$totalUCA) {
                                    case 176:$graduado = true; break;//pensum 1992 administracion - diseño - civil - electronica - turismo
                                    case 175:$graduado = true; break;//pensum 1992 computacion
                                    default: $graduado = false;break;//indica que no esta graduado aun
                                }

                            }else{//esto quiere decir que no se ha podido calcular bien sus UCA
                                $graduado = false;
                            }

                            if($graduado == true){//si lo esta, verifico si tiene mas de 2 años graduado

                                $tiempo_graduado = $this->tesis->getTiempoGraduado($datosestudiante[0]['usuario']);


                                if($tiempo_graduado >= 2){//tiene al menos 2 años de graduado

                                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Tiene +2 años de graduado</span>');";//cumple
                                    $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";

                                }else{//no tiene el tiempo requerido
                                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No tiene 2 años de graduado</span>');";//no cumple
                                    $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";
                                }


                            }else{//si no esta graduado, no cumple
                                $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: red;\'>No esta graduado</span>');";//no cumple
                                $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";
                            }

                        }else{//no eres estudiante

                            if(!empty($dataRow)){//tienes perfil docente, administrativo o tutor

                                $grupoadministrativo = $this->tesis->getUsuariogrupo($pk_usuario,1745); 
                                $grupodocente = $this->tesis->getUsuariogrupo($pk_usuario,854);

                                if(!empty($grupodocente)){//verificamos si es administrativo

                                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Es docente</span>');";//mientras se hace la creacion de las autoridades
                                    $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";

                                    
                                }elseif(!empty($grupoadministrativo)){

                                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Es administrativo</span>');";//cumple
                                    $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";

                                }else{
                                    $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Ha sido tutor</span>');";//cumple
                                    $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";

                                    $dataRow['titulo_academico'] = $titulo_academico[0]['titulo_academico'];
                                    $hide_titulo = false;

                                }

                            }else{//eres un tutor externo

                                $cumple = "$('#pk_usuario-element').append('<span style=\'margin-left: 10px; color: green;\'>Tutor externo</span>');";//cumple
                                $cumple .= "$('#permiso_tutor').val('".md5($this->si)."');";
                                $hide = false;
                                $hide_titulo = false;

                                $dataRow['titulo_academico'] = $titulo_academico[0]['titulo_academico'];
                                
                            }
                    }
                }
            }
                
                //$dataRow['nacionalidad']    = $this->SwapBytes_Form3->setValueToBoolean($dataRow['nacionalidad']);
                
                $dataRow['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($dataRow['fechanacimiento']);
                
                //seccion donde se carga el tipo de tutor
                $tipotutor = $this->tesis->getTutorTipo($pk_usuario);

                if(empty($tipotutor)){
                    $tutorexterno = $this->tesis->getTutorTipoExterno();
                    $tipotutor[0]['tipo'] = $tutorexterno;
                }

                $tipostutores = $this->tesis->getTutoresTipos($tipotutor[0]['tipo']);
                $this->SwapBytes_Form3->fillSelectBox('fk_tipo', $tipostutores, 'pk_atributo', 'valor');              
                
                // Verificamos si el Usuario existe, al ser asi evitamos que
                // se pueda agregar, de lo contrario lo permitimos.
                
                if($usuario == 1) {
                    $Status = true;
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                } else if($usuario == 0) {

                    $dataRow['pk_usuario'] = $pk_usuario;
                    $Status = false;
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');

                } else if($usuario == 1) {
                    $Status = true;
                    $dataRow['id'] = $pk_usuario;

                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                     $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Cancelar');
                }

                 // Creamos el frmModal con los datos necesarios.

                $this->SwapBytes_Form3->set($this->view->form_tutor);


                if(isset($dataRow)) {
                    $dataRow['nacionalidad'] = (empty($dataRow['nacionalidad']))? 't' : 'f';
                    $dataRow['sexo']         = (empty($dataRow['sexo']))?         'f' : 't';
                    $this->view->form_tutor->populate($dataRow);
                }

                // Definimos el acceso a los controles del frmModal.
                $this->SwapBytes_Form3->readOnlyElement('primer_nombre'   , $Status);
                $this->SwapBytes_Form3->readOnlyElement('segundo_nombre'  , $Status);
                $this->SwapBytes_Form3->readOnlyElement('primer_apellido' , $Status);
                $this->SwapBytes_Form3->readOnlyElement('segundo_apellido', $Status);
                $this->SwapBytes_Form3->readOnlyElement('direccion'       , $Status);
                $this->SwapBytes_Form3->readOnlyElement('correo'          , $Status);
                $this->SwapBytes_Form3->readOnlyElement('fechanacimiento' , $Status);
                $this->SwapBytes_Form3->readOnlyElement('telefono'        , $Status);
                $this->SwapBytes_Form3->readOnlyElement('telefono_movil'  , $Status);

                $this->view->form_tutor = $this->SwapBytes_Form3->get();

                // Preparamos el frmModal para ser enviado por AJAX.
                $html  .= $this->SwapBytes_Ajax->render($this->view->form_tutor);
                
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono_movil');
                $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$pk_usuario}'");

                $json[] = $cumple;

                if($hide == true){
                    $json[] = "$('#fechanacimiento-label').hide();";
                    $json[] = "$('#fechanacimiento-element').hide();";
                    $json[] = "$('#sexo-label').hide();";
                    $json[] = "$('#sexo-element').hide();";
                    $json[] = "$('#nacionalidad-label').hide();";
                    $json[] = "$('#nacionalidad-element').hide();";
                    $json[] = "$('#direccion-label').hide();";
                    $json[] = "$('#direccion-element').hide();";
                    $json[] = "$('#telefono-label').hide();";
                    $json[] = "$('#telefono-element').hide();";
                    $json[] = "$('#telefono_movil-label').hide();";
                    $json[] = "$('#telefono_movil-element').hide();";
                    $json[] = "$('#correo-label').hide();";
                    $json[] = "$('#correo-element').hide();";
                }else{
                    $json[] = "$('#fechanacimiento-label').show();";
                    $json[] = "$('#fechanacimiento-element').show();";
                    $json[] = "$('#sexo-label').show();";
                    $json[] = "$('#sexo-element').show();";
                    $json[] = "$('#nacionalidad-label').show();";
                    $json[] = "$('#nacionalidad-element').show();";
                    $json[] = "$('#direccion-label').show();";
                    $json[] = "$('#direccion-element').show();";
                    $json[] = "$('#telefono-label').show();";
                    $json[] = "$('#telefono-element').show();";
                    $json[] = "$('#telefono_movil-label').show();";
                    $json[] = "$('#telefono_movil-element').show();";
                    $json[] = "$('#correo-label').show();";
                    $json[] = "$('#correo-element').show();";
                }

                if($hide_titulo == false){
                    $json[] = "$('#titulo_academico-label').show();";
                    $json[] = "$('#titulo_academico-element').show();";
                }else{
                    $json[] = "$('#titulo_academico-label').hide();";
                    $json[] = "$('#titulo_academico-element').hide();";
                }

                if($pk_usuario == $cedula){
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                }

                if($nopermitir == true){
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Guardar');
                }else{
                    $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonShow('frmModal', 'Guardar');
                }

                $this->getResponse()->setBody(Zend_Json::encode($json));
            } else {

                $html  .= $this->SwapBytes_Ajax->render($this->view->form_tutor);
                
                $json[] = $this->SwapBytes_Jquery->setHtml('frmModal', $html);
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Aceptar');
                $json[] = $this->SwapBytes_Jquery_Ui_Form->buttonHide('frmModal', 'Eliminar');
                $json[] = $this->SwapBytes_Jquery_Mask->date('fechanacimiento');
                $json[] = $this->SwapBytes_Jquery_Mask->phone('telefono');
                
                $this->getResponse()->setBody(Zend_Json::encode($json));
            
            } 
        }
    } 
    
    public function photoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id    = $this->_getParam('id', 0);
        $image = $this->Usuarios->getPhoto($id);

        $this->getResponse()
             ->setHeader('Content-type', 'image/jpeg')
             ->setBody($image);
    }
         
    function datos(){
            $cedula = $this->authSpace->userId;
            
    //        tesis
            $nombretesis = $this->tesis->getTesisNombre($cedula,null);
            
            if(empty($nombretesis)){//no tiene tesis

               $tesis .=  '<span style="color: gray;font-style:italic;">Ninguno</span>';
               $tesis_opciones .= '<a onclick="agregar_tesis()">Agregar</a>';
               $otro_tesista .= '';         
            }else{//tiene alguna tesis
                $tesis .= $nombretesis[0]['titulo'];
                $tesis_opciones .= '<a onclick="ver_tesis('.$nombretesis[0]['pk_datotesis'].')">Ver</a>&nbsp;&nbsp;';           

                   if($nombretesis[0]['valor'] != 'Aprobado'){//esto es para que el estudiante pueda renunciar a su tema antes de que se lo aprueben (ponerse en otra tesis)
                    $tesis_opciones .= '<a onclick="editar_tesis('.$nombretesis[0]['pk_datotesis'].')">Editar</a>&nbsp;&nbsp;';
                    $tesis_opciones .= '<a onclick="renuncia_tema('.$nombretesis[0]['pk_datotesis'].')">Renuncia Tema</a>&nbsp;&nbsp;';     
                   }

                    // otro tesista
                    $nombretesista = $this->tesis->getTesistaNombre($nombretesis[0]['pk_datotesis'],$cedula);
                    
                    if(empty($nombretesista)){
                       $otro_tesista .=  '<span style="color: gray;font-style:italic;">Ninguno</span>';
                       $otro_tesista_opciones .= '<a onclick="agregar_otrotesista()">Agregar</a>&nbsp;&nbsp;';
                    }else{
                        $otro_tesista .= $nombretesista[0]['nombre'];
                        $otro_tesista_opciones .= '<a onclick="ver_otrotesista('.$nombretesista[0]['pk_usuario'].')">Ver</a>&nbsp;&nbsp;';
                    }

        //        tutor y cambios (solo si el estudiante tiene su tema aprobado)
                    $pk_tutortesis = $this->tesis->getPkTutor($nombretesis[0]['pk_datotesis']);
                    $tutortesis = $this->tesis->getTutorDatos($pk_tutortesis);

                    if($nombretesis[0]['valor'] === 'Aprobado'){//esto va asi porque el tutor esta muy relacionado con el tema aprobado

                        // verifico si el tutor existe
                        if(empty($tutortesis[0]['nombre'])){

                            $tutor .=  '<span style="color: gray;font-style:italic;">Ninguno</span>';
                            $tutor_opciones .= '<a onclick="agregar_tutor()">Agregar</a>&nbsp;&nbsp;';

                        }else{

                            $tutor .= $tutortesis[0]['nombre'];
                            $tutor_opciones .= '<a onclick="ver_tutor('.$tutortesis[0]['pk_usuario'].', '.$tutortesis[0]['pk_datotesis'].')">Ver</a>&nbsp;&nbsp;';

                            
                            if($tutortesis[0]['valortutor'] != 'Aprobado'){
                                $tutor_opciones .= '<a onclick="editar_tutor('.$tutortesis[0]['pk_usuario'].', '.$tutortesis[0]['pk_datotesis'].')">Editar</a>&nbsp;&nbsp;';
                            }

                            if($tutortesis[0]['valortutor'] == 'Aprobado'){//si el titulo esta aprobado, el cambio de tutor se hace por el proceso normal

                                $tutor_opciones .= '<a onclick="cambiar_tutor('.$tutortesis[0]['pk_usuario'].','.$nombretesis[0]['pk_datotesis'].')">Renuncia Tutor</a>';                        

                            }else{//si no, el estudiante puede cambiarlo

                                // $tutor_opciones .= '<a onclick="nuevotutor('.$tutortesis[0]['pk_tutortesis'].')">Nuevo Tutor</a>';                        
                            }
 
                        }

                    }else{
                        $tutor .= '';
                    }

        //          estado tesis
                    $estadotesis .= $nombretesis[0]['valor'];

                    switch ($estadotesis) {
                        case 'Aprobado':
                            $estadotesis = '<span style="color: green;">'.$estadotesis.'</span>';
                            break;
                        case 'Por Aprobar':
                            $estadotesis = '<span style="color: orange;">'.$estadotesis.'</span>';
                            break;
                        case 'No Aprobado':
                            $estadotesis = '<span style="color: red;">'.$estadotesis.'</span>';
                            break;    
                        
                        default:
                            $estadotesis = '<span style="color: gray;font-style:italic;">Ninguno</span>';
                            break;
                    }
        //          estado tutor
                    $estadotutor .= $tutortesis[0]['valortutor'];
                    switch ($estadotutor) {
                        case 'Aprobado':
                            $estadotutor = '<span style="color: green;">'.$estadotutor.'</span>';
                            break;
                        case 'Por Aprobar':
                            $estadotutor = '<span style="color: orange;">'.$estadotutor.'</span>';
                            break;
                        case 'No Aprobado':
                            $estadotutor = '<span style="color: red;">'.$estadotutor.'</span>';
                            break;    
                        
                        default:
                            $estadotutor = '<span style="color: gray;font-style:italic;">Ninguno</span>';
                            break;
                    }            

            //defensa de tesis
                    $defensa = $this->tesis->getDatosDefensa($nombretesis[0]['pk_datotesis'],$this->tesis->getPeriodoActual());            

                    if(!empty($defensa)){
                        $datosdefensa.= '<span><b>Fecha: </b>'.$defensa[0]['fecha'].'</span><br>';
                        $datosdefensa.= '<span><b>Hora: </b>'.$defensa[0]['horainicio'].'</span><br>';
                        $datosdefensa.= '<span><b>Edificio: </b>'.$defensa[0]['edif'].'</span><br>';
                        $datosdefensa.= '<span><b>Aula: </b>'.$defensa[0]['aula'].'</span><br>';
                        $datosdefensa.= '<span><b>Evaluadores: </b>' .$defensa[0]['evaluador']. '</span><br>';
                        
                    }else{
                        $datosdefensa = '';
                    }

            //          cambios
                    $cambiostesis = $this->tesis->getTesisCambios($nombretesis[0]['pk_datotesis']);
                    $cambiostutor = $this->tesis->getTutorCambios($nombretesis[0]['pk_datotesis']);
  
                    $cambios .= '<div style="text-align: justify;">';

                    // valido que tenga una observacion de la tesis
                    if(empty($cambiostesis[0]['observacion_tesis'])){
                        $cambios .= '';    
                    }else{
                        $cambios .= '<span><b>Tipo</b>: '.$cambiostesis[0]['descripcion_tesis'].'</span><br>';
                        $cambios .= '<span><b>Observacion: </b> '.$cambiostesis[0]['observacion_tesis'].'</span><br><br>';    
                    }

                    // valido que tenga una observacion del tutor
                    if(empty($cambiostutor[0]['observacion_tutor'])){
                        $cambios .= '';    
                    }else{
                        $cambios .= '<span><b>Tipo</b>: '.$cambiostutor[0]['descripcion_tutor'].'</span><br>';
                        $cambios .= '<span><b>Observacion: </b> '.$cambiostutor[0]['observacion_tutor'].'</span><br><br>';    
                    }            
                 
                    $cambios .= '</div>';                    
            }

            // modelo
            $html .= '<br>';
            $html .= '<br>';
            $html .= '<table class="tableData" style="margin:0 auto;width:600px;">';
            $html .= '<tbody>';
            $html .= '<tr>'; 
            $html .= '<th>DATO</th>'; 
            $html .= '<th>VALOR</th>';
            $html .= '<th>ESTADO</th>'; 
            $html .= '<th>OPCIONES</th>'; 
            $html .= '</tr>';
            $html .= '<tr height="80px">';
            $html .= '<td style="color:#666666;text-align:center;text-transform: uppercase;">Titulo</td>';        
            $html .= '<td>'.addslashes($tesis).'</td>';
            $html .= '<td>'.$estadotesis.'</td>';
            $html .= '<td><center>'.$tesis_opciones.'</center></td>';
            $html .= '</tr>';
            
            if(!empty($nombretesis)){
                $html .= '<tr height="80px">';
                $html .= '<td style="color:#666666;text-align:center;text-transform: uppercase;">Otro Tesista</td>';
                $html .= '<td>'.$otro_tesista.'</td>';
                $html .= '<td></td>';
                $html .= '<td><center>'.$otro_tesista_opciones.'</center></td>';
                $html .= '</tr>';                 
            }
            
            if($nombretesis[0]['valor'] === 'Aprobado'){
                $html .= '<tr height="80px">';
                $html .= '<td style="color:#666666;text-align:center;text-transform: uppercase;">Tutor</td>';
                $html .= '<td>'.$tutor.'</td>';
                $html .= '<td>'.$estadotutor.'</td>';
                $html .= '<td><center>'.$tutor_opciones.'</center></td>';
                $html .= '</tr>';                
            }

            if(!empty($defensa)){
                $html .= '<tr height="80px">';
                $html .= '<td style="color:#666666;text-align:center;text-transform: uppercase;">Defensa TG</td>';
                $html .= '<td>'.$datosdefensa.'</td>';
                $html .= '<td></td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }             
           
            $html .= '<tr height="80px">';
            $html .= '<td style="color:#666666;text-align:center;text-transform: uppercase;">Cambios</td>';
            $html .= '<td>'.$cambios.'</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>'; 
            $html .= '</tbody>';       
            $html .= '</table>'; 
            
            return $html;      

    }


}    
