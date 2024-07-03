<?php

class Transactions_BibliotecaestudianteController extends Zend_Controller_Action {


    private $Title = 'Biblioteca estudiante';

    
   
    public function init() {
         
        /* Initialize action controller here */
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
        Zend_Loader::loadClass('Models_DbTable_Bibliotecaestudiante');
        Zend_Loader::loadClass('Models_DbTable_Prestamo');
        Zend_Loader::loadClass('Models_DbTable_BibliotecaAgregar');
        Zend_Loader::loadClass('Une_Filtros');
        
        $this->estudiantes   = new Models_DbTable_Bibliotecaestudiante();
        $this->prestamo      = new Models_DbTable_Prestamo();
        $this->agregar       = new Models_DbTable_BibliotecaAgregar();
        $this->usuario       = new Models_DbTable_Usuarios();
        $this->grupo         = new Models_DbTable_UsuariosGrupos();
        $this->filtros       = new Une_Filtros(); 
        $this->CmcBytes_Filtros         = new CmcBytes_Filtros();
        $this->SwapBytes_Date           = new SwapBytes_Date();
        $this->SwapBytes_Uri            = new SwapBytes_Uri();
        $this->SwapBytes_Form           = new SwapBytes_Form();
        $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
        $this->SwapBytes_Crud_Action    = new SwapBytes_Crud_Action();
        $this->SwapBytes_Crud_List      = new SwapBytes_Crud_List();
        $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();
        $this->SwapBytes_Crud_Search    = new SwapBytes_Crud_Search();
        $this->SwapBytes_Html           = new SwapBytes_Html();
        $this->SwapBytes_Html_Message   = new SwapBytes_Html_Message();
        $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->SwapBytes_Jquery_Ui = new SwapBytes_Jquery_Ui();
        $this->SwapBytes_Jquery_Mask    = new SwapBytes_Jquery_Mask();
        $this->authSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->Request = Zend_Controller_Front::getInstance()->getRequest();
        $this->session = new Zend_Session_Namespace('session');
        $this->redirect_session = new Zend_Session_Namespace('redirect_session');
        
      

        $this->SwapBytes_Crud_Action->setDisplay(false, false, false, false, false, false);
	$this->SwapBytes_Crud_Action->setEnable(false, false, false, false, false, false);
        $Listar = "<button id='btnListar' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnListar' role='button' aria-disabled='false'>Listar";
        $this->SwapBytes_Crud_Action->addCustum($Listar);
        $Solicitar = "<button id='btnSolicitar' class='ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only' name='btnSolicitar' role='button' aria-disabled='false'>Solicitar";
        $this->SwapBytes_Crud_Action->addCustum($Solicitar);
        
        
        $sede[0]['valor']='7';
        $sede[0]['display']='los Naranjos ';
        $sede[1]['valor']='8';
        $sede[1]['display']='Centro ';
        $this->sede =  $sede;
        
        $iten[0]['valor']='1';
        $iten[0]['display']='Libro ';
        $iten[1]['valor']='2';
        $iten[1]['display']='Tesis ';
        $this->iten =  $iten;
        
        //$this->autor;
        
       // $this->materia;
        
        $this->tablas = Array(
                                  
            );

        $this->_params['filters'] = $this->SwapBytes_Uri->queryToArray($this->Request->getParam('filters'));
       
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
        $this->view->title      = $this->Title;
        $this->view->filters    = $this->filtros;
        $this->view->SwapBytes_Jquery      = $this->SwapBytes_Jquery;
		$this->view->SwapBytes_Crud_Action = $this->SwapBytes_Crud_Action;
		$this->view->SwapBytes_Crud_Form   = $this->SwapBytes_Crud_Form;
		$this->view->SwapBytes_Crud_Search = $this->SwapBytes_Crud_Search;
        $this->view->SwapBytes_Ajax        = new SwapBytes_Ajax();
        $this->view->SwapBytes_Ajax->setView($this->view);
        $this->view->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();

        
       
    }
       
