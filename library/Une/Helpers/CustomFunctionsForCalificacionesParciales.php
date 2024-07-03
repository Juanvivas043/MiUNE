<?php 

class Une_Helpers_CustomFunctionsForCalificacionesParciales {

  protected $_PORCENTAJE_DE_INASISTENCIAS = 0.25;

	public function __construct() {
        Zend_Loader::loadClass('Models_DbTable_Asignaturas');
        Zend_Loader::loadClass('Models_DbTable_Asignaciones');
        $this->SwapBytes_Ajax         = new SwapBytes_Ajax();
        $this->asignaturas   = new Models_DbTable_Asignaturas();
        $this->asignaciones   = new Models_DbTable_Asignaciones();
        $this->SwapBytes_Ajax_Action = new SwapBytes_Ajax_Action();
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Uri = new SwapBytes_Uri();
        $this->SwapBytes_Html = new SwapBytes_Html();

        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
    * Mueve el ultimo item de un array y lo coloca de primero
    **/
    public function moverUltimoItemDePrimero($array){

          /*Agarras la ultima columna del array que es la materia*/
          $lastvalue = end($array);
          $lastkey = key($array);

          /*Creas la columna con los 2 valores*/
          $arr1 = array($lastkey=>$lastvalue);

          /*Extrae el ultimo elemento del array*/
          array_pop($array);

          /*El nuevo valor en introducido al principio */
          $columnName = array_merge($arr1,$array);

          return $columnName;
    }

    public function castToFloat($valor){
    	try{
    		$final = (Float) $valor;
    	}catch(Exception $e){
    		$final = 0;
    	}
    	return $final;
    }

    /**
    * @param $Notas es un arreglo que contiene arregles y cada uno contiene notas de una materia
    * @return Retorna un arreglo que contiene arreglos con notas por materia
    */
    public function convertirArrayNotas($Notas, $arrayDeParametros = null){

      $temp_anterior = "";
      $notas_propiedades = array();
      $solo_notas = array();
      $final_array = array();
      $total = 0;
      $acumulado = 0;
      $inasistencias = 0;
      $haveInasistencias = false;
      $ultimo = (count($Notas)-1);
      $primero = 0;

      foreach ($Notas as $key => $value) {

        //Si es la primera vez o Si el pk de la asignatura actual es igual al de la anterior
        if($temp_anterior == $value["pk_atributo"] || $key == $primero){

          if($key == $ultimo) { //Si es el ultimo

            $notas_propiedades[$value['evaluacion']] = array('nota' => $value['calificacion'], array('evaluable' => $value["evaluable"], 'total' => false));
            $solo_notas[$value['evaluacion']] = $value['calificacion'];

            if($value['evaluable']){ //Si es una nota del tipo evaluable
                $acumulado += $this->castToFloat($value["calificacion"]);   
                $total += $this->castToFloat($value["calificacion"]);

            }else{

              $haveInasistencias = true;
              $inasistencias += $this->castToFloat($value["calificacion"]);
            }


            $nombreDeMateria = $Notas[$key]["materia"]; //Guardo el nombre de la materia anterior para llamar asi el array
            if($haveInasistencias) { //Si el regimen tiene inasistencias
              if(isset($arrayDeParametros)){ //Si esta el array con la informacion para sacar la cantidad de clases
                
                $params = $this->asignaciones->getInfoInsistencias($value['pk_atributo'], $arrayDeParametros['periodo'], $arrayDeParametros['sede'], $arrayDeParametros['escuela'], $arrayDeParametros['pensum']);
                $cantidadDeClases = $this->asignaciones->get_clases_asignacion_feriado($params);
                $cantidadDeInasistenciasPosibles = $cantidadDeClases * $this->_PORCENTAJE_DE_INASISTENCIAS; //Multiplicamos por el porcentaje de inasistencias permitido
                $cantidadDeInasistenciasPosibles = ceil($cantidadDeInasistenciasPosibles); //Redondear hacia arriba

                $notas_propiedades['t.inasist'] = array('nota' => $inasistencias, 'condition'=>array('isInasis'=>true,'limite' => $cantidadDeInasistenciasPosibles));
                $solo_notas['t.inasist'] = $inasistencias;
                
              }else{ //Si no tiene el array, simplemente guarda las inasistencias del alumno.
                $notas_propiedades['t.inasist'] = array('nota' => $inasistencias, array('total' => true));
                $solo_notas['t.inasist'] = $inasistencias;
              }

              
            }
            
            $notas_propiedades['acum'] = array('nota' => $acumulado, array('total' => true));
            $solo_notas['acum'] = $acumulado;
            $solo_notas['c.f'] = $inasistencias >= $cantidadDeInasistenciasPosibles ? 0 : round($total);
            $notas_propiedades['c.f'] = array('nota' => $solo_notas['c.f'], array('total' => true));
            

            $notas_propiedades['pk_atributo'] = $value['pk_atributo'];
            $notas_propiedades['materia'] = $nombreDeMateria;
            $notas_propiedades['estado'] = $value["estado"];
            $solo_notas['pk_atributo'] = $value['pk_atributo'];
            $solo_notas['materia'] = $nombreDeMateria;
            $solo_notas['estado'] = $value["estado"];

            
            $total = 0; 
            $acumulado = 0;
            $inasistencias = 0;

            $materias[$nombreDeMateria] = $notas_propiedades;
            $materias1[$nombreDeMateria] = $solo_notas;

          }else{
            $notas_propiedades[$value['evaluacion']] = array('nota' => $value['calificacion'], array('evaluable' => $value["evaluable"], 'total' => false));
            $solo_notas[$value['evaluacion']] = $value['calificacion'];

            if($value['evaluable']){//Si es una nota del tipo evaluable
                $acumulado += $this->castToFloat($value["calificacion"]);   
                $total += $this->castToFloat($value["calificacion"]);
            }else{

               $haveInasistencias = true;
               $inasistencias += $this->castToFloat($value["calificacion"]);
            }

          }

        }else{

          $nombreDeMateria = $Notas[$key-1]["materia"]; //Guardo el nombre de la materia anterior para llamar asi el array
          if($haveInasistencias) {
              if(isset($arrayDeParametros)){
                
                $params = $this->asignaciones->getInfoInsistencias($Notas[$key-1]["pk_atributo"], $arrayDeParametros['periodo'], $arrayDeParametros['sede'], $arrayDeParametros['escuela'], $arrayDeParametros['pensum']);
                $cantidadDeClases = $this->asignaciones->get_clases_asignacion_feriado($params);
                $cantidadDeInasistenciasPosibles = $cantidadDeClases * $this->_PORCENTAJE_DE_INASISTENCIAS; //Multiplicamos por el porcentaje de inasistencias permitido
                $cantidadDeInasistenciasPosibles = ceil($cantidadDeInasistenciasPosibles); //Redondear hacia arriba

                $notas_propiedades['t.inasist'] = array('nota' => $inasistencias, 'condition'=>array('isInasis'=>true,'limite' => $cantidadDeInasistenciasPosibles));
                $solo_notas['t.inasist'] = $inasistencias;

              }else{
                $notas_propiedades['t.inasist'] = array('nota' => $inasistencias, array('total' => true));
                $solo_notas['t.inasist'] = $inasistencias;
              }
              
          }
          
          $notas_propiedades['acum'] = array('nota' => $acumulado, array('total' => true));
          $solo_notas['acum'] = $acumulado;
          $solo_notas['c.f'] = $inasistencias >= $cantidadDeInasistenciasPosibles ? 0 : round($total);
          $notas_propiedades['c.f'] = array('nota' => $solo_notas['c.f'], array('total' => true));
          

          $total = 0;
          $acumulado = 0;
          $inasistencias = 0;

          $notas_propiedades['pk_atributo'] = $value['pk_atributo'];
          $notas_propiedades['materia'] = $nombreDeMateria;
          $notas_propiedades['estado'] = $Notas[$key-1]["estado"];
          $solo_notas['pk_atributo'] = $value['pk_atributo'];
          $solo_notas['materia'] = $nombreDeMateria;
          $solo_notas['estado'] = $Notas[$key-1]["estado"];


          $materias[$nombreDeMateria] = $notas_propiedades;
          $materias1[$nombreDeMateria] = $solo_notas;

          unset($notas_propiedades);
          unset($solo_notas);

          $notas_propiedades[$value['evaluacion']] = array('nota' => $value['calificacion'], array('evaluable' => $value["evaluable"], 'total' => false));
          $solo_notas[$value['evaluacion']] = $value['calificacion'];

          if($value['evaluable']){ //Si en el regimen la nota es evaluabl

              $acumulado += $this->castToFloat($value["calificacion"]);   
              $total += $this->castToFloat($value["calificacion"]);

          }else{
             $inasistencias += $this->castToFloat($value["calificacion"]);
          }

        }
        $temp_anterior = $value["pk_atributo"];
      }
      $final_array['notas'] = $materias1;
      $final_array['prop'] = $materias;

      return $materias;
    }

    /**
    * Llena filtros con array personalizado.
    **/
    public function fillSelect($parametros){
      $this->SwapBytes_Ajax->setHeader();
      $consulta = $this->asignaturas->getDatosRecordEstudiante($parametros);
      $this->SwapBytes_Ajax_Action->fillSelect($consulta);
    }

}