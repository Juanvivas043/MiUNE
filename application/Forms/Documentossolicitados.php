<?php
class Forms_Documentossolicitados extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Solicitud');

        

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $docs_atrs = new Zend_Form_Element_Hidden('doc_atrs');
		$docs_atrs->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

        

       $documentos = new Zend_Form_Element_Hidden('hidden-documentos');
       $documentos->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'documentos'))
                ));

         

        $this->addElements(array($id,
			$docs_atrs,
                        $page,
                        
            $documentos
                        ));
    }
}

?>