    public function listAction(){
     if ($this->_request->isXmlHttpRequest()) {
         $this->SwapBytes_Ajax->setHeader();
         
          $pageNumber  = $this->_getParam('page', 1);
          $buscar  = $this->_getParam('buscar');         
          $itemPerPage = 4;
          $pageRange   = 5;
          $tipo = $this->_getParam('tipo');
         // $autor = $this->_getParam('autor');
         // $materia = $this->_getParam('materia');
          $sede = $this->_getParam('sede');
          $val = "checkiten";
          $cont = 0;
          $json[] = '$("#mensaje").hide()';
          if($tipo == '1'){ // libro
              $iten = $this->estudiantes->get_libro($itemPerPage, $pageNumber,$sede,NULL);
              $count = $this->estudiantes->getSQLCountLibros($sede);
              $pk_libro  = $this->pk_libros($iten);
              if($pk_libro != NULL){
              $autor_principal  = $this->agregar->get_autor($pk_libro,'t');
              }
              $iten = $this->transformarlibro($iten, $autor_principal);
              if($buscar != ""){
              $iten = $this->estudiantes->get_libro($itemPerPage, $pageNumber,$sede,$buscar); 
              $iten = $this->transformarlibro($iten, $autor_principal);
              $iten = $this->buscarlibro($iten, $buscar);
              $count = Count($iten);
              }
              
          }else{ // tesis
              $iten = $this->estudiantes->get_tesis($itemPerPage, $pageNumber,$sede,NULL);
              $count = $this->estudiantes->getSQLCountTesis();
               if($buscar != ""){
              $iten = $this->estudiantes->get_tesis($itemPerPage, $pageNumber,$sede,$buscar);     
              $iten = $this->buscartesis($iten, $buscar);
              $count = Count($iten);
              }
              
          }
          
      $html = '<table id ="tbl_iten" width="800px"'; 
      foreach($iten as $obj){
      $val = $val.$cont;    
      // titulo
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> TITULO : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="titulotxt">';
      $html .= $obj['titulo'];
      $html .= '</h1>';    

      $html .= '</td>';
      $html .= '</tr>';
      if($tipo == '1'){
      // Autor principal
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> AUTOR PRINCIPAL : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="autortxt">';
      $html .= $autor_principal[$cont]['autor'];
      $html .='</h1>';
      $html .= '</td>';
      $html .= '</tr>';
      
      // ano de publicacion
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> AÑO DE PUBLICACION : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="anotxt">';
      $html .= $obj['ano'];
      $html .='</h1>';
      $html .= '</td>';
      $html .= '</tr>';
      
       // ciudad
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> CIUDAD : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="ciudadtxt">';
      $html .= $obj['ciudad'];
      $html .= '</h1>';
      $html .= '</td>';
      $html .= '</tr>';
      
       // editorial
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> EDITORIAL : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="editorialtxt">';
      $html .= $obj['editorial'];
      $html .=' </h1>';
      $html .= '</td>';
      $html .= '</tr>';
      }else{
        // Autor principal
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> AUTOR PRINCIPAL : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="autortxt">';
      $html .= $obj['autor'];
      $html .='</h1>';
      $html .= '</td>';
      $html .= '</tr>';   
       // Escuela
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> ESCUELA : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="escuelatxt">';
      $html .= $obj['escuela'];
      $html .= '</h1>';
      $html .= '</td>';
      $html .= '</tr>';
      
       // Calificacion
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> CALIFICACION : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="calificaciontxt">';
      $html .= $obj['calificacion'];
      $html .=' </h1>';
      $html .= '</td>';
      $html .= '</tr>';    
          
          
          
      }
       // PAGINA
      $html .= '<tr>';
      $html .= '<td>';
      $html .= '<h1> PAGINA : </h1>';
      $html .= '</td>';
      $html .= '<td style="text-align:center">';
      $html .= '<h1 id ="paginatxt">';
      $html .= $obj['pagina'];
      $html .='</h1>';
      $html .= '</td>';
      
      //checkbox
      $html .= '<td>';
	      $html .= '<input class ="checkiten" name ="checkiten" type="checkbox"';
	      $html .= "id = {$val} ";
	     // $html .= " value={$obj['cota']}";
		$html .= 'value ="';
		$html .= $obj[cota];
	       $html .= '">Agregar';
	      $html .= '</td>';     

	      $html .= '</tr>';
	      
	      // Separacion
	      $html .= '<tr>';
	      $html .= '<td>';
	      $html .= '<p>---------------</p>';
	      $html .= '</td>';
	      $html .= '</tr>';
	      $cont = $cont + 1;
	      $val = 'checkiten';
	      }
	      $html .= '</table>';
	       if(isset($itemPerPage) && isset($pageNumber) && isset($pageRange) && isset($count)) {
			$paginator  = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
			$paginator->setItemCountPerPage($itemPerPage)
                          ->setCurrentPageNumber($pageNumber)
                          ->setPageRange($pageRange);
      
                $html .= $paginator;
            }
      $html  = str_replace("\n", "", $html);    
      if($cont > 0){
      $json[] = "$('#tableData').show()";   
      $json[] = $this->SwapBytes_Jquery->setHtml('tableData', $html);
      }else{
       $json[] = "$('#tableData').hide()";   
      }
      $this->getResponse()->setBody(Zend_Json::encode($json));
     }
    }  

