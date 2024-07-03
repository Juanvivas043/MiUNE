<?php

/**
 * @author Alejandro Tejada.
 * alectejada@gmail.com 26/5/2014
 */

class Une_Cde_Transactions_CalificacionesParciales {

    public static $regla_insasistencias = 25;

	public function __construct($recordAcademico, $asignaciones, $jquery) {
		Zend_Loader::loadClass('Models_DbTable_CalificacionesParciales');
        $this->calificaciones           = new Models_DbTable_CalificacionesParciales();
		$this->record = $recordAcademico;
		$this->asignaciones = $asignaciones;
		$this->jquery = $jquery;
	}

	public function listar($params) {

		$rows = $this->record->getEstudiantesEvaluaciones($params);
		return $this->pivote_evaluaciones($rows);
	}
	/**
	 * Analizar arreglos pgsql de evaluaciones
	 * para agregarlos a la tabla de notas
	 *  parciales
	 */
	private function pivote_evaluaciones($rows) {

		foreach ($rows as $inde => $row) {
			foreach ($row as $index => $value) {
				if (strstr($index,'ev')) {
					$array 	= $this->pg_array_parse($value);
					if ($array) {
						foreach ($array as $columna => $valor) {
							$rows[$inde][$array['fk_evaluacion'].'_'.$columna] = $valor;
						}
					}
				}
			}
		}
		return $rows;
	}

	/**
	 *	arrays de postgresql a arrays de php
	 *	para poder analizar las notas de cada evaluacion
	 *
	 * */
	private function pg_array_parse($literal) {
		if ($literal == '') return;
		$array = array();
		list($array['pk_recordevaluacion'],$array['fk_evaluacion'],$array['calificacion']) = explode(',', trim($literal, '{}'));
		return $array;
	}


    public function isAsignaturaRegimenContinuo($data) {
        $asignatura = $this->calificaciones->getAsignaturaRegimen($data);
        return $asignatura[0]['cargacontinua'];
    }
	/* Valida el formato de las notas */
	public function isValid($user, $rows, $periodo, $data) {

		$insert = array();
		$isValid = true;
		$evaluaciones = $this->record->getAllEvaluacionesMax($data);
		/*pivote a las evaluaciones para solo recorrer una vez*/
		$maximos = array();

		foreach($evaluaciones as $index => $evaluacion) {
			$maximos[$evaluacion['pk_regimen_evaluacion']] = $evaluacion['maximo'];
		}
		foreach ($rows as $rowIndex => $rowValue) {
			$insertValue = array();
			//solo los campos que le asigne "_" son calificaciones
			//Se tomara en cuenta los campos Vacios como no evaluados
			if (strstr($rowIndex, '_')) {
				list($insertValue['fk_evaluacion'], $insertValue['fk_recordacademico']) = explode('_',$rowIndex);
				$insertValue['calificacion'] = $rowValue;
				if ($rowValue !== '') {
					$errorRow = false;
					$insert[] = $insertValue;
					// Si se encuentra en el rango de notas.
					if (!($rowValue >= 0 && $rowValue <= $maximos[$insertValue['fk_evaluacion']]) && $maximos[$insertValue['fk_evaluacion']] != 0)
					{ $errorRow = true; $isValid = false; }
					// Verifica si es numerico.
					if (!is_numeric($rowValue) && !is_float($rowValue) )  { $errorRow = true; $isValid = false; }
					//quita las comas
					if (strstr($RowValue, ',') > 0) { $errorRow = true; $isValid = false; }
					// asigna la class del cuadro dependiendo si tiene error


					// Cambio para no considerar inasistencias
					 $errorRow = false;
					if ($errorRow == true) $backgroundColor = "'TextBoxAlert'"; else $backgroundColor = "'TextBoxNormal'";
					// Cambio para no considerar inasistencias


					// acciones para la vista js para asignar las clases y 0 en cuadros vacios
					$json[] = $this->jquery->setAttr($rowIndex, 'class', $backgroundColor);
					$json[] = "$('#{$rowIndex}').val({$rowValue})";
				}
				}
			unset($rows[$rowIndex]);
		}
		if (empty($insert)) return array('isValid' => $isValid);
		$isComplete = $this->record->getCountEvaluacionesFaltantes($data, array('862','864','1699'), $insert) == 0;
		$this->isValidAction = $json;
		$isValidForUpdate = $this->isValidForUpdate($user, $periodo, $insert);
		return array('isValid' => $isValid , 'isComplete' => $isComplete);
	}

	/*Cambia el estado de la materia para que lo puedan imprimir */
	public function setEstadoMateria($pk_asignaciones, $estado) {

		if (strrpos($pk_asignaciones, ",")) {
			$pk_asignaciones = explode(",", $pk_asignaciones);
			foreach ($pk_asignaciones as $pk_asignacion) {
				$this->asignaciones->updateRow($pk_asignacion, array('fk_estado' => $estado));
			}
		} else {
			$this->asignaciones->updateRow($pk_asignaciones, array('fk_estado' => $estado));
		}
	}

