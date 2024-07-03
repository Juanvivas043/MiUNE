
<?php

class Reports_HorariosController extends Zend_Controller_Action {

    private $_title = 'Reportes \ Horarios Académicos';

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Horarios');
        Zend_Loader::loadClass('Models_DbTable_Periodos');
        Zend_Loader::loadClass('Une_Filtros');
        Zend_Loader::loadClass('Models_DbView_Semestres');
        Zend_Loader::loadClass('Models_DbView_Turnos');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        Zend_Loader::loadClass('Zend_Soap_Client');

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();

        $this->filtros = new Une_Filtros();
//      $this->SwapBytes_Ajax        = new SwapBytes_Ajax();
//      $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Soap = new SwapBytes_Soap();

        $this->SwapBytes_Ajax = new SwapBytes_Ajax();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Crud_Action = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search = new SwapBytes_Crud_Search();
        $this->SwapBytes_Date = new SwapBytes_Date();
        $this->SwapBytes_Form = new SwapBytes_Form();
        $this->SwapBytes_Html_Message = new SwapBytes_Html_Message();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        $this->asignaciones = new Models_DbTable_Asignaciones();
        $this->horarios = new Models_DbTable_Horarios();
        $this->grupo = new Models_DbTable_UsuariosGrupos();
        $this->periodo = new Models_DbTable_Periodos();
        $this->vw_semestres = new Models_DbView_Semestres();

        $this->Data['periodo']  = $this->Request->getParam('periodo');
        $this->Data['sede']     = $this->Request->getParam('sede');
        $this->Data['escuela']  = $this->Request->getParam('escuela');
        $this->Data['semestre'] = $this->Request->getParam('semestre');
        $this->Data['materia']  = $this->Request->getParam('materia');
        $this->Data['seccion']  = $this->Request->getParam('seccion');
        $this->Data['turno']    = $this->Request->getParam('turno');

        $this->filtros->setDisplay(true, false, false, false, false, false, false, false, false);
        $this->filtros->setDisabled(false, false, false, false, false, false, false, false, false);
        $this->filtros->setRecursive(false, false, false, false, false, false, false, false, false);

        $this->SwapBytes_Crud_Search->setDisplay(false);
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

