<?php
class Consultas_SolventeController extends Zend_Controller_Action{
    private $Title = "Consultas / Solvente";

    public function init() {
       $this->SwapBytes_Ajax           = new SwapBytes_Ajax();
       $this->SwapBytes_Ajax->setView($this->view);
       $this->namespace                = new Zend_Session_Namespace("nop");
       $this->cedulas                  = new Zend_Session_Namespace("cedula");
       Zend_Loader::loadClass('Models_DbTable_Grupos');
       Zend_Loader::loadClass('Models_DbTable_Reiniciarpass');
       Zend_Loader::loadClass('CmcBytes_Profit');
       Zend_Loader::loadClass('Models_DbTable_Usuarios');
       Zend_Loader::loadClass('Models_DbTable_Solventes');
       Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
       $this->Misgrupos                = new Models_DbTable_Grupos();
       $this->reiniciar                = new Models_DbTable_Reiniciarpass();
       $this->solvente               = new Models_DbTable_Solventes();
       $this->CmcBytes_profit          = new CmcBytes_Profit();
       $this->SwapBytes_Jquery         = new SwapBytes_Jquery();
       $this->estudiante               = new Models_DbTable_Usuarios();
       $this->SwapBytes_Crud_Form      = new SwapBytes_Crud_Form();

       $this->grupo = new Models_DbTable_UsuariosGrupos();
      }

     public function preDispatch() {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
              $this->_helper->redirector('index', 'login', 'default');
            }

