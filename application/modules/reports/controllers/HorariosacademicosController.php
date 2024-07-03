<?php

class Reports_HorariosacademicosController extends Zend_Controller_Action {
    
    private $title = 'Reportes \ Horarios Académicos';

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbView_Semestres');
        Zend_Loader::loadClass('Models_DbView_Turnos');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Zend_Soap_Client');
        Zend_Loader::loadClass('Models_DbTable_EstructurasEscuelas');
        Zend_Loader::loadClass('SwapBytes_Crud_Horario');


        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->filtros                      = new Une_Filtros();
        
        $this->SwapBytes_Ajax               = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action        = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action        = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List          = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form          = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search        = new SwapBytes_Crud_Search();
        $this->SwapBytes_Crud_Horario       = new SwapBytes_Crud_Horario();
        $this->SwapBytes_Date               = new SwapBytes_Date();
        $this->SwapBytes_Form               = new SwapBytes_Form();
        $this->SwapBytes_Html_Message       = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery             = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form     = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Uri                = new SwapBytes_Uri();
        $this->SwapBytes_Soap               = new SwapBytes_Soap();
        

        $this->escuelas         = new Models_DbTable_EstructurasEscuelas();
        $this->asignaciones     = new Models_DbTable_Asignaciones();
        $this->horarios         = new Models_DbTable_Horarios();
        $this->grupo            = new Models_DbTable_UsuariosGrupos();
        $this->periodo          = new Models_DbTable_Periodos();
        $this->vw_semestres     = new Models_DbView_Semestres();

        $this->Data['periodo']  = $this->Request->getParam('periodo');
        $this->Data['sede']     = $this->Request->getParam('sede');
        $this->Data['escuela']  = $this->Request->getParam('escuela');
        $this->Data['semestre'] = $this->Request->getParam('semestre');
        $this->Data['materia']  = $this->Request->getParam('materia');
        $this->Data['seccion']  = $this->Request->getParam('seccion');
        $this->Data['turno']    = $this->Request->getParam('turno');

        $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, true, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(false);
        $customFilters = array(
            array(
              'id' => 'pensum',
              'name' => 'selPensum',
              'label' => 'Pensum',
              'recursive' => false
              
            )    
          );