    public function solicitarAction(){
         if ($this->_request->isXmlHttpRequest()) {
         $this->SwapBytes_Ajax->setHeader();
         $pk = $this->_getParam('pk');
         $cota = explode(",", $pk);
         $usuario = $this->estudiantes->get_usuariogrupo($this->authSpace->userId);
         $json[] = '$("#mensaje").hide()';
         $estado = $this->valestado($this->authSpace->userId);
         $cantidad = count($cota)-1;


          if($estado != "mora" && $estado != "prestamo" && $estado != "espera"){
              if(($cantidad <=3 && $cantidad != 0) || true){
                   $json[] = '$("#mensaje").show()';
                    $mensaje = 'Solicitud generada exitosamente ';
                    $json[] = '$("#mensaje").html("'.$mensaje.'")';
                    $this->estudiantes->insertar_fichaprestamo($usuario[0]['pk_usuariogrupo']);    
                    $pk_prestamo = $this->estudiantes->get_pkprestamo($usuario[0]['pk_usuariogrupo']);
                    foreach ($cota as $mycota){
                    if($mycota != ''){
                    $this->estudiantes->insert_prestamo($pk_prestamo[0]['pk_prestamo'],$mycota);
                    }
                }
              }else{
                  // mensaje de que la cantidad sobrepasa a 3 libros
                   $json[] = '$("#mensaje").show()';
                   $mensaje = 'Error no puedes solicitar mas de 3 libros ';
                   $json[] = '$("#mensaje").html("'.$mensaje.'")';
                   
                 
              } 
         }else{
             $json[] = '$("#mensaje").show()';
             $mensaje = 'Tiene una ficha en mora , en prestamo o espera.';
             $json[] = '$("#mensaje").html("'.$mensaje.'")';
             // mensaje de que tiene un ficha en mora , en prestamo o espera.
            // echo 'por ficha';
         }
     }
     $this->getResponse()->setBody(Zend_Json::encode($json));
    }