    public function escuelaAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede'));
        $dataRows = $this->asignaciones->getSelectEscuelas();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function semestreAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela', 'turno'));
        $dataRows = $this->asignaciones->getSelectSemestres();
        $this->SwapBytes_Ajax_Action->fillSelect($dataRows);
    }

    public function turnoAction() {
        $this->asignaciones->setData($this->Data, array('periodo', 'sede', 'escuela'));
        $dataRows = $this->asignaciones->getSelectTurnos();
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
        // var_dump($Datos);
        $query = Array();
        while ($Dato = current($Datos)) {
            $nombre = key($Datos);
            //echo '<b>'.$nombre.'</b><br/>';

            if ($this->verUbicacion($nombre, 0) == null) {
                // echo 'Periodo ='.$Dato.'<br/>';
                $periodo = $Dato;
            } else if ($this->verUbicacion($nombre, 0) > -1) {
                if ($this->verUbicacion($nombre, 1) == -1) {
                    $sede = $Dato;
                    $pagina = $this->verUbicacion($nombre, 0);
                    // echo 'Pagina ='.$pagina.'<br/>';
                    // echo 'Periodo = '.$periodo.' Sede ='.$sede.'<br/>';
                    $Datos1 = $Datos;
                    while ($Dato1 = current($Datos1)) {
                        $nombre1 = key($Datos1);
                        if ($this->verUbicacion($nombre1, 0) == $pagina && $this->verUbicacion($nombre1, 1) > -1) {
                            if ($this->verUbicacion($nombre1, 2) == -1) {
                                $escuela = $Dato1;
                                $grupo = $this->verUbicacion($nombre1, 1);
                                // echo 'Pagina ='.$pagina.' Grupo = '.$grupo.'<br/>';
                                // echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.'<br/>';
                                $Datos2 = $Datos;
                                while ($Dato2 = current($Datos2)) {
                                    $nombre2 = key($Datos2);
                                    if ($this->verUbicacion($nombre2, 0) == $pagina && $this->verUbicacion($nombre2, 1) == $grupo && $this->verUbicacion($nombre2, 2) > -1) {
                                        if ($this->verUbicacion($nombre2, 3) == -1) {
                                            $turno = $Dato2;
                                            $subgrupo = $this->verUbicacion($nombre2, 2);
                                            // echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.'<br/>';
                                            // echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.'<br/>';
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
                                                        // echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.' Columna = '.$columna.'<br/>';
                                                        // echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.' Semestres = '.$semestre.' Seccion = '.$seccion.'<br/>';
                                                        if (isset($semestre) && isset($seccion)) {
                                                            $valor = '{' . $pagina . ',' . $grupo . ',' . $subgrupo . ',' . $columna . ',' . $turno . ',' . $semestre . ',' . $seccion . ',' . $escuela . ',' . $sede . ',' . $seccion . '},';
                                                            if (!in_array($valor, $query)) {
                                                                array_push($query, $valor);
                                                                // echo "$valor</br>";
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
                                                                        // echo 'Pagina ='.$pagina.' Grupo = '.$grupo.' SubGrupo = '.$subgrupo.' Columna = '.$columna.' SubColumna = '.$subcolumna.'<br/>';
                                                                        // echo 'Periodo = '.$periodo.' Sede ='.$sede.' Escuela = '.$escuela.' Turno = '.$turno.' Semestres = '.$semestre.' Seccion = '.$seccion.' SubSeccion = '.$subseccion.'<br/>';
                                                                        // echo '{'.$pagina.','.$grupo.','.$subgrupo.','.$columna.','.$turno.','.$semestre.','.$seccion.','.$escuela.','.$sede.'}'.'<br/>';
                                                                        $valor = '{' . $pagina . ',' . $grupo . ',' . $subgrupo . ',' . $columna . ',' . $turno . ',' . $semestre . ',' . $subseccion . ',' . $escuela . ',' . $sede . ',' . $seccion . '},';
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
        $valores = '';
        foreach ($query as $key) {
            $valores .= $key;
        }
        // var_dump($this->horarios->getHorariosDetalle($periodo, chop($valores, ",")));
        // echo $periodo." ".chop($valores, ",");
        // $horarios = $this->horarios->getHorariosDetalle($periodo, chop($valores, ","));
        sort($horarios);

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
		$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f pdf -b64";
		//$cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmd.jar -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -f pdf -b64";

		Zend_Layout::getMvcInstance()->disableLayout();
        // Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        // Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/pdf");
        // Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename=horarios.pdf" );

        // $outstream = exec($cmd);
        // echo base64_decode($outstream);
      each      cho $cmd;
    }

    private function mostrarHorario($arreglo) {
        $matrix = Array();
        $pos = 0;
        while ($registro = current($arreglo)) {
            $pagina = $registro['pagina'];
            $grupo = $registro['grupo'];
            $columna = $registro['columna'];
            $subcolumna = $registro['subcolumna'];
            $posicion = $registro['posicion'];
            $iddia = $registro['iddia'];
            $idhora = $registro['idhora'];
            $seccionid = $registro['seccionid'];
            if (!isset($registro['materia'])) {
                next($arreglo);
                $pos++;
                $registro = current($arreglo);
                if (isset($registro['materia']) &&
                        $pagina == $registro['pagina'] &&
                        $grupo == $registro['grupo'] &&
                        $columna == $registro['columna'] &&
                        $subcolumna == $registro['subcolumna'] &&
                        $posicion == $registro['posicion'] &&
                        $iddia == $registro['iddia'] &&
                        $idhora == $registro['idhora']  //   &&
                // $seccionid  == $registro['seccionid']
                ) {
                    // echo "</br> array pos :".($pos - 1)." es null </br>";
                    unset($arreglo[$pos - 1]);
                } else {
                    prev($arreglo);
                    $pos--;
                }
            }
            // echo "</br>Materia: ".$registro['materia']." -> ".key($registro);
            // $matrix[$registro['pagina']][$registro['grupo']][$registro['columna']][$registro['subcolumna']][$registro['iddia']][$registro['idhora']] = $registro['materia'];
            $matrix[$registro['pagina']][$registro['grupo']][$registro['columna']][$registro['subcolumna']][$registro['iddia']][$registro['idhora']] = $pos;
            next($arreglo);
            $pos++;
        }
        // $this->mostrarArreglo($arreglo);
        // $tabla = "";
        // // var_dump($matrix);
        // // echo count($matrix);
        // // echo count($matrix[1]);
        // // echo count($matrix[1][1]);
        // // echo count($matrix[1][1][1]);
        // // echo count($matrix[1][1][1][1]);
        // // echo count($matrix[1][1][1][1][1]);
        // // echo count($matrix[1][1][1][1][1][1]);
        // // echo count($matrix[1][1][1][2]);
        // for ($i = 1; $i <= count($matrix); $i++) {
        //    // echo "</br>";
        //    // echo "Paginas ".$i;
        //    // echo "</br>";
        //    // $tabla .= "<table style='overflow= hidden;'>";
        //    $tabla .= "<table width='700px'>";
        //    for ($j = 1; $j <= count($matrix[$i]); $j++) {
        //       // echo "Grupo ".$j;
        //       // echo "</br>";
        //       // $tabla .= "<table style='overflow= hidden;'>";
        //       $tabla .= "<table>";
        //       $tabla .= "<tr>";
        //       for ($k = 1; $k <= count($matrix[$i][$j]); $k++) {
        //          // echo "Columna ".$k;
        //          // echo "</br>";
        //          $tabla .= "<td>";
        //          for ($o = 1; $o <= count($matrix[$i][$j][$k]); $o++) {
        //             // echo "SubColumna ".$o;
        //             $tabla .= "<div style='clear: both;'>";
        //             // echo "</br>";
        //             // $tabla .= "<table>";
        //             for ($e = 1; $e <= count($matrix[$i][$j][$k][$o]); $e++) {
        //                // echo "iddia ".$e;
        //                // echo "</br>";
        //                $tabla .= "<table style='float: left;'>";
        //                $tabla .= "<tr>";
        //                   $tabla .= "<td>";
        //                for ($t = 1; $t <= count($matrix[$i][$j][$k][$o][$e]); $t++) {
        //                   $pos = $matrix[$i][$j][$k][$o][$e][$t];
        //                   // echo "idhora ".$t;
        //                   // echo "</br>";
        //                $tabla .= "<tr>";
        //                   $tabla .= "<td>";
        //                   if(isset($arreglo[$pos]['materia'])){
        //                      $tabla .= $arreglo[$pos]['materia'];
        //                   }else{
        //                      $tabla .= '&nbsp;';
        //                   }
        //                   $tabla .= "</td>";
        //                $tabla .= "</tr>";
        //                   // echo $arreglo[$pos]['materia'];
        //                   // echo $matrix[$i][$j][$k][$o][$e][$t];
        //                   // echo "</br>";
        //                }
        //                   $tabla .= "</td>";
        //                $tabla .= "</tr>";
        //                   $tabla .= "</table>";
        //             }
        //             $tabla .= "</div>";
        //             // $tabla .= "</table>";
        //          }
        //             $tabla .= "</td>";
        //       }
        //             $tabla .= "</tr>";
        //             $tabla .= "</table>";
        //    }
        //             $tabla .= "</table>";
        // }
        // echo $tabla;
        // echo "</br>Paginas : ";
        // echo count($matrix);
        // echo "</br>Grupos Pag1 : ";
        // echo count($matrix[1]);
        // echo "</br>Columnas Pag1 Grup1 : ";
        // echo count($matrix[1][1]);
        // echo "</br>SubColumnas 1 1 1 : ";
        // echo count($matrix[1][1][1]);
        // echo "</br>idhora 1 1 1 1 : ";
        // echo count($matrix[1][1][1][1]);
        // echo "</br>iddia 1 1 1 1 1 :";
        // echo count($matrix[1][1][1][1][1]);
        // var_dump($matrix);
        $swt = 0;
        $swtprimero = 0;
        $diause = Array();
        $horause = Array(1 => Array(), 2 => Array(), 3 => Array(), 4 => Array(), 5 => Array(), 6 => Array());
        $horario = "<center><table class='tbl_horario'><tr><td class='td_horario'>";
        $pagina = 0;
        reset($arreglo);
        while ($registro = current($arreglo)) {
            if ($pagina == 0) {
                $horario .= " <div id=' " . $registro['pagina'] . " ' class='hor_pagina'     > ";
                $horario .= " <div id=' " . $registro['grupo'] . " ' class='hor_grupo'      > ";
                $horario .= " <div id=' " . $registro['columna'] . " ' class='hor_columna'    > ";
                $horario .= " <div id=' " . $registro['subcolumna'] . " ' class='hor_subcolumna' > ";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'   > ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia_base'        > " . $registro['dia'] . "</div>";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'        > ";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora_base''      ><table><tr><td> " . $registro['horainicio'] . "</td></tr><tr><td>" . $registro['horafin'] . "</td></tr></table></div>";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''      > ";
                $pagina = $registro['pagina'];
                $grupo = $registro['grupo'];
                $columna = $registro['columna'];
                $subcolumna = $registro['subcolumna'];
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($pagina == $registro['pagina']) {

            } else {
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= " <div id=' " . $registro['pagina'] . " ' class='hor_pagina'>     ";
                $horario .= " <div id=' " . $registro['grupo'] . " ' class='hor_grupo'>      ";
                $horario .= " <div id=' " . $registro['columna'] . " ' class='hor_columna'>    ";
                $horario .= " <div id=' " . $registro['subcolumna'] . " ' class='hor_subcolumna'> ";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'>   ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia_base'        > " . $registro['dia'] . "</div>";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'        > ";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora_base''      ><table><tr><td> " . $registro['horainicio'] . "</td></tr><tr><td>" . $registro['horafin'] . "</td></tr></table></div>";
                $pagina = $registro['pagina'];
                $grupo = $registro['grupo'];
                $columna = $registro['columna'];
                $subcolumna = $registro['subcolumna'];
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($grupo == $registro['grupo']) {
                
            } else {
                $swt = 0;
                $horause = Array();
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= " <div id=' " . $registro['grupo'] . " ' class='hor_grupo'>      ";
                $horario .= " <div id=' " . $registro['columna'] . " ' class='hor_columna'>    ";
                $horario .= " <div id=' " . $registro['subcolumna'] . " ' class='hor_subcolumna'> ";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'>   ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'>        ";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $grupo = $registro['grupo'];
                $columna = $registro['columna'];
                $subcolumna = $registro['subcolumna'];
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($columna == $registro['columna']) {

            } else {
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= " <div id=' " . $registro['columna'] . " ' class='hor_columna'>    ";
                $horario .= " <div id=' " . $registro['subcolumna'] . " ' class='hor_subcolumna'> ";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'>   ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'>        ";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $columna = $registro['columna'];
                $subcolumna = $registro['subcolumna'];
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($subcolumna == $registro['subcolumna']) {

            } else {
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= " <div id=' " . $registro['subcolumna'] . " ' class='hor_subcolumna'> ";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'>   ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'>        ";
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $subcolumna = $registro['subcolumna'];
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($posicion == $registro['posicion']) {
                
            } else {
                $swt = 1;
                $cont = 0;
                // echo "</br> posicion : ".$registro['posicion']." ".$registro['idhora']."</br>";
                // var_dump($horause[$registro['iddia']]);
                // echo "</br>";
                // echo $horause[$registro['iddia']][0][0];
                // echo "</br>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= "</div>";
                $horario .= " <div id=' " . $registro['posicion'] . " ' class='hor_posicion'>   ";
                $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'>        ";
                if ($horause[$registro['iddia']][0][$cont] < $registro['idhora'] && $swt == 1) {
                    $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_hora''>      ";
                    $horario .= "&nbsp;" . $horause[$registro['iddia']][0][0] . "&nbsp;";
                    $horario .= "</div>";
                    $cont++;
                }
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $posicion = $registro['posicion'];
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($iddia == $registro['iddia']) {

            } else {
                $cont = 0;
                array_push($horause[$iddia], $diause);
                $diause = Array();
                $horario .= "</div>";
                $horario .= "</div>";
                if ($swt == 0) {
                    $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia_base'        > " . $registro['dia'] . "</div>";
                    $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'        > ";
                    $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora_base''      ><table><tr><td> " . $registro['horainicio'] . "</td></tr><tr><td>" . $registro['horafin'] . "</td></tr></table></div>";
                } else {
                    $horario .= " <div id=' " . $registro['iddia'] . " ' class='hor_dia'>        ";
                    if (isset($horause[$registro['iddia']][0][$cont]) && $horause[$registro['iddia']][0][$cont] < $registro['idhora'] && $swt == 1) {
                        $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                        $horario .= "&nbsp;" . $horause[$registro['iddia']][0][0] . "&nbsp;";
                        $horario .= "</div>";
                        $cont++;
                    }
                }
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $iddia = $registro['iddia'];
                $idhora = $registro['idhora'];
            }
            if ($idhora == $registro['idhora']) {

            } else {
                $horario .= "</div>";
                if ($swt == 0) {
                    $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora_base''      ><table><tr><td> " . $registro['horainicio'] . "</td></tr><tr><td>" . $registro['horafin'] . "</td></tr></table></div>";
                }
                $horario .= " <div id=' " . $registro['idhora'] . " ' class='hor_hora''>      ";
                $idhora = $registro['idhora'];
            }
            if (isset($registro['materia'])) {
                $horario .= "&nbsp;" . $registro['idhora'] . "  " . $registro['materia'];
            } else {
                $horario .= "&nbsp;" . $registro['idhora'] . "&nbsp;";
            }
            $dia = $registro['idhora'];
            array_push($diause, $dia);
            // echo $horause[$dia];
            // echo "Pagina $pagina </br>";
            // echo "grupo $grupo </br>";
            // echo "columna $columna </br>";
            // echo "subcolumna $subcolumna </br>";
            // echo "posicion $posicion </br>";
            // echo "iddia $pagina </br>";
            // echo "idhora $idhora </br>";
            // echo "seccionid ".$registro['seccionid']. "</br>";
            // echo "materia ".$registro['materia']."</br>";
            next($arreglo);
        }
        // var_dump($horause);
        $horario .= "</td></tr></table></center>";
        echo $horario;
    }

    private function buscarMatriz($matriz, $buscar1, $buscar2, $buscar3, $buscar4, $buscar5, $buscar6) {
        foreach ($matriz as $arreglo) {
            if (in_array($buscar1, $arreglo) && in_array($buscar2, $arreglo) && in_array($buscar3, $arreglo) && in_array($buscar4, $arreglo) && in_array($buscar5, $arreglo) && in_array($buscar6, $arreglo)) {
                return true;
            }
        }
        return false;
    }

    private function mostrarArreglo($arreglo) {
        $tablatop = '<table id = "horarios" border = 1>';
        $tabla = '';
        $swtheader = 0;
        $headers = '<thead>';
        $conttotal = 0;
        while ($filas = current($arreglo)) {
            $tabla .= '<tr>';
            $cont = 0;
            while ($columnas = current($filas)) {
                if ($swtheader == 0) {
                    $headers .='<th>' . key($filas) . '</th>';
                    $conttotal++;
                }
                $cont++;
                $tabla .= '<td>';
                $tabla .= $columnas;
                $tabla .= '</td>';
                next($filas);
            }
            if ($cont != $conttotal) {
                for ($i = 0; $i < ( $conttotal - $cont ); $i++) {
                    $tabla .= '<td> &nbsp; </td>';
                }
            }
            $tabla .= '</tr>';
            $swtheader++;
            next($arreglo);
        }
        $tabla .= '</tbody></table>';
        echo $tablatop;
        echo $headers . '</thead><tbody>';
        echo $tabla;
    }

}
