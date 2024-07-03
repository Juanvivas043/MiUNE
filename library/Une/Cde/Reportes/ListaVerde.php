<?php
/**
 *
 * @author Lic. Nicola Strappazzon C.
 */
class Une_Cde_Reportes_ListaVerde {
    private $Listas         = null;
    private $fechaImpresion = null;
    private $NumeroPagina   = 1;
    private $MaxColumnas    = 130;
    private $MaxCol_Numero  = 2;
    private $MaxCol_Cedula  = 8;
    private $MaxCol_Estuds  = 45;
    private $MaxRowsPerPage = 40;
    private $RowNumPage     = 1;
    private $RowNumTotal    = 1;
    private $fileContent    = '';
    
    public function __construct() {
        ini_set("memory_limit","64M");

        Zend_Loader::loadClass('Models_DbTable_Atributos');
        Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

        $this->Atributos       = new Models_DbTable_Atributos();
        $this->RecordAcademico = new Models_DbTable_Recordsacademicos();
        $this->fechaImpresion  = date('d/m/y H:i');
    }

    public function generar($Data) {
        $Periodo     = $Data['selPeriodo'];
        $Sede        = $Data['selSede'];
        $Escuela     = $Data['selEscuela'];
        $Semestre    = $Data['selSemestre'];
        $Materia     = $Data['selMateria'];
        $Seccion     = $Data['selSeccion'];
        $Estudiantes = $Data['chkEstudiante'];
        $TipoReporte = $Data['tipoListaVerde'];

        // Definimos el nombre del archivo a descargar.
        $fileName   = 'LV';

        // Asignamos las condiciones a la clausula WHERE siempre y cuando sean
        // comunes para cada Tipo de Reporte.
        $Where  = '     periodo = ' . $Periodo;
        $Where .= ' AND sede    = ' . $Sede;
        $Where .= ' AND escuela = ' . $Escuela;

        // Asignamos las condiciones a la clausula WHERE de forma particular
        // dependiendo del Tipo de Reporte seleccionado.
        switch($TipoReporte) {
            case 1:
                $Where .= ' AND semestre >= ' . $Semestre;
                break;
            case 2:
                $Where .= ' AND semestre = ' . $Semestre;
                $Where .= ' AND materia  = ' . $Materia;
                $Where .= ' AND seccion  = ' . $Seccion;

                // Agregamos los estudiantes seleccionados.
                if(isset($Estudiantes)) {
                    $Usuarios = (is_array($Estudiantes))? implode(',', $Estudiantes) : $Estudiantes;
                    $Where   .= " AND usuario IN ({$Usuarios})";
                }

                break;
        }

        $this->Listas = $this->RecordAcademico->getListaVerde($Where);

        /*
         * Inabilitamos el Layout y definimos un Header apropiado para el archivo
         * que se genera, obligando para que se pueda descargar, y definir las
         * caracteristicas basicas del mismo.
         */
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', 'text/plain;charset=ASCII');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Transfer-Encoding:', 'binary');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Description', 'File Transfer');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        // Iniciamos valores del rompe control para el reporte.
        $RompeControl['Docente'] = null;
        $RompeControl['Seccion'] = null;
        $RompeControl['Materia'] = null;