        $this->filtros->addCustom($customFilters);
    }
    public function preDispatch() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'login', 'default');
        }

        if (!$this->grupo->haveAccessToModule()) {
            $this->_helper->redirector('accesserror', 'profile', 'default');
        }
    }

    public function indexAction() {
        $this->view->title = $this->_title;
        $this->view->filters = $this->filtros;
        $this->view->module     = $this->Request->getModuleName();
        $this->view->controller = $this->Request->getControllerName();
        $this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
        $this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
        $this->view->SwapBytes_Crud_Form = $this->SwapBytes_Crud_Form;
        $this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
    }
	public function periodoAction() {
        $this->filtros->getAction();
    }

    public function sedeAction() {
        $this->asignaciones->setData($this->Data, array('periodo'));
        $dataRows = $this->asignaciones->getSelectSedes();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function pensumAction() {
   
        if ($this->_request->isXmlHttpRequest()) {
          $this->SwapBytes_Ajax->setHeader();
          $pensum = $this->asignaciones->getSelectPensums2();
          //var_dump($pensum);die;
          $this->SwapBytes_Ajax_Action->fillSelect($pensum);
          
        }
      }

    public function escuelaAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede'));
        $dataRows = $this->asignaciones->getSelectEscuelas();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function turnoAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela', 'pensum'));
        $dataRows = $this->asignaciones->getSelectTurnos();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function semestreAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela', 'turno', 'pensum'));
        $dataRows = $this->asignaciones->getSelectSemestres();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function seccionAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela', 'semestre', 'turno'));
        $dataRows = $this->asignaciones->getSelectSecciones();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function subseccionAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela', 'semestre', 'turno'));
        $dataRows = $this->asignaciones->getSelectSecciones();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    private function verUbicacion($variable, $num) {
        sscanf($variable, 'sel%[a-zA-Z]%[0-9\-]', $nombre, $numeros);
        $ubicacion = explode("-", $numeros);
        if (isset($ubicacion[$num])) {
            return $ubicacion[$num];
        } else {
            return -1;
        }
    }

   	public function generarAction() {
        $Datos = $this->Request->getParams();
        //var_dump($Datos);die;
        $periodo = $Datos["selPeriodo"];
        $pensums = $Datos["selPensum"];
        $sede = $Datos["selsede1"];
        //var_dump($Datos);die;
        $query = Array();
        while ($Dato = current($Datos)) {
        $nombre = key($Datos);
        //echo '<b>'.$nombre.'</b><br/>';
        //var_dump($Datos);die;
        if ($this->verUbicacion($nombre, 0) == null) {
            //var_dump($this->verUbicacion($nombre, 0));die;
        	//echo 'Periodo ='.$Dato.'<br/>';
        	

        } else if ($this->verUbicacion($nombre, 0) > -1) {
        	if ($this->verUbicacion($nombre, 1) == -1) {
        		$sede = $Dato;
        		$pagina = $this->verUbicacion($nombre, 0);
        		//echo 'Pagina ='.$pagina.'<br/>';
        		//echo 'Periodo = '.$periodo.' Sede ='.$sede.'<br/>';
        		$Datos1 = $Datos;
        		while ($Dato1 = current($Datos1)) {
        			$nombre1 = key($Datos1);
        			if ($this->verUbicacion($nombre1, 0) == $pagina && $this->verUbicacion($nombre1, 1) > -1) {
        				if ($this->verUbicacion($nombre1, 2) == -1) {
        					$escuela = $Dato1;
        					$grupo = $this->verUbicacion($nombre1, 1);
        					//echo 'Pagina ='.$pagina.' Grupo = '.$grupo.'<br/>';
        					//echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.'<br/>';
        					$Datos2 = $Datos;
        					while ($Dato2 = current($Datos2)) {
        						$nombre2 = key($Datos2);
        						if ($this->verUbicacion($nombre2, 0) == $pagina && $this->verUbicacion($nombre2, 1) == $grupo && $this->verUbicacion($nombre2, 2) > -1) {
        							if ($this->verUbicacion($nombre2, 3) == -1) {
        								$turno = $Dato2;
        								$subgrupo = $this->verUbicacion($nombre2, 2);
        								//echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.'<br/>';
        								//echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.'<br/>';
        								$Datos3 = $Datos;
        								$swt = 0;
        								unset($semestre);
        								unset($seccion);
        								while ($Dato3 = current($Datos3)) {
        									$nombre3 = key($Datos3);
        									if ($this->verUbicacion($nombre3, 0) == $pagina && $this->verUbicacion($nombre3, 1) == $grupo && $this->verUbicacion($nombre3, 2) == $subgrupo && $this->verUbicacion($nombre3, 3) > -1) {
        										if ($this->verUbicacion($nombre3, 4) == -1) {
        											if ($swt == 0) {
        												$semestre = $Dato3;
        												$swt++;
        											} else {
        												$seccion = $Dato3;
        												$swt = 0;
        											}
        											$columna = $this->verUbicacion($nombre3, 3);
        											//echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.' Columna = '.$columna.'<br/>';
        											//echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.' Semestres = '.$semestre.' Seccion = '.$seccion.'<br/>';
        											if (isset($semestre) && isset($seccion)) {
        												$valor =  $pagina . ',' . $grupo . ',' . $subgrupo . ',' . $columna . ',' . $turno . ',' . $semestre . ',' . $seccion . ',' . $escuela . ',' . $sede . ',' . $seccion . ',';
        												if (!in_array($valor, $query)) {
        													array_push($query, $valor);
        													 //echo "$valor</br>";
        												}
        											}
        											$Datos4 = $Datos;
        											$swt1 = 0;
        											while ($Dato4 = current($Datos4)) {
        												$nombre4 = key($Datos4);
        												if ($this->verUbicacion($nombre4, 0) == $pagina && $this->verUbicacion($nombre4, 1) == $grupo && $this->verUbicacion($nombre4, 2) == $subgrupo && $this->verUbicacion($nombre4, 3) == $columna && $this->verUbicacion($nombre4, 4) > -1) {
        													if ($this->verUbicacion($nombre4, 5) == -1) {
        														$subseccion = $Dato4;
        														$subcolumna = $this->verUbicacion($nombre4, 4);
        														if (isset($seccion)) {
        															//echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.' Columna = '.$columna.' SubColumna = '.$subcolumna.'<br/>';
        															//echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.' Semestres = '.$semestre.' Seccion = '.$seccion.' SubSeccion = '.$subseccion.'<br/>';
        															//echo '{'.$pagina.','.$grupo.','.$subgrupo.','.$columna.','.$turno.','.$semestre.','.$seccion.','.$escuela.','.$sede.'}'.'<br/>';
        															$valor = $pagina . ',' . $grupo . ',' . $subgrupo . ',' . $columna . ',' . $turno . ',' . $semestre . ',' . $subseccion . ',' . $escuela . ',' . $sede . ',' . $seccion . ',';
        															if (!in_array($valor, $query)) {
        																array_push($query, $valor);
        															}
        														}
        													}
        												}
        												next($Datos4);
        											}
        										}
        									}
        									next($Datos3);
        								}
        							}
        						}
        						next($Datos2);
        					}
        				}
        			}
        			next($Datos1);
        			}
        		}
        	}
        	next($Datos);
        }
        //var_dump($Datos, $Datos1, $Datos2, $Datos3, $Datos4);die;
    
        $valores = '';
        foreach ($query as $key) {
            $valores .= $key;
        }

        $valores=rtrim($valores, ","); //se quita la ultima coma
        $array2 = array_chunk(explode(',', $valores),10); //divido el array en segmentos iguales

        

        $npaginas = array_values(end($array2))[0];
        
        $master = array();
        $cantarrays = count($array2);
        //var_dump(count($array2));
        $i = 0;
        $u = 0;
        $sub_sec = 0;
        $pag_actual = 1;
        $grupo_actual = 1;
        $subgrupo_actual = 1;
        $columna_actual = 1;
        $unicode = array();
        //var_dump($array2);die;
        $final = array();
        $secciones2 = $this->asignaciones->getAllSecciones();
        //var_dump($secciones2);die;
        foreach ($secciones2 as $value) {
            $secciones[$value["valor"]] = intval($value["pk_atributo"]);           
        }
        foreach ($array2 as $key2 => $value2){ 
            
            $unicode=$value2[8];
            $value2[8]=$value2[4];
            $value2[4]=$unicode;
            
            $unicode=$value2[7];
            $value2[7]=$value2[5];
            $value2[5]=$unicode;
            
            $unicode=$value2[8];
            $value2[8]=$value2[6];
            $value2[6]=$unicode;
            
            $unicode=$value2[9];
            $value2[9]=$value2[8];
            $value2[8]=$unicode;
            
            
            array_push($final, $value2);
            
        }
        $sub_sec = 0;
        
//var_dump($final);
        foreach ($final as $key => $value) {
            //var_dump($value);
            //var_dump($pag_actual+1);
            //var_dump(intval($value[1]),$grupo_actual);die;


            if(intval($value[0])==intval($pag_actual)){//---------------------misma pagina
                //var_dump('misma pagina');
                //var_dump($master);
                $pag_actual=intval($value[0]);
                

                if(!isset($master[$pag_actual])){

                    array_push($master[$pag_actual], array());
                }
                //var_dump($master);


            }elseif(intval($value[0])==intval($pag_actual)+1){//----------------diferente pagina
                $grupo_actual = 1;
                $subgrupo_actual = 1;
                $columna_actual = 1;
                //var_dump('diferente pagina');
                //var_dump($master);
                $pag_actual=intval($value[0]);
                
                if(!isset($master[$pag_actual])){
                    array_push($master[$pag_actual], array());
                }
            //var_dump($master);
            }


            if(intval($value[1])==intval($grupo_actual)){//---------------------------mismo grupo
                //var_dump('mismo grupo');
                //var_dump($master);
                $grupo_actual=intval($value[1]);
                if(!isset($master[$pag_actual][$grupo_actual])){

                    array_push($master[$pag_actual][$grupo_actual], array());
                }
                if(intval($value[4])==7 && !isset($master[$pag_actual][0])){
                    //var_dump('Los Naranjos');
                    //array_unshift($master[$pag_actual], "Pagina $pag_actual Los Naranjos");
                }elseif (intval($value[4])==8 && !isset($master[$pag_actual][0])) {
                    //var_dump('Centro');
                    //array_unshift($master[$pag_actual], "Pagina $pag_actual Centro");
                }
                //var_dump($master);



            }elseif(intval($value[1])==intval($grupo_actual)+1){//--------------------diferente grupo
                $subgrupo_actual = 1;
                $columna_actual = 1;
                //var_dump('diferente grupo');
                //var_dump($master);
                $grupo_actual = intval($value[1]);
                
                array_push($master[$pag_actual][$grupo_actual], array());
                if(intval($value[4])==7 && !isset($master[$pag_actual][0])){
                    var_dump('Los Naranjos');
                    //array_unshift($master[$pag_actual], "Pagina $pag_actual Los Naranjos");
                }elseif (intval($value[4])==8 && !isset($master[$pag_actual][0])) {
                    var_dump('Centro');
                    //array_unshift($master[$pag_actual], "Pagina $pag_actual Centro");
                }
                    //var_dump($master);
            }


            if(intval($value[2])==intval($subgrupo_actual)){//---------------------------mismo subgrupo
                //var_dump('mismo subgrupo');
                //var_dump($master);
                $subgrupo_actual=intval($value[2]);
                if(!isset($master[$pag_actual][$grupo_actual][$subgrupo_actual])){

                    array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                }
                if (!isset($master[$pag_actual][$grupo_actual][0])) {
                    //array_unshift($master[$pag_actual][$grupo_actual], "Grupo $grupo_actual");
                }
                
                

            }elseif(intval($value[2])==intval($subgrupo_actual)+1){//--------------------diferente subgrupo
                //var_dump('diferente subgrupo');
                //var_dump($master);
                $subgrupo_actual = intval($value[2]);
                array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                //var_dump($master);
                if (!isset($master[$pag_actual][$grupo_actual][0])) {
                    //array_unshift($master[$pag_actual][$grupo_actual], "Grupo $grupo_actual");
                }
                $columna_actual = 1;
            }


                        
            if(intval($value[3])==intval($columna_actual)){//--------------------------------misma columna
                //var_dump('misma columna');
                //var_dump($master);
                $columna_actual =intval($value[3]);
                if(!isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                    $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);

                    array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual], array());

                    //$master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                }
                if (!isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][0])) {
                    //var_dump($master[$pag_actual][$grupo_actual][$subgrupo_actual][0],'aja');
                    $escuela = $this->escuelas->getName($value[5]);
                    //array_unshift($master[$pag_actual][$grupo_actual][$subgrupo_actual], "SubGrupo $subgrupo_actual $escuela");
                    //var_dump('esteban',$master);
                }
                /*
                $semestre = $this->vw_semestres->getName($value[7]);             
                array_unshift($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual], "Columna $columna_actual $semestre");
                */

            }elseif(intval($value[3])==intval($columna_actual)+1){ //-----------------------diferente columna
                //var_dump('diferente columna');
                //var_dump('dido',$master);
                $sub_sec=0;
                $columna_actual = intval($value[3]);
                //$mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                //var_dump($mate);    
                //$master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual], array());
                //var_dump($master);
                if (!isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][0])) {
                    $escuela = $this->escuelas->getName($value[5]);
                    //array_unshift($master[$pag_actual][$grupo_actual][$subgrupo_actual], "SubGrupo $subgrupo_actual $escuela");
                }
                /*
                $semestre = $this->vw_semestres->getName($value[7]);               
                array_unshift($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual], "Columna $columna_actual $semestre");
                */
            }
            
            if($value[8]==$value[9]){               // MISMA SUBSECCION

                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                //var_dump($mate);die;
                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual][0]=$mate;
                //var_dump($master);die;
                
            }elseif ($value[8] != $value[9]) {      //DISTINTA SUBSECCION

                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual][$sub_sec+1]=$mate;
                $sub_sec++;
            }

            //var_dump($periodo, $value[], $value[], $value[], $value[], $value[], )
            //$this->periodo_header = c;
            //$this->pensum_header = v;
            //$this->sede_header = v;
            //$this->edificio_header =v;
            //$this->escuela_header = v;
            //var_dump($this->view->baseUrl());die;
            if($sede = $Datos["selsede1"] == 7){
                $sede='Los Naranjos';
            }else{
                $sede='Centro';
            }
            
        

            
            $escuela=$this->horarios->checkescuela($value[7],$value[6],$value[5],$value[8],$periodo);
             //var_dump($escuela);die;
        }
        //var_dump($escuela[0]["valor"]);die;

        $table_header   = array(    'periodo' => $this->periodo->getNamePeriodo($periodo),
                                    'pensum' => $pensums,
                                    'sede' => $sede,
                                    'escuela' => $escuela[0]["valor"],
                                    'url' => $this->view->baseUrl()
                                );
        $data=$this->SwapBytes_Crud_Horario->getdata($master);
        $header=$this->SwapBytes_Crud_Horario->makeHeader($table_header);
        $final=$this->SwapBytes_Crud_Horario->makeTable($header , $data);
        //$final= $this->SwapBytes_Crud_Horario->makeplantilla($table_header);
        echo $final;die;   
             
            /**

                    }elseif(intval($value[2])==intval($subgrupo_actual)+1){ //diferente subgrupo
                        var_dump('diferente subgrupo');
                        $subgrupo_actual = intval($value[2]);
                        array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        //var_dump($master);
                        $columna_actual = 1;



                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        

                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                        }
                    }
                


                }elseif(intval($value[1])==intval($grupo_actual)+1){ //diferente grupo
                    $subgrupo_actual = 1;
                    $columna_actual = 1;
                    var_dump('diferente grupo');
                    $grupo_actual = intval($value[1]);
                    
                    array_push($master[$pag_actual][$grupo_actual], array());
                    //var_dump($master);




                    if(intval($value[2])==intval($subgrupo_actual)){ //mismo subgrupo
                        
                        var_dump('mismo subgrupo');
                        $subgrupo_actual=intval($value[2]);
                        if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual])){

                        }else{
                            array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        }




                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        

                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }
                        //var_dump($master);




                    }elseif(intval($value[2])==intval($subgrupo_actual)+1){ //diferente subgrupo
                        var_dump('diferente subgrupo');
                        $subgrupo_actual = intval($value[2]);
                        array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        //var_dump($master);
                        $columna_actual = 1;





                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        
                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }
                    }
                    
                }
            }
            if(intval($value[0])==intval($pag_actual)+1){           //diferente pagina
                $grupo_actual = 1;
                $subgrupo_actual = 1;
                $columna_actual = 1;
                var_dump('diferente pagina');
                $pag_actual=intval($value[0]);
                
                if(isset($master[$pag_actual])){

                }else{
                    array_push($master[$pag_actual], array());
                }
                //var_dump($master);
                if(intval($value[1])==intval($grupo_actual)){ //mismo grupo
                    var_dump('mismo grupo');
                    $grupo_actual=intval($value[1]);
                    if(isset($master[$pag_actual][$grupo_actual])){

                    }else{
                        array_push($master[$pag_actual][$grupo_actual], array());
                    }
                    //var_dump($master);
                    
                    if(intval($value[2])==intval($subgrupo_actual)){ //mismo subgrupo
                        var_dump('mismo subgrupo');
                        $subgrupo_actual=intval($value[2]);
                        if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual])){

                        }else{
                            array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        }
                        //var_dump($master);
                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        
                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }

                        
                    }elseif(intval($value[2])==intval($subgrupo_actual)+1){ //diferente subgrupo
                        var_dump('diferente subgrupo');
                        $subgrupo_actual = intval($value[2]);
                        array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        //var_dump($master);
                        $columna_actual = 1;
                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        
                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }
                    }
                    $subgrupo_actual=1;
                }elseif(intval($value[1])==intval($grupo_actual)+1){ //diferente grupo
                    $subgrupo_actual = 1;
                    $columna_actual = 1;
                    var_dump('diferente grupo');
                    $grupo_actual = intval($value[1]);
                    
                    array_push($master[$pag_actual][$grupo_actual], array());
                    //var_dump($master);
                    if(intval($value[2])==intval($subgrupo_actual)){ //mismo subgrupo
                        
                        var_dump('mismo subgrupo');
                        $subgrupo_actual=intval($value[2]);
                        if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual])){

                        }else{
                            array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        }
                        //var_dump($master);
                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        
                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }
                    }elseif(intval($value[2])==intval($subgrupo_actual)+1){ //diferente subgrupo
                        var_dump('diferente subgrupo');
                        $subgrupo_actual = intval($value[2]);
                        array_push($master[$pag_actual][$grupo_actual][$subgrupo_actual], array());
                        //var_dump($master);
                        $columna_actual = 1;
                        if(intval($value[3])==intval($columna_actual)){ //misma columna
                            var_dump('misma columna');
                            $columna_actual =intval($value[3]);
                            if(isset($master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual])){

                            }else{
                                $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                                $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            }
                            //var_dump($master);

                        
                        }elseif(intval($value[3])==intval($columna_actual)+1){ //diferente columna
                            var_dump('diferente columna');
                            $columna_actual = intval($value[3]);
                            $mate=$this->horarios->checkvalues($value[7],$value[6],$value[5],$value[8],$periodo);
                                
                            $master[$pag_actual][$grupo_actual][$subgrupo_actual][$columna_actual]= $mate;
                            //var_dump($master);
                        }
                    }
                    $subgrupo_actual=1;
                }
            }

        }  
            
    */
            
        //var_dump($master);die;






        

        /*foreach ($zordon as $jason) {
            var_dump($jason["semestre"],$jason["turno"],$jason["escuela"],$jason["seccion"],$jason["periodo"]);
            $aja[$n] = $this->horarios->checkvalues($jason["semestre"],$jason["turno"],$jason["escuela"],$jason["seccion"],$jason["periodo"]); 
            
            $n++;
        }
        var_dump($aja);die;
           
        //var_dump($aja);die;
        
        
         //var_dump($array2);die;
         //var_dump($this->horarios->getHorariosDetalle($periodo, chop($valores, ",")));
         //var_dump('1');die;
         //echo $periodo." ".chop($valores, ",");
         //var_dump('2');die;
        $horarios = $this->horarios->getHorariosDetalle($periodo, chop($valores, ","));
        //var_dump('3');die;
        sort($horarios);
        //var_dump('4');die;
        //var_dump($horarios);die;
        // Genera el reporte en PDF.
        $config = Zend_Registry::get('config');

		$dbname = $config->database->params->dbname;
		$dbuser = $config->database->params->username;
		$dbpass = $config->database->params->password;
		$dbhost = $config->database->params->host;
		$report = APPLICATION_PATH . '/modules/reports/templates/Horarios/reportehorarios.jrxml';
//		$filename    = 'horarios';
        $valores     = "{{{1,1,1,1,9,873,865,13,7,865},{1,1,1,1,9,873,1630,13,7,865},{1,1,1,1,9,873,1631,13,7,865},{2,1,1,1,10,873,871,11,8,871}}}";
        // $valores     = chop($valores, ",");
		$params      = "'periodo=integer:{$periodo}|valores=string:{$valores}'";
		// $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f pdf -b64";
		// $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f pdf -b64";
		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -f pdf -b64";

		//Zend_Layout::getMvcInstance()->disableLayout();
        // Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        // Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/pdf");
        // Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename=horarios.pdf" );

        // $outstream = exec($cmd);
        // echo base64_decode($outstream);
      echo $cmd;*/
    }

}