            if (!$this->grupo->haveAccessToModule()) {
              $this->_helper->redirector('accesserror', 'profile', 'default');
            }
      }

     public function validar($ci){
          $cont = 0;
          if($ci == ""){
              return false;
          }
          $per = "0123456789";
          for ($i = 0; $i<strlen($ci);$i++){
              for($j = 0; $j<strlen($per);$j++){
                  if($ci[$i]== $per[$j]){
                      $cont = $cont + 1;

                  }
              }


          }
          if($cont == strlen($ci) ){
             return true;
                                   }

          return false;
      }

     public function buscarAction(){
          $this->SwapBytes_Ajax->setHeader();
          $ci= $this->_getParam('ci');
          $json = array();
          $DDTI = false;
          if($this->validar($ci)){
                $entro = false;
                $this->namespace->array = array();
                $this->cedulas->array = array();
                array_push($this->namespace->array,"false");
                array_push($this->cedulas->array,$ci);

                $mostra = false;
                $usuarioes = $this->Misgrupos->getUsuarioestudiante($ci);
                $usuariodoce = $this->Misgrupos->getUsuariodocente($ci);
                $cuadro =  $this->Misgrupos->getCuadro($ci);
                $cantidad = $this->reiniciar->getCantidadgrupos($ci);
                $solvente   =$this->CmcBytes_profit->getSolvente($ci);
                $grupo = $this->solvente->getGrupos($ci);
                $periodo = $this->solvente->getUltPeriodo();
                $docente = $this->solvente->getDocenteActivo($ci,$periodo[0]['pk_periodo']);
                $estudiante = $this->solvente->getEstuInscr($ci,$periodo[0]['pk_periodo']);

                if($usuarioes[0]['nombre']!= "" && $cantidad[0]['count']<2 ){

                    $json[] = '$("#n_usuariotxt").html("'.$usuarioes[0]['nombre'].'")';
                    $json[] = '$("#a_usuariotxt").html("'.$usuarioes[0]['apellido'].'")';
                    $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
                    $json[] = '$("#mensaje").html("")';
                    $json[] = '$("#claveclik").attr("disabled",false)';
                    $entro = true;
                    $mostrar = true;
                }
                if(!$entro && $usuariodoce[0]['nombre']!=""){
                    $escuela = false;

                    $json[] = '$("#n_usuariotxt").html("'.$usuariodoce[0]['nombre'].'")';
                    $json[] = '$("#a_usuariotxt").html("'.$usuariodoce[0]['apellido'].'")';

                     foreach($cuadro as $misgrupo)
                     {

                         if($misgrupo['grupo']=="Estudiante" && !$escuela){
                           $json[] = '$("#e_usuariotxt").html("'.$usuarioes[0]['valor'].'")';
                           $json[] = '$("#claveclik").attr("disabled",false)';
                           $escuela = true;
                         }

                          if($misgrupo['grupo']=="Docente" && !$escuela){
                           $json[] = '$("#e_usuariotxt").html("Docente")';
                           $json[] = '$("#claveclik").attr("disabled",false)';
                           $escuela = true;
                         }
                         if($misgrupo['grupo']=="DDTI"){
                             $DDTI =true;
                           $this->namespace->array[0] = "true";
//                            $json[] = '$("#mensaje").html("Contactar con el Administrador")';
//                            $json[] = '$("#claveclik").attr("disabled",true)';
                         }


                     }
                    if(!$escuela){
                       $json[] = '$("#e_usuariotxt").html("Administrativo")';
                    }


                    $mostrar = true;
                }

                if($mostrar){
                    $json[] = '$("#informacion").show()';
                    $json[] = '$("#mensaje").hide()';
                    if($solvente){
                        $json[] = '$("#est_usuariotxt").html("<b><p style=color:green;>Cuotas Solventes</p></b>")';
                    }else{
                        $json[] = '$("#est_usuariotxt").html("<b><p style=color:red;>Cuotas no solventes</p></b>")';

                    }

                    $perfil .= "<table>";

                    foreach($grupo AS $grup){

                        $perfil .= "<tr><td>";
                        $perfil .= $grup['grupo'];
                        $perfil .= "</td>";
                        $perfil .= "<td>";
                        if($grup['pk_atributo']==854){ //Docente

                            if($docente[0] != ''){
                                $perfil .= '<p style=color:green;><b>&nbsp;Activo</b></p>';
                            }else{
                                $perfil .= '<p style=color:red;><b>&nbsp;No Activo</b></p>';
                            }

                        }else if($grup['pk_atributo']==855){ //Estudiante

                            if($estudiante[0] != ''){
                                $perfil .= '<p style=color:green;><b>&nbsp;Inscrito</b></p>';
                            }else{
                                $perfil .= '<p style=color:red;><b>&nbsp;No Inscrito</b></p>';
                            }

                        }if($grup['pk_atributo']==1745){ //Administrativo

                            $perfil .= '<p style=color:green;><b>&nbsp;Activo</b></p>';

                        }
                        $perfil .= "</td></tr>";

                    }

                    $perfil .= "</table>";

                    $json[] = '$("#per_usuariotxt").html("'.$perfil.'")';

                    if($DDTI){
//                      $json[] = '$("#mensaje").show()';
                    }
                    $json[] = $this->SwapBytes_Jquery->setAttr('foto', 'src', "'{$this->SwapBytes_Ajax->getUrlAjax()}photo/id/{$ci}'");

                    //$this->getResponse()->setBody(Zend_Json::encode($json));
                    $this->SwapBytes_Crud_Form->setJson($json);
                    $this->SwapBytes_Crud_Form->setWidthLeft('120px');
                    $this->SwapBytes_Crud_Form->getAddOrEditLoad();

                }else{
                    $json[] = '$("#informacion").hide()';
                    $json[] = '$("#mensaje").html("<b><p style=font-size:18px;color:red;>Cedula no encontrada</p></b>")';
                    $json[] = '$("#mensaje").show()';
                    $json[] = '$("#claveclik").attr("disabled",true)';
                    $this->getResponse()->setBody(Zend_Json::encode($json));
                }

            }else{
               $json[] = '$("#informacion").hide()';
               $json[] = '$("#mensaje").html("<b><p style=font-size:18px;color:red;>Cedula no encontrada</p></b>")';
               $json[] = '$("#mensaje").show()';
               $json[] = '$("#claveclik").attr("disabled",true)';
               $this->getResponse()->setBody(Zend_Json::encode($json));
            }


      }

     /**
      * Obtiene la foto de un usuario desde la Base de Datos.
      */
     public function photoAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id    = $this->_getParam('id', 0);
		$image = $this->estudiante->getPhoto($id);

		$this->getResponse()
		     ->setHeader('Content-type', 'image/jpeg')
		     ->setBody($image);

     }



     public function indexAction() {
	$this->view->title = $this->Title;
        $this->view->SwapBytes_Ajax = new SwapBytes_Ajax();
      }
}

?>