        // Se inicia todo el proceso siempre y cuando exista una lista de estudiantes.
        if(!empty($this->Listas)) {
            foreach($this->Listas as $Index => $Lista) {
                $escuela    = utf8_decode($Lista['Escuela']);
                $turno      = utf8_decode($Lista['Turno']);
                $asignatura = utf8_decode($Lista['AsignaturaNombre']);
                $docente    = utf8_decode($Lista['Docente']);
                $fi         = utf8_decode($Lista['fechainicio']);
                $ff         = utf8_decode($Lista['fechafin']);

                /*
                 * Aplicamos el rompe control siempre y cuando sea diferente:
                 * - Los docentes.
                 * - La seccion.
                 * - Asignatura.
                 */
                if(($Lista['Docente C.I.'] <> $RompeControl['Docente']) ||
                    ($Lista['Seccion'] <> $RompeControl['Seccion'])      ||
                    ($Lista['AsignaturaCodigo'] <> $RompeControl['Materia'])) {
                    if($Index > 0 && ($this->NumeroPagina == ceil($this->RowNumTotal / $this->MaxRowsPerPage))) {
                        if($this->RowNumPage <= $this->MaxRowsPerPage) {
                            $this->completarLineas();
                        }
                        $this->pieDePagina();
                    }

                    $RompeControl['Docente'] = $Lista['Docente C.I.'];
                    $RompeControl['Seccion'] = $Lista['Seccion'];
                    $RompeControl['Materia'] = $Lista['AsignaturaCodigo'];

                    $this->RowNumTotal  = 1;
                    $this->RowNumPage   = 1;
                    $this->NumeroPagina = 1;

                    $this->encabezado($escuela, $turno, $asignatura, $Lista['AsignaturaCodigo'], $Lista['Semestre'], $Lista['Seccion'], $Lista['Periodo'], $fi, $ff, $docente, $Lista['Docente C.I.']);
                }

                /*
                 * Se imprime el encabezado siempre y cuando:
                 * - El numero de pagina no es el ultimo.
                 */
                if($this->NumeroPagina <> ceil($this->RowNumTotal / $this->MaxRowsPerPage)) {
                    $this->NumeroPagina = ceil($this->RowNumTotal / $this->MaxRowsPerPage);

                    $this->encabezado($escuela, $turno, $asignatura, $Lista['AsignaturaCodigo'], $Lista['Semestre'], $Lista['Seccion'], $Lista['Periodo'], $fi, $ff, $docente, $Lista['Docente C.I.']);
                }

                /**
                 * Se imprime cada registro con los datos del estudiante, siempre
                 * y cuando:
                 * - El numero de lineas por pagina no supera el maximo definido.
                 */
                if($this->RowNumPage <= $this->MaxRowsPerPage) {
                    $apellido = utf8_decode($Lista['Apellido']);
                    $nombre   = utf8_decode($Lista['Nombre']);

                    $estudiante = $apellido . ', ' . $nombre;

                    if(strlen($estudiante) > ($this->MaxCol_Estuds - 1)) {
                        $estudiante = substr($estudiante, 0, strrpos($estudiante, ' ') + 2) . '.';
                    }

                    $estudiante = substr($estudiante, 0, $this->MaxCol_Estuds);

                    $this->fileContent .= ' ';
                    $this->fileContent .= str_pad($this->RowNumTotal       , $this->MaxCol_Numero, ' '    , STR_PAD_LEFT) . ' ';
                    $this->fileContent .= str_pad($Lista['Estudiante C.I.'], $this->MaxCol_Cedula, ' '    , STR_PAD_LEFT) . ' ';
                    $this->fileContent .= str_pad($estudiante              , $this->MaxCol_Estuds, CHR(95)              ) . ' ';
                    $this->fileContent .= ' |___|    |____|____|______|  ' . chr(179) . '  |___|    |____|____|______|  =  |_____|';
                    $this->fileContent .= "\r\n";
                }

                $this->NumeroPagina = ceil($this->RowNumTotal / $this->MaxRowsPerPage);

                /*
                 * Imprimimos el pie de pagina siempre y cuando:
                 * - Se completa la cantidad de lineas por pagina.
                 * - Se llego a la ultima pagina.
                 */
                if($this->RowNumPage == $this->MaxRowsPerPage && ($this->NumeroPagina == ceil($this->RowNumTotal / $this->MaxRowsPerPage))) {
                    $this->pieDePagina();
                    $this->RowNumPage = 0;
                }

                $this->RowNumTotal++;
                $this->RowNumPage++;
            }
        }

        $this->completarLineas();
        $this->pieDePagina();

        /*
         * Realizamos la conversion de formato para que se pueda apreciar los
         * caracteres especiales en MS-DOS, utilizando la codificacion ASCII.
         */
        $this->fileContent = $this->convertToASCII($this->fileContent);

