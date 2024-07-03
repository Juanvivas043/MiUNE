<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CmcBytes_Filtros {


    private $querys = Array();

    public function __construct() {
        Zend_Loader::loadClass('Models_DbTable_Generic');
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->moduleName     = $this->Request->getModuleName();
        $this->controllerName = $this->Request->getControllerName();
        //$this->logger = Zend_Registry::get('logger');
        $this->genericSQL = new Models_DbTable_Generic();
    }




    public function getUrlAjax() {
        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->moduleName . '/' . $this->controllerName . '/';
    }

    public function generateReserved($valor,$key,$filter){

        $code = $key . ' ';

        if(is_array($valor) && $key != 'WHERE'){

            if($key == 'SELECT'){
                $code .= 'DISTINCT ';
            }
                $aux = implode(', ', $valor);
                $code .= $aux;


        }else if($key == 'WHERE'){
            
            if(is_array($valor)){
                foreach ($valor as $pos => $val) {

                    if(strpos($val, '##') === false){
                        $code .= $val;
                        
                    }else{
                        
                        $aux = array_keys($filter);
                        
                        foreach($aux as $p){
                           if(strpos($val, '##' .$p. '##') !== false){
                                $precode = str_replace('##' .$p. '##',$filter[$p], $val);
                           
                           }
                        }
                        //$code .= $val . ' = ' . $filter[substr($val, 3)];
                        $code .= $precode;
                    }

                    if($pos != count($valor) - 1){
                        $code .= ' AND ';
                    }

                }
            }else{

                if(strpos($valor, '##') === false){
                        $code .= $valor;

                    }else{

                        $aux = array_keys($filter);
                        foreach($aux as $p){
                            if(strpos($val, '##' .$p. '##') !== false){
                                $precode = str_replace('##' .$p. '##',$filter[$p], $val);

                           }
                        }
                        //$code .= $val . ' = ' . $filter[substr($val, 3)];
                        $code .= $precode;

                    }

            }

        }else{

            $code .= $valor;

        }

        return $code;

    }

    public function generateQueries($tablas,$filter = null,$ejecucion = NULL,$elemento = NULL){
        

        $queryarray = Array();

        if($ejecucion == 1){
            $label = array_keys($tablas);
            $js = $this->genertaBoxes($label);

        
        
        foreach($tablas as $key => $tab){

            foreach($tab as $keys => $t){

                switch ($keys) {

                    case 0:
                        $from = $this->generateReserved($t, 'FROM', NULL);
                        break;
                    case 1:
                        if($t != NULL){
                           
                           $where = $this->generateReserved($t, 'WHERE',$filtersarray);
                           
                        }else{
                            $where = '';
                        }
                        break;
                    case 2:
                        $columnas = $this->generateReserved($t, 'SELECT', NULL);
                        break;
                    case 3:
                        $pos = strrpos($t, " ");
                        if($t != NULL && $pos === false){

                           $order = 'ORDER BY 2 ' . $t;

                        }else if($t != NULL){
                            $order = 'ORDER BY '. $t;
                        }
                        break;
                    case 4:
                        if($t != NULL){

                            $group = '';

                        }else{
                            $group = $t;
                        }
                        break;
                }


                
            
            


            }
            $code = $columnas . ' '. $from . ' ' . $where . ' ' . $group . ' ' . $order;
            $cont++;
            //var_dump($code);
            
            if($cont<=1){

            $queryresult = $this->genericSQL->getSelect($code);
            }else{
                $queryresult = Array();
            }
            
            
            $llaves = array_keys($queryresult[0]);
                if(!$queryresult){
                    $html .= "<option value=''>------------------</option>";
                }
            foreach($queryresult as $mikey => $res){

                
                    
                if($mikey == 0){
                    $selected2 = Array($key => $res[$llaves[0]]);
                    $html .= "<option value=''>------------------</option>";
                    
                }
                $html .= "<option value=". $res[$llaves[0]].">".$res[$llaves[1]]."</option>";

            }
           

                $js .= '$("#'. $key .'").html("' . $html . '");';
                $js .= $this->buildOnChange($key);
                $html = '';
                $filtersarray = array_merge($selected2,$queryarray);
                $queryarray = $filtersarray;
                
            
                
        }
    }else{ //si es ejecutado por segunda vez hago recursividad
        
        
        if($filter[$elemento] != ''){
            $allstripe = true;
        }else{
            $allstripe = false;
        }

        $mod_keys = array_keys($filter);
        foreach($mod_keys as $key => $mod){

            if($mod == $elemento){
                $cont = $key;
            }
        }

        
        $cont2 = 0;
        $done = false;
        foreach($tablas as $key => $tab){
            
            if($cont2 > $cont){ //este if se encarga de saltar al elemento que cambie a mano y sus anteriores
                
                foreach($tab as $keys => $t){
                
                    switch ($keys) {

                        case 0:
                            $from = $this->generateReserved($t, 'FROM', NULL);
                            break;
                        case 1:
                            if($t != NULL){
                                if($done == true){
                                    $where = $this->generateReserved($t, 'WHERE',$filtersarray);
                                }else{
                                    $where = $this->generateReserved($t, 'WHERE',$filter);
                                }
                            }else{
                                $where = '';
                            }
                            break;
                        case 2:
                            $columnas = $this->generateReserved($t, 'SELECT', NULL);
                            break;
                        case 3:
                            $pos = strrpos($t, " ");
                            if($t != NULL && $pos === false){

                               $order = 'ORDER BY 2 ' . $t;

                            }else if($t != NULL){
                                $order = 'ORDER BY '. $t;
                            }
                            break;
                        case 4:
                            
                        if($t == NULL){

                            $group = '';

                        }else{
                            
                            $group = $t;
                            //var_dump($group);
                        }
                        break;
                    }
                }
                $code = $columnas . ' '. $from . ' ' . $where . ' ' . $group .' ' .$order;
                
                if($allstripe == true){

                    $queryresult = $this->genericSQL->getSelect($code);
                }else{
                    $queryresult = Array();
                }


                $llaves = array_keys($queryresult[0]);

                if(!$queryresult){
                    $html .= "<option value=''>------------------</option>";
                }

                foreach($queryresult as $mikey => $res){
                //recorro el result del query para rellenar los elementos y a la vez guardar los pk de cada uno


                    if($mikey == 0){
                        
                        $selected2 = Array($key => $res[$llaves[0]]);

                    }
                    if($cont2 > $cont && $mikey == 0){
                        $html .= "<option value=''>------------------</option>";
                        
                    }
                    $html .= "<option value=". $res[$llaves[0]].">".$res[$llaves[1]]."</option>";

                }
                
                    $js .= '$("#'. $key .'").html("' . $html . '");';
                    $html = '';
                    $filtersarray = array_merge($selected2,$queryarray);
                    $queryarray = $filtersarray;
                    $done = true;
                    
            $cont2++;
            }else{
             //almaceno el valor del elemento q cambie a mano para poderlo usar en los demas filtros
             $cont2++;
             $selected2 = Array($mod_keys[$cont] => $filter[$mod_keys[$cont]]);
             $filtersarray = array_merge($selected2,$queryarray);
             $queryarray = $filtersarray;
            }
            

        }
    

    }


        return $js;
    }


    public function genertaBoxes($labels){

        $html .= "<table id=select_filters><tr><td></td>";

        foreach($labels as $nombre){

            $html .= "<td class='label'><label>{$nombre}</label></td>";

        }
        
        $html .= "</tr><tr><td width='100px' align='right' style='text-align:right;font-weight:bolder;'><img src=' " . Zend_Controller_Front::getInstance()->getBaseUrl() ."/images/icons/table_go.png'>&nbsp;Filtro:&nbsp;</td>";
        
        foreach($labels as $nombre){
        
            $html .= "<td class='select'><select id='{$nombre}' name='{$nombre}'></select></td>";

        }
        $html .= "</tr></table>";
        
        $js = "$(\"#filtro\").append(\"" .$html. "\");";
        return $js;

    }


    public function buildOnChange($element){
        $url = $this->getUrlAjax(); //"/MiUNEControlDeEstudios/transactions/inscripcionautomatica";
        $js = "$(\"#" . $element . "\").change(function(){ $.getJSON('$url' + 'filter/select/' + $(this).attr('id') + '/filters/' +  escape($('#filtro').find(':input').serialize()) + '', function(d) {executeCmdsFromJSON(d)});});";

        return $js;

    }

   public function addCustom($nombre,$fill,$id=null){

       /*DANIEL ESTUVO AQUI. ADOLFO, EN REALIDAD*/
       if(isset($id)){
           $html = "<td class='select'><select id='{$id}' name='{$nombre}'>";
           $label = "<td class='label'><label>{$nombre}</label></td>";
       }else{
           $html = "<td class='select'><select id='{$nombre}' name='{$nombre}'>";
           $label = "<td class='label'><label>{$nombre}</label></td>";
       }

        foreach($fill as $val){
            
            $html .= "<option value='{$val['valor']}'>{$val['display']}</option>";

        }


        $html .= "</select></td>";

        $js = "$('#select_filters').children().children().first().append(\"" . $label . "\");";
        $js .= "$('#select_filters').children().children().last().append(\"" . $html . "\");";

        return $js;
        //echo $html;

    }
      

}

?>