	/*Valida si el usuarios puede actualizar las notas */
	public function isValidForUpdate($user, $periodo, $insert) {
		$in= '';
		foreach ($insert as $value) {
			$in .= "{$value['fk_recordacademico']}";
			if ($value != end($insert)) {
				$in .=',';
			}
		}
		 return $this->record->isValidUpdateforUser($user,$periodo,$in);
	}

	/*Asigna solo los records que vamos a actualizar una vez*/
	public function formatRecords($rows) {
		$records = array();
		foreach($rows as $rowIndex => $value) {
			if (strstr($rowIndex, '_')) {
				$record = explode('_',$rowIndex);
				$record = $record[1];
				if (!in_array($record,$records)) {
					$records[] = $record;
				}
			}
		}
		return $records;
	}


	public function transaction_cxud ($rows, $isCommitFinal, $data) {
		$upsert = array();
		$delete = array();
		$records = array();
		foreach ($rows as $index => $value) {
			if(strstr($index, '_')) {
				// asigno los valores que voy a insertar
				list($values['fk_evaluacion'],$values['fk_recordacademico']) = explode('_',$index);
				$values['calificacion'] = $value;
				if ($value !== '') {
					$upsert[] = $values;
				} else {
					$delete[] = $values;
				}
				if (!in_array($values['fk_recordacademico'], $records) && $isCommitFinal) {
					$records[] = $values['fk_recordacademico'];
				}
			}
		}
		if($isCommitFinal) {
			$clases = $this->asignaciones->get_clases_asignacion_feriado($data);

// Cambio para no considerar inasistencias

$inasistencias = ceil($clases);			
//$inasistencias = ceil($clases * Une_Cde_Transactions_CalificacionesParciales::$regla_insasistencias/100);

// Cambio para no considerar inasistencias


		}
		return $this->record->transaction_calificaciones_parciales($upsert, $delete, $isCommitFinal, $records, $inasistencias, $data['periodo']);
	}