        // Imprimimos el contenido para generar el archivo.
        echo $this->fileContent;
    }

    /*
     * Llenamos con Nuevas Lineas para completar correctamente la estructura
     * del archivo.
     */
    private function completarLineas() {
        for($i = 0; $i <= ($this->MaxRowsPerPage - $this->RowNumPage); $i++) {
            $this->fileContent .= "\r\n";
        }
    }

    /**
     * Convierte la codificacion de caracteres ISO 8859-1 a ASCII.
     */
    private function convertToASCII($content) {
        $arr=array(chr(190)=>chr(165), // Ñ
                   chr(241)=>chr(164), // ñ
                   chr(211)=>chr(162), // ó
        );
        
        return strtr($content,$arr);
    }

    private function encabezado($Escuela, $Turno, $AsignaturaNombre, $AsignaturaCodigo, $Semestre, $Seccion, $Periodo, $FI, $FF, $Profesor, $CI) {
        $this->fileContent .= " PAGINA " . $this->NumeroPagina;
        $this->fileContent .= str_pad($this->FechaImpresion, $this->MaxColumnas - 8, ' ',STR_PAD_LEFT);
        $this->fileContent .= "\r\n";
        $this->fileContent .= "\r\n";
        $this->fileContent .= " U N I V E R S I D A D  N U E V A  E S P A R T A";
        $this->fileContent .= "\r\n";
        $this->fileContent .= " ESCUELA: " . str_pad($Escuela, 30, ' ') . " PERIODO: {$Periodo}, {$FI} - {$FF}.\r\n";
        $this->fileContent .= " TURNO: " . str_pad($Turno  , 32, ' ') . " SEMESTRE: {$Semestre} - {$Seccion}.\r\n";
        $this->fileContent .= " ASIGNATURA: " . str_pad($AsignaturaNombre  , 27, ' ') . " CODIGO DE LA MATERIA: {$AsignaturaCodigo}{$Seccion}\r\n\r\n";
        $this->fileContent .= " C.I.: " . str_pad($CI  , 33, ' ') . " PROFESOR: " . str_pad($Profesor  , 42, ' ') . " FIRMA: " . str_repeat(CHR(95), 30);
        $this->fileContent .= "\r\n\r\n";
        $this->fileContent .= ' ANOTE EN LAS COLUMNAS IDENTIFICADAS COMO \'I\' LA CANTIDAD DE INASISTENCIAS, \'EC\' LA EVALUACION CONTINUA, \'PL\' LA PRUEBA DE LAPSO, L1 ';
        $this->fileContent .= "\r\n";
        $this->fileContent .= ' Y L2 DEFINITIVA DE CADA LAPSO, Y \'DEF\' LA CALIFICACION DEFINITIVA. ANOTE EN NUMEROS TODAS LAS CASILLAS. NO ENMIENDE.';
        $this->fileContent .= "\r\n";
        $this->fileContent .= '';
        $this->fileContent .= "\r\n";
        $this->fileContent .= ' ' . str_repeat('-', $this->MaxColumnas);
        $this->fileContent .= "\r\n";
        $this->fileContent .= '  # ';
        $this->fileContent .= str_pad('CEDULA'           , $this->MaxCol_Cedula, ' ', STR_PAD_BOTH);
        $this->fileContent .= str_pad('APELLIDO Y NOMBRE', $this->MaxCol_Estuds, ' ', STR_PAD_BOTH);
        $this->fileContent .= '   | I |    | EC | PL |  L1  |     | I |    | EC | PL |  L2  |     | DEF |';
        $this->fileContent .= "\r\n";
        $this->fileContent .= ' ' . str_repeat('=', $this->MaxColumnas);
        $this->fileContent .= "\r\n";
    }

    private function pieDePagina(){
        $Msg = "++++ NO AGREGAR ESTUDIANTES EN LA HOJA DE EVALUACION. EL ESTUDIANTE DEBERA REPORTAR LOS CASOS A CONTROL DE ESTUDIOS. ++++";

        $this->fileContent .= "\r\n";
        $this->fileContent .= str_pad($Msg, $this->MaxColumnas, ' ', STR_PAD_BOTH);
        $this->fileContent .= "\r\n";
        $this->fileContent .= "\r\n";
        $this->fileContent .= " EL PROFESOR DEBE CONSIGAR COMO ANEXO EL LISTADO PROVISIONAL DE ESTUDIANTES YA ENTREGADO, DEBIDAMENTE COMPLETADO CON LAS ASISTENCIAS\r\n";
        $this->fileContent .= " Y LAS CALIFICACIONES DE LA EVALUACION CONTINUA.\r\n";
        $this->fileContent .= "\r\n";
        $this->fileContent .= "\r\n";
        $this->fileContent .= "\r\n";
        $this->fileContent .= "                                                   ____________________________________    ____________________________________";
        $this->fileContent .= "\r\n";
        $this->fileContent .= "                                                      Fecha y firma del delegado (L1)         Fecha y firmadel delegado (L2)";
        $this->fileContent .= "\r\n";
        $this->fileContent .= "\r\n";
    }
}
?>
