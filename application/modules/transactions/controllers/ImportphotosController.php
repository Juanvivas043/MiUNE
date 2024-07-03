<?php

/**
 * @todo Crear el directorio /tmp/importphotos despues de limpiar el mismo.
 * @todo Definir las variables de sesion en el momento de usar este modulo.
 */

class Transactions_ImportphotosController extends Zend_Controller_Action {

    private $_title = 'Transacciones \ Importar fotos';
    private $_targetDir = '/tmp/importphotos';

    public function init() {
        Zend_Loader::loadClass('Models_DbTable_Usuarios');
        Zend_Loader::loadClass('Forms_Importphotos');
        Zend_Loader::loadClass('Models_DbTable_UsuariosGrupos');

        $this->usuario = new Models_DbTable_Usuarios();
        $this->grupo   = new Models_DbTable_UsuariosGrupos();
    }

    /**
     * Se inicia antes del metodo indexAction, y valida si esta autentificado,
     * al no ser asi, redirecciona a al modulo de login.
     */
    //function preDispatch() {
    //    if (!Zend_Auth::getInstance()->hasIdentity()) {
    //        $this->_helper->redirector('index', 'login', 'default');
    //    }

    //    if(!$this->grupo->haveAccessToModule()) {
    //        $this->_helper->redirector('accesserror', 'profile', 'default');
    //    }
    //}

    /**
     * Crea la estructura base de la pagina principal.
     */
    public function indexAction() {
        $this->view->title = $this->_title;
        $this->view->form = new Forms_Importphotos();

        //$this->_deleteAll($this->_targetDir);

        if ($this->_request->isPost()) {
            $data = $this->_request->getPost();
            if ($this->view->form->isValid($data)) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->setDestination($this->_targetDir);

                if ($upload->isValid()) {
                    try {
                        $upload->receive();
                    } catch (Zend_File_Transfer_Exception $e) {
                        $e->getMessage();
                    }

                    $name = $upload->getFileName('file');
                    $size = $upload->getFileSize('file');
                    $type = $upload->getMimeType('file');

                    $decompress = new Zend_Filter_Decompress(array('adapter' => 'Zip',
                                'options' => array('target' => $this->_targetDir)));

                    $decompress->filter($name);

                    $files = $this->_getFiles($this->_targetDir);
                    $ok    = 0;
                    $total = 0;

                    foreach ($files as $file) {
                        preg_match("/([0-9]+)\.([^\.]+)$/", $file, $matches);
                        $name = strtoupper($matches[1]);
                        $extension = strtoupper($matches[2]);

                        if (is_numeric($name) && $extension == 'JPG') {
                            $image = file_get_contents(realpath($file));
                            $affected = $this->usuario->setPhoto($name, $image);
                            $status = ($affected == 1) ? '[ OK ]' : '[FAIL]';
                            $ok += ( $affected == 1) ? 1 : 0;
                            $total++;

                            $this->view->report .= "{$name}.{$extension}\t{$status}\n";
                        }
                    }

                    $this->_deleteAll($this->_targetDir);

                    $fail = $total - $ok;

                    $this->view->report .= "\n\nResumen:\n--------\n   Ok: {$ok}\n Fail: {$fail}\nTotal: {$total}\n";
                }
            }
        }
    }

    private function _getFiles($path = '.', $level = 0) {
        $list = array();
        $ignore = array('.', '..');
        $handle = @opendir($path);

        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, $ignore)) {
                if (is_dir("$path/$file")) {
                    $list = array_merge($list, $this->_getFiles("$path/$file", ($level + 1)));
                } else {
                    $list[] = "$path/$file";
                }
            }
        }
        closedir($handle);

        return $list;
    }

    private function _deleteAll($directory, $empty = false) {
        if (substr($directory, -1) == "/") {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (!is_readable($directory)) {
            return false;
        } else {
            $directoryHandle = opendir($directory);

            while ($contents = readdir($directoryHandle)) {
                if ($contents != '.' && $contents != '..') {
                    $path = $directory . "/" . $contents;

                    if (is_dir($path)) {
                        $this->_deleteAll($path);
                    } else {
                        unlink($path);
                    }
                }
            }

            closedir($directoryHandle);

            if ($empty == false) {
                if (!rmdir($directory)) {
                    return false;
                }
            }

            return true;
        }
    }

}