	/**
	 * Añade las Validaciones js a la table para mostrar en rojo donde hay errores
	 */
	public function GridValidationJs($table, $action, $input, $button, $prefix) {

		$regEx ='/^\+?\d*([\.|,?]\d{1,2}$)?$/';
		$notSelector="[class=TextBoxAlert],";
		foreach ($prefix as $tag) {
            if(!is_array($tag)) $notSelector .= "[name^={$tag}],";
		}
        $inasistencias= $prefix['inasistencia'];
		foreach ($prefix['inasistencia'] as $inasis) {
			$notSelector .= "[name^={$inasis}]";
            if ($inasis !== end($inasistencias)) $notSelector .= ',';
        }
		$js  = "$('#{$table}').find('input{$input}').{$action}(function() {";
		/* Solo sumo los que no esten seleccionado como no cursa*/
		$js .= " if (!$(this).attr('readonly')) { var decimal = {$regEx};";
		/* Solo los que pasen la validacion de la expresion regular*/
		$js .= " var valid   = $(this).val().match(decimal);";
		$js .= " var str = $(this).attr('name');";
		$js .= " var record = str.split('_');";
		/* Solo sumo los numeros validos*/
		$js .= "if ($(this).data('valid') !== '') {";
		$js .= "if (valid !== null && $(this).val() <= $(this).data('valid')) {";
		/*Es valido por lo tanto class normal y desabilitamos tooltip*/
			$js .= "$(this).attr('class','TextBoxNormal');if (\$j(this).tooltip())\$j(this).tooltip('disable');";
			$js .= "var subtotal = 0;";
            $js .= "var row = $(this).closest('tr').find('input{$input}[name*='+record[1]+']:not({$notSelector})').toArray();";
			/*Sumatoria  Asincronica solo numeros validos y no vacios*/
			$js.="chunk(row, valueOfField ,this, function (subtotal, item) { $('#{$prefix['total']}_'+record[1]).val(subtotal.toFixed(2));if ($(item).closest('tr').hasClass('nocursa')){subtotal= 0;}$('#{$prefix['calificacion']}_'+record[1]).val(Math.round(subtotal.toFixed(2)));});";
		$js.= "}else{";
		$js.= "var obj = $(this).attr('name').split('_');$(this).attr('title','El puntaje máximo es '+$(this).data('valid'));";
		$js.= "\$j(this).attr('class','TextBoxAlert');\$j(this).tooltip({content:$(this).attr('title')});\$j(this).tooltip('enable').tooltip('open');}";
		/* activo la sumatoria para el campo total*/
		$js.= "$('#{$prefix['total']}'+str.replace('txt','')).change();}}";
		$js.= "});";
//        var_dump($js);die;
		return $this->minifyJs($js);
	}
	/**
	 * Accion de las  inasistencias de la tabla
	 *
	 */
	public function CheckBoxValidation($table, $action, $prefix, $maxInasistencias) {

		$notSelector = "[name^={$prefix['total']}],[name^={$prefix['calificacion']}],";
        $inasistencias= $prefix['inasistencia'];
        $inasistenciasSelector = '';
		foreach ($inasistencias as $inasis) {
            $inasistenciasSelector .= "input[type=text][name^={$inasis}]";
            if ($inasis !== end($inasistencias)) $inasistenciasSelector .= ',';
        }
        $notSelector .= $inasistenciasSelector;
		$js = "$('#{$table}').find('{$inasistenciasSelector}').{$action}( function () {";
		$js .= '
		var row = $(this).closest("tr");
		var cantidad = 0;
		row.children().find("'.$inasistenciasSelector.'").each(function(ind,val){cantidad += Number($(val).val());});
		row.children().find("input[type=text][name^=tinasist]").val(cantidad);
		if (cantidad >= '.$maxInasistencias.') {if (row.attr("class") !== "retirado") row.attr("class","nocursa"); } else {if ($(this).closest("tr").hasClass("nocursa")){row.removeAttr("class");}}
	    row.children().find("input[class^=TextBox][type=text]:not('.$notSelector.')").keyup();$(this).focus(); });';
		return $this->minifyJs($js);
	}
	/**
	 * Mascara para solo numeros maximo 2 decimales
	 *
	 */
	public function GridMaskJs($table) {
		$js="\$j('#{$table}').find('input:text').filter('[data-mask]').each(function (){ \$j(this).decimalMask($(this).attr('data-mask')); });";
		return $this->minifyJs($js);
	}

	/**
		* Extrae una Cadena con las Asignaciones presentes en los records
		* de los Estudiantes
	 *
	 */
	public function implodeAsignaciones($rows) {

		$pk_asignaciones = array();
		foreach ($rows as $row) {
			if (!in_array($row['pk_asignacion'],$pk_asignaciones)) {
				$pk_asignaciones[] = $row['pk_asignacion'];
			}
		}
		if (count($pk_asignaciones)>1) {
			$pk_asignacion = implode(",", $pk_asignaciones);
		} else {
			$pk_asignacion = $pk_asignaciones[0];
		}
		return $pk_asignacion;
	}

	/**
	 * Retorna si ya fueron consignadas las calificaciones
	 * Depende solo del estado de la materia y que existan
     * personas inscritas
	 */
	public function isConsignado($data) {

		return $this->record->getInscritosEstado($data);
	}


	public function	isNotValidMessage() {
		return "<br><p style=\"text-align:justify;\">No se puede enviar la información a Control de Estudios, existen valores incorrectos y/o incompletos en los recuadros, verifique las siguientes observaciones y vuelva a intentarlo.</p><br>";
	}

	public function isValidMessage() {
		return  "<br>¿Esta seguro que desea Actualizar las Calificaciones?";

	}

	public function isValidEndMessage() {
		return  "Recuerde que antes de asentar las calificaciones en el Acta Definitiva de Calificaciones del Sistema de Control de Estudios, se haya cerciorado que fueron debidamente revisadas con todos los estudiantes, las notas estan completas y que no existen errores, ya que la Resolución del Consejo Universitario N° 01-92-02, vigente desde el 16 de Enero de 1992, establece que:<b>\Las calificaciones definitivas, una vez que han sido procesadas en el Computador, no podrán ser modificadas, a menos que el error haya sido originado en la transcripción desde la hoja de evaluación al acta definitiva de Calificaciones.\</b><br><br>¿Desea enviar la información a Control de Estudios?";

	}

	public function isValidAction() {

		if (isset($this->isValidAction)) {
			return $this->isValidAction;
		} else {
			return;
		}
	}

    private function minifyJs($code) {
        // make it into one long line
        $code = str_replace(array("\n","\r"),'',$code);
        // replace all multiple spaces by one space
        $code = preg_replace('!\s+!',' ',$code);
        // replace some unneeded spaces, modify as needed
        $code = str_replace(array(' {',' }','{ ','; '),array('{','}','{',';'),$code);
        return $code;
    }

    public function generarReporte($Params) {

            $config = Zend_Registry::get('config');
            $dbname = $config->database->params->dbname;
            $dbuser = $config->database->params->username;
            $dbpass = $config->database->params->password;
            $dbhost = $config->database->params->host;
            $report = APPLICATION_PATH . '/modules/transactions/templates/calificacionesparciales/ReporteCalificaciones.jasper';
            $subReport = APPLICATION_PATH . '/modules/transactions/templates/calificacionesparciales/';
            $filename    = 'ListadoDeEstudiantes';
            $filetype    = 'pdf';
            $params      = "'SUBREPORT_DIR=string:{$subReport}|Estructura=integer:{$Params['sede']}|Periodo=integer:{$Params['periodo']}|Escuela=integer:{$Params['escuela']}|Semestre=integer:{$Params['semestre']}|Materia=integer:{$Params['materia']}|Seccion=integer:{$Params['seccion']}|Pensum=integer:{$Params['pensum']}'";

        $cmd         = "java -jar " . APPLICATION_PATH . "/../library/Cli/JRExportCmdCMC.jar -D PGSQL -h {$dbhost} -u {$dbuser} -d {$dbname} -p {$dbpass} -i {$report} -P {$params} -f {$filetype} -b64";
            Zend_Layout::getMvcInstance()->disableLayout();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "application/{$filetype}");
            Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename={$filename}.{$filetype}");
            $outstream = exec($cmd);
            return $outstream;
    }

}
