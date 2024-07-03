<?php

/**
 * @todo Ocultar el boton "Ocultar" del formulario Ver.
 * @todo Ocultar el boton "Eliminar" del formulario Eliminar, solo cuando no se
 *       permita.
 */
class Transactions_ArticulacionController extends Zend_Controller_Action {
	/*
	 * Mensajes a mostrar para el usuario:
	 */

	private $Title = 'Transacciones \ Articulacion';

	public function init() {
		/* Initialize action controller here */
		Zend_Loader::loadClass('Models_DbTable_Usuarios');
		Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');
		Zend_Loader::loadClass('Models_DbTable_Recordsacademicos');

		$this->grupo = new Models_DbTable_UsuariosGrupos();
		$this->estudiante = new Models_DbTable_Usuarios();
		$this->records = new Models_DbTable_Recordsacademicos();

		$this->SwapBytes_Ajax = new SwapBytes_Ajax();
		$this->SwapBytes_Date = new SwapBytes_Date();
		$this->SwapBytes_Jquery = new SwapBytes_Jquery();

		$this->Request = Zend_Controller_Front::getInstance()->getRequest();
	}

	/**
	 * Se inicia antes del metodo indexAction, y valida si esta autentificado,
	 * sl no ser asi, redirecciona a al modulo de login.
	 */
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
		$this->view->title = $this->Title;
		$this->view->module = $this->Request->getModuleName();
		$this->view->controller = $this->Request->getControllerName();
		$this->view->SwapBytes_Ajax = $this->SwapBytes_Ajax;
		$this->view->SwapBytes_Ajax->setView($this->view);
		$this->view->SwapBytes_Jquery = $this->SwapBytes_Jquery;
	}

	public function buscarAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$ci = $this->_getParam('ci');

			if(isset($ci) && is_numeric($ci) && $ci > 0) {
				$datos = $this->estudiante->getRow($ci);
            $datos_arti = $this->records->getEstadoArticulacion($ci);

            // var_dump($datos_arti);

				if (is_array($datos) && count($datos) > 1) {
					$datos['fechanacimiento'] = $this->SwapBytes_Date->convertToForm($datos['fechanacimiento']);

					$json[] = $this->SwapBytes_Jquery->setHtml('lblNombre', $datos['nombre']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblApellido', $datos['apellido']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblFechaNacimiento', $datos['fechanacimiento']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblCorreo', $datos['correo']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblUltimoP', $datos_arti[0]['max']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblCantidadP', $datos_arti[0]['count']);
					$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', '');
					$json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'null');
					$json[] = $this->SwapBytes_Jquery->setHide('lblMessage');

               if ($datos_arti[0]['count'] >= 1 || !empty($datos_arti[0]['count'])){
                  $json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'null');
                  $json[] = $this->SwapBytes_Jquery->setAttr('btnEliminar', 'disabled', 'null');
               }else{
                  $json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'null');
                  $json[] = $this->SwapBytes_Jquery->setAttr('btnEliminar', 'disabled', 'null');
               }

				} else {
					$json[] = $this->SwapBytes_Jquery->setHtml('lblNombre', '');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblApellido', '');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblFechaNacimiento', '');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblCorreo', '');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblUltimoP', '');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblCantidadP', '');
					$json[] = $this->SwapBytes_Jquery->setAttr('txtNewCI', 'disabled', 'true');
					$json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'true');

					$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
					$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', 'El estudiante no existe.');
				}
			} else {
				$json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'true');
				$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
				$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', 'La C.I. no es valida.');
			}

			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

	public function cambiarAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$oldci = $this->_getParam('oldci');
			$datos = $this->estudiante->getRow($oldci);
         $escuela = $this->records->getIDEscuelaUltima($oldci);
			$msg   = '';
			$err   = 0;

			if(!is_numeric($escuela)) {
				$msg .= "- No tiene escuela {$escuela}<br>";
				$err++;
			}

			if(!is_numeric($oldci)) {
				$msg .= '- La C.I. nueva o vieja no son validas.<br>';
				$err++;
			}

			if(isset($datos) && is_array($datos) && count($datos) > 0) {
         }else{
				$msg .= '- La C.I. es Invalida<br>';
				$err++;
         }

			if($err == 0) {
				$rowsAffected = $this->records->makeArticulacion($oldci, $escuela);

					$json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'true');
					$json[] = $this->SwapBytes_Jquery->setAttr('btnEliminar', 'disabled', 'null');
					$msg .= '- La Articulación a sido cargada.<br>';
			}

			$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
			$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', $msg);

			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}



	public function eliminarAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->SwapBytes_Ajax->setHeader();

			$oldci = $this->_getParam('oldci');
			$datos = $this->estudiante->getRow($oldci);
         $escuela = $this->records->getIDEscuelaUltima($oldci);
			$msg   = '';
			$err   = 0;

			if(!is_numeric($escuela)) {
				$msg .= "- No tiene escuela {$escuela}<br>";
				$err++;
			}

			if(!is_numeric($oldci)) {
				$msg .= '- La C.I. nueva o vieja no son validas.<br>';
				$err++;
			}

			if(isset($datos) && is_array($datos) && count($datos) > 0) {
         }else{
				$msg .= '- La C.I. es Invalida<br>';
				$err++;
         }

			if($err == 0) {
				$rowsAffected = $this->records->removeArticulacion($oldci, $escuela);
            $msg .= '- La Eliminación de la Articulación ha sido realizada.<br>';
            $json[] = $this->SwapBytes_Jquery->setAttr('btnEliminar', 'disabled', 'true');
            $json[] = $this->SwapBytes_Jquery->setAttr('btnCambiar', 'disabled', 'null');
			}

			$json[] = $this->SwapBytes_Jquery->setShow('lblMessage');
			$json[] = $this->SwapBytes_Jquery->setHtml('lblMessage', $msg);

			$this->getResponse()->setBody(Zend_Json::encode($json));
		}
	}

}