    public function filterAction(){
            $this->SwapBytes_Ajax->setHeader(); 
            $select = $this->_getParam('select');
            $values = $this->SwapBytes_Uri->queryToArray($this->_getParam('filters'));
            
            if(!$select || !$values){
                 
                
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,null,1,null);
                $json[] = $this->CmcBytes_Filtros->addCustom('Sede'  ,$this->sede); 
                $json[] = $this->CmcBytes_Filtros->addCustom('Tipo'  ,$this->iten); 
                //$json[] = $this->CmcBytes_Filtros->addCustom('Autor'  ,$this->autor);
                //$json[] = $this->CmcBytes_Filtros->addCustom('Materia'  ,$this->materia);
            }else{
                $json[] = $this->CmcBytes_Filtros->generateQueries($this->tablas,$values,null,$select);
            }           
            $json[]=  $this->autorAction($values);
            $json[]=  $this->materiaAction($values);
            $this->getResponse()->setBody(Zend_Json::encode($json));
        }
        
    public function autorAction($values){
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $sede = 7;
            $dataRow = $this->estudiantes->get_autor($sede);
             $json2 .= "<option value=-1 select>-----------------</option>";
            foreach ($dataRow as $autor){
                $json2 .= "<option value={$autor['pk_atributo']} select>{$autor['valor']}</option>";
            }
             $json[] = '$("#Autor").html("'.$json2.'")';
             return $json;
         }
        
    }
    
    public function materiaAction(){
         if ($this->_request->isXmlHttpRequest()) {
            $this->SwapBytes_Ajax->setHeader();
            $dataRow = $this->estudiantes->get_materia($tipo);
             $json2 .= "<option value=-1 select>-----------------</option>";
            foreach ($dataRow as $materia){
                $json2 .= "<option value={$materia['pk_atributo']} select>{$materia['valor']}</option>";
            }
             $json[] = '$("#Materia").html("'.$json2.'")';
             return $json;
         }
        
    }

    private function valestado($pk_usuario){
      
     if(is_numeric($pk_usuario) && !empty($pk_usuario)) {
        $mora = $this->prestamo->getMORA($pk_usuario);  
        $prestamo = $this->prestamo->getPrestamo($pk_usuario);
        $devuelto = $this->prestamo->getDevuelto($pk_usuario);
        $espera   = $this->prestamo->getEspera($pk_usuario);
        if(count($mora)>0){
          return "mora"  ;//mora
        }
        if(count($prestamo)>0){
          return "prestamo";//Transito
        }
        if(count($espera)>0){
          return "espera"  ;//espera
        }
        if(count($devuelto)>0){
          return "devuelto";//solvente
        }
        
        
      }
      
     
  }
    
    public function pk_libros($RowData){
     $pk_libro = '';
      foreach ($RowData as $data){
          $pk_libro .= $data['pk_libro'].',';
      }
      $pk_libro = trim($pk_libro, ',');
      return $pk_libro;
  }
  
   public function buscarlibro($rows,$search){
     
      if($search != ''){
          $i = 0;
          foreach ($rows as $row){
              $v1 = strpos(strtolower($row['cota'])     , strtolower($search));
              $v2 = strpos(strtolower($row['titulo'])   , strtolower($search));
              $v3 = strpos(strtolower($row['editorial']), strtolower($search));
              $v4 = strpos(strtolower($row['autor_principal']), strtolower($search));
              $v5 = strpos(strtolower($row['ciudad']), strtolower($search));
              if(is_numeric($v1) || is_numeric($v2) || is_numeric($v3) || is_numeric($v4) || is_numeric($v5)){
                    $rows[$i]['find']= true; 
                    
              }else{
                  unset($rows[$i]);
              }
                
               $i = $i + 1;
          }
          
 
    }
    return $rows;
  }
  
   public function buscartesis($rows,$search){
     
      if($search != ''){
          $i = 0;
          foreach ($rows as $row){
              $v1 = strpos(strtolower($row['cota'])     , strtolower($search));
              $v2 = strpos(strtolower($row['titulo'])   , strtolower($search));
              $v3 = strpos(strtolower($row['editorial']), strtolower($search));
              $v4 = strpos(strtolower($row['autor_principal']), strtolower($search));
              $v5 = strpos(strtolower($row['escuela']), strtolower($search));
              if(is_numeric($v1) || is_numeric($v2) || is_numeric($v3) || is_numeric($v4) || is_numeric($v5)){
                    $rows[$i]['find']= true; 
                    
              }else{
                  unset($rows[$i]);
              }
                
               $i = $i + 1;
          }
          
 
    }
    return $rows;
  }
  
   private function transformarlibro($libro,$principal){
         
      $cant_libros    = count($libro);
      $cant_principal = count($principal);
     
      for ($i = 0 ; $i < $cant_libros; $i++ ){
          for($j = 0; $j < $cant_principal; $j++ ){
              if($libro[$i]['pk_libro']==$principal[$j]['pk_libro']){
                  $libro[$i]['autor_principal'] = $principal[$j]['autor'];
              }
          }
          
      }
        return  $libro;
          
         
  }
  
  
}